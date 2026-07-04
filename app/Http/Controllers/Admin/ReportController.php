<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewReportRequest;
use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $reports = Report::with('reporter', 'reported')
            // Pendientes primero (portable en MySQL y SQLite).
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20);

        return view('admin.reports.index', [
            'reports' => $reports,
            'reasons' => StoreReportRequest::reasons(),
        ]);
    }

    public function show(Report $report): View
    {
        $report->load('reporter.profile', 'reported.profile', 'reviewer');

        return view('admin.reports.show', [
            'report' => $report,
            'reasons' => StoreReportRequest::reasons(),
        ]);
    }

    public function review(ReviewReportRequest $request, Report $report): RedirectResponse
    {
        $report->update([
            'status' => $request->validated()['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        // Opcional: suspender al usuario reportado desde el detalle.
        if ($request->boolean('suspend_reported')
            && $report->reported
            && $report->reported_id !== $request->user()->id
            && ! $report->reported->is_suspended) {
            $report->reported->update(['is_suspended' => true, 'suspended_at' => now()]);

            return redirect()->route('admin.reports.show', $report)
                ->with('status', 'Reporte actualizado y usuario reportado suspendido.');
        }

        return redirect()->route('admin.reports.show', $report)
            ->with('status', 'Reporte actualizado.');
    }
}
