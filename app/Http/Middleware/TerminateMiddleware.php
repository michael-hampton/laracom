<?php

namespace App\Http\Middleware;

use Closure;

class TerminateMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        echo "Executing statements of handle method of TerminateMiddleware.";
        return $next($request);
    }

}
