<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AdminUserController extends Controller
{
    public function ban(User $receptionist) :RedirectResponse
    {
        if(!$receptionist->isBanned()){
            $receptionist->ban();
            return redirect()->back()->with('success', 'Receptionist has been banned successfully');
        }   
        return redirect()->back()->with('error', 'Receptionist is already banned.');
    }

    public function unban(User $receptionist) :RedirectResponse
    {
        if($receptionist->isBanned()){
            $receptionist->unban();
            return redirect()->back()->with('success', 'Receptionist has been unbanned successfully');
        }   
        return redirect()->back()->with('error', 'Receptionist is not banned.');
    }   
}
