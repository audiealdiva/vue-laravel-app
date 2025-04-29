<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;

class EmployeesExport implements FromCollection
{
    public function __construct(private array $fields) {}

    public function collection()
    {
        return Employee::all($this->fields);
    }
}
