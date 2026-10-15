<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        crossorigin="anonymous">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Submitted Changes</title>
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

        .navbar .nav-item {
            margin: 0 0.2rem;
        }

        .navbar {
            background-color: #f8f9fa !important;
            border-bottom: 2px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .change-title {
            color: #d93025;
            margin-top: 10px;
        }

        .original-box {
            border-left: 4px solid #28a745;
            background-color: #f1fdf4;
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
                    <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                        <a class="nav-link" href="/">Home</a>
                    </li>
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
                    <li class="nav-item {{ Request::is('change-log') ? 'active' : '' }}">
                        <a class="nav-link" href="/change-log">Change Log</a>
                    </li>

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
                                        <span class="badge badge-danger ml-1" style="font-size: 0.7rem; vertical-align: middle;">{{ $totalCount }}</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endauth

                    @if(auth()->check())
                        <li class="nav-item {{ Request::is('profile') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('profile.show') }}">
                                <i class="fa fa-user-circle"></i> {{ Auth::user()->name }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">@csrf</form>
                            <a href="#" class="nav-link text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        </li>
                    @else
                        <li class="nav-item {{ Request::is('login') ? 'active' : '' }}"><a class="nav-link" href="/login">Login</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h4 class="text-center font-weight-bold">My Submitted Change Requests</h4>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Requirement #</th>
                    <th>Project</th>
                    <th>Feature</th>
                    <th>Submitted At</th>
                    <th>Changes</th>
                    <th>Status</th>
                    <th>Reviewed By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingUpdates as $update)
                    @php
                        $updatedData = json_decode($update->updated_data, true) ?? [];
                        $previousData = json_decode($update->previous_data, true) ?? [];
                    @endphp
                    <tr>
                        @php
                            $proj = $update->requirement->project ?? null;
                            $feat = $update->requirement->feature ?? null;
                            $req = $update->requirement ?? null;

                            // Project index among all projects (ordered by id)
                            $projectIds = \App\Models\Project::orderBy('id')->pluck('id')->toArray();
                            $pIndex = $proj ? (array_search($proj->id, $projectIds) !== false ? array_search($proj->id, $projectIds) + 1 : '?') : '?';

                            // Feature index within the project (ordered by id)
                            $featureIds = $proj ? $proj->features()->orderBy('id')->pluck('id')->toArray() : [];
                            $fIndex = $feat ? (array_search($feat->id, $featureIds) !== false ? array_search($feat->id, $featureIds) + 1 : '?') : '?';

                            // Requirement index within the feature (ordered by id)
                            $reqIds = $feat ? $feat->requirements()->orderBy('id')->pluck('id')->toArray() : [];
                            $rIndex = $req ? (array_search($req->id, $reqIds) !== false ? array_search($req->id, $reqIds) + 1 : '?') : '?';

                            $hier = "$pIndex.$fIndex.$rIndex";
                        @endphp

                        <td>{{ $hier }}</td>
                        <td>{{ $update->requirement->project->name ?? 'N/A' }}</td>
                        <td>{{ $update->requirement->title ?? '—' }}</td>
                        <td>{{ $update->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <div><strong>New:</strong></div>
                            <div style="padding:8px; background:#f8f9fa; border-radius:4px;">
                                <p class="mb-1"><strong>Feature:</strong> {{ $updatedData['title'] ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Description:</strong> {{ $updatedData['description'] ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Priority:</strong> {{ $updatedData['priority'] ?? 'N/A' }}</p>
                            </div>
                            <div class="mt-2"><strong>Original:</strong></div>
                            <div class="original-box" style="padding:8px;">
                                <p class="mb-1"><strong>Feature:</strong> {{ $previousData['title'] ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Description:</strong> {{ $previousData['description'] ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Priority:</strong> {{ $previousData['priority'] ?? 'N/A' }}</p>
                            </div>
                        </td>
                        <td>{{ $update->status }}</td>
                        <td>{{ optional($update->reviewedBy)->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No pending changes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h3 class="mt-4">My Proposed Resolutions</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Conflict</th>
                    <th>Project</th>
                    <th>Submitted At</th>
                    <th>Requirement A</th>
                    <th>Requirement B</th>
                    <th>Resolution</th>
                    <th>Updated Data</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingResolutions as $resolution)
                    @php
                        $updatedData = json_decode($resolution->updated_data, true) ?? [];
                        $reqA = $resolution->requirementA ?? null;
                        $reqB = $resolution->requirementB ?? null;
                    @endphp
                    <tr>
                        <td>{{ $resolution->conflict_id ?? ($resolution->conflict->id ?? '—') }}</td>
                        <td>{{ optional($reqA->project ?? $reqB->project)->name ?? 'N/A' }}</td>
                        <td>{{ $resolution->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($reqA->description ?? 'N/A', 100) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($reqB->description ?? 'N/A', 100) }}</td>
                        <td><span class="badge badge-info">{{ $resolution->resolution }}</span></td>
                        <td>
                            @if($resolution->resolution === 'Combine and Reword')
                                <div style="padding:8px; background-color:#f8f9fa; border-radius:4px;">
                                    <p class="mb-1"><strong>Combined Description:</strong></p>
                                    <p class="mb-1">{{ $updatedData['description'] ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Combine Into:</strong>
                                        <span class="badge badge-secondary">
                                            {{ ($updatedData['combine_into'] ?? '') === 'requirement_a' ? 'Requirement A' : (($updatedData['combine_into'] ?? '') === 'requirement_b' ? 'Requirement B' : '—') }}
                                        </span>
                                    </p>
                                </div>
                            @elseif($resolution->resolution === 'Prioritize One Over the Other')
                                <div style="padding:8px; background-color:#f8f9fa; border-radius:4px;">
                                    <p class="mb-1"><strong>Archive Requirement:</strong></p>
                                    <p class="mb-0">{{ $updatedData['delete_requirement_description'] ?? 'N/A' }}</p>
                                </div>
                            @else
                                <div style="padding:8px; background-color:#f8f9fa; border-radius:4px;">
                                    <pre style="white-space:pre-wrap; margin:0;">{{ json_encode($updatedData, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @endif
                        </td>
                        <td>{{ $resolution->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No pending resolutions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>