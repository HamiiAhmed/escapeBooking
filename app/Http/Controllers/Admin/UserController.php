<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Role, Module};
use Exception;

class UserController extends Controller
{
    protected $module_id = 3;
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
            $title = 'Users';
            $users = User::with('role')
                ->where('is_trash', 0)
                ->where('is_admin', 1)
                ->where('id', '!=', Auth::id()) // Exclude the logged-in user
                ->get();
            $roles = Role::all();
            $module = $this->module;
            return view('admin.users', compact('users', 'title', 'roles', 'module'));
        } catch (Exception $e) {
            Log::error("Error fetching users: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load users.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
                'name' => 'required|string',
                'email' => 'required|email',
                'role_id' => 'required|integer',
                'password' => 'required|string',
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            if ($request->hasFile('profile_pic')) {
                $image = $request->file('profile_pic');
                $imageName = time() . '_' . $image->getClientOriginalName();

                // Move file to public/images/users
                $image->move(public_path('images/users'), $imageName);

                // Store relative path in database
                $data['profile_pic'] = $imageName;
            }
            // Hash the password
            $data['password'] = Hash::make($request->password);
            $data['is_admin'] = 1;

            // Create a new user
            User::create($data);

            return redirect()->back()->with('success', 'User created successfully!');
        } catch (Exception $e) {
            Log::error("Error creating User: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create User. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('view', $this->module);
        try {
            $user = User::findOrFail($id);
            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (Exception $e) {
            Log::error("Error fetching user with ID $id: " . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

        /**
     * Display the user profile.
     */
    public function profile()
    {
        try {
            $id = Auth::id();
            $title = 'Update User Profile';
            $user = User::findOrFail($id);
            return view('admin.profile', compact('user', 'title'));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (Exception $e) {
            Log::error("Error fetching user with ID $id: " . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, Request $request) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('update', $this->module);
        try {
            // Validate data
            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'role_id' => 'required|integer',
                'password' => 'nullable|string|confirmed',
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);
            
            // Find the user
            $user = User::findOrFail($id);
            
            // Handle profile picture upload if provided
            if ($request->hasFile('profile_pic')) {
                $image = $request->file('profile_pic');
                $imageName = time() . '_' . $image->getClientOriginalName();
                
                // Move file to public/images/users
                $image->move(public_path('images/users'), $imageName);
                
                // Delete old profile picture if exists
                if ($user->profile_pic) {
                    $oldImagePath = public_path('images/users/' . $user->profile_pic);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                
                // Store new image in the database
                $data['profile_pic'] = $imageName;
            }
            
            // Only update the password if a new one is provided
            if (!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']); // Remove password from data if not updating
            }
            
            // Update the user
            $user->update($data);

            return redirect()->back()->with('success', 'User updated successfully.');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'User not found.');
        } catch (Exception $e) {
            Log::error("Error updating User ID $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update User.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateProfile(Request $request)
    {
        try {
            $id = Auth::id();
            // Validate data
            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'oldPassword' => 'nullable|string',
                'password' => 'nullable|string',
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            // Find the user
            $user = User::findOrFail($id);

            // Handle profile picture upload if provided
            if ($request->hasFile('profile_pic')) {
                $image = $request->file('profile_pic');
                $imageName = time() . '_' . $image->getClientOriginalName();

                // Move file to public/images/users
                $image->move(public_path('images/users'), $imageName);

                // Delete old profile picture if exists
                if ($user->profile_pic) {
                    $oldImagePath = public_path('images/users/' . $user->profile_pic);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Store new image in the database
                $data['profile_pic'] = $imageName;
            }

            // Check if the user provided an old password
            if (!empty($request->oldPassword)) {
                if (!Hash::check($request->oldPassword, $user->password)) {
                    return redirect()->back()->with(['error' => 'The current password is incorrect.']);
                }

                // If old password is correct and new password is provided, hash it
                if (!empty($request->password)) {
                    $data['password'] = Hash::make($request->password);
                }
            } else {
                // Prevent password field from being updated if old password was not provided
                unset($data['password']);
            }

            // Update the user
            $user->update($data);
            Auth::setUser($user->fresh());

            return redirect()->back()->with('success', 'Profile updated successfully.');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Profile not found.');
        } catch (Exception $e) {
            Log::error("Error updating Profile ID $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to Update Profile.');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete', $this->module);
        try {
            // Find and update the user
            $user = User::findOrFail($id);
            $user->is_trash = 1;
            $user->save();

            return redirect()->back()->with('success', 'User Deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'User not found.');
        } catch (Exception $e) {
            Log::error("Error updating User ID $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update User.');
        }
    }
}
