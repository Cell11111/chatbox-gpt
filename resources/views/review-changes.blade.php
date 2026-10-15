<!DOCTYPE html>
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
    <title>Review Changes</title>
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

    <div class="header-container">
        <h4 class="text-center font-weight-bold">Review Pending Changes</h4>
    </div>
    <div class="container mt-5">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Requirement #</th>
                    <th>Project</th>
                    <th>Requirement</th>
                    <th>Submitted By</th>
                    <th>Changes</th>
                    <th>Status</th>
                    <th>Analyze Impact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Build a project and feature index map for requirement numbering
                    $allProjects = \App\Models\Project::orderBy('id')->get();
                    $projectIndexMap = [];
                    $pi = 0;
                    foreach ($allProjects as $proj) {
                        $pi++;
                        $projectIndexMap[$proj->id] = $pi;
                    }
                    
                    // Build feature index map per project
                    $featureIndexMap = [];
                    foreach ($pendingUpdates as $update) {
                        $projectId = $update->requirement->project_id;
                        $featureId = $update->requirement->feature_id;
                        
                        if (!isset($featureIndexMap[$projectId])) {
                            $featureIndexMap[$projectId] = [];
                            $allFeaturesInProject = \App\Models\ProjectFeature::where('project_id', $projectId)->orderBy('id')->get();
                            $fi = 0;
                            foreach ($allFeaturesInProject as $pf) {
                                $fi++;
                                $featureIndexMap[$projectId][$pf->id] = $fi;
                            }
                        }
                    }
                    
                    // Track counter for each feature
                    $featureReqCounter = [];
                @endphp
                @forelse($pendingUpdates as $update)
                    @php
                        $projectId = $update->requirement->project_id;
                        $featureId = $update->requirement->feature_id;
                        $projectNumber = $projectIndexMap[$projectId] ?? '0';
                        $featureIndex = $featureIndexMap[$projectId][$featureId] ?? '0';
                        
                        if (!isset($featureReqCounter[$featureId])) {
                            $featureReqCounter[$featureId] = 0;
                        }
                        $featureReqCounter[$featureId]++;
                        
                        $reqNumber = $projectNumber . '.' . $featureIndex . '.' . $featureReqCounter[$featureId];
                    @endphp
                    <tr data-requirement-id="{{ $update->requirement_id }}">
                        <!-- <td>{{ $update->id }}</td> -->
                        <td>{{ $reqNumber }}</td>
                        <td>{{ $update->requirement->project->name ?? 'N/A' }}</td>
                        <td>{{ $update->requirement->title }}</td>
                        <td>{{ $update->submittedBy->name }}</td>
                        <td>
                            @php
                                $updatedData = json_decode($update->updated_data, true);
                                $previousData = json_decode($update->previous_data, true);
                                $requirement = $update->requirement;
                            @endphp
                            <h6>Changes:</h6>
                            <div class="mb-2" style="padding: 8px; background-color: #f8f9fa; border-radius: 4px;">
                                @php
                                    $featureName = 'N/A';
                                    $featureChanged = isset($updatedData['feature_id']) || isset($previousData['feature_id']);
                                    if (isset($updatedData['feature_id']) && $updatedData['feature_id']) {
                                        $feature = \App\Models\ProjectFeature::find($updatedData['feature_id']);
                                        $featureName = $feature ? $feature->name : 'N/A';
                                    } elseif ($requirement->feature) {
                                        $featureName = $requirement->feature->feature;
                                    }
                                    
                                    $descChanged = isset($updatedData['description']) || isset($previousData['description']);
                                    $categoryChanged = isset($updatedData['category']) || isset($previousData['category']);
                                    $priorityChanged = isset($updatedData['priority']) || isset($previousData['priority']);
                                @endphp
                                <p class="mb-1" style="{{ $featureChanged ? 'background-color: #fff3cd; padding: 4px; border-radius: 3px;' : '' }}"><strong>Feature:</strong> {{ $featureName }}</p>
                                <p class="mb-1" style="{{ $descChanged ? 'background-color: #fff3cd; padding: 4px; border-radius: 3px;' : '' }}"><strong>Description:</strong> {{ $updatedData['description'] ?? $requirement->description }}</p>
                                <p class="mb-1" style="{{ $categoryChanged ? 'background-color: #fff3cd; padding: 4px; border-radius: 3px;' : '' }}"><strong>Category:</strong> {{ $updatedData['category'] ?? $requirement->category }}</p>
                                <p class="mb-0" style="{{ $priorityChanged ? 'background-color: #fff3cd; padding: 4px; border-radius: 3px;' : '' }}"><strong>Priority:</strong> {{ $updatedData['priority'] ?? $requirement->priority }}</p>
                            </div>
                            <h6 class="mt-2">Original:</h6>
                            <div style="padding: 8px; background-color: #f8f9fa; border-radius: 4px;">
                                @php
                                    $prevFeatureName = 'N/A';
                                    if (isset($previousData['feature_id']) && $previousData['feature_id']) {
                                        $prevFeature = \App\Models\ProjectFeature::find($previousData['feature_id']);
                                        $prevFeatureName = $prevFeature ? $prevFeature->name : 'N/A';
                                    } elseif ($requirement->feature) {
                                        $prevFeatureName = $requirement->feature->name;
                                    }
                                @endphp
                                <p class="mb-1" style="{{ $featureChanged ? 'background-color: #f8d7da; padding: 4px; border-radius: 3px;' : '' }}"><strong>Feature:</strong> {{ $prevFeatureName }}</p>
                                <p class="mb-1" style="{{ $descChanged ? 'background-color: #f8d7da; padding: 4px; border-radius: 3px;' : '' }}"><strong>Description:</strong> {{ $previousData['description'] ?? $requirement->description }}
                                </p>
                                <p class="mb-1" style="{{ $categoryChanged ? 'background-color: #f8d7da; padding: 4px; border-radius: 3px;' : '' }}"><strong>Category:</strong> {{ $previousData['category'] ?? $requirement->category }}</p>
                                <p class="mb-0" style="{{ $priorityChanged ? 'background-color: #f8d7da; padding: 4px; border-radius: 3px;' : '' }}"><strong>Priority:</strong> {{ $previousData['priority'] ?? $requirement->priority }}</p>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-warning">{{ $update->status }}</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm analyze-impact-btn"
                                data-id="{{ $update->id }}">
                                Analyze Impact
                            </button>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('pending-updates.review', $update->id) }}"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>

                            <form method="POST" action="{{ route('pending-updates.review', $update->id) }}"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>

                        <!-- Hidden: NEW values -->
                        <td class="d-none new-title">{{ $updatedData['title'] ?? '' }}</td>
                        <td class="d-none new-description">{{ $updatedData['description'] ?? '' }}</td>
                        <td class="d-none new-priority">{{ $updatedData['priority'] ?? '' }}</td>

                        <!-- Hidden: OLD values -->
                        <td class="d-none old-title">{{ $previousData['title'] ?? '' }}</td>
                        <td class="d-none old-description">{{ $previousData['description'] ?? '' }}</td>
                        <td class="d-none old-priority">{{ $previousData['priority'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No pending changes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h3>Pending Resolutions</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Conflict ID</th>
                    <th>Project</th>
                    <th>Requirement A</th>
                    <th>Requirement B</th>
                    <th>Resolution</th>
                    <th>Updated Data</th>
                    <th>Proposed By</th>
                    <th>Comments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingResolutions as $resolution)
                    @php
                        $updatedData = json_decode($resolution->updated_data, true);
                    @endphp
                    <tr>
                        <td>{{ $resolution->conflict_id }}</td>
                        <td>{{ $resolution->requirementA->project->name ?? 'N/A' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($resolution->requirementA->description ?? 'N/A', 100) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($resolution->requirementB->description ?? 'N/A', 100) }}</td>
                        <td>
                            <span class="badge badge-info">{{ $resolution->resolution }}</span>
                        </td>
                        <td>
                            @if($resolution->resolution === 'Combine and Reword')
                                <div style="padding: 8px; background-color: #f8f9fa; border-radius: 4px;">
                                    <p class="mb-1"><strong>Combined Description:</strong></p>
                                    <p class="mb-1">{{ $updatedData['description'] ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Combine Into:</strong> 
                                        <span class="badge badge-secondary">
                                            {{ $updatedData['combine_into'] === 'requirement_a' ? 'Requirement A' : 'Requirement B' }}
                                        </span>
                                    </p>
                                </div>
                            @elseif($resolution->resolution === 'Prioritize One Over the Other')
                                <div style="padding: 8px; background-color: #f8f9fa; border-radius: 4px;">
                                    <p class="mb-1"><strong>Archive Requirement:</strong></p>
                                    <p class="mb-0">{{ $updatedData['delete_requirement_description'] ?? 'N/A' }}</p>
                                </div>
                            @else
                                <span class="text-muted">{{ $resolution->updated_data }}</span>
                            @endif
                        </td>
                        <td>{{ $resolution->proposedBy->name }}</td>
                        <td>{{ $resolution->comments ?? 'No comments provided.' }}</td>
                        <td>
                            <form method="POST" action="{{ route('pending-resolutions.review', $resolution->id) }}"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('pending-resolutions.review', $resolution->id) }}"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No pending resolutions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
    <script>
        $(document).on('click', '.analyze-impact-btn', function () {
            const updateId = $(this).data('id');
            const row = $(this).closest('tr');
            const requirementId = row.data('requirement-id'); // Add an attribute for requirement_id

            const oldValue = {
                title: row.find('.old-title').text().trim(),
                description: row.find('.old-description').text().trim(),
                priority: row.find('.old-priority').text().trim()
            };

            const newValue = {
                title: row.find('.new-title').text().trim(),
                description: row.find('.new-description').text().trim(),
                priority: row.find('.new-priority').text().trim()
            };


            console.log("Update ID:", updateId);
            console.log("Requirement ID:", requirementId);
            console.log("Old Value:", oldValue);
            console.log("New Value:", newValue);

            const url = "{{ route('analyze.impact') }}";

            // Prepare changes payload (this can vary based on your actual data structure)
            const changes = [];

            if (oldValue.title !== newValue.title) {
                changes.push({
                    requirement_id: requirementId,
                    field: "title",
                    old: oldValue.title,
                    new: newValue.title
                });
            }

            if (oldValue.description !== newValue.description) {
                changes.push({
                    requirement_id: requirementId,
                    field: "description",
                    old: oldValue.description,
                    new: newValue.description
                });
            }

            if (oldValue.priority !== newValue.priority) {
                changes.push({
                    requirement_id: requirementId,
                    field: "priority",
                    old: oldValue.priority,
                    new: newValue.priority
                });
            }

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
                                        .replace(/_/g, ' ') // Replace underscores with spaces
                                        .replace(/^\w/, c => c.toUpperCase()); // Capitalize the first letter
                                    return `<strong>${formattedKey}:</strong><ul>${cleanAndFormatText(value)}</ul>`;
                                })
                                .join('');
                        }
                        if (typeof text !== "string") {
                            text = String(text || "");
                        }
                        return text
                            .replace(/^\s*-\s*/gm, '') // Remove leading dashes
                            .replace(/\\n/g, '') // Remove "\n"
                            .trim() // Remove trailing/leading whitespace
                            .split('. ') // Split into sentences
                            .filter(line => line.trim() !== '') // Filter out empty lines
                            .map(line => `<li>${line.trim()}</li>`) // Convert to bullet points
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
                    });

                    // Update the HTML content
                    $("#impact-result-container")
                        .empty() // Clear previous content
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
    </script>
</body>

</html>