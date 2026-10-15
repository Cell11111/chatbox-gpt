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
                                    // Priority filter logic (per project details section)
                                    document.querySelectorAll('.project-details').forEach(function(section) {
                                        const btns = section.querySelectorAll('.priority-filter-btn');
                                        btns.forEach(function(btn) {
                                            btn.addEventListener('click', function() {
                                                // Update active state
                                                btns.forEach(b => b.classList.remove('active'));
                                                this.classList.add('active');

                                                const selected = (this.getAttribute('data-priority') || 'All').toLowerCase();
                                                // Find all requirement rows inside this section
                                                section.querySelectorAll('tr.requirement-row').forEach(function(row) {
                                                    const p = (row.getAttribute('data-priority') || '').toLowerCase();
                                                    const show = (selected === 'all') || (p === selected);
                                                    row.style.display = show ? '' : 'none';
                                                    // Hide/show the first defect row that immediately follows a requirement row
                                                    const next = row.nextElementSibling;
                                                    if (next && next.querySelector('h6') && next.querySelector('h6').textContent.includes('Defect')) {
                                                        next.style.display = show ? '' : 'none';
                                                    }
                                                });
                                            });
                                        });
                                    });
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h2>Project Overview</h2>
        <!-- Priority sort control is per-table (arrow in the Priority header) -->
        <div>
            @auth
                @if(auth()->user()->role !== 'software engineer')
                    <a href="/add-project" class="btn btn-primary mr-2">Add Project</a>
                @endif
            @endauth
            <button class="btn btn-success" onclick="exportReport()">Export Final Report</button>
        </div>
    </div>
</div>

