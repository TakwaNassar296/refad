<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FamiliesExport implements FromCollection, WithHeadings, WithStyles
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        $families = $this->query
            ->with('members.relationship', 'members.medicalCondition', 'maritalStatus', 'camp', 'delegate')
            ->get();

        $rows = collect();

        foreach ($families as $family) {
            foreach ($family->members as $member) {
                $rows->push([
                    $family->id,
                    $family->family_name,
                    $family->national_id,
                    $family->phone,
                    $family->backup_phone,
                    optional($family->maritalStatus)->name,
                    $family->total_members,
                    $family->tent_number,
                    $family->location,
                    $family->camp?->name ?? 'N/A',
                    $family->delegate?->name ?? 'N/A',
                    $member->name,
                    $member->gender,
                    $member->dob?->format('Y-m-d'),
                    $member->national_id,
                    optional($member->relationship)->name,
                    optional($member->medicalCondition)->name,
                    $family->created_at->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            __('messages.id'),
            __('messages.family_name'),
            __('messages.national_id'),
            __('messages.phone'),
            __('messages.backup_phone'),
            __('messages.marital_status'),
            __('messages.total_members'),
            __('messages.tent_number'),
            __('messages.location'),
            __('messages.camp'),
            __('messages.delegate'),
            __('messages.member_name'),
            __('messages.member_gender'),
            __('messages.member_dob'),
            __('messages.member_national_id'),
            __('messages.member_relationship'),
            __('messages.member_medical_condition'),
            __('messages.created_at'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
