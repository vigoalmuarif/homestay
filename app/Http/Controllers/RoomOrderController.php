<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\StoreRoomOrderRequest;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Services\RoomOrderService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class RoomOrderController extends Controller
{
    /**
     * Store a newly created room order / mini-pos item for a booking.
     */
    public function store(StoreRoomOrderRequest $request, Booking $booking, RoomOrderService $orderService): mixed
    {
        if (in_array($booking->status, ['checked_out', 'cancelled'])) {
            $msg = 'Tidak dapat menambahkan pesanan untuk reservasi yang sudah selesai atau dibatalkan.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['error' => $msg]);
        }

        try {
            $item = $orderService->createOrder($booking, $request->validated(), auth()->id());

            $billingNotice = $item->billing_type === 'charged_to_room'
                ? 'dibebankan ke tagihan kamar'
                : 'dibayar langsung (lunas)';

            $successMsg = "Pesanan '{$item->item_name}' (x{$item->quantity}) berhasil disimpan ({$billingNotice}).";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'data' => $item,
                ], 201);
            }

            return back()->with('success', $successMsg);
        } catch (RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (Exception $e) {
            $errorMsg = 'Terjadi kesalahan sistem saat memproses pesanan: ' . $e->getMessage();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                ], 500);
            }
            return back()->withErrors(['error' => $errorMsg]);
        }
    }

    /**
     * Cancel an existing room order item and restore inventory.
     */
    public function destroy(Booking $booking, BookingItem $bookingItem, RoomOrderService $orderService): RedirectResponse
    {
        if ($bookingItem->booking_id !== $booking->id) {
            abort(404);
        }

        if (in_array($booking->status, ['checked_out', 'cancelled'])) {
            return back()->withErrors(['error' => 'Tidak dapat membatalkan pesanan untuk reservasi yang sudah checkout atau dibatalkan.']);
        }

        try {
            $orderService->cancelOrder($bookingItem);

            return back()->with('success', "Pesanan '{$bookingItem->item_name}' berhasil dibatalkan dan stok telah dikembalikan.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal membatalkan pesanan: ' . $e->getMessage()]);
        }
    }
}