<body>
    <div class="container mt-4">
        <div class="row">
            @forelse($projects as $project)
                @if(auth()->check() && auth()->user()->role !== 'software engineer')
                    <!-- Manager View: Project Overview Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <h5 class="mb-0 font-weight-bold">{{ $project->name }}</h5>
                                @if(auth()->check() && auth()->user()->role === 'project manager')
                                    <div>
                                        <button class="btn btn-sm btn-light mr-2" data-toggle="modal" data-target="#editProjectModal" data-project-id="{{ $project->id }}" data-project-name="{{ $project->name }}" data-project-description="{{ $project->description }}" data-project-engineers="{{ $project->engineers->pluck('id')->implode(',') }}">
                                            <i class="fa fa-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#deleteProjectModal" data-project-id="{{ $project->id }}" data-project-name="{{ $project->name }}">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">{{ $project->description }}</p>
                                
                                @php
                                    $totalRequirements = $project->features->flatMap->requirements->count();
                                    $pendingReqs = $project->features->flatMap->requirements->where('status', 'Pending')->count();
                                    $approvedReqs = $project->features->flatMap->requirements->where('status', 'Completed')->count();
                                    $totalDefects = $project->features->flatMap->requirements->flatMap->defects->count();
                                    $totalConflicts = \App\Models\Conflict::where('project_id', $project->id)->count();
                                    $progress = $totalRequirements > 0 ? round(($approvedReqs / $totalRequirements) * 100) : 0;
                                @endphp
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small">Progress</span>
                                        <span class="text-muted small">{{ $progress }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>

                                @auth
                                @if(auth()->user()->role === 'project manager')
                                <!-- Edit Project Modal -->
                                <div class="modal fade" id="editProjectModal" tabindex="-1" role="dialog" aria-labelledby="editProjectModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form id="editProjectForm">
                                                <div class="modal-body">
                                                    <input type="hidden" id="editProjectId" />
                                                    <div class="form-group">
                                                        <label for="editProjectName">Name</label>
                                                        <input type="text" class="form-control" id="editProjectName" required />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editProjectDescription">Description</label>
                                                        <textarea class="form-control" id="editProjectDescription" rows="3"></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editProjectEngineers">Assigned Engineers</label>
                                                        <select id="editProjectEngineers" class="form-control" multiple></select>
                                                        <small class="text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Project Modal -->
                                <div class="modal fade" id="deleteProjectModal" tabindex="-1" role="dialog" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title" id="deleteProjectModalLabel">Delete Project</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete project <strong id="deleteProjectName"></strong>?</p>
                                                <p class="text-danger"><i class="fa fa-exclamation-triangle"></i> This action cannot be undone. All requirements, features, conflicts, and defects associated with this project will be deleted.</p>
                                                <input type="hidden" id="deleteProjectId" />
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-outline-danger" id="confirmDeleteProject">Delete Project</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endauth
                                
                                <div class="row text-center">
                                    <div class="col-6 mb-2">
                                        <div class="border rounded p-2">
                                            <h6 class="mb-0 text-primary">{{ $totalRequirements }}</h6>
                                            <small class="text-muted">Requirements</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="border rounded p-2">
                                            <h6 class="mb-0 text-warning">{{ $pendingReqs }}</h6>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="border rounded p-2">
                                            <h6 class="mb-0 text-danger">{{ $totalDefects }}</h6>
                                            <small class="text-muted">Defects</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="border rounded p-2">
                                            <h6 class="mb-0 text-info">{{ $totalConflicts }}</h6>
                                            <small class="text-muted">Conflicts</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3 mb-3">
                                    <h6 class="text-muted mb-2"><i class="fa fa-users"></i> Assigned Engineers:</h6>
                                    @if($project->engineers->isNotEmpty())
                                        <div class="d-flex flex-wrap" style="gap: 5px;">
                                            @foreach($project->engineers as $engineer)
                                                <span class="badge badge-secondary">{{ $engineer->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <small class="text-muted">No engineers assigned</small>
                                    @endif
                                </div>
                                
                                <div class="mt-3">
                                    @if($totalConflicts > 0)
                                        <a href="/view-conflict?project_id={{ $project->id }}" class="btn btn-sm btn-outline-warning btn-block">
                                            <i class="fa fa-exclamation-triangle"></i> Resolve Conflicts
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Engineer View: Detailed Requirements -->
                    <div class="col-md-12">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white rounded-top">
                                <h5 class="mb-0 font-weight-bold">
                                    <button class="btn btn-link text-white toggle-project-details"
                                        data-project-index="{{ $loop->index }}">
                                        Project: {{ $project->name }}
                                    </button>
                                </h5>
                            </div>
                            <div id="project-details-{{ $loop->index }}" class="card-body project-details d-none">
                            <p class="mb-4"><strong>Project Description:</strong> {{ $project->description }}</p>

                            <!-- Project Snapshot Controls -->
                            @auth
                            <div class="alert alert-info mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-2"><i class="fa fa-camera"></i> Project Snapshots</h6>
                                        <small>Save the current state of all requirements at any time. Project managers can rollback to previous snapshots.</small>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button type="button" class="btn btn-sm btn-info" data-target="#saveSnapshotModal" data-project-id="{{ $project->id }}" data-project-name="{{ $project->name }}">
                                            <i class="fa fa-save"></i> Save Current State
                                        </button>
                                        @if(auth()->user()->role === 'project manager')
                                            <button class="btn btn-sm btn-secondary" id="viewSnapshots-{{ $project->id }}" onclick="loadSnapshots({{ $project->id }})">
                                                <i class="fa fa-history"></i> View Snapshots
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Snapshots List (Hidden by default, Manager only) -->
                            @if(auth()->user()->role === 'project manager')
                            <div id="snapshots-container-{{ $project->id }}" class="mb-4 d-none">
                                <div class="card border-info">
                                    <div class="card-header bg-light border-info">
                                        <h6 class="mb-0">Saved Project States</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="snapshots-list-{{ $project->id }}" class="list-group">
                                            <p class="text-muted text-center">Loading snapshots...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endauth

                            <!-- Priority Filter -->
                            <div class="form-inline mb-3">
                                <label class="mr-2 font-weight-bold" for="priorityFilter-{{ $project->id }}">Filter by Priority:</label>
                                <select id="priorityFilter-{{ $project->id }}" class="form-control form-control-sm priority-filter-dropdown" data-project-id="{{ $project->id }}">
                                    <option value="All" selected>All</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>

                            <div class="mt-3">
                                <h6><strong>Feedback:</strong></h6>
                                <!-- Feedback Form -->
                                <form id="feedback-form-{{ $project->id }}" method="POST"
                                    action="{{ route('submitFeedback', $project->id) }}">
                                    @csrf
                                    <textarea name="feedback" class="form-control" rows="4"
                                        placeholder="Enter feedback here" required></textarea>
                                    <button type="submit" class="btn btn-primary mt-2">Submit Feedback</button>
                                </form>

                                <div class="feedback-list mt-3">
                                    @forelse($project->feedback as $feedback)
                                        <div class="card mb-2">
                                            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p><strong>{{ $feedback->author->name ?? 'Anonymous' }}:</strong>
                                                        {{ $feedback->content }}</p>
                                                    <p class="text-muted small mb-0">Submitted on
                                                        {{ $feedback->created_at->format('d M Y, H:i') }}
                                                    </p>
                                                </div>
                                                <!-- Delete Button (Visible only to managers) -->
                                                @if(auth()->user() && auth()->user()->role === 'project manager')
                                                    <form method="POST" action="{{ route('deleteFeedback', $feedback->id) }}"
                                                        class="ml-2">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this feedback?')">X</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted">No feedback available for this project.</p>
                                    @endforelse
                                </div>
                            </div>

                            @php
                                $grouped = $project->features->groupBy('category');
                                // Build a feature index map so features have stable numbers across categories
                                $featureIndexMap = [];
                                $fi = 0;
                                foreach ($project->features as $pf) {
                                    $fi++;
                                    $featureIndexMap[$pf->id] = $fi;
                                }
                                // Project number is the loop index + 1
                                $projectNumber = $loop->iteration;
                            @endphp

                            <div id="accordion-{{ $loop->index }}">
                                @foreach($grouped as $category => $features)
                                    <div id="accordion-{{ $loop->index }}">
                                        <div class="card mb-3 border-info shadow-sm">
                                            <div class="card-header bg-info text-white"
                                                id="heading-{{ $loop->index }}-{{ $loop->iteration }}">
                                                <h6 class="mb-0">
                                                    <a href="#collapse-{{ $loop->index }}-{{ $loop->iteration }}"
                                                        class="text-white" data-toggle="collapse" aria-expanded="false"
                                                        aria-controls="collapse-{{ $loop->index }}-{{ $loop->iteration }}">
                                                        <strong>Category:</strong> {{ $category }}
                                                    </a>
                                                </h6>
                                            </div>

                                            <div id="collapse-{{ $loop->index }}-{{ $loop->iteration }}" class="collapse"
                                                data-parent="#accordion-{{ $loop->index }}">
                                                <div class="card-body bg-light">
                                                    <table class="table table-striped table-bordered">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th>Feature</th>
                                                                <th>Requirement #</th>
                                                                <th>Requirement</th>
                                                                <th>Status</th>
                                                                <th>
                                                                    Priority
                                                                    <span class="priority-sort" data-sort="desc"
                                                                        title="Toggle priority sort"
                                                                        style="cursor:pointer; margin-left:8px; font-size:0.9rem;">
                                                                        &#9660;
                                                                    </span>
                                                                </th>
                                                                <th>Author</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($features as $feature)
                                                                @php $reqCounter = 0;
                                                                $featureIndex = $featureIndexMap[$feature->id] ?? '0'; @endphp
                                                                @forelse($feature->requirements as $requirement)
                                                                    @php
                                                                        $pendingUpdate = $requirement->pendingUpdates()->latest()->first(); // Fetch the latest pending update for this requirement
                                                                    @endphp
                                                                    <tr class="requirement-row"
                                                                        data-priority="{{ $requirement->priority }}">
                                                                        <td>{{ $feature->feature }}</td>
                                                                        @php $reqCounter++;
                                                                        $reqNumber = $projectNumber . '.' . $featureIndex . '.' . $reqCounter; @endphp
                                                                        <td>{{ $reqNumber }}</td>
                                                                        <td>{{ $requirement->description }}</td>
                                                                        <!-- <td>
                                                                                                                            @if($pendingUpdate && $pendingUpdate->status === 'Pending')
                                                                                                                                <span class="badge badge-warning">Pending</span>
                                                                                                                            @elseif($pendingUpdate && $pendingUpdate->status === 'Rejected')
                                                                                                                                <span class="badge badge-danger">Pending</span>
                                                                                                                            @else
                                                                                                                                <span
                                                                                                                                    class="badge badge-success">{{ $requirement->status }}</span>
                                                                                                                            @endif
                                                                                                                        </td> -->
                                                                        <td><span
                                                                                class="badge badge-success">{{ $requirement->status }}</span>
                                                                        </td>
                                                                        <td>{{ $requirement->priority ?? 'N/A' }}</td>
                                                                        <td>{{ $requirement->author->name ?? 'N/A' }}</td>
                                                                        <td>
                                                                            <button class="btn btn-warning btn-sm edit-btn"
                                                                                data-toggle="modal" data-target="#editModal"
                                                                                data-id="{{ $requirement->id }}"
                                                                                data-title="{{ $requirement->title }}"
                                                                                data-description="{{ $requirement->description }}"
                                                                                data-priority="{{ $requirement->priority }}"
                                                                                data-category="{{ $requirement->category }}"
                                                                                data-project_id="{{ $requirement->project_id }}"
                                                                                data-status="{{ $requirement->status }}">
                                                                                Edit
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn btn-info btn-sm btn-analyze-defect"
                                                                                data-requirement-id="{{ $requirement->id }}"
                                                                                data-requirement-title="{{ $requirement->title }}"
                                                                                data-requirement-description="{{ $requirement->description }}">
                                                                                <i class="fa fa-search"></i> Analyze
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn btn-danger btn-sm btn-delete-requirement"
                                                                                data-requirement-id="{{ $requirement->id }}"
                                                                                data-requirement-title="{{ \Illuminate\Support\Str::limit($requirement->title ?: $requirement->description, 80) }}"
                                                                                data-requirement-description="{{ \Illuminate\Support\Str::limit($requirement->description, 300) }}">
                                                                                Delete
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                    @forelse($requirement->defects->take(1) as $defect)
                                                                        <tr>
                                                                            <td colspan="6">
                                                                                <div class="ml-4 mt-2 p-2 border rounded bg-light">
                                                                                    <h6 class="text-warning font-weight-bold">Defect
                                                                                    </h6>
                                                                                    <p><strong>Type:</strong> {{ $defect->type }}</p>
                                                                                    <p><strong>Description:</strong>
                                                                                        {{ $defect->description }}</p>
                                                                                    <p><strong>Suggestion:</strong>
                                                                                        {{ $defect->suggestion ?? 'No suggestion available.' }}
                                                                                    </p>
                                                                                    <div class="actions mt-2">
                                                                                        <button class="btn btn-success btn-sm btn-auto-fix"
                                                                                            data-requirement-id="{{ $requirement->id }}"
                                                                                            data-defect-id="{{ $defect->id }}"
                                                                                            data-suggestion="{{ $defect->suggestion }}"
                                                                                            data-field="description">
                                                                                            Auto-Fix
                                                                                        </button>
                                                                                        <button class="btn btn-danger btn-sm btn-ignore"
                                                                                            data-defect-id="{{ $defect->id }}"
                                                                                            data-defect-description="{{ \Illuminate\Support\Str::limit($defect->description, 300) }}">
                                                                                            Ignore
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="6" class="text-muted text-center">No defects
                                                                                found for this
                                                                                requirement.</td>
                                                                        </tr>
                                                                    @endforelse
                                                                @empty
                                                                    <tr>
                                                                        <td>{{ $feature->feature }}</td>
                                                                        <td colspan="4" class="text-muted text-center">No
                                                                            requirements found for
                                                                            this feature.</td>
                                                                        <td></td>
                                                                    </tr>
                                                                @endforelse
                                                                <!-- Add Requirement button -->
                                                                <tr>
                                                                    <td colspan="6" class="text-center">
                                                                        <button class="btn btn-primary btn-sm"
                                                                            onclick="redirectToSubmitRequirement('{{ $project->id }}', '{{ $feature->category }}', '{{ $feature->id }}')">
                                                                            Add Requirement
                                                                        </button>

                                                                        <button class="btn-delete-feature"
                                                                            data-feature-id="{{ $feature->id }}"
                                                                            onclick="confirmDeleteFeature('{{ $feature->id }}', '{{ $feature->feature }}')">
                                                                            Delete Feature
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button class="btn btn-primary btn-add-features" data-toggle="modal" data-target="#feature-modal"
                            data-project-id="{{ $project->id }}" data-project-name="{{ $project->name }}"
                            data-project-description="{{ $project->description }}">
                            Add Features
                        </button>
                    </div>
                </div>
                @endif
            @empty
                <div class="alert alert-warning text-center">No projects found.</div>
            @endforelse
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form method="POST" id="editForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Requirement</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Feature</label>
                                <input type="text" name="title" class="form-control" id="editTitle" readonly>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" id="editDescription"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" class="form-control" id="editPriority">
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control" id="editStatus">
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Requirement Modal -->
        <div class="modal fade" id="deleteRequirementModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteRequirementModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="deleteRequirementForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteRequirementModalLabel">Confirm Requirement Deletion</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the following requirement?</p>
                            <p class="font-weight-bold requirement-title"></p>
                            <p class="requirement-desc text-muted"></p>
                            <p class="text-muted small">This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" id="confirmDeleteRequirementBtn" class="btn btn-danger">Delete
                                Requirement</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ignore Defect Modal -->
        <div class="modal fade" id="ignoreDefectModal" tabindex="-1" role="dialog"
            aria-labelledby="ignoreDefectModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ignoreDefectModalLabel">Confirm Ignore Defect</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to ignore this defect? It will be removed from the requirement.</p>
                        <p class="font-weight-bold defect-desc"></p>
                        <p class="text-muted small">You can undo this by re-adding the defect manually if needed.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" id="confirm-ignore-defect" class="btn btn-danger">Ignore Defect</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fix Modal -->
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
                        <div id="loading-spinner" class="spinner-border text-success ml-2" role="status"
                            style="display: none;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature Modal -->
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
                        <button id="manual-entry" class="btn btn-secondary" data-dismiss="modal">Add
                            Manually</button>
                        <button id="generate-ai" class="btn btn-primary" data-dismiss="modal">Generate with
                            AI</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="feature-loading" class="feature-loading" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Generating suggestions...</span>
            </div>
            <p>Generating feature suggestions with AI...</p>
        </div>
        <div class="modal fade" id="deleteFeatureModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteFeatureModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteFeatureModalLabel">Confirm Feature
                            Deletion</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete the feature
                        "<strong id="feature-name"></strong>"? This
                        action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <form id="delete-feature-form" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
        <script>
            // Populate edit modal with project data
            $('#editProjectModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const id = button.data('project-id');
                const name = button.data('project-name');
                const description = button.data('project-description');
                const engineersCsv = (button.data('project-engineers') || '').toString();
                const selectedIds = engineersCsv ? engineersCsv.split(',').filter(Boolean) : [];
                $('#editProjectId').val(id);
                $('#editProjectName').val(name);
                $('#editProjectDescription').val(description);

                // Load engineers list
                const select = $('#editProjectEngineers');
                select.empty().append('<option value="" disabled>Loading...</option>');
                $.get('{{ route('engineers.list') }}').done(function(list) {
                    select.empty();
                    list.forEach(function(u) {
                        const opt = $('<option>').val(u.id).text(u.name);
                        if (selectedIds.includes(String(u.id))) opt.attr('selected', 'selected');
                        select.append(opt);
                    });
                }).fail(function() {
                    select.empty();
                    select.append('<option value="" disabled>Error loading engineers</option>');
                });
            });

            // Submit edit form via AJAX
            $('#editProjectForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#editProjectId').val();
                const name = $('#editProjectName').val();
                const description = $('#editProjectDescription').val();
                $.ajax({
                    url: '{{ url('/projects') }}' + '/' + id,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: name,
                        description: description,
                        engineers: $('#editProjectEngineers').val() || []
                    },
                    success: function(res) {
                        $('#editProjectModal').modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Failed to update project');
                    }
                });
            });

            // Populate delete modal with project data
            $('#deleteProjectModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const id = button.data('project-id');
                const name = button.data('project-name');
                $('#deleteProjectId').val(id);
                $('#deleteProjectName').text(name);
            });

            // Handle delete project confirmation
            $('#confirmDeleteProject').on('click', function() {
                const id = $('#deleteProjectId').val();
                const btn = $(this);
                btn.prop('disabled', true).text('Deleting...');
                
                $.ajax({
                    url: '{{ url('/projects') }}' + '/' + id,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        $('#deleteProjectModal').modal('hide');
                        $('#messageModalHeader').removeClass('bg-danger').addClass('bg-success text-white');
                        $('#messageModalLabel').text('Success');
                        $('#messageModalText').text(res.message || 'Project deleted successfully');
                        $('#messageModal').modal('show');
                        $('#messageModalOkBtn').one('click', function() {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Delete Project');
                        $('#deleteProjectModal').modal('hide');
                        $('#messageModalHeader').removeClass('bg-success').addClass('bg-danger text-white');
                        $('#messageModalLabel').text('Error');
                        $('#messageModalText').text(xhr.responseJSON?.message || 'Failed to delete project');
                        $('#messageModal').modal('show');
                    }
                });
            });
        </script>

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
</body>

</html>
<!-- Custom CSS -->
<style>
    .card {
        border-radius: 10px;
        /* Rounded corners for cards */
    }

    .card-header {
        border-radius: 10px 10px 0 0;
        /* Rounded top corners */
    }

    .card-body {
        border-radius: 0 0 10px 10px;
        /* Rounded bottom corners */
    }

    .shadow-sm {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        /* Subtle shadow for depth */
    }

    .mb-4 {
        margin-bottom: 1.5rem;
        /* Larger spacing between elements */
    }

    .badge {
        font-size: 0.85rem;
        /* Slightly smaller badges */
        padding: 5px 10px;
        border-radius: 5px;
        /* Rounded badge */
    }

    .badge-success {
        background-color: #28a745;
        /* Green for success */
    }

    .card-body .border {
        border: 1px solid #ddd;
        /* Light border for requirements */
    }

    .text-muted {
        color: #6c757d;
        /* Muted text color */
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .modal {
        display: none;
        /* Hide modal by default */
    }

    .d-none {
        display: none !important;
    }

    #feature-loading {
        display: none;
        /* Ensure spinner is hidden initially */
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1050;
        /* High enough to overlay everything */
        text-align: center;
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

    #loading-spinner {
        width: 1.5rem;
        height: 1.5rem;
    }

    .feedback-list .card {
        border-left: 4px solid #007bff;
        background-color: #f8f9fa;
    }

    .feedback-list .card-body {
        font-size: 0.9rem;
    }

    .btn-danger {
        font-size: 0.8rem;
        padding: 2px 6px;
        border-radius: 50%;
    }

    .btn-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }

    /* Ensure modal delete button is rectangular and properly sized */
    #deleteRequirementModal .btn-danger {
        border-radius: 6px !important;
        padding: 8px 16px !important;
        font-size: 1rem !important;
    }

    /* Make delete and edit buttons in actions column the same size */
    .btn-delete-requirement,
    .edit-btn {
        border-radius: 6px !important;
        padding: 6px 10px !important;
        min-width: 72px;
        display: inline-block;
        text-align: center;
    }

    /* Make ignore defect buttons rectangular and match delete button style */
    .btn-ignore {
        border-radius: 6px !important;
        padding: 6px 10px !important;
        min-width: 72px;
        display: inline-block;
        text-align: center;
    }

    /* Make the ignore-confirm button in the modal rectangular and match modal delete style */
    #ignoreDefectModal .btn-danger,
    #confirm-ignore-defect {
        border-radius: 6px !important;
        padding: 8px 16px !important;
        font-size: 1rem !important;
    }

    .btn-delete-feature {
        background-color: #dc3545;
    }
