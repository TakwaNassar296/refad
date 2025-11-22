<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class StatisticsResource extends JsonResource
{
    protected $startDate;
    protected $endDate;
    protected $monthType;

    public function __construct($resource, $startDate = null, $endDate = null, $monthType = 'F')
    {
        parent::__construct($resource);
        $this->startDate = $startDate ?? Carbon::now()->subMonths(2)->startOfMonth();
        $this->endDate = $endDate ?? Carbon::now()->endOfMonth();
        $this->monthType = $monthType;
    }

    public function toArray($request): array
    {
        $user = $this->resource;

        $families = $user->families()
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();

        $projects = $user->projects()
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();

        $months = collect();
        $current = $this->startDate->copy()->startOfMonth();

        while ($current <= $this->endDate) {
            $months->push($current->copy());
            $current->addMonth();
        }

        $lastMonths = [];
        foreach ($months as $month) {
            $monthKey = $month->format($this->monthType . ' Y');

            $monthFamilies = $families->filter(fn($f) => $f->created_at->month == $month->month && $f->created_at->year == $month->year);
            $monthProjects = $projects->filter(fn($p) => $p->created_at->month == $month->month && $p->created_at->year == $month->year);

            $deliveredProjects = $monthProjects->where('status', 'delivered');
            $contributionsPercentage = $monthProjects->count() > 0 
                ? round(($deliveredProjects->count() / $monthProjects->count()) * 100)
                : 0;

            $lastMonths[$monthKey] = [
                'familiesCount' => $monthFamilies->count(),
                'projectsCount' => $monthProjects->count(),
                'contributionsPercentage' => $contributionsPercentage,
            ];
        }

        return [
            'total' => [
                'familiesCount' => $user->families()->count(),
                'projectsCount' => $user->projects()->count(),
                'projectsDeliveredCount' => $user->projects()->where('status', 'delivered')->count(),
                'contributionsCount' => $user->contributions()->count(),
            ],
            'lastMonths' => $lastMonths
        ];
    }
}
