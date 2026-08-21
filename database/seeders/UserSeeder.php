<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Default
        User::updateOrCreate(
            ['email' => 'admin@kawai.co.id'],
            [
                'name' => 'Administrator System',
                'username' => 'admin',
                'employee_id' => 'EMP-000',
                'role' => 'admin',
                'department' => 'IT & Systems',
                'password' => Hash::make('admin123'),
                'permissions' => ['*'],
            ]
        );

        $usersMap = [
            'supervisor@kawai.co.id' => 'supervisor',
            'leader@kawai.co.id' => 'leader',
            'staff@kawai.co.id' => 'staff1',
            'bambang@kawai.co.id' => 'bambang',
            'siska@kawai.co.id' => 'siska',
        ];

        foreach ($usersMap as $email => $uname) {
            $u = User::where('email', $email)->first();
            if ($u) {
                $u->update([
                    'username' => $uname,
                    'permissions' => ['step1', 'step2', 'step3', 'step4', 'step5', 'step6', 'categories'],
                ]);
            }
        }
    }
}
