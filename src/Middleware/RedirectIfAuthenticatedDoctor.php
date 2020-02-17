<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedDoctor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'doctor')
    {

        if (Auth::guard($guard)->check()) {
            
            if(auth()->guard($guard)->user()->hasRole('doctor')){
                return redirect('/doctor/dashboard/usd/');
            }else{
                auth()->guard($guard)->logout();
                return redirect('doctor/')->with('error_message', trans('admin/login.invalid_login_message'));
            }
            
            
        }

        return $next($request);
    }
}
