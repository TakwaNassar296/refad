<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FamiliesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            __('messages.id'),
            __('messages.family_name'),
            __('messages.father_name'),
            __('messages.national_id'),
            __('messages.phone'),
            __('messages.email'),
            __('messages.total_members'),
            __('messages.elderly_count'),
            __('messages.medical_conditions_count'),
            __('messages.children_count'),
            __('messages.tent_number'),
            __('messages.location'),
            __('messages.camp'),
            __('messages.added_by'),
            __('messages.created_at'),
        ];
    }

    public function map($family): array
    {
        return [
            $family->id,
            $family->family_name,
            $family->father_name,
            $family->national_id,
            $family->phone,
            $family->email,
            $family->total_members,
            $family->elderly_count,
            $family->medical_conditions_count,
            $family->children_count,
            $family->tent_number,
            $family->location,
            $family->camp?->name ?? 'N/A',
            $family->delegate?->name ?? 'N/A',
            $family->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