</style>

<script>
    function redirectToSubmitRequirement(projectId, category, featureId) {
        const url = `/submit-requirements?project_id=${projectId}&category=${encodeURIComponent(category)}&feature_id=${featureId}`;
        window.location.href = url;
    }
</script>
<script>
    $(document).ready(function () {
        // Populate the modal with the clicked requirement's data
        $('.edit-btn').on('click', function () {
            console.log('Clicked edit button data:', {
                id: $(this).data('id'),
                title: $(this).data('title'),
                description: $(this).data('description'),
                priority: $(this).data('priority'),
                projectId: $(this).data('project_id'),
                status: $(this).data('status'),
            });
            const id = $(this).data('id');
            const title = $(this).data('title');
            const description = $(this).data('description');
            const priority = $(this).data('priority');
            const projectId = $(this).data('project_id');
            const status = $(this).data('status');
            const requirementId = $(this).data('id');

            // Set form action to include the requirement ID
            $('#editForm').attr('action', `/requirements/${id}`);
            console.log('Edit form action URL set to:', `/requirements/${id}`);

            // Populate the modal fields
            $('#editTitle').val(title);
            $('#editDescription').val(description);
            $('#editPriority').val(priority);

            if ($('#editStatus').length) {
                $('#editStatus').val(status);
                console.log('Status field populated with:', status);
            } else {
                console.log('Status field not available for this user.');
            }
            console.log('Modal fields populated with:', {
                title,
                description,
                priority,
                status,
            });
        });
    });
