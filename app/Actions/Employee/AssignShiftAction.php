<?php

namespace App\Actions\Employee;

use App\Models\Shift;
use App\Models\Employee;

class AssignShiftAction
{
    /**
     * Execute the action to assign a shift to an employee.
     *
     * @param Employee $employee
     * @param array $shiftData
     * @return Shift
     */
    public function execute(Employee $employee, array $shiftData): Shift
    {
        // TODO: Ensure employee has active status, validate overlapping shifts
        return Shift::create(array_merge($shiftData, [
            'employee_id' => $employee->id,
            'status' => 'scheduled',
        ]));
    }
}
