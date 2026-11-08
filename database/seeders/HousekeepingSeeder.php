<?php

namespace Database\Seeders;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class HousekeepingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $housekeeper = User::where('email', 'housekeeping@homestay.test')->first() ?? User::first();
        $admin = User::where('email', 'admin@homestay.test')->first() ?? User::first();

        // 1. Task for Room 102 (Dirty - checkout clean, high priority)
        $room102 = Room::where('room_number', '102')->first();
        if ($room102) {
            $room102->update([
                'status' => 'dirty',
                'housekeeping_notes' => 'Tamu baru checkout jam 12:00. Sprei & sarung bantal perlu diganti baru.',
            ]);

            HousekeepingTask::firstOrCreate(
                ['room_id' => $room102->id, 'task_type' => 'checkout_clean'],
                [
                    'user_id' => $housekeeper?->id,
                    'created_by' => $admin?->id,
                    'priority' => 'high',
                    'status' => 'pending',
                    'notes' => 'Pembersihan pasca check-out. Ada jadwal check-in sore ini.',
                ]
            );
        }

        // 2. Task for Room 201 (Dirty - cleaning in progress)
        $room201 = Room::where('room_number', '201')->first();
        if ($room201) {
            $room201->update([
                'status' => 'cleaning',
                'housekeeping_notes' => 'Sedang dibersihkan oleh staf kebersihan.',
            ]);

            HousekeepingTask::firstOrCreate(
                ['room_id' => $room201->id, 'task_type' => 'deep_clean'],
                [
                    'user_id' => $housekeeper?->id,
                    'created_by' => $admin?->id,
                    'priority' => 'normal',
                    'status' => 'in_progress',
                    'started_at' => now()->subMinutes(20),
                    'notes' => 'Pembersihan menyeluruh balkon & kamar mandi.',
                ]
            );
        }

        // 3. Task for Room 203 (Dirty - pending)
        $room203 = Room::where('room_number', '203')->first();
        if ($room203) {
            $room203->update([
                'status' => 'dirty',
                'housekeeping_notes' => 'Perlu tambahan 2 set handuk bersih.',
            ]);

            HousekeepingTask::firstOrCreate(
                ['room_id' => $room203->id, 'task_type' => 'stayover_clean'],
                [
                    'user_id' => $housekeeper?->id,
                    'created_by' => $admin?->id,
                    'priority' => 'normal',
                    'status' => 'pending',
                    'notes' => 'Ganti handuk & isi ulang perlengkapan mandi.',
                ]
            );
        }

        // 4. Task for Room 206 (Maintenance)
        $room206 = Room::where('room_number', '206')->first();
        if ($room206) {
            $room206->update([
                'status' => 'maintenance',
                'housekeeping_notes' => 'AC kamar netes air & kran wastafel dol.',
            ]);

            HousekeepingTask::firstOrCreate(
                ['room_id' => $room206->id, 'task_type' => 'maintenance_repair'],
                [
                    'user_id' => $admin?->id,
                    'created_by' => $admin?->id,
                    'priority' => 'urgent',
                    'status' => 'in_progress',
                    'started_at' => now()->subHours(1),
                    'notes' => 'Panggilan teknisi AC & perbaikan pipa wastafel.',
                ]
            );
        }

        // 5. Completed task example for Room 103 (Clean)
        $room103 = Room::where('room_number', '103')->first();
        if ($room103) {
            $room103->update([
                'status' => 'available',
                'last_cleaned_at' => now()->subHours(2),
                'last_cleaned_by' => $housekeeper?->id,
                'housekeeping_notes' => 'Kamar rapi dan wangi, siap huni.',
            ]);

            HousekeepingTask::firstOrCreate(
                ['room_id' => $room103->id, 'task_type' => 'inspection'],
                [
                    'user_id' => $housekeeper?->id,
                    'created_by' => $admin?->id,
                    'priority' => 'normal',
                    'status' => 'completed',
                    'started_at' => now()->subHours(3),
                    'completed_at' => now()->subHours(2),
                    'notes' => 'Inspeksi kesiapan kamar selesai. Semua fasilitas berfungsi.',
                ]
            );
        }
    }
}
