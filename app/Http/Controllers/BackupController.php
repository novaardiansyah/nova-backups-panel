<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function destroy(Request $request, Backup $backup): RedirectResponse
    {
        abort_unless((int) $backup->user_id === (int) $request->user()->id, 403);

        $backup->delete();

        return back()->with('status', 'Backup record deleted.');
    }
}
