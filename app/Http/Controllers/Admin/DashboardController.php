<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // DB::enableQueryLog();
        // dd(DB::getQueryLog());
        return view('admin.index');
        // return redirect(route('/'));
    }
}
