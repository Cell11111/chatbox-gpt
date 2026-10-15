<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"
    integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Include Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AI Requirement Manager</title>
  <style>
    body {
      font-family: Segoe UI, sans-serif;
      margin: 20px;
      background-color: #f9f9f9;
    }

    h2 {
      font-size: 18px;
      margin-bottom: 10px;
    }

    .container {
      display: flex;
      gap: 20px;
    }

    .conflicts,
    .details {
      background-color: white;
      border: 1px solid #ccc;
      border-radius: 4px;
      padding: 20px;
    }

    .conflicts {
      flex: 2;
    }

    .details {
      flex: 1.5;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th,
    td {
      text-align: left;
      padding: 10px;
      border-bottom: 1px solid #e0e0e0;
    }

    th {
      background-color: #f0f0f0;
    }

    .high {
      color: red;
      font-weight: bold;
    }

    button {
      display: block;
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      border: none;
      border-radius: 4px;
      font-weight: bold;
      cursor: pointer;
    }

    .blue {
      background-color: #007bff;
      color: white;
    }

    #ai-loading {
      display: none;
      text-align: center;
      margin: 20px 0;
    }

    #ai-loading .spinner-border {
      width: 3rem;
      height: 3rem;
    }

    .header {
      font-size: 16px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .no-conflicts {
      padding: 20px;
      text-align: center;
      background-color: #f8f9fa;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      color: #6c757d;
      font-style: italic;
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
      position: relative;
    }

    .navbar .nav-link:hover {
      background-color: #f8f9fa;
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

    .button-container {
      display: flex;
      justify-content: center;
      /* Align button to the left */
      align-items: center;
    }

    #analyze-requirements {
      width: auto;
      /* Let the button size adapt to its text content */
      padding: 10px 20px;
    }

    .conflict-card {
      border: 1px solid #e2e2e2;
      border-radius: 8px;
      background: white;
    }

    .conflict-header {
      background: #f8f9fa;
      border-bottom: 1px solid #e5e5e5;
      border-radius: 8px 8px 0 0;
    }

    .conflict-body p {
      margin-bottom: 0.5rem;
    }

    .conflict-actions button {
      min-width: 150px;
    }

    .gap-2>* {
      margin-left: 10px;
    }

    /* Modal styling improvements */
    .modal-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-bottom: none;
      border-radius: 8px 8px 0 0;
      padding: 1.25rem 1.5rem;
    }

    .modal-header .modal-title {
      font-weight: 600;
      font-size: 1.25rem;
    }

    .modal-header .close {
      color: white;
      opacity: 0.8;
      text-shadow: none;
      font-size: 1.1rem;
      font-weight: 300;
      padding: 0.25rem 0.5rem;
      margin: -0.5rem -0.5rem -0.5rem auto;
      width: auto;
      height: auto;
      line-height: 1;
    }

    .modal-header .close:hover {
      opacity: 1;
      color: white;
    }

    .modal-content {
      border: none;
      border-radius: 8px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .modal-body {
      padding: 1.5rem;
    }

    .modal-body > p:first-child {
      color: #555;
      font-size: 1rem;
      margin-bottom: 1.5rem;
    }

    #resolutionChoiceModal .btn-block {
      text-align: left;
      padding: 1rem 1.25rem;
      border-radius: 6px;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    #resolutionChoiceModal .btn-block:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    #resolutionChoiceModal .btn-primary {
      background: #007bff;
      border-color: #007bff;
    }

    #resolutionChoiceModal .btn-primary:hover {
      background: #0056b3;
      border-color: #0056b3;
    }

    #resolutionChoiceModal .btn-warning {
      background: #ffc107;
      border-color: #ffc107;
      color: #212529;
    }

    #resolutionChoiceModal .btn-warning:hover {
      background: #e0a800;
      border-color: #e0a800;
    }

    #resolutionChoiceModal .btn-block strong {
      display: block;
      font-size: 1.1rem;
      margin-bottom: 0.25rem;
    }

    #resolutionChoiceModal .btn-block small {
      opacity: 0.9;
      font-size: 0.875rem;
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
            <a class="nav-link dropdown-toggle" href="#" id="projectsDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
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

  <div class="header-container">
    <h4 class="text-center font-weight-bold">Conflict Detection</h4>
  </div>

  <div class="container">
    <!-- Left Panel: Detected Conflicts -->
    <div class="conflicts">
      <h2>Detected Conflicts</h2>

      {{-- LEFT: cards with conflicts --}}
      @forelse($conflicts as $conflict)
        <div class="conflict-card shadow-sm mb-4">

          <!-- Header -->
          <div class="conflict-header p-3 d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-1 font-weight-bold">Conflict #C{{ $conflict->id }}</h5>
              <small class="text-muted">{{ $conflict->project->name ?? 'Unknown Project' }}</small>
            </div>
            <!-- <span class="badge badge-danger px-3 py-2" style="font-size: 0.9rem;">
              Severity: {{ ucfirst($conflict->severity) }}
            </span> -->
          </div>

          <!-- Body -->
          <div class="conflict-body p-3">

            <div class="mb-2">
              <strong>Involved Requirements:</strong><br>
              @php
                // Build feature index map for the conflict's project
                $projId = $conflict->project_id;
                $allProjects = \App\Models\Project::orderBy('id')->get();
                $projectNumber = 0;
                foreach ($allProjects as $proj) {
                  $projectNumber++;
                  if ($proj->id === $projId)
                    break;
                }
                
                $allFeaturesInProject = \App\Models\ProjectFeature::where('project_id', $projId)->orderBy('id')->get();
                $featureIndexMap = [];
                $fi = 0;
                foreach ($allFeaturesInProject as $pf) {
                  $fi++;
                  $featureIndexMap[$pf->id] = $fi;
                }

                $reqA = $conflict->requirementA ?? null;
                $reqB = $conflict->requirementB ?? null;

                $reqA_num = 'N/A';
                if ($reqA) {
                  $featureId = $reqA->feature_id;
                  $featureIndex = $featureIndexMap[$featureId] ?? '0';
                  $reqsInFeature = \App\Models\Requirement::where('project_id', $projId)
                    ->where('feature_id', $featureId)
                    ->orderBy('id')
                    ->get();
                  $pos = 0;
                  foreach ($reqsInFeature as $r) {
                    $pos++;
                    if ($r->id === $reqA->id)
                      break;
                  }
                  $reqA_num = $projectNumber . '.' . $featureIndex . '.' . $pos;
                }

                $reqB_num = 'N/A';
                if ($reqB) {
                  $featureId = $reqB->feature_id;
                  $featureIndex = $featureIndexMap[$featureId] ?? '0';
                  $reqsInFeature = \App\Models\Requirement::where('project_id', $projId)
                    ->where('feature_id', $featureId)
                    ->orderBy('id')
                    ->get();
                  $pos = 0;
                  foreach ($reqsInFeature as $r) {
                    $pos++;
                    if ($r->id === $reqB->id)
                      break;
                  }
                  $reqB_num = $projectNumber . '.' . $featureIndex . '.' . $pos;
                }
              @endphp

              <span class="badge badge-primary">{{ $reqA_num }}</span>
              <span class="badge badge-primary">{{ $reqB_num }}</span>
            </div>

            <div class="mt-3">
              <strong>Summary:</strong>
              <p class="text-muted">{{ $conflict->summary }}</p>
            </div>

            <div class="mt-3">
              <strong>Status:</strong>
              <span class="badge badge-secondary">{{ $conflict->status }}</span>
            </div>

          </div>

          <!-- Action buttons -->
          <div class="conflict-actions p-3 border-top d-flex justify-content-end gap-2">
            <button class="btn btn-success mr-2 btn-apply-resolution" data-id="{{ $conflict->id }}"
              data-requirement-a-description="{{ $conflict->requirementA->description }}"
              data-requirement-b-description="{{ $conflict->requirementB->description }}"
              data-resolution="{{ $conflict->resolution }}">
              Resolve Conflict
            </button>

            <button class="btn btn-outline-danger btn-dismiss" data-id="{{ $conflict->id }}" data-action="dismiss">
              Dismiss
            </button>
          </div>

        </div>
      @empty
        <div class="no-conflicts">
          <p>No requirement conflicts detected.</p>
        </div>
      @endforelse
    </div>
  </div>
  <div class="modal" id="resolutionChoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Choose Resolution Method</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>How would you like to resolve this conflict?</p>
          <div class="form-group">
            <button type="button" class="btn btn-primary btn-block" id="btn-choose-combine">
              <strong>Combine and Reword</strong><br>
              <small>Merge both requirements into one improved requirement</small>
            </button>
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-warning btn-block" id="btn-choose-prioritize">
              <strong>Prioritize One</strong><br>
              <small>Keep one requirement and archive the other</small>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="combineModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Combine and Reword Requirements</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="combineForm">
            <input type="hidden" id="combine_conflict_id" name="conflict_id">
            <div class="form-group">
              <label for="requirement_a">Requirement A:</label>
              <p id="combine_requirement_a" class="form-control-static"></p>
            </div>
            <div class="form-group">
              <label for="requirement_b">Requirement B:</label>
              <p id="combine_requirement_b" class="form-control-static"></p>
            </div>
            <div class="form-group">
              <label>Choose Requirement to Combine Into:</label>
              <div>
                <label>
                  <input type="radio" name="combine_into" value="requirement_a" required>
                  Requirement A: <span id="combine_into_a_description"></span>
                </label>
              </div>
              <div>
                <label>
                  <input type="radio" name="combine_into" value="requirement_b">
                  Requirement B: <span id="combine_into_b_description"></span>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label>Suggested by AI:</label>
              <div id="ai_suggestion_container" class="mb-2" style="display:none;">
                <textarea id="ai_suggestion_text" class="form-control" readonly
                  style="min-height:120px; max-height:600px; overflow:auto; resize:vertical; background:#f8f9fa; white-space:pre-wrap;"></textarea>
              </div>
              <label for="combined_description">Combined Requirement:</label>
              <textarea id="combined_description" name="combined_description" class="form-control" rows="3"
                required></textarea>
            </div>
            <div class="form-group">
              @auth
                @if(auth()->user()->role === 'software engineer')
                  <label for="comments">Add Comment:</label>
                  <textarea id="comments" name="comments" class="form-control" rows="3"
                    placeholder="Optional comment"></textarea>
                @endif
              @endauth
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="modal" id="prioritizeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Prioritize Requirement</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="prioritizeForm">
            <input type="hidden" id="conflict_id" name="conflict_id">
            <div class="form-group">
              <label>Choose Requirement to Prioritize:</label>
              <div>
                <label>
                  <input type="radio" name="priority" value="requirement_a" required>
                  Requirement A: <span id="requirement_a_description"></span>
                </label>
              </div>
              <div>
                <label>
                  <input type="radio" name="priority" value="requirement_b">
                  Requirement B: <span id="requirement_b_description"></span>
                </label>
              </div>
            </div>
            <div class="form-group">
              @auth
                @if(auth()->user()->role === 'software engineer')
                  <label for="comments">Add Comment:</label>
                  <textarea id="comments" name="comments" class="form-control" rows="3"
                    placeholder="Enter your comment"></textarea>
                @endif
              @endauth
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- AI Loading Indicator -->
  <div id="ai-loading">
    <div class="spinner-border text-primary" role="status">
      <span class="sr-only">Analyzing...</span>
    </div>
    <p>Analyzing requirements with AI...</p>
  </div>

  <div class="button-container">
    <button id="analyze-requirements" class="btn blue">Analyze Requirements</button>
  </div>

  <!-- Dismiss Confirmation Modal -->
  <div class="modal fade" id="dismissConfirmModal" tabindex="-1" role="dialog"
    aria-labelledby="dismissConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dismissConfirmModalLabel">Confirm Dismiss Conflict</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to dismiss this conflict?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" id="confirm-dismiss-btn" class="btn btn-danger">Dismiss</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
  <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="successModalLabel">
            <i class="fa fa-check-circle"></i> Success
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center py-4">
          <i class="fa fa-check-circle text-success" style="font-size: 3rem;"></i>
          <p class="mt-3 mb-0" id="successMessage" style="font-size: 1.1rem;"></p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success" id="successModalOkBtn">OK</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
  <script>
    $('#analyze-requirements').on('click', function () {
      // Show AI loading indicator
      $('#ai-loading').show();

      $.ajax({
        url: '/analyze-all-requirements',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
          $('#successMessage').text(res.message || 'Analysis complete.');
          $('#successModal').modal('show');
        },
        error: function (err) {
          alert('Error: ' + err.responseJSON?.message || 'Something went wrong.');
        },
        complete: function () {
          // Hide AI loading indicator after request is complete
          $('#ai-loading').hide();
        }
      });
    });
  </script>
  <script>
    let currentConflictData = {};

    $('.btn-apply-resolution').on('click', function () {
      const conflictId = $(this).data('id');
      const requirementADescription = $(this).data('requirement-a-description');
      const requirementBDescription = $(this).data('requirement-b-description');

      // Store the conflict data for later use
      currentConflictData = {
        conflictId: conflictId,
        requirementADescription: requirementADescription,
        requirementBDescription: requirementBDescription
      };

      // Show the resolution choice modal
      $('#resolutionChoiceModal').modal('show');
    });

    // Handle resolution choice
    $('#btn-choose-combine').on('click', function () {
      $('#resolutionChoiceModal').modal('hide');

      // Populate the modal for combining
      $('#combine_conflict_id').val(currentConflictData.conflictId);
      $('#combine_requirement_a').text(currentConflictData.requirementADescription);
      $('#combine_requirement_b').text(currentConflictData.requirementBDescription);
      $('#combine_into_a_description').text(currentConflictData.requirementADescription);
      $('#combine_into_b_description').text(currentConflictData.requirementBDescription);

      // Show combine modal and request AI suggestion (do NOT prefill combined textarea)
      $('#combined_description').val('');
      $('#combined_description').prop('disabled', false);
      // show placeholder in AI suggestion container (use textarea value)
      $('#ai_suggestion_text').val('Generating suggested combined requirement...');
      $('#ai_suggestion_container').show();
      $('#combineModal').modal('show');

      // Fetch AI suggestion
      $.ajax({
        url: '/suggest-combined',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { conflict_id: currentConflictData.conflictId },
        success: function (res) {
          if (res.suggestion) {
            // Display suggestion above textarea but DO NOT prefill the editable field
            $('#ai_suggestion_text').val(res.suggestion);
            $('#ai_suggestion_container').show();
          } else {
            // No suggestion returned
            $('#ai_suggestion_container').hide();
            $('#ai_suggestion_text').val('');
          }
        },
        error: function (err) {
          console.error('Failed to fetch AI suggestion', err);
          $('#ai_suggestion_container').hide();
          $('#ai_suggestion_text').val('');
          $('#combined_description').val('');
        },
        complete: function () {
          $('#combined_description').prop('disabled', false);
        }
      });
    });

    $('#btn-choose-prioritize').on('click', function () {
      $('#resolutionChoiceModal').modal('hide');

      // Populate the modal for prioritization
      $('#conflict_id').val(currentConflictData.conflictId);
      $('#requirement_a_description').text(currentConflictData.requirementADescription);
      $('#requirement_b_description').text(currentConflictData.requirementBDescription);

      // Show the prioritization modal
      $('#prioritizeModal').modal('show');
    });
    $('#combineForm').on('submit', function (e) {
      e.preventDefault();

      const formData = $(this).serialize();
      console.log(formData);
      // Directly make an AJAX call for "Combine and Reword"
      $.ajax({
        url: '/apply-resolution',
        type: 'POST',
        data: formData, // Only conflict ID is needed
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
          $('#combineModal').modal('hide');
          $('#successMessage').text(res.message);
          $('#successModal').modal('show');
        },
        error: function (err) {
          alert('Error: ' + (err.responseJSON?.message || 'Something went wrong.'));
        }
      });
    });

    // Submit handler for prioritization form
    $('#prioritizeForm').on('submit', function (e) {
      e.preventDefault();

      const formData = $(this).serialize();

      $.ajax({
        url: '/apply-resolution',
        type: 'POST',
        data: formData,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
          $('#prioritizeModal').modal('hide');
          $('#successMessage').text(res.message);
          $('#successModal').modal('show');
        },
        error: function (err) {
          alert('Error: ' + (err.responseJSON?.message || 'Something went wrong.'));
        }
      });
    });

    // Handle OK button in success modal
    $('#successModalOkBtn').on('click', function() {
      location.reload();
    });
  </script>
  <script>
    // Dismiss flow using confirmation modal
    let conflictToDismissId = null;

    // Open modal when dismiss button clicked
    $(document).on('click', '.btn-dismiss', function () {
      const conflictId = $(this).data('id');
      conflictToDismissId = conflictId;
      // Optionally populate modal details here
      $('#dismissConfirmModal').modal('show');
    });

    // Confirm dismiss in modal
    $('#confirm-dismiss-btn').on('click', function () {
      if (!conflictToDismissId) return;

      const btn = $(this);
      btn.prop('disabled', true).text('Dismissing...');

      $.ajax({
        url: '/dismiss-conflict',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { conflict_id: conflictToDismissId },
        success: function (res) {
          // remove the conflict card from the DOM if present
          const card = $(`[data-id="${conflictToDismissId}"]`).closest('.conflict-card');
          if (card && card.length) {
            card.fadeOut(200, function () { $(this).remove(); });
          } else {
            // fallback: reload
            location.reload();
          }

          $('#dismissConfirmModal').modal('hide');
          conflictToDismissId = null;
        },
        error: function (err) {
          alert('Error: ' + (err.responseJSON?.message || 'Something went wrong.'));
        },
        complete: function () {
          btn.prop('disabled', false).text('Dismiss');
        }
      });
    });
  </script>
</body>

</html>