<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use App\Models\{Booking, Package, Payment};

class DashboardController extends Controller
{
    public function index()
    {
        $payments = [
            'total_completed' => Payment::where('status', 'completed')->sum('amount'),
            'total_pending' => Payment::where('status', 'pending')->sum('amount'),
            'total_failed' => Payment::where('status', 'failed')->count(),
            'today_count' => Payment::whereDate('created_at', today())->count(),
        ];
        $bookings = [
            'total' => Booking::count(),
            'today' => Booking::whereDate('created_at', today())->count(),
            'upcoming' => Booking::where('booking_start_time', '>=', today())->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];
        // DB::enableQueryLog();
        // dd(DB::getQueryLog());
        return view('admin.index', compact('payments', 'bookings'));
        // return redirect(route('/'));
    }
}
