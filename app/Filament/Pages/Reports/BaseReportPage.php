<?php

namespace App\Filament\Pages\Reports;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

abstract class BaseReportPage extends Page
{
    use HasPageShield;
    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    public ?string $from = null;

    public ?string $to = null;

    protected function from(): ?Carbon
    {
        return filled($this->from) ? Carbon::parse($this->from) : null;
    }

    protected function to(): ?Carbon
    {
        return filled($this->to) ? Carbon::parse($this->to) : null;
    }

    public function clearFilters(): void
    {
        $this->from = null;
        $this->to = null;
    }
}
