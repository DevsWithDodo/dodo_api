<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;

class Owner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $type)
    {
        $user = Auth::guard('api')->user();
        switch ($type) {
            case 'purchase':
                if($request->purchase->buyer->user != $user){
                    return response()->json(["error" => 13], 400);
                }
                break;
            case 'payment':
                if($request->payment->payer != $user){
                    return response()->json(["error" => 14], 400);
                }
                break;
            case 'request':
                if($request->shopping_request->requester != $user){
                    return response()->json(["error" => 15], 400);
                }
                break;
        }
        return $next($request);
    }
}
