<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Package, Module, Role};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    protected $module_id = 4;
    protected $module;

    public function __construct()
    {
        $this->module = Module::find($this->module_id);
    }

    public function index(Request $request)
    {
        $this->authorize('view', $this->module);
    
        $title = 'Packages';
        $roles = Role::all();
        $module = $this->module;
        $packages = Package::orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.packages.index', compact('packages', 'roles', 'title', 'module'));
    }

    public function create()
    {
        #
    }

    public function store(Request $request)
    {
        $this->authorize('create', $this->module);

        $request->validate([
            'name' => 'required|max:255|unique:packages,name',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'min_bookings' => 'required|integer|min:1|max:50',
            'max_bookings' => 'required|integer|min:1|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|max:1000',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('packages', 'public');
        }

        Package::create($data);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully!');
    }


    public function show(Package $package)
    {
        #
    }

    public function edit(Package $package)
    {
        #
    }

    public function update(Request $request, Package $package)
    {
        $this->authorize('update', $this->module);

        $request->validate([
            'name' => 'required|max:255|unique:packages,name,' . $package->id,
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'min_bookings' => 'required|integer|min:1|max:50',
            'max_bookings' => 'required|integer|min:1|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|max:1000',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->except('image');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($package->image && Storage::exists('public/' . $package->image)) {
                Storage::delete('public/' . $package->image);
            }

            $data['image'] = $request->file('image')->store('packages', 'public');
        }

        $package->update($data);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully!');
    }


    public function destroy(Package $package)
    {
        $this->authorize('delete', $this->module);
        
        $package->delete();
        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted successfully!');
    }
}
