<?php

namespace Wormhole\Http\Middleware;

use Closure;
use Illuminate\Support\MessageBag;

class ReponseFormat
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
        $response = $next($request);

        $data = $response->original;
        $tmp = [];
        if($data instanceof MessageBag){
            $tmp['version']= env("API_VERSION");
            $tmp['status_code'] = 400;
            $tmp['message']=' error messagebag';
            $tmp['data'] = $data;
        }else if(is_array($data)) {
            $tmp = $data;
            $tmp['version'] = env("API_VERSION");
        }else{
            return $response;
        }

        $response->setContent($tmp);


        return $response;
    }
}
