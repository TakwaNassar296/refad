<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with('camp', 'addedBy');
    }

    public function headings(): array
    {
        return [
            __('messages.id'),
            __('messages.name'),
            __('messages.type'),
            __('messages.beneficiary_count'),
            __('messages.college'),
            __('messages.status'),
            __('messages.is_approved'),
            __('messages.camp'),
            __('messages.added_by'),
            __('messages.created_at'),
        ];
    }

    public function map($project): array
    {
        return [
            $project->id,
            $project->name,
            $project->type,
            $project->beneficiary_count,
            $project->college,
            __('messages.status_' . $project->status),
            $project->is_approved ? __('messages.yes') : __('messages.no'),
            $project->camp ? $project->camp->name : 'N/A',
            $project->addedBy ? $project->addedBy->name : 'N/A',
            $project->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
