<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Log;
use Closure;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            // Collect sanitized payload
            $payload = $request->except(['password','password_confirmation','token','api_token','_token']);
            $payloadJson = is_array($payload) && count($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null;

            $user = $request->user();

            ActivityLog::create([
                'user_id' => $user?->id,
                'ip_address' => $request->ip(),
                'method' => $request->method(),
                'path' => $request->path(),
                'request_payload' => $payloadJson,
                'response_summary' => null, // optional: you can extract status or small json
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            // swallow logging errors to avoid breaking responses
            Log::error('Activity log failed: ' . $e->getMessage());
        }

        return $response;
    }
}
