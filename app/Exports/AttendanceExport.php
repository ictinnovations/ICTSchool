<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AttendanceExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        //
    }
    protected $input;
    protected $attendance;

    public function __construct($input, $attendance)
    {
        $this->input = $input;
        $this->attendance = $attendance;
    }

    public function view(): View
    {

        return view('app.attendanceExcel', [
            'attendance' => $this->attendance,
            'input' => $this->input,
        ]);
    }
}
