<?php
// app/Http/Controllers/Admin/BookingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Booking, Module, Package, Payment, Coupon};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            ->where('status', '=', 'paid')
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
        session()->forget('pending_booking_data');
        $validated = $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string',
            'customer_email' => 'required|email',
            'package_id'     => 'required|exists:packages,id',
            'date'           => 'required|date|after_or_equal:today',
            'selected_slot'  => 'required|date',
            'num_people'     => 'required|integer|min:1',
            'coupon_code'    => 'nullable|string|exists:coupons,code',
        ]);


        $package = Package::lockForUpdate()->findOrFail($validated['package_id']);

        if (
            $validated['num_people'] < $package->min_bookings ||
            $validated['num_people'] > $package->max_bookings
        ) {

            return response()->json([
                'success' => false,
                'message' => "Number of people must be between {$package->min_bookings} - {$package->max_bookings}"
            ], 422);
        }

        DB::beginTransaction();

        try {

            $originalAmount = $package->price * $validated['num_people'];
            $discountAmount = 0;
            $couponId = null;
            $couponCode = null;

            if (!empty($validated['coupon_code'])) {

                $coupon = Coupon::where('code', $validated['coupon_code'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$coupon) {
                    throw new \Exception("Invalid coupon.");
                }

                if (
                    $coupon->usage_limit &&
                    $coupon->used_count >= $coupon->usage_limit
                ) {
                    throw new \Exception("Coupon usage limit reached.");
                }

                if (
                    $coupon->min_amount > 0 &&
                    $originalAmount < $coupon->min_amount
                ) {
                    throw new \Exception("Minimum amount not reached.");
                }

                if ($coupon->discount_type === 'percent') {
                    $discountAmount = ($originalAmount * $coupon->discount_value) / 100;
                } else {
                    $discountAmount = $coupon->discount_value;
                }

                $discountAmount = min($discountAmount, $originalAmount);

                $couponId   = $coupon->id;
                $couponCode = $coupon->code;
            }

            $totalAmount = $originalAmount - $discountAmount;

            $bookingToken = Str::uuid()->toString();

            session([
                'pending_booking_data' => [
                    'token'           => $bookingToken,
                    'validated'       => $validated,
                    'original_amount' => $originalAmount,
                    'discount_amount' => $discountAmount,
                    'total_amount'    => $totalAmount,
                    'coupon_id'       => $couponId,
                    'coupon_code'     => $couponCode,
                ]
            ]);

            DB::commit();

            $paymentUrl = $this->createTapCharge($totalAmount, $validated);

            return response()->json([
                'success' => true,
                'redirect_url' => $paymentUrl
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
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
                'slot' => $bookingData['selected_slot'],
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

        if (Payment::where('transaction_id', $tapId)->exists()) {
            Log::warning('Duplicate Tap redirect attempt', ['tap_id' => $tapId]);

            return view('tap.failed', [
                'error' => 'This payment has already been processed.',
                'tapId' => $tapId
            ]);
        }

        $pendingData = session('pending_booking_data');

        if (!$pendingData) {
            Log::warning('No pending booking data in session for tap_id: ' . $tapId);
            return view('tap.failed', [
                'error' => 'Booking session expired',
                'tapId' => $tapId
            ]);
        }

        try {
            $charge = $this->verifyTapCharge($tapId);

            if ($charge['status'] !== 'CAPTURED') {

                session()->forget('pending_booking_data');

                Log::warning('Payment failed with status: ' . ($charge['status'] ?? 'unknown'), [
                    'tap_id' => $tapId
                ]);

                return view('tap.failed', [
                    'error'  => 'Payment ' . strtolower($charge['status'] ?? 'failed'),
                    'tapId'  => $tapId,
                    'status' => $charge['status'] ?? 'failed'
                ]);
            }

            $tapAmount = (float) $charge['amount'];
            $sessionAmount = (float) $pendingData['total_amount'];

            if ($tapAmount !== $sessionAmount) {
                Log::error('Payment amount mismatch', [
                    'tap_amount' => $tapAmount,
                    'session_amount' => $sessionAmount,
                    'tap_id' => $tapId
                ]);

                session()->forget('pending_booking_data');

                return view('tap.failed', [
                    'error' => 'Payment amount mismatch.',
                    'tapId' => $tapId
                ]);
            }

            DB::beginTransaction();

            $validated = $pendingData['validated'];
            $package   = Package::findOrFail($validated['package_id']);

            $originalAmount = $pendingData['original_amount'] ?? $sessionAmount;
            $discountAmount = $pendingData['discount_amount'] ?? 0;
            $totalAmount    = $pendingData['total_amount'];
            $couponId       = $pendingData['coupon_id'] ?? null;
            $couponCode     = $pendingData['coupon_code'] ?? null;

            $booking = Booking::create([
                'user_id'            => auth()->id() ?? null,
                'package_id'         => $validated['package_id'],
                'booking_start_time' => $validated['selected_slot'],
                'duration_minutes'   => $package->duration_minutes ?? 60,
                'customer_name'      => $validated['customer_name'],
                'customer_phone'     => $validated['customer_phone'],
                'customer_email'     => $validated['customer_email'],
                'people_count'       => $validated['num_people'],
                'original_amount'    => $originalAmount,
                'discount_amount'    => $discountAmount,
                'total_amount'       => $totalAmount,
                'coupon_id'          => $couponId,
                'coupon_code'        => $couponCode,
                'status'             => 'paid',
                'payment_id'         => $tapId
            ]);

            $payment = Payment::create([
                'booking_id'    => $booking->id,
                'amount'        => $totalAmount,
                'currency'      => 'SAR',
                'status'        => 'completed',
                'payment_method' => 'tap',
                'transaction_id' => $tapId,
                'metadata'      => json_encode([
                    'tap_response' => $charge,
                    'card_brand'   => $charge['card']['brand'] ?? null,
                    'card_last4'   => $charge['card']['last4'] ?? null,
                    'package_name' => $package->name,
                    'booking_date' => $validated['date'],
                    'selected_slot' => $validated['selected_slot']
                ])
            ]);

            if ($couponId) {
                Coupon::where('id', $couponId)
                    ->lockForUpdate()
                    ->increment('used_count');
            }

            DB::commit();

            session()->forget('pending_booking_data');

            try {
                Mail::to($booking->customer_email)->send(new BookingNotification($booking, 'customer'));

                Mail::to(env('OWNER_EMAIL'))->send(new BookingNotification($booking, 'owner'));
            } catch (\Exception $e) {
                Log::error('Failed to send booking emails: ' . $e->getMessage());
            }

            Log::info('Payment completed successfully', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'tap_id'     => $tapId
            ]);

            return view('tap.success', [
                'transactionId' => $tapId,
                'packageName'   => $package->name,
                'bookingDate'   => $booking->booking_start_time
                    ? date('Y-m-d h:i A', strtotime($booking->booking_start_time))
                    : null,
                'numPeople'     => $booking->people_count,
                'totalAmount'   => number_format($booking->total_amount, 2),
                'customerEmail' => $booking->customer_email,
                'bookingId'     => $booking->id,
                'paymentMethod' => 'Tap (via Card)',
                'cardBrand'     => $charge['card']['brand'] ?? 'Card',
                'cardLast4'     => $charge['card']['last4'] ?? '****'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Tap verification failed: ' . $e->getMessage(), [
                'tap_id' => $tapId
            ]);

            session()->forget('pending_booking_data');

            return view('tap.failed', [
                'error' => 'Unable to verify payment: ' . $e->getMessage(),
                'tapId' => $tapId
            ]);
        }
    }
    // public function handleRedirect(Request $request)
    // {
    //     $tapId = $request->query('tap_id');

    //     if (!$tapId) {
    //         return view('tap.failed', [
    //             'error' => 'Invalid payment response',
    //             'tapId' => null
    //         ]);
    //     }

    //     $pendingData = session('pending_booking_data');

    //     if (!$pendingData) {
    //         Log::warning('No pending booking data in session for tap_id: ' . $tapId);
    //         return view('tap.failed', [
    //             'error' => 'Booking session expired',
    //             'tapId' => $tapId
    //         ]);
    //     }

    //     try {
    //         $charge = $this->verifyTapCharge($tapId);
    //         if ($charge['status'] === 'CAPTURED') {
    //             // Create Booking record
    //             $validated = $pendingData['validated'];
    //             $package   = Package::findOrFail($validated['package_id']);

    //             $originalAmount = $pendingData['original_amount'] ?? $pendingData['totalAmount'];
    //             $discountAmount = $pendingData['discount_amount'] ?? 0;
    //             $totalAmount    = $pendingData['total_amount'] ?? $pendingData['totalAmount'];
    //             $couponId       = $pendingData['coupon_id'] ?? null;
    //             $couponCode     = $pendingData['coupon_code'] ?? null;

    //             $booking = Booking::create([
    //                 'user_id'            => auth()->id() ?? null,
    //                 'package_id'         => $validated['package_id'],
    //                 'booking_start_time' => $validated['selected_slot'],
    //                 'duration_minutes'   => $package->duration_minutes ?? 60,
    //                 'customer_name'      => $validated['customer_name'],
    //                 'customer_phone'     => $validated['customer_phone'],
    //                 'customer_email'     => $validated['customer_email'],
    //                 'people_count'       => $validated['num_people'],
    //                 'original_amount'    => $originalAmount,
    //                 'discount_amount'    => $discountAmount,
    //                 'total_amount'       => $totalAmount,
    //                 'coupon_id'          => $couponId,
    //                 'coupon_code'        => $couponCode,
    //                 'status'             => 'paid',
    //                 'payment_id'         => $tapId
    //             ]);

    //             if ($couponId) {
    //                 Coupon::where('id', $couponId)->increment('used_count');
    //             }

    //             // Create Payment record
    //             $payment = Payment::create([
    //                 'booking_id' => $booking->id,
    //                 'amount'     => $totalAmount,
    //                 'currency'   => 'SAR',
    //                 'status'     => 'completed',
    //                 'payment_method' => 'tap',
    //                 'transaction_id' => $tapId,
    //                 'metadata' => json_encode([
    //                     'tap_response' => $charge,
    //                     'card_brand'   => $charge['card']['brand'] ?? null,
    //                     'card_last4'   => $charge['card']['last4'] ?? null,
    //                     'package_name' => $package->name,
    //                     'booking_date' => $validated['date'],
    //                     'selected_slot' => $validated['selected_slot']
    //                 ])
    //             ]);

    //             // Clear session data
    //             session()->forget('pending_booking_data');

    //             // Send emails
    //             try {
    //                 Mail::to($booking->customer_email)
    //                     ->send(new BookingNotification($booking, 'customer'));
    //                 Mail::to(env('OWNER_EMAIL'))
    //                     ->send(new BookingNotification($booking, 'owner'));
    //             } catch (\Exception $e) {
    //                 Log::error('Failed to send booking emails: ' . $e->getMessage());
    //             }

    //             Log::info('Payment completed successfully', [
    //                 'booking_id' => $booking->id,
    //                 'payment_id' => $payment->id,
    //                 'tap_id' => $tapId
    //             ]);

    //             return view('tap.success', [
    //                 'transactionId' => $tapId,
    //                 'packageName'   => $package->name,
    //                 'bookingDate'   => $booking->booking_start_time ? date('Y-m-d h:i A', strtotime($booking->booking_start_time)) : null,
    //                 'numPeople'     => $booking->people_count,
    //                 'totalAmount'   => number_format($booking->total_amount, 2),
    //                 'customerEmail' => $booking->customer_email,
    //                 'bookingId'     => $booking->id,
    //                 'paymentMethod' => 'Tap (via Card)',
    //                 'cardBrand'     => $charge['card']['brand'] ?? 'Card',
    //                 'cardLast4'     => $charge['card']['last4'] ?? '****'
    //             ]);
    //         } else {
    //             // Payment not captured – clear session and show failure
    //             session()->forget('pending_booking_data');
    //             Log::warning('Payment failed with status: ' . ($charge['status'] ?? 'unknown'), [
    //                 'tap_id' => $tapId
    //             ]);

    //             return view('tap.failed', [
    //                 'error'  => 'Payment ' . strtolower($charge['status'] ?? 'failed'),
    //                 'tapId'  => $tapId,
    //                 'status' => $charge['status'] ?? 'failed'
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Tap verification failed: ' . $e->getMessage(), [
    //             'tap_id' => $tapId
    //         ]);
    //         session()->forget('pending_booking_data');
    //         return view('tap.failed', [
    //             'error' => 'Unable to verify payment: ' . $e->getMessage(),
    //             'tapId' => $tapId
    //         ]);
    //     }
    // }

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

    public function checkCoupon(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code.'
            ]);
        }

        // Usage limit check
        if (
            $coupon->usage_limit !== null &&
            $coupon->used_count >= $coupon->usage_limit
        ) {

            return response()->json([
                'valid' => false,
                'message' => 'Coupon usage limit exceeded.'
            ]);
        }

        return response()->json([
            'valid' => true,
            'coupon' => [
                'type'       => $coupon->discount_type,
                'value'      => $coupon->discount_value,
                'min_amount' => $coupon->min_amount ?? 0,
            ]
        ]);
    }
}
