<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $query = Person::query()
            ->with(['location', 'formation', 'experience']);

        // Filtro por DNI
        if ($request->has('dni')) {
            $query->where('dni', 'like', '%' . $request->dni . '%');
        }

        // Filtro por nombre
        if ($request->has('name')) {
            $query->where(function (Builder $q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->name . '%')
                    ->orWhere('full_name', 'like', '%' . $request->name . '%');
            });
        }

        // Filtro por email
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Filtro por género
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filtro por rango de edad
        if ($request->has('min_age')) {
            $query->where('age', '>=', $request->min_age);
        }
        if ($request->has('max_age')) {
            $query->where('age', '<=', $request->max_age);
        }

        // Filtro por nacionalidad
        if ($request->has('nationality')) {
            $query->where('nationality', 'like', '%' . $request->nationality . '%');
        }

        // Ordenamiento
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginación
        $perPage = $request->input('per_page', 10);
        
        return $query->paginate($perPage);
    }

    /**
     * Calcula la edad basada en la fecha de nacimiento
     */
    private function calculateAge($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return null;
        }
        
        return \Carbon\Carbon::parse($dateOfBirth)->age;
    }

    /**
     * Genera el nombre completo basado en nombre y apellido
     */
    private function generateFullName($firstName, $lastName)
    {
        if ($firstName && $lastName) {
            return trim($firstName . ' ' . $lastName);
        }
        return null;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'nullable|string|max:8|unique:people',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'full_name' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'age' => 'nullable|integer|min:0|max:150',
            'nationality' => 'nullable|string|max:30',
            'family_phone_number' => 'nullable|string|max:15',
            'linkedin' => 'nullable|string|max:70|url',
            'location_id' => 'nullable|exists:locations,id',
            'formation_id' => 'nullable|exists:academic_formations,id',
            'experience_id' => 'nullable|exists:experiences,id',
        ]);

        // Calcular edad automáticamente si se proporciona fecha de nacimiento
        if (isset($validated['date_of_birth'])) {
            $validated['age'] = $this->calculateAge($validated['date_of_birth']);
        }

        // Generar nombre completo automáticamente
        $validated['full_name'] = $this->generateFullName(
            $validated['first_name'] ?? null, 
            $validated['last_name'] ?? null
        );

        try {
            DB::beginTransaction();
            $person = Person::create($validated);
            DB::commit();

            return response()->json([
                'message' => 'Persona creada exitosamente',
                'data' => $person->load(['location', 'formation', 'experience'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear la persona',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $person = Person::with(['location', 'formation', 'experience'])
                ->findOrFail($id);

            return response()->json([
                'data' => $person
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Persona no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $person = Person::findOrFail($id);

            $validated = $request->validate([
                'dni' => 'nullable|string|max:8|unique:people,dni,' . $id,
                'first_name' => 'nullable|string|max:50',
                'last_name' => 'nullable|string|max:50',
                'full_name' => 'nullable|string|max:100',
                'gender' => 'nullable|string|max:20',
                'phone_number' => 'nullable|string|max:15',
                'email' => 'nullable|email|max:100',
                'date_of_birth' => 'nullable|date|before:today',
                'age' => 'nullable|integer|min:0|max:150',
                'nationality' => 'nullable|string|max:30',
                'family_phone_number' => 'nullable|string|max:15',
                'linkedin' => 'nullable|string|max:70|url',
                'location_id' => 'nullable|exists:locations,id',
                'formation_id' => 'nullable|exists:academic_formations,id',
                'experience_id' => 'nullable|exists:experiences,id',
            ]);

            // Calcular edad automáticamente si se proporciona fecha de nacimiento
            if (isset($validated['date_of_birth'])) {
                $validated['age'] = $this->calculateAge($validated['date_of_birth']);
            }

            // Generar nombre completo automáticamente si se actualizan nombre o apellido
            if (isset($validated['first_name']) || isset($validated['last_name'])) {
                $firstName = $validated['first_name'] ?? $person->first_name;
                $lastName = $validated['last_name'] ?? $person->last_name;
                $validated['full_name'] = $this->generateFullName($firstName, $lastName);
            }

            DB::beginTransaction();
            $person->update($validated);
            DB::commit();

            return response()->json([
                'message' => 'Persona actualizada exitosamente',
                'data' => $person->load(['location', 'formation', 'experience'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar la persona',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    public function destroy($id)
    {
        try {
            $person = Person::findOrFail($id);
            
            DB::beginTransaction();
            $person->delete();
            DB::commit();

            return response()->json([
                'message' => 'Persona eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar la persona',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }
}
