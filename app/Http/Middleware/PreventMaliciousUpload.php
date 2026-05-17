<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventMaliciousUpload
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            
            // Check for double extensions like file.php.jpg
            if (preg_match('/\.[^.]+\./', $filename)) {
                abort(400, 'Invalid filename format.');
            }
            
            // Scan for malicious strings
            $malicious = config('security.uploads.malicious_extensions', ['php', 'phtml', 'exe', 'sh', 'bat']);
            $extension = strtolower($file->getClientOriginalExtension());
            if (in_array($extension, $malicious)) {
                abort(400, 'Malicious file type detected.');
            }
        }
        return $next($request);
    }
}
