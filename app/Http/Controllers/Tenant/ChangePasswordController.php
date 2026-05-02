<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChangePasswordController extends Controller
{
    public function show()
    {
        if (! auth()->user()->must_change_password) {
            return redirect()->route('tenant.dashboard');
        }

        return view('tenant.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if ($value === 'password') {
                        $fail('Your new password cannot be the same as the temporary password.');
                    }
                },
            ],
        ]);

        auth()->user()->update([
            'password' => $request->password,
            'must_change_password' => false,
        ]);

        return redirect()->route('tenant.dashboard')
            ->with('success', 'Password changed successfully. Welcome to RentTrack!');
    }
}