</script>
<script>
    // Delete requirement modal handler
    $(document).on('click', '.btn-delete-requirement', function () {
        const reqId = $(this).data('requirement-id');
        const reqTitle = $(this).data('requirement-title') || 'Requirement';
        const reqDesc = $(this).data('requirement-description') || '';
        // set modal title/preview
        $('#deleteRequirementModal .requirement-title').text(reqTitle);
        // set modal description
        $('#deleteRequirementModal .requirement-desc').text(reqDesc);
        // set form action
        $('#deleteRequirementForm').attr('action', '/requirements/' + reqId);
        // show modal
        $('#deleteRequirementModal').modal('show');
    });

    // Optional: after successful delete, you might want to remove rows via AJAX.
    // This form currently submits normally and will rely on server redirect/flash.
</script>
@if(session('success'))
    <script>
        $(function () {
            // build the alert
            const alertHtml = `
                                                                                          <div class="alert alert-success alert-dismissible fade show" role="alert" style="position:fixed; top:20px; right:20px; z-index:1050;">
                                                                                            {{ session('success') }}
                                                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                              <span aria-hidden="true">&times;</span>
                                                                                            </button>
                                                                                          </div>
                                                                                        `;
            // prepend to body (or some container)
            $('body').append(alertHtml);
        });
    </script>
