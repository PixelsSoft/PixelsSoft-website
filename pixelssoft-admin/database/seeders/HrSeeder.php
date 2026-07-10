<?php

namespace Database\Seeders;

use App\Models\Hr\Department;
use App\Models\Hr\LeaveType;
use Illuminate\Database\Seeder;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Management', 'Development', 'Design', 'Sales', 'HR & Finance'] as $name) {
            Department::firstOrCreate(['name' => $name]);
        }

        $leaveTypes = [
            ['name' => 'Annual Leave', 'days_per_year' => 20, 'paid' => true],
            ['name' => 'Sick Leave', 'days_per_year' => 10, 'paid' => true],
            ['name' => 'Unpaid Leave', 'days_per_year' => 0, 'paid' => false],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
