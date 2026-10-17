@extends('backend.app')

@section('title', 'Legacy Property Import')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title fs-2">Legacy Property Import</h1>
    </div>
    <div class="ms-auto pageheader-btn">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Legacy Import</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header border-bottom">
                <h3 class="card-title">Upload Legacy SQL File</h3>
            </div>

            <div class="card-body">
                <p class="text-muted mb-3">
                    This importer reads only these legacy tables: <strong>vrs7_listings, vrs7_listing_photos, vrs7_listing_types,
                        vrs7_listing_videos, vrs7_locations, vrs7_rates, vrs7_bathroom_options, vrs7_bedroom_options, vrs7_access_options, vrs7_utilities, vrs7_reviews</strong>.
                    It writes only into: <strong>property_types, properties, locations, property_photos, property_videos, rates, property_facilities, amenities, property_policies, reviews</strong>.
                </p>

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('property-import.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="mb-3">
                        <label for="sql_file" class="form-label">SQL File</label>
                        <input type="file" class="form-control" id="sql_file" name="sql_file" accept=".sql,.txt" required>
                        <small class="text-muted">Max size: 100MB</small>
                    </div>

                    <button type="submit" class="btn btn-primary" id="importBtn">
                        Import Legacy Property Data
                    </button>
                </form>

                {{-- Live Progress Bar --}}
                <div id="progressSection" class="mt-4" style="display: none;">
                    <hr>
                    <h5 class="mb-3"><i class="fe fe-loader"></i> Import Progress</h5>
                    <div class="progress mb-2" style="height: 25px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            0%
                        </div>
                    </div>
                    <p id="progressMessage" class="text-muted mb-0">Waiting...</p>
                </div>

                {{-- Completed / Failed Result --}}
                <div id="resultSection" class="mt-4" style="display: none;">
                    <hr>
                    <div id="resultAlert" class="alert"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        var form = document.getElementById('importForm');
        var importBtn = document.getElementById('importBtn');
        var progressSection = document.getElementById('progressSection');
        var progressBar = document.getElementById('progressBar');
        var progressMessage = document.getElementById('progressMessage');
        var resultSection = document.getElementById('resultSection');
        var resultAlert = document.getElementById('resultAlert');
        var pollTimer = null;

        form.addEventListener('submit', function() {
            // Disable button and show progress section after form submits
            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="fe fe-loader fe-spin"></i> Queuing import...';

            // Small delay to let the form submit, then start polling
            setTimeout(function() {
                progressSection.style.display = 'block';
                startPolling();
            }, 2000);
        });

        function startPolling() {
            pollTimer = setInterval(function() {
                fetch('{{ route("property-import.progress") }}')
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        updateProgress(data);
                    })
                    .catch(function(err) {
                        console.log('Progress poll error:', err);
                    });
            }, 3000); // Poll every 3 seconds
        }

        function updateProgress(data) {
            var percent = data.percent || 0;
            var status = data.status || 'idle';
            var message = data.message || '';

            // Update progress bar
            progressBar.style.width = percent + '%';
            progressBar.setAttribute('aria-valuenow', percent);
            progressBar.textContent = percent + '%';
            progressMessage.textContent = message;

            if (status === 'completed') {
                // Import finished successfully
                clearInterval(pollTimer);
                progressBar.classList.remove('progress-bar-animated', 'bg-primary');
                progressBar.classList.add('bg-success');
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';

                resultSection.style.display = 'block';
                resultAlert.className = 'alert alert-success';
                resultAlert.innerHTML = '<strong><i class="fe fe-check-circle"></i> Import Completed!</strong><br>' + message;

                importBtn.disabled = false;
                importBtn.innerHTML = 'Import Legacy Property Data';

            } else if (status === 'failed') {
                // Import failed
                clearInterval(pollTimer);
                progressBar.classList.remove('progress-bar-animated', 'bg-primary');
                progressBar.classList.add('bg-danger');

                resultSection.style.display = 'block';
                resultAlert.className = 'alert alert-danger';
                resultAlert.innerHTML = '<strong><i class="fe fe-x-circle"></i> Import Failed</strong><br>' + message;

                importBtn.disabled = false;
                importBtn.innerHTML = 'Import Legacy Property Data';

            } else if (status === 'importing') {
                // Still running — update the bar color based on phase
                if (percent > 30) {
                    progressBar.classList.remove('bg-primary');
                    progressBar.classList.add('bg-info');
                }
            }
        }

        // Check if there's already an import running on page load
        fetch('{{ route("property-import.progress") }}')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.status === 'importing') {
                    // An import is already in progress — show progress and start polling
                    progressSection.style.display = 'block';
                    importBtn.disabled = true;
                    importBtn.innerHTML = '<i class="fe fe-loader"></i> Import in progress...';
                    updateProgress(data);
                    startPolling();
                }
            })
            .catch(function() {});
    })();
</script>
@endpush