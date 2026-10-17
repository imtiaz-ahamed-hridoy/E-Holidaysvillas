@extends('backend.app')

@section('title', 'Manage Properties')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title fs-2">Manage Properties</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('property.index') }}">Properties</a></li>
                <li class="breadcrumb-item active" aria-current="page">Manage Properties</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom"
                    style="margin-bottom: 0; display: flex; justify-content: space-between;">
                    <h3 class="card-title">Property List</h3>
                    <a href="{{ route('property.create') }}" class="btn btn-primary">Add New Property</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100" id="datatable">
                            <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">#</th>
                                    <th class="wd-15p border-bottom-0">Title</th>
                                    <th class="wd-15p border-bottom-0">Property Type</th>
                                    <th class="wd-15p border-bottom-0">Unit Type</th>
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
                    ajax: {
                        url: "{{ route('property.index') }}",
                        type: "GET",
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'title', name: 'title' },
                        { data: 'property_type', name: 'property_type' },
                        { data: 'unit_type', name: 'unit_type' },
                        { data: 'status', name: 'status', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false },
                    ],
                });
            }
        });

        function toggleStatus(id) {
            $.ajax({
                type: "GET",
                url: "{{ route('property.status', ':id') }}".replace(':id', id),
                success: function (resp) {
                    $('#datatable').DataTable().ajax.reload();
                    if (resp['t-success']) {
                        toastr.success(resp.message);
                    } else {
                        toastr.error(resp.message);
                    }
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
                        url: "{{ route('property.destroy', ':id') }}".replace(':id', id),
                        data: { _token: "{{ csrf_token() }}" },
                        success: function (resp) {
                            $('#datatable').DataTable().ajax.reload();
                            if (resp['t-success']) {
                                toastr.success(resp.message);
                            } else {
                                toastr.error(resp.message);
                            }
                        }
                    });
                }
            });
        }
    </script>
@endpush