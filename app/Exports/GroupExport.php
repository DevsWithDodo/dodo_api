<?php

namespace App\Exports;

use App\Exports\Sheets\MembersSheet;
use App\Exports\Sheets\PaymentsSheet;
use App\Exports\Sheets\PurchasesSheet;
use App\Exports\Sheets\TimelineSheet;
use App\Exports\Sheets\AboutSheet;
use App\Group;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GroupExport implements WithMultipleSheets
{
    protected $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }


    public function sheets(): array
    {
        if ($this->group->boosted) {
            $payments = $this->group->payments;
            $purchases = $this->group->purchases;
        } else {
            $payments = $this->group->payments()->where('created_at', '>', now()->subDays(30)->setTime(0, 0))->get();
            $purchases = $this->group->purchases()->where('created_at', '>', now()->subDays(30)->setTime(0, 0))->get();
        }

        $sheets = [
            new AboutSheet($this->group),
            new MembersSheet($this->group),
            new PurchasesSheet($this->group, $purchases),
            new PaymentsSheet($this->group, $payments),
            new TimelineSheet($this->group, $payments->concat($purchases))
        ];

        return $sheets;
    }
}
