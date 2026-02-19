<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Role, RoleModulePermission, Module};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class RoleController extends Controller
{
    protected $module_id = 2;
    protected $module;

    public function __construct()
    {
        $this->module = Module::find($this->module_id);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view', $this->module);
        try {
            $title = 'Roles';
            $roles = Role::all();
            $module = $this->module;
            return view('admin.roles', compact('roles', 'title', 'module'));
        } catch (Exception $e) {
            Log::error("Error fetching roles: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load roles.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', $this->module);
        try {
            // Validate the request data
            $data = $request->validate([
                'title' => 'required|string|max:255'
            ]);

            // Create a new role
            $role = Role::create($data);
            $modules = Module::all();

            foreach ($modules as $module) {
                RoleModulePermission::create([
                    'role_id' => $role->id,
                    'module_id' => $module->id,
                    'can_create' => 0,
                    'can_view' => 0,
                    'can_update' => 0,
                    'can_delete' => 0,
                    'can_view_report' => 0,
                ]);
            }

            return redirect()->back()->with('success', 'Role created successfully!');
        } catch (Exception $e) {
            Log::error("Error creating role: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create role. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('view', $this->module);
        try {
            $role = Role::findOrFail($id);
            return response()->json($role);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Role not found'], 404);
        } catch (Exception $e) {
            Log::error("Error fetching role with ID $id: " . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('update', $this->module);
        try {
            // Validate data
            $validatedData = $request->validate(['title' => 'required|string|max:255']);

            // Find and update the role
            $role = Role::findOrFail($id);
            $role->update($validatedData);

            return redirect()->back()->with('success', 'Role updated successfully.');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Role not found.');
        } catch (Exception $e) {
            Log::error("Error updating role ID $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update role.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete', $this->module);
        try {
            $role = Role::findOrFail($id);

            // Prevent deletion of the Super Admin role
            if ($role->id == 1) {
                return redirect()->back()->with('error', "You can't delete the Super Admin role.");
            }

            $role->delete();

            return redirect()->back()->with('success', 'Role deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Role not found.');
        } catch (Exception $e) {
            Log::error("Error deleting role ID $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete role.');
        }
    }

    /**
     * Show permissions for a specific role.
     */
    public function editPermissions($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $modules = Module::all(); // Fetch all modules for display

        return view('admin.permissions', compact('role', 'modules'));
    }

    /**
     * Update permissions for a role.
     */
    public function updatePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        // dd($request->all());
        // Validate the request
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_update' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
            'permissions.*.can_view_report' => 'boolean',
        ]);

        $modules = Module::all();

        foreach ($modules as $module) {
            $p = RoleModulePermission::where('role_id', $id)->where('module_id', $module->id)->first();
            if (!empty($p)) {
                RoleModulePermission::where('role_id', $id)
                    ->where('module_id', $module->id)
                    ->update([
                        'can_create' => $validated['permissions'][$module->id]['can_create'] ?? 0,
                        'can_view' => $validated['permissions'][$module->id]['can_view'] ?? 0,
                        'can_update' => $validated['permissions'][$module->id]['can_update'] ?? 0,
                        'can_delete' => $validated['permissions'][$module->id]['can_delete'] ?? 0,
                        'can_view_report' => $validated['permissions'][$module->id]['can_view_report'] ?? 0,
                    ]);
            } else {
                RoleModulePermission::create([
                    'role_id' => $id,
                    'module_id' => $module->id,
                    'can_create' => $validated['permissions'][$module->id]['can_create'] ?? 0,
                    'can_view' => $validated['permissions'][$module->id]['can_view'] ?? 0,
                    'can_update' => $validated['permissions'][$module->id]['can_update'] ?? 0,
                    'can_delete' => $validated['permissions'][$module->id]['can_delete'] ?? 0,
                    'can_view_report' => $validated['permissions'][$module->id]['can_view_report'] ?? 0,
                ]);
            }
        }
        // Update permissions
        // foreach ($validated['permissions'] as $key => $perm) {
        //     RoleModulePermission::where('role_id', $id)
        //         ->where('module_id', $key)
        //         ->update([
        //             'can_create' => $perm['can_create'] ?? 0,
        //             'can_view' => $perm['can_view'] ?? 0,
        //             'can_update' => $perm['can_update'] ?? 0,
        //             'can_delete' => $perm['can_delete'] ?? 0,
        //             'can_view_report' => $perm['can_view_report'] ?? 0,
        //         ]);
        // }

        return redirect()->back()->with('success', 'Permissions updated successfully!');
    }
}
