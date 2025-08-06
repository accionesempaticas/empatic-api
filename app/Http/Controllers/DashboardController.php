<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Importar el Facade de Log

class DashboardController extends Controller
{
    public function index()
    {
        Log::info('DashboardController@index: Método alcanzado.'); // Añadir log

        $totalPeople = Person::count();
        $totalProgram = Program::count();
        $totalVolunteers = Person::where('role', 'user')->count();
        $donatedHours = 12456; // Logica implementada

        // $recentActivity = Person::latest()->take(5)->get()->map(function ($person) {
        //     return [
        //         'user' => $person->full_name,
        //         'description' => 'se ha registrado en la plataforma.',
        //         'time' => $person->created_at->diffForHumans(),
        //     ];
        // });

        $response = [
            'totalPeople' => $totalPeople,
            'totalProgram' => $totalProgram,
            'totalVolunteers' => $totalVolunteers,
            'donatedHours' => $donatedHours,
            // 'recentActivity' => $recentActivity,
        ];

        Log::info('DashboardController@index: Respuesta preparada.', $response);

        return response()->json($response);
    }
}
