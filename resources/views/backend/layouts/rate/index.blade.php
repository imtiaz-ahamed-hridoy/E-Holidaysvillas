@extends('backend.app')

@section('title', 'Rates')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title fs-2">Rates List</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Table</a></li>
                <li class="breadcrumb-item active" aria-current="page">Rates</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom"
                    style="margin-bottom: 0; display: flex; justify-content: space-between;">
                    <h3 class="card-title">Manage Rates</h3>
                    <a href="{{ route('rate.create') }}" class="btn btn-primary">Create New Rate</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100" id="datatable">
                            <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">#</th>
                                    <th class="wd-15p border-bottom-0">Property</th>
                                    <th class="wd-15p border-bottom-0">Unit Type</th>
                                    <th class="wd-15p border-bottom-0">Nightly Rate</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- dynamic data --}}
                            </tbody>
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
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

            if (!$.fn.DataTable.isDataTable('#datatable')) {
                let dTable = $('#datatable').DataTable({
                    order: [],
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    processing: true,
                    responsive: true,
                    serverSide: true,

                    language: {
                        processing: `<div class="text-center">
                                                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                              </div>
                                                </div>`
                    },

                    scroller: {
                        loadingIndicator: false
                    },
                    pagingType: "full_numbers",
                    dom: "<'row justify-content-between table-topbar'<'col-md-2 col-sm-4 px-0'l><'col-md-2 col-sm-4 px-0'f>>tipr",
                    ajax: {
                        url: "{{ route('rate.index') }}",
                        type: "GET",
                    },

                    columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'property',
                        name: 'property',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'unit_type',
                        name: 'unit_type',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'nightly_rate',
                        name: 'nightly_rate',
                    }, {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }],
                });

                dTable.buttons().container().appendTo('#file_exports');
            }
        });

        function statusToggle(id) {
            $.ajax({
                type: "GET",
                url: "{{ route('rate.status', ':id') }}".replace(':id', id),
                success: function (resp) {
                    if (resp['success']) {
                        toastr.success(resp.message);
                    } else {
                        toastr.error(resp.message);
                    }
                    $('#datatable').DataTable().ajax.reload();
                }
            });
        }

        function deleteData(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('rate.destroy', ':id') }}".replace(':id', id),
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (resp) {
                            $('#datatable').DataTable().ajax.reload();
                            if (resp['success']) {
                                toastr.success(resp.message);
                            } else {
                                toastr.error(resp.message);
                            }
                        },
                        error: function () {
                            toastr.error('An error occurred. Please try again.');
                        }
                    });
                }
            });
        }
    </script>
@endpush