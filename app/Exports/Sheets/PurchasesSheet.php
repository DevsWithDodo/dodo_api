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

class PurchasesSheet implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
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
            __('export.name'),
            __('export.amount') . " (" . $this->group->currency . ")",
            __('export.amount'),
            __('export.buyer'),
            __('export.receivers'),
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

    public function map($purchase): array
    {
        $data = [];
        $receivers = [];
        foreach ($purchase->receivers as $receiver) {
            $receivers[] = ($this->group->isMember($receiver->receiver_id) ? $receiver->user->username : 'N/A');
        }
        $data[] = [
            $purchase->name,
            $purchase->amount,
            $purchase->original_amount . " " . $purchase->original_currency,
            ($this->group->isMember($purchase->buyer_id) ? $purchase->buyer->username : 'N/A'),
            implode(", ", $receivers),
            $purchase->created_at
        ];

        return $data;
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
        return __('export.purchases');
    }
}
