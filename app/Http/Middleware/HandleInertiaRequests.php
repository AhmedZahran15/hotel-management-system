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
        $userData = $user ? $this->formatUserData($user) : null;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $userData,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }

    /**
     * Format user data for sharing with the frontend
     */
    private function formatUserData($user): array
    {
        // Load the necessary relationships first
        $user->load('roles:id,name');

        $formattedProfile = $this->formatUserProfile($user);
        $permissions = $user->getAllPermissions()->pluck('name')
            ->map(fn($permission) => strtolower(str($permission)->title()));

        $roles = $user->roles->pluck('name')
            ->map(fn($role) => strtolower(str($role)->title()));

        $avatar = $user->getAvatarUrl();
        $userData = (new UserResource($user))->resolve();

        return [
            ...$userData,
            'profile' => $formattedProfile,
            'roles' => $roles,
            'avatar' => $avatar,
            'permissions' => $permissions,
        ];
    }

    /**
     * Format the user profile based on user type
     */
    private function formatUserProfile($user)
    {
        if (!$user->profile) {
            return null;
        }

        if ($user->user_type == "client") {
            // Load phones relationship first, then create the resource
            $user->profile->load('phones');
            $formattedProfile = (new ClientResource($user->profile))->resolve();
            return $formattedProfile;
        }

        return $user->profile;
    }
}
