<?php
// app/Http/Controllers/Admin/BookingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Booking, Module, Package, Payment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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


    // public function initiatePayment(Request $request)
    // {
    //     $validated = $request->validate([
    //         'customer_name' => 'required|string|max:255',
    //         'customer_phone' => 'required|string',
    //         'customer_email' => 'required|email',
    //         'package_id' => 'required|exists:packages,id',
    //         'date' => 'required|date|after:now',
    //         'selected_slot' => 'required|date'
    //     ]);

    //     $package = Package::where('id', $validated['package_id'])->where('is_active', true)->firstOrFail();

    //     // Session save for callback
    //     session(['pending_booking' => array_merge($validated, [
    //         'package_price' => $package->price,
    //         'package_name' => $package->name
    //     ])]);

    //     // Tap Payment URL generate
    //     $paymentUrl = $this->generateTapPaymentUrl($package->price, $validated);

    //     return response()->json([
    //         'success' => true,
    //         'redirect_url' => $paymentUrl
    //     ]);
    // }

    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string',
            'customer_email' => 'required|email',
            'package_id' => 'required|exists:packages,id',
            'date' => 'required|date|after_or_equal:today',
            'selected_slot' => 'required|date',
            'num_people' => 'required|integer|min:1',  // NEW
            'package_min' => 'required|integer',
            'package_max' => 'required|integer'
        ]);

        $package = Package::findOrFail($validated['package_id']);

        if ($validated['num_people'] < $package->min_bookings || $validated['num_people'] > $package->max_bookings) {
            return response()->json([
                'success' => false,
                'message' => "Number of people must be between {$package->min_bookings} - {$package->max_bookings}"
            ], 422);
        }

        $totalAmount = $package->price * $validated['num_people'];

        session(['pending_booking' => array_merge($validated, [
            'package_price' => $package->price,
            'duration_minutes' => $package->duration_minutes,
            'total_amount' => $totalAmount,
            'package_name' => $package->name
        ])]);

        try {
            $paymentUrl = $this->createTapCharge($totalAmount, $validated);
            // ✅ Return clean JSON with the URL string
            return response()->json([
                'success' => true,
                'redirect_url' => $paymentUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Payment initiation exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Payment initiation failed. Please try again.'
            ], 400);
        }

        return response()->json(['success' => true, 'redirect_url' => $paymentUrl]);
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
            CURLOPT_SSL_VERIFYPEER => false,  // ✅ SSL fix
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
        Log::info('Tap Charge Response', [
            'http_code' => $httpCode,
            'transaction' => $result['transaction']['url'],
        ]);

        if ($httpCode == 200 && isset($result['transaction']['url'])) {
            return $result['transaction']['url'];
        } else {
            Log::error('Tap payment initiation failed', ['response' => $result]);
            throw new \Exception('Payment initiation failed: Unable to get payment URL from Tap.');
        }
    }

    public function tapCallback(Request $request)
    {
        Log::info('Tap Charge Response', [
            'tap call back function called',
        ]);
        try {
            $chargeId = $request->id;

            // Verify payment status
            $charge = $this->verifyTapCharge($chargeId);

            if ($charge['status'] === 'CAPTURED') {
                $bookingData = session('pending_booking');

                if (!$bookingData) {
                    throw new \Exception('No pending booking in session');
                }

                try {
                    $booking = Booking::create([
                        'user_id' => auth()->id() ?? null,
                        'package_id' => $bookingData['package_id'],
                        'booking_start_time' => $bookingData['selected_slot'],
                        'duration_minutes' => $bookingData['duration_minutes'] ?? 60,
                        'customer_name' => $bookingData['customer_name'],
                        'customer_phone' => $bookingData['customer_phone'],
                        'customer_email' => $bookingData['customer_email'],
                        'final_price' => $bookingData['total_amount'],
                        'people_count' => $bookingData['num_people'],
                        'total_amount' => $bookingData['total_amount'],
                        'status' => 'paid',
                        'payment_id' => $chargeId
                    ]);

                    // Create payment record
                    $payment = Payment::create([
                        'booking_id' => $booking->id,
                        'user_id' => $booking->user_id,
                        'amount' => $bookingData['total_amount'],
                        'currency' => 'SAR',
                        'status' => 'completed',
                        'payment_method' => 'tap',
                        'transaction_id' => $chargeId,
                        'metadata' => json_encode([
                            'tap_response' => $charge,
                            'card_brand' => $charge['card']['brand'] ?? null,
                            'card_last4' => $charge['card']['last4'] ?? null,
                        ])
                    ]);

                    session()->forget('pending_booking');

                    Log::info('Payment completed successfully', [
                        'booking_id' => $booking->id,
                        'payment_id' => $payment->id
                    ]);

                    return redirect()->route('tap.success')->with('success', 'Booking confirmed!');
                } catch (\Exception $e) {
                    Log::critical('Payment was captured but database save failed!', [
                        'charge_id' => $chargeId,
                        'error' => $e->getMessage(),
                        'booking_data' => $bookingData
                    ]);

                    // Notify admin, store in failed transactions, etc.

                    return redirect()->route('tap.failed')
                        ->with('error', 'Payment successful but booking failed. Please contact support with ID: ' . $chargeId);
                }
            }

            return redirect()->route('tap.failed')->with('error', 'Payment not captured');
        } catch (\Exception $e) {
            Log::error('Tap callback failed: ' . $e->getMessage());
            return redirect()->route('tap.failed')->with('error', 'Payment verification failed');
        }
    }
    // public function tapCallback(Request $request)
    // {
    //     try {
    //         $chargeId = $request->id;

    //         if (!$chargeId) {
    //             Log::error('Tap callback: No charge ID provided');
    //             return redirect()->route('tap.failed')->with('error', 'Invalid payment response');
    //         }

    //         // Verify payment status
    //         $charge = $this->verifyTapCharge($chargeId);

    //         if ($charge['status'] === 'CAPTURED') {
    //             $bookingData = session('pending_booking');

    //             if (!$bookingData) {
    //                 Log::error('Tap callback: No pending booking in session', ['charge_id' => $chargeId]);
    //                 return redirect()->route('tap.failed')->with('error', 'Booking session expired');
    //             }

    //             // Create booking
    //             $booking = Booking::create([
    //                 'user_id' => auth()->id() ?? null,
    //                 'package_id' => $bookingData['package_id'],
    //                 'booking_start_time' => $bookingData['selected_slot'],
    //                 'duration_minutes' => $bookingData['duration_minutes'] ?? 60,
    //                 'customer_name' => $bookingData['customer_name'],
    //                 'customer_phone' => $bookingData['customer_phone'],
    //                 'customer_email' => $bookingData['customer_email'],
    //                 'final_price' => $bookingData['total_amount'],
    //                 'people_count' => $bookingData['num_people'],
    //                 'total_amount' => $bookingData['total_amount'],
    //                 'status' => 'paid',
    //                 'payment_id' => $chargeId
    //             ]);

    //             // Create payment record
    //             Payment::create([
    //                 'booking_id' => $booking->id,
    //                 'user_id' => $booking->user_id,
    //                 'amount' => $bookingData['total_amount'],
    //                 'currency' => 'SAR',
    //                 'status' => 'completed',
    //                 'payment_method' => 'tap',
    //                 'transaction_id' => $chargeId,
    //                 'metadata' => json_encode([
    //                     'tap_response' => $charge,
    //                     'card_brand' => $charge['card']['brand'] ?? null,
    //                     'card_last4' => $charge['card']['last4'] ?? null,
    //                 ])
    //             ]);

    //             session()->forget('pending_booking');

    //             Log::info('Payment completed successfully', [
    //                 'booking_id' => $booking->id,
    //                 'payment_id' => $chargeId
    //             ]);

    //             return redirect()->route('tap.success')->with('success', 'Booking confirmed!');
    //         }

    //         // Payment not captured - log and redirect to failed
    //         Log::warning('Tap payment not captured', [
    //             'charge_id' => $chargeId,
    //             'status' => $charge['status'] ?? 'unknown'
    //         ]);

    //         return redirect()->route('tap.failed')->with('error', 'Payment was not successful');
    //     } catch (\Exception $e) {
    //         Log::error('Tap callback exception: ' . $e->getMessage(), [
    //             'charge_id' => $chargeId ?? null,
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return redirect()->route('tap.failed')->with('error', 'An error occurred processing your payment');
    //     }
    // }

    private function verifyTapCharge($chargeId)
    {
        $secretKey = config('tap.secret_key');

        $ch = curl_init();
        curl_setopt_array($ch, [  // ✅ Official style
            CURLOPT_URL => "https://api.tap.company/v2/charges/{$chargeId}",  // ✅ v2 endpoint
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",  // ✅ GET method
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
        
        Log::info('Tap redirect', ['tap_id' => $tapId]);
        
        if (!$tapId) {
            return redirect()->route('tap.failed')->with('error', 'Invalid payment response');
            }
            
            try {
            // Verify the charge status via API (you already have this method)
            $charge = $this->verifyTapCharge($tapId);
                
            if ($charge['status'] === 'CAPTURED') {
                return redirect()->route('tap.success')->with('success', 'Payment successful!');
            } else {
                return redirect()->route('tap.failed')->with('error', 'Payment ' . strtolower($charge['status'] ?? 'failed'));
            }
        } catch (\Exception $e) {
            Log::error('Tap verification failed: ' . $e->getMessage());
            return redirect()->route('tap.failed')->with('error', 'Unable to verify payment');
        }
    }
}
