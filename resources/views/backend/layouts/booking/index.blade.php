@extends('backend.app')

@section('title', 'Manage Bookings')

@section('content')
    {{-- PAGE-HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Bookings</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Bookings</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h3 class="card-title">All Bookings</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100" id="datatable">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">#</th>
                                    <th class="border-bottom-0">Code</th>
                                    <th class="border-bottom-0">Guest</th>
                                    <th class="border-bottom-0">Property</th>
                                    <th class="border-bottom-0">Dates</th>
                                    <th class="border-bottom-0">Total</th>
                                    <th class="border-bottom-0">Status</th>
                                    <th class="border-bottom-0">Payment</th>
                                    <th class="border-bottom-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") }
            });

            if (!$.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable({
                    order: [],
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    processing: true,
                    responsive: true,
                    serverSide: true,
                    language: {
                        processing: `<div class="text-center"><div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"><span class="visually-hidden">Loading...</span></div></div>`
                    },
                    pagingType: "full_numbers",
                    dom: "<'row justify-content-between table-topbar'<'col-md-2 col-sm-4 px-0'l><'col-md-2 col-sm-4 px-0'f>>tipr",
                    ajax: {
                        url: "{{ route('bookings.index') }}",
                        type: "GET",
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'booking_code', name: 'booking_code', orderable: true, searchable: true },
                        { data: 'guest', name: 'guest_name', orderable: true, searchable: true },
                        { data: 'property_title', name: 'property_title', orderable: true, searchable: true },
                        { data: 'dates', name: 'check_in', orderable: true, searchable: false },
                        { data: 'grand_total', name: 'grand_total', orderable: true, searchable: false },
                        { data: 'booking_status_badge', name: 'booking_status', orderable: true, searchable: true },
                        { data: 'payment_status_badge', name: 'payment_status', orderable: true, searchable: true },
                        { data: 'action', name: 'action', orderable: false, searchable: false },
                    ],
                });
            }
        });

        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'This booking will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteItem(id);
                }
            });
        }

        function deleteItem(id) {
            let url = "{{ route('bookings.destroy', ':id') }}".replace(':id', id);
            $.ajax({
                type: "DELETE",
                url: url,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (resp) {
                    $('#datatable').DataTable().ajax.reload();
                    resp['t-success'] ? toastr.success(resp.message) : toastr.error(resp.message);
                },
                error: function () { toastr.error('An error occurred.'); }
            });
        }
    </script>
@endpush