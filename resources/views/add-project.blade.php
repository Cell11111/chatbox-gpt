<!DOCTYPE html>
<html>

<head>
    <title>Create New Project</title>
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

        h3 {
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
    <div class="form-container">
        <h3 class="text-center mb-4">Create New Project</h3>
        <form id="project-form">
            <div class="form-group">
                <label for="project-name"><strong>Project Name</strong></label>
                <input type="text" id="project-name" class="form-control" placeholder="Enter project name" required>
            </div>
            <div class="form-group">
                <label for="project-desc"><strong>Project Description</strong></label>
                <textarea id="project-desc" class="form-control" placeholder="Enter project description"
                    rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="project-engineers"><strong>Assign Software Engineers</strong></label>
                <select id="project-engineers" class="form-control" multiple size="6">
                    @foreach($engineers as $engineer)
                        <option value="{{ $engineer->id }}">{{ $engineer->name }} ({{ $engineer->email }})</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple engineers. Leave empty to assign later.</small>
            </div>
            <button type="submit" class="btn btn-submit btn-block">Create Project</button>
        </form>
        <div id="success-messages" class="mt-3"></div>
    </div>

    <!-- Modal for choosing feature generation -->
    <div class="modal fade" id="feature-modal" tabindex="-1" aria-labelledby="featureModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="featureModalLabel">Choose Feature Entry Method</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    How would you like to add features to this project?
                </div>
                <div class="modal-footer">
                    <button id="manual-entry" class="btn btn-secondary" data-dismiss="modal">Add Manually</button>
                    <button id="generate-ai" class="btn btn-primary" data-dismiss="modal">Generate with AI</button>
                </div>
            </div>
        </div>
    </div>

    <div id="feature-loading" class="d-none text-center my-3">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Generating suggestions...</span>
        </div>
        <p class="mt-2">Generating feature suggestions with AI...</p>
    </div>

    <div id="suggested-features" class="mt-4"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            let createdProject = {};

            $('#project-form').submit(function (e) {
                e.preventDefault();

                const name = $('#project-name').val().trim();
                const description = $('#project-desc').val().trim();
                const engineerIds = $('#project-engineers').val() || [];

                if (!name) {
                    alert("Project name is required.");
                    return;
                }

                // Format engineers array properly
                const engineersData = {};
                engineerIds.forEach((id, index) => {
                    engineersData[`engineers[${index}]`] = id;
                });

                $.ajax({
                    type: 'POST',
                    url: '/project-store',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { 
                        name: name, 
                        description: description, 
                        engineers: engineerIds 
                    },
                    success: function (res) {
                        createdProject = {
                            id: res.project_id,
                            name: name,
                            description: description
                        };
                        $('#project-form')[0].reset();
                        $('#success-messages').html(`
                        <div class="alert alert-success" role="alert">
                            Project <strong>"${name}"</strong> created successfully with ${engineerIds.length} engineer(s).
                        </div>
                    `);
                        // Show modal for feature selection
                        $('#feature-modal').modal('show');
                    },
                    error: function (xhr) {
                        console.log('Error:', xhr);
                        alert("Failed to create project: " + (xhr.responseJSON?.message || "Unknown error"));
                    }
                });
            });

            $('#generate-ai').click(function () {
                $('#feature-loading').removeClass('d-none');
                $('#suggested-features').empty();

                // Request feature suggestions
                $.post('/suggest-features', {
                    project_id: createdProject.id,
                    name: createdProject.name,
                    description: createdProject.description,
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                    .done(function (areas) {
                        let html = '<h5 class="mt-4">Suggested Feature Areas</h5>';
                        areas.forEach(area => {
                            html += `<div class="card mb-2">
                            <div class="card-header font-weight-bold">
                                ${area.category}
                            </div>
                            <ul class="list-group list-group-flush">`;
                            area.features.forEach(f => {
                                html += `<li class="list-group-item">${f}</li>`;
                            });
                            html += '</ul></div>';
                        });
                        $('#suggested-features').html(html);
                    })
                    .fail(() => {
                        alert('Failed to get feature suggestions');
                    })
                    .always(() => {
                        // Hide loading spinner regardless of success/failure
                        $('#feature-loading').addClass('d-none');
                    });
            });

            $('#manual-entry').click(function () {
                if (!createdProject.id) {
                    alert('Please create a project first.');
                    return;
                }

                // Redirect to add-feature page with project ID as a query parameter
                window.location.href = `/add-feature?project_id=${createdProject.id}`;
            });
        });
    </script>

</body>

</html>