@endif
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let currentButton = null;
        let requirementId = null;
        let defectId = null;
        let suggestion = null;
        let field = null;
        let originalText = null;

        console.log("DOM fully loaded and parsed.");

        // Auto-fix button click: fetch suggestion, show modal
        document.querySelectorAll('.btn-auto-fix').forEach(button => {
            button.addEventListener('click', function () {
                console.log("Auto-fix button clicked.");

                currentButton = this;
                requirementId = this.getAttribute('data-requirement-id');
                defectId = this.getAttribute('data-defect-id');
                suggestion = this.getAttribute('data-suggestion');
                field = this.getAttribute('data-field');

                console.log("Current Button:", currentButton);
                console.log("Requirement ID:", requirementId);
                console.log("Defect ID:", defectId);
                console.log("Suggestion:", suggestion);
                console.log("Field:", field);

                const parentRequirementRow = this.closest('tr').previousElementSibling;
                if (parentRequirementRow) {
                    const originalText = parentRequirementRow.querySelectorAll('td')[2]?.textContent.trim() || '';
                    document.getElementById('original-text').textContent = originalText;
                    document.getElementById('new-text').textContent = suggestion;
                    $('#fixModal').modal('show');
                } else {
                    console.error("Parent requirement row not found.");
                    alert("Unable to locate the associated requirement.");
                }
            });
        });
        // Call API to get the AI suggestion (without saving yet)


        // Confirm fix: update DOM and optionally persist
        document.getElementById('confirm-fix').addEventListener('click', function () {
            if (!currentButton) return;

            console.log("Confirm Fix button clicked.");
            isConfirmingFix = true; //

            const spinner = document.getElementById('loading-spinner');
            spinner.style.display = 'inline-block';

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
                    console.log("Response from API:", data);
                    if (data.pending) {
                        // Software Engineer path: show unified modal
                        $('#fixModal').modal('hide');
                        $('#messageModalHeader').removeClass('bg-danger').addClass('bg-success text-white');
                        $('#messageModalLabel').text('Submitted for Approval');
                        $('#messageModalText').text(data.message || 'Auto-fix submitted for approval.');
                        $('#messageModal').modal('show');
                        $('#messageModalOkBtn').off('click').on('click', function() {
                            $('#messageModal').modal('hide');
                        });
                    }
                    else if (data.updated) {
                        const parentRequirementRow = currentButton.closest('tr').previousElementSibling;
                        if (parentRequirementRow) {
                            const cells = parentRequirementRow.querySelectorAll('td');
                            if (cells.length > 1) {
                                cells[2].innerHTML = `<span class="text-success">${data.new_value}</span>`; // Update Requirement
                                cells[3].innerHTML = `<span class="badge badge-success">${data.status}</span>`; // Update Status
                                cells[4].innerHTML = data.priority ?? 'N/A'; // Update Priority
                                currentButton.disabled = true;
                                currentButton.innerText = "Fixed ✔";
                                console.log("Parent requirement row updated successfully.");
                            } else {
                                console.error("Requirement row does not have enough cells.");
                            }
                        } else {
                            console.error("Parent requirement row not found.");
                        }

                        let defectRow = currentButton.closest('tr');
                        while (defectRow && defectRow.querySelector('h6.text-warning')) {
                            const nextRow = defectRow.nextElementSibling;
                            defectRow.remove();
                            defectRow = nextRow;
                        }

                        console.log("All defect rows removed.");
                    }
                    else {
                        // Show error in unified modal instead of alert
                        $('#messageModalHeader').removeClass('bg-success').addClass('bg-danger text-white');
                        $('#messageModalLabel').text('Error');
                        $('#messageModalText').text('Failed to apply fix.');
                        $('#messageModal').modal('show');
                        $('#messageModalOkBtn').off('click').on('click', function() {
                            $('#messageModal').modal('hide');
                        });
                    }
                })
                .catch(error => {
                    console.error("Error during the API call:", error);
                    // Show error in unified modal instead of alert
                    $('#fixModal').modal('hide');
                    $('#messageModalHeader').removeClass('bg-success').addClass('bg-danger text-white');
                    $('#messageModalLabel').text('Error');
                    $('#messageModalText').text('An error occurred. Please try again.');
                    $('#messageModal').modal('show');
                    $('#messageModalOkBtn').off('click').on('click', function() {
                        $('#messageModal').modal('hide');
                    });
                })
                .finally(() => {
                    spinner.style.display = 'none';
                    $('#fixModal').modal('hide');
                    isConfirmingFix = false; // Reset flag
                    console.log("Modal closed after confirm.");
                });
        });

        $('#fixModal').on('hidden.bs.modal', function () {
            if (isConfirmingFix) return;
            console.log("Modal hidden. Clearing state.");

            currentButton = null;
            requirementId = null;
            defectId = null;
            suggestion = null;
            field = null;
            originalText = null;

            console.log("State cleared:", { currentButton, requirementId, defectId, suggestion, field, originalText });
        });
    });

    // Ignore Defect: open confirmation modal first
    let defectToIgnore = null;
    $(document).on('click', '.btn-ignore', function () {
        defectToIgnore = this; // store button reference
        const desc = $(this).data('defect-description') || '';
        $('#ignoreDefectModal .defect-desc').text(desc);
        $('#ignoreDefectModal').modal('show');
    });

    // Confirm ignore action — perform AJAX call and remove defect row
    $('#confirm-ignore-defect').on('click', function () {
        if (!defectToIgnore) return;
        const $btn = $(defectToIgnore);
        const defectId = $btn.data('defect-id');
        const defectRow = $btn.closest('tr');

        fetch('/ignore-defect', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ defect_id: defectId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.deleted) {
                    defectRow.remove();
                    $('#ignoreDefectModal').modal('hide');
                    defectToIgnore = null;
                } else {
                    alert('Failed to ignore defect.');
                }
            })
            .catch(err => {
                console.error('Error ignoring defect:', err);
                alert('An error occurred while ignoring the defect.');
            });
    });
