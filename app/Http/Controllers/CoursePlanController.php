<?php

namespace App\Http\Controllers;

use App\Models\CoursePlan;
use App\Models\CoursePlanLesson;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use App\Services\CoursePlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoursePlanController extends Controller
{
    public function __construct(
        private readonly CoursePlanService $coursePlanService
    ) {}

    public function edit(Request $request, Subject $subject): View|RedirectResponse
    {
        $period = $this->resolvePeriod($request);
        $user = $request->user();

        if (! $this->canView($user, $subject, $period)) {
            return redirect()
                ->route('professors.subjects', $user)
                ->with('error', 'No estás asignado a esta materia en el periodo «'.$period->name.'».');
        }

        if (! $this->canEdit($user, $subject, $period)) {
            return redirect()
                ->route('course-plans.show', $subject)
                ->with('error', $period->is_locked
                    ? 'Este periodo está bloqueado. Puedes ver el plan pero no editarlo.'
                    : 'No tienes permiso para editar este plan de curso.');
        }

        $plan = $this->coursePlanService->findOrCreateForSubjectPeriod($subject, $period);
        $auto = $this->coursePlanService->autoFilledData($subject, $period);
        $periods = Period::orderBy('year')->orderBy('trimester')->get();

        return view('course-plans.edit', compact('subject', 'period', 'plan', 'auto'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $period = $this->resolvePeriod($request);
        $user = $request->user();

        if (! $this->canEdit($user, $subject, $period)) {
            return redirect()
                ->route('course-plans.show', $subject)
                ->with('error', 'No puedes guardar cambios en este periodo.');
        }

        $validated = $request->validate([
            'textbook' => 'nullable|string|max:500',
            'cognitive_objectives' => 'nullable|array|max:4',
            'cognitive_objectives.*' => 'nullable|string|max:1000',
            'affective_objectives' => 'nullable|array|max:4',
            'affective_objectives.*' => 'nullable|string|max:1000',
            'psychomotor_objectives' => 'nullable|array|max:4',
            'psychomotor_objectives.*' => 'nullable|string|max:1000',
            'requirements' => 'nullable|array|max:10',
            'requirements.*' => 'nullable|string|max:1000',
            'task_descriptions' => 'nullable|array|max:4',
            'task_descriptions.*' => 'nullable|string|max:1000',
            'general_notes' => 'nullable|array|max:10',
            'general_notes.*' => 'nullable|string|max:1000',
            'complementary_data' => 'nullable|string|max:5000',
            'bibliography' => 'nullable|array|max:3',
            'bibliography.*' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published',
            'lessons' => 'nullable|array',
            'lessons.*.id' => 'required|integer|exists:course_plan_lessons,id',
            'lessons.*.topic' => 'nullable|string|max:500',
            'lessons.*.assignment' => 'nullable|string|max:2000',
            'lessons.*.has_homework' => 'nullable|boolean',
        ]);

        $plan = $this->coursePlanService->findOrCreateForSubjectPeriod($subject, $period);

        $plan->update([
            'textbook' => $validated['textbook'] ?? null,
            'cognitive_objectives' => $this->filterEmptyStrings($validated['cognitive_objectives'] ?? []),
            'affective_objectives' => $this->filterEmptyStrings($validated['affective_objectives'] ?? []),
            'psychomotor_objectives' => $this->filterEmptyStrings($validated['psychomotor_objectives'] ?? []),
            'requirements' => $this->filterEmptyStrings($validated['requirements'] ?? []),
            'task_descriptions' => $this->filterEmptyStrings($validated['task_descriptions'] ?? []),
            'general_notes' => $this->filterEmptyStrings($validated['general_notes'] ?? []),
            'complementary_data' => $validated['complementary_data'] ?? null,
            'bibliography' => $this->filterEmptyStrings($validated['bibliography'] ?? []),
            'status' => $validated['status'],
            'last_edited_by' => $user->id,
        ]);

        foreach ($validated['lessons'] ?? [] as $lessonData) {
            CoursePlanLesson::query()
                ->where('id', $lessonData['id'])
                ->where('course_plan_id', $plan->id)
                ->update([
                    'topic' => $lessonData['topic'] ?? null,
                    'assignment' => $lessonData['assignment'] ?? null,
                    'has_homework' => ! empty($lessonData['has_homework']),
                ]);
        }

        $message = $validated['status'] === CoursePlan::STATUS_PUBLISHED
            ? 'Plan de curso publicado correctamente.'
            : 'Plan de curso guardado como borrador.';

        $redirectRoute = $validated['status'] === CoursePlan::STATUS_PUBLISHED
            ? 'course-plans.show'
            : 'course-plans.edit';

        return redirect()->route($redirectRoute, array_filter([
            'subject' => $subject,
            'period_id' => ($user->is_admin && $request->filled('period_id'))
                ? $request->integer('period_id')
                : null,
        ]))->with('success', $message);
    }

    public function show(Request $request, Subject $subject): View|RedirectResponse
    {
        $period = $this->resolvePeriod($request);
        $user = $request->user();

        if (! $this->canView($user, $subject, $period)) {
            abort(403, 'No tienes permiso para ver este plan de curso.');
        }

        $plan = CoursePlan::query()
            ->where('subject_id', $subject->id)
            ->where('period_id', $period->id)
            ->with('lessons', 'lastEditor')
            ->first();

        if (! $plan && ($user->is_admin || $this->isProfessorForPeriod($user, $subject, $period))) {
            $plan = $this->coursePlanService->findOrCreateForSubjectPeriod($subject, $period);
            $plan->load('lessons', 'lastEditor');
        }

        if (! $plan) {
            return redirect()
                ->back()
                ->with('error', 'Esta materia aún no tiene plan de curso para este periodo.');
        }

        $unpublishedForStudent = ! $user->is_admin
            && ! $this->isProfessorForPeriod($user, $subject, $period)
            && ! $plan->isPublished();

        if ($unpublishedForStudent) {
            return view('course-plans.show', [
                'subject' => $subject,
                'period' => $period,
                'plan' => $plan,
                'auto' => $this->coursePlanService->autoFilledData($subject, $period),
                'canEdit' => false,
                'unpublishedForStudent' => true,
            ]);
        }

        $auto = $this->coursePlanService->autoFilledData($subject, $period);
        $canEdit = $this->canEdit($user, $subject, $period);

        return view('course-plans.show', compact('subject', 'period', 'plan', 'auto', 'canEdit'));
    }

    private function resolvePeriod(Request $request): Period
    {
        $user = $request->user();

        if ($user?->is_admin && $request->filled('period_id')) {
            return Period::findOrFail($request->integer('period_id'));
        }

        return Period::active()->firstOrFail();
    }

    private function isProfessorForPeriod(User $user, Subject $subject, Period $period): bool
    {
        if (! $user->is_profesor) {
            return false;
        }

        return $subject->professorsForPeriod($period)
            ->where('users.id', $user->id)
            ->exists();
    }

    private function canEdit(User $user, Subject $subject, Period $period): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($period->is_locked || ! $user->is_profesor) {
            return false;
        }

        return $this->isProfessorForPeriod($user, $subject, $period);
    }

    private function canView(User $user, Subject $subject, Period $period): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($this->isProfessorForPeriod($user, $subject, $period)) {
            return true;
        }

        return $subject->studentsForPeriod($period)
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * @param  list<string|null>  $items
     * @return list<string>
     */
    private function filterEmptyStrings(array $items): array
    {
        return array_values(array_filter($items, fn ($item) => filled(trim((string) $item))));
    }
}
