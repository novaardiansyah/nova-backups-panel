<?php

namespace App\Http\Middleware;

use App\Models\AgentCredential;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAgent
{
  public function handle(Request $request, Closure $next): Response
  {
    $bearer = (string) $request->bearerToken();
    if ($bearer === '') {
      return response()->json(['ok' => false, 'message' => 'Missing agent bearer token.'], 401);
    }

    $tokenHash = hash('sha256', $bearer);

    $credential = AgentCredential::query()
      ->where('token_hash', $tokenHash)
      ->whereNull('revoked_at')
      ->where(function ($query): void {
        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
      })
      ->with('agent')
      ->first();

    if ($credential === null || $credential->agent === null) {
      return response()->json(['ok' => false, 'message' => 'Invalid or revoked agent token.'], 401);
    }

    $request->attributes->set('agent', $credential->agent);

    return $next($request);
  }
}