</script>
<script>
    $(document).ready(function () {
        // Toggle project details visibility
        $('.toggle-project-details').on('click', function () {
            const projectIndex = $(this).data('project-index');

            // Hide all other project details
            $('.project-details').not(`#project-details-${projectIndex}`).addClass('d-none');

            // Toggle the clicked project's details
            $(`#project-details-${projectIndex}`).toggleClass('d-none');
        });
    });
</script>
<script>
    $(document).ready(function () {
        let selectedProject = {};

        // Open modal with project details
        $(".btn-add-features").click(function () {
            const projectId = $(this).data("project-id");
            const projectName = $(this).data("project-name");
            const projectDescription = $(this).data("project-description");

            selectedProject = { projectId, projectName, projectDescription };
        });

        // Manual Entry
        $("#manual-entry").click(function () {
            window.location.href = `/add-feature?project_id=${selectedProject.projectId}`;
        });

        // AI Feature Generation
        $("#generate-ai").click(function () {
            $("#feature-loading").show();


            $.post("/suggest-features", {
                project_id: selectedProject.projectId,
                name: selectedProject.projectName,
                description: selectedProject.projectDescription,
                _token: $('meta[name="csrf-token"]').attr("content")
            })
                .done(function (areas) {
                    $('#messageModalHeader').removeClass('bg-danger').addClass('bg-success text-white');
                    $('#messageModalLabel').text('Success');
                    $('#messageModalText').text('Features have been successfully generated. The page will refresh shortly.');
                    $('#messageModal').modal('show');
                    $('#messageModalOkBtn').off('click').on('click', function() {
                        location.reload();
                    });
                })
                .fail(function () {
                    $('#messageModalHeader').removeClass('bg-success').addClass('bg-danger text-white');
                    $('#messageModalLabel').text('Error');
                    $('#messageModalText').text('Failed to generate feature suggestions');
                    $('#messageModal').modal('show');
                    $('#messageModalOkBtn').off('click').on('click', function() {
                        $('#messageModal').modal('hide');
                    });
                })
                .always(function () {
                    $("#feature-loading").hide();
                });
        });
    });
</script>
<script>
    function exportReport() {
        window.location.href = '/export-report';
    }
</script>
<script>
    // Dropdown-based priority filtering across all features in a project section
    (function() {
        function filterSection(section, selected) {
            const sel = (selected || 'All').toLowerCase();
            
            // Find all collapse elements (categories) in this section and expand them
            const collapseElements = section.querySelectorAll('.collapse');
            
            if (sel === 'all') {
                // If "All" is selected, collapse all categories back to default state
                collapseElements.forEach(function(collapse) {
                    $(collapse).collapse('hide');
                });
            } else {
                // If a specific priority is selected, expand all categories to show matching requirements
                collapseElements.forEach(function(collapse) {
                    $(collapse).collapse('show');
                });
            }
            
            // Filter the requirement rows
            const rows = section.querySelectorAll('tr.requirement-row');
            rows.forEach(function(row) {
                const p = (row.getAttribute('data-priority') || '').toLowerCase();
                const show = (sel === 'all') || (p === sel);
                row.style.display = show ? '' : 'none';
                
                // Also handle the defect row that follows
                const next = row.nextElementSibling;
                if (next && next.querySelector('h6') && next.querySelector('h6').textContent.includes('Defect')) {
                    next.style.display = show ? '' : 'none';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.priority-filter-dropdown').forEach(function(dd) {
                dd.addEventListener('change', function() {
                    const section = dd.closest('.project-details');
                    if (section) filterSection(section, dd.value);
                });
                // initialize
                const section = dd.closest('.project-details');
                if (section) filterSection(section, dd.value);
            });
        });
    })();
