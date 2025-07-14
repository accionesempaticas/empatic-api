<?php

namespace App\Http\Controllers;

use App\Enums\PostulantStatus;
use App\Models\AcademicFormation;
use App\Models\Experience;
use App\Models\Location;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PersonController extends Controller
{
    public function index()
    {
        return Person::with('formation', 'location', 'experience')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:DNI,CE',
            'document_number' => [
                'required',
                'string',
                'unique:people,document_number',
                function ($attribute, $value, $fail) {
                    $type = request('document_type');
                    if ($type === 'DNI' && !preg_match('/^\d{8}$/', $value)) {
                        $fail('El DNI debe tener exactamente 8 dígitos.');
                    }
                    if ($type === 'CE' && (strlen($value) < 9 || strlen($value) > 20)) {
                        $fail('El Carnet de Extranjería debe tener entre 9 y 20 caracteres.');
                    }
                },
            ],
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
        return Person::findOrFail($id)->load('formation', 'location', 'experience');
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

    public function registered(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'formation.academic_degree' => 'required|string|max:50',
            'formation.career' => 'nullable|string|max:100',
            'formation.formation_center' => 'nullable|string|max:100',
            'location.region' => 'nullable|string|max:100',
            'location.province' => 'nullable|string|max:100',
            'location.address' => 'nullable|string|max:255',
            'experience.experience_time' => 'nullable|string|max:100',
            'experience.other_volunteer_work' => 'nullable|string|max:255',
            'document_type' => 'required|in:DNI,CE',
            'document_number' => [
                'required',
                'string',
                'unique:people,document_number',
                function ($attribute, $value, $fail) {
                    $type = request('document_type');
                    if ($type === 'DNI' && !preg_match('/^\d{8}$/', $value)) {
                        $fail('El DNI debe tener exactamente 8 dígitos.');
                    }
                    if ($type === 'CE' && (strlen($value) < 9 || strlen($value) > 20)) {
                        $fail('El Carnet de Extranjería debe tener entre 9 y 20 caracteres.');
                    }
                },
            ],
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100|unique:people',
            'gender' => 'nullable|string|max:20',
            'age' => 'nullable|integer',
            'family_phone_number' => 'nullable|string|max:15',
            'linkedin' => 'nullable|string|max:70',
            'cv_file' => 'required|mimes:pdf|max:10240',
        ]);

        //Save formation
        $formation = AcademicFormation::create($request->input('formation'));
        //Save location
        $location = Location::create($request->input('location'));
        //Save experience
        $experience = Experience::create($request->input('experience'));

        $personData = $request->except(['formation', 'location', 'experience', 'cv_file']);

        $personData['formation_id'] = $formation->id;
        $personData['location_id'] = $location->id;
        $personData['experience_id'] = $experience->id;
        $personData['role'] = 'user';
        $personData['status'] = PostulantStatus::REGISTERED;

        $person = Person::create($personData);

        $file = $request->file('cv_file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("privates/{$person->id}", $filename);

        $person->cv_path = $path;
        $person->save();

        $person->load('formation', 'location', 'experience');

        return response()->json($person, 201);
    }

    public function interviewing($id)
    {
        $person = Person::findOrFail($id);
        $person->status = PostulantStatus::INTERVIEWING;
        $person->save();
        $person->load('formation', 'location', 'experience');

        return response()->json($person);
    }

    public function generatePassword($id)
    {
        $person = Person::findOrFail($id);
        $person->password = Hash::make($person->document_number);
        $person->status = PostulantStatus::DOCS_PENDING;
        $person->save();

        return response()->json(['message' => 'Contraseña generada exitosamente.']);
    }

    public function docsPending(Request $request, $id)
    {
        $request->validate([
            'program_id' => 'nullable|exists:programs,id',
            'dni_scan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'lab_cert' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'commitment_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'photo_informal' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'photo_formal' => 'nullable|image|mimes:jpg,jpeg,png|max:10240'
        ]);

        $authenticatedPerson = $request->user();

        if ($authenticatedPerson->id != $id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $person = $authenticatedPerson;

        $fields = [
            'dni_scan',
            'lab_cert',
            'commitment_letter',
            'photo_informal',
            'photo_formal'
        ];

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs("privates/{$person->id}", $filename);

                $person->{$field . '_path'} = $path;
            }
        }

        $person->status = PostulantStatus::COMPLETED;
        $person->save();

        return response()->json([
            'message' => 'Archivos subidos correctamente',
            'person_id' => $person->id,
        ]);
    }

    public function accepted($id){
        $person = Person::findOrFail($id);
        $person->status = PostulantStatus::ACCEPTED;
        $person->save();

        return response()->json($person);
    }

    public function reject(Request $request, $id)
    {
        $person = Person::findOrFail($id);
        $person->status = PostulantStatus::REJECTED;
        $person->reject_reason = $request->input('reject_reason');
        $person->save();

        return response()->json($person);
    }
}
