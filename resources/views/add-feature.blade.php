<!DOCTYPE html>
<html>

<head>
    <title>Submit Features</title>
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

<body>
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
                                <a class="nav-link position-relative" href="/review-changes">
                                    Review Changes

                                    {{-- Notification badge for pending updates --}}
                                    @php
                                        $pendingCount = \App\Models\PendingUpdate::where('status', 'Pending')->count();
                                        $pendingResolutionsCount = \App\Models\PendingResolution::where('status', 'Pending')->count();
                                        $totalCount = $pendingCount + $pendingResolutionsCount;
                                      @endphp

                                    @if($totalCount > 0)
                                        <span class="badge badge-danger position-absolute"
                                            style="top: 0; right: 50px; font-size: 0.7rem;">
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

    <!-- Modal for Selecting a Project -->
    <div class="modal fade" id="selectProjectModal" tabindex="-1" role="dialog" aria-labelledby="selectProjectLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectProjectLabel">Select a Project</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="select-project-form">
                        <div class="form-group">
                            <label for="project-select">Available Projects</label>
                            <select class="form-control" id="project-select" required>
                                <option value="" disabled selected>Select a project</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Confirm Selection</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="form-container">
        <h3 class="text-center mb-4">Submit New Feature</h3>
        <form id="requirement-form">
            <div class="form-group">
                <label for="project_id"><strong>Project</strong></label>
                <span id="project-name" class="text-primary font-weight-bold"></span>
            </div>
            <div class="form-group">
                <label for="feature-category"><strong>Category</strong></label>
                <input type="text" id="feature-category" class="form-control" placeholder="Enter feature category"
                    required>
            </div>
            <div class="form-group">
                <label for="feature-name"><strong>Feature Description</strong></label>
                <textarea id="feature-name" class="form-control" placeholder="Enter feature description" rows="3"
                    required></textarea>
            </div>
            <button type="submit" class="btn btn-submit btn-block">Submit Feature</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>

    <!-- Success/Error Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" id="messageModalHeader">
                    <h5 class="modal-title" id="messageModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" id="messageModalBody">
                    <p id="messageModalText"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="messageModalOkBtn">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            const urlParams = new URLSearchParams(window.location.search);
            let projectId = urlParams.get('project_id');

            if (!projectId) {
                // Show modal to select a project
                $('#selectProjectModal').modal('show');

                // Use projects passed from controller
                const projects = @json($projects);
                
                if (projects.length === 0) {
                    $('#selectProjectModal .modal-body').html('<p class="text-center text-muted">You are not assigned to any project. Please contact your project manager.</p>');
                } else {
                    // Populate the dropdown with project options
                    projects.forEach(project => {
                        $('#project-select').append(`<option value="${project.id}">${project.name}</option>`);
                    });
                }

                // Handle project selection
                $('#select-project-form').submit(function (e) {
                    e.preventDefault();
                    const selectedProjectId = $('#project-select').val();
                    if (selectedProjectId) {
                        // Reload the page with the selected project_id in the URL
                        window.location.href = `${window.location.pathname}?project_id=${selectedProjectId}`;
                    } else {
                        alert('Please select a project.');
                    }
                });
            } else {
                // Fetch and display project details
                $.ajax({
                    type: 'GET',
                    url: `/projects/${projectId}`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        $('#project-name').text(`(${res.name})`);
                    },
                    error: function () {
                        alert('Failed to fetch project details.');
                    }
                });
            }

            // Handle form submission
            $('#requirement-form').submit(function (e) {
                e.preventDefault();

                const category = $('#feature-category').val().trim();
                const feature = $('#feature-name').val().trim();

                if (!category || !feature) {
                    alert('All fields are required.');
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: '/feature-store',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { project_id: projectId, category, feature },
                    success: function () {
                        $('#messageModalHeader').removeClass('bg-danger').addClass('bg-success text-white');
                        $('#messageModalLabel').text('Success');
                        $('#messageModalText').text('Feature submitted successfully.');
                        $('#messageModal').modal('show');
                        $('#messageModalOkBtn').one('click', function() {
                            $('#requirement-form')[0].reset();
                            $('#messageModal').modal('hide');
                        });
                    },
                    error: function () {
                        $('#messageModalHeader').removeClass('bg-success').addClass('bg-danger text-white');
                        $('#messageModalLabel').text('Error');
                        $('#messageModalText').text('Failed to submit feature.');
                        $('#messageModal').modal('show');
                    }
                });
            });
        });
    </script>
</body>

</html>