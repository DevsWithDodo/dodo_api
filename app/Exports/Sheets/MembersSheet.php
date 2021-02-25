<?php

namespace App\Exports\Sheets;

use App\Group;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MembersSheet implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    private $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function headings(): array
    {
        return [
            __('export.username'),
            __('export.nickname'),
            __('export.balance') . " (" . $this->group->currency . ")",
            __('export.joined'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
            'A1' => [] //reset selection
        ];
    }

    public function map($member): array
    {
        return [
            $member->username,
            $member->member_data->nickname,
            (round($member->member_data->balance, 2) == 0 ? "0" : round($member->member_data->balance, 2)),
            $member->member_data->created_at
        ];
    }

    public function collection()
    {
        return $this->group->members->sortByDesc('member_data.balance');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return __('export.members');
    }
}
