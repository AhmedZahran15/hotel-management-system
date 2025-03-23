<?php

namespace App\Http\Middleware;

use App\Http\Resources\ClientResource;
use App\Http\Resources\UserResource;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        $formattedProfile = null;
        $permissions = null;
        if ($user) {
            // Load the necessary relationships first
            $user->load('roles:id,name');

            if ($user->user_type == "client" && $user->profile) {
                // Load phones relationship first, then create the resource
                $user->profile->load('phones');
                $formattedProfile = new ClientResource($user->profile);
            } elseif ($user->profile) {
                $formattedProfile = $user->profile;
            }
            $permissions = $user->getAllPermissions()->pluck('name')->map(fn($permission) => strtolower(str($permission)->title()));
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user ? [
                    ...(new UserResource(resource: $user))->resolve(),
                    'profile' => $formattedProfile,
                    'roles' => $user->roles
                        ->pluck('name')
                        ->map(fn($role) => strtolower(str($role)->title())),
                    'avatar' => $user->getAvatarUrl(),
                    'permissions' => $permissions,
                ] : null,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
