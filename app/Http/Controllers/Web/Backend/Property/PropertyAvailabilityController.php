<?php

namespace App\Http\Controllers\Web\Backend\Property;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Rate;
use App\Services\AdminPricingService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PropertyAvailabilityController extends Controller
{
    public function index(Request $request): JsonResponse | View
    {
        if ($request->ajax()) {
            $data = Property::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return $data->title;
                })
                ->addColumn('status', function ($data) {
                    $badges = [
                        'inactive' => 'bg-secondary',
                        'active' => 'bg-success',
                        'pending' => 'bg-warning',
                        'sold' => 'bg-info',
                        'withdrawn' => 'bg-danger',
                    ];
                    $badgeClass = $badges[$data->property_status] ?? 'bg-primary';
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($data->property_status) . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<a href="' . route('property-availability.show', $data->property_id) . '" class="btn btn-sm btn-primary">
                                <i class="fe fe-calendar"></i> Manage Availability
                            </a>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view('backend.layouts.property.availability.index');
    }

    public function show($id): View
    {
        $property = Property::with(['currency', 'rates' => function ($q) {
            $q->where('status', 'active');
        }, 'addon', 'discount'])->findOrFail($id);

        $bookedCustomers = Booking::where('property_id', $id)
            ->whereIn('booking_status', ['confirmed', 'pending', 'checked_in', 'completed', 'cancelled', 'no_show'])
            ->orderByDesc('created_at')
            ->get();

        return view('backend.layouts.property.availability.show', compact('property', 'bookedCustomers'));
    }

    /**
     * AJAX: Calculate price for live preview in the booking modal.
     */
    public function calculatePrice(Request $request, AdminPricingService $pricingService): JsonResponse
    {
        $request->validate([
            'property_id'    => 'required|exists:properties,property_id',
            'check_in'       => 'required|date',
            'check_out'      => 'required|date|after:check_in',
            'no_of_guests'   => 'required|integer|min:1',
        ]);

        $result = $pricingService->calculate([
            'property_id'          => $request->property_id,
            'checkin_date'         => $request->check_in,
            'checkout_date'        => $request->check_out,
            'no_of_guests'         => $request->no_of_guests,
        ]);

        return response()->json($result);
    }

    /**
     * AJAX: Get active rates for a property.
     */
    public function propertyRates($id): JsonResponse
    {
        $rates = Rate::where('property_id', $id)
            ->where('status', 'active')
            ->orderBy('start_date')
            ->get()
            ->map(function ($rate) {
                return [
                    'rate_id'                  => $rate->rate_id,
                    'start_date'               => $rate->start_date->format('Y-m-d'),
                    'end_date'                 => $rate->end_date->format('Y-m-d'),
                    'nightly_rate'             => (float) $rate->nightly_rate,
                    'nightly_rate_weekend'     => (float) ($rate->nightly_rate_weekend ?? 0),
                    'min_stay'                 => (int) ($rate->min_stay ?? 1),
                    'persons'                  => (int) ($rate->persons ?? 1),
                    'extra_person_price_night' => (float) ($rate->extra_person_price_night ?? 0),
                    'is_special_offer'         => $rate->is_special_offer,
                    'label'                    => $rate->nightly_rate . '/night | ' .
                        $rate->start_date->format('M d') . ' – ' . $rate->end_date->format('M d, Y') .
                        ' | min ' . ($rate->min_stay ?? 1) . ' nights',
                ];
            });

        return response()->json(['rates' => $rates]);
    }

    public function fetchEvents($id): JsonResponse
    {
        // ── Confirmed Bookings ──
        $bookings = Booking::where('property_id', $id)
            ->whereIn('booking_status', ['confirmed', 'pending', 'checked_in', 'completed', 'cancelled', 'no_show'])
            ->get();

        $events = [];
        foreach ($bookings as $booking) {
            $colorMap = [
                'confirmed' => '#dc3545',
                'checked_in' => '#0d6efd',
                'pending' => '#ffc107',
                'completed' => '#198754',
                'cancelled' => '#6c757d',
                'no_show' => '#212529',
            ];

            $events[] = [
                'id' => 'booking-' . $booking->booking_id,
                'title' => $booking->guest_name ?: 'Customer',
                'start' => Carbon::parse($booking->check_in)->toDateString(),
                'end' => Carbon::parse($booking->check_out)->toDateString(),
                'color' => $colorMap[$booking->booking_status] ?? '#6c757d',
                'allDay' => true,
                'extendedProps' => [
                    'status' => $booking->booking_status,
                    'guest' => $booking->guest_name,
                    'guest_email' => $booking->guest_email,
                    'guest_phone' => $booking->guest_phone,
                    'guest_address' => $booking->guest_address,
                    'payment_status' => $booking->payment_status,
                    'price' => (float) $booking->grand_total,
                    'booking_code' => $booking->booking_code,
                ],
            ];
        }

        return response()->json($events);
    }

    public function store(Request $request, AdminPricingService $pricingService): JsonResponse
    {
        $request->validate([
            'property_id'     => 'required|exists:properties,property_id',
            'check_in'        => 'required|date',
            'check_out'       => 'required|date|after:check_in',
            'booking_status'  => 'required|in:confirmed,pending,checked_in,completed,cancelled,no_show,removed',
            'guest_name'      => 'required_unless:booking_status,removed|string|max:100',
            'guest_email'     => 'required_unless:booking_status,removed|email|max:255',
            'guest_phone'     => 'required_unless:booking_status,removed|string|max:30',
            'guest_address'   => 'nullable|string|max:255',
            'payment_status'  => 'required_unless:booking_status,removed|in:unpaid,partially_paid,paid,refunded',
            'number_adults'   => 'nullable|integer|min:1',
            'number_children' => 'nullable|integer|min:0',
            'payment_method'  => 'nullable|in:manual_cash,bank_transfer,stripe,paypal,cheque,others',
            'guest_message'   => 'nullable|string|max:1000',
            'internal_notes'  => 'nullable|string|max:1000',
        ]);

        try {
            // ── Handle "removed" status — delete overlapping bookings ──
            if ($request->booking_status === 'removed') {
                $deleted = Booking::where('property_id', $request->property_id)
                    ->where(function ($query) use ($request) {
                        $query->whereBetween('check_in', [$request->check_in, $request->check_out])
                            ->orWhereBetween('check_out', [$request->check_in, $request->check_out])
                            ->orWhere(function ($q) use ($request) {
                                $q->where('check_in', '<=', $request->check_in)
                                    ->where('check_out', '>=', $request->check_out);
                            });
                    })
                    ->whereIn('booking_status', ['confirmed', 'pending'])
                    ->delete();

                if ($deleted > 0) {
                    return response()->json(['t-success' => true, 'message' => $deleted . ' booking(s) removed successfully!']);
                } else {
                    return response()->json(['t-success' => false, 'message' => 'No bookings found in the selected date range.']);
                }
            }

            // ── Check for overlapping bookings ──
            $overlap = Booking::where('property_id', $request->property_id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('check_in', [$request->check_in, $request->check_out])
                        ->orWhereBetween('check_out', [$request->check_in, $request->check_out])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('check_in', '<=', $request->check_in)
                                ->where('check_out', '>=', $request->check_out);
                        });
                })
                ->whereIn('booking_status', ['confirmed', 'pending'])
                ->exists();

            if ($overlap) {
                return response()->json(['t-success' => false, 'message' => 'Selected dates are already booked!']);
            }

            $totalGuests = (int) ($request->number_adults ?? 1) + (int) ($request->number_children ?? 0);
            $totalGuests = max(1, $totalGuests);
            $checkin = Carbon::parse($request->check_in)->startOfDay();
            $checkout = Carbon::parse($request->check_out)->startOfDay();
            $nights = (int) $checkin->diffInDays($checkout);

            if ($nights <= 0) {
                return response()->json(['t-success' => false, 'message' => 'Invalid booking dates selected.']);
            }

            $pricingResult = $pricingService->calculate([
                'property_id'   => $request->property_id,
                'checkin_date'  => $request->check_in,
                'checkout_date' => $request->check_out,
                'no_of_guests'  => $totalGuests,
            ]);

            if (!$pricingResult['success']) {
                return response()->json(['t-success' => false, 'message' => $pricingResult['message']]);
            }

            $pricingData = $pricingResult['data'];

            $booking = Booking::create([
                'booking_source'  => 'admin_dashboard',
                'property_id'     => $request->property_id,
                'user_id'         => null,
                'created_by'      => Auth::id(),
                'guest_name'      => $request->guest_name,
                'guest_email'     => $request->guest_email,
                'guest_phone'     => $request->guest_phone,
                'guest_address'   => $request->guest_address,
                'check_in'        => $request->check_in,
                'check_out'       => $request->check_out,
                'number_adults'   => $request->number_adults ?? 1,
                'number_children' => $request->number_children ?? 0,
                'number_nights'   => $pricingData['nights'],
                'rental_price'    => $pricingData['subtotal'],
                'discount_amount' => $pricingData['discount_amount'],
                'addon_total'     => $pricingData['addon_total'],
                'coupon_discount' => $pricingData['coupon_discount'],
                'grand_total'     => $pricingData['grand_total'],
                'subtotal'        => $pricingData['subtotal'],
                'addon_id'        => $pricingData['applied_addon']['addon_id'] ?? null,
                'discount_id'     => $pricingData['applied_discount']['discount_id'] ?? null,
                'payment_method'  => $request->payment_method ?? 'manual_cash',
                'payment_status'  => $request->payment_status,
                'booking_status'  => $request->booking_status,
                'guest_message'   => $request->guest_message,
                'internal_notes'  => $request->internal_notes,
                'status'          => 'active',
            ]);

            if ($booking->booking_status === 'confirmed' && $booking->guest_email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($booking->guest_email)->send(new \App\Mail\BookingConfirmationMail($booking));
                    $booking->update([
                        'confirmation_email_sent' => true,
                        'confirmation_email_sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
                }
            }

            return response()->json([
                't-success' => true,
                'message'   => 'Customer booking saved successfully! Code: ' . $booking->booking_code,
                'booking'   => [
                    'booking_id' => $booking->booking_id,
                    'booking_code' => $booking->booking_code,
                    'guest_name' => $booking->guest_name,
                    'guest_email' => $booking->guest_email,
                    'guest_phone' => $booking->guest_phone,
                    'check_in' => Carbon::parse($booking->check_in)->format('Y-m-d'),
                    'check_out' => Carbon::parse($booking->check_out)->format('Y-m-d'),
                    'grand_total' => (float) $booking->grand_total,
                    'payment_status' => $booking->payment_status,
                    'booking_status' => $booking->booking_status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['t-success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Update booking status and payment status for a customer.
     */
    public function updateCustomerStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'property_id'     => 'required|exists:properties,property_id',
            'booking_status'  => 'required|in:confirmed,pending,checked_in,completed,cancelled,no_show',
            'payment_status'  => 'required|in:unpaid,partially_paid,paid,refunded',
        ]);

        try {
            $booking = Booking::where('booking_id', $id)
                ->where('property_id', $request->property_id)
                ->firstOrFail();
            $booking->update([
                'booking_status' => $request->booking_status,
                'payment_status' => $request->payment_status,
            ]);

            return response()->json([
                't-success' => true,
                'message'   => 'Booking status updated successfully!',
                'booking'   => [
                    'booking_id'     => $booking->booking_id,
                    'booking_code'   => $booking->booking_code,
                    'guest_name'     => $booking->guest_name,
                    'guest_email'    => $booking->guest_email,
                    'guest_phone'    => $booking->guest_phone,
                    'check_in'       => Carbon::parse($booking->check_in)->format('Y-m-d'),
                    'check_out'      => Carbon::parse($booking->check_out)->format('Y-m-d'),
                    'grand_total'    => (float) $booking->grand_total,
                    'payment_status' => $booking->payment_status,
                    'booking_status' => $booking->booking_status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['t-success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }



    /**
     * Parse a value that may be a percentage string (e.g. "10%") or a fixed number.
     */
    private function parseValue(float $base, $val): float
    {
        if (is_string($val) && str_contains($val, '%')) {
            $pct = (float) str_replace('%', '', $val);
            return $base * ($pct / 100);
        }
        return (float) $val;
    }
}