</script>
<script>
    // Client-side sorting/filtering by priority (preserves defect rows grouped under requirement)
    (function () {
        const mapHigh = { 'High': 1, 'Medium': 2, 'Low': 3 };
        const mapLow = { 'High': 3, 'Medium': 2, 'Low': 1 };

        function getPriorityFromRow(row) {
            return (row.dataset && row.dataset.priority) ? row.dataset.priority : (row.querySelectorAll('td')[4] ? row.querySelectorAll('td')[4].textContent.trim() : null);
        }

        function collectGroups(tbody) {
            const rows = Array.from(tbody.children);
            const groups = [];
            for (let i = 0; i < rows.length; i++) {
                const r = rows[i];
                if (r.classList && r.classList.contains('requirement-row')) {
                    const group = [r];
                    let j = i + 1;
                    while (j < rows.length && !(rows[j].classList && rows[j].classList.contains('requirement-row'))) {
                        group.push(rows[j]);
                        j++;
                    }
                    groups.push(group);
                    i = j - 1;
                }
            }
            return groups;
        }

        function applyOrderToTbody(tbody, groups) {
            // Clear tbody
            while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
            // Append groups in order
            groups.forEach(group => group.forEach(row => tbody.appendChild(row)));
        }

        function sortTbodyByMode(tbody, mode) {
            const groups = collectGroups(tbody);
            if (!groups.length) return;

            if (mode === 'default') {
                // No sorting, leave as-is
                return;
            }

            if (mode === 'only-high' || mode === 'only-medium' || mode === 'only-low') {
                const only = mode.split('-')[1];
                const filtered = groups.filter(g => (getPriorityFromRow(g[0]) || '').toLowerCase() === only);
                applyOrderToTbody(tbody, filtered);
                return;
            }

            const isHighFirst = mode === 'high-first';
            groups.sort((a, b) => {
                const pa = (getPriorityFromRow(a[0]) || '');
                const pb = (getPriorityFromRow(b[0]) || '');
                const ma = isHighFirst ? (mapHigh[pa] || 99) : (mapLow[pa] || 99);
                const mb = isHighFirst ? (mapHigh[pb] || 99) : (mapLow[pb] || 99);
                return ma - mb;
            });

            applyOrderToTbody(tbody, groups);
        }

        function applySortToAll(mode) {
            document.querySelectorAll('table.table tbody').forEach(tbody => {
                sortTbodyByMode(tbody, mode);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Attach click handlers to each table's priority header arrow
            document.querySelectorAll('.priority-sort').forEach(function (el) {
                el.addEventListener('click', function () {
                    const current = el.getAttribute('data-sort') || 'desc';
                    const next = current === 'desc' ? 'asc' : 'desc';
                    el.setAttribute('data-sort', next);
                    // update icon
                    el.innerHTML = next === 'desc' ? '&#9660;' : '&#9650;';

                    // find the tbody for this table
                    const table = el.closest('table');
                    if (!table) return;
                    const tbody = table.querySelector('tbody');
                    if (!tbody) return;

                    // choose mode
                    const mode = next === 'desc' ? 'high-first' : 'low-first';
                    sortTbodyByMode(tbody, mode);
                });
            });
        });
    })();
</script>
<script>
    function confirmDeleteFeature(featureId, featureName) {
        // Update the modal with the feature name
        document.getElementById("feature-name").innerText = featureName;

        // Update the form action to point to the delete route
        document.getElementById("delete-feature-form").action = `/features/${featureId}`;

        // Show the modal
        $('#deleteFeatureModal').modal('show');
    }
</script>

<!-- Analyze Defects Modal -->
<div class="modal fade" id="analyzeDefectModal" tabindex="-1" role="dialog" aria-labelledby="analyzeDefectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="analyzeDefectModalLabel">Analyze Requirement for Defects</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="analyzeDefectContent">
                    <h6><strong>Requirement:</strong></h6>
                    <p id="analyzeReqTitle" class="font-weight-bold"></p>
                    <p id="analyzeReqDescription" class="text-muted"></p>
                    <hr>
                    <div id="analyzeDefectResults" style="display:none;">
                        <h6><strong>Analysis Results:</strong></h6>
                        <div id="defectResultsList"></div>
                    </div>
                    <div id="analyzeDefectLoading" class="text-center" style="display:none;">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Analyzing...</span>
                        </div>
                        <p class="mt-2">Analyzing requirement for defects...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="startAnalyzeDefect">Start Analysis</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentAnalyzeRequirementId = null;
    let analyzeShouldReload = false;

    // Handle Analyze Defect button click
    $(document).on('click', '.btn-analyze-defect', function() {
        const reqId = $(this).data('requirement-id');
        const reqTitle = $(this).data('requirement-title');
        const reqDescription = $(this).data('requirement-description');
        
        currentAnalyzeRequirementId = reqId;
        $('#analyzeReqTitle').text(reqTitle);
        $('#analyzeReqDescription').text(reqDescription);
        $('#defectResultsList').html('');
        $('#analyzeDefectResults').hide();
        $('#analyzeDefectLoading').hide();
        $('#startAnalyzeDefect').show();
        
        $('#analyzeDefectModal').modal('show');
    });

    // Reload page after modal closes if analysis completed
    $('#analyzeDefectModal').on('hidden.bs.modal', function () {
        if (analyzeShouldReload) {
            analyzeShouldReload = false;
            location.reload();
        }
    });

    // Handle Start Analysis button click
    $('#startAnalyzeDefect').on('click', function() {
        if (!currentAnalyzeRequirementId) return;
        
        const btn = $(this);
        btn.prop('disabled', true);
        $('#analyzeDefectLoading').show();
        $('#analyzeDefectResults').hide();
        
        $.ajax({
            url: '/analyze-requirements',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { 
                requirement: $('#analyzeReqDescription').text(),
                requirement_id: currentAnalyzeRequirementId,
                skip_split: true
            },
            success: function(res) {
                let resultsHtml = '';
                
                if (res.results && res.results.length > 0) {
                    res.results.forEach((result, idx) => {
                        if (result.defects && result.defects.length > 0) {
                            result.defects.forEach(defect => {
                                if (defect.type !== 'None') {
                                    resultsHtml += `
                                        <div class="card mb-3">
                                            <div class="card-header bg-warning text-dark">
                                                <strong>${defect.type}</strong>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Description:</strong> ${defect.description}</p>
                                                <p><strong>Suggestion:</strong> ${defect.suggestion || 'No suggestion provided'}</p>
                                            </div>
                                        </div>
                                    `;
                                } else {
                                    resultsHtml = '<div class="alert alert-success">No defects found in this requirement.</div>';
                                }
                            });
                        }
                    });
                } else {
                    resultsHtml = '<div class="alert alert-success">No defects found in this requirement.</div>';
                }
                
                $('#defectResultsList').html(resultsHtml);
                $('#analyzeDefectResults').show();
                $('#analyzeDefectLoading').hide();
                btn.prop('disabled', false).hide();
                analyzeShouldReload = true;
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to analyze requirement');
                $('#analyzeDefectLoading').hide();
                btn.prop('disabled', false);
            }
        });
    });

    // ===== PROJECT SNAPSHOT FUNCTIONALITY =====
    
    // Handle Save Snapshot Modal - Attach to button click instead of modal show
    $(document).on('click', 'button[data-target="#saveSnapshotModal"]', function(e) {
        const projectId = $(this).attr('data-project-id');
        const projectName = $(this).attr('data-project-name');
        
        console.log('=== Save Snapshot Button Clicked ===');
        console.log('Project ID:', projectId);
        console.log('Project Name:', projectName);
        
        if (!projectId) {
            console.error('ERROR: Project ID is not in button attributes!');
            console.log('Button element:', this);
            console.log('Button attributes:', this.attributes);
            alert('Error: Could not get project information. Please refresh the page.');
            return false;
        }
        
        // Store in hidden input BEFORE opening modal
        $('#snapshotProjectId').val(projectId);
        $('#snapshotProjectName').text(projectName);
        $('#snapshotName').val('');
        $('#snapshotDescription').val('');
        
        console.log('Values stored:');
        console.log('  snapshotProjectId:', $('#snapshotProjectId').val());
        console.log('  snapshotProjectName:', $('#snapshotProjectName').text());
        console.log('=== End Button Click ===');
        
        // Open the modal manually after setting values
        $('#saveSnapshotModal').modal('show');
        e.preventDefault();
        return false;
    });

    // Save snapshot
    $(document).on('click', '#saveSnapshotBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const projectId = $('#snapshotProjectId').val();
        const snapshotName = $('#snapshotName').val().trim();
        const description = $('#snapshotDescription').val().trim();

        console.log('Save button clicked');
        console.log('Form values:', { projectId, snapshotName, description });
        console.log('Project ID type:', typeof projectId, 'Value:', projectId);

        if (!projectId || projectId === '' || projectId === undefined) {
            alert('Error: Project ID is missing. Please close and reopen the modal.');
            console.error('Project ID is empty/undefined!');
            return false;
        }

        if (!snapshotName) {
            alert('Please enter a snapshot name');
            return false;
        }

        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        const ajaxData = {
            project_id: parseInt(projectId),
            snapshot_name: snapshotName,
            description: description
        };

        console.log('Sending AJAX request with data:', ajaxData);

        $.ajax({
            url: '/project-snapshot/save',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: ajaxData,
            success: function(res) {
                console.log('Save snapshot success:', res);
                if (res.success) {
                    $('#saveSnapshotModal').modal('hide');
                    // Only load snapshots if the container exists (for managers)
                    if ($('#snapshots-container-' + projectId).length) {
                        loadSnapshots(projectId);
                    }

                    // Show success in the unified message modal
                    $('#messageModalHeader').removeClass('bg-danger').addClass('bg-success text-white');
                    $('#messageModalLabel').text('Success');
                    $('#messageModalText').text(res.message || 'Snapshot saved successfully!');
                    $('#messageModal').modal('show');
                    $('#messageModalOkBtn').off('click').on('click', function() {
                        $('#messageModal').modal('hide');
                    });
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || xhr.responseText || 'Unknown error';
                console.error('Save snapshot error:', xhr.status, xhr.responseJSON, errorMsg);

                // Show error in the unified message modal
                $('#saveSnapshotModal').modal('hide');
                $('#messageModalHeader').removeClass('bg-success').addClass('bg-danger text-white');
                $('#messageModalLabel').text('Error');
                $('#messageModalText').text('Error saving snapshot: ' + errorMsg);
                $('#messageModal').modal('show');
                $('#messageModalOkBtn').off('click').on('click', function() {
                    $('#messageModal').modal('hide');
                });
            },
            complete: function() {
                $('#saveSnapshotBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Snapshot');
            }
        });
        
        return false;
    });

    // Load and display snapshots
    function loadSnapshots(projectId) {
        $.ajax({
            url: '/project/' + projectId + '/snapshots',
            type: 'GET',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if (res.success) {
                    const container = $('#snapshots-container-' + projectId);
                    const list = $('#snapshots-list-' + projectId);
                    
                    if (res.snapshots.length === 0) {
                        list.html('<p class="text-muted text-center">No snapshots saved yet.</p>');
                    } else {
                        let html = '';
                        res.snapshots.forEach(snapshot => {
                            const createdDate = new Date(snapshot.created_at).toLocaleString();
                            html += `
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${snapshot.snapshot_name}</h6>
                                            <small class="text-muted d-block">Saved: ${createdDate}</small>
                                            ${snapshot.description ? `<small class="text-muted d-block">${snapshot.description}</small>` : ''}
                                            <small class="text-muted d-block">By: ${snapshot.creator.name}</small>
                                        </div>
                                        <div class="btn-group-sm" role="group">
                                            <button class="btn btn-sm btn-success" onclick="rollbackSnapshot(${projectId}, ${snapshot.id}, '${snapshot.snapshot_name}')">
                                                <i class="fa fa-undo"></i> Rollback
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteSnapshot(${snapshot.id})">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        list.html(html);
                    }
                    
                    container.removeClass('d-none');
                    $('#viewSnapshots-' + projectId).removeClass('btn-secondary').addClass('btn-warning').html('<i class="fa fa-chevron-up"></i> Hide Snapshots');
                }
            },
            error: function(xhr) {
                alert('Error loading snapshots: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }

    // Rollback to snapshot
    function rollbackSnapshot(projectId, snapshotId, snapshotName) {
        if (!confirm(`Are you sure you want to rollback project to snapshot "${snapshotName}"? This will create new versions of all requirements.`)) {
            return;
        }

        $.ajax({
            url: '/project-snapshot/rollback',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                project_id: projectId,
                snapshot_id: snapshotId
            },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error rolling back snapshot: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }

    // Delete snapshot
    function deleteSnapshot(snapshotId) {
        if (!confirm('Are you sure you want to delete this snapshot?')) {
            return;
        }

        $.ajax({
            url: '/project-snapshot/' + snapshotId,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if (res.success) {
                    alert('Snapshot deleted successfully.');
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error deleting snapshot: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }
</script>

<!-- Save Project Snapshot Modal -->
<div class="modal fade" id="saveSnapshotModal" tabindex="-1" role="dialog" aria-labelledby="saveSnapshotLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="saveSnapshotLabel">Save Project State</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="saveSnapshotForm">
                <div class="modal-body">
                    <input type="hidden" id="snapshotProjectId" name="project_id" value="" />
                    <div class="form-group">
                        <label for="snapshotProjectName" class="font-weight-bold">Project</label>
                        <p id="snapshotProjectName" class="text-muted"></p>
                    </div>
                    <div class="form-group">
                        <label for="snapshotName" class="font-weight-bold">Snapshot Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="snapshotName" name="snapshot_name" placeholder="e.g., Version 1.0, After client review, Pre-deployment" required />
                        <small class="text-muted">Give this snapshot a descriptive name so you can easily identify it later.</small>
                    </div>
                    <div class="form-group">
                        <label for="snapshotDescription" class="font-weight-bold">Description (Optional)</label>
                        <textarea class="form-control" id="snapshotDescription" name="description" rows="3" placeholder="Add notes about this snapshot..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" id="saveSnapshotBtn"><i class="fa fa-save"></i> Save Snapshot</button>
                </div>
            </form>
        </div>
    </div>
</div>

