<?php

namespace Wormhole\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class MonitorRequest
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
        Log::debug( "New Request ".PHP_EOL.
            "header:".json_encode($request->headers->all()) .PHP_EOL.
            "Body :". json_encode($request->all() ));
        if($request->expectsJson()){

            $data = $request->all();

            if(array_key_exists('params',$data)){
                $data = $data['params'];
                $request->replace($data);
            }


        }



        return $next($request);
    }
}
