<?php

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Railway (like most PaaS platforms) terminates TLS at its edge and
     * forwards requests to this container as plain HTTP on the internal
     * $PORT, setting X-Forwarded-Proto: https on the way in. Without
     * trusting that proxy here, Laravel never sees the forwarded scheme,
     * so every url()/asset() call resolves to http:// even though the
     * public site is https:// -- which the browser then silently blocks
     * as mixed content (no CSS/JS/error, they just never load). Trusting
     * all proxies is safe here: this app is only ever reached through
     * Railway's edge, never directly.
     *
     * @var array|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_AWS_ELB;
}
