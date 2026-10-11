<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
use App\Support\Shift;
use Carbon\Carbon;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = Auth::user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nip' => $user->nip ?: '-',
                    'role' => $user->role,
                    'is_active' => (bool) $user->is_active,
                ] : null,
                'permissions' => $user ? $user->getPermissionSlugs() : [],
            ],
            // Shift berjalan untuk Header (dihitung ulang di browser tiap pergantian jam)
            'currentShift' => fn () => $user ? Shift::current() : null,
            // Tanggal hari ini untuk Header sebelum jam di browser siap, mis. "Senin, 12 Okt 2026"
            'today' => fn () => $user ? Carbon::today()->locale('id')->translatedFormat('l, j M Y') : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'isLocal' => app()->isLocal(),
        ]);
    }
}
