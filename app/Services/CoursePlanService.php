<?php

namespace App\Services;

use App\Models\CoursePlan;
use App\Models\CoursePlanLesson;
use App\Models\Period;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CoursePlanService
{
    /**
     * @return list<string>
     */
    public function getSundaysForPeriod(Period $period): array
    {
        if ($period->start_date && $period->end_date) {
            return $this->getSundaysBetween(
                Carbon::parse($period->start_date)->startOfDay(),
                Carbon::parse($period->end_date)->startOfDay()
            );
        }

        return $this->getSundaysFromLegacyTrimesterMonths($period);
    }

    /**
     * @return list<string>
     */
    private function getSundaysBetween(Carbon $start, Carbon $end): array
    {
        if ($start->gt($end)) {
            return [];
        }

        $cursor = $start->copy();
        while (! $cursor->isSunday()) {
            $cursor->addDay();
            if ($cursor->gt($end)) {
                return [];
            }
        }

        $sundays = [];
        while ($cursor->lte($end)) {
            $sundays[] = $cursor->format('Y-m-d');
            $cursor->addWeek();
        }

        return $sundays;
    }

    /**
     * @return list<string>
     */
    private function getSundaysFromLegacyTrimesterMonths(Period $period): array
    {
        $year = $period->year;
        $currentTrimester = $period->trimester;

        $sundays = [];
        $monthRanges = [
            1 => [1, 2, 3, 4],
            2 => [5, 6, 7, 8],
            3 => [9, 10, 11, 12],
        ];
        $months = $monthRanges[$currentTrimester] ?? [1, 2, 3, 4];

        foreach ($months as $month) {
            $startDate = new \DateTime("$year-$month-01");
            $endDate = new \DateTime("$year-$month-".$startDate->format('t'));
            while ($startDate->format('N') != 7) {
                $startDate->add(new \DateInterval('P1D'));
            }
            while ($startDate <= $endDate) {
                $sundays[] = $startDate->format('Y-m-d');
                $startDate->add(new \DateInterval('P7D'));
            }
        }

        return $sundays;
    }

    public function findOrCreateForSubjectPeriod(Subject $subject, Period $period): CoursePlan
    {
        $existing = CoursePlan::query()
            ->where('subject_id', $subject->id)
            ->where('period_id', $period->id)
            ->with('lessons')
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($subject, $period) {
            $plan = CoursePlan::create([
                'subject_id' => $subject->id,
                'period_id' => $period->id,
                'status' => CoursePlan::STATUS_DRAFT,
            ]);

            $sundays = $this->getSundaysForPeriod($period);

            foreach ($sundays as $index => $date) {
                CoursePlanLesson::create([
                    'course_plan_id' => $plan->id,
                    'class_number' => $index + 1,
                    'class_date' => $date,
                ]);
            }

            return $plan->load('lessons');
        });
    }

    /**
     * @return array<string, string>
     */
    public function gradingSystemLabels(): array
    {
        return [
            'Tareas' => '100%',
            'Examen 1' => '100%',
            'Examen 2' => '100%',
            'Participación' => '100%',
            'Biblia' => '100%',
            'Versículos' => '100%',
            'Otro' => '100%',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function autoFilledData(Subject $subject, Period $period): array
    {
        $professors = $subject->professorsForPeriod($period)->get();

        $duration = $period->name;
        if ($period->start_date && $period->end_date) {
            $duration = $period->start_date->format('d/m/Y').' — '.$period->end_date->format('d/m/Y');
        }

        return [
            'title' => $subject->name,
            'teachers' => $professors->pluck('name')->join(', '),
            'duration' => $duration,
            'grading_system' => $this->gradingSystemLabels(),
        ];
    }
}
