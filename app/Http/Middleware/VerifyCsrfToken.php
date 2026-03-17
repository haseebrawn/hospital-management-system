<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * We keep the default behaviour for all other routes and
     * only relax CSRF for the web auth endpoints that we are
     * calling via AJAX from Blade.
     *
     * @var array<int, string>
     */
    protected $except = [
        // only exclude true APIs if needed
        // 'api/*',
    ];
}

