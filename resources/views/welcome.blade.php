<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"
        integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <style>
        .card {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .btn-block {
            width: 100%;
        }

        .badge {
            font-size: 0.9rem;
            padding: 6px 12px;
        }

        .status-dropdown {
            min-width: 150px;
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

        /* Manager vs Engineer theming */
        .manager-hero {
            background: linear-gradient(135deg, #0d47a1, #42a5f5);
            color: #fff;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 10px 30px rgba(13, 71, 161, 0.25);
        }

        .manager-kpi {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .engineer-hero {
            background: linear-gradient(135deg, #1b5e20, #66bb6a);
            color: #fff;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 10px 30px rgba(27, 94, 32, 0.25);
        }

        .engineer-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
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
    @if(auth()->check() && auth()->user()->role !== 'software engineer')
        <!-- Manager Dashboard -->
        <div class="container mt-4">
            <div class="manager-hero mb-4">
                <h4 class="mb-1">Manager Dashboard</h4>
                <p class="mb-0">Track approvals, changes, and project health at a glance.</p>
            </div>

            <div class="row mt-3">
                <div class="col-md-3 mb-3">
                    <div class="card text-center manager-kpi">
                        <h6 class="mt-3">Total Projects</h6>
                        <p class="mb-3"><span class="badge badge-primary">{{ $totalProjects }}</span></p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center manager-kpi">
                        <h6 class="mt-3">Pending Reviews</h6>
                        <p class="mb-3"><span class="badge badge-info">{{ $totalPendingReviews }}</span></p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center manager-kpi">
                        <h6 class="mt-3">Total Conflicts</h6>
                        <p class="mb-3"><span class="badge badge-warning">{{ $totalConflicts }}</span></p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center manager-kpi">
                        <h6 class="mt-3">Outstanding Defects</h6>
                        <p class="mb-3"><span class="badge badge-danger">{{ $outstandingDefects }}</span></p>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12 mb-3">
                    <div class="card manager-kpi">
                        <div class="d-flex justify-content-between align-items-center px-3 pt-3">
                            <h6 class="mb-0">Requirements by Status</h6>
                            <span class="text-muted small">Portfolio view</span>
                        </div>
                        <div style="display: flex; justify-content: center; align-items: center; height: 32vh;">
                            <canvas id="requirementsStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-7 mb-3">
                    <div class="card manager-kpi h-100">
                        <div class="d-flex justify-content-between align-items-center px-3 pt-3">
                            <h6 class="mb-0">Recent Updates</h6>
                            <span class="text-muted small">Latest activities</span>
                        </div>
                        <ul class="list-group list-group-flush">
                            @foreach($recentUpdates as $update)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <b>{{ $update->title }}</b>
                                        <div class="text-muted small">{{ $update->description }}</div>
                                    </div>
                                    <span class="badge badge-info">{{ $update->created_at->format('Y-m-d') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-md-5 mb-3">
                    <div class="card manager-kpi h-100">
                        <div class="d-flex justify-content-between align-items-center px-3 pt-3">
                            <h6 class="mb-0">Project Durations</h6>
                            <span class="text-muted small">Changes by author</span>
                        </div>
                        <ul class="list-group list-group-flush">
                            @foreach($projectDurations as $index => $project)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <b>{{ $project['name'] }}</b><br>
                                        <small class="text-muted">Duration: {{ $project['duration'] }}</small>
                                    </div>
                                    <span class="badge badge-info cursor-pointer" data-toggle="modal"
                                        data-target="#changesModal{{ $index }}">
                                        {{ $project['changeCount'] }} Changes
                                    </span>
                                </li>

                                <!-- Modal -->
                                <div class="modal fade" id="changesModal{{ $index }}" tabindex="-1" role="dialog"
                                    aria-labelledby="changesModalLabel{{ $index }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="changesModalLabel{{ $index }}">
                                                    Changes by Authors for {{ $project['name'] }}
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="list-group">
                                                    @forelse($project['authorChanges'] as $authorChange)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            {{ $authorChange['authorName'] }}
                                                            <span class="badge badge-secondary">{{ $authorChange['changeCount'] }} Changes</span>
                                                        </li>
                                                    @empty
                                                        <p>No changes available for this project.</p>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Software Engineer Dashboard -->
        <div class="container mt-4">
            <div class="engineer-hero mb-4">
                <h4 class="mb-1">Engineer Workspace</h4>
                <p class="mb-0">Focus on requirements, defects, and your recent activity.</p>
            </div>

            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <div class="card engineer-card text-center">
                        <h6 class="mt-3">Assigned Projects</h6>
                        <p class="mb-3"><span class="badge badge-primary">{{ $totalProjects }}</span></p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card engineer-card text-center">
                        <h6 class="mt-3">Requirements</h6>
                        <p class="mb-3"><span class="badge badge-success">{{ $totalRequirements }}</span></p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card engineer-card text-center">
                        <h6 class="mt-3">Open Defects</h6>
                        <p class="mb-3"><span class="badge badge-danger">{{ $outstandingDefects }}</span></p>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 mb-3">
                    <div class="card engineer-card h-100">
                        <div class="d-flex justify-content-between align-items-center px-3 pt-3">
                            <h6 class="mb-0">Status Mix</h6>
                            <span class="text-muted small">Your view</span>
                        </div>
                        <div style="display: flex; justify-content: center; align-items: center; height: 32vh;">
                            <canvas id="engineerRequirementsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card engineer-card h-100">
                        <div class="d-flex justify-content-between align-items-center px-3 pt-3">
                            <h6 class="mb-0">Recent Updates</h6>
                            <span class="text-muted small">Latest items</span>
                        </div>
                        <ul class="list-group list-group-flush">
                            @foreach($recentUpdates as $update)
                                <li class="list-group-item">
                                    <b>{{ $update->title }}</b>
                                    <div class="text-muted small">{{ $update->description }}</div>
                                    <span class="badge badge-info float-right mt-1">{{ $update->created_at->format('Y-m-d') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</body>

<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script>
    const statusCounts = @json($statusCounts);

    // Map status to specific colors
    const statusColors = {
        'Approved': '#28a745',    // Green
        'Pending': '#ffc107',     // Yellow
        'Rejected': '#dc3545',    // Red
        'Completed': '#17a2b8',   // Cyan
        'In Progress': '#6c757d'  // Gray
    };

    const labels = Object.keys(statusCounts);
    const backgroundColors = labels.map(status => statusColors[status] || '#6c757d');

    const data = {
        labels: labels,
        datasets: [{
            label: 'Requirements by Status',
            data: Object.values(statusCounts),
            backgroundColor: backgroundColors,
        }]
    };

    const managerCanvas = document.getElementById('requirementsStatusChart');
    if (managerCanvas) {
        new Chart(managerCanvas.getContext('2d'), {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    }

    const engineerCanvas = document.getElementById('engineerRequirementsChart');
    if (engineerCanvas) {
        new Chart(engineerCanvas.getContext('2d'), {
            type: 'doughnut',
            data: data,
            options: {
                cutout: '55%',
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
</script>


</html>