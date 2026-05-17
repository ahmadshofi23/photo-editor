<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Trusted external domains for images.
     */
    protected array $trustedImageSources = [
        'https://images.unsplash.com',
        'https://source.unsplash.com',
        'https://www.transparenttextures.com',
        'https://picsum.photos',
    ];

    /**
     * Trusted font domains.
     */
    protected array $trustedFontSources = [
        'https://fonts.bunny.net',
        'https://fonts.gstatic.com',
        'https://fonts.googleapis.com',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! method_exists($response, 'header')) {
            return $response;
        }

        $isDev = config('app.debug') && app()->environment('local');

        $csp = $this->buildCsp($isDev);

        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->header('Content-Security-Policy', $csp);

        return $response;
    }

    /**
     * Build the Content-Security-Policy header value.
     * In local development, Vite dev server (127.0.0.1:5173) must be whitelisted.
     */
    protected function buildCsp(bool $isDev): string
    {
        $viteDevServer = $isDev ? 'http://127.0.0.1:5173 ws://127.0.0.1:5173' : '';

        $fontSrc   = implode(' ', $this->trustedFontSources);
        $imgSrc    = implode(' ', $this->trustedImageSources);

        $directives = [
            // Default: only allow same origin
            "default-src 'self'",

            // Scripts: self + inline (Alpine.js) + eval (Vite HMR) + dev server
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$viteDevServer}",

            // Styles: self + inline (Tailwind JIT) + fonts + dev server
            "style-src 'self' 'unsafe-inline' {$fontSrc} {$viteDevServer}",

            // Fonts
            "font-src 'self' {$fontSrc}",

            // Images: self + data URIs + blob (canvas exports) + external CDNs
            // In dev, also allow both localhost variants since storage URLs may differ from request host
            "img-src 'self' data: blob: {$imgSrc}" . ($isDev ? ' http://127.0.0.1:8000 http://localhost:8000 http://localhost' : ''),

            // Connect: self + Vite HMR WebSocket + API calls
            "connect-src 'self' {$viteDevServer}",

            // Objects, embeds, base
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        // Remove empty segments from non-dev builds
        return implode('; ', array_map(
            fn ($d) => trim(preg_replace('/\s+/', ' ', $d)),
            $directives
        ));
    }
}
