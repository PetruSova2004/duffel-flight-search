<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * @return View
     */
    public function edit(): View
    {
        return view('auth.password');
    }

    /**
     * Other sessions are dropped by the auth.session middleware once the hash changes.
     *
     * @param UpdatePasswordRequest $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(UpdatePasswordRequest $request, #[CurrentUser] User $user): RedirectResponse
    {
        $user->update(['password' => $request->validated('password')]);
        $request->session()->regenerate();

        return redirect()->route('password.edit')->with('status', __('auth.password.updated'));
    }
}
