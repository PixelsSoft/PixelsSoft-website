<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MediaStorage;
use Illuminate\Http\Request;

class ProfileAdminController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.system.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $updates = [
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            MediaStorage::delete($user->avatar);
            $updates['avatar'] = MediaStorage::store($request->file('avatar'));
        }

        if ($request->filled('password')) {
            // User model uses the "hashed" cast — do not Hash::make() here
            $updates['password'] = $data['password'];
        }

        $user->update($updates);

        return back()->with('success', 'Profile updated.');
    }
}
