<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'usersTotal' => User::count(),
            'usersSuspended' => User::where('is_suspended', true)->count(),
            'reportsPending' => Report::where('status', 'pending')->count(),
            'reportsReviewed' => Report::where('status', '!=', 'pending')->count(),
        ]);
    }
}
