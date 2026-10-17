@extends('backend.app')

@section('title', 'Addons')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title fs-2">Addons List</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Manage Property</a></li>
                <li class="breadcrumb-item active" aria-current="page">Addons</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom"
                    style="margin-bottom: 0; display: flex; justify-content: space-between;">
                    <h3 class="card-title">Addons Management</h3>
                    <a href="{{ route('addons.create') }}" class="btn btn-primary">Create New Addon</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100" id="datatable">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">#</th>
                                    <th class="border-bottom-0">Addon Type</th>
                                    <th class="border-bottom-0">Price</th>
                                    <th class="border-bottom-0">Status</th>
                                    <th class="border-bottom-0">Action</th>
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
                $('#datatable').DataTable({
                    order: [],
                    processing: true,
                    responsive: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('addons.index') }}",
                        type: "GET",
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'addon_type', name: 'addon_type' },
                        { data: 'price', name: 'price' },
                        { data: 'status', name: 'status', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false },
                    ],
                });
            }
        });

        function toggleStatus(id) {
            $.ajax({
                type: "GET",
                url: "{{ route('addons.status', ':id') }}".replace(':id', id),
                success: function (resp) {
                    if (resp['t-success']) {
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
                        url: "{{ route('addons.destroy', ':id') }}".replace(':id', id),
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