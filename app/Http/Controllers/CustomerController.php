<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers with search and filter.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status', 'all');

        $query = Customer::query()->latest();

        if (filled($search)) {
            $query->search($search);
        }

        if ($status === 'blacklisted') {
            $query->blacklisted();
        } elseif ($status === 'active') {
            $query->active();
        }

        $customers = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::active()->count(),
            'blacklisted' => Customer::blacklisted()->count(),
        ];

        return view('pages.customers.index', [
            'title' => 'Data Tamu / Customer',
            'customers' => $customers,
            'stats' => $stats,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Search customers for reservation autocomplete (JSON).
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->query('q', '');

        $customers = Customer::query()
            ->search($keyword)
            ->limit(10)
            ->get([
                'id',
                'full_name',
                'identity_type',
                'identity_number',
                'phone_number',
                'email',
                'is_blacklisted',
                'special_notes',
            ]);

        if ($request->query('format') === 'select2' || $request->has('q')) {
            $formatted = $customers->map(function ($c) {
                $nik = $c->identity_number ? " • {$c->identity_number}" : "";
                $blacklistTag = $c->is_blacklisted ? " [BLACKLIST]" : "";
                return [
                    'id' => $c->id,
                    'text' => "{$c->full_name} ({$c->phone_number}){$nik}{$blacklistTag}",
                    'full_name' => $c->full_name,
                    'phone_number' => $c->phone_number,
                    'is_blacklisted' => (bool)$c->is_blacklisted,
                    'special_notes' => $c->special_notes,
                ];
            });
            return response()->json(['results' => $formatted]);
        }

        return response()->json($customers);
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tamu baru berhasil ditambahkan.',
                'data' => $customer,
            ], 201);
        }

        return redirect()->route('customers.index')->with('success', 'Data tamu berhasil disimpan.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $customer->load(['bookings' => fn($q) => $q->latest()->with(['room.roomType', 'items'])]);

        return view('pages.customers.show', [
            'title' => 'Detail Tamu - ' . $customer->full_name,
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data tamu berhasil diperbarui.',
                'data' => $customer,
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Data tamu berhasil diperbarui.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Data tamu berhasil dihapus.');
    }

    /**
     * Quick toggle customer blacklist status.
     */
    public function toggleBlacklist(Customer $customer): RedirectResponse
    {
        $customer->is_blacklisted = !$customer->is_blacklisted;
        $customer->save();

        $statusMsg = $customer->is_blacklisted ? 'dimasukkan ke daftar hitam (Blacklist).' : 'diaktifkan kembali.';

        return back()->with('success', "Status tamu {$customer->full_name} berhasil {$statusMsg}");
    }
}

