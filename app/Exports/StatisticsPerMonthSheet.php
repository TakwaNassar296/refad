<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class StatisticsPerMonthSheet implements FromArray, WithTitle
{
    protected $camp;
    protected $startDate;
    protected $endDate;
    protected $monthType;

    public function __construct($camp, $startDate, $endDate, $monthType)
    {
        $this->camp = $camp;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->monthType = $monthType;
    }

    public function array(): array
    {
        $families = $this->camp->families()
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();

        $projects = $this->camp->projects()
            ->where('is_approved', true)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();

        $months = collect();
        $current = $this->startDate->copy()->startOfMonth();
        while ($current <= $this->endDate) {
            $months->push($current->copy());
            $current->addMonth();
        }

        $data = [[
            __('messages.month'),
            __('messages.families_count'),
            __('messages.projects_count'),
            __('messages.contributions_percentage')
        ]];

        foreach ($months as $month) {
            $monthFamilies = $families->filter(fn($f) => $f->created_at->month == $month->month && $f->created_at->year == $month->year);
            $monthProjects = $projects->filter(fn($p) => $p->created_at->month == $month->month && $p->created_at->year == $month->year);

            $totalReceived = $monthProjects->sum('total_received');
            $totalCollege = $monthProjects->sum('college');
            $contributionsPercentage = $totalCollege > 0 ? round(($totalReceived / $totalCollege) * 100) : 0;

            $data[] = [
                $month->format($this->monthType . ' Y'),
                $monthFamilies->count(),
                $monthProjects->count(),
                $contributionsPercentage . '%',
            ];
        }

        return $data;
    }

    public function title(): string
    {
        return __('messages.monthly_stats');
    }
}

