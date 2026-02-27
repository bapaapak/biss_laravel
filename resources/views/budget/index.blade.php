@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a5f;">Budget Investment Plan</h4>
            <p class="text-muted mb-0 small">Manage annual budgets.</p>
        </div>
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
            <!-- Search Bar -->
            <div class="input-group" style="width: 100%; max-width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="searchBudget" class="form-control border-start-0"
                    placeholder="Search budget plans...">
            </div>
            <a href="{{ route('budget.create') }}" class="btn btn-primary w-100 w-md-auto">
                <i class="fas fa-plus me-2"></i>New Budget Plan
            </a>
        </div>
    </div>

    <!-- Mobile View (Cards) -->
    <div class="d-md-none">
        @forelse($plans as $plan)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            @php
                                $statusClass = match ($plan->status ?? 'Draft') {
                                    'Approved' => 'bg-success',
                                    'Draft' => 'bg-secondary',
                                    'Pending' => 'bg-warning',
                                    default => 'bg-info'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} mb-1">{{ $plan->status ?? 'Draft' }}</span>
                            <h6 class="fw-bold mb-0 text-dark">{{ $plan->project->project_name ?? '-' }}</h6>
                            <small class="text-primary fw-bold">{{ $plan->io_number ?? '-' }}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('budget.show', $plan->id) }}"><i
                                            class="fas fa-pen me-2 text-primary"></i> Edit</a></li>
                                <li><a class="dropdown-item" href="{{ route('budget.print', $plan->id) }}" target="_blank"><i
                                            class="fas fa-print me-2 text-dark"></i> Print</a></li>
                                @if(auth()->user()->role === 'Super Admin')
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('budget.destroy', $plan->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete this plan and all items?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash-alt me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <div class="row g-2 small mb-3">
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">Customer</div>
                            <div class="fw-medium text-dark">{{ $plan->customer_code ?? $plan->customer ?? '-' }}</div>
                            <div class="text-muted" style="font-size: 0.65rem;">{{ $plan->model ?? '-' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">Dept</div>
                            <div class="fw-medium text-dark">{{ $plan->department ? strtoupper($plan->department) : '-' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">Items</div>
                            <div class="fw-medium text-dark">{{ $plan->items->count() }} Items</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">Total Amount</div>
                            <div class="fw-bold text-dark">Rp {{ number_format($plan->total_budget, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-folder-open mb-2 fa-2x"></i>
                <p>No budget plans found.</p>
            </div>
        @endforelse
    </div>

    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tableBudget" style="font-size: 0.85rem;">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem;">IO
                                Number</th>
                            <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem;">CC</th>
                            <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem;">Customer</th>
                            <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem;">Dept</th>
                            <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem;">Project Name
                            </th>
                            <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem;">Machine /
                                Equipment</th>
                            <th class="text-uppercase text-muted small fw-semibold text-end" style="font-size: 0.7rem;">
                                Total Amount</th>
                            <th class="text-uppercase text-muted small fw-semibold text-center" style="font-size: 0.7rem;">
                                Status</th>
                            <th class="text-uppercase text-muted small fw-semibold text-center pe-4"
                                style="font-size: 0.7rem; width: 4%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                            <tr>
                                <td class="ps-4 fw-semibold text-primary align-middle">{{ $plan->io_number ?? '-' }}</td>
                                <td class="align-middle">{{ $plan->cc_code ?? '-' }}</td>
                                <td class="align-middle">
                                    <div class="fw-semibold">{{ $plan->customer_code ?? $plan->customer ?? '-' }}</div>
                                    @if($plan->model)
                                        <div class="small text-muted fw-normal" style="font-size: 0.75rem;">{{ $plan->model }}</div>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($plan->department)
                                        <span class="badge"
                                            style="background-color: #f59e0b; color: #fff; font-size: 0.65rem;">{{ strtoupper($plan->department) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="fw-semibold align-middle">{{ $plan->project->project_name ?? '-' }}</td>
                                <td class="align-middle" style="min-width: 280px;">
                                    @php
                                        $itemCount = $plan->items->count();
                                    @endphp
                                    @if($itemCount > 0)
                                        <!-- Summary row - always visible -->
                                        <div class="items-summary d-flex justify-content-between align-items-center"
                                            style="cursor: pointer; padding: 8px 12px; background: #f8f9fa; border-radius: 4px; margin-bottom: 8px;"
                                            onclick="toggleItems(this)">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-chevron-right chevron-icon"
                                                    style="font-size: 0.7rem; color: #64748b; transition: transform 0.2s;"></i>
                                                <span class="fw-semibold" style="font-size: 0.85rem; color: #1e3a5f;">
                                                    {{ $itemCount }} Item{{ $itemCount > 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                            <span class="text-muted small">Click to expand</span>
                                        </div>

                                        <!-- Items detail - hidden by default -->
                                        <div class="items-detail" style="display: none;">
                                            @foreach($plan->items as $item)
                                                <div
                                                    class="d-flex justify-content-between align-items-center {{ !$loop->last ? 'border-bottom pb-2 mb-2' : '' }}">
                                                    <div style="flex: 1;">
                                                        <div class="fw-semibold">{{ $item->item_name }}</div>
                                                        <small class="text-muted">FY {{ $plan->fiscal_year }}</small>
                                                    </div>
                                                    <div class="text-center" style="width: 100px;">
                                                        <div class="fw-medium">{{ $item->qty }} {{ $item->uom }}</div>
                                                        <small
                                                            class="text-muted text-capitalize">{{ $item->process ?? 'Preparation' }}</small>
                                                    </div>
                                                    <div class="text-end" style="width: 120px;">
                                                        <span>Rp {{ number_format($item->total_amount, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">No items</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold align-middle">Rp
                                    {{ number_format($plan->total_budget, 0, ',', '.') }}
                                </td>
                                <td class="text-center align-middle">
                                    @php
                                        $statusClass = match ($plan->status ?? 'Draft') {
                                            'Approved' => 'bg-success',
                                            'Draft' => 'bg-secondary',
                                            'Pending' => 'bg-warning',
                                            default => 'bg-info'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} text-white"
                                        style="padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem;">
                                        {{ $plan->status ?? 'Draft' }}
                                    </span>
                                </td>
                                <td class="text-center pe-4 align-middle">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0 bg-transparent" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                            style="font-size: 0.8rem;">
                                            <li>
                                                <a class="dropdown-item text-primary"
                                                    href="{{ route('budget.show', $plan->id) }}">
                                                    <i class="fas fa-pen me-2"></i> Edit Plan
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-dark" href="{{ route('budget.print', $plan->id) }}"
                                                    target="_blank">
                                                    <i class="fas fa-print me-2"></i> Print Plan
                                                </a>
                                            </li>
                                            @if(auth()->user()->role === 'Super Admin')
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('budget.destroy', $plan->id) }}" method="POST"
                                                        onsubmit="return confirm('Delete this plan and all items?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-trash-alt me-2"></i> Delete Plan
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
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">No budget plans found. <a href="{{ route('budget.create') }}">Create your
                                            first budget plan</a>.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($plans->hasPages())
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                    <div class="text-muted small">
                        Showing {{ $plans->firstItem() }} to {{ $plans->lastItem() }} of {{ $plans->total() }} plans
                    </div>
                    <div>
                        {{ $plans->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Search functionality
        document.getElementById('searchBudget').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                let filter = this.value;
                let url = new URL(window.location.href);
                url.searchParams.set('search', filter);
                url.searchParams.set('page', 1); // Reset to page 1
                window.location.href = url.toString();
            }
        });

        // Toggle items detail
        function toggleItems(summaryElement) {
            const detailDiv = summaryElement.nextElementSibling;
            const chevronIcon = summaryElement.querySelector('.chevron-icon');
            const expandText = summaryElement.querySelector('.text-muted.small');

            if (detailDiv.style.display === 'none') {
                // Expand
                detailDiv.style.display = 'block';
                chevronIcon.style.transform = 'rotate(90deg)';
                expandText.textContent = 'Click to collapse';
            } else {
                // Collapse
                detailDiv.style.display = 'none';
                chevronIcon.style.transform = 'rotate(0deg)';
                expandText.textContent = 'Click to expand';
            }
        }
    </script>
@endpush