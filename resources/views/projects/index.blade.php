@extends('layouts.app')

@section('content')
    <style>
        .badge-status {
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.75rem;
        }
    </style>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center page-header mb-3 gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e293b;">Projects</h4>
            <p class="text-muted mb-0 small">Manage product introductions and detailed schedules.</p>
        </div>
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
            <!-- Search Bar -->
            <div class="input-group" style="width: 100%; max-width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="searchProject" class="form-control border-start-0" placeholder="Search projects...">
            </div>
            <a href="{{ route('projects.create') }}" class="btn btn-primary w-100 w-md-auto">
                <i class="fas fa-plus me-2"></i>New Project
            </a>
        </div>
    </div>

    <!-- Mobile View (Cards) -->
    <div class="d-md-none">
        @forelse($projects as $p)
            @php
                $st = $p->status;
                $badgeClass = 'bg-secondary';
                if ($st == 'Active' || $st == 'Ongoing') {
                    $badgeClass = 'bg-success';
                }
                if ($st == 'Draft') {
                    $badgeClass = 'bg-warning';
                }
                if ($st == 'Completed') {
                    $badgeClass = 'bg-primary';
                }
            @endphp
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge {{ $badgeClass }} mb-1">{{ $st }}</span>
                            <h6 class="fw-bold mb-0 text-dark">{{ $p->project_name }}</h6>
                            <small class="text-primary fw-bold">{{ $p->project_code }}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('projects.edit', $p->id) }}"><i class="fas fa-eye me-2 text-info"></i> View Details</a></li>
                                @if(in_array(Auth::user()->role, ['Admin', 'Super Admin']))
                                    <li><a class="dropdown-item" href="{{ route('projects.edit', $p->id) }}"><i class="fas fa-pen me-2 text-primary"></i> Edit</a></li>
                                    @if(Auth::user()->role === 'Super Admin')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('projects.destroy', $p->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus project ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                @endif
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row g-2 small mb-3">
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">Customer</div>
                            <div class="fw-medium text-dark">{{ $p->customer_code ?? '-' }}</div>
                            <div class="text-muted" style="font-size: 0.65rem;">{{ Str::limit($p->customer_name, 15) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">PM</div>
                            <div class="fw-medium text-dark">{{ $p->pic_name ? Str::limit($p->pic_name, 10) : '-' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">Model / Year</div>
                            <div class="fw-medium text-dark">{{ $p->model ?? '-' }} / {{ $p->year ?? '-' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">Mass Pro</div>
                            <div class="fw-medium text-danger">{{ $p->mass_pro ? date('d-M-y', strtotime($p->mass_pro)) : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-folder-open mb-2 fa-2x"></i>
                <p>No projects found.</p>
            </div>
        @endforelse
    </div>

    <!-- Desktop View (Table) -->
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="table-responsive">
            <table class="table mb-0 table-hover align-middle" id="tableProject" style="font-size: 0.85rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="text-muted text-uppercase fw-bold text-nowrap ps-4" style="font-size: 0.75rem;">Code</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap" style="font-size: 0.75rem;">Customer</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap" style="min-width: 200px; font-size: 0.75rem;">Project Name</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap" style="font-size: 0.75rem;">PM</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap" style="font-size: 0.75rem;">Model</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap" style="font-size: 0.75rem;">Year</th>
                        <!-- Dates Group -->
                        <th class="text-muted text-uppercase fw-bold text-nowrap text-center" style="font-size: 0.75rem;">Die Go</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap text-center" style="font-size: 0.75rem;">T0</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap text-center" style="font-size: 0.75rem;">PP1</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap text-center" style="font-size: 0.75rem;">PP2</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap text-center" style="font-size: 0.75rem;">PP3</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap text-center" style="font-size: 0.75rem;">Mass Pro</th>
                        
                        <th class="text-muted text-uppercase fw-bold text-nowrap text-center" style="font-size: 0.75rem;">Status</th>
                        <th class="text-muted text-uppercase fw-bold text-nowrap text-center pe-4" style="font-size: 0.75rem; width: 4%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $p)
                        @php
                            $st = $p->status;
                            $badgeClass = 'bg-secondary';
                            if ($st == 'Active' || $st == 'Ongoing') {
                                $badgeClass = 'bg-success';
                            }
                            if ($st == 'Draft') {
                                $badgeClass = 'bg-warning';
                            }
                            if ($st == 'Completed') {
                                $badgeClass = 'bg-primary';
                            }
                        @endphp
                        <tr>
                            <td class="fw-bold text-primary text-nowrap ps-4">{{ $p->project_code }}</td>
                            <td class="text-nowrap">
                                @if($p->customer_code)
                                    <div>{{ $p->customer_code }}</div>
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ Str::limit($p->customer_name, 15) }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($p->category)
                                    <span class="badge bg-light text-dark border mb-1" style="font-size: 0.65rem;">{{ $p->category }}</span><br>
                                @endif
                                <span class="fw-semibold text-dark">{{ $p->project_name }}</span>
                            </td>
                            <td class="text-nowrap">{{ $p->pic_name ? Str::limit($p->pic_name, 10) : '-' }}</td>
                            <td class="text-nowrap">{{ $p->model ?? '-' }}</td>
                            <td class="text-center">{{ $p->year ?? '-' }}</td>
                            
                            <!-- Dates - using cleaner format -->
                            <td class="text-center text-nowrap">{{ $p->die_go ? date('d-M-y', strtotime($p->die_go)) : '-' }}</td>
                            <td class="text-center text-nowrap">{{ $p->to ? date('d-M-y', strtotime($p->to)) : '-' }}</td>
                            <td class="text-center text-nowrap">{{ $p->pp1 ? date('d-M-y', strtotime($p->pp1)) : '-' }}</td>
                            <td class="text-center text-nowrap">{{ $p->pp2 ? date('d-M-y', strtotime($p->pp2)) : '-' }}</td>
                            <td class="text-center text-nowrap">{{ $p->pp3 ? date('d-M-y', strtotime($p->pp3)) : '-' }}</td>
                            <td class="text-center text-nowrap">{{ $p->mass_pro ? date('d-M-y', strtotime($p->mass_pro)) : '-' }}</td>
                            
                            <td class="text-center">
                                <span class="badge badge-status {{ $badgeClass }} text-white">{{ $st }}</span>
                            </td>
                            <td class="text-center pe-4 align-middle">
                                @php
                                    $isAdmin = in_array(Auth::user()->role, ['Admin', 'Super Admin']);
                                    $isSuperAdmin = Auth::user()->role === 'Super Admin';
                                @endphp
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.8rem;">
                                        <li>
                                            <a class="dropdown-item text-primary" href="{{ route('projects.edit', $p->id) }}">
                                                <i class="fas {{ $isAdmin ? 'fa-pen' : 'fa-eye' }} me-2"></i> {{ $isAdmin ? 'Edit Project' : 'View Details' }}
                                            </a>
                                        </li>
                                        
                                        @if($isSuperAdmin)
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('projects.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus project ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash-alt me-2"></i> Delete Project
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center text-muted py-5">No projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($projects->hasPages())
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <div class="text-muted small">
                    Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} projects
                </div>
                <div>
                    {{ $projects->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        document.getElementById('searchProject').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                let filter = this.value;
                let url = new URL(window.location.href);
                url.searchParams.set('search', filter);
                url.searchParams.set('page', 1); // Reset to page 1
                window.location.href = url.toString();
            }
        });
    </script>
@endpush