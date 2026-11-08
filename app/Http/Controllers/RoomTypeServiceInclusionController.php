<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inclusion\StoreInclusionRequest;
use App\Models\RoomType;
use App\Models\RoomTypeServiceInclusion;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomTypeServiceInclusionController extends Controller
{
    /**
     * Display inclusions configuration matrix per room type.
     */
    public function index(Request $request): View
    {
        $roomTypes = RoomType::with(['inclusions.service', 'rooms'])->get();
        $services = Service::active()->orderBy('category')->orderBy('name')->get();

        return view('pages.services.inclusions', [
            'title' => 'Pengaturan Inklusi Paket Tipe Kamar',
            'roomTypes' => $roomTypes,
            'services' => $services,
        ]);
    }

    /**
     * Store an inclusion rule for a room type.
     */
    public function store(StoreInclusionRequest $request): RedirectResponse
    {
        RoomTypeServiceInclusion::create($request->validated());

        return redirect()->route('service-inclusions.index')->with('success', 'Hak inklusi paket berhasil ditambahkan ke tipe kamar.');
    }

    /**
     * Remove an inclusion rule.
     */
    public function destroy(RoomTypeServiceInclusion $serviceInclusion): RedirectResponse
    {
        $serviceInclusion->delete();

        return redirect()->route('service-inclusions.index')->with('success', 'Inklusi paket berhasil dihapus.');
    }
}

