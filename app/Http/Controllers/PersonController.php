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
    public function index(Request $request)
    {
        $query = Person::with('formation', 'location', 'experience');

        // Filtering
        if ($request->has('document_type')) {
            $query->where('document_type', $request->input('document_type'));
        }

        if ($request->has('document_number')) {
            $query->where('document_number', $request->input('document_number'));
        }

        if ($request->has('first_name')) {
            $query->where('first_name', 'like', '%' . $request->input('first_name') . '%');
        }

        if ($request->has('last_name')) {
            $query->where('last_name', 'like', '%' . $request->input('last_name') . '%');
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }

        if ($request->has('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        if ($request->has('nationality')) {
            $query->where('nationality', 'like', '%' . $request->input('nationality') . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Age range filtering
        if ($request->has('min_age')) {
            $query->where('age', '>=', $request->input('min_age'));
        }

        if ($request->has('max_age')) {
            $query->where('age', '<=', $request->input('max_age'));
        }

        // Ordering
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        // Pagination - si no se especifica per_page, devolver todos
        $perPage = $request->input('per_page');
        
        if ($perPage) {
            return $query->paginate($perPage);
        } else {
            // Para el admin frontend, devolver todos los registros
            return response()->json($query->get());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:DNI,CE,CC',
            'document_number' => [
                'required',
                'string',
                'unique:people,document_number',
                function ($attribute, $value, $fail) {
                    $type = request('document_type');
                    if ($type === 'DNI' && !preg_match('/^\d{8}$/', $value)) {
                        $fail('El DNI debe tener exactamente 8 dígitos.');
                    }
                    if ($type === 'CE' && (strlen($value) < 6 || strlen($value) > 20)) {
                        $fail('El Carnet de Extranjería debe tener entre 6 y 20 caracteres.');
                    }
                    if ($type === 'CC' && (!is_numeric($value) || strlen($value) < 5 || strlen($value) > 15)) {
                        $fail('La CC debe tener entre 5 y 15 dígitos numéricos.');
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
        
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:50',
            'full_name' => 'sometimes|string|max:100',
            'document_type' => 'sometimes|string|in:DNI,CE',
            'document_number' => 'sometimes|string|max:20',
            'phone_number' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:100|unique:people,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string|in:admin,user',
            'area' => 'sometimes|string|max:100',
            'group' => 'sometimes|string|max:100',
            'user_status' => 'sometimes|string|in:ACTIVO,INACTIVO,PENDIENTE,RETIRADO,BL',
        ]);

        // Si se envían first_name y last_name, generar full_name
        if (isset($validated['first_name']) && isset($validated['last_name'])) {
            $validated['full_name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);
        }

        // Hash password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $person->update($validated);

        return response()->json($person->fresh());
    }

    public function destroy($id)
    {
        $person = Person::findOrFail($id);
        $person->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function registered(Request $request): \Illuminate\Http\JsonResponse
    {
        // Log detallado para debugging
        \Log::info('POST /postulant - Todos los datos recibidos:', $request->all());
        \Log::info('POST /postulant - Archivos recibidos:', array_keys($request->allFiles()));
        
        // Log específico de campos con notación de puntos
        \Log::info('POST /postulant - Campo formation.academic_degree:', ['value' => $request->input('formation.academic_degree')]);
        \Log::info('POST /postulant - Campo location.country:', ['value' => $request->input('location.country')]);
        \Log::info('POST /postulant - Campo experience.experience_time:', ['value' => $request->input('experience.experience_time')]);
        
        // Verificar si existen los campos específicos
        \Log::info('POST /postulant - Campo area existe:', ['exists' => $request->has('area'), 'value' => $request->get('area')]);
        \Log::info('POST /postulant - Campo group existe:', ['exists' => $request->has('group'), 'value' => $request->get('group')]);
        
        // Validar directamente los campos tal como llegan del frontend
        try {
            $request->validate([
            'formation.academic_degree' => 'required|string|max:50',
            'formation.career' => 'required|string|max:100',
            'formation.formation_center' => 'required|string|max:100',
            'location.country' => 'required|string|max:100',
            'location.region' => 'required|string|max:100',
            'location.province' => 'required|string|max:100',
            'location.district' => 'required|string|max:100',
            'location.address' => 'required|string|max:255',
            'experience.experience_time' => 'required|string|max:100',
            'experience.other_volunteer_work' => 'required|string|max:255',
            'document_type' => 'required|in:DNI,CE,CC',
            'document_number' => [
                'required',
                'string',
                'unique:people,document_number',
                function ($attribute, $value, $fail) {
                    $type = request('document_type');
                    if ($type === 'DNI' && !preg_match('/^\d{8}$/', $value)) {
                        $fail('El DNI debe tener exactamente 8 dígitos.');
                    }
                    if ($type === 'CE' && (strlen($value) < 6 || strlen($value) > 20)) {
                        $fail('El Carnet de Extranjería debe tener entre 6 y 20 caracteres.');
                    }
                    if ($type === 'CC' && (!is_numeric($value) || strlen($value) < 5 || strlen($value) > 15)) {
                        $fail('La CC debe tener entre 5 y 15 dígitos numéricos.');
                    }
                },
            ],
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'nationality' => 'required|string|max:30',
            'date_of_birth' => 'required|date',
            'phone_number' => 'required|string|max:15',
            'email' => 'required|email|max:100|unique:people',
            'gender' => 'required|string|max:20',
            'family_phone_number' => 'nullable|string|max:15',
            'linkedin' => 'nullable|string|max:70',
            'cv_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'pi_file' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'pf_file' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'dni_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'cul_file' => 'required|file|mimes:pdf|max:10240',

        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Construir objetos para la base de datos después de la validación exitosa
        $formationData = [
            'academic_degree' => $request->input('formation.academic_degree'),
            'career' => $request->input('formation.career'), 
            'formation_center' => $request->input('formation.formation_center')
        ];
        
        $locationData = [
            'country' => $request->input('location.country'),
            'region' => $request->input('location.region'),
            'province' => $request->input('location.province'),
            'district' => $request->input('location.district'),
            'address' => $request->input('location.address')
        ];
        
        $experienceData = [
            'experience_time' => $request->input('experience.experience_time'),
            'other_volunteer_work' => $request->input('experience.other_volunteer_work')
        ];

        //Save formation
        $formation = AcademicFormation::create($formationData);
        //Save location
        $location = Location::create($locationData);
        //Save experience
        $experience = Experience::create($experienceData);

        $personData = $request->except([
            'formation.academic_degree', 'formation.career', 'formation.formation_center',
            'location.country', 'location.region', 'location.province', 'location.district', 'location.address',
            'experience.experience_time', 'experience.other_volunteer_work',
            'cv_file', 'pi_file', 'pf_file', 'dni_file', 'cul_file'
        ]);

        // Calculate age from date_of_birth
        if ($personData['date_of_birth']) {
            $birthDate = new \DateTime($personData['date_of_birth']);
            $today = new \DateTime('today');
            $personData['age'] = $birthDate->diff($today)->y;
        }

        $personData['formation_id'] = $formation->id;
        $personData['location_id'] = $location->id;
        $personData['experience_id'] = $experience->id;
        $personData['role'] = 'user';
        $personData['user_status'] = 'PENDIENTE'; // Estado inicial para nuevos registros
        
        // Ensure area and group are included
        $personData['area'] = $request->get('area', 'Sin área');
        $personData['group'] = $request->get('group', 'Sin grupo');

        \Log::info('POST /postulant - Person data antes de crear:', ['data' => $personData]);

        try {
            $person = Person::create($personData);
            \Log::info('POST /postulant - Persona creada exitosamente:', ['id' => $person->id]);
        } catch (\Exception $e) {
            \Log::error('POST /postulant - Error al crear persona:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Error al crear persona',
                'message' => $e->getMessage()
            ], 500);
        }

        // Handle file uploads with error handling
        try {
            // Handle CV file
            $cvFile = $request->file('cv_file');
            $cvFilename = 'cv_' . Str::uuid() . '.' . $cvFile->getClientOriginalExtension();
            $cvPath = $cvFile->storeAs("privates/{$person->id}", $cvFilename);
            $person->cv_path = $cvPath;

            // Handle informal photo
            $piFile = $request->file('pi_file');
            $piFilename = 'photo_informal_' . Str::uuid() . '.' . $piFile->getClientOriginalExtension();
            $piPath = $piFile->storeAs("privates/{$person->id}", $piFilename);
            $person->photo_informal_path = $piPath;

            // Handle formal photo
            $pfFile = $request->file('pf_file');
            $pfFilename = 'photo_formal_' . Str::uuid() . '.' . $pfFile->getClientOriginalExtension();
            $pfPath = $pfFile->storeAs("privates/{$person->id}", $pfFilename);
            $person->photo_formal_path = $pfPath;

            // Handle DNI file
            $dniFile = $request->file('dni_file');
            $dniFilename = 'dni_' . Str::uuid() . '.' . $dniFile->getClientOriginalExtension();
            $dniPath = $dniFile->storeAs("privates/{$person->id}", $dniFilename);
            $person->dni_scan_path = $dniPath;

            // Handle CUL file
            $culFile = $request->file('cul_file');
            $culFilename = 'cul_' . Str::uuid() . '.' . $culFile->getClientOriginalExtension();
            $culPath = $culFile->storeAs("privates/{$person->id}", $culFilename);
            $person->lab_cert_path = $culPath;
            
            \Log::info('POST /postulant - Archivos procesados correctamente');
        } catch (\Exception $e) {
            \Log::error('POST /postulant - Error procesando archivos:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Error procesando archivos',
                'message' => $e->getMessage()
            ], 500);
        }

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

    public function createAdmin(Request $request)
    {
        // Verificar si ya existe un admin
        $existingAdmin = Person::where('role', 'admin')->first();
        if ($existingAdmin) {
            return response()->json([
                'message' => 'Ya existe un usuario administrador',
                'admin_email' => $existingAdmin->email
            ], 409);
        }

        // Crear nuevo usuario admin
        $admin = Person::create([
            'document_type' => 'DNI',
            'document_number' => '99999999',
            'first_name' => 'Admin',
            'last_name' => 'Sistema',
            'full_name' => 'Admin Sistema',
            'email' => $request->email ?? 'admin@test.com',
            'password' => Hash::make($request->password ?? 'admin123'),
            'role' => 'admin',
            'gender' => 'Otro',
            'phone_number' => '999999999',
            'age' => 30,
            'nationality' => 'Peruana',
            'status' => PostulantStatus::ACCEPTED,
            'user_status' => 'ACTIVO'
        ]);

        return response()->json([
            'message' => 'Usuario administrador creado exitosamente',
            'email' => $admin->email,
            'password' => $request->password ?? 'admin123'
        ], 201);
    }
}
