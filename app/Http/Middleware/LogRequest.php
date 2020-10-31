<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Log;

use Closure;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Log::info('incoming request', ['request' => $request->all()]);
        return $next($request);
    }
}
