<?php

namespace App\Http\Middleware;

use Closure;
use Redirect;
Use Auth;
class AdminMiddleware
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
        {
            if (Auth::user()){
              if ($request->user()->type != 'admin'){
                Auth::logout();
                return Redirect::route('admin')->with('error','Enter Admin Email and Password');
                }
            }
            return $next($request);
        }
    }
}
