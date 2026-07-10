<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InviteController extends Controller
{
    public function show(string $token)
    {
        $invitation = $this->findValidInvitation($token);

        if (!$invitation) {
            return view('admin.auth.accept-invite', ['invitation' => null, 'token' => $token]);
        }

        return view('admin.auth.accept-invite', compact('invitation', 'token'));
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->findValidInvitation($token);

        if (!$invitation) {
            return back()->with('error', 'This invitation is invalid or has expired.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $invitation->email,
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $user->syncRoles($invitation->roles ?? []);

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect()->route('admin.dashboard')->with('success', 'Welcome! Your account is ready.');
    }

    private function findValidInvitation(string $token): ?UserInvitation
    {
        $invitation = UserInvitation::where('token', $token)->first();

        if (!$invitation || $invitation->isAccepted() || $invitation->isExpired()) {
            return null;
        }

        return $invitation;
    }
}
