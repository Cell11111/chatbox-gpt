<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

        <style>
        .status-dropdown {
            min-width: 150px;
        }
    </style>

</head>

<body>
    <div class="container mt-4">
        <h4 class="text-center font-weight-bold">Edit Requirement</h4>
        <div class="card">
            <form method="GET" action="" class="form-inline mb-3">
                <!-- Filters -->
            </form>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Author</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                @php
                    $query = App\Models\Requirement::query();
                    $requirements = $query->orderBy('created_at', 'desc')->get();
                @endphp
                <tbody>
                    @foreach($requirements as $requirement)
                        <tr>
                            <td>{{ $requirement->title }}</td>
                            <td>{{ $requirement->description }}</td>
                            <td>
                                <select class="form-control status-dropdown" data-id="{{ $requirement->id }}">
                                    <option value="Applicable" {{ $requirement->status === 'Applicable' ? 'selected' : '' }}>
                                        Applicable</option>
                                    <option value="Not Applicable" {{ $requirement->status === 'Not Applicable' ? 'selected' : '' }}>Not Applicable</option>
                                    <option value="Pending" {{ $requirement->status === 'Pending' ? 'selected' : '' }}>Pending
                                    </option>
                                </select>
                            </td>
                            <td>{{ $requirement->category }}</td>
                            <td>{{ $requirement->priority }}</td>
                            <td>{{ $requirement->author }}</td>
                            <td>{{ $requirement->created_at->format('Y-m-d') }}</td>
                            <td>
                                <button class="btn btn-sm btn-info edit-btn" data-toggle="modal" data-target="#editModal"
                                    data-id="{{ $requirement->id }}" data-title="{{ $requirement->title }}"
                                    data-description="{{ $requirement->description }}"
                                    data-category="{{ $requirement->category }}"
                                    data-priority="{{ $requirement->priority }}">Edit</button>
                                <form method="POST" action="{{ route('requirements.destroy', $requirement->id) }}"
                                    style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" id="editTitle">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" id="editDescription"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control" id="editCategory">
                                <option value="Non-Functional">Non-Functional</option>
                                <option value="Functional">Functional</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <select name="priority" class="form-control" id="editPriority">
                                <option value="High">High</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).on('change', '.status-dropdown', function () {
                const id = $(this).data('id');
                const status = $(this).val();
                $.post('/update-status', { id, status }, function (response) {
                    alert('Status updated successfully');
                }).fail(function () {
                    alert('Failed to update status.');
                });
            });

            $('.edit-btn').on('click', function () {
                const id = $(this).data('id');
                $('#editForm').attr('action', `/requirements/${id}`);
                $('#editTitle').val($(this).data('title'));
                $('#editDescription').val($(this).data('description'));
                $('#editCategory').val($(this).data('category'));
                $('#editPriority').val($(this).data('priority'));
            });
        });
    </script>
</body>

</html>