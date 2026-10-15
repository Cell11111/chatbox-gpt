<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"
        integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Change Tracking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .title {
            text-align: center;
            color: #1a1a2e;
            font-size: 26px;
            margin-bottom: 30px;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .change-box {
            border-left: 4px solid;
            padding-left: 16px;
            margin-bottom: 20px;
            background-color: #f5f9ff;
            padding: 12px 16px;
            border-radius: 8px;
        }

        .blue-border {
            border-color: #2b7de9;
        }

        .impact-section .impact-box {
            background-color: #ffecec;
            padding: 20px;
            border-radius: 10px;
        }

        .change-title {
            color: #d93025;
            margin-top: 10px;
        }

        .highlight {
            background-color: #e0e0e0;
            padding: 2px 4px;
            border-radius: 4px;
            font-style: italic;
        }

        .original-box {
            border-left: 4px solid #28a745;
            background-color: #f1fdf4;
        }

        .d-none {
            display: none !important;
        }

        select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fff;
            font-size: 14px;
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
    <div class="header-container">
        <h4 class="text-center font-weight-bold">Change Tracking</h4>
    </div>
    
    @php
        use App\Models\Requirement;
        
        // Projects and versions are provided by the controller
        $projects = $projects ?? collect();
        $projectMap = $projects->keyBy('id');
        $projectOrder = $projects->pluck('id')->toArray();
        // Build number map early so it's available for filters
        $reqIds = array_unique(array_merge(
            array_column($originals, 'requirement_id'),
            array_column($changes, 'requirement_id')
        ));

        $numberMap = [];
        foreach ($reqIds as $rid) {
            $r = Requirement::with(['project.features', 'feature.requirements'])->find($rid);
            if (!$r)
                continue;
            
            // Get project number
            $projectNumber = 0;
            foreach ($projectOrder as $projId) {
                $projectNumber++;
                if ($projId === $r->project_id)
                    break;
            }
            
            // build feature index map for this project (ordered by id)
            $fi = 0;
            $featureIndex = null;
            foreach ($r->project->features->sortBy('id') as $pf) {
                $fi++;
                if ($pf->id == $r->feature_id) {
                    $featureIndex = $fi;
                    break;
                }
            }

            // find requirement index within the feature (ordered by id)
            $ri = 0;
            foreach ($r->feature->requirements->sortBy('id') as $rr) {
                $ri++;
                if ($rr->id == $r->id)
                    break;
            }

            if ($featureIndex && $ri) {
                $numberMap[$rid] = $projectNumber . '.' . $featureIndex . '.' . $ri;
            }
        }
        
        // Map of project versions provided by the controller
        $projectVersions = $projectVersions ?? [];
    @endphp
    
    <div class="container">
        <div class="section">
            <h2 class="section-title">Filter Changes</h2>
            <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 20px;">
                <select id="viewMode">
                    <option value="snapshots">Saved Snapshots</option>
                    <option value="changes">All Changes</option>
                </select>
            </div>
        </div>

        <!-- SNAPSHOTS VIEW -->
        <div id="snapshotsView">
            <div class="section">
                <h2 class="section-title">Project Snapshots</h2>
                <p class="text-muted mb-4">View all saved snapshots for each project. Engineers can rollback to any saved snapshot.</p>
                <div id="snapshotsContainer" class="row">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading snapshots...</span>
                        </div>
                        <p class="mt-2">Loading snapshots...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHANGES VIEW -->
        <div id="changesView" style="display: none;">
            <div class="section">
                <h2 class="section-title">Filter Changes</h2>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 20px;">
                    <select id="filter-requirement">
                        <option value="">All Requirements</option>
                        @php
                            // Sort requirement numbers for display
                            $reqNumbers = $numberMap;
                            asort($reqNumbers);
                        @endphp
                        @foreach($reqNumbers as $reqId => $reqNum)
                            <option value="{{ $reqId }}">Requirement #{{ $reqNum }}</option>
                        @endforeach
                    </select>

                    <select id="filter-field">
                        <option value="">All Fields</option>
                        @foreach(array_unique(array_column($changes, 'field')) as $field)
                            <option value="{{ $field }}">{{ ucfirst($field) }}</option>
                        @endforeach
                    </select>

                    <select id="filter-author">
                        <option value="">All Authors</option>
                        @foreach(array_unique(array_column($changes, 'author')) as $author)
                            <option value="{{ $author }}">{{ $author }}</option>
                        @endforeach
                    </select>

                    <select id="filter-version">
                        <option value="">All Versions</option>
                        @php
                            $all_versions = array_unique(array_merge(
                                array_column($changes, 'version'),
                                array_fill(0, count($originals), 1.0) // assuming all originals are version 1.0
                            ));
                            sort($all_versions); // sort for a cleaner UI
                        @endphp

                        @foreach($all_versions as $version)
                            <option value="{{ number_format($version, 1) }}">Version {{ number_format($version, 1) }}</option>
                        @endforeach
                    </select>
                    <form method="GET" action="{{ url('change-log') }}">
                        <input type="date" name="date" value="{{ $filterDate !== 'all' ? $filterDate : '' }}"
                            class="form-control mb-3">
                        <div style="display:flex; gap:8px;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <button type="submit" name="date" value="all" class="btn btn-secondary">Show All</button>
                        </div>
                    </form>

                </div>
            </div>

            @forelse ($projectMap as $projectId => $project)
                @php
                    $projectNumberVal = array_search($projectId, $projectOrder, true) !== false
                        ? array_search($projectId, $projectOrder, true) + 1
                        : 1;
                @endphp
                <div class="section mb-3 project-section" data-project-id="{{ $projectId }}" data-project-number="{{ $projectNumberVal }}">
                <div class="d-flex align-items-center justify-content-between" style="border-bottom: 3px solid #007bff; padding-bottom: 10px;">
                    <h2 class="section-title mb-0" style="color: #007bff;">
                        <button class="btn btn-link p-0 mr-2 toggle-project" data-project-id="{{ $projectId }}" aria-expanded="false" aria-controls="project-body-{{ $projectId }}" title="Expand/Collapse">
                            <i class="fa fa-chevron-right"></i>
                        </button>
                        <i class="fa fa-folder-open"></i> {{ $project->name }}
                    </h2>
                </div>

                <div id="project-body-{{ $projectId }}" class="project-body collapse">
                <div class="mb-3 d-flex align-items-center" style="gap: 10px; flex-wrap: wrap;">
                    <label class="mb-0 font-weight-bold">View project at version:</label>
                    <select class="project-version-select" data-project-id="{{ $projectId }}" data-project-number="{{ $projectNumberVal }}">
                        <option value="">Select version</option>
                        @foreach(($projectVersions[$projectId] ?? []) as $version)
                            <option value="{{ $version }}">Version {{ number_format($version, 1) }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Displays all requirements as they existed at the selected version.</small>
                </div>
                <div class="project-snapshot" id="project-snapshot-{{ $projectId }}"></div>
                
                {{-- Changes for this project --}}
                @if(isset($changesByProject[$projectId]))
                    <h3 class="mt-4 mb-3" style="font-size: 18px; color: #dc3545;">Change Log</h3>
                    @foreach ($changesByProject[$projectId] as $change)
                        <div class="change-box blue-border" data-requirement-id="{{ $change['requirement_id'] }}" data-project-id="{{ $projectId }}">
                            <h4>Requirement #{{ $numberMap[$change['requirement_id']] ?? $change['requirement_id'] }} — Version {{ number_format($change['version'], 1) }}</h4>
                            <p>
                                <strong>Author:</strong> {{ $change['author'] ?? 'Unknown' }}<br>
                                <strong>Timestamp:</strong> {{ $change['created_at'] }}<br>
                                <strong>Modified Field:</strong> {{ ucfirst($change['field']) }}<br>
                                <strong>Old:</strong> <span class="highlight">{{ $change['old'] }}</span><br>
                                <strong>New:</strong> <span class="highlight">{{ $change['new'] }}</span>
                            </p>
                            @if(($change['version'] ?? 1) >= 2)
                                <div class="mt-2 p-2 bg-white border rounded">
                                    <h6 class="mb-2">Requirement Details</h6>
                                    <p class="mb-1"><strong>Title:</strong> <span class="highlight">{{ $change['title'] ?? 'N/A' }}</span></p>
                                    <p class="mb-1"><strong>Description:</strong> <span class="highlight">{{ $change['description'] ?? 'N/A' }}</span></p>
                                    <p class="mb-1"><strong>Category:</strong> {{ $change['category'] ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Priority:</strong> {{ $change['priority'] ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Status:</strong> {{ $change['status'] ?? 'N/A' }}</p>
                                </div>
                            @endif
                            <button type="button" class="btn btn-info btn-sm analyze-impact-btn mt-3"
                                data-requirement-id="{{ $change['requirement_id'] }}" data-field="{{ $change['field'] }}"
                                data-old="{{ $change['old'] }}" data-new="{{ $change['new'] }}">
                                Analyze Impact
                            </button>
                        </div>
                    @endforeach
                @endif
                </div>
            </div>
        @empty
            <p>No changes found.</p>
        @endforelse
        </div>
    </div>

    <div class="container mt-4">
        <h2>Impact Analysis Results</h2>
        <div id="impact-result-container" class="alert alert-info" style="display: none;"></div>
    </div>

    <!-- Global Loading Overlay -->
    <div id="global-loading">
        <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem;"></div>
        <p class="mt-3" style="font-size:1.2rem;">Analyzing impact...</p>
    </div>

    <!-- Project Rollback Confirmation Modal -->
    <div class="modal fade" id="projectRollbackConfirmModal" tabindex="-1" role="dialog" aria-labelledby="projectRollbackConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectRollbackConfirmModalLabel">Confirm Project Rollback</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to rollback the entire project <strong><span id="prjName"></span></strong>
                    to <strong>Version <span id="prjVer"></span></strong>?
                    <div class="mt-2 text-muted small">This will rollback ALL requirements in the project to their Version <span id="prjVerSmall"></span> state. The history will be preserved.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmProjectRollbackBtn">Confirm Project Rollback</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rollback Confirmation Modal -->
    <div class="modal fade" id="rollbackConfirmModal" tabindex="-1" role="dialog" aria-labelledby="rollbackConfirmModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rollbackConfirmModalLabel">Rollback Disabled</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Individual requirement rollback has been disabled. Use the <strong>Rollback Project to Version</strong> button at the project header to rollback all requirements in the project.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rollback Success Modal -->
    <div class="modal fade" id="rollbackSuccessModal" tabindex="-1" role="dialog" aria-labelledby="rollbackSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="rollbackSuccessModalLabel">
                        <i class="fa fa-check-circle"></i> Success
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fa fa-check-circle text-success" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0" id="rollbackSuccessMessage" style="font-size: 1.1rem;"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success" id="rollbackSuccessOkBtn">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rollback Error Modal -->
    <div class="modal fade" id="rollbackErrorModal" tabindex="-1" role="dialog" aria-labelledby="rollbackErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rollbackErrorModalLabel">
                        <i class="fa fa-exclamation-triangle"></i> Error
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fa fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0" id="rollbackErrorMessage" style="font-size: 1.1rem;"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Snapshot Rollback Confirmation Modal -->
    <div class="modal fade" id="snapshotRollbackConfirmModal" tabindex="-1" role="dialog" aria-labelledby="snapshotRollbackConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="snapshotRollbackConfirmModalLabel">
                        <i class="fa fa-undo"></i> Confirm Snapshot Rollback
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3" role="alert">
                        <i class="fa fa-exclamation-triangle"></i> <strong>Important Action</strong>
                    </div>
                    <p>Are you sure you want to rollback the project to snapshot <strong id="snapshotRollbackName"></strong>?</p>
                    <p class="text-muted small mb-0">This will restore all requirements to their state at this snapshot. The change history will be preserved.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmSnapshotRollbackBtn">
                        <i class="fa fa-undo"></i> Confirm Rollback
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- <div id="impact-loading" class="d-none text-center my-4">
        <div class="spinner-border text-danger" role="status">
            <span class="sr-only">Analyzing...</span>
        </div>
        <p class="mt-2">Analyzing impact with AI...</p>
    </div>

    <div class="section impact-section">
        <h2 class="section-title text-2xl font-semibold mb-4">Impact Analysis</h2>
        <div class="impact-box bg-pink-50 p-6 rounded-lg shadow-md text-gray-800" id="impact-analysis-container">
            @if(!empty($impact_analysis))
                @php
                    // Split text into sections using recognizable headers
                    preg_match_all('/\*\*(System Impact|User Impact|Workflow Impact)\*\*(.*?)(?=\*\*|$)/s', $impact_analysis, $matches, PREG_SET_ORDER);
                @endphp

                @foreach($matches as $section)
                    @php
                        $title = $section[1];
                        $content = trim($section[2]);
                        $points = preg_split('/\n+|\•\s*/', $content);
                    @endphp

                    <div class="mb-6">
                        <h3 class="font-bold text-lg text-red-600 mb-2">{{ $title }}</h3>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1 leading-relaxed">
                            @foreach($points as $point)
                                @if(trim($point))
                                    <li>{!! nl2br(e(trim($point))) !!}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @else
                <p class="text-gray-600 italic">Submit changes to see the impact analysis.</p>
            @endif
        </div>
    </div>
    </div>
</body> -->

</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
<script>
    $(document).on('click', '.analyze-impact-btn', function () {
        const requirementId = $(this).data('requirement-id');
        const field = $(this).data('field');
        const oldValue = $(this).data('old');
        const newValue = $(this).data('new');

        console.log("Requirement ID:", requirementId);
        console.log("Field:", field);
        console.log("Old Value:", oldValue);
        console.log("New Value:", newValue);

        const url = "{{ route('analyze.impact') }}";

        // Prepare changes payload
        const changes = [
            {
                requirement_id: requirementId,
                field: field,
                old: oldValue,
                new: newValue
            }
        ];

        $('#global-loading').css('display', 'flex');
        $('#impact-result-container').hide();

        $.ajax({
            url: url,
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                changes: changes
            },
            success: function (response) {
                const impactAnalysis = response.impact_analysis;
                let impactHtml = '';

                // Function to clean and format the text
                function cleanAndFormatText(text) {
                    if (typeof text === "object") {
                        return Object.entries(text)
                            .map(([key, value]) => {
                                const formattedKey = key
                                    .replace(/_/g, ' ')
                                    .replace(/^\w/, c => c.toUpperCase());
                                return `<strong>${formattedKey}:</strong><ul>${cleanAndFormatText(value)}</ul>`;
                            })
                            .join('');
                    }
                    if (typeof text !== "string") {
                        text = String(text || "");
                    }
                    return text
                        .replace(/^\s*-\s*/gm, '')
                        .replace(/\\n/g, '')
                        .trim()
                        .split('. ')
                        .filter(line => line.trim() !== '')
                        .map(line => `<li>${line.trim()}</li>`)
                        .join('');
                }

                // Process each item in the response
                impactAnalysis.forEach(item => {
                    const cleanImpact = cleanAndFormatText(item.impact.requirement);
                    const cleanRecommendation = item.recommendation
                        ? cleanAndFormatText(item.recommendation)
                        : '<li>No recommendation provided.</li>';
                    const cleanOtherRequirements = item.impact.other_requirements
                        ? cleanAndFormatText(item.impact.other_requirements)
                        : '<li>No other requirements appear to be impacted.</li>';

                    impactHtml += `
        <div class="mb-4">
            <h5>Project: <strong>${item.project_title}</strong> | Requirement #${item.requirement_number || item.requirement_id}</h5>
            <p><strong>Description:</strong> ${item.description}</p>
            <p><strong>Impact on Requirement:</strong></p>
            <ul>${cleanAndFormatText(item.impact.requirement)}</ul>
            <p><strong>Impact on Other Requirements:</strong></p>
            <ul>${cleanOtherRequirements}</ul>
            <p><strong>Recommendation:</strong></p>
            <ul>${cleanRecommendation}</ul>
        </div>`;
                });                // Update the HTML content
                $("#impact-result-container")
                    .empty()
                    .html(impactHtml)
                    .fadeIn();
            },
            error: function () {
                $('#impact-result-container').html('<div class="alert alert-danger">Error analyzing impact.</div>').fadeIn();
            },
            complete: function () {
                $('#global-loading').hide();
            }
        });
    });
    // Rollback flow using modal confirmation
    let rollbackCtx = { requirementId: null, version: null, projectId: null };
    
    // Project Rollback
    $(document).on('click', '.rollback-project-btn', function() {
        const projectId = $(this).data('project-id');
        const projectName = $(this).data('project-name');
        const version = $(this).data('version');
        rollbackCtx = { projectId, version, requirementId: null };
        $('#prjName').text(projectName);
        $('#prjVer').text(version);
        $('#prjVerSmall').text(version);
        $('#confirmProjectRollbackBtn').prop('disabled', false).text('Confirm Project Rollback');
        $('#projectRollbackConfirmModal').modal('show');
    });

    $('#confirmProjectRollbackBtn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).text('Rolling back...');
        $.ajax({
            url: "{{ route('project.rollback') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                project_id: rollbackCtx.projectId,
                target_version: rollbackCtx.version
            },
            success: function(response) {
                $('#projectRollbackConfirmModal').modal('hide');
                $('#rollbackSuccessMessage').text(response.message);
                $('#rollbackSuccessModal').modal('show');
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to rollback project.';
                $('#projectRollbackConfirmModal').modal('hide');
                $('#rollbackErrorMessage').text(message);
                $('#rollbackErrorModal').modal('show');
            },
            complete: function() {
                btn.prop('disabled', false).text('Confirm Project Rollback');
            }
        });
    });



    // Reload page when success modal OK is clicked
    $('#rollbackSuccessOkBtn').on('click', function() {
        location.reload();
    });
</script>
<script>
    const canRollbackProject = {{ auth()->check() && auth()->user()->role === 'project manager' ? 'true' : 'false' }};

    // Load a snapshot of the entire project at a selected version
    $('.project-version-select').on('change', function() {
        const projectId = $(this).data('project-id');
        const projectNumber = $(this).data('project-number') || $(this).closest('.project-section').data('project-number');
        const version = $(this).val();
        const container = $(`#project-snapshot-${projectId}`);
        container.empty();

        if (!version) {
            container.html('<div class="alert alert-secondary">Select a version to view the project snapshot.</div>');
            return;
        }

        container.html('<div class="alert alert-info mb-2">Loading version ' + version + '...</div>');

        $.ajax({
            url: "{{ route('project.version.snapshot') }}",
            method: 'GET',
            data: {
                project_id: projectId,
                target_version: version,
                project_number: projectNumber
            },
            success: function(response) {
                const requirements = response.requirements || [];
                if (!requirements.length) {
                    container.html('<div class="alert alert-warning">No requirements found for Version ' + version + ' in this project.</div>');
                    return;
                }

                let html = `<div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <strong>${response.project.name}</strong> — Version ${version}
                    </div>
                    <div class="list-group list-group-flush">`;

                requirements.forEach(item => {
                    html += `<div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div><strong>Requirement #${item.number}</strong></div>
                            <span class="badge badge-pill badge-secondary">${item.status || 'N/A'}</span>
                        </div>
                        <div class="text-muted small mb-2">Feature: ${item.feature || 'N/A'} | Author: ${item.author || 'System'} | ${item.timestamp || ''}</div>
                        <div><strong>Title:</strong> ${item.title || 'N/A'}</div>
                        <div><strong>Description:</strong> ${item.description || 'N/A'}</div>
                        <div class="d-flex flex-wrap mt-2" style="gap: 12px;">
                            <span><strong>Category:</strong> ${item.category || 'N/A'}</span>
                            <span><strong>Priority:</strong> ${item.priority || 'N/A'}</span>
                        </div>
                    </div>`;
                });

                html += `</div>`;

                if (canRollbackProject) {
                    html += `<div class="card-footer bg-white">
                        <button type="button" class="btn btn-sm btn-warning rollback-project-btn"
                            data-project-id="${projectId}"
                            data-project-name="${response.project.name}"
                            data-version="${version}">
                            <i class="fa fa-undo"></i> Rollback Project to Version ${version}
                        </button>
                    </div>`;
                }

                html += `</div>`;
                container.html(html);
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to load project snapshot.';
                container.html('<div class="alert alert-danger">' + message + '</div>');
            }
        });
    });
</script>
<script>
    // Toggle project collapse and persist state per project in localStorage
    $(document).on('click', '.toggle-project', function() {
        const btn = $(this);
        const projectId = btn.data('project-id');
        const body = $(`#project-body-${projectId}`);
        const icon = btn.find('i');
        body.collapse('toggle');
        body.on('shown.bs.collapse', function() {
            btn.attr('aria-expanded', 'true');
            icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
            try { localStorage.setItem(`project-open-${projectId}`, 'true'); } catch (e) {}
        });
        body.on('hidden.bs.collapse', function() {
            btn.attr('aria-expanded', 'false');
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
            try { localStorage.setItem(`project-open-${projectId}`, 'false'); } catch (e) {}
        });
    });

    // Initialize collapse state from localStorage
    $(function() {
        $('.project-section').each(function() {
            const section = $(this);
            const projectId = section.data('project-id');
            const saved = (localStorage.getItem(`project-open-${projectId}`) || 'false') === 'true';
            const body = $(`#project-body-${projectId}`);
            const btn = section.find('.toggle-project');
            const icon = btn.find('i');
            if (saved) {
                body.addClass('show');
                btn.attr('aria-expanded', 'true');
                icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
            }
        });
    });
</script>
<script>
    function filterChanges() {
        const requirementId = $('#filter-requirement').val();
        const field = $('#filter-field').val().toLowerCase();
        const author = $('#filter-author').val().toLowerCase();
        const version = $('#filter-version').val();

        $('.change-box.blue-border, .change-box.original-box').each(function () {
            const box = $(this);
            const boxReqId = box.data('requirement-id');
            const fieldText = box.find('p').html().match(/Modified Field:<\/strong>\s*([^<]+)/i);
            const authorText = box.find('p').html().match(/Author:<\/strong>\s*([^<]+)/i);
            const versionText = box.find('h4').text().match(/Version\s+([\d.]+)/i);

            const boxField = fieldText ? fieldText[1].trim().toLowerCase() : '';
            const boxAuthor = authorText ? authorText[1].trim().toLowerCase() : '';
            const boxVersion = versionText ? versionText[1].trim() : '';

            let match = true;
            if (requirementId && boxReqId != requirementId) match = false;
            if (field && boxField !== field) match = false;
            if (author && boxAuthor !== author) match = false;
            if (version && parseFloat(boxVersion).toFixed(1) !== parseFloat(version).toFixed(1)) match = false;

            box.toggle(match);
        });
    }

    $('#filter-requirement, #filter-field, #filter-author, #filter-version').on('change', filterChanges);
</script>
<script>
    $('#filter-date').on('change', function () {
        const selectedDate = $(this).val();
        const url = `/change-log?filter_date=${selectedDate}`;

        window.location.href = url;
    });
</script>

<!-- SNAPSHOTS VIEW FUNCTIONALITY -->
<script>
    // Handle view mode switching
    $('#viewMode').on('change', function() {
        const mode = $(this).val();
        if (mode === 'snapshots') {
            $('#snapshotsView').show();
            $('#changesView').hide();
            loadAllSnapshots();
        } else {
            $('#snapshotsView').hide();
            $('#changesView').show();
        }
    });

    // Load all project snapshots
    function loadAllSnapshots() {
        const container = $('#snapshotsContainer');
        container.html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div><p class="mt-2">Loading snapshots...</p></div>');

        // Get all projects from the page
        const projectIds = [];
        $('.project-section').each(function() {
            projectIds.push($(this).data('project-id'));
        });

        // Fetch snapshots for all projects
        Promise.all(projectIds.map(projectId => 
            fetch(`/project/${projectId}/snapshots`, {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).then(r => r.json())
        )).then(results => {
            let html = '';
            let hasSnapshots = false;

            results.forEach((res, idx) => {
                const projectId = projectIds[idx];
                const project = $(`[data-project-id="${projectId}"]`).first();
                const projectName = project.find('.section-title').text().trim();

                if (res.success && res.snapshots.length > 0) {
                    hasSnapshots = true;
                    html += `
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">📁 ${projectName}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                    `;

                    res.snapshots.forEach(snapshot => {
                        const createdDate = new Date(snapshot.created_at).toLocaleString();
                        const snapshotData = snapshot.snapshot_data;
                        const requirementsCount = snapshotData.requirements ? snapshotData.requirements.length : 0;
                        
                        html += `
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">📸 ${snapshot.snapshot_name}</h6>
                                        <small class="text-muted d-block">Saved: ${createdDate}</small>
                                        ${snapshot.description ? `<small class="text-muted d-block">${snapshot.description}</small>` : ''}
                                        <small class="text-muted d-block">By: ${snapshot.creator.name}</small>
                                        <small class="text-muted d-block"><i class="fa fa-list"></i> ${requirementsCount} requirement(s) saved</small>
                                    </div>
                                    <div class="btn-group-vertical">
                                        <button class="btn btn-sm btn-info mb-1" onclick="toggleSnapshotDetails(${snapshot.id})">
                                            <i class="fa fa-eye"></i> View Details
                                        </button>
                                        <button class="btn btn-sm btn-success" onclick="rollbackToSnapshot(${projectId}, ${snapshot.id}, '${snapshot.snapshot_name}')">
                                            <i class="fa fa-undo"></i> Rollback
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Snapshot Details (Hidden by default) -->
                                <div id="snapshot-details-${snapshot.id}" class="snapshot-details" style="display: none;">
                                    <hr>
                                    <h6 class="text-primary mb-3"><i class="fa fa-list-alt"></i> Requirements in this Snapshot:</h6>
                        `;
                        
                        if (snapshotData.features && snapshotData.features.length > 0) {
                            let featureIndex = 0;
                            snapshotData.features.forEach(feature => {
                                if (feature.requirements && feature.requirements.length > 0) {
                                    featureIndex++;
                                    html += `
                                        <div class="mb-3">
                                            <h6 class="text-secondary"><i class="fa fa-folder"></i> ${feature.feature} (${feature.category})</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="100">Req #</th>
                                                            <th>Description</th>
                                                            <th>Category</th>
                                                            <th>Priority</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                    `;
                                    
                                    feature.requirements.forEach((req, reqIndex) => {
                                        const reqNumber = `${idx + 1}.${featureIndex}.${reqIndex + 1}`;
                                        const priorityBadge = req.priority === 'High' ? 'badge-danger' : 
                                                            req.priority === 'Medium' ? 'badge-warning' : 'badge-info';
                                        const statusBadge = req.status === 'Approved' ? 'badge-success' : 
                                                          req.status === 'Completed' ? 'badge-primary' : 
                                                          req.status === 'Rejected' ? 'badge-danger' : 'badge-secondary';
                                        
                                        html += `
                                            <tr>
                                                <td><strong>${reqNumber}</strong></td>
                                                <td>${req.description}</td>
                                                <td>${req.category}</td>
                                                <td><span class="badge ${priorityBadge}">${req.priority}</span></td>
                                                <td><span class="badge ${statusBadge}">${req.status}</span></td>
                                            </tr>
                                        `;
                                    });
                                    
                                    html += `
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    `;
                                }
                            });
                        } else if (snapshotData.requirements && snapshotData.requirements.length > 0) {
                            html += `
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="100">Req #</th>
                                                <th>Description</th>
                                                <th>Category</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            
                            snapshotData.requirements.forEach((req, reqIndex) => {
                                const reqNumber = `${idx + 1}.${reqIndex + 1}`;
                                const priorityBadge = req.priority === 'High' ? 'badge-danger' : 
                                                    req.priority === 'Medium' ? 'badge-warning' : 'badge-info';
                                const statusBadge = req.status === 'Approved' ? 'badge-success' : 
                                                  req.status === 'Completed' ? 'badge-primary' : 
                                                  req.status === 'Rejected' ? 'badge-danger' : 'badge-secondary';
                                
                                html += `
                                    <tr>
                                        <td><strong>${reqNumber}</strong></td>
                                        <td>${req.description}</td>
                                        <td>${req.category}</td>
                                        <td><span class="badge ${priorityBadge}">${req.priority}</span></td>
                                        <td><span class="badge ${statusBadge}">${req.status}</span></td>
                                    </tr>
                                `;
                            });
                            
                            html += `
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        } else {
                            html += `<p class="text-muted">No requirements in this snapshot.</p>`;
                        }
                        
                        html += `
                                </div>
                            </div>
                        `;
                    });

                    html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });

            if (!hasSnapshots) {
                html = '<div class="col-md-12"><p class="text-muted text-center">No snapshots saved yet.</p></div>';
            }

            container.html(html);
        }).catch(err => {
            console.error('Error loading snapshots:', err);
            container.html('<div class="alert alert-danger">Error loading snapshots</div>');
        });
    }

    // Rollback to snapshot
    function rollbackToSnapshot(projectId, snapshotId, snapshotName) {
        // Store values and show confirmation modal
        window.pendingRollback = {
            projectId: projectId,
            snapshotId: snapshotId,
            snapshotName: snapshotName
        };
        
        $('#snapshotRollbackName').text(`"${snapshotName}"`);
        $('#snapshotRollbackConfirmModal').modal('show');
    }

    // Handle confirmed snapshot rollback
    $(document).on('click', '#confirmSnapshotRollbackBtn', function() {
        if (!window.pendingRollback) return;

        const { projectId, snapshotId, snapshotName } = window.pendingRollback;
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rolling back...');

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
                    $('#snapshotRollbackConfirmModal').modal('hide');
                    $('#rollbackSuccessMessage').text(res.message || 'Project rollbacked to snapshot successfully!');
                    $('#rollbackSuccessModal').modal('show');
                    $('#rollbackSuccessOkBtn').off('click').on('click', function() {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Unknown error';
                $('#snapshotRollbackConfirmModal').modal('hide');
                $('#rollbackErrorMessage').text('Error rolling back to snapshot: ' + errorMsg);
                $('#rollbackErrorModal').modal('show');
                btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Confirm Rollback');
                window.pendingRollback = null;
            }
        });
    });

    // Toggle snapshot details visibility
    function toggleSnapshotDetails(snapshotId) {
        const detailsDiv = $(`#snapshot-details-${snapshotId}`);
        const isVisible = detailsDiv.is(':visible');
        
        // Hide all other snapshot details first
        $('.snapshot-details').slideUp();
        
        // Toggle the clicked one
        if (!isVisible) {
            detailsDiv.slideDown();
        }
    }

    // Load snapshots on page load
    $(document).ready(function() {
        loadAllSnapshots();
    });
</script>