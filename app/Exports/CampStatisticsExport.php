<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CampStatisticsExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            new DelegateStatisticsSheet($this->camp),
            new StatisticsPerMonthSheet($this->camp, $this->startDate, $this->endDate, $this->monthType)
        ];
    }
}
