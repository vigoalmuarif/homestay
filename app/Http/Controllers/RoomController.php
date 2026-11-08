<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms with filter and status switcher.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $typeId = $request->query('room_type_id');
        $search = $request->query('search');

        $query = Room::query()->with('roomType')->orderBy('room_number');

        if ($status !== 'all' && in_array($status, ['available', 'occupied', 'dirty', 'maintenance'])) {
            $query->where('status', $status);
        }

        if (filled($typeId)) {
            $query->where('room_type_id', $typeId);
        }

        if (filled($search)) {
            $query->search($search);
        }

        $rooms = $query->paginate(12)->withQueryString();
        $roomTypes = RoomType::orderBy('name')->get();

        $stats = [
            'total' => Room::count(),
            'available' => Room::available()->count(),
            'occupied' => Room::occupied()->count(),
            'dirty' => Room::dirty()->count(),
            'maintenance' => Room::maintenance()->count(),
        ];

        return view('pages.rooms.index', [
            'title' => 'Master Unit Kamar Fisik',
            'rooms' => $rooms,
            'roomTypes' => $roomTypes,
            'stats' => $stats,
            'status' => $status,
            'typeId' => $typeId,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created room.
     */
    public function store(StoreRoomRequest $request): RedirectResponse
    {
        Room::create($request->validated());

        return redirect()->route('rooms.index')->with('success', 'Unit kamar baru berhasil ditambahkan.');
    }

    /**
     * Update the specified room.
     */
    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $room->update($request->validated());

        return redirect()->route('rooms.index')->with('success', 'Data unit kamar berhasil diperbarui.');
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Unit kamar berhasil dihapus.');
    }

    /**
     * Fast status switcher for Front Desk & Housekeeping.
     */
    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied,dirty,cleaning,maintenance'],
        ]);

        $room->status = $validated['status'];
        $room->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status kamar {$room->room_number} berhasil diubah ke {$room->status}.",
                'data' => $room->load('roomType'),
            ]);
        }

        return back()->with('success', "Status kamar {$room->room_number} berhasil diperbarui.");
    }
}

