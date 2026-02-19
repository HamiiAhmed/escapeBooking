<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $module_id = 6;
    protected $module;

    public function __construct()
    {
        $this->module = Module::find($this->module_id);
    }
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $this->authorize('view', $this->module);

        $title = 'Payments';
        $module = $this->module;

        $query = Payment::with(['booking', 'user'])
            ->latest(); // Most recent first

        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search by transaction ID, customer name, or email
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('booking', function($bookingQuery) use ($search) {
                      $bookingQuery->where('customer_name', 'like', "%{$search}%")
                                   ->orWhere('customer_email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(15)->appends($request->except('page')); // Preserve filters in pagination links

        // Get summary statistics
        $stats = [
            'total_completed' => Payment::where('status', 'completed')->sum('amount'),
            'total_pending' => Payment::where('status', 'pending')->sum('amount'),
            'total_failed' => Payment::where('status', 'failed')->count(),
            'today_count' => Payment::whereDate('created_at', today())->count(),
        ];

        return view('admin.payments.index', compact('payments', 'stats', 'title', 'module'));
    }

    /**
     * Display the specified payment details.
     */
    public function show($id)
    {
        $this->authorize('view', $this->module);

        $title = 'Payments Details';
        $module = $this->module;
        $payment = Payment::with(['booking', 'user'])
            ->findOrFail($id);

        // Decode metadata if it's a string
        if (is_string($payment->metadata)) {
            $payment->metadata = json_decode($payment->metadata, true);
        }

        return view('admin.payments.show', compact('payment', 'title', 'module'));
    }

    /**
     * Update payment status (for manual adjustments).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded'
        ]);

        $payment = Payment::findOrFail($id);
        $oldStatus = $payment->status;
        $payment->status = $request->status;
        $payment->save();

        // Also update booking status if payment is completed/refunded
        if ($payment->booking) {
            if ($request->status === 'completed') {
                $payment->booking->update(['status' => 'paid']);
            } elseif ($request->status === 'refunded') {
                $payment->booking->update(['status' => 'refunded']);
            } elseif ($request->status === 'failed') {
                $payment->booking->update(['status' => 'failed']);
            }
        }

        Log::info('Payment status updated', [
            'payment_id' => $id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'user_id' => auth()->id()
        ]);

        return redirect()->route('payments.show', $id)
            ->with('success', 'Payment status updated successfully');
    }

    /**
     * Export payments as CSV.
     */
    public function export(Request $request)
    {
        $query = Payment::with(['booking']);

        // Apply filters same as index
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->get();

        $filename = 'payments_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $handle = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($handle, [
            'ID', 'Transaction ID', 'Booking ID', 'Customer Name', 
            'Customer Email', 'Amount', 'Currency', 'Status', 
            'Payment Method', 'Date', 'Metadata'
        ]);

        foreach ($payments as $payment) {
            fputcsv($handle, [
                $payment->id,
                $payment->transaction_id,
                $payment->booking_id,
                $payment->booking->customer_name ?? 'N/A',
                $payment->booking->customer_email ?? 'N/A',
                $payment->amount,
                $payment->currency,
                $payment->status,
                $payment->payment_method,
                $payment->created_at->format('Y-m-d H:i:s'),
                json_encode($payment->metadata)
            ]);
        }

        fclose($handle);
        
        return response()->stream(
            function() use ($handle) {
                // Already output via fputcsv above
            },
            200,
            $headers
        );
    }
}