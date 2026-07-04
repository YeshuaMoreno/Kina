<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filtro'); // null | 'suspendidos'

        $users = User::query()
            ->with('profile')
            ->when($filter === 'suspendidos', fn ($q) => $q->where('is_suspended', true))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filter' => $filter,
        ]);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('status', 'No puedes suspenderte a ti mismo.');
        }

        $user->update([
            'is_suspended' => true,
            'suspended_at' => now(),
        ]);

        return back()->with('status', "Se suspendió a {$user->name}.");
    }

    public function reactivate(User $user): RedirectResponse
    {
        $user->update([
            'is_suspended' => false,
            'suspended_at' => null,
        ]);

        return back()->with('status', "Se reactivó a {$user->name}.");
    }
}
