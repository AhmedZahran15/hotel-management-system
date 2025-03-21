<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ManagerReceptionistController extends Controller
{
    public function ban(User $receptionist): RedirectResponse
    {
        //ensure that the manager only ban his/her receptionist
        if (!auth()->user()->hasRole('admin') && $receptionist->creator_user_id !== auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot ban a receptionist that you did not create.');
        }
        if (!$receptionist->isBanned()) {
            $receptionist->ban();
            return redirect()->back()->with('success', 'Receptionist has been banned successfully.');
        }

        return redirect()->back()->with('error', 'Receptionist is already banned.');
    }
    public function unban(User $receptionist): RedirectResponse
    {
        //ensure that the manager only unban his/her receptionist
        if (!auth()->user()->hasRole('admin') && $receptionist->creator_user_id !== auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot unban a receptionist that you did not create.');
        }

        if ($receptionist->isBanned()) {
            $receptionist->unban();
            return redirect()->back()->with('success', 'Receptionist has been unbanned successfully.');
        }

        return redirect()->back()->with('error', 'Receptionist is not banned.');
    }
}
