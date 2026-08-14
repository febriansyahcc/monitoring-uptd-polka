<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Master Permissions
        $permissionsData = [
            // Monitoring Arus
            ['name' => 'Lihat Monitoring Arus', 'slug' => 'monitoring_arus.view', 'category' => 'Monitoring Arus', 'description' => 'Melihat data pencatatan arus feeder'],
            ['name' => 'Input/Edit Monitoring Arus', 'slug' => 'monitoring_arus.input', 'category' => 'Monitoring Arus', 'description' => 'Menginput dan memperbarui pencatatan arus'],
            
            // Monitoring kWh Produksi
            ['name' => 'Lihat Monitoring kWh', 'slug' => 'monitoring_kwh.view', 'category' => 'Monitoring kWh', 'description' => 'Melihat data produksi kWh'],
            ['name' => 'Input/Edit Monitoring kWh', 'slug' => 'monitoring_kwh.input', 'category' => 'Monitoring kWh', 'description' => 'Menginput dan memperbarui data kWh produksi'],

            // Monitoring Gangguan
            ['name' => 'Lihat Monitoring Gangguan', 'slug' => 'monitoring_gangguan.view', 'category' => 'Monitoring Gangguan', 'description' => 'Melihat riwayat gangguan operasional'],
            ['name' => 'Kelola Monitoring Gangguan', 'slug' => 'monitoring_gangguan.manage', 'category' => 'Monitoring Gangguan', 'description' => 'Menambah, mengedit, dan menghapus gangguan'],

            // Monitoring Stok BBM
            ['name' => 'Lihat Monitoring BBM', 'slug' => 'monitoring_bbm.view', 'category' => 'Monitoring Stok BBM', 'description' => 'Melihat stok dan pemakaian BBM'],
            ['name' => 'Input/Edit Monitoring BBM', 'slug' => 'monitoring_bbm.input', 'category' => 'Monitoring Stok BBM', 'description' => 'Menginput stok dan pemakaian BBM harian'],

            // Verifikasi & Pengelolaan
            ['name' => 'Verifikasi Data Operasional', 'slug' => 'data.verify', 'category' => 'Verifikasi', 'description' => 'Memverifikasi dan menyetujui log data operasional'],
            ['name' => 'Pengelolaan Pengguna (User Management)', 'slug' => 'users.manage', 'category' => 'Sistem Admin', 'description' => 'Mengelola akun pengguna dan izin PBAC'],
        ];

        $permissions = [];
        foreach ($permissionsData as $pData) {
            $permissions[$pData['slug']] = Permission::updateOrCreate(
                ['slug' => $pData['slug']],
                $pData
            );
        }

        $allPermIds = Permission::pluck('id')->toArray();

        // Helper function to assign permissions
        $assignPermissions = function (User $user, array $allowedSlugs) use ($permissions) {
            foreach ($allowedSlugs as $slug) {
                if (isset($permissions[$slug])) {
                    UserPermission::updateOrCreate(
                        ['user_id' => $user->id, 'permission_id' => $permissions[$slug]->id],
                        ['is_granted' => true]
                    );
                }
            }
        };

        // 2. Create Admin Account
        $admin = User::updateOrCreate(
            ['email' => 'admin@pln.co.id'],
            [
                'name' => 'Administrator System',
                'nip' => '9900112233',
                'role' => 'admin',
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $assignPermissions($admin, array_keys($permissions));

        // 3. Create Manager Account
        $manager = User::updateOrCreate(
            ['email' => 'manager@pln.co.id'],
            [
                'name' => 'Manager Operasional',
                'nip' => '8811223344',
                'role' => 'manager',
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $assignPermissions($manager, [
            'monitoring_arus.view',
            'monitoring_kwh.view',
            'monitoring_gangguan.view',
            'monitoring_bbm.view',
            'data.verify',
        ]);

        // 4. Create Team Leader Operasi
        $tlOperasi = User::updateOrCreate(
            ['email' => 'tl.operasi@pln.co.id'],
            [
                'name' => 'TL Operasi Sistem',
                'nip' => '7722334455',
                'role' => 'tl_operasi',
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $assignPermissions($tlOperasi, [
            'monitoring_arus.view',
            'monitoring_arus.input',
            'monitoring_kwh.view',
            'monitoring_kwh.input',
            'monitoring_bbm.view',
            'data.verify',
        ]);

        // 5. Create Team Leader Pemeliharaan
        $tlPemeliharaan = User::updateOrCreate(
            ['email' => 'tl.pemeliharaan@pln.co.id'],
            [
                'name' => 'TL Pemeliharaan & Gangguan',
                'nip' => '6633445566',
                'role' => 'tl_pemeliharaan',
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $assignPermissions($tlPemeliharaan, [
            'monitoring_gangguan.view',
            'monitoring_gangguan.manage',
            'monitoring_arus.view',
            'data.verify',
        ]);

        // 6. Create 8 Operator Accounts
        for ($i = 1; $i <= 8; $i++) {
            $operator = User::updateOrCreate(
                ['email' => "operator{$i}@pln.co.id"],
                [
                    'name' => "Operator Shift {$i}",
                    'nip' => '554433220' . $i,
                    'role' => 'operator',
                    'is_active' => true,
                    'password' => Hash::make('password'),
                ]
            );

            // Operators get input & view permissions for all operational logs
            $assignPermissions($operator, [
                'monitoring_arus.view',
                'monitoring_arus.input',
                'monitoring_kwh.view',
                'monitoring_kwh.input',
                'monitoring_gangguan.view',
                'monitoring_gangguan.manage',
                'monitoring_bbm.view',
                'monitoring_bbm.input',
            ]);
        }
    }
}
