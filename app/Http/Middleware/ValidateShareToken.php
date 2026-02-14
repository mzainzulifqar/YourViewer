<?php

namespace App\Http\Middleware;

use App\Models\SharedReport;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateShareToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');

        $share = SharedReport::valid()->where('token', $token)->first();

        if (! $share) {
            abort(404);
        }

        $request->attributes->set('shared_report', $share);

        return $next($request);
    }
}
