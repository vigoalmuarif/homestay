<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HousekeepingController extends Controller
{
    /**
     * Display the housekeeping dashboard & room status management.
     */
    public function index(Request $request): View
    {
        $selectedFloor = $request->input('floor');
        $selectedStatus = $request->input('status');
        $search = $request->input('search');

        $today = Carbon::today();

        // 1. Operational KPI Counters
        $totalRooms = Room::count();
        $dirtyRoomsCount = Room::where('status', 'dirty')->count();
        $cleaningRoomsCount = Room::where('status', 'cleaning')->count();
        $availableRoomsCount = Room::where('status', 'available')->count();
        $occupiedRoomsCount = Room::where('status', 'occupied')->count();
        $maintenanceRoomsCount = Room::where('status', 'maintenance')->count();

        $activeTasksCount = HousekeepingTask::active()->count();
        $urgentTasksCount = HousekeepingTask::active()
            ->whereIn('priority', ['high', 'urgent'])
            ->count();

        // 2. Query Rooms with eager relationships
        $roomsQuery = Room::with([
            'roomType',
            'lastCleanedByUser',
            'activeHousekeepingTask.assignedUser',
            'bookings' => function ($q) use ($today) {
                $q->whereDate('check_in_date', $today)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->with('customer');
            },
        ]);

        if (!empty($selectedFloor) && $selectedFloor !== 'all') {
            $roomsQuery->where('floor', $selectedFloor);
        }

        if (!empty($selectedStatus) && $selectedStatus !== 'all') {
            $roomsQuery->where('status', $selectedStatus);
        }

        if (!empty($search)) {
            $roomsQuery->search($search);
        }

        $rooms = $roomsQuery->orderBy('floor')->orderBy('room_number')->get();

        // 3. Query Housekeeping Tasks / Work Orders
        $tasksQuery = HousekeepingTask::with(['room.roomType', 'assignedUser', 'creator']);

        $taskFilter = $request->input('task_filter', 'active');
        if ($taskFilter === 'active') {
            $tasksQuery->active();
        } elseif ($taskFilter === 'completed') {
            $tasksQuery->completed();
        }

        $tasks = $tasksQuery->latest()->paginate(10, ['*'], 'tasks_page')->withQueryString();

        // 4. Floor list & Housekeeper users for modal assignment
        $floors = Room::select('floor')->whereNotNull('floor')->distinct()->pluck('floor');

        $housekeepers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['housekeeping', 'admin', 'receptionist']);
        })->get();

        if ($housekeepers->isEmpty()) {
            $housekeepers = User::all();
        }

        return view('pages.housekeeping.index', [
            'title' => 'Housekeeping & Manajemen Kamar',
            'rooms' => $rooms,
            'tasks' => $tasks,
            'floors' => $floors,
            'housekeepers' => $housekeepers,
            'selectedFloor' => $selectedFloor,
            'selectedStatus' => $selectedStatus,
            'search' => $search,
            'taskFilter' => $taskFilter,
            'metrics' => [
                'total' => $totalRooms,
                'dirty' => $dirtyRoomsCount,
                'cleaning' => $cleaningRoomsCount,
                'available' => $availableRoomsCount,
                'occupied' => $occupiedRoomsCount,
                'maintenance' => $maintenanceRoomsCount,
                'active_tasks' => $activeTasksCount,
                'urgent_tasks' => $urgentTasksCount,
            ],
        ]);
    }

    /**
     * Update room status directly from housekeeping grid.
     */
    public function updateRoomStatus(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied,dirty,cleaning,maintenance'],
            'housekeeping_notes' => ['nullable', 'string', 'max:1000'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $newStatus = $validated['status'];
        $notes = $validated['housekeeping_notes'] ?? null;

        $room->status = $newStatus;
        if ($notes !== null) {
            $room->housekeeping_notes = $notes;
        }

        // Logic when marked as clean / available
        if ($newStatus === 'available') {
            $room->last_cleaned_at = now();
            $room->last_cleaned_by = auth()->id();

            // Auto-complete active task for this room if exists
            HousekeepingTask::where('room_id', $room->id)
                ->active()
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
        }

        // Logic when marked as cleaning (in-progress)
        if ($newStatus === 'cleaning') {
            $activeTask = HousekeepingTask::where('room_id', $room->id)->active()->first();
            if ($activeTask) {
                $activeTask->update([
                    'status' => 'in_progress',
                    'started_at' => $activeTask->started_at ?? now(),
                    'user_id' => $validated['assigned_user_id'] ?? $activeTask->user_id ?? auth()->id(),
                ]);
            } else {
                HousekeepingTask::create([
                    'room_id' => $room->id,
                    'user_id' => $validated['assigned_user_id'] ?? auth()->id(),
                    'created_by' => auth()->id(),
                    'task_type' => 'checkout_clean',
                    'priority' => 'normal',
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'notes' => $notes,
                ]);
            }
        }

        $room->save();

        return back()->with('success', "Status Kamar {$room->room_number} berhasil diubah menjadi {$room->status_badge['label']}.");
    }

    /**
     * Create a new housekeeping work order / task.
     */
    public function storeTask(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'task_type' => ['required', 'in:checkout_clean,stayover_clean,deep_clean,inspection,maintenance_repair'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'room_id.required' => 'Unit kamar wajib dipilih.',
            'room_id.exists' => 'Unit kamar yang dipilih tidak valid.',
            'task_type.required' => 'Jenis pekerjaan kebersihan wajib dipilih.',
            'task_type.in' => 'Pilihan jenis pekerjaan tidak valid.',
            'priority.required' => 'Tingkat prioritas wajib ditentukan.',
            'priority.in' => 'Pilihan tingkat prioritas tidak valid.',
            'user_id.exists' => 'Staf yang dipilih tidak terdaftar dalam sistem.',
            'notes.max' => 'Catatan instruksi maksimal 1000 karakter.',
        ]);

        $task = HousekeepingTask::create([
            'room_id' => $validated['room_id'],
            'user_id' => $validated['user_id'] ?? null,
            'created_by' => auth()->id(),
            'task_type' => $validated['task_type'],
            'priority' => $validated['priority'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // If maintenance task, mark room as maintenance
        if ($validated['task_type'] === 'maintenance_repair') {
            $task->room->update(['status' => 'maintenance']);
        } elseif ($task->room->status === 'available') {
            $task->room->update(['status' => 'dirty']);
        }

        return back()->with('success', "Tugas kebersihan baru untuk Kamar {$task->room->room_number} berhasil dibuat.");
    }

    /**
     * Fast toggle / update status of a housekeeping task.
     */
    public function updateTaskStatus(Request $request, HousekeepingTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
        ]);

        $newStatus = $validated['status'];
        $task->status = $newStatus;

        if ($newStatus === 'in_progress') {
            $task->started_at = now();
            if ($task->room->status !== 'occupied') {
                $task->room->update(['status' => 'cleaning']);
            }
        } elseif ($newStatus === 'completed') {
            $task->completed_at = now();
            if ($task->room->status !== 'occupied') {
                $task->room->update([
                    'status' => 'available',
                    'last_cleaned_at' => now(),
                    'last_cleaned_by' => auth()->id(),
                ]);
            }
        }

        $task->save();

        return back()->with('success', "Status tugas Kamar {$task->room->room_number} berhasil diperbarui.");
    }

    /**
     * Remove a task.
     */
    public function destroyTask(HousekeepingTask $task): RedirectResponse
    {
        $roomNum = $task->room->room_number;
        $task->delete();

        return back()->with('success', "Tugas kebersihan Kamar {$roomNum} berhasil dihapus.");
    }
}

