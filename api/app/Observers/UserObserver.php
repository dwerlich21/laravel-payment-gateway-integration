<?php

namespace App\Observers;

use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        SendWelcomeEmail::dispatch($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     * Anonimiza dados pessoais para LGPD
     */
    public function deleted(User $user): void
    {
        // Deletar imagem de perfil se existir
        $avatarPath = "users/{$user->id}/perfil.png";
        if (Storage::exists($avatarPath)) {
            Storage::delete($avatarPath);
        }

        // Anonimizar dados pessoais
        $timestamp = Carbon::now()->timestamp;
        $user->updateQuietly([
            'avatar' => null,
            'email'  => "deleted_{$timestamp}_{$user->id}@deleted.local",
            'name'   => "Deleted {$timestamp} {$user->id}",
            'cpf'    => str_pad($user->id, 11, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
