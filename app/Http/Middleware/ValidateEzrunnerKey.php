<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateEzrunnerKey
{
    /** Lindungi webhook mesin produksi dengan secret yang disimpan di .env. */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.ezrunner.key');
        $providedKey = (string) $request->header('X-EZRunner-Key');

        if ($configuredKey === '' || !hash_equals($configuredKey, $providedKey)) {
            abort(401, 'Kunci integrasi EZRunner tidak valid.');
        }

        return $next($request);
    }
}
