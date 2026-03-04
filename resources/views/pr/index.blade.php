@extends('layouts.app')

@section('content')
    <style>
        .pr-link {
            color: #0066FF;
            font-weight: 500;
            text-decoration: none;
        }

        .pr-link:hover {
            text-decoration: underline;
        }

        .badge-status {
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* Items expand inline */
        .items-detail-overlay {
            padding: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            display: none;
            max-height: 220px;
            overflow-y: auto;
        }
    </style>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center page-header mb-3 gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e293b;">Purchase Requests</h4>
            <p class="text-muted mb-0 small">Manage procurement requests and approvals.</p>
        </div>
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
            <!-- Search Bar -->
            <div class="input-group" style="width: 100%; max-width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="searchPR" class="form-control border-start-0"
                    placeholder="Search purchase requests...">
            </div>
            <button type="button" class="btn btn-success w-100 w-md-auto" data-bs-toggle="modal"
                data-bs-target="#importExcelModal">
                <i class="fas fa-file-excel me-2"></i>Import Excel
            </button>
            <a href="{{ route('pr.create') }}" class="btn btn-primary w-100 w-md-auto">
                <i class="fas fa-plus me-2"></i>New PR
            </a>
        </div>
    </div>

    <!-- Import Excel Modal -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelModalLabel">Import PR from Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="importExcelForm" action="{{ route('pr.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Choose Excel File</label>
                            <input type="file" class="form-control" id="importFile" name="file" required
                                accept=".xlsx,.xls">
                            <div class="form-text mt-2">
                                <a href="{{ asset('template_pr_persis_format.xlsx') }}" download
                                    class="text-decoration-none">
                                    <i class="fas fa-download me-1"></i> Download Template Excel
                                </a>
                            </div>
                        </div>
                        <div id="importValidationResult" style="display:none;"></div>
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i> Pastikan format file sesuai dengan template.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="importSubmitBtn" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="importSpinner" role="status"></span>
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Mobile View (Cards) -->
    <div class="d-md-none">
        @forelse($prs as $pr)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            @php
                                $st = $pr->status;
                                $approver = $pr->current_approver_role;
                                $badgeClass = 'bg-primary';
                                if ($st == 'Approved') {
                                    $badgeClass = 'bg-success';
                                }
                                if ($st == 'Rejected') {
                                    $badgeClass = 'bg-danger';
                                }
                                if ($st == 'Pending') {
                                    $badgeClass = 'bg-warning';
                                }
                                if ($st == 'Submitted') {
                                    $badgeClass = 'bg-info';
                                }
                                if ($st == 'Draft') {
                                    $badgeClass = 'bg-secondary';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }} mb-1">{{ $st }}</span>
                            <h6 class="fw-bold mb-0 text-dark">{{ $pr->pr_number }}</h6>
                            <small class="text-muted">{{ $pr->request_date }}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($st == 'Submitted' && $approver && Auth::user()->canApprovePR($approver))
                                    <li><a class="dropdown-item text-success" href="{{ route('pr.approve', $pr->pr_number) }}"><i
                                                class="fas fa-check me-2"></i> Approve</a></li>
                                    <li><a class="dropdown-item text-danger" href="#"
                                            onclick="event.preventDefault(); swalRedirect('{{ route('pr.reject', $pr->pr_number) }}', 'Reject PR?', 'Reject this PR?', 'warning')"><i class="fas fa-times me-2"></i> Reject</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                @endif

                                <li><a class="dropdown-item"
                                        href="{{ route('pr.show', ['pr' => $pr->id]) }}?pr_number={{ $pr->pr_number }}"><i
                                            class="fas fa-eye me-2 text-info"></i> View Details</a></li>
                                <li><a class="dropdown-item" href="{{ route('pr.print', $pr->pr_number) }}" target="_blank"><i
                                            class="fas fa-print me-2 text-dark"></i> Print</a></li>

                                @if(auth()->user()->role === 'Super Admin' || auth()->user()->role === 'Admin')
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('pr.destroy', ['pr' => $pr->id]) }}?pr_number={{ $pr->pr_number }}"
                                            method="POST" class="d-inline"
                                            onsubmit="swalDelete(event, this);">
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

                    <div class="mb-2">
                        <div class="text-muted" style="font-size: 0.75rem;">Purpose:</div>
                        <div class="text-dark small">{{ Str::limit($pr->purpose ?: '-', 50) }}</div>
                    </div>

                    <div class="row g-2 small mb-2">
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">Dept / Project</div>
                            <div class="fw-medium text-dark">{{ $pr->department_code ?? $pr->project_code ?? '-' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size: 0.7rem;">Total Cost</div>
                            <div class="fw-bold text-dark">Rp {{ number_format($pr->total_amount ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>

                    @if($approver && $st == 'Submitted')
                        <div class="alert alert-light border py-1 px-2 mb-0 d-flex align-items-center">
                            <i class="fas fa-hourglass-half text-warning me-2" style="font-size: 0.8rem;"></i>
                            <small class="text-muted">Waiting: Only <strong>{{ $approver }}</strong></small>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-folder-open mb-2 fa-2x"></i>
                <p>No purchase requests found.</p>
            </div>
        @endforelse
    </div>

    <div class="card border-0 shadow-sm d-none d-md-block" style="border-radius: 8px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="tablePr" style="font-size: 0.85rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem; width: 10%;">Customer</th>
                        <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem; width: 15%;">Project Name</th>
                        <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem; width: 10%;">PR Number</th>
                        <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem; width: 8%;">Date</th>
                        <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem; width: 9%;">Due Date</th>
                        <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem; width: 5%;">Dept</th>
                        <th class="text-uppercase text-muted small fw-semibold" style="font-size: 0.7rem; width: 16%;">Peruntukan & Items</th>
                        <th class="text-uppercase text-muted small fw-semibold text-end" style="font-size: 0.7rem; width: 10%;">Total Cost</th>
                        <th class="text-uppercase text-muted small fw-semibold text-center" style="font-size: 0.7rem; width: 10%;">Status</th>
                        <th class="text-uppercase text-muted small fw-semibold text-center pe-4" style="font-size: 0.7rem; width: 4%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prs as $pr)
                            <td class="ps-4 align-middle">
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $pr->customer_code ?: '-' }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 150px;">{{ $pr->customer_name ?: '-' }}</div>
                            </td>
                            <td class="align-middle">
                                @if($pr->business_category)
                                    <span class="badge bg-light text-dark border mb-1" style="font-size: 0.65rem; font-weight: 500;">
                                        {{ $pr->business_category }}
                                    </span>
                                @endif
                                <div class="fw-bold text-dark text-uppercase" style="font-size: 0.85rem; line-height: 1.2;">
                                    {{ $pr->project_name ?: '-' }}
                                </div>
                            </td>
                            <td class="fw-semibold align-middle">
                                <a href="{{ route('pr.show', ['pr' => $pr->id]) }}?pr_number={{ $pr->pr_number }}"
                                    class="pr-link">{{ $pr->pr_number }}</a>
                            </td>
                            <td class="align-middle">{{ $pr->request_date }}</td>
                            <td class="align-middle">
                                @if($pr->due_date)
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($pr->due_date);
                                        $createdDate = \Carbon\Carbon::parse($pr->request_date);
                                        $daysFromCreation = $createdDate->diffInDays($dueDate, false);
                                        $daysRemaining = now()->diffInDays($dueDate, false);
                                    @endphp
                                    <div class="small">{{ $pr->due_date }}</div>
                                    <span
                                        class="badge bg-{{ $daysRemaining < 0 ? 'danger' : ($daysRemaining <= 7 ? 'warning' : 'success') }} text-white"
                                        style="display: inline-block; font-size: 0.65rem; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        {{ $daysFromCreation }} hari dari PR
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if($pr->department_code)
                                    @php
                                        $deptName = $pr->department_code;
                                        // Use dept_short_code from DB if available, otherwise generate abbreviation
                                        if (!empty($pr->dept_short_code)) {
                                            $deptDisplay = strtoupper($pr->dept_short_code);
                                        } else {
                                            $deptAbbreviations = [
                                                'Project Management Accessories' => 'PMA',
                                                'Project Management Wiring Harness' => 'PMWH',
                                                'Cost Analyst' => 'CA',
                                                'Product Innovation Development' => 'PID',
                                                'Project Management Power & Energy Industrial Solution' => 'PMPEIS',
                                                'Finance Accounting' => 'FA',
                                                'Purchasing & Procurement' => 'PNP',
                                                'Division Head' => 'DH',
                                            ];
                                            $deptDisplay = $deptAbbreviations[$deptName] ?? strtoupper(
                                                implode('', array_map(fn($w) => $w[0] ?? '', preg_split('/\s+/', $deptName)))
                                            );
                                        }
                                    @endphp
                                    <span class="badge rounded-pill fw-semibold" title="{{ $deptName }}"
                                        style="background-color: #1e40af; color: #fff; font-size: 0.7rem; padding: 0.3rem 0.6rem; letter-spacing: 0.5px;">
                                        {{ $deptDisplay }}
                                    </span>
                                @elseif($pr->project_code)
                                    @php
                                        $projName = $pr->project_code;
                                        $projAbbreviations = [
                                            'Project Management Accessories' => 'PMA',
                                            'Project Management Wiring Harness' => 'PMWH',
                                        ];
                                        $projDisplay = $projAbbreviations[$projName] ?? strtoupper(
                                            implode('', array_map(fn($w) => $w[0] ?? '', preg_split('/\s+/', $projName)))
                                        );
                                    @endphp
                                    <span class="badge rounded-pill fw-semibold" title="{{ $projName }}"
                                        style="background-color: #6366f1; color: #fff; font-size: 0.7rem; padding: 0.3rem 0.6rem; letter-spacing: 0.5px;">
                                        {{ $projDisplay }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="align-middle" style="min-width: 280px;">
                                <div class="fw-semibold mb-1 text-dark">{{ $pr->purpose ?: '-' }}</div>
                                @php
                                    $prItems = \App\Models\PurchaseRequest::where('pr_number', $pr->pr_number)->get();
                                    $itemCount = $prItems->count();
                                @endphp
                                @if($itemCount > 0)
                                    <!-- Summary row - always visible -->
                                    <div class="items-summary d-flex justify-content-between align-items-center"
                                        style="cursor: pointer; padding: 6px 10px; background: #f8f9fa; border-radius: 4px; border: 1px solid #e2e8f0;"
                                        onclick="toggleItems(this)">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-chevron-right chevron-icon"
                                                style="font-size: 0.7rem; color: #64748b; transition: transform 0.2s;"></i>
                                            <span class="fw-semibold" style="font-size: 0.75rem; color: #1e3a5f;">
                                                {{ $itemCount }} Item{{ $itemCount > 1 ? 's' : '' }}
                                            </span>
                                        </div>
                                        <span class="text-muted" style="font-size: 0.7rem;">Click to expand</span>
                                    </div>

                                    <!-- Items detail - hidden by default, positioned as overlay -->
                                    <div class="items-detail items-detail-overlay mt-2">
                                        @foreach($prItems as $item)
                                            <div
                                                class="d-flex justify-content-between align-items-center {{ !$loop->last ? 'border-bottom pb-2 mb-2' : '' }}">
                                                <div style="flex: 1;">
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $item->item_code ?? 'Item ID' }}</div>
                                                    <small class="text-muted"
                                                        style="font-size: 0.7rem;">{{ $item->description }}</small>
                                                </div>
                                                <div class="text-center px-2" style="width: 80px;">
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $item->qty_req }} {{ $item->uom }}
                                                    </div>
                                                </div>
                                                <div class="text-end" style="width: 100px;">
                                                    <span style="font-size: 0.75rem;">Rp
                                                        {{ number_format($item->estimated_price * $item->qty_req, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-end fw-bold align-middle text-nowrap">
                                Rp {{ number_format($pr->total_amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-center align-middle">
                                @php
                                    $st = $pr->status;
                                    $approver = $pr->current_approver_role;
                                    
                                    // Make the status badge more descriptive based on workflow history
                                    $displayStatus = $st;
                                    $badgeClass = 'bg-primary';
                                    
                                    if ($st == 'Approved') {
                                        $badgeClass = 'bg-success';
                                    } elseif ($st == 'Rejected') {
                                        $badgeClass = 'bg-danger';
                                    } elseif ($st == 'Pending') {
                                        $badgeClass = 'bg-warning';
                                    } elseif ($st == 'Draft') {
                                        $badgeClass = 'bg-secondary';
                                    } elseif ($st == 'Submitted') {
                                        $badgeClass = 'bg-info';
                                        
                                        // If it's submitted but already passed some approvers, show the progress map
                                        // The flow goes: Dept Head -> Finance -> Division Head -> Purchasing
                                        if ($approver == 'Finance') {
                                            $displayStatus = 'Apprv: Dept Head';
                                        } elseif ($approver == 'Division Head') {
                                            $displayStatus = 'Apprv: Finance';
                                        } elseif ($approver == 'Purchasing') {
                                            $displayStatus = 'Apprv: Div Head';
                                        }
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }} text-white"
                                    style="padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem;">
                                    {{ $displayStatus }}
                                </span>
                                @if($approver && $st == 'Submitted')
                                    <div class="text-muted small mt-1" style="font-size: 0.7rem;">
                                        <i class="fas fa-hourglass-half me-1"></i>Waiting: {{ $approver }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center pe-4 align-middle">
                                @php
                                    $isApproved = $pr->status === 'Approved';
                                    $isAdmin = auth()->user()->role === 'Admin' || auth()->user()->role === 'Super Admin';
                                    $isCreator = $pr->requester_id == auth()->id();
                                    $canEdit = $isAdmin || ($isCreator && ($pr->status == 'Rejected' || $pr->current_approver_role == 'Dept Head'));
                                    $canDelete = $isAdmin || ($isCreator && ($pr->status == 'Rejected' || $pr->status == 'Submitted'));
                                @endphp

                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.8rem;">
                                        @if($st == 'Submitted' && $approver && Auth::user()->canApprovePR($approver))
                                            <li>
                                                <a class="dropdown-item text-success fw-medium" href="{{ route('pr.approve', $pr->pr_number) }}">
                                                    <i class="fas fa-check me-2"></i> Approve
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger fw-medium" href="#" onclick="event.preventDefault(); swalRedirect('{{ route('pr.reject', $pr->pr_number) }}', 'Reject PR?', 'Reject this PR?', 'warning')">
                                                    <i class="fas fa-times me-2"></i> Reject
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                        @endif

                                        <li>
                                            <a class="dropdown-item text-primary" href="{{ route('pr.show', ['pr' => $pr->id]) }}?pr_number={{ $pr->pr_number }}">
                                                <i class="fas {{ $canEdit ? 'fa-pen' : 'fa-eye' }} me-2"></i> {{ $canEdit ? 'Edit PR' : 'View Details' }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-dark" href="{{ route('pr.print', $pr->pr_number) }}" target="_blank">
                                                <i class="fas fa-print me-2"></i> Print PR
                                            </a>
                                        </li>

                                        @if($canDelete)
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('pr.destroy', ['pr' => $pr->id]) }}?pr_number={{ $pr->pr_number }}" method="POST" onsubmit="swalDelete(event, this);">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash-alt me-2"></i> Delete PR
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
                            <td colspan="7" class="text-center py-5 text-muted">No purchase requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($prs->hasPages())
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <div class="text-muted small">
                    Showing {{ $prs->firstItem() }} to {{ $prs->lastItem() }} of {{ $prs->total() }} purchase requests
                </div>
                <div>
                    {{ $prs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        // Show SweetAlert for session messages (import result)
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                confirmButtonColor: '#28a745',
                timer: 4000,
                timerProgressBar: true
            });
        @endif
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: @json(session('error')),
                confirmButtonColor: '#dc3545'
            });
        @endif
        // Fix dropdown inside table-responsive causing scrollbar
        document.querySelectorAll('.table-responsive').forEach(function(el) {
            el.addEventListener('show.bs.dropdown', function() {
                this.style.overflow = 'visible';
            });
            el.addEventListener('hide.bs.dropdown', function() {
                this.style.overflow = 'auto';
            });
        });

        document.getElementById('searchPR').addEventListener('keydown', function (e) {
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
            const expandText = summaryElement.querySelector('.text-muted');
            const isHidden = detailDiv.style.display === 'none' || detailDiv.style.display === '';

            // Close all other open expands first
            document.querySelectorAll('.items-detail-overlay').forEach(function(el) {
                if (el !== detailDiv) {
                    el.style.display = 'none';
                    const parentSummary = el.previousElementSibling;
                    if (parentSummary) {
                        const icon = parentSummary.querySelector('.chevron-icon');
                        const text = parentSummary.querySelector('.text-muted');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                        if (text) text.textContent = 'Click to expand';
                    }
                }
            });

            if (isHidden) {
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

        // Import Excel validation & submission
        document.getElementById('importFile').addEventListener('change', function() {
            const resultDiv = document.getElementById('importValidationResult');
            resultDiv.style.display = 'none';
            resultDiv.innerHTML = '';

            const file = this.files[0];
            if (!file) return;

            const validExts = ['.xlsx', '.xls'];
            const ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
            if (!validExts.includes(ext)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format File Salah',
                    html: 'File harus berformat <b>.xlsx</b> atau <b>.xls</b>.<br>File yang dipilih: <code>' + file.name + '</code>',
                    confirmButtonColor: '#dc3545'
                });
                this.value = '';
                return;
            }

            // Validate via AJAX
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            resultDiv.style.display = 'block';
            resultDiv.className = 'alert alert-secondary small';
            resultDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memvalidasi format file...';

            fetch('{{ route("pr.validate-import") }}', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.valid) {
                        resultDiv.className = 'alert alert-success small';
                        resultDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i> Format valid, siap diimport.';
                    } else {
                        resultDiv.className = 'alert alert-danger small';
                        resultDiv.innerHTML = '<i class="fas fa-times-circle me-1"></i> Format tidak sesuai template.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Format Tidak Sesuai',
                            text: 'Harap periksa kembali dokumennya.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(() => {
                    resultDiv.style.display = 'none';
                });
        });

        document.getElementById('importExcelForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('importFile');
            if (!fileInput.files.length) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Pilih File', text: 'Silakan pilih file Excel terlebih dahulu.', confirmButtonColor: '#3085d6' });
                return;
            }
            const btn = document.getElementById('importSubmitBtn');
            const spinner = document.getElementById('importSpinner');
            btn.disabled = true;
            spinner.classList.remove('d-none');
        });

        // Reset modal to clean state when closed
        document.getElementById('importExcelModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('importFile').value = '';
            const resultDiv = document.getElementById('importValidationResult');
            resultDiv.style.display = 'none';
            resultDiv.innerHTML = '';
            const btn = document.getElementById('importSubmitBtn');
            btn.disabled = false;
            document.getElementById('importSpinner').classList.add('d-none');
        });


    </script>
@endpush