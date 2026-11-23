<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\StorageAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorageAccountController extends Controller
{
  public function update(Request $request, StorageAccount $storageAccount): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:150'],
    ]);

    $storageAccount->update([
      'name' => trim($validated['name']),
    ]);

    AuditLog::create([
      'user_id' => $user->id,
      'action' => 'storage_account.updated',
      'subject_type' => 'StorageAccount',
      'subject_id' => $storageAccount->id,
      'ip_address' => $request->ip(),
      'metadata' => ['name' => $storageAccount->name],
    ]);

    return back()->with('status', "Storage account {$storageAccount->name} updated.");
  }

  public function destroy(Request $request, StorageAccount $storageAccount): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    if ($storageAccount->profileDestinations()->exists() || $storageAccount->runDestinations()->exists() || $storageAccount->uploads()->exists()) {
      return back()->withErrors(['storage' => 'Cannot delete a storage account that is used by backup profiles or runs.']);
    }

    $name = $storageAccount->name;
    $id = $storageAccount->id;
    $storageAccount->delete();

    AuditLog::create([
      'user_id' => $user->id,
      'action' => 'storage_account.deleted',
      'subject_type' => 'StorageAccount',
      'subject_id' => $id,
      'ip_address' => $request->ip(),
      'metadata' => ['name' => $name],
    ]);

    return back()->with('status', "Storage account {$name} removed.");
  }
}
