<?php
// app/Http/Controllers/Admin/BookingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Booking, Module, Package, Payment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingNotification;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    protected $module_id = 5;
    protected $module;

    public function __construct()
    {
        $this->module = Module::find($this->module_id);
    }
    public function index()
    {
        $this->authorize('view', $this->module);

        $title = 'Packages';
        $module = $this->module;
        $bookings = Booking::with(['user', 'package'])
            ->latest()
            ->paginate(15);

        return view('admin.bookings.index', compact('bookings', 'title', 'module'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'package']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,confirmed,cancelled'
        ]);

        $booking->update(['status' => $request->status]);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking status updated!');
    }

    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $this->module);
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking deleted successfully!');
    }

    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string',
            'customer_email' => 'required|email',
            'package_id'     => 'required|exists:packages,id',
            'date'           => 'required|date|after_or_equal:today',
            'selected_slot'  => 'required|date',
            'num_people'     => 'required|integer|min:1',
            'package_min'    => 'required|integer',
            'package_max'    => 'required|integer'
        ]);

        $package = Package::findOrFail($validated['package_id']);

        if ($validated['num_people'] < $package->min_bookings || $validated['num_people'] > $package->max_bookings) {
            return response()->json([
                'success' => false,
                'message' => "Number of people must be between {$package->min_bookings} - {$package->max_bookings}"
            ], 422);
        }

        $totalAmount = $package->price * $validated['num_people'];

        try {
            $booking = Booking::create([
                'user_id'            => auth()->id() ?? null,
                'package_id'         => $validated['package_id'],
                'booking_start_time' => $validated['selected_slot'],
                'duration_minutes'   => $package->duration_minutes ?? 60,
                'customer_name'      => $validated['customer_name'],
                'customer_phone'     => $validated['customer_phone'],
                'customer_email'     => $validated['customer_email'],
                'people_count'       => $validated['num_people'],
                'total_amount'       => $totalAmount,
                'status'             => 'pending', // Pending until payment confirmed
                'payment_id'         => null
            ]);

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount'     => $totalAmount,
                'currency'   => 'SAR',
                'status'     => 'pending', // Pending until payment confirmed
                'payment_method' => 'tap',
                'transaction_id' => null, // Will be updated after payment
                'metadata' => json_encode([
                    'package_name'  => $package->name,
                    'booking_date'  => $validated['date'],
                    'selected_slot' => $validated['selected_slot']
                ])
            ]);

            session([
                'pending_booking_id' => $booking->id,
                'pending_payment_id' => $payment->id,
                'booking_data'       => $validated // Keep original data if needed
            ]);

            // Create Tap charge and get payment URL
            $paymentUrl = $this->createTapCharge($totalAmount, $validated, $booking->id, $payment->id);

            return response()->json([
                'success' => true,
                'redirect_url' => $paymentUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Payment initiation exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking. Please try again.'
            ], 400);
        }
    }


    private function createTapCharge($amount, $bookingData)
    {
        $secretKey = config('tap.secret_key');

        $data = [
            'amount' => $amount,
            'currency' => 'SAR',
            'customer_initiated' => true,
            'threeDSecure' => true,
            'save_card' => false,
            'description' => 'EscapeBooking - Package Booking',
            'receipt' => [
                'email' => true,
                'sms' => true
            ],
            'reference' => [
                'transaction' => 'txn_' . uniqid(),
                'order' => 'ord_' . uniqid()
            ],
            'customer' => [
                'first_name' => $bookingData['customer_name'],
                'middle_name' => '',
                'last_name' => '',
                'email' => $bookingData['customer_email'],
                'phone' => [
                    'country_code' => 966,
                    'number' => ltrim($bookingData['customer_phone'], '0')
                ]
            ],
            'merchant' => [
                'id' => '49439197'
            ],
            'source' => [
                'id' => 'src_all'
            ],
            'post' => [
                'url' => route('tap.callback', [], true)
            ],
            'redirect' => [
                'url' => route('tap.redirect', [], true)
            ],
            'metadata' => [
                'booking_date' => $bookingData['date'],
                'package_id' => $bookingData['package_id'],
                'slot' => $bookingData['selected_slot']
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.tap.company/v2/charges',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $secretKey,
                'accept: application/json',
                'content-type: application/json',
                'lang_code: en'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        Log::info('Tap Charge Response', [
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error
        ]);

        $result = json_decode($response, true) ?: ['raw' => $response];

        if ($httpCode == 200 && isset($result['transaction']['url'])) {
            return $result['transaction']['url'];
        } else {
            Log::error('Tap payment initiation failed', ['response' => $result]);
            throw new \Exception('Payment initiation failed: Unable to get payment URL from Tap.');
        }
    }

    public function tapCallback(Request $request)
    {
        //
    }

    private function verifyTapCharge($chargeId)
    {
        $secretKey = config('tap.secret_key');

        $ch = curl_init();
        curl_setopt_array($ch, [ 
            CURLOPT_URL => "https://api.tap.company/v2/charges/{$chargeId}", 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET", 
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$secretKey}",
                "accept: application/json"
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // ✅ Debug logging
        Log::info('Tap Verify Charge', [
            'charge_id' => $chargeId,
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error
        ]);

        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        $result = json_decode($response, true) ?: ['raw' => $response];

        if ($httpCode !== 200) {
            $errorMsg = $result['errors'][0]['description'] ?? $result['message'] ?? 'Unknown error';
            throw new \Exception("Verify failed ({$httpCode}): {$errorMsg}");
        }

        return $result;
    }

    public function handleRedirect(Request $request)
    {
        $tapId = $request->query('tap_id');

        if (!$tapId) {
            return view('tap.failed', [
                'error' => 'Invalid payment response',
                'tapId' => null
            ]);
        }

        try {
            $bookingId = session('pending_booking_id');
            $paymentId = session('pending_payment_id');


            if (!$bookingId || !$paymentId) {
                Log::warning('No pending booking/payment in session for tap_id: ' . $tapId);

                $payment = Payment::where('metadata', 'like', '%' . $tapId . '%')->first();

                if ($payment) {
                    $bookingId = $payment->booking_id;
                    $paymentId = $payment->id;
                    Log::info('Found payment by tap_id in metadata', [
                        'booking_id' => $bookingId,
                        'payment_id' => $paymentId
                    ]);
                } else {
                    try {
                        $charge = $this->verifyTapCharge($tapId);
                        Log::info('Charge status for orphaned redirect', [
                            'tap_id' => $tapId,
                            'status' => $charge['status'] ?? 'unknown'
                        ]);
                    } catch (\Exception $e) {
                        // Ignore verification errors here
                    }

                    return view('tap.failed', [
                        'error' => 'Booking session expired',
                        'tapId' => $tapId
                    ]);
                }
            }

            $charge = $this->verifyTapCharge($tapId);
            if ($charge['status'] === 'CAPTURED') {
                $booking = Booking::with('package')->findOrFail($bookingId);
                $booking->update([
                    'status' => 'paid',
                    'payment_id' => $tapId
                ]);

                $payment = Payment::findOrFail($paymentId);

                $payment->update([
                    'status' => 'completed',
                    'transaction_id' => $tapId,
                    'metadata' => json_encode([
                        'tap_response' => $charge,
                        'card_brand' => $charge['card']['brand'] ?? null,
                        'card_last4' => $charge['card']['last4'] ?? null,
                    ])
                ]);

                session()->forget(['pending_booking_id', 'pending_payment_id', 'booking_data']);
                try {
                    // Send email to customer
                    Mail::to($booking->customer_email)
                        ->send(new BookingNotification($booking, 'customer'));
                    // Send email to owner
                    Mail::to(env('OWNER_EMAIL'))
                        ->send(new BookingNotification($booking, 'owner'));
                } catch (\Exception $e) {
                    Log::error('Failed to send booking emails: ' . $e->getMessage());
                }
                
                Log::info('Payment completed successfully', [
                    'booking_id' => $bookingId,
                    'payment_id' => $paymentId,
                    'tap_id' => $tapId
                ]);

                return view('tap.success', [
                    'transactionId' => $tapId,
                    'packageName' => $booking->package->name ?? 'Package',
                    'bookingDate' => $booking->booking_start_time ? date('Y-m-d h:i A', strtotime($booking->booking_start_time)) : null,
                    'numPeople' => $booking->people_count,
                    'totalAmount' => number_format($booking->total_amount, 2),
                    'customerEmail' => $booking->customer_email,
                    'bookingId' => $booking->id,
                    'paymentMethod' => 'Tap (via Card)',
                    'cardBrand' => $charge['card']['brand'] ?? 'Card',
                    'cardLast4' => $charge['card']['last4'] ?? '****'
                ]);
            } else {
                Log::warning('Payment failed with status: ' . ($charge['status'] ?? 'unknown'), [
                    'tap_id' => $tapId,
                    'booking_id' => $bookingId,
                    'payment_id' => $paymentId
                ]);

                $this->handleFailedPayment($bookingId, $paymentId, $charge['status'] ?? 'failed');

                // ✅ Return failed view with error details
                return view('tap.failed', [
                    'error' => 'Payment ' . strtolower($charge['status'] ?? 'failed'),
                    'tapId' => $tapId,
                    'status' => $charge['status'] ?? 'failed'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Tap verification failed: ' . $e->getMessage(), [
                'tap_id' => $tapId,
                'booking_id' => $bookingId ?? null,
                'payment_id' => $paymentId ?? null
            ]);

            if (isset($bookingId) && isset($paymentId)) {
                $this->handleFailedPayment($bookingId, $paymentId, 'verification_failed');
            } else {
                try {
                    $payment = Payment::where('metadata', 'like', '%' . $tapId . '%')->first();
                    if ($payment) {
                        $this->handleFailedPayment($payment->booking_id, $payment->id, 'verification_failed_orphaned');
                    }
                } catch (\Exception $cleanupError) {
                    Log::error('Failed to cleanup orphaned payment', [
                        'tap_id' => $tapId,
                        'error' => $cleanupError->getMessage()
                    ]);
                }
            }

            return view('tap.failed', [
                'error' => 'Unable to verify payment: ' . $e->getMessage(),
                'tapId' => $tapId
            ]);
        }
    }

    /**
     * Handle failed payment - either delete records or mark as failed
     */
    private function handleFailedPayment($bookingId, $paymentId, $reason)
    {
        try {
            // Option 1: Delete the records (clean approach)
            Booking::where('id', $bookingId)->update(['status' => 'failed']);
            Payment::where('id', $paymentId)->update(['status' => 'failed']);
            // Payment::where('id', $paymentId)->delete();
            // Booking::where('id', $bookingId)->delete();

            Log::info('Failed payment records cleaned up', [
                'booking_id' => $bookingId,
                'payment_id' => $paymentId,
                'reason' => $reason
            ]);

            session()->forget(['pending_booking_id', 'pending_payment_id', 'booking_data']);
        } catch (\Exception $e) {
            Log::error('Failed to cleanup payment records', [
                'booking_id' => $bookingId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
