<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Coupon, Module};
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $module_id = 8; // Your module ID
    protected $module;

    public function __construct()
    {
        $this->module = Module::find($this->module_id);
    }

    public function index()
    {
        $this->authorize('view', $this->module);
        
        $title = 'Coupons';
        $module = $this->module;
        $coupons = Coupon::latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons', 'title', 'module'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', $this->module);
        
        $request->validate([
            'code' => 'required|string|max:20|unique:coupons,code',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0|max:1000',
            'min_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean'
        ]);

        Coupon::create($request->all());

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully!');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->authorize('update', $this->module);
        
        $request->validate([
            'code' => 'required|string|max:20|unique:coupons,code,' . $coupon->id,
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0|max:1000',
            'min_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean'
        ]);

        $coupon->update($request->all());

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully!');
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorize('delete', $this->module);
        $coupon->delete();
        
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully!');
    }
}
