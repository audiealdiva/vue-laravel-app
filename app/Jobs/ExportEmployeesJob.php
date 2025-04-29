<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $fields, public $user) {}

    public function handle()
    {
        $file = Excel::store(new EmployeesExport($this->fields), 'exports/employees.xlsx', 'public');
    }
}
