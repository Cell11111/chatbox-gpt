<!DOCTYPE html>
<html>

<head>
    <title>Submit Requirement</title>
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
            background-color: #f2f5f9;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            max-width: 700px;
            margin: 50px auto;
        }

        .form-container h3 {
            font-weight: bold;
            color: #2c3e50;
        }

        .btn-submit {
            background-color: #3498db;
            color: white;
            font-weight: 500;
        }

        .btn-submit:hover {
            background-color: #2980b9;
        }

        #defect-results {
            margin-top: 30px;
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        #defect-output {
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            max-width: 100%;
        }

        #defect-output table {
            width: 100%;
            table-layout: fixed;
        }

        #defect-output th:first-child,
        #defect-output td:first-child {
            width: 100px;
            min-width: 100px;
        }

        #defect-output th,
        #defect-output td {
            word-break: break-word;
            white-space: normal;
            padding: 10px;
            vertical-align: top;
        }

        .top-left-menu-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
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

        #global-loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
    </style>
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
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
                    @php
                        $pendingCount = \App\Models\PendingUpdate::where('status', 'Pending')->count();
                        $pendingResolutionsCount = \App\Models\PendingResolution::where('status', 'Pending')->count();
                        $totalCount = $pendingCount + $pendingResolutionsCount;
                    @endphp

                    @if(auth()->user()->role !== 'software engineer')
                        <li class="nav-item {{ Request::is('review-changes') ? 'active' : '' }}">
                            <a class="nav-link" href="/review-changes">
                                Review Changes
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
    <div class="form-container">
        <h3 class="text-center mb-4">Submit New Requirement</h3>
        @if(($projects ?? collect())->isEmpty())
            <div class="alert alert-info text-center">
                You are not assigned to any project. Please contact your project manager to be assigned before submitting requirements.
            </div>
        @else
        <form id="requirement-form">
            <div class="form-group">
                <label for="project_id"><strong>Project</strong></label>
                <select id="project_id" name="project_id" class="form-control" required>
                    <option value="">-- Select Project --</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="category"><strong>Category</strong></label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    @foreach ($projectFeatures as $category => $features)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="title"><strong>Feature</strong></label>
                <select id="title" name="title" class="form-control" required>
                    <option value="">-- Select Feature --</option>
                </select>
            </div>
            <div class="form-group">
                <label for="description"><strong>Requirement Description</strong></label>
                <textarea id="description" name="description" class="form-control"
                    placeholder="Enter detailed requirement description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="priority"><strong>Priority</strong></label>
                <select id="priority" name="priority" class="form-control">
                    <option>High</option>
                    <option>Medium</option>
                    <option>Low</option>
                </select>
            </div>
            <button type="submit" class="btn btn-submit btn-block">Submit Requirement</button>
        </form>
        <div id="ai-loading" class="d-none text-center my-3">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Analyzing...</span>
            </div>
            <p class="mt-2">Analyzing requirement with AI...</p>
        </div>
        <div id="defect-results" class="d-none">
            <h5>AI-Detected Defects</h5>
            <div id="defect-output"></div>
        </div>
        <!-- Confirm Submission Buttons (after analysis) -->
        <div id="confirm-section" class="d-none mt-3 text-center">
            <button id="confirmSubmitBtn" class="btn btn-success mr-2">Submit Requirement</button>
            <button id="cancelSubmitBtn" class="btn btn-danger">Cancel</button>
        </div>
        <div id="success-messages" class="mt-3"></div>
        @endif
    </div>
    <!-- Modal for Asking Analysis -->
    <div class="modal fade" id="analysisModal" tabindex="-1" role="dialog" aria-labelledby="analysisModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="analysisModalLabel">Analyze Requirement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Would you like to analyze the saved requirement for potential defects?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="noAnalyzeBtn" data-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" id="analyzeBtn">Yes, Analyze</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Loading Overlay -->
    <div id="global-loading">
        <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem;"></div>
        <p class="mt-3" style="font-size:1.2rem;">Processing your requirement...</p>
    </div>



    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const urlParams = new URLSearchParams(window.location.search);
            const projectId = urlParams.get('project_id');
            const category = urlParams.get('category');
            const featureId = urlParams.get('feature_id');

            function populateCategories(selectedProjectId) {
                const categories = Object.keys(projectFeatures[selectedProjectId] || {});
                $('#category').empty().append('<option value="">-- Select Category --</option>');
                categories.forEach(cat => {
                    $('#category').append(`<option value="${cat}">${cat}</option>`);
                });
            }

            function populateFeatures(selectedProjectId, selectedCategory) {
                const features = projectFeatures[selectedProjectId]?.[selectedCategory] || [];
                $('#title').empty().append('<option value="">-- Select Feature --</option>');
                features.forEach(feat => {
                    $('#title').append(`<option value="${feat.id}">${feat.title}</option>`);
                });
            }

            if (projectId) {
                $('#project_id').val(projectId).change();
                populateCategories(projectId);

                if (category) {
                    $('#category').val(category).change();
                    populateFeatures(projectId, category);

                    if (featureId) {
                        $('#title').val(featureId);
                    }
                }
            }

            $('#project_id').change(function () {
                const selectedProjectId = $(this).val();
                populateCategories(selectedProjectId);
                $('#title').empty().append('<option value="">-- Select Feature --</option>');
            });

            $('#category').change(function () {
                const selectedProjectId = $('#project_id').val();
                const selectedCategory = $(this).val();
                populateFeatures(selectedProjectId, selectedCategory);
            });
        });
        function renderDefects(defects) {
            if (defects.length === 1 && defects[0].type === "None") {
                return "<p>No defects found.</p>";
            }

            let html = '<table class="table table-bordered"><thead><tr><th>Type</th><th>Description</th></tr></thead><tbody>';
            defects.forEach(defect => {
                html += `<tr><td>${defect.type}</td><td>${defect.description}</td></tr>`;
            });
            html += '</tbody></table>';
            return html;
        }

        $(document).ready(function () {
            // Keep pending requirement in window object while user decides
            window.pendingRequirement = null;

            // Prepare: when user clicks "Submit Requirement" on the form
            $('#requirement-form').off('submit').on('submit', function (e) {
                e.preventDefault();

                // Collect values and validate quickly
                const projectId = $('#project_id').val();
                const category = $('#category').val();
                const featureId = $('#title').val();
                const description = $('#description').val().trim();
                const priority = $('#priority').val();
                const title = $('#title option:selected').text();

                if (!projectId || !category || !featureId || !description) {
                    alert("Please select project/category/feature and enter description.");
                    return;
                }

                // store temporarily
                window.pendingRequirement = {
                    project_id: projectId,
                    category: category,
                    feature_id: featureId,
                    description: description,
                    priority: priority,
                    title: title
                };

                // Show modal asking if they want to analyze first
                $('#analysisModal .modal-body').text('Would you like to analyze the requirement for potential defects before saving?');
                $('#analysisModal').modal('show');
            });

            // If user chooses "No" => Save immediately
            $('#noAnalyzeBtn').off('click').on('click', function () {
                $('#analysisModal').modal('hide');
                if (!window.pendingRequirement) {
                    alert('No requirement data found.');
                    return;
                }
                // Save directly
                $.ajax({
                    type: 'POST',
                    url: '/save-requirements',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        title: window.pendingRequirement.title,
                        description: window.pendingRequirement.description,
                        category: window.pendingRequirement.category,
                        priority: window.pendingRequirement.priority,
                        project_id: window.pendingRequirement.project_id,
                        feature_id: window.pendingRequirement.feature_id
                    },
                    success: function (resp) {
                        $('#success-messages').html(`
                    <div class="alert alert-success">
                        Requirement saved successfully.
                    </div>
                `);
                        // clear UI
                        window.pendingRequirement = null;
                        $('#defect-results').addClass('d-none');
                        $('#confirm-section').addClass('d-none');
                        $('#requirement-form')[0].reset();
                    },
                    error: function () {
                        alert('Failed to save requirement.');
                    }
                });
            });

            // If user chooses "Yes, Analyze" => call analyze in preview mode (no DB write)
            $('#analyzeBtn').off('click').on('click', function () {
                $('#analysisModal').modal('hide');

                if (!window.pendingRequirement) {
                    alert('No requirement data found to analyze.');
                    return;
                }

                $('#ai-loading').removeClass('d-none');
                $('#defect-results').addClass('d-none');
                $('#confirm-section').addClass('d-none');
                $('#defect-output').empty();

                $.ajax({
                    type: 'POST',
                    url: '/analyze-requirements', // we updated controller to accept preview
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        requirement: window.pendingRequirement.description,
                        // Do NOT send requirement_id -> indicates preview mode
                    },
                    success: function (data) {
                        $('#ai-loading').addClass('d-none');

                        // data.results expected: [{ requirement_id(optional), defects: [...] }, ... ] or results: [...]
                        // and data.atomic_requirements might be present
                        let outputHtml = '';

                        if (Array.isArray(data.results) && data.results.length > 0) {
                            outputHtml = data.results.map((r, i) => {
                                // r.defects expected to be array
                                const header = `<h6>Requirement ${i + 1}</h6>`;
                                const defectsHtml = (r.defects && r.defects.length) ? renderDefects(r.defects) : '<p>No defects found.</p>';
                                return header + defectsHtml;
                            }).join('');
                        } else if (Array.isArray(data.atomic_requirements)) {
                            // fallback render atomic requirements
                            outputHtml = data.atomic_requirements.map((req, i) => `<h6>Requirement ${i + 1}</h6><p>${req}</p>`).join('');
                        } else {
                            outputHtml = '<p>No analysis results available.</p>';
                        }

                        $('#defect-output').html(outputHtml);
                        $('#defect-results').removeClass('d-none');
                        $('#confirm-section').removeClass('d-none'); // show Confirm / Cancel buttons
                    },
                    error: function (xhr) {
                        $('#ai-loading').addClass('d-none');
                        $('#defect-output').text('Error analyzing the requirement.');
                        $('#defect-results').removeClass('d-none');
                    }
                });
            });

            // Confirm & Save after analysis
            $('#confirmSubmitBtn').off('click').on('click', function () {
                if (!window.pendingRequirement) {
                    alert('Nothing to save.');
                    return;
                }

                $('#global-loading').css('display', 'flex');

                // 1. Save requirement first
                $.ajax({
                    type: 'POST',
                    url: '/save-requirements',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        title: window.pendingRequirement.title,
                        description: window.pendingRequirement.description,
                        category: window.pendingRequirement.category,
                        priority: window.pendingRequirement.priority,
                        project_id: window.pendingRequirement.project_id,
                        feature_id: window.pendingRequirement.feature_id
                    },
                    success: function (resp) {
                        const newReqId = resp.requirement_id;

                        // 2. Now run the REAL analyze (save defects)
                        $.ajax({
                            type: 'POST',
                            url: '/analyze-requirements',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            data: {
                                requirement: window.pendingRequirement.description,
                                requirement_id: newReqId // IMPORTANT
                            },
                            success: function (analysis) {

                                $('#global-loading').hide();
                                $('#success-messages').html(`
                        <div class="alert alert-success">
                            Requirement saved and analyzed successfully, defects stored.
                        </div>
                    `);

                                // Clear UI
                                window.pendingRequirement = null;
                                $('#confirm-section').addClass('d-none');
                                $('#requirement-form')[0].reset();
                            },
                            error: function () {
                                alert('Requirement saved, but analysis failed.');
                            }
                        });
                    },
                    error: function () {
                        alert('Failed to save requirement.');
                    }
                });
            });

            // Cancel after analysis - hide confirm section but keep the form if user wants to edit
            $('#cancelSubmitBtn').off('click').on('click', function () {
                $('#defect-results').addClass('d-none');
                $('#confirm-section').addClass('d-none');
                $('#success-messages').html(`
            <div class="alert alert-warning">Requirement was not saved. You can edit and retry.</div>
        `);
            });

            // renderDefects helper (keeps your existing function)
            function renderDefects(defects) {
                if (!defects || defects.length === 0) return "<p>No defects found.</p>";
                if (defects.length === 1 && defects[0].type === "None") {
                    return "<p>No defects found.</p>";
                }
                let html = '<table class="table table-bordered"><thead><tr><th>Type</th><th>Description</th><th>Suggestion</th></tr></thead><tbody>';
                defects.forEach(defect => {
                    html += `<tr><td>${defect.type}</td><td>${defect.description}</td><td>${defect.suggestion ?? ''}</td></tr>`;
                });
                html += '</tbody></table>';
                return html;
            }
        });
    </script>
    <script>
        const projectFeatures = @json($projectFeatures);

        $(document).ready(function () {
            // Populate categories and features based on selected project
            $('#project_id').change(function () {
                const selectedProjectId = $(this).val();
                const categories = Object.keys(projectFeatures[selectedProjectId] || {});

                // Populate the category dropdown
                $('#category').empty().append('<option value="">-- Select Category --</option>');
                categories.forEach(category => {
                    $('#category').append(`<option value="${category}">${category}</option>`);
                });

                // Clear the feature dropdown initially
                $('#title').empty().append('<option value="">-- Select Feature --</option>');

                // Populate features when a category is selected
                $('#category').change(function () {
                    const selectedCategory = $(this).val();
                    const features = projectFeatures[selectedProjectId][selectedCategory] || [];

                    $('#title').empty().append('<option value="">-- Select Feature --</option>');
                    features.forEach(feature => {
                        $('#title').append(`<option value="${feature.id}">${feature.title}</option>`);
                    });
                });
                $('#title').change(function () {
                    const selectedFeatureName = $(this).find(':selected').data('feature-name');
                    $('#title-text').val(selectedFeatureName); // A hidden input for the feature name if needed
                });
            });
        });


    </script>

</body>

</html>