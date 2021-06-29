<?php

namespace App\Http\Middleware;

use Closure;
use Auth ;
class IsAdmin
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
       if (Auth::user() &&  Auth::user()->group == 'Admin') {
    
            return $next($request);
     }

    return redirect('page-not-found');
    }
}
