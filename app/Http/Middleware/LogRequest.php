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
        if (config('app.log'))
            Log::channel('requests')->info('request ', ['path' => $request->path(), 'data' => $request->except(['password', 'password_confirmation', 'old_password', 'new_password', 'new_password_confirmation', 'password_reminder'])]);
        $response = $next($request);
        if (config('app.log'))
            Log::channel('requests')->info('response', ['path' => $request->path(), 'data' => $response->getContent()]);
        return $response;
    }
}
