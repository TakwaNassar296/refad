<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class DelegateStatisticsSheet implements FromArray, WithTitle
{
    protected $camp;

    public function __construct($camp)
    {
        $this->camp = $camp;
    }

    public function array(): array
    {
        $projects = $this->camp->projects()->where('is_approved', true)->get();
        $currentProjects = $projects->where('status', '!=', 'delivered');
        $deliveredProjects = $projects->where('status', 'delivered');
        $familiesCount = $this->camp->families()->count();
        $contributionsCount = $projects->sum(fn($p) => $p->contributions()->count());

        return [
            [__('messages.field'), __('messages.value')],
            [__('messages.current_projects'), $currentProjects->count()],
            [__('messages.delivered_projects'), $deliveredProjects->count()],
            [__('messages.families_count'), $familiesCount],
            [__('messages.contributions_count'), $contributionsCount],
        ];
    }

    public function title(): string
    {
        return __('messages.summary');
    }
}
