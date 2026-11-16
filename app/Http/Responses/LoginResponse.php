<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        $user = $request->user();

        // Jika user adalah pasien (role: 'user'), arahkan ke halaman Ambil Antrean
        if ($user && $user->role === 'user') {
            return redirect()->intended(route('queue.index'));
        }

        // Jika administrator, arahkan ke dashboard
        return redirect()->intended(route('dashboard'));
    }
}
