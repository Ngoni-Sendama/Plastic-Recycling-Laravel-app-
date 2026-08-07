<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\PrinterSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ThermalTestPrintController
{
    public function __invoke(Request $request): View
    {
        $printerName = PrinterSetting::where('user_id', $request->user()->id)
            ->value('printer_name');

        return view('web.qz-tray.test-print', [
            'printerName' => $printerName,
        ]);
    }
}
