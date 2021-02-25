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

class TimelineSheet implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    private $group;
    private $data;

    public function __construct(Group $group, $data)
    {
        $this->group = $group;
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            __('export.payer'),
            "   ",
            __('export.amount') . " (" . $this->group->currency . ")",
            __('export.taker'),
            __('export.note'),
            __('export.date')
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
    public function map($row): array
    {
        if ($row->name) { // purchase
            $rows = [];
            foreach ($row->receivers as $receiver) {
                $rows[] = [
                    ($this->group->isMember($row->buyer_id) ? $row->buyer->username : 'N/A'),
                    "🛒",
                    $receiver->amount,
                    ($this->group->isMember($receiver->receiver_id) ? $receiver->user->username : 'N/A'),
                    $row->name,
                    $row->created_at
                ];
            }
            return $rows;
        } else { //payment
            return [
                ($this->group->isMember($row->payer_id) ? $row->payer->username : 'N/A'),
                "💰",
                $row->amount,
                ($this->group->isMember($row->taker_id) ? $row->taker->username : 'N/A'),
                $row->note,
                $row->created_at
            ];
        }
    }

    public function collection()
    {
        return $this->data->sortBy('created_at');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return __('export.timeline');
    }
}
