<!DOCTYPE html>
<html>

<head>
    <title>Defect Detection</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"
        integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 40px;
            max-width: 1100px;
        }

        .card {
            background-color: #fff;
            border: 1px solid #e1e4e8;
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .defect-card {
            background-color: #fff;
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            margin-bottom: 16px;
            padding: 0;
            overflow: hidden;
        }

        .defect-card-header {
            background: #f9f9f9;
            border-bottom: 1px solid #e1e4e8;
            padding: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .defect-card-body {
            padding: 16px;
        }

        .defect-card-footer {
            padding: 12px 16px;
            border-top: 1px solid #e9ecef;
            background: #fff;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .requirement {
            font-weight: bold;
            color: #333;
        }

        .defect {
            background-color: #fff3cd;
            border-left: 6px solid #ffe066;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
        }

        .actions {
            margin-top: 10px;
        }

        .btn-auto-fix {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            min-width: 120px;
        }

        .btn-ignore {
            min-width: 120px;
        }

        .form-control {
            margin-top: 10px;
        }

        .top-left-menu-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        .navbar .nav-link.active {
            color: #007bff !important;
            font-weight: bold;
            text-decoration: underline;
        }

        .navbar .nav-link {
            transition: color 0.3s ease-in-out;
        }

        .navbar .nav-link:hover {
            color: #0056b3 !important;
        }

        .requirement-section {
            background-color: #f9f9f9;
            border-left: 4px solid #007bff;
        }

        .requirement-section .requirement {
            color: #007bff;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .navbar .nav-link {
            transition: all 0.3s ease-in-out;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
            border-radius: 6px;
        }
        .navbar .nav-link:hover {
            background-color: #e9ecef;
            color: #0056b3 !important;
        }
        .navbar .nav-link.active {
            background-color: #007bff;
            color: white !important;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
        }
        .navbar .dropdown-toggle.active {
            background-color: #007bff;
            color: white !important;
        }
        .navbar .nav-item {
            margin: 0 0.2rem;
        }
        .navbar {
            background-color: #f8f9fa !important;
            border-bottom: 2px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
</head>

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="/">AI-Driven Requirement Management</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <!-- Home -->
                <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                    <a class="nav-link" href="/">Home</a>
                </li>

                <!-- Projects Dropdown -->
                <li class="nav-item dropdown {{ Request::is('view-project*') ? 'active' : '' }}">
                    <a class="nav-link dropdown-toggle" href="#" id="projectsDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Projects
                    </a>
                    <div class="dropdown-menu" aria-labelledby="projectsDropdown">
                        <a class="dropdown-item" href="/view-project">View Projects</a>
                        @auth
                            @if(auth()->user()->role !== 'software engineer')
                                <a class="dropdown-item" href="/add-project">Create Project</a>
                            @endif
                            @if(auth()->user()->role === 'software engineer')
                                <a class="dropdown-item" href="/submit-requirements">Add Requirement</a>
                                <a class="dropdown-item" href="/add-feature">Add Feature</a>
                            @endif
                        @endauth
                    </div>
                </li>

                <!-- Conflicts -->
                <li class="nav-item {{ Request::is('view-conflict') ? 'active' : '' }}">
                    <a class="nav-link" href="/view-conflict">Conflicts</a>
                </li>

                @auth
                    @if(auth()->user()->role === 'software engineer')
                        <li class="nav-item {{ Request::is('my-changes') ? 'active' : '' }}">
                            <a class="nav-link" href="/my-changes">Pending Approval</a>
                        </li>
                    @endif
                @endauth
                <!-- Defects -->
                <li class="nav-item {{ Request::is('review-defects') ? 'active' : '' }}">
                    <a class="nav-link" href="/review-defects">Defects</a>
                </li>

                <!-- Change Log -->
                <li class="nav-item {{ Request::is('change-log') ? 'active' : '' }}">
                    <a class="nav-link" href="/change-log">Change Log</a>
                </li>

                <!-- in your navbar -->
                @auth
                    @if(auth()->user()->role !== 'software engineer')
                        <li class="nav-item {{ Request::is('review-changes') ? 'active' : '' }}">
                            <a class="nav-link" href="/review-changes">
                                Review Changes

                                {{-- Notification badge for pending updates --}}
                                @php
                                    $pendingCount = \App\Models\PendingUpdate::where('status', 'Pending')->count();
                                    $pendingResolutionsCount = \App\Models\PendingResolution::where('status', 'Pending')->count();
                                    $totalCount = $pendingCount + $pendingResolutionsCount;
                                  @endphp

                                @if($totalCount > 0)
                                    <span class="badge badge-danger ml-1" style="font-size: 0.7rem; vertical-align: middle;">
                                        {{ $totalCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                @endauth
                <!-- Authentication -->
                @if(auth()->check())
                    <!-- User Profile -->
                    <li class="nav-item {{ Request::is('profile') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('profile.show') }}">
                            <i class="fa fa-user-circle"></i> {{ Auth::user()->name }}
                        </a>
                    </li>
                    <!-- Logout -->
                    <li class="nav-item">
                        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                            @csrf
                        </form>
                        <a href="#" class="nav-link text-danger"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa fa-sign-out"></i> Logout
                        </a>
                    </li>
                @else
                    <li class="nav-item {{ Request::is('login') ? 'active' : '' }}">
                        <a class="nav-link" href="/login">Login</a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<body>
    <div class="header-container">
        <h4 class="text-center font-weight-bold">Defect Detection</h4>
    </div>

    <div class="container">

        @php
            // Flatten defects for individual card display
            // $requirements is now passed from controller (already filtered for engineers)
            $allDefects = [];
            foreach ($requirements as $req) {
                foreach ($req->defects as $defect) {
                    $allDefects[] = ['requirement' => $req, 'defect' => $defect];
                }
            }
        @endphp

        @forelse($allDefects as $item)
            @php
                $requirement = $item['requirement'];
                $defect = $item['defect'];
                $projectId = $requirement->project_id;
                $featureId = $requirement->feature_id;
                $feature = $requirement->feature;
                $project = $requirement->project;

                // Get project number
                $allProjects = App\Models\Project::orderBy('id')->get();
                $projectNumber = 0;
                foreach ($allProjects as $proj) {
                    $projectNumber++;
                    if ($proj->id === $projectId)
                        break;
                }

                // Build feature index map
                $allFeaturesInProject = App\Models\ProjectFeature::where('project_id', $projectId)->orderBy('id')->get();
                $featureIndexMap = [];
                $fi = 0;
                foreach ($allFeaturesInProject as $pf) {
                    $fi++;
                    $featureIndexMap[$pf->id] = $fi;
                }

                $featureIndex = $featureIndexMap[$featureId] ?? '0';
                $reqsInFeature = App\Models\Requirement::where('project_id', $projectId)
                    ->where('feature_id', $featureId)
                    ->orderBy('id')
                    ->get();
                $reqCounter = 0;
                foreach ($reqsInFeature as $r) {
                    $reqCounter++;
                    if ($r->id === $requirement->id)
                        break;
                }
                $reqNumber = $projectNumber . '.' . $featureIndex . '.' . $reqCounter;
            @endphp

            <div class="defect-card shadow-sm">
                <!-- Header with Requirement Number -->
                <div class="defect-card-header">
                    <div>
                        <h5 class="mb-1 font-weight-bold">Requirement {{ $reqNumber }}</h5>
                        <small class="text-muted">{{ $project->name ?? 'Unknown Project' }}</small>
                    </div>
                    <span class="badge badge-warning px-3 py-2" style="font-size: 0.9rem;">
                        Type: {{ ucfirst($defect->type) }}
                    </span>
                </div>

                <!-- Body with Project, Feature, and Requirement Details -->
                <div class="defect-card-body">
                    <div class="mb-3">
                        <strong>Feature:</strong>
                        <p class="text-muted">{{ $feature->feature }} <small>(Category: {{ $feature->category }})</small>
                        </p>
                    </div>

                    <div class="mb-3">
                        <strong>Requirement Description:</strong>
                        <p class="text-muted">{{ $requirement->description }}</p>
                    </div>

                    <!-- Defect Details -->
                    <div class="defect">
                        <strong>Defect Description:</strong> {{ $defect->description }}<br>
                        <strong>Defect Type:</strong> {{ $defect->type }}<br>
                        <strong>Suggestion:</strong> {{ $defect->suggestion ?? 'No suggestion provided.' }}<br>
                    </div>
                </div>

                <!-- Footer with Action Buttons -->
                <div class="defect-card-footer">
                    <button class="btn btn-success btn-auto-fix" data-requirement-id="{{ $requirement->id }}"
                        data-defect-id="{{ $defect->id }}" data-suggestion="{{ $defect->suggestion }}"
                        data-field="description">Auto-Fix</button>
                    <button class="btn btn-outline-danger btn-ignore" data-defect-id="{{ $defect->id }}">Ignore</button>
                </div>
            </div>
        @empty
            <div class="alert alert-info text-center">
                <p>No defects detected.</p>
            </div>
        @endforelse
    </div>
    <div class="modal fade" id="fixModal" tabindex="-1" role="dialog" aria-labelledby="fixModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fixModalLabel">Requirement Auto-Fix Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Original:</strong></p>
                    <div id="original-text" class="p-2 bg-light border rounded mb-3"></div>

                    <p><strong>Suggested Fix Applied:</strong></p>
                    <div id="new-text" class="p-2 bg-white border rounded"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="confirm-fix">Apply Fix</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Ignore Defect Confirmation Modal -->
    <div class="modal fade" id="ignoreDefectModalReview" tabindex="-1" role="dialog"
        aria-labelledby="ignoreDefectModalReviewLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ignoreDefectModalReviewLabel">Confirm Ignore Defect</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="ignore-defect-message">Are you sure you want to ignore this defect?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-ignore-defect-review" class="btn btn-danger">Ignore
                        Defect</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Engineer Success Modal -->
    <div class="modal fade" id="engineerSuccessModal" tabindex="-1" role="dialog" aria-labelledby="engineerSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="engineerSuccessModalLabel"><i class="fa fa-check-circle"></i> Submitted for Approval</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fa fa-check-circle text-success" style="font-size: 2.5rem;"></i>
                    <p class="mt-3 mb-0" id="engineerSuccessMessage" style="font-size: 1.05rem;"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let currentButton = null;
        let requirementId = null;
        let defectId = null;
        let suggestion = null;
        let field = null;
        let originalText = null;

        // Auto-fix button click: Show modal and set context
        document.querySelectorAll('.btn-auto-fix').forEach(button => {
            button.addEventListener('click', function () {
                currentButton = this;
                requirementId = this.getAttribute('data-requirement-id');
                defectId = this.getAttribute('data-defect-id');
                suggestion = this.getAttribute('data-suggestion');
                field = this.getAttribute('data-field');

                // Get original text from defect card
                const card = currentButton.closest('.defect-card');
                const descriptionP = card.querySelector('.defect-card-body > div:nth-child(2) p'); // Requirement Description
                originalText = descriptionP ? descriptionP.innerText.trim() : '';

                // Populate modal with current and suggested values
                document.getElementById('original-text').textContent = originalText;
                document.getElementById('new-text').textContent = suggestion || "No suggestion available.";
                $('#fixModal').modal('show');
            });
        });

        // Confirm fix: Perform fetch to apply fix
        document.getElementById('confirm-fix').addEventListener('click', function () {
            if (currentButton) {

                const confirmButton = document.getElementById('confirm-fix');
                confirmButton.disabled = true;
                confirmButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Applying...`;

                fetch('/auto-fix-requirement', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        requirement_id: requirementId,
                        suggestion: suggestion,
                        field: field,
                        defect_id: defectId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.updated || data.pending) {
                            const card = currentButton.closest('.defect-card');
                            
                            if (data.updated) {
                                // For project managers: show the updated value
                                const descriptionP = card.querySelector('.defect-card-body > div:nth-child(2) p');
                                if (descriptionP) {
                                    descriptionP.innerHTML = `<span class="text-success">${data.new_value}</span>`;
                                }
                                currentButton.innerText = "Fixed ✔";
                            } else if (data.pending) {
                                // For software engineers: show pending status
                                currentButton.innerText = "Pending Approval";
                                currentButton.classList.remove('btn-success');
                                currentButton.classList.add('btn-warning');
                            }

                            // Remove defects and actions from card body
                            card.querySelectorAll('.defect, .actions').forEach(el => el.remove());

                            // Disable button
                            currentButton.disabled = true;

                            $('#fixModal').modal('hide');
                            
                            // Show success message via modal for engineers
                            if (data.message) {
                                $('#engineerSuccessMessage').text(data.message);
                                $('#engineerSuccessModal').modal('show');
                            }
                        } else {
                            alert("Failed to apply fix.");
                        }
                    })
                    .catch(err => {
                        console.error("Error applying fix:", err);
                        alert("An error occurred while applying the fix.");
                    })
                    .finally(() => {
                        // Reset loading state
                        confirmButton.disabled = false;
                        confirmButton.innerHTML = "Apply Fix";
                    });
            }
        });

        // Clear modal and reset data on cancel
        $('#fixModal').on('hidden.bs.modal', function () {
            currentButton = null;
            requirementId = null;
            defectId = null;
            suggestion = null;
            field = null;
            originalText = null;
        });

        // Ignore defect: show Bootstrap modal, then send AJAX and remove the entire feature card on success
        let defectToIgnore = null; // will hold { defectId, btn }

        document.querySelectorAll('.btn-ignore').forEach(button => {
            button.addEventListener('click', function () {
                const defectId = this.getAttribute('data-defect-id');
                defectToIgnore = { defectId: defectId, btn: this };
                $('#ignoreDefectModalReview').modal('show');
            });
        });

        document.getElementById('confirm-ignore-defect-review').addEventListener('click', function () {
            if (!defectToIgnore) return;

            const btn = defectToIgnore.btn;
            const confirmBtn = this;
            btn.disabled = true;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Ignoring...`;

            fetch('/ignore-defect', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ defect_id: defectToIgnore.defectId })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.deleted || data.success) {
                        // Remove the entire defect card
                        const card = btn.closest('.defect-card');
                        if (card) {
                            $(card).fadeOut(200, function () { $(this).remove(); });
                        }

                        $('#ignoreDefectModalReview').modal('hide');
                        defectToIgnore = null;
                    } else {
                        throw new Error(data.message || 'Failed to ignore defect.');
                    }
                })
                .catch(err => {
                    console.error('Error ignoring defect:', err);
                    alert('Error: ' + (err.message || 'An error occurred while trying to ignore the defect.'));
                    btn.disabled = false;
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Ignore Defect';
                });
        });

        // Clean up when modal closed without confirming
        $('#ignoreDefectModalReview').on('hidden.bs.modal', function () {
            defectToIgnore = null;
            // Ensure confirm button restored
            const confirmBtn = document.getElementById('confirm-ignore-defect-review');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = 'Ignore Defect';
            }
        });
    });
</script>