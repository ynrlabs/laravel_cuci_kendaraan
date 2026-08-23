<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach essential security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Anti-Clickjacking protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer policy for privacy and security
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy to restrict unnecessary browser features
        $response->headers->set('Permissions-Policy', 'camera=(self), geolocation=(), microphone=()');

        // Strict-Transport-Security for HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Remove revealing server headers
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
