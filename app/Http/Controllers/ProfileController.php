<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion en libre-service du profil de l'utilisateur connecté
 * (distinct de la gestion admin des utilisateurs).
 */
class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->validated()['password']]);

        // Révoque les autres tokens par sécurité, conserve la session courante.
        $current = $user->currentAccessToken();
        if ($current && $current->getKey()) {
            $user->tokens()->where('id', '!=', $current->getKey())->delete();
        }

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }
}
