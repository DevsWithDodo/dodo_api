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
        $user = auth('api')->user();
        $group = ($request->group instanceof Group) ? $request->group : Group::findOrFail($request->group);
        if (!$group->members->contains($user)) abort(400, '$$not_member$user$$');

        return $next($request);
    }
}
