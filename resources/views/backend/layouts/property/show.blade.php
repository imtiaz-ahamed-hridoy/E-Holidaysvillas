@extends('backend.app')

@section('title', 'Property Details - ' . $property->title)

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    <style>
        /* ── Booking Calendar Styles ── */
        .booking-calendar-card .fc {
            font-size: 0.9rem;
        }
        .booking-calendar-card .fc-toolbar-title {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
        }
        .booking-calendar-card .fc-daygrid-day-frame {
            min-height: 80px !important;
        }
        .booking-calendar-card .fc-event {
            cursor: pointer;
            border: none !important;
            border-radius: 4px !important;
            padding: 2px 6px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
        }
        .booking-calendar-card .fc-day-today {
            background-color: rgba(14, 165, 233, 0.06) !important;
        }

        /* Legend */
        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 1rem;
        }
        .calendar-legend .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #475569;
        }
        .calendar-legend .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        /* Booking Detail Modal */
        .booking-detail-modal .modal-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #fff;
            border-bottom: none;
        }
        .booking-detail-modal .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        .booking-detail-modal .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .booking-detail-modal .detail-row:last-child {
            border-bottom: none;
        }
        .booking-detail-modal .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.85rem;
        }
        .booking-detail-modal .detail-value {
            font-weight: 500;
            color: #1e293b;
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title fs-2">Property Details</h1>
            <p class="text-muted mb-0">{{ $property->title }}</p>
        </div>
        <div class="ms-auto pageheader-btn d-flex gap-2">
            <a href="{{ route('property.edit', $property->property_id) }}" class="btn btn-primary btn-sm">
                <i class="fe fe-edit me-1"></i> Edit
            </a>
            <a href="{{ route('property.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fe fe-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Title:</strong> {{ $property->title }}</div>
                        <div class="col-md-6"><strong>Status:</strong> {{ ucfirst($property->property_status ?? 'n/a') }}</div>
                        <div class="col-md-6"><strong>Main Type:</strong> {{ ucfirst($property->property_main_type ?? 'n/a') }}</div>
                        <div class="col-md-6"><strong>Category:</strong> {{ $property->propertyCategory->title ?? 'N/A' }}</div>
                        <div class="col-md-6"><strong>Property Type:</strong> {{ $property->propertyType->title ?? 'N/A' }}</div>
                        <div class="col-md-6"><strong>Unit Type:</strong> {{ $property->unitType->title ?? 'N/A' }}</div>
                        <div class="col-md-6"><strong>Owner:</strong> {{ $property->user->name ?? 'N/A' }}</div>
                        <div class="col-md-6"><strong>Available From:</strong> {{ $property->available_from ? \Carbon\Carbon::parse($property->available_from)->format('M d, Y') : 'N/A' }}</div>
                        <div class="col-md-6"><strong>Price:</strong>
                            €{{ number_format((float) $property->price, 2) }}</div>
                        <div class="col-md-6"><strong>Stars:</strong> {{ $property->stars ?? 'N/A' }}</div>
                        <div class="col-md-3"><strong>Bedrooms:</strong> {{ $property->no_bedrooms ?? 0 }}</div>
                        <div class="col-md-3"><strong>Bathrooms:</strong> {{ $property->no_bathrooms ?? 0 }}</div>
                        <div class="col-md-3"><strong>Floors:</strong> {{ $property->no_floors ?? 0 }}</div>
                        <div class="col-md-3"><strong>Guests:</strong> {{ $property->no_guests ?? 0 }}</div>
                        <div class="col-md-12"><strong>Description:</strong>
                            <div class="mt-1 text-muted">{!! $property->description !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Location</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Country:</strong> {{ $property->location->country ?? 'N/A' }}</div>
                        <div class="col-md-6"><strong>Region:</strong> {{ $property->location->region ?? 'N/A' }}</div>
                        <div class="col-md-6"><strong>City:</strong> {{ $property->location->city ?? 'N/A' }}</div>
                        <div class="col-md-6"><strong>Post Code:</strong> {{ $property->location->post_code ?? 'N/A' }}</div>
                        <div class="col-md-12"><strong>Address:</strong> {{ $property->location->address ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Rates</h5>
                </div>
                <div class="card-body">
                    @if($property->rates->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Nightly</th>
                                        <th>Min Stay</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($property->rates as $rate)
                                        <tr>
                                            <td>{{ $rate->start_date ? \Carbon\Carbon::parse($rate->start_date)->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $rate->end_date ? \Carbon\Carbon::parse($rate->end_date)->format('M d, Y') : 'N/A' }}</td>
                                            <td>€{{ number_format((float) $rate->nightly_rate, 2) }}</td>
                                            <td>{{ $rate->min_stay ?? 1 }}</td>
                                            <td>{{ ucfirst($rate->status ?? 'n/a') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mb-0 text-muted">No rates added.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Amenities</h5>
                </div>
                <div class="card-body">
                    @forelse($property->amenities as $amenity)
                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $amenity->title ?? 'Amenity' }}</span>
                    @empty
                        <p class="mb-0 text-muted">No amenities added.</p>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Facilities</h5>
                </div>
                <div class="card-body">
                    @if($property->facilities->isNotEmpty())
                        <ul class="mb-0 ps-3">
                            @foreach($property->facilities as $facility)
                                <li>{{ $facility->title ?? 'Facility' }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mb-0 text-muted">No facilities added.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Media</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Photos:</strong> {{ $property->photos->count() }}
                    </div>
                    <div>
                        <strong>Videos:</strong> {{ $property->videos->count() }}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Bookings Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2"><strong>Total:</strong> {{ $property->bookings->count() }}</div>
                    <div class="mb-2"><strong>Confirmed:</strong>
                        {{ $property->bookings->where('booking_status', 'confirmed')->count() }}</div>
                    <div class="mb-2"><strong>Pending:</strong>
                        {{ $property->bookings->where('booking_status', 'pending')->count() }}</div>
                    <div class="mb-2"><strong>Checked In:</strong>
                        {{ $property->bookings->where('booking_status', 'checked_in')->count() }}</div>
                    <div class="mb-2"><strong>Completed:</strong>
                        {{ $property->bookings->where('booking_status', 'completed')->count() }}</div>
                    <div class="mb-2"><strong>Cancelled:</strong>
                        {{ $property->bookings->where('booking_status', 'cancelled')->count() }}</div>
                    <div class="mb-0"><strong>No Show:</strong>
                        {{ $property->bookings->where('booking_status', 'no_show')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         BOOKING CALENDAR SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <div class="row mt-2">
        <div class="col-12">
            <div class="card booking-calendar-card">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0"><i class="fe fe-calendar me-2"></i>Booking Calendar</h5>
                    <a href="{{ route('property-availability.show', $property->property_id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fe fe-plus me-1"></i> Manage Availability
                    </a>
                </div>
                <div class="card-body">
                    {{-- Legend --}}
                    <div class="calendar-legend">
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#dc3545;"></span> Confirmed
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#ffc107;"></span> Pending
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#0d6efd;"></span> Checked In
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#198754;"></span> Completed
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#6c757d;"></span> Cancelled
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#212529;"></span> No Show
                        </div>
                    </div>

                    {{-- Calendar Container --}}
                    <div id="propertyBookingCalendar"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Booking Detail Modal ── --}}
    <div class="modal fade booking-detail-modal" id="bookingDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fe fe-info me-2"></i>Booking Details</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="bookingDetailContent">
                        {{-- Populated by JS --}}
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Upcoming Bookings Table ── --}}
    <div class="row mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0"><i class="fe fe-list me-2"></i>All Bookings for This Property</h5>
                </div>
                <div class="card-body">
                    @if($property->bookings->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Guest</th>
                                        <th>Check-In</th>
                                        <th>Check-Out</th>
                                        <th>Nights</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($property->bookings->sortByDesc('check_in') as $booking)
                                        @php
                                            $statusBadge = match($booking->booking_status) {
                                                'confirmed' => 'bg-danger',
                                                'pending' => 'bg-warning text-dark',
                                                'checked_in' => 'bg-primary',
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-secondary',
                                                'no_show' => 'bg-dark',
                                                default => 'bg-info',
                                            };
                                            $payBadge = match($booking->payment_status) {
                                                'paid' => 'bg-success',
                                                'partially_paid' => 'bg-warning text-dark',
                                                'refunded' => 'bg-info',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $booking->booking_code }}</strong></td>
                                            <td>
                                                {{ $booking->guest_name ?? 'N/A' }}
                                                @if($booking->guest_email)
                                                    <br><small class="text-muted">{{ $booking->guest_email }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') : '-' }}</td>
                                            <td>{{ $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') : '-' }}</td>
                                            <td>{{ $booking->number_nights ?? '-' }}</td>
                                            <td>€{{ number_format((float) $booking->grand_total, 2) }}</td>
                                            <td><span class="badge {{ $payBadge }}">{{ ucfirst(str_replace('_', ' ', $booking->payment_status ?? 'unpaid')) }}</span></td>
                                            <td><span class="badge {{ $statusBadge }}">{{ ucfirst(str_replace('_', ' ', $booking->booking_status)) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mb-0 text-muted">No bookings found for this property.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const propertyId = {{ $property->property_id }};
            const currencyCode = '€';
            const eventsUrl = '{{ route("property-availability.events", $property->property_id) }}';

            const calendarEl = document.getElementById('propertyBookingCalendar');
            if (!calendarEl) return;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                height: 'auto',
                firstDay: 1,
                eventDisplay: 'block',
                dayMaxEvents: 3,
                events: {
                    url: eventsUrl,
                    failure: function () {
                        console.error('Failed to load booking events.');
                    }
                },
                eventClick: function (info) {
                    info.jsEvent.preventDefault();
                    const props = info.event.extendedProps;
                    const start = info.event.start;
                    const end = info.event.end;

                    const formatDate = (d) => {
                        if (!d) return '-';
                        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                    };

                    const statusBadgeColor = {
                        'confirmed': '#dc3545',
                        'pending': '#ffc107',
                        'checked_in': '#0d6efd',
                        'completed': '#198754',
                        'cancelled': '#6c757d',
                        'no_show': '#212529',
                    };

                    const paymentBadgeColor = {
                        'paid': '#198754',
                        'partially_paid': '#ffc107',
                        'unpaid': '#6c757d',
                        'refunded': '#0dcaf0',
                    };

                    let html = `
                        <div class="detail-row">
                            <span class="detail-label">Booking Code</span>
                            <span class="detail-value"><strong>${props.booking_code || '-'}</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Guest Name</span>
                            <span class="detail-value">${props.guest || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value">${props.guest_email || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone</span>
                            <span class="detail-value">${props.guest_phone || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Address</span>
                            <span class="detail-value">${props.guest_address || '-'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Check-In</span>
                            <span class="detail-value">${formatDate(start)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Check-Out</span>
                            <span class="detail-value">${formatDate(end)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total</span>
                            <span class="detail-value"><strong>${currencyCode}${(props.price || 0).toFixed(2)}</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Status</span>
                            <span class="detail-value">
                                <span class="badge" style="background-color:${paymentBadgeColor[props.payment_status] || '#6c757d'}; color:#fff;">
                                    ${(props.payment_status || 'unpaid').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Booking Status</span>
                            <span class="detail-value">
                                <span class="badge" style="background-color:${statusBadgeColor[props.status] || '#6c757d'}; color:#fff;">
                                    ${(props.status || '-').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </span>
                            </span>
                        </div>
                    `;

                    document.getElementById('bookingDetailContent').innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById('bookingDetailModal'));
                    modal.show();
                },
                eventDidMount: function (info) {
                    // Tooltip on hover
                    const props = info.event.extendedProps;
                    info.el.title = `${props.guest || 'Customer'} — ${(props.status || '').replace(/_/g, ' ')}`;
                }
            });

            calendar.render();
        });
    </script>
@endpush
