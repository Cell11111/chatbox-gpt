<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
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
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Profile Information</h4>
                </div>
                @if(session('success'))
                    <div class="alert alert-success mt-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card-body">
                    <div class="profile-info">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="profile-avatar text-center">
                                    <!-- Avatar placeholder - could be replaced with actual avatar implementation -->
                                    <div class="avatar-placeholder rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto"
                                        style="width: 100px; height: 100px; font-size: 2.5rem;">
                                        {{ strtoupper(substr(Auth::user()->name ?? '', 0, 1)) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="profile-details">
                                    <h5 class="text-primary mb-3">Account Details</h5>
                                    <div class="mb-3">
                                        <label class="fw-bold">Name:</label>
                                        <p class="mb-2">{{ Auth::user()->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Email:</label>
                                        <p class="mb-2">{{ Auth::user()->email ?? 'N/A' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Role:</label>
                                        <p class="mb-2">
                                            <span class="badge bg-info">
                                                {{ ucwords(Auth::user()->role ?? 'N/A') }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-bold">Member Since:</label>
                                        <p class="mb-2">
                                            {{ Auth::user()->created_at ? Auth::user()->created_at->format('F j, Y') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Summary Section -->
                        <div class="activity-summary mt-4">
                            <h5 class="text-primary mb-3">Activity Summary</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6>Requirements Created</h6>
                                            <h4>{{ Auth::user()->requirements()->count() }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <!-- Add more activity metrics as needed -->
                            </div>
                        </div>

                        <!-- Edit Profile Button -->
                        <div class="mt-4">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#editProfileModal">
                                Edit Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="{{ Auth::user()->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation"
                            name="new_password_confirmation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>

</html>