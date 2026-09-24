<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSIONS = [
        ['name' => 'Lihat Monitoring Operasi Engine', 'slug' => 'monitoring_engine.view', 'category' => 'Monitoring Operasi Engine', 'description' => 'Melihat data control panel & engine area'],
        ['name' => 'Input/Edit Monitoring Operasi Engine', 'slug' => 'monitoring_engine.input', 'category' => 'Monitoring Operasi Engine', 'description' => 'Menginput dan memperbarui data control panel & engine area'],
    ];

    // Hak akses default untuk user yang sudah ada (admin selalu punya semua izin)
    private const ROLE_GRANTS = [
        'manager' => ['monitoring_engine.view'],
        'tl_operasi' => ['monitoring_engine.view', 'monitoring_engine.input'],
        'tl_pemeliharaan' => ['monitoring_engine.view'],
        'operator' => ['monitoring_engine.view', 'monitoring_engine.input'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        foreach (self::PERMISSIONS as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                $permission + ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('slug', array_column(self::PERMISSIONS, 'slug'))
            ->pluck('id', 'slug');

        foreach (self::ROLE_GRANTS as $role => $slugs) {
            foreach (DB::table('users')->where('role', $role)->pluck('id') as $userId) {
                foreach ($slugs as $slug) {
                    DB::table('user_permissions')->updateOrInsert(
                        ['user_id' => $userId, 'permission_id' => $permissionIds[$slug]],
                        ['is_granted' => true, 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('slug', array_column(self::PERMISSIONS, 'slug'))->delete();
    }
};
