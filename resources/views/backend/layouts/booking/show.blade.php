@extends('backend.app')

@section('title', 'Booking Details - ' . $booking->booking_code)

@push('styles')
    <style>
        .detail-card {
            border-left: 4px solid #6366f1;
        }

        .detail-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }

        .payment-timeline .timeline-item {
            border-left: 2px solid #e2e8f0;
            padding-left: 1rem;
            padding-bottom: 1rem;
            margin-left: 0.5rem;
            position: relative;
        }

        .payment-timeline .timeline-item::before {
            content: '';
            width: 10px;
            height: 10px;
            background: #6366f1;
            border-radius: 50%;
            position: absolute;
            left: -6px;
            top: 4px;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1rem;
        }
    </style>
@endpush

@section('content')
    @php $curr = '€'; @endphp
    <div class="page-header">
        <div>
            <h1 class="page-title">Booking: {{ $booking->booking_code }}</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fe fe-arrow-left me-1"></i> Back to Bookings
            </a>
        </div>
    </div>

    <div class="row">
        <!-- ── Left Column: Booking Details ── -->
        <div class="col-lg-8">
            <!-- Booking Overview -->
            <div class="card detail-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">{{ $booking->booking_code }}</h5>
                            <small class="text-muted">Created {{ $booking->created_at->format('M d, Y H:i A') }} by
                                {{ $booking->creator->name ?? 'System' }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            @php
                                $bsBadges = ['pending' => 'bg-warning', 'confirmed' => 'bg-success', 'checked_in' => 'bg-info', 'completed' => 'bg-primary', 'cancelled' => 'bg-danger', 'no_show' => 'bg-secondary'];
                                $psBadges = ['unpaid' => 'bg-danger', 'partially_paid' => 'bg-warning', 'paid' => 'bg-success', 'refunded' => 'bg-info'];
                            @endphp
                            <span
                                class="badge {{ $bsBadges[$booking->booking_status] ?? 'bg-secondary' }} fs-12">{{ ucfirst(str_replace('_', ' ', $booking->booking_status)) }}</span>
                            <span
                                class="badge {{ $psBadges[$booking->payment_status] ?? 'bg-secondary' }} fs-12">{{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="section-title"><i class="fe fe-user me-1"></i> Guest Information</div>
                            <div class="mb-2">
                                <div class="detail-label">Name</div>
                                <div class="detail-value">{{ $booking->guest_name }}</div>
                            </div>
                            <div class="mb-2">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">{{ $booking->guest_email }}</div>
                            </div>
                            <div class="mb-2">
                                <div class="detail-label">Phone</div>
                                <div class="detail-value">{{ $booking->guest_phone }}</div>
                            </div>
                            @if(!empty($booking->guest_country))
                                <div class="mb-2">
                                    <div class="detail-label">Country</div>
                                    <div class="detail-value">{{ $booking->guest_country }}</div>
                            </div>@endif
                        </div>
                        <div class="col-md-6">
                            <div class="section-title"><i class="fe fe-calendar me-1"></i> Stay Details</div>
                            <div class="mb-2">
                                <div class="detail-label">Property</div>
                                <div class="detail-value">{{ $booking->property->title ?? 'N/A' }}</div>
                            </div>
                            <div class="mb-2">
                                <div class="detail-label">Check-in</div>
                                <div class="detail-value">{{ Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="detail-label">Check-out</div>
                                <div class="detail-value">{{ Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="detail-label">Nights</div>
                                <div class="detail-value">{{ $booking->number_nights }}</div>
                            </div>
                            <div class="mb-2">
                                <div class="detail-label">Guests</div>
                                <div class="detail-value">{{ $booking->number_adults }} Adult(s),
                                    {{ $booking->number_children }} Child(ren)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Breakdown -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0"><i class="fe fe-credit-card me-1"></i> Pricing Breakdown</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td>Rental Price</td>
                                <td class="text-end">{{ $curr }}{{ number_format($booking->rental_price, 2) }}</td>
                            </tr>
                            @if(!empty($booking->addon_total) && $booking->addon_total > 0)
                                <tr>
                                     <td>Add-ons</td>
                                     <td class="text-end">{{ $curr }}{{ number_format($booking->addon_total, 2) }}</td>
                            </tr>@endif
                            @if(!empty($booking->extra_guest_charge) && $booking->extra_guest_charge > 0)
                                <tr>
                                    <td>Extra Guest Charge</td>
                                    <td class="text-end">{{ $curr }}{{ number_format($booking->extra_guest_charge, 2) }}</td>
                            </tr>@endif
                            @if(!empty($booking->booking_fee) && $booking->booking_fee > 0)
                                <tr>
                                    <td>Booking Fee</td>
                                    <td class="text-end">{{ $curr }}{{ number_format($booking->booking_fee, 2) }}</td>
                            </tr>@endif
                            @if(!empty($booking->discount_amount) && $booking->discount_amount > 0)
                                <tr class="text-success">
                                    <td>Discount</td>
                                    <td class="text-end">-{{ $curr }}{{ number_format($booking->discount_amount, 2) }}</td>
                            </tr>@endif
                            @if(!empty($booking->coupon_discount) && $booking->coupon_discount > 0)
                                <tr class="text-success">
                                    <td>Coupon Discount</td>
                                    <td class="text-end">-{{ $curr }}{{ number_format($booking->coupon_discount, 2) }}</td>
                            </tr>@endif
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end">{{ $curr }}{{ number_format($booking->subtotal, 2) }}</td>
                            </tr>
                            @if(!empty($booking->tax_amount) && $booking->tax_amount > 0)
                                <tr>
                                    <td>Tax</td>
                                    <td class="text-end">{{ $curr }}{{ number_format($booking->tax_amount, 2) }}</td>
                            </tr>@endif
                            @if(!empty($booking->security_deposit) && $booking->security_deposit > 0)
                                <tr>
                                    <td>Security Deposit</td>
                                    <td class="text-end">{{ $curr }}{{ number_format($booking->security_deposit, 2) }}</td>
                            </tr>@endif
                            <tr class="fw-bold fs-16 border-top">
                                <td>Grand Total</td>
                                <td class="text-end">{{ $curr }}{{ number_format($booking->grand_total, 2) }}</td>
                            </tr>
                            <tr class="text-success">
                                <td>Total Paid</td>
                                <td class="text-end">{{ $curr }}{{ number_format($booking->total_paid, 2) }}</td>
                            </tr>
                            <tr class="text-danger fw-bold">
                                <td>Balance Due</td>
                                <td class="text-end">{{ $curr }}{{ number_format($booking->balance_due, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fe fe-credit-card me-1"></i> Payment History</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                        <i class="fe fe-plus me-1"></i> Record Payment
                    </button>
                </div>
                <div class="card-body">
                    @if($booking->payments->count() > 0)
                        <div class="payment-timeline">
                            @foreach($booking->payments as $payment)
                                            <div class="timeline-item">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <strong>{{ $curr }}{{ number_format($payment->amount, 2) }}</strong>
                                                        <span
                                                            class="badge bg-light text-dark ms-1">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                                        @php
                                                            $pBadges = ['completed' => 'bg-success', 'pending' => 'bg-warning', 'failed' => 'bg-danger', 'refunded' => 'bg-info'];
                                                        @endphp
                                 <span
                                                            class="badge {{ $pBadges[$payment->payment_status] ?? 'bg-secondary' }} ms-1">{{ ucfirst($payment->payment_status) }}</span>
                                                    </div>
                                                    <small
                                                        class="text-muted">{{ Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</small>
                                                </div>
                                                <small class="text-muted">Collected by {{ $payment->collector->name ?? 'N/A' }}</small>
                                                @if($payment->notes)<br><small class="text-muted fst-italic">{{ $payment->notes }}</small>@endif
                                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fe fe-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No payments recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ── Right Column: Actions ── -->
        <div class="col-lg-4">
            <!-- Update Booking Status -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0"><i class="fe fe-settings me-1"></i> Update Booking</h5>
                </div>
                <div class="card-body">
                    <form id="updateBookingForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Guest Name</label>
                            <input type="text" name="guest_name" class="form-control" value="{{ $booking->guest_name }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Guest Email</label>
                            <input type="email" name="guest_email" class="form-control" value="{{ $booking->guest_email }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Guest Phone</label>
                            <input type="text" name="guest_phone" class="form-control" value="{{ $booking->guest_phone }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Booking Status</label>
                            <select name="booking_status" class="form-select">
                                @foreach(['pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show'] as $s)
                                    <option value="{{ $s }}" {{ $booking->booking_status === $s ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                @foreach(['unpaid', 'partially_paid', 'paid', 'refunded'] as $s)
                                    <option value="{{ $s }}" {{ $booking->payment_status === $s ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                @foreach(['manual_cash' => '💵 Cash', 'bank_transfer' => '🏦 Bank Transfer', 'stripe' => '💳 Stripe', 'paypal' => '🅿️ PayPal', 'cheque' => '📄 Cheque', 'others' => '📋 Others'] as $val => $label)
                                    <option value="{{ $val }}" {{ $booking->payment_method === $val ? 'selected' : '' }}>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Internal Notes</label>
                            <textarea name="internal_notes" class="form-control"
                                rows="3">{{ $booking->internal_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <i class="fe fe-save me-1"></i> Update Booking
                        </button>
                    </form>
                </div>
            </div>

            @if($booking->guest_message)
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0"><i class="fe fe-message-square me-1"></i> Guest Message</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ $booking->guest_message }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ── Record Payment Modal ── -->
    <div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #fff;">
                    <h5 class="modal-title fw-bold"><i class="fe fe-credit-card me-1"></i> Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        style="filter: brightness(0) invert(1);"></button>
                </div>
                <form id="recordPaymentForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $curr }}</span>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required
                                    placeholder="0.00">
                            </div>
                            <small class="text-muted">Balance due: {{ $curr }}{{ number_format($booking->balance_due, 2) }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="manual_cash">💵 Cash</option>
                                <option value="bank_transfer">🏦 Bank Transfer</option>
                                <option value="stripe">💳 Stripe</option>
                                <option value="paypal">🅿️ PayPal</option>
                                <option value="cheque">📄 Cheque</option>
                                <option value="others">📋 Others</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fe fe-check me-1"></i> Record
                            Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({ headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") } });

            // Update Booking
            $('#updateBookingForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('bookings.update', $booking->booking_id) }}",
                    type: "PUT",
                    data: $(this).serialize(),
                    success: function (resp) {
                        resp['t-success'] ? toastr.success(resp.message) : toastr.error(resp.message);
                        if (resp['t-success']) setTimeout(() => location.reload(), 1000);
                    },
                    error: function (xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            Object.values(xhr.responseJSON.errors).forEach(err => toastr.error(err[0]));
                        } else { toastr.error('An error occurred.'); }
                    }
                });
            });

            // Record Payment
            $('#recordPaymentForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('bookings.record-payment', $booking->booking_id) }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (resp) {
                        resp['t-success'] ? toastr.success(resp.message) : toastr.error(resp.message);
                        if (resp['t-success']) setTimeout(() => location.reload(), 1000);
                    },
                    error: function (xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            Object.values(xhr.responseJSON.errors).forEach(err => toastr.error(err[0]));
                        } else { toastr.error('An error occurred.'); }
                    }
                });
            });
        });
    </script>
@endpush
