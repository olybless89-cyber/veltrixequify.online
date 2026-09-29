<?php

namespace XContains\XContains\Draug;

use Closure;

class MD
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
