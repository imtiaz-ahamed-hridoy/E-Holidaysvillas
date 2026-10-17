<?php

namespace App\Http\Controllers\Web\Backend\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            $data = Booking::with(['property', 'property.currency', 'creator'])->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('booking_code', fn($d) => '<span class="fw-bold text-primary">' . $d->booking_code . '</span>')
                ->addColumn('guest', fn($d) => '<strong>' . e($d->guest_name) . '</strong><br><small class="text-muted">' . e($d->guest_email) . '</small>')
                ->addColumn('property_title', fn($d) => $d->property->title ?? 'N/A')
                ->addColumn('dates', fn($d) => Carbon::parse($d->check_in)->format('M d') . ' → ' . Carbon::parse($d->check_out)->format('M d, Y'))
                ->addColumn('grand_total', fn($d) => '<strong>€' . number_format($d->grand_total, 2) . '</strong>')
                ->addColumn('booking_status_badge', function ($d) {
                    $badges = [
                        'pending'    => 'bg-warning',
                        'confirmed'  => 'bg-success',
                        'checked_in' => 'bg-info',
                        'completed'  => 'bg-primary',
                        'cancelled'  => 'bg-danger',
                        'no_show'    => 'bg-secondary',
                    ];
                    return '<span class="badge ' . ($badges[$d->booking_status] ?? 'bg-secondary') . '">' . ucfirst(str_replace('_', ' ', $d->booking_status)) . '</span>';
                })
                ->addColumn('payment_status_badge', function ($d) {
                    $badges = [
                        'unpaid'         => 'bg-danger',
                        'partially_paid' => 'bg-warning',
                        'paid'           => 'bg-success',
                        'refunded'       => 'bg-info',
                    ];
                    return '<span class="badge ' . ($badges[$d->payment_status] ?? 'bg-secondary') . '">' . ucfirst(str_replace('_', ' ', $d->payment_status)) . '</span>';
                })
                ->addColumn('action', function ($d) {
                    return '<div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('bookings.show', $d->booking_id) . '" class="btn btn-primary fs-14 text-white" title="View">
                            <i class="fe fe-eye"></i>
                        </a>
                        <a href="javascript:void(0)" onclick="showDeleteConfirm(' . $d->booking_id . ')" class="btn btn-danger fs-14 text-white" title="Delete">
                            <i class="fe fe-trash"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['booking_code', 'guest', 'grand_total', 'booking_status_badge', 'payment_status_badge', 'action'])
                ->make();
        }

        return view('backend.layouts.booking.index');
    }

    /**
     * Display the specified booking with payment history.
     */
    public function show($id): View
    {
        $booking = Booking::with(['property', 'property.currency', 'creator', 'payments.collector', 'addon', 'discount'])->findOrFail($id);
        $properties = Property::where('status', 'active')->get();
        return view('backend.layouts.booking.show', compact('booking', 'properties'));
    }

    /**
     * Update booking details.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'booking_status' => 'required|in:pending,confirmed,checked_in,completed,cancelled,no_show',
            'payment_status' => 'required|in:unpaid,partially_paid,paid,refunded',
            'payment_method' => 'required|in:manual_cash,bank_transfer,stripe,paypal,cheque,others',
            'guest_name'     => 'required|string|max:100',
            'guest_email'    => 'required|email|max:255',
            'guest_phone'    => 'required|string|max:30',
            'internal_notes' => 'nullable|string',
        ]);

        try {
            $booking = Booking::findOrFail($id);
            $booking->update($request->only([
                'booking_status',
                'payment_status',
                'payment_method',
                'guest_name',
                'guest_email',
                'guest_phone',
                'internal_notes',
            ]));

            return response()->json(['t-success' => true, 'message' => 'Booking updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['t-success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Record a payment for a booking.
     */
    public function recordPayment(Request $request, $id): JsonResponse
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:manual_cash,bank_transfer,stripe,paypal,cheque,others',
            'payment_date'   => 'required|date',
            'notes'          => 'nullable|string|max:500',
        ]);

        try {
            $booking = Booking::findOrFail($id);

            $payment = Payment::create([
                'booking_id'     => $booking->booking_id,
                'amount'         => $request->amount,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method,
                'payment_status' => 'completed',
                'collected_by'   => Auth::id(),
                'notes'          => $request->notes,
                'status'         => 'active',
            ]);

            // Auto-update booking payment_status and booking_status
            $totalPaid = $booking->payments()->where('payment_status', 'completed')->sum('amount');
            if ($totalPaid >= $booking->grand_total) {
                $booking->update([
                    'payment_status' => 'paid',
                    'booking_status' => 'confirmed'
                ]);
            } elseif ($totalPaid > 0) {
                $booking->update([
                    'payment_status' => 'partially_paid',
                    'booking_status' => 'confirmed'
                ]);
            }

            // Send Payment Receipt Email
            if ($booking->guest_email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($booking->guest_email)->send(new \App\Mail\PaymentReceiptMail($booking, $payment));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send payment receipt email: ' . $e->getMessage());
                }
            }

            $curr = '€';

            return response()->json([
                't-success' => true,
                'message'   => 'Payment of ' . $curr . number_format($request->amount, 2) . ' recorded successfully! Receipt email sent.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['t-success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified booking.
     */
    public function destroy($id): JsonResponse
    {
        try {
            Booking::findOrFail($id)->delete();
            return response()->json(['t-success' => true, 'message' => 'Booking deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['t-success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
