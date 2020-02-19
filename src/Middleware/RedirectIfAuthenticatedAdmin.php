<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {

        if (Auth::guard($guard)->check()) {
            
            if(auth()->guard($guard)->user()->hasRole('admin')){
                return redirect('/admin/dashboard');
            }else{
                auth()->guard('admin')->logout();
                return redirect('admin/')->with('error_message', trans('admin/login.invalid_login_message'));
            }
            
            
        }

        return $next($request);
    }
}
