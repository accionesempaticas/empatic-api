<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LimitRegistrationsByIP
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $cacheKey = 'registration_count_' . $ip;
        $maxRegistrations = 30;
        $ttl = 60 * 60 * 24; // 24 horas en segundos

        // Obtener contador actual de la IP
        $currentCount = Cache::get($cacheKey, 0);

        // Si ya alcanzó el límite, rechazar
        if ($currentCount >= $maxRegistrations) {
            return response()->json([
                'message' => 'Has alcanzado el límite máximo de 10 registros desde esta dirección IP en las últimas 24 horas.',
                'error' => 'IP_REGISTRATION_LIMIT_EXCEEDED'
            ], 429); // Too Many Requests
        }

        // Procesar la solicitud
        $response = $next($request);

        // Si la respuesta fue exitosa (registro creado), incrementar contador
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            Cache::put($cacheKey, $currentCount + 1, $ttl);
        }

        return $response;
    }
}