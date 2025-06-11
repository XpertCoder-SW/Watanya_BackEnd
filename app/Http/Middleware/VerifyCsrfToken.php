<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'api/*', // تعطيل CSRF لجميع الـ routes التي تبدأ بـ api/
        'docs',  // تعطيل CSRF لـ Swagger UI
    ];
}
