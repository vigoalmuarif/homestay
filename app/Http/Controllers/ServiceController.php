<?php

namespace App\Http\Controllers;

use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display a listing of services & mini-POS items.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $category = $request->query('category', 'all');
        $stockFilter = $request->query('stock', 'all');

        $query = Service::query()->latest();

        if (filled($search)) {
            $query->search($search);
        }

        if ($category !== 'all') {
            $query->category($category);
        }

        if ($stockFilter === 'has_stock') {
            $query->physicalStock();
        }

        $services = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Service::count(),
            'meals' => Service::where('category', 'meal')->count(),
            'beverages' => Service::where('category', 'beverage')->count(),
            'physical_stock' => Service::physicalStock()->count(),
            'active' => Service::active()->count(),
        ];

        return view('pages.services.index', [
            'title' => 'Katalog Layanan & Mini-POS',
            'services' => $services,
            'stats' => $stats,
            'search' => $search,
            'category' => $category,
            'stockFilter' => $stockFilter,
        ]);
    }

    /**
     * Search services for Mini-POS and Select2 autocomplete (JSON).
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->query('q', '');
        $category = $request->query('category');

        $query = Service::query()->active()->search($keyword);

        if (filled($category) && !in_array($category, ['all', 'any'])) {
            $query->where('category', $category);
        }

        $items = $query->limit(20)->get([
            'id',
            'name',
            'category',
            'sub_category',
            'price',
            'has_stock',
            'stock_quantity',
        ]);

        $formatted = $items->map(function ($item) {
            $stockInfo = $item->has_stock ? " (Stok: {$item->stock_quantity})" : "";
            $formattedPrice = number_format($item->price, 0, ',', '.');
            return [
                'id' => $item->id,
                'text' => '[' . strtoupper($item->category) . '] ' . $item->name . ' - Rp ' . $formattedPrice . $stockInfo,
                'name' => $item->name,
                'category' => $item->category,
                'sub_category' => $item->sub_category,
                'price' => (float) $item->price,
                'has_stock' => (bool) $item->has_stock,
                'stock_quantity' => (int) $item->stock_quantity,
            ];
        });

        if ($request->query('format') === 'select2' || $request->boolean('select2') || $request->has('q')) {
            return response()->json([
                'results' => $formatted,
            ]);
        }

        return response()->json($formatted);
    }

    /**
     * Store a newly created service.
     */
    public function store(StoreServiceRequest $request)
    {
        $service = Service::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan baru berhasil ditambahkan.',
                'data' => $service,
            ], 201);
        }

        return redirect()->route('services.index')->with('success', 'Layanan / menu baru berhasil ditambahkan.');
    }

    /**
     * Update the specified service.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil diperbarui.',
                'data' => $service,
            ]);
        }

        return redirect()->route('services.index')->with('success', 'Layanan / menu berhasil diperbarui.');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Layanan berhasil dihapus.');
    }

    /**
     * Quick toggle active status.
     */
    public function toggleActive(Service $service): RedirectResponse
    {
        $service->is_active = !$service->is_active;
        $service->save();

        $statusMsg = $service->is_active ? 'diaktifkan kembali.' : 'dinonaktifkan dari katalog.';

        return back()->with('success', "Item {$service->name} berhasil {$statusMsg}");
    }
}

