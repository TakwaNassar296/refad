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
        $camp = $this->resource;

        $families = $camp->families()
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();

        $projects = $camp->projects()
            ->where('is_approved', true)
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

            $totalReceived = $monthProjects->sum(function($p){
                return $p->contributions()->where('status','approved')->sum('total_quantity');
            });
            $totalCollege = $monthProjects->sum('college');
            $contributionsPercentage = $totalCollege > 0 ? round(($totalReceived / $totalCollege) * 100) : 0;

            $lastMonths[$monthKey] = [
                'familiesCount' => $monthFamilies->count(),
                'projectsCount' => $monthProjects->count(),
                'contributionsPercentage' => $contributionsPercentage . '%',
            ];
        }

        $totalProjects = $camp->projects()->where('is_approved', true)->get();
        $totalReceived = $totalProjects->sum(function($p){
            return $p->contributions()->where('status','approved')->sum('total_quantity');
        });
        $totalCollege = $totalProjects->sum('college');
        $totalContributionsPercentage = $totalCollege > 0 ? round(($totalReceived / $totalCollege) * 100) : 0;

        return [
            'total' => [
                'familiesCount' => $camp->families()->count(),
                'projectsCount' => $totalProjects->count(),
                'contributionsPercentage' => $totalContributionsPercentage . '%',
            ],
            'lastMonths' => $lastMonths,
        ];
    }
}
