<?php

namespace Database\Seeders;

use App\Models\Accounts\ExpenseCategory;
use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Travel', 'Software', 'Office Supplies', 'Marketing', 'Miscellaneous'] as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name]);
        }
    }
}
