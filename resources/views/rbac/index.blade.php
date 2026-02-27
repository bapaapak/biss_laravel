@extends('layouts.app')

@section('content')
    <style>
        .tab-pills .nav-link {
            border-radius: 20px;
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #64748b;
            background: #f1f5f9;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
        }

        .tab-pills .nav-link.active {
            background: #0d6efd;
            color: #fff;
        }

        .card-role {
            transition: transform 0.2s;
        }

        .card-role:hover {
            transform: translateY(-5px);
        }
    </style>

    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Access Control (RBAC)</h3>
        <p class="text-muted mb-0">Manage system roles, permissions, and user assignments.</p>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav tab-pills mb-4" id="rbacTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles" type="button" role="tab"
                aria-controls="roles" aria-selected="true"><i class="fas fa-user-shield me-1"></i> Roles</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions" type="button"
                role="tab" aria-controls="permissions" aria-selected="false"><i class="fas fa-lock me-1"></i>
                Permissions</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab"
                aria-controls="users" aria-selected="false"><i class="fas fa-users me-1"></i> User Assignments</a>
        </li>
    </ul>

    <div class="tab-content" id="rbacTabContent">

        <!-- ROLES TAB -->
        <div class="tab-pane fade show active" id="roles" role="tabpanel" aria-labelledby="roles-tab">
            <div class="row g-4">
                @foreach ($roles as $role)
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm card-role">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0 text-primary">{{ $role }}</h5>
                                    <i class="fas fa-shield-alt text-muted opacity-25 fa-2x"></i>
                                </div>
                                <p class="text-muted small">
                                    Role for system access level: <strong>{{ $role }}</strong>.
                                </p>
                                <hr class="my-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><i class="fas fa-users me-1"></i>
                                        {{ \App\Models\User::where('role', $role)->count() }} Users</small>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                        onclick="filterUsersByRole('{{ $role }}')">View Details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- PERMISSIONS TAB -->
        <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0">Role Permissions Matrix</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Role</th>
                                <th>Assigned Permissions</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $role => $perms)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $role }}</td>
                                    <td>
                                        @foreach ($perms as $perm)
                                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $perm }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 edit-perm-btn"
                                            data-role="{{ $role }}" data-permissions='@json($perms)'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- USERS TAB -->
        <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h6 class="fw-bold m-0 me-3">Registered Users</h6>
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="fas fa-search text-muted"></i></span>
                            <input type="text" id="userSearch" class="form-control border-start-0"
                                placeholder="Search by name or role..." onkeyup="filterUsersTable()">
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus me-1"></i> Add User
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="usersTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $user->full_name }}</td>
                                    <td class="text-muted">{{ $user->username }}</td>
                                    <td>
                                        <span class="badge {{ $user->role == 'Admin' ? 'bg-danger' : 'bg-info text-dark' }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td>{{ $user->department }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                            onclick="editUser({{ $user->id }}, '{{ addslashes($user->full_name) }}', '{{ addslashes($user->username) }}', '{{ $user->role }}', '{{ $user->department }}', {{ json_encode($user->customers->pluck('id')) }})">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        @if (auth()->user()->role === 'Super Admin')
                                            <form action="{{ route('rbac.user.destroy', $user->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($users->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                        <div class="text-muted small">
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                        </div>
                        <div>
                            {{ $users->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('rbac.user.store') }}" method="POST" onsubmit="return validateUsername('addUsername')">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" id="addUsername" name="username" class="form-control" required
                                oninput="checkUsernameSpaces(this)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach ($depts as $d)
                                    <option value="{{ $d->dept_name }}">{{ $d->dept_code }} | {{ $d->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save User</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editUserForm" action="" method="POST" onsubmit="return validateUsername('editUserUser')">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" id="editUserName" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" id="editUserUser" name="username" class="form-control" required readonly
                                style="background-color: #e9ecef;">
                            <small class="text-muted">Username cannot be changed.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <small class="text-muted">(Leave blank to keep
                                    current)</small></label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select id="editUserRole" name="role" class="form-select" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select id="editUserDept" name="department" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach ($depts as $d)
                                    <option value="{{ $d->dept_name }}">{{ $d->dept_code }} | {{ $d->dept_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Assigned Customers</label>
                            <p class="text-muted small mb-1">Applicable for User/Dept Head roles.</p>

                            <div class="accordion" id="customerAccordion" style="max-height: 300px; overflow-y: auto;">
                                @foreach($groupedCustomers as $category => $grpParams)
                                    @if($grpParams->isEmpty()) @continue @endif
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingCust{{ $loop->index }}">
                                            <button class="accordion-button collapsed py-2 small" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseCust{{ $loop->index }}"
                                                aria-expanded="false" aria-controls="collapseCust{{ $loop->index }}">
                                                {{ $category ?: 'Other/Uncategorized' }} <span
                                                    class="badge bg-secondary ms-2">{{ $grpParams->count() }}</span>
                                            </button>
                                        </h2>
                                        <div id="collapseCust{{ $loop->index }}" class="accordion-collapse collapse"
                                            data-bs-parent="#customerAccordion" aria-labelledby="headingCust{{ $loop->index }}">
                                            <div class="accordion-body p-2">
                                                @foreach($grpParams as $c)
                                                    <div class="form-check">
                                                        <input class="form-check-input customer-check" type="checkbox"
                                                            name="customers[]" value="{{ $c->id }}"
                                                            id="cust_{{ $loop->parent->index }}_{{ $c->id }}"
                                                            data-cust-id="{{ $c->id }}">
                                                        <label class="form-check-label small"
                                                            for="cust_{{ $loop->parent->index }}_{{ $c->id }}">
                                                            {{ $c->customer_code }} - {{ $c->customer_name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Permissions Modal -->
    <div class="modal fade" id="editPermissionsModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('rbac.permissions.update') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Role Permissions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" id="editPermRole" name="role" class="form-control" readonly
                                style="background-color: #e9ecef;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Permissions</label>
                            <div class="row g-2" id="permCheckboxes">
                                @foreach($allPermissions as $perm)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input perm-check" type="checkbox" name="permissions[]"
                                                value="{{ $perm }}" id="perm_{{ Str::slug($perm) }}">
                                            <label class="form-check-label" for="perm_{{ Str::slug($perm) }}">
                                                {{ $perm }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function editUser(id, name, username, role, dept, customers) {
            let form = document.getElementById('editUserForm');
            let url = "{{ route('rbac.user.update', ':id') }}";
            url = url.replace(':id', id);
            form.action = url;

            document.getElementById('editUserName').value = name;
            document.getElementById('editUserUser').value = username;
            document.getElementById('editUserRole').value = role;
            document.getElementById('editUserDept').value = dept;

            // Reset customer checkboxes
            document.querySelectorAll('.customer-check').forEach(cb => cb.checked = false);

            // Check assigned customers
            if (Array.isArray(customers)) {
                customers.forEach(custId => {
                    document.querySelectorAll(`.customer-check[data-cust-id="${custId}"]`).forEach(cb => {
                        cb.checked = true;
                    });
                });
            }

            let modalEl = document.getElementById('editUserModal');
            let modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) {
                modal = new bootstrap.Modal(modalEl);
            }
            modal.show();
        }

        // Sync checkboxes for same customer across categories
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('customer-check')) {
                const custId = e.target.getAttribute('data-cust-id');
                const isChecked = e.target.checked;

                document.querySelectorAll(`.customer-check[data-cust-id="${custId}"]`).forEach(cb => {
                    if (cb !== e.target) {
                        cb.checked = isChecked;
                    }
                });
            }
        });

        // Attach click handlers to Edit Permission buttons
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.edit-perm-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var role = this.getAttribute('data-role');
                    var currentPerms = [];

                    try {
                        currentPerms = JSON.parse(this.getAttribute('data-permissions'));
                    } catch (err) {
                        console.error('Failed to parse permissions:', err);
                        currentPerms = [];
                    }

                    document.getElementById('editPermRole').value = role;

                    // Reset all checkboxes
                    document.querySelectorAll('.perm-check').forEach(function (cb) { cb.checked = false; });

                    // Check ones that exist
                    if (Array.isArray(currentPerms)) {
                        currentPerms.forEach(function (perm) {
                            document.querySelectorAll('.perm-check').forEach(function (cb) {
                                if (cb.value === perm) {
                                    cb.checked = true;
                                }
                            });
                        });
                    }

                    var modal = new bootstrap.Modal(document.getElementById('editPermissionsModal'));
                    modal.show();
                });
            });
        });

        // Filtering Users
        function filterUsersByRole(role) {
            let url = new URL(window.location.href);
            url.searchParams.set('role', role);
            url.searchParams.set('tab', 'users');
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }

        function filterUsersTable() {
            // Function kept for compatibility
        }

        document.getElementById('userSearch').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                let filter = this.value;
                let url = new URL(window.location.href);
                url.searchParams.set('search', filter);
                url.searchParams.set('tab', 'users');
                url.searchParams.set('page', 1);
                window.location.href = url.toString();
            }
        });

        // Username validation functions
        function checkUsernameSpaces(input) {
            const value = input.value;
            if (value.includes(' ')) {
                input.classList.add('is-invalid');
                // Show alert only once
                if (!input.dataset.spaceWarned) {
                    alert('Username tidak boleh mengandung spasi!');
                    input.dataset.spaceWarned = 'true';
                }
                // Remove spaces automatically
                input.value = value.replace(/\s+/g, '');
            } else {
                input.classList.remove('is-invalid');
                input.dataset.spaceWarned = '';
            }

            if (value.length > 0 && value.length < 3) {
                input.classList.add('is-invalid');
            }
        }

        function validateUsername(inputId) {
            const input = document.getElementById(inputId);
            const value = input.value;

            // Allow empty if readonly or hidden logic applies, but here required
            if (input.hasAttribute('readonly')) return true;

            if (value.includes(' ')) {
                alert('Username tidak boleh mengandung spasi!');
                input.focus();
                return false;
            }

            if (value.length < 3) {
                alert('Username harus minimal 3 karakter!');
                input.focus();
                return false;
            }

            const pattern = /^[A-Za-z0-9_-]+$/;
            if (!pattern.test(value)) {
                alert('Username hanya boleh huruf, angka, dash (-), dan underscore (_)!');
                input.focus();
                return false;
            }

            return true;
        }

        // Initial Tab Handling from URL
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                const tabTrigger = document.querySelector(`#${tab}-tab`);
                if (tabTrigger) {
                    const tabInstance = new bootstrap.Tab(tabTrigger);
                    tabInstance.show();
                }
            }
        });
    </script>
@endpush