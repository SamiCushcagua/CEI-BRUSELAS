<?php

namespace App\Http\Controllers;

use App\Models\CoursePlan;
use App\Models\Period;
use App\Models\Subject;
use App\Services\CoursePlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminCoursePlanOverviewController extends Controller
{
    public function __construct(
        private readonly CoursePlanService $coursePlanService
    ) {}

    public function index(Request $request): View
    {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            abort(403);
        }

        $period = $request->filled('period_id')
            ? Period::findOrFail($request->integer('period_id'))
            : (Period::active()->first() ?? Period::orderBy('year')->orderBy('trimester')->firstOrFail());

        $periods = Period::orderBy('year')->orderBy('trimester')->get();

        $subjects = Subject::query()
            ->whereIn('id', function ($q) use ($period) {
                $q->select('subject_id')
                    ->from('subject_professor')
                    ->where('period_id', $period->id);
            })
            ->orderBy('name')
            ->get();

        $plansBySubject = CoursePlan::query()
            ->where('period_id', $period->id)
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->with(['lessons', 'lastEditor'])
            ->get()
            ->keyBy('subject_id');

        $summaries = $subjects->map(function (Subject $subject) use ($period, $plansBySubject) {
            $plan = $plansBySubject->get($subject->id);
            $status = 'empty';

            if ($plan) {
                $status = $plan->adminDisplayStatus();
            }

            return [
                'subject' => $subject,
                'professors' => $subject->professorsForPeriod($period)->pluck('name')->join(', '),
                'status' => $status,
                'plan' => $plan,
            ];
        });

        $selectedSubjectId = $request->query('subject_id');
        if (! $selectedSubjectId) {
            $selectedSubjectId = $subjects->first()?->id;
        }

        $selectedSubject = $selectedSubjectId
            ? $subjects->firstWhere('id', (int) $selectedSubjectId)
            : null;

        $selectedPlan = null;
        $auto = null;
        $selectedStatus = 'empty';

        if ($selectedSubject) {
            $selectedPlan = $plansBySubject->get($selectedSubject->id);
            $auto = $this->coursePlanService->autoFilledData($selectedSubject, $period);

            $summaryEntry = $summaries->first(
                fn (array $row) => (int) $row['subject']->id === (int) $selectedSubject->id
            );
            $selectedStatus = $summaryEntry['status'] ?? 'empty';
        }

        return view('admin.course-plans-overview', compact(
            'period',
            'periods',
            'subjects',
            'summaries',
            'selectedSubject',
            'selectedPlan',
            'auto',
            'selectedStatus',
        ));
    }
}
