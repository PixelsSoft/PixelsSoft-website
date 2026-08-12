<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class InvitationAdminController extends Controller
{
    public function index(Request $request)
    {
        $invitations = UserInvitation::with('inviter')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('email', 'like', $q)
                        ->orWhere('name', 'like', $q)
                        ->orWhereHas('inviter', fn ($i) => $i->where('name', 'like', $q));
                });
            })
            ->when($request->input('status') === 'accepted', fn ($query) => $query->whereNotNull('accepted_at'))
            ->when($request->input('status') === 'pending', function ($query) {
                $query->whereNull('accepted_at')->where('expires_at', '>=', now());
            })
            ->when($request->input('status') === 'expired', function ($query) {
                $query->whereNull('accepted_at')->where('expires_at', '<', now());
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->pluck('name');

        return view('admin.system.invitations.index', compact('invitations', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255|unique:users,email|unique:user_invitations,email',
            'name' => 'nullable|string|max:255',
            'roles' => 'required|array|min:1',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $invitation = UserInvitation::create([
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'token' => UserInvitation::generateToken(),
            'roles' => $data['roles'],
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);

        $link = route('invite.accept', $invitation->token);

        return back()->with('success', "Invitation created. Share this link: {$link}");
    }

    public function destroy(UserInvitation $invitation)
    {
        $invitation->delete();

        return back()->with('success', 'Invitation revoked.');
    }
}
