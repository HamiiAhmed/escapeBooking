<?php
// app/Http/Controllers/CalendarController.php
namespace App\Http\Controllers;

use App\Models\{Booking, Package, WorkingHour};
use App\Services\BookingSlotGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $packages = Package::where('is_active', true)->orderBy('seq_no')->get();
        return view('calendar.iframe', compact('packages'));
    }

    public function getBookings(Request $request)
    {
        $date = $request->query('date');
        $packageId = $request->query('package_id');
        $package = Package::where('id', $packageId)->where('is_active', true)->first();
        if (!$package) {
            return response()->json([]);
        }

        // Current bookings
        $currentBookings = Booking::whereDate('booking_start_time', $date)
            ->where('package_id', $packageId)
            ->select('id', 'booking_start_time', 'duration_minutes')
            ->with('package')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'start' => $booking->booking_start_time,
                    'duration_minutes' => $booking->duration_minutes,
                ];
            });

        // WORKING HOUR get
        $dateCarbon = Carbon::parse($date);
        $dayType = strtolower($dateCarbon->format('l'));
        $workingHour = WorkingHour::where('day_type', $dayType)->first();

        // SLOTS GENERATE
        $slots = BookingSlotGenerator::generateSlots(
            $date,
            $package->duration_minutes,
            $currentBookings,
            $workingHour
        );

        return response()->json([
            'slots' => $slots,
            'package' => [
                'id' => $package->id,
                'name' => $package->name,
                'min_bookings' => $package->min_bookings,
                'max_bookings' => $package->max_bookings,
                'price' => $package->price
            ]
        ]);
    }

}
