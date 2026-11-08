<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomType\StoreRoomTypeRequest;
use App\Http\Requests\RoomType\UpdateRoomTypeRequest;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of room types.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $roomTypes = RoomType::query()
            ->withCount('rooms')
            ->search($search)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.room-types.index', [
            'title' => 'Tipe Kamar & Paket',
            'roomTypes' => $roomTypes,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created room type.
     */
    public function store(StoreRoomTypeRequest $request): RedirectResponse
    {
        RoomType::create($request->validated());

        return redirect()->route('room-types.index')->with('success', 'Tipe kamar baru berhasil ditambahkan.');
    }

    /**
     * Update the specified room type.
     */
    public function update(UpdateRoomTypeRequest $request, RoomType $roomType): RedirectResponse
    {
        $roomType->update($request->validated());

        return redirect()->route('room-types.index')->with('success', 'Tipe kamar berhasil diperbarui.');
    }

    /**
     * Remove the specified room type from storage.
     */
    public function destroy(RoomType $roomType): RedirectResponse
    {
        if ($roomType->rooms()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus tipe kamar karena masih memiliki unit kamar fisik aktif.');
        }

        $roomType->delete();

        return redirect()->route('room-types.index')->with('success', 'Tipe kamar berhasil dihapus.');
    }
}

