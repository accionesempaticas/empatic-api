<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PersonController extends Controller
{
    public function index()
    {
        return Person::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'nullable|string|max:8|unique:people',
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'full_name' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100|unique:people',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer',
            'nationality' => 'nullable|string|max:30',
            'family_phone_number' => 'nullable|string|max:15',
            'linkedin' => 'nullable|string|max:70',
            'location_id' => 'nullable|exists:locations,id',
            'formation_id' => 'nullable|exists:academic_formations,id',
            'experience_id' => 'nullable|exists:experiences,id',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,user',
        ]);

        // Hashear la contraseña antes de crear la persona
        $validated['password'] = Hash::make($validated['password']);

        $person = Person::create($validated);

        return response()->json($person, 201);
    }

    public function show($id)
    {
        return Person::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $person = Person::findOrFail($id);
        $person->update($request->all());

        return response()->json($person);
    }

    public function destroy($id)
    {
        $person = Person::findOrFail($id);
        $person->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
