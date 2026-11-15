<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountController extends Controller
{
    public function avatar(User $user): StreamedResponse
    {
        abort_unless(
            $user->avatar_path && Storage::disk('local')->exists($user->avatar_path),
            404,
            'Foto profil tidak ditemukan.'
        );

        return Storage::disk('local')->response($user->avatar_path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
