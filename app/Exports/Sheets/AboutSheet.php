<?php

namespace App\Exports\Sheets;

use App\Group;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AboutSheet implements FromCollection, WithTitle, WithHeadings, WithColumnWidths, WithDrawings, WithCustomStartCell, WithStyles
{
    private $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function drawings()
    {
        $logo = new Drawing();
        $logo->setName('Logo');
        $logo->setDescription('Dodo Logo');
        $logo->setPath(public_path('/logo_color.png'));
        $logo->setHeight(150);
        $logo->setCoordinates('B5');

        return $logo;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setShowGridlines(false);
        return [
            12 => [
                'font' => ['size' => 30, 'name' => 'Roboto', 'color' => ['rgb' => '3b5152'],],
            ],
            13 => ['alignment' => ['horizontal' => 'center']],
            14 => ['alignment' => ['horizontal' => 'center']],
            15 => ['alignment' => ['horizontal' => 'center']],
            16 => ['alignment' => ['horizontal' => 'center']],
            18 => [
                'alignment' => ['horizontal' => 'center'],
                'font'      => ['italic' => true]
            ],
            'A1' => [] //reset selection
        ];
    }

    public function columnWidths(): array
    {
        return [
            "A" => 60,
            "B" => 20,
            "C" => 120
        ];
    }

    public function startCell(): string
    {
        return "B12";
    }

    public function headings(): array
    {
        return [
            "Dodo"
        ];
    }

    public function collection()
    {
        return collect([
            [__('export.thank_you')],
            [__('export.descr', ['group' => $this->group->name])],
            [__('export.n_a_descr')],
            [($this->group->boosted ? "" : __('export.preview'))],
            [""],
            [__('export.support')]
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return __('export.about');
    }
}
