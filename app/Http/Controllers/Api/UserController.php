<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Person; // Add this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(User::with('person')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,user',
        ]);

        $person = Person::create([
            'full_name' => $request->full_name,
            // Add other person fields if necessary, e.g., 'email' => $request->email
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'person_id' => $person->id,
        ]);

        return response()->json($user->load('person'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('person')->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::with('person')->findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|string|in:admin,user',
        ]);

        // Update Person data
        if ($user->person) {
            $user->person->full_name = $request->full_name;
            $user->person->save();
        } else {
            // Handle case where a user might not have a person record (e.g., old data)
            $person = Person::create([
                'full_name' => $request->full_name,
            ]);
            $user->person_id = $person->id;
        }

        // Update User data
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8',
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json($user->load('person'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::with('person')->findOrFail($id);
        if ($user->person) {
            $user->person->delete();
        }
        $user->delete();

        return response()->json(null, 204);
    }
}
