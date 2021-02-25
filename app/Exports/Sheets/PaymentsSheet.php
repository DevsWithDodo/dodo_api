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

class PaymentsSheet implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
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
            __('export.taker'),
            __('export.amount') . " (" . $this->group->currency . ")",
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

    public function map($payment): array
    {
        return [
            ($this->group->isMember($payment->payer_id) ? $payment->payer->username : 'N/A'),
            ($this->group->isMember($payment->taker_id) ? $payment->taker->username : 'N/A'),
            $payment->amount,
            $payment->note,
            $payment->created_at
        ];
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
        return __('export.payments');
    }
}
