<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function create(Request $request, User $user): View|RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return redirect()->route('descubrir.index')->with('status', 'No puedes reportarte a ti mismo.');
        }

        return view('reportes.create', [
            'user' => $user,
            'reasons' => StoreReportRequest::reasons(),
        ]);
    }

    public function store(StoreReportRequest $request, User $user): RedirectResponse
    {
        $me = $request->user();

        if ($me->id === $user->id) {
            return redirect()->route('descubrir.index')->with('status', 'No puedes reportarte a ti mismo.');
        }

        Report::create([
            'reporter_id' => $me->id,
            'reported_id' => $user->id,
            'reason' => $request->validated()['reason'],
            'description' => $request->validated()['description'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('descubrir.index')
            ->with('status', 'Gracias por avisarnos. Nuestro equipo revisará el reporte.');
    }
}
