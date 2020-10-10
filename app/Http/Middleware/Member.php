<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use App\Group;
use Closure;

class Member
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
        $user = Auth::guard('api')->user();
        $group = ($request->group instanceof Group) ? $request->group : Group::find($request->group);
        if(!$group->members->contains($user)){
            return response()->json(['error' => 1], 400);
        }

        return $next($request);
    }
}
