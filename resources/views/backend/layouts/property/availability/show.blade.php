    @extends('backend.app')

    @section('title', 'Manage Availability - ' . $property->title)

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
        <style>
            :root {
                --calendar-border: #e2e8f0;
                --booked-bg: #e11d48;
                --pending-bg: #f59e0b;
                --request-bg: #6f42c1;
                --checkedin-bg: #0d6efd;
            }

            /* Input bar */
            .booking-input-bar {
                background-color: #f8f9fb;
                border: 1px solid #e9edf4;
                padding: 1.25rem 1.5rem;
                border-radius: 6px;
                margin-bottom: 1.5rem;
            }

            .form-label-small {
                font-size: 0.8rem;
                font-weight: 600;
                color: #475569;
                margin-bottom: 0.4rem;
                display: block;
            }

            /* Calendar Grid */
            .calendar-dashboard-grid {
                display: flex;
                flex-wrap: nowrap;
                gap: 12px;
                width: 100%;
                overflow-x: auto;
                overflow-y: hidden;
                justify-content: flex-start;
                padding-bottom: 4px;
            }

            .calendar-item-wrapper {
                flex: 0 0 300px;
                width: 300px;
                height: 300px;
            }

            /* FullCalendar Minimal Theme */
            .fc-minimal {
                border: 1px solid var(--calendar-border) !important;
                border-radius: 6px;
                padding: 8px;
                background: #fff;
            }

            .fc-minimal .fc-toolbar-title {
                font-size: 0.85rem !important;
                font-weight: 800 !important;
                color: #1e293b !important;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }

            .fc-minimal .fc-scrollgrid {
                border: none !important;
            }

            .fc-minimal .fc-scrollgrid td,
            .fc-minimal .fc-scrollgrid th {
                border-color: #f1f5f9 !important;
            }

            .fc-minimal .fc-col-header-cell {
                border: none !important;
                padding: 6px 0 !important;
            }

            .fc-minimal .fc-col-header-cell-cushion {
                font-size: 0.65rem !important;
                font-weight: 600 !important;
                color: #94a3b8 !important;
                text-transform: uppercase;
                text-decoration: none !important;
                padding: 4px 0 !important;
                display: block;
            }

            .fc-minimal .fc-daygrid-day {
                border: 0.5px solid #f1f5f9 !important;
            }

            .fc-minimal .fc-daygrid-day-number {
                font-size: 0.75rem !important;
                color: #475569 !important;
                text-decoration: none !important;
                padding: 4px 6px !important;
            }

            .fc-minimal .fc-daygrid-day-frame {
                min-height: 50px !important;
            }

            .fc-minimal .fc-day-other {
                background-color: transparent !important;
                opacity: 0.3;
            }

            .fc-minimal .fc-daygrid-day.fc-day-today {
                background-color: rgba(14, 165, 233, 0.04) !important;
            }

            .fc-minimal .fc-header-toolbar {
                margin-bottom: 0.4rem !important;
                padding: 0 4px;
            }

            /* Event Styling */
            .fc-event-booked {
                background-color: var(--booked-bg) !important;
                border: none !important;
                color: white !important;
                font-size: 0.55rem !important;
                font-weight: 800 !important;
                text-align: center !important;
                padding: 2px 0 !important;
                border-radius: 3px !important;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }

            .fc-event-pending {
                background-color: var(--pending-bg) !important;
                border: none !important;
                color: white !important;
                font-size: 0.55rem !important;
                font-weight: 800 !important;
                text-align: center !important;
                padding: 2px 0 !important;
                border-radius: 3px !important;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }

            .fc-event-request {
                background-color: var(--request-bg) !important;
                border: none !important;
                color: white !important;
                font-size: 0.55rem !important;
                font-weight: 800 !important;
                text-align: center !important;
                padding: 2px 0 !important;
                border-radius: 3px !important;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }

            .fc-event-checkedin {
                background-color: var(--checkedin-bg) !important;
                border: none !important;
                color: white !important;
                font-size: 0.55rem !important;
                font-weight: 800 !important;
                text-align: center !important;
                padding: 2px 0 !important;
                border-radius: 3px !important;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }

            /* Modal Styling */
            .booking-modal .modal-header {
                background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
                color: #fff;
                border-bottom: none;
                border-radius: 0.5rem 0.5rem 0 0;
            }

            .booking-modal .modal-header .btn-close {
                filter: brightness(0) invert(1);
            }

            .booking-modal .modal-content {
                overflow: visible;
            }

            .booking-modal .modal-body {
                padding: 1.5rem;
            }

            .booking-modal .section-divider {
                font-size: 0.75rem;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 0.75rem;
                padding-bottom: 0.4rem;
                border-bottom: 2px solid #e2e8f0;
            }

            .booking-modal .form-label {
                font-size: 0.8rem;
                font-weight: 600;
                color: #475569;
            }

            .booking-modal .modal-footer {
                border-top: 1px solid #e2e8f0;
                padding: 1rem 1.5rem;
            }

            /* Customer Booking Modal — full modal scrolls in viewport */
            #customerBookingModal {
                overflow-y: auto !important;
            }

            #customerBookingModal .modal-dialog {
                max-width: min(1080px, 96vw);
                margin: 1.75rem auto;
            }

            #customerBookingModal .modal-content {
                border-radius: 0.75rem !important;
                overflow: visible;
            }

            #customerBookingModal .modal-body {
                padding: 1.25rem 1.5rem 1.5rem;
            }

            #customerBookingModal .modal-footer {
                background: #fff;
                border-top: 1px solid #e2e8f0;
                padding: 0.75rem 1.5rem;
            }

            /* Responsive */
            @media (max-width: 1200px) {
                .calendar-dashboard-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 10px;
                }
            }

            @media (max-width: 768px) {
                .calendar-dashboard-grid {
                    grid-template-columns: 1fr;
                }

                .booking-input-bar {
                    padding: 1rem;
                }

                #customerBookingModal .modal-dialog {
                    max-width: calc(100vw - 1rem);
                }

                #customerBookingModal .modal-body {
                    padding: 1rem;
                }
            }
        </style>
    @endpush

    @section('content')
        <div class="row mt-3">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-1">Edit listing: #{{ $property->property_id }}. {{ $property->title }} —
                                Details</h4>
                            <p class="text-muted small mb-0">Manage your property availability and bookings.</p>
                        </div>
                        <div>
                            <a href="{{ route('property-availability.index') }}"
                                class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                <i class="fe fe-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Input Bar -->
                        <div class="booking-input-bar">
                            <form id="dateSelectionForm">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->property_id }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label-small">Arrival date <span class="text-danger">*</span></label>
                                        <input type="date" name="check_in" id="check_in" class="form-control bg-white" required>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label-small">Departure date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="check_out" id="check_out" class="form-control bg-white"
                                            required>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label-small">Booking status</label>
                                        <div class="d-flex align-items-center gap-4 mt-1">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="booking_status"
                                                    id="statusBooked" value="confirmed" checked>
                                                <label class="form-check-label fw-semibold" for="statusBooked">Booked</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="booking_status"
                                                    id="statusPending" value="pending">
                                                <label class="form-check-label fw-semibold" for="statusPending">Pending</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="booking_status"
                                                    id="statusRemoved" value="removed">
                                                <label class="form-check-label fw-semibold text-danger"
                                                    for="statusRemoved">Removed</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                            <i class="fe fe-refresh-cw me-1"></i> Update availability
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- 3-Month Calendar Grid -->
                        <div id="multiMonthContainer" class="calendar-dashboard-grid"></div>

                        <!-- Legend -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center flex-wrap gap-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 12px; height: 12px; background: var(--booked-bg); border-radius: 2px;">
                                    </div>
                                    <span class="small fw-semibold text-muted">Booked</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div
                                        style="width: 12px; height: 12px; background: #fff; border: 1px solid var(--calendar-border); border-radius: 2px;">
                                    </div>
                                    <span class="small fw-semibold text-muted">Available</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 12px; height: 12px; background: var(--pending-bg); border-radius: 2px;">
                                    </div>
                                    <span class="small fw-semibold text-muted">Pending</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div
                                        style="width: 12px; height: 12px; background: var(--checkedin-bg); border-radius: 2px;">
                                    </div>
                                    <span class="small fw-semibold text-muted">Checked-in</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="card border mb-0">
                                <div class="card-header bg-light border-bottom d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0 fw-semibold">
                                        <i class="fe fe-users me-2"></i>Booked Customers
                                    </h5>
                                    <span class="badge bg-primary" id="bookedCustomersCount">{{ $bookedCustomers->count() }}</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                    <th>Check-in</th>
                                                    <th>Check-out</th>
                                                    <th>Price</th>
                                                    <th>Payment</th>
                                                    <th>Booking</th>
                                                    <th>Code</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="bookedCustomersTableBody">
                                                @forelse($bookedCustomers as $customerBooking)
                                                    <tr id="booking-row-{{ $customerBooking->booking_id }}">
                                                        <td class="fw-semibold">{{ $customerBooking->guest_name }}</td>
                                                        <td>{{ $customerBooking->guest_email }}</td>
                                                        <td>{{ $customerBooking->guest_phone }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($customerBooking->check_in)->format('Y-m-d') }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($customerBooking->check_out)->format('Y-m-d') }}</td>
                                                        <td>€{{ number_format((float) $customerBooking->grand_total, 2) }}</td>
                                                        <td>
                                                            <span class="badge {{ $customerBooking->payment_status === 'paid' ? 'bg-success' : ($customerBooking->payment_status === 'partially_paid' ? 'bg-warning' : ($customerBooking->payment_status === 'refunded' ? 'bg-secondary' : 'bg-danger')) }}">
                                                                {{ ucfirst(str_replace('_', ' ', $customerBooking->payment_status)) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $customerBooking->booking_status === 'confirmed' ? 'bg-danger' : ($customerBooking->booking_status === 'pending' ? 'bg-warning' : ($customerBooking->booking_status === 'checked_in' ? 'bg-primary' : ($customerBooking->booking_status === 'completed' ? 'bg-success' : 'bg-secondary'))) }}">
                                                                {{ ucfirst(str_replace('_', ' ', $customerBooking->booking_status)) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $customerBooking->booking_code }}</td>
                                                        <td>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary edit-customer-status-btn"
                                                                data-booking-id="{{ $customerBooking->booking_id }}"
                                                                data-booking-status="{{ $customerBooking->booking_status }}"
                                                                data-payment-status="{{ $customerBooking->payment_status }}"
                                                                data-guest-name="{{ $customerBooking->guest_name }}"
                                                                title="Edit Status">
                                                                <i class="fe fe-edit"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr id="noBookedCustomersRow">
                                                        <td colspan="10" class="text-center text-muted py-4">No customer bookings found for this property yet.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade booking-modal" id="customerBookingModal" tabindex="-1" aria-labelledby="customerBookingModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <form id="customerBookingForm">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $property->property_id }}">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="customerBookingModalLabel">
                                    <i class="fe fe-user-plus me-2"></i> Customer Booking Details
                                </h5>
                                <small class="opacity-75">Add customer information and save booking to calendar</small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="guest_name" id="customer_guest_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="guest_email" id="customer_guest_email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="guest_phone" id="customer_guest_phone" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="guest_address" id="customer_guest_address" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Check-in <span class="text-danger">*</span></label>
                                    <input type="date" name="check_in" id="customer_check_in" class="form-control bg-light" readonly required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Check-out <span class="text-danger">*</span></label>
                                    <input type="date" name="check_out" id="customer_check_out" class="form-control bg-light" readonly required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Adults</label>
                                    <input type="number" name="number_adults" class="form-control" value="1" min="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Children</label>
                                    <input type="number" name="number_children" class="form-control" value="0" min="0">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                                    <select name="payment_status" class="form-select" required>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="partially_paid">Partially Paid</option>
                                        <option value="paid">Paid</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Booking Status <span class="text-danger">*</span></label>
                                    <select name="booking_status" id="customer_booking_status" class="form-select" required>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="pending">Pending</option>
                                        <option value="checked_in">Checked In</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="no_show">No Show</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="manual_cash">Manual / Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="stripe">Stripe</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="others">Others</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Note</label>
                                    <textarea name="internal_notes" class="form-control" rows="2"></textarea>
                                </div>

                                <div class="col-12">
                                    <div class="card border mt-2 mb-0">
                                        <div class="card-header bg-light py-2">
                                            <h6 class="mb-0 fw-semibold">Pricing Breakdown</h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <th class="ps-3">Nights</th>
                                                            <td class="text-end pe-3" id="customer_nights_display">-</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-3">Subtotal</th>
                                                            <td class="text-end pe-3" id="customer_subtotal_display">-</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-3">Discount</th>
                                                            <td class="text-end pe-3 text-success" id="customer_discount_display">-</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-3">Addon</th>
                                                            <td class="text-end pe-3" id="customer_addon_display">-</td>
                                                        </tr>
                                                        <tr class="table-light">
                                                            <th class="ps-3">Grand Total</th>
                                                            <td class="text-end pe-3 fw-bold" id="customer_grand_total_display">-</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fe fe-x me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary fw-bold" id="saveCustomerBookingBtn">
                                <i class="fe fe-save me-1"></i> Save Booking
                            </button>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /#customerBookingModal -->

        <div class="modal fade booking-modal" id="editCustomerStatusModal" tabindex="-1" aria-labelledby="editCustomerStatusModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 0.5rem;">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="editCustomerStatusModalLabel">
                                <i class="fe fe-edit me-2"></i>Edit Customer Status
                            </h5>
                            <small class="opacity-75" id="editCustomerStatusGuestName">Update booking and payment status</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editCustomerStatusForm">
                        @csrf
                        <input type="hidden" id="edit_status_booking_id">
                        <input type="hidden" id="edit_status_property_id" value="{{ $property->property_id }}">

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Booking Status <span class="text-danger">*</span></label>
                                    <select id="edit_status_booking_status" class="form-select" required>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="pending">Pending</option>
                                        <option value="checked_in">Checked In</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="no_show">No Show</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                                    <select id="edit_status_payment_status" class="form-select" required>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="partially_paid">Partially Paid</option>
                                        <option value="paid">Paid</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="updateCustomerStatusBtn">
                                <i class="fe fe-save me-1"></i>Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endsection

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script>
            $(document).ready(function () {
                const propertyId = "{{ $property->property_id }}";
                const currency = "€";
                const basePrice = Number("{{ (float) $property->price }}");
                const currentYear = new Date().getFullYear();
                const currentMonth = new Date().getMonth();
                const calendars = [];

                function escapeHtml(text) {
                    return $('<div>').text(text ?? '').html();
                }

                function bookingStatusBadgeClass(status) {
                    if (status === 'confirmed') return 'bg-danger';
                    if (status === 'pending') return 'bg-warning';
                    if (status === 'checked_in') return 'bg-primary';
                    if (status === 'completed') return 'bg-success';
                    return 'bg-secondary';
                }

                function paymentStatusBadgeClass(status) {
                    if (status === 'paid') return 'bg-success';
                    if (status === 'partially_paid') return 'bg-warning';
                    if (status === 'refunded') return 'bg-secondary';
                    return 'bg-danger';
                }

                function appendBookedCustomerRow(booking) {
                    if (!booking || !booking.booking_id) return;

                    $('#noBookedCustomersRow').remove();

                    const bookingStatus = String(booking.booking_status || '').replace(/_/g, ' ');
                    const paymentStatus = String(booking.payment_status || '').replace(/_/g, ' ');

                    const rowHtml = `
                        <tr id="booking-row-${booking.booking_id}">
                            <td class="fw-semibold">${escapeHtml(booking.guest_name)}</td>
                            <td>${escapeHtml(booking.guest_email)}</td>
                            <td>${escapeHtml(booking.guest_phone)}</td>
                            <td>${escapeHtml(booking.check_in)}</td>
                            <td>${escapeHtml(booking.check_out)}</td>
                            <td>${escapeHtml(currency)}${Number(booking.grand_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td><span class="badge ${paymentStatusBadgeClass(booking.payment_status)}">${escapeHtml(paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1))}</span></td>
                            <td><span class="badge ${bookingStatusBadgeClass(booking.booking_status)}">${escapeHtml(bookingStatus.charAt(0).toUpperCase() + bookingStatus.slice(1))}</span></td>
                            <td>${escapeHtml(booking.booking_code)}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary edit-customer-status-btn"
                                    data-booking-id="${escapeHtml(booking.booking_id)}"
                                    data-booking-status="${escapeHtml(booking.booking_status)}"
                                    data-payment-status="${escapeHtml(booking.payment_status)}"
                                    data-guest-name="${escapeHtml(booking.guest_name)}"
                                    title="Edit Status">
                                    <i class="fe fe-edit"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    const existingRow = $(`#booking-row-${booking.booking_id}`);
                    if (existingRow.length) {
                        existingRow.replaceWith(rowHtml);
                    } else {
                        $('#bookedCustomersTableBody').prepend(rowHtml);
                    }

                    $('#bookedCustomersCount').text($('#bookedCustomersTableBody tr').not('#noBookedCustomersRow').length);
                }

                $(document).on('click', '.edit-customer-status-btn', function () {
                    const bookingId = $(this).data('booking-id');
                    const bookingStatus = $(this).data('booking-status');
                    const paymentStatus = $(this).data('payment-status');
                    const guestName = $(this).data('guest-name') || 'Customer';

                    $('#edit_status_booking_id').val(bookingId);
                    $('#edit_status_booking_status').val(bookingStatus);
                    $('#edit_status_payment_status').val(paymentStatus);
                    $('#editCustomerStatusGuestName').text('Customer: ' + guestName);

                    const modal = new bootstrap.Modal(document.getElementById('editCustomerStatusModal'));
                    modal.show();
                });

                $('#editCustomerStatusForm').on('submit', function (e) {
                    e.preventDefault();

                    const bookingId = $('#edit_status_booking_id').val();
                    const bookingStatus = $('#edit_status_booking_status').val();
                    const paymentStatus = $('#edit_status_payment_status').val();
                    const propertyIdFromModal = $('#edit_status_property_id').val();
                    const $btn = $('#updateCustomerStatusBtn');
                    const originalHtml = $btn.html();

                    $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Updating...');

                    $.ajax({
                        url: "{{ route('property-availability.update-customer-status', ':id') }}".replace(':id', bookingId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            property_id: propertyIdFromModal,
                            booking_status: bookingStatus,
                            payment_status: paymentStatus,
                        },
                        success: function (resp) {
                            $btn.prop('disabled', false).html(originalHtml);
                            if (resp['t-success']) {
                                toastr.success(resp.message);
                                if (resp.booking) {
                                    appendBookedCustomerRow(resp.booking);
                                }
                                calendars.forEach(c => c.refetchEvents());
                                bootstrap.Modal.getInstance(document.getElementById('editCustomerStatusModal')).hide();
                            } else {
                                toastr.error(resp.message || 'Failed to update status.');
                            }
                        },
                        error: function (xhr) {
                            $btn.prop('disabled', false).html(originalHtml);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                Object.values(xhr.responseJSON.errors).forEach(err => toastr.error(err[0]));
                            } else {
                                toastr.error('Failed to update status.');
                            }
                        }
                    });
                });

                function formatMoney(value) {
                    return currency + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function calculateCustomerPricing() {
                    const checkIn = $('#customer_check_in').val();
                    const checkOut = $('#customer_check_out').val();
                    const adults = parseInt($('#customerBookingForm input[name="number_adults"]').val()) || 1;
                    const children = parseInt($('#customerBookingForm input[name="number_children"]').val()) || 0;
                    const guests = Math.max(1, adults + children);

                    if (!checkIn || !checkOut) return;

                    const payload = {
                        _token: '{{ csrf_token() }}',
                        property_id: propertyId,
                        check_in: checkIn,
                        check_out: checkOut,
                        no_of_guests: guests,
                    };

                    $.ajax({
                        url: "{{ route('property-availability.calculate-price') }}",
                        type: "POST",
                        data: payload,
                        success: function (resp) {
                            if (!resp.success || !resp.data) {
                                return;
                            }

                            const data = resp.data;

                            $('#customer_subtotal_display').text(formatMoney(data.subtotal));
                            $('#customer_nights_display').text(data.nights ?? '-');
                            $('#customer_discount_display').text('-' + formatMoney(data.discount_amount));
                            $('#customer_addon_display').text('+' + formatMoney(data.addon_total));
                            $('#customer_grand_total_display').text(formatMoney(data.grand_total));
                        },
                        error: function () {
                            toastr.error('Failed to calculate pricing.');
                        }
                    });
                }

                // ── Render 3 months ──
                for (let i = 0; i < 5; i++) {
                    const monthDate = new Date(currentYear, currentMonth + i, 1);
                    const monthId = `calendar-${i}`;

                    $('#multiMonthContainer').append(`<div class="calendar-item-wrapper" id="${monthId}"></div>`);

                    const calendarEl = document.getElementById(monthId);
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        initialDate: monthDate,
                        headerToolbar: {
                            left: '',
                            center: 'title',
                            right: ''
                        },
                        events: "{{ route('property-availability.events', $property->property_id) }}",
                        height: 300,
                        themeSystem: 'standard',
                        dayMaxEvents: true,
                        fixedWeekCount: false,
                        eventDidMount: function (info) {
                            const status = info.event.extendedProps.status;
                            const guest = info.event.extendedProps.guest || info.event.title || 'Customer';

                            if (status === 'confirmed') {
                                info.el.classList.add('fc-event-booked');
                                info.el.innerText = 'BOOKED - ' + guest;
                            } else if (status === 'checked_in') {
                                info.el.classList.add('fc-event-checkedin');
                                info.el.innerText = 'CHECKED-IN - ' + guest;
                            } else if (status === 'pending') {
                                info.el.classList.add('fc-event-pending');
                                info.el.innerText = 'PENDING - ' + guest;
                            } else {
                                info.el.classList.add('fc-event-booked');
                                info.el.innerText = status.toUpperCase().replace('_', '-') + ' - ' + guest;
                            }
                        },
                        viewDidMount: function () {
                            $(calendarEl).addClass('fc-minimal');
                        }
                    });

                    calendar.render();
                    calendars.push(calendar);
                }

                $('#dateSelectionForm').on('submit', function (e) {
                    e.preventDefault();

                    const checkIn = $('#check_in').val();
                    const checkOut = $('#check_out').val();
                    const bookingStatus = $('input[name="booking_status"]:checked').val();

                    if (!checkIn || !checkOut) {
                        toastr.error('Please select both arrival and departure dates.');
                        return;
                    }

                    // ── If "Removed", submit directly (no modal) ──
                    if (bookingStatus === 'removed') {
                        $.ajax({
                            url: "{{ route('property-availability.store') }}",
                            type: "POST",
                            data: $(this).serialize(),
                            success: function (resp) {
                                if (resp['t-success']) {
                                    toastr.success(resp.message);
                                    calendars.forEach(c => c.refetchEvents());
                                    $('#dateSelectionForm')[0].reset();
                                } else {
                                    toastr.error(resp.message);
                                }
                            },
                            error: function (xhr) {
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    Object.values(xhr.responseJSON.errors).forEach(err => toastr.error(err[0]));
                                } else {
                                    toastr.error('An unexpected error occurred.');
                                }
                            }
                        });
                        return;
                    }

                    $('#customer_check_in').val(checkIn);
                    $('#customer_check_out').val(checkOut);
                    $('#customer_booking_status').val(bookingStatus);
                    $('#customer_guest_name').val('');
                    $('#customer_guest_email').val('');
                    $('#customer_guest_phone').val('');
                    $('#customer_guest_address').val('');

                    $('#customerBookingForm input[name="number_adults"]').val(1);
                    $('#customerBookingForm input[name="number_children"]').val(0);
                    $('#customer_subtotal_display').text('-');
                    $('#customer_nights_display').text('-');
                    $('#customer_discount_display').text('-');
                    $('#customer_addon_display').text('-');
                    $('#customer_grand_total_display').text('-');

                    const modal = new bootstrap.Modal(document.getElementById('customerBookingModal'));
                    modal.show();

                    setTimeout(function () {
                        calculateCustomerPricing();
                    }, 200);
                });

                $('#customerBookingForm input[name="number_adults"], #customerBookingForm input[name="number_children"]').on('change keyup', function () {
                    calculateCustomerPricing();
                });



                $('#customerBookingForm').on('submit', function (e) {
                    e.preventDefault();

                    const $btn = $('#saveCustomerBookingBtn');
                    const originalHtml = $btn.html();
                    $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Saving...');

                    $.ajax({
                        url: "{{ route('property-availability.store') }}",
                        type: "POST",
                        data: $(this).serialize(),
                        success: function (resp) {
                            $btn.prop('disabled', false).html(originalHtml);
                            if (resp['t-success']) {
                                toastr.success(resp.message);
                                calendars.forEach(c => c.refetchEvents());
                                if (resp.booking) {
                                    appendBookedCustomerRow(resp.booking);
                                }
                                $('#dateSelectionForm')[0].reset();
                                $('#customerBookingForm')[0].reset();
                                bootstrap.Modal.getInstance(document.getElementById('customerBookingModal')).hide();
                            } else {
                                toastr.error(resp.message);
                            }
                        },
                        error: function (xhr) {
                            $btn.prop('disabled', false).html(originalHtml);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                Object.values(xhr.responseJSON.errors).forEach(err => toastr.error(err[0]));
                            } else {
                                toastr.error('An unexpected error occurred.');
                            }
                        }
                    });
                });

            });
        </script>
    @endpush
