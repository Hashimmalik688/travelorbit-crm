<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block known scanners, crawlers, and vulnerability tools.
 * Adapted from Taurus CRM RestrictToAllowedDevice bot signature list.
 */
class BotDetection
{
    protected array $botSignatures = [
        'masscan','zgrab','nuclei','nmap','sqlmap','nikto','scanner',
        'shodan','censys','internet-measurement','nessus','qualys',
        'python-requests','go-http-client','java/','libwww','wget',
        'curl','httpclient','okhttp','axios/',
    ];

    // Login-specific: stricter - block all non-browser UA on the login route
    protected array $browserPrefixes = [
        'mozilla/', 'opera/', 'chrome/', 'safari/', 'edge/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $ua = strtolower($request->userAgent() ?? '');

        // Empty UA - block
        if (empty($ua)) {
            return response('', 403);
        }

        // Known scanner/bot UA - block with 403 (no body to avoid info leakage)
        foreach ($this->botSignatures as $sig) {
            if (str_contains($ua, $sig)) {
                return response('', 403);
            }
        }

        return $next($request);
    }
}
