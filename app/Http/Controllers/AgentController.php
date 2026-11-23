<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentCredential;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
  public function store(Request $request): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:150'],
      'hostname' => ['required', 'string', 'max:255'],
    ]);

    $agent = Agent::create([
      'name' => trim($validated['name']),
      'hostname' => trim($validated['hostname']),
      'status' => 'offline',
    ]);

    $plainToken = 'nba_'.bin2hex(random_bytes(32));

    AgentCredential::create([
      'agent_id' => $agent->id,
      'token_hash' => hash('sha256', $plainToken),
      'expires_at' => now()->addYear(),
    ]);

    AuditLog::create([
      'user_id' => $user->id,
      'action' => 'agent.created',
      'subject_type' => 'Agent',
      'subject_id' => $agent->id,
      'ip_address' => $request->ip(),
      'metadata' => ['name' => $agent->name, 'hostname' => $agent->hostname],
    ]);

    return back()
      ->with('status', "Agent {$agent->name} created.")
      ->with('agent_token', $plainToken);
  }

  public function update(Request $request, Agent $agent): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:150'],
      'hostname' => ['required', 'string', 'max:255'],
    ]);

    $agent->update([
      'name' => trim($validated['name']),
      'hostname' => trim($validated['hostname']),
    ]);

    AuditLog::create([
      'user_id' => $user->id,
      'action' => 'agent.updated',
      'subject_type' => 'Agent',
      'subject_id' => $agent->id,
      'ip_address' => $request->ip(),
      'metadata' => ['name' => $agent->name],
    ]);

    return back()->with('status', "Agent {$agent->name} updated.");
  }

  public function rotateToken(Request $request, Agent $agent): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    AgentCredential::where('agent_id', $agent->id)
      ->whereNull('revoked_at')
      ->update(['revoked_at' => now()]);

    $plainToken = 'nba_'.bin2hex(random_bytes(32));

    AgentCredential::create([
      'agent_id' => $agent->id,
      'token_hash' => hash('sha256', $plainToken),
      'expires_at' => now()->addYear(),
    ]);

    AuditLog::create([
      'user_id' => $user->id,
      'action' => 'agent.token_rotated',
      'subject_type' => 'Agent',
      'subject_id' => $agent->id,
      'ip_address' => $request->ip(),
    ]);

    return back()
      ->with('status', "New API token generated for {$agent->name}.")
      ->with('agent_token', $plainToken);
  }

  public function destroy(Request $request, Agent $agent): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    if ($agent->backupProfiles()->exists() || $agent->backupRuns()->exists()) {
      return back()->withErrors(['agent' => 'Cannot delete an agent that is linked to backup profiles or runs.']);
    }

    $name = $agent->name;
    $id = $agent->id;
    $agent->delete();

    AuditLog::create([
      'user_id' => $user->id,
      'action' => 'agent.deleted',
      'subject_type' => 'Agent',
      'subject_id' => $id,
      'ip_address' => $request->ip(),
      'metadata' => ['name' => $name],
    ]);

    return back()->with('status', "Agent {$name} deleted.");
  }
}
