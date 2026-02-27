@extends('layouts.app')

@section('content')
    <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 p-2 rounded-3">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0" style="color: #1e3a5f;">Budget Investment Plan Details</h5>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        @php
                            $statusColor = match ($plan->status) {
                                'Approved' => 'success',
                                'Rejected' => 'danger',
                                'Submitted' => 'warning',
                                default => 'secondary'
                            };
                            $isEditable = auth()->user()->role == 'Super Admin' ||
                                (($plan->status == 'Draft' || $plan->status == 'Rejected') && auth()->id() == $plan->created_by);
                        @endphp
                        <span class="badge bg-{{ $statusColor }} shadow-sm px-3 rounded-pill">{{ $plan->status }}</span>
                        @if($plan->status == 'Submitted')
                            <span
                                class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 rounded-pill">
                                <i class="fas fa-user-clock me-1"></i>
                                Waiting: {{ $plan->current_approver_role }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                @if($plan->status == 'Draft')
                    <form action="{{ route('budget.submit', $plan->id) }}" method="POST"
                        onsubmit="return confirm('Submit this budget plan for approval?');">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill shadow-sm">
                            <i class="fas fa-paper-plane me-1"></i> Submit
                        </button>
                    </form>
                @endif

                @if($plan->status == 'Submitted' && auth()->user()->canApproveBudget($plan->current_approver_role))
                    <form action="{{ route('budget.approve', $plan->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to APPROVE this budget plan?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm px-3 rounded-pill shadow-sm">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                    </form>
                    <form action="{{ route('budget.reject', $plan->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to REJECT this budget plan?');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm px-3 rounded-pill shadow-sm">
                            <i class="fas fa-times me-1"></i> Reject
                        </button>
                    </form>
                @endif

                <a href="{{ route('budget.index') }}" class="btn btn-light btn-sm rounded-circle shadow-sm">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('budget.update', $plan->id) }}" method="POST" id="budgetForm">
                @csrf
                @method('PUT')

                <!-- Header Section: Purpose & Metadata -->
                <div class="row mb-4 bg-light p-3 rounded-3 g-3">
                    <div class="col-md-4">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Purpose</label>
                        <div class="d-flex gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="purpose" id="purpose_prod"
                                    value="Production" {{ ($plan->purpose ?? 'Production') == 'Production' ? 'checked' : '' }}
                                    {{ !$isEditable ? 'disabled' : '' }}>
                                <label class="form-check-label small" for="purpose_prod">Production</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="purpose" id="purpose_rnd" value="R and D"
                                    {{ ($plan->purpose ?? '') == 'R and D' ? 'checked' : '' }} {{ !$isEditable ? 'disabled' : '' }}>
                                <label class="form-check-label small" for="purpose_rnd">R & D</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="purpose" id="purpose_others"
                                    value="Others" {{ ($plan->purpose ?? '') == 'Others' ? 'checked' : '' }} {{ !$isEditable ? 'disabled' : '' }}>
                                <label class="form-check-label small" for="purpose_others">Others</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-uppercase text-muted small fw-bold" style="font-size: 0.7rem;">Fiscal
                            Year</label>
                        <select name="fiscal_year" class="form-select form-select-sm border-0 shadow-sm" {{ !$isEditable ? 'disabled' : '' }}>
                            @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $plan->fiscal_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Department</label>
                        <select name="department" class="form-select form-select-sm border-0 shadow-sm" required {{ !$isEditable ? 'disabled' : '' }}>
                            <option value="">-- Select Dept --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->dept_code }}" {{ $plan->department == $dept->dept_code ? 'selected' : '' }}>{{ $dept->dept_code }} - {{ $dept->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase text-muted small fw-bold" style="font-size: 0.7rem;">Cost
                            Center</label>
                        <select name="cost_center" class="form-select form-select-sm border-0 shadow-sm" required {{ !$isEditable ? 'disabled' : '' }}>
                            <option value="">-- Select CC --</option>
                            @foreach($ccs as $cc)
                                <option value="{{ $cc->cc_code }}" {{ $plan->cc_code == $cc->cc_code ? 'selected' : '' }}>
                                    {{ $cc->cc_code }} - {{ $cc->cc_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Project & IO Selection -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label text-uppercase text-muted small fw-bold" style="font-size: 0.7rem;">Project
                            / IO Reference</label>
                        <select id="projectIoSelect" class="form-select border-0 shadow-sm" onchange="updateProjectIo(this)"
                            required {{ !$isEditable ? 'disabled' : '' }}>
                            <option value="">-- Select Project/IO --</option>
                            @foreach($ios as $io)
                                <option value="{{ $io->io_number }}" data-project-id="{{ $io->project_id }}"
                                    data-project-name="{{ $io->project_name }}" {{ $plan->io_number == $io->io_number ? 'selected' : '' }}>
                                    {{ $io->io_number }} | {{ $io->project_name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="project_id" id="project_id" value="{{ $plan->project_id }}">
                        <input type="hidden" name="io_number" id="io_number" value="{{ $plan->io_number }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Customer</label>
                        <select name="customer" class="form-select border-0 shadow-sm" required {{ !$isEditable ? 'disabled' : '' }}>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->customer_name }}" {{ ($plan->customer == $cust->customer_name || $plan->customer == $cust->customer_code) ? 'selected' : '' }}>
                                    {{ $cust->customer_code ?? '-' }} - {{ $cust->customer_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Model</label>
                        <input type="text" name="model" class="form-control border-0 shadow-sm"
                            value="{{ $plan->model ?? '' }}" placeholder="Model Name" style="text-transform: uppercase;" {{ !$isEditable ? 'disabled' : '' }} oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-uppercase text-muted small fw-bold"
                            style="font-size: 0.7rem;">Investment Type</label>
                        <div class="bg-white p-2 rounded-3 border d-flex gap-3 shadow-sm">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="investment_type" id="inv_capex"
                                    value="Capex" {{ $plan->investment_type == 'Capex' ? 'checked' : '' }} {{ !$isEditable ? 'disabled' : '' }}>
                                <label class="form-check-label small" for="inv_capex">CAPEX</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="investment_type" id="inv_opex"
                                    value="Opex" {{ $plan->investment_type == 'Opex' ? 'checked' : '' }} {{ !$isEditable ? 'disabled' : '' }}>
                                <label class="form-check-label small" for="inv_opex">OPEX</label>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="description" value="{{ $plan->description }}">

                <!-- Hidden container for item inputs -->
                <div id="itemsContainer"></div>

                <!-- Plan Items Section -->
                <div class="border-top pt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0" style="color: #1e3a5f;">Machine / Equipment List</h6>
                        @if($isEditable)
                            <button type="button" class="btn btn-primary btn-sm px-3 rounded-pill shadow-sm"
                                onclick="showAddItemRow()">
                                <i class="fas fa-plus me-1"></i> Add Item
                            </button>
                        @endif
                    </div>

                    <!-- Add Item Row (Toggleable) -->
                    <div id="addItemRow" class="card border-0 bg-light mb-4 shadow-sm"
                        style="display: none; border-radius: 12px;">
                        <div class="card-body p-3">
                            <div class="row g-3 mb-3">
                                <div class="col-md-2">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Code</label>
                                    <input type="text" id="item_code" class="form-control form-control-sm border-0"
                                        placeholder="A/B">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Category</label>
                                    <input type="text" id="item_category" class="form-control form-control-sm border-0"
                                        list="category_list">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Name</label>
                                    <input type="text" id="item_name" class="form-control form-control-sm border-0"
                                        onblur="this.value = toFlexibleProperCase(this.value)">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Brand / Spec</label>
                                    <input type="text" id="item_brand" class="form-control form-control-sm border-0"
                                        onblur="this.value = toFlexibleProperCase(this.value)">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">App Process</label>
                                    <input type="text" id="item_app_process" class="form-control form-control-sm border-0"
                                        onblur="this.value = toFlexibleProperCase(this.value)">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Process Section</label>
                                    <select id="item_process" class="form-select form-select-sm border-0">
                                        <option value="Preparation">Preparation</option>
                                        <option value="Final Assy">Final Assy</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Qty</label>
                                    <input type="number" id="item_qty" class="form-control form-control-sm border-0"
                                        value="1">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Unit Cost (IDR)</label>
                                    <input type="text" id="item_price_display" class="form-control form-control-sm border-0"
                                        value="0" onkeyup="formatNumber(this)">
                                    <input type="hidden" id="item_price" value="0">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Condition Status</label>
                                    <select id="item_condition_status" class="form-select form-select-sm border-0">
                                        <option value="Ready">Ready</option>
                                        <option value="Not Ready">Not Ready</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Condition Notes</label>
                                    <input type="text" id="item_condition_notes"
                                        class="form-control form-control-sm border-0"
                                        onblur="this.value = toFlexibleProperCase(this.value)">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase text-muted small fw-bold"
                                        style="font-size: 0.65rem;">Target Schedule</label>
                                    <input type="date" id="item_schedule" class="form-control form-control-sm border-0">
                                </div>
                            </div>

                            <!-- Breakdown Section -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="has_breakdown"
                                    onchange="toggleBreakdown()">
                                <label class="form-check-label small fw-bold text-muted" for="has_breakdown">
                                    Has Details / Breakdown Components?
                                </label>
                            </div>

                            <div id="breakdown_section" class="d-none mb-3 ps-3 border-start border-3 border-secondary">
                                <h6 class="text-xs text-uppercase text-muted fw-bold mb-2">Breakdown Components</h6>
                                <table class="table table-sm table-bordered mb-2" id="breakdown_table">
                                    <thead class="table-light">
                                        <tr class="text-center small">
                                            <th>Component Name</th>
                                            <th>Brand / Spec</th>
                                            <th>App Process</th>
                                            <th width="10%">Qty</th>
                                            <th width="20%">Price (Unit)</th>
                                            <th width="5%"><i class="fas fa-trash-alt"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="breakdown_table_body"></tbody>
                                </table>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill"
                                    onclick="addBreakdownRow()">
                                    <i class="fas fa-plus me-1"></i> Add Component
                                </button>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-light btn-sm px-3 me-2" id="cancelEditBtn"
                                    onclick="cancelEdit()" style="display: none;">Cancel Edit</button>
                                <button type="button" class="btn btn-light btn-sm px-3 me-2"
                                    onclick="hideAddItemRow()">Close Form</button>
                                <button type="button" class="btn btn-primary btn-sm px-4" id="addItemBtn"
                                    onclick="addItem()">Add to List</button>
                                <button type="button" class="btn btn-warning btn-sm px-4 text-white d-none"
                                    id="updateItemBtn" onclick="updateItem()">Update Item</button>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive rounded-3 border shadow-sm">
                        <table class="table table-hover mb-0" style="font-size: 0.8rem;">
                            <thead class="bg-primary text-white">
                                <tr class="align-middle">
                                    <th width="4%" class="text-center">No</th>
                                    <th width="12%">Machine / Equipment</th>
                                    <!-- Removed Model -->
                                    <th width="10%">Brand / Spec</th>
                                    <th width="4%" class="text-center">Qty</th>
                                    <th width="10%">App Process</th>
                                    <th width="8%">Condition</th>
                                    <th width="12%" class="text-end">Total Cost</th>
                                    <th width="12%" class="text-end">PR Used</th>
                                    <th width="12%" class="text-end">Remaining</th>
                                    <th width="8%" class="text-center">Schedule</th>
                                    @if($isEditable)
                                        <th width="5%" class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="itemsTable">
                                <!-- Dynamic content -->
                            </tbody>
                            <tfoot class="bg-light fw-bold" id="totalRow">
                                <tr>
                                    <td colspan="6" class="text-end py-3">GRAND TOTAL (TOTAL INVESTMENT)</td>
                                    <td id="grandTotalDisplay" class="text-end py-3 text-primary">IDR 0</td>
                                    <td id="grandTotalPrUsed" class="text-end py-3" style="color: #b45309;">IDR 0</td>
                                    <td id="grandTotalRemaining" class="text-end py-3 text-success">IDR 0</td>
                                    <td></td>
                                    @if($isEditable)
                                        <td></td>
                                    @endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="itemsContainer"></div>
            </form>

            <div class="mt-5 pt-4 border-top">
                <h6 class="fw-bold mb-4" style="color: #1e3a5f;">Approval History & Status</h6>

                <div class="position-relative">
                    <div class="position-absolute top-0 start-0 w-100"
                        style="height: 2px; background: #e2e8f0; top: 12px; z-index: 0;"></div>
                    <div class="row position-relative" style="z-index: 1;">
                        <div class="col-md-3 text-center">
                            @php
                                $isDraft = $plan->status == 'Draft';
                                $step1Label = $isDraft ? 'Draft' : 'Submitted';
                                $step1BadgeClass = $isDraft ? 'bg-secondary' : 'bg-success';
                                $step1Icon = $isDraft ? 'pencil-alt' : 'check';
                            @endphp
                            <div class="{{ $step1BadgeClass }} text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm"
                                style="width: 24px; height: 24px;">
                                <i class="fas fa-{{ $step1Icon }}" style="font-size: 12px;"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">{{ $step1Label }}</h6>
                            <div class="badge {{ $step1BadgeClass }} mb-2">{{ $step1Label }}</div>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-user me-1"></i> {{ $plan->creator->full_name ?? 'Unknown' }}
                            </p>
                            <p class="text-xs text-muted">
                                {{ ($plan->submitted_at ?? $plan->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                            </p>
                        </div>

                        @php
                            $isDeptApproved = !is_null($plan->dept_head_id);
                            $isDeptPending = $plan->status == 'Submitted' && $plan->current_approver_role == 'Dept Head';
                            $deptColor = $isDeptApproved ? 'success' : ($isDeptPending ? 'warning' : 'secondary');
                            $deptIcon = $isDeptApproved ? 'check' : ($isDeptPending ? 'clock' : 'circle');
                        @endphp
                        <div class="col-md-3 text-center">
                            <div class="bg-{{ $deptColor }} text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm"
                                style="width: 24px; height: 24px;">
                                <i class="fas fa-{{ $deptIcon }}" style="font-size: 12px;"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Dept Head</h6>
                            @if($isDeptApproved)
                                <div class="badge bg-success mb-2">Approved</div>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-user-check me-1"></i> {{ $plan->deptHeadApprover->full_name ?? '-' }}
                                </p>
                                <p class="text-xs text-muted">
                                    {{ $plan->dept_head_approved_at ? $plan->dept_head_approved_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }}
                                </p>
                            @elseif($isDeptPending)
                                <div class="badge bg-warning text-dark mb-2">Waiting Approval</div>
                                <p class="small text-muted fst-italic">Pending review...</p>
                            @else
                                <div class="badge bg-light text-muted border mb-2">Pending</div>
                            @endif
                        </div>

                        @php
                            $isDivApproved = !is_null($plan->div_head_id);
                            $isDivPending = $plan->status == 'Submitted' && $plan->current_approver_role == 'Division Head';
                            $divColor = $isDivApproved ? 'success' : ($isDivPending ? 'warning' : 'secondary');
                            $divIcon = $isDivApproved ? 'check' : ($isDivPending ? 'clock' : 'circle');
                        @endphp
                        <div class="col-md-3 text-center">
                            <div class="bg-{{ $divColor }} text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm"
                                style="width: 24px; height: 24px;">
                                <i class="fas fa-{{ $divIcon }}" style="font-size: 12px;"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Division Head</h6>
                            @if($isDivApproved)
                                <div class="badge bg-success mb-2">Approved</div>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-user-check me-1"></i> {{ $plan->divHeadApprover->full_name ?? '-' }}
                                </p>
                                <p class="text-xs text-muted">
                                    {{ $plan->div_head_approved_at ? $plan->div_head_approved_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }}
                                </p>
                            @elseif($isDivPending)
                                <div class="badge bg-warning text-dark mb-2">Waiting Approval</div>
                                <p class="small text-muted fst-italic">Pending review...</p>
                            @else
                                <div class="badge bg-light text-muted border mb-2">Pending</div>
                            @endif
                        </div>

                        @php
                            $isFinApproved = !is_null($plan->finance_id) || $plan->status == 'Approved';
                            $isFinPending = $plan->status == 'Submitted' && $plan->current_approver_role == 'Finance';
                            $finColor = $isFinApproved ? 'success' : ($isFinPending ? 'warning' : 'secondary');
                            $finIcon = $isFinApproved ? 'check' : ($isFinPending ? 'clock' : 'circle');
                        @endphp
                        <div class="col-md-3 text-center">
                            <div class="bg-{{ $finColor }} text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm"
                                style="width: 24px; height: 24px;">
                                <i class="fas fa-{{ $finIcon }}" style="font-size: 12px;"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Finance</h6>
                            @if($isFinApproved)
                                <div class="badge bg-success mb-2">Approved</div>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-user-check me-1"></i> {{ $plan->financeApprover->full_name ?? '-' }}
                                </p>
                                <p class="text-xs text-muted">
                                    {{ $plan->finance_approved_at ? $plan->finance_approved_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }}
                                </p>
                            @elseif($isFinPending)
                                <div class="badge bg-warning text-dark mb-2">Waiting Approval</div>
                                <p class="small text-muted fst-italic">Pending review...</p>
                            @else
                                <div class="badge bg-light text-muted border mb-2">Pending</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between py-3 border-top">
            <div class="d-flex gap-2">
                <a href="{{ route('budget.index') }}" class="btn btn-light px-4 rounded-pill">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <a href="{{ route('budget.print', $plan->id) }}" target="_blank"
                    class="btn btn-outline-dark px-4 rounded-pill">
                    <i class="fas fa-print me-2"></i>Print Plan
                </a>
            </div>
            @if($isEditable)
                <div class="d-flex gap-2">
                    @if($plan->status == 'Draft' || $plan->status == 'Rejected')
                        <button type="submit" name="action" value="draft" form="budgetForm"
                            class="btn btn-outline-primary px-4 rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i>Save as Draft
                        </button>
                        <button type="submit" name="action" value="submit" form="budgetForm"
                            class="btn btn-primary px-4 rounded-pill shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i>Submit
                        </button>
                    @else
                        <button type="submit" name="action" value="update" form="budgetForm"
                            class="btn btn-primary px-4 rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i>Update Plan
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius: 15px;">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold" style="color: #1e3a5f;">
                        <i class="fas fa-pen me-2 text-primary"></i>Edit Machine / Equipment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_item_index">
                    <div class="row g-3 mb-3">
                        <div class="col-md-2">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Code</label>
                            <input type="text" id="edit_item_code" class="form-control" placeholder="A/B">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Category</label>
                            <input type="text" id="edit_item_category" class="form-control" list="category_list">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Machine / Equipment Name</label>
                            <input type="text" id="edit_item_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Brand / Model / Spec</label>
                            <input type="text" id="edit_item_brand" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Application Process</label>
                            <input type="text" id="edit_item_app_process" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Process Section</label>
                            <select id="edit_process" class="form-select">
                                <option value="Preparation">Preparation</option>
                                <option value="Final Assy">Final Assy</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Condition Status</label>
                            <select id="edit_item_cond_status" class="form-select">
                                <option value="Ready">Ready</option>
                                <option value="Not Ready">Not Ready</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Condition Notes</label>
                            <input type="text" id="edit_item_cond_notes" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-2">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Qty</label>
                            <input type="number" id="edit_qty" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">UoM</label>
                            <input type="text" id="edit_uom" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Target Schedule</label>
                            <input type="date" id="edit_item_target" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-uppercase text-muted small fw-bold"
                                style="font-size: 0.65rem;">Unit Cost (IDR)</label>
                            <input type="text" id="edit_price_display" class="form-control" onkeyup="formatNumber(this)"
                                required>
                            <input type="hidden" id="edit_price" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light p-3" style="border-radius: 0 0 15px 15px;">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 rounded-pill" onclick="saveEditItem()">
                        <i class="fas fa-save me-2"></i>Update Item
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Item Modal (Admin Only) -->
    @if(auth()->user()->role === 'Super Admin')
        <div class="modal fade" id="transferItemModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" style="color: #f59e0b;">
                            <i class="fas fa-exchange-alt me-2"></i>Transfer Item
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="transferItemForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body pt-4">
                            <!-- Item Info -->
                            <div class="mb-4">
                                <label class="form-label text-uppercase text-muted small fw-semibold"
                                    style="font-size: 0.7rem;">Item to Transfer</label>
                                <div class="fw-bold" id="transfer_item_name">-</div>
                                <small class="text-primary" id="transfer_current_io">Current IO: -</small>
                            </div>

                            <!-- Target Budget Plan -->
                            <div class="mb-3">
                                <label class="form-label text-uppercase text-muted small fw-semibold"
                                    style="font-size: 0.7rem;">
                                    Select Target Budget (IO) <span class="text-danger">*</span>
                                </label>
                                <select name="target_plan" id="transfer_target_plan" class="form-select" required>
                                    <option value="">-- Select Target IO --</option>
                                    @foreach(\App\Models\BudgetPlan::with('project')->orderBy('id', 'desc')->get() as $targetPlan)
                                        <option value="{{ $targetPlan->id }}" {{ $plan->id == $targetPlan->id ? 'disabled' : '' }}>
                                            {{ $targetPlan->io_number ?? 'N/A' }} -
                                            {{ $targetPlan->project->project_name ?? 'Unknown Project' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Reason -->
                            <div class="mb-3">
                                <label class="form-label text-uppercase text-muted small fw-semibold"
                                    style="font-size: 0.7rem;">
                                    Reason for Transfer <span class="text-danger">*</span>
                                </label>
                                <textarea name="transfer_reason" id="transfer_reason" class="form-control" rows="3"
                                    placeholder="Why is this item moving to another IO?" required></textarea>
                            </div>

                            <!-- Berita Acara Upload -->
                            <div class="mb-3">
                                <label class="form-label text-uppercase text-muted small fw-semibold"
                                    style="font-size: 0.7rem;">
                                    Berita Acara (PDF) <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="berita_acara" id="transfer_berita_acara" class="form-control"
                                    accept=".pdf" required>
                                <div class="form-text text-muted" style="font-size: 0.7rem;">
                                    <i class="fas fa-file-pdf text-danger me-1"></i>
                                    Upload berita acara dalam format PDF (maks. 10MB)
                                </div>
                            </div>

                            <!-- Info Alert -->
                            <div class="alert d-flex align-items-start"
                                style="background-color: #e0f2fe; border: none; border-radius: 8px;">
                                <i class="fas fa-info-circle text-info me-2 mt-1"></i>
                                <small class="text-muted">The item will be removed from the current plan and added to the
                                    selected target plan immediately. Berita acara wajib dilampirkan.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn" style="background-color: #f59e0b; color: #fff;">
                                <i class="fas fa-check me-2"></i>Confirm Transfer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    <datalist id="category_list">
        @foreach($categories as $cat)
            <option value="{{ $cat }}">
        @endforeach
    </datalist>
@endsection

@push('scripts')
    <script>
            // Initialize items with breakd    own structure
            let items = [
                @foreach($plan->items->whereNull('parent_item_id') as $item)
                            {
                    id: {{ $item->id }},
                    code: "{{ addslashes($item->item_code ?? '') }}",
                    category: "{{ addslashes($item->category ?? '') }}",
                    process: "{{ addslashes($item->process ?? 'Preparation') }}",
                    name: "{{ addslashes($item->item_name) }}",
                    brand_spec: "{{ addslashes($item->brand_spec ?? '') }}",
                    app_process: "{{ addslashes($item->application_process ?? '') }}",
                    qty: {{ $item->qty }},
                    price: {{ $item->estimated_price ?? 0 }},
                    pr_used: {{ $prUsagePerItem[$item->id] ?? 0 }},
                    condition_status: "{{ addslashes($item->condition_status ?? 'Ready') }}",
                    condition_notes: "{{ addslashes($item->condition_notes ?? '') }}",
                    target_schedule: "{{ $item->target_schedule ?? '' }}",
                    year: "{{ $item->fiscal_year ?? $plan->fiscal_year }}",
                    breakdown: [
                        @foreach($plan->items->where('parent_item_id', $item->id) as $sub)
                                        {
                            name: "{{ addslashes($sub->item_name) }}",
                            brand: "{{ addslashes($sub->brand_spec ?? '') }}",
                            app_process: "{{ addslashes($sub->application_process ?? '') }}",
                            qty: {{ $sub->qty }},
                            price: {{ $sub->estimated_price ?? 0 }}
                                        },
                        @endforeach
                                ],
                    has_breakdown: {{ $plan->items->where('parent_item_id', $item->id)->count() > 0 ? 'true' : 'false' }}
                            },
                @endforeach
                    ];

            let editingindex = -1;

            document.addEventListener('DOMContentLoaded', function () {
                renderTable();
                const select = document.getElementById('projectIoSelect');
                if (select && select.value) {
                    updateProjectIo(select);
                }
            });

            function toggleBreakdown() {
                const isChecked = document.getElementById('has_breakdown').checked;
                const section = document.getElementById('breakdown_section');
                if (isChecked) {
                    section.classList.remove('d-none');
                    calculateBreakdownTotal();
                } else {
                    section.classList.add('d-none');
                }
            }

            function addBreakdownRow(data = null) {
                const tbody = document.getElementById('breakdown_table_body');
                const row = document.createElement('tr');

                const name = data ? data.name : '';
                const brand = data ? data.brand : '';
                const process = data ? data.app_process : '';
                const qty = data ? data.qty : 1;
                const price = data ? data.price : 0;
                const priceDisplay = formatWithDots(price);

                row.innerHTML = `
                                    <td><input type="text" class="form-control form-control-sm bd-name" placeholder="Component Name" value="${name}" onblur="this.value = toFlexibleProperCase(this.value)"></td>
                                    <td><input type="text" class="form-control form-control-sm bd-brand" placeholder="Brand/Spec" value="${brand}" onblur="this.value = toFlexibleProperCase(this.value)"></td>
                                    <td><input type="text" class="form-control form-control-sm bd-process" placeholder="App Process" value="${process}" onblur="this.value = toFlexibleProperCase(this.value)"></td>
                                    <td><input type="number" class="form-control form-control-sm bd-qty" value="${qty}" min="1" onchange="calculateBreakdownTotal()"></td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm bd-price-display" value="${priceDisplay}" onkeyup="formatNumber(this); calculateBreakdownTotal()">
                                        <input type="hidden" class="bd-price" value="${price}">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm text-danger" onclick="removeBreakdownRow(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                `;
                tbody.appendChild(row);
            }

            function removeBreakdownRow(btn) {
                btn.closest('tr').remove();
                calculateBreakdownTotal();
            }

            function calculateBreakdownTotal() {
                if (!document.getElementById('has_breakdown').checked) return;

                let total = 0;
                document.querySelectorAll('#breakdown_table_body tr').forEach(row => {
                    const qty = parseFloat(row.querySelector('.bd-qty').value) || 0;
                    const price_disp = row.querySelector('.bd-price-display').value.replace(/\./g, '');
                    const price = parseFloat(price_disp) || 0;
                    row.querySelector('.bd-price').value = price;
                    total += qty * price;
                });
                // Main item price manual input, so no auto-update here
            }

            function updateProjectIo(selectElement) {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                document.getElementById('project_id').value = selectedOption.getAttribute('data-project-id');
                document.getElementById('io_number').value = selectElement.value;
            }

            function formatNumber(input) {
                let value = input.value.replace(/[^\d]/g, '');
                if (value.length > 1) value = value.replace(/^0+/, '');
                if (value === '') value = '0';
                input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                if (input.id === 'item_price_display') {
                    document.getElementById('item_price').value = value;
                }
            }

            function toFlexibleProperCase(str) {
                if (!str) return str;
                if (str === str.toUpperCase() && /[A-Z]/.test(str)) return str;
                return str.replace(/\w\S*/g, function (txt) {
                    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                });
            }

            function formatWithDots(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function showAddItemRow() {
                document.getElementById('addItemRow').style.display = 'block';
                resetForm();
                document.getElementById('addItemBtn').classList.remove('d-none');
                document.getElementById('updateItemBtn').classList.add('d-none');
                document.getElementById('cancelEditBtn').classList.add('d-none');
            }

            function hideAddItemRow() {
                document.getElementById('addItemRow').style.display = 'none';
                resetForm();
            }

            function addItem() {
                const data = {
                    code: document.getElementById('item_code').value,
                    category: document.getElementById('item_category').value,
                    process: document.getElementById('item_process').value,
                    name: document.getElementById('item_name').value,
                    brand_spec: document.getElementById('item_brand').value,
                    app_process: document.getElementById('item_app_process').value,
                    qty: parseFloat(document.getElementById('item_qty').value),
                    price: parseFloat(document.getElementById('item_price').value),
                    condition_status: document.getElementById('item_condition_status').value,
                    condition_notes: document.getElementById('item_condition_notes').value,
                    target_schedule: document.getElementById('item_schedule').value,
                    year: document.querySelector('[name="fiscal_year"]').value,
                    breakdown: [],
                    has_breakdown: document.getElementById('has_breakdown').checked
                };

                if (data.has_breakdown) {
                    document.querySelectorAll('#breakdown_table_body tr').forEach(row => {
                        data.breakdown.push({
                            name: row.querySelector('.bd-name').value,
                            brand: row.querySelector('.bd-brand').value,
                            app_process: row.querySelector('.bd-process').value,
                            qty: parseFloat(row.querySelector('.bd-qty').value),
                            price: parseFloat(row.querySelector('.bd-price').value)
                        });
                    });

                    if (data.breakdown.length === 0) {
                        Swal.fire('Warning', 'Please add at least one breakdown component', 'warning');
                        return;
                    }
                } else if (!data.name || data.qty <= 0 || data.price <= 0) {
                    Swal.fire('Warning', 'Please enter Name, Qty, and Price', 'warning');
                    return;
                }

                items.push(data);
                renderTable();
                hideAddItemRow();
            }

            function editItem(index) {
                const item = items[index];
                editingindex = index;

                showAddItemRow();
                document.getElementById('addItemBtn').classList.add('d-none');
                document.getElementById('updateItemBtn').classList.remove('d-none');
                document.getElementById('cancelEditBtn').classList.remove('d-none');

                document.getElementById('item_code').value = item.code;
                document.getElementById('item_category').value = item.category;
                document.getElementById('item_process').value = item.process;
                document.getElementById('item_name').value = item.name;
                document.getElementById('item_brand').value = item.brand_spec;
                document.getElementById('item_app_process').value = item.app_process;
                document.getElementById('item_qty').value = item.qty;
                document.getElementById('item_price').value = item.price;
                document.getElementById('item_price_display').value = formatWithDots(item.price);
                document.getElementById('item_condition_status').value = item.condition_status;
                document.getElementById('item_condition_notes').value = item.condition_notes;
                document.getElementById('item_schedule').value = item.target_schedule;

                // Handle Breakdown
                if (item.has_breakdown && item.breakdown) {
                    document.getElementById('has_breakdown').checked = true;
                    toggleBreakdown();
                    document.getElementById('breakdown_table_body').innerHTML = '';
                    item.breakdown.forEach(bd => addBreakdownRow(bd));
                } else {
                    document.getElementById('has_breakdown').checked = false;
                    toggleBreakdown();
                    document.getElementById('breakdown_table_body').innerHTML = '';
                }

                document.getElementById('addItemRow').scrollIntoView({ behavior: 'smooth' });
            }

            function updateItem() {
                if (editingindex === -1) return;

                const data = {
                    code: document.getElementById('item_code').value,
                    category: document.getElementById('item_category').value,
                    process: document.getElementById('item_process').value,
                    name: document.getElementById('item_name').value,
                    brand_spec: document.getElementById('item_brand').value,
                    app_process: document.getElementById('item_app_process').value,
                    qty: parseFloat(document.getElementById('item_qty').value),
                    price: parseFloat(document.getElementById('item_price').value),
                    condition_status: document.getElementById('item_condition_status').value,
                    condition_notes: document.getElementById('item_condition_notes').value,
                    target_schedule: document.getElementById('item_schedule').value,
                    year: document.querySelector('[name="fiscal_year"]').value,
                    breakdown: [],
                    has_breakdown: document.getElementById('has_breakdown').checked
                };

                if (data.has_breakdown) {
                    document.querySelectorAll('#breakdown_table_body tr').forEach(row => {
                        data.breakdown.push({
                            name: row.querySelector('.bd-name').value,
                            brand: row.querySelector('.bd-brand').value,
                            app_process: row.querySelector('.bd-process').value,
                            qty: parseFloat(row.querySelector('.bd-qty').value),
                            price: parseFloat(row.querySelector('.bd-price').value)
                        });
                    });
                    if (data.breakdown.length === 0) {
                        Swal.fire('Warning', 'Please add at least one breakdown component', 'warning');
                        return;
                    }
                } else if (!data.name || data.qty <= 0 || data.price <= 0) {
                    Swal.fire('Warning', 'Please enter Name, Qty, and Price', 'warning');
                    return;
                }

                items[editingindex] = data;
                renderTable();
                cancelEdit();
            }

            function cancelEdit() {
                resetForm();
                editingindex = -1;
                hideAddItemRow();
            }

            function resetForm() {
                document.getElementById('item_code').value = '';
                document.getElementById('item_category').value = '';
                document.getElementById('item_name').value = '';
                document.getElementById('item_brand').value = '';
                document.getElementById('item_app_process').value = '';
                document.getElementById('item_qty').value = '1';
                document.getElementById('item_price').value = '0';
                document.getElementById('item_price_display').value = '0';
                document.getElementById('item_condition_status').value = 'Ready';
                document.getElementById('item_condition_notes').value = '';
                document.getElementById('item_schedule').value = '';

                document.getElementById('has_breakdown').checked = false;
                toggleBreakdown();
                document.getElementById('breakdown_table_body').innerHTML = '';
            }

            function removeItem(index) {
                if (confirm('Are you sure you want to remove this item?')) {
                    items.splice(index, 1);
                    renderTable();
                }
            }

            function renderTable() {
                const tbody = document.getElementById('itemsTable');
                const footer = document.getElementById('totalRow');
                const form = document.getElementById('budgetForm');
                const grandTotalDisplay = document.getElementById('grandTotalDisplay');

                // Find or create itemsContainer inside the form
                let container = document.getElementById('itemsContainer');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'itemsContainer';
                    form.appendChild(container);
                    console.log('itemsContainer created dynamically');
                }

                if (items.length === 0) {
                    tbody.innerHTML = '<tr id="emptyRow"><td colspan="9" class="text-center py-4 text-muted small"><img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="48" class="opacity-25 mb-2"><br>No items added yet.</td></tr>';
                    grandTotalDisplay.innerText = 'IDR 0';
                    container.innerHTML = '';
                    return;
                }

                tbody.innerHTML = '';
                container.innerHTML = '';

                let grandTotal = 0;

                const grouped = {};
                items.forEach((item, index) => {
                    const key = item.code
                        ? `${item.code}. ${item.category} - ${item.process}`
                        : `${item.category} - ${item.process}`;
                    if (!grouped[key]) grouped[key] = [];
                    grouped[key].push({ ...item, originalIndex: index });
                });

                for (const key in grouped) {
                    let originalIndexCounter = 1;
                    const headerRow = document.createElement('tr');
                    headerRow.className = 'table-light fw-bold';
                    headerRow.innerHTML = `<td colspan="11" class="py-2 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">${key}</td>`;
                    tbody.appendChild(headerRow);

                    grouped[key].forEach(item => {
                        const total = item.qty * item.price;
                        grandTotal += total;
                        const index = item.originalIndex;

                        const rowClass = item.has_breakdown ? 'fw-bold bg-white' : '';
                        const row = document.createElement('tr');
                        row.className = `align-middle ${rowClass}`;
                        row.innerHTML = `
                                    <td class="text-center">${originalIndexCounter++}</td>
                                    <td>
                                        <div class="fw-bold">${item.name}</div>
                                        ${item.has_breakdown ? '<div class="small text-muted fst-italic mt-1">Include:</div>' : ''}
                                    </td>
                                    <!-- Removed Model -->
                                    <td>${item.brand_spec || '-'}</td>
                                    <td class="text-center">${item.qty} ${item.uom || 'Unit'}</td>
                                    <td>${item.app_process || '-'}</td>
                                    <td>
                                        <span class="badge bg-${item.condition_status === 'Ready' ? 'success' : 'warning'} bg-opacity-10 text-${item.condition_status === 'Ready' ? 'success' : 'dark'} border">${item.condition_status}</span>
                                        ${item.condition_notes ? `<div class="small text-muted mt-1">${item.condition_notes}</div>` : ''}
                                    </td>
                                    <td class="text-end fw-bold">IDR ${formatWithDots(total)}</td>
                                    <td class="text-end" style="color: #b45309; font-weight: 600;">IDR ${formatWithDots(item.pr_used || 0)}</td>
                                    <td class="text-end fw-bold" style="color: ${(total - (item.pr_used || 0)) < 0 ? '#dc3545' : '#198754'};">
                                        IDR ${formatWithDots(total - (item.pr_used || 0))}
                                    </td>
                                    <td class="text-center">${item.target_schedule || '-'}</td>
                                    @if($isEditable)
                                        <td class="text-center pe-3">
                                            <div class="d-flex justify-content-center gap-1">
                                            @if(auth()->user()->role === 'Super Admin')
                                                ${item.id ? `<button type="button" class="btn btn-sm btn-outline-warning border-0" onclick="openTransfer(${index})" title="Transfer"><i class="fas fa-exchange-alt"></i></button>` : ''}
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0" onclick="editItem(${index})"><i class="fas fa-pen"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeItem(${index})"><i class="fas fa-trash"></i></button>
                                        </div>
                                        </td>
                                    @endif
                                `;
                        tbody.appendChild(row);

                        // Add Hidden Inputs for Form Submission
                        let html = `
                                    <input type="hidden" name="items[${index}][code]" value="${item.code}">
                                    <input type="hidden" name="items[${index}][category]" value="${item.category}">
                                    <input type="hidden" name="items[${index}][process]" value="${item.process}">
                                    <input type="hidden" name="items[${index}][name]" value="${item.name}">
                                    <input type="hidden" name="items[${index}][brand_spec]" value="${item.brand_spec}">
                                    <input type="hidden" name="items[${index}][application_process]" value="${item.app_process}">
                                    <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                                    <input type="hidden" name="items[${index}][price]" value="${item.price}">
                                    <input type="hidden" name="items[${index}][condition_status]" value="${item.condition_status}">
                                    <input type="hidden" name="items[${index}][condition_notes]" value="${item.condition_notes}">
                                    <input type="hidden" name="items[${index}][target_schedule]" value="${item.target_schedule}">
                                    <input type="hidden" name="items[${index}][year]" value="${item.year}">
                                `;

                        // Breakdown Hidden Inputs & Display Items
                        if (item.has_breakdown && item.breakdown.length > 0) {
                            item.breakdown.forEach((bd, i) => {
                                html += `
                                            <input type="hidden" name="items[${index}][breakdown][${i}][name]" value="${bd.name}">
                                            <input type="hidden" name="items[${index}][breakdown][${i}][brand]" value="${bd.brand}">
                                            <input type="hidden" name="items[${index}][breakdown][${i}][app_process]" value="${bd.app_process}">
                                            <input type="hidden" name="items[${index}][breakdown][${i}][qty]" value="${bd.qty}">
                                            <input type="hidden" name="items[${index}][breakdown][${i}][price]" value="${bd.price}">
                                        `;

                                // Visual Sub-row
                                const subTr = document.createElement('tr');
                                subTr.className = 'align-middle small text-muted';
                                subTr.style.backgroundColor = '#f8f9fa';
                                subTr.innerHTML = `
                                            <td></td>
                                            <td class="ps-4"><i class="fas fa-level-up-alt fa-rotate-90 me-2"></i> ${bd.name}</td>
                                            <td>${bd.brand || '-'}</td>
                                            <td class="text-center">${bd.qty} Unit</td>
                                            <td>${bd.app_process || '-'}</td>
                                            <td><span class="badge bg-secondary">Detail</span></td>
                                            <td class="text-end">IDR ${formatWithDots(bd.price * bd.qty)}</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        `;
                                tbody.appendChild(subTr);
                            });
                        }

                        container.insertAdjacentHTML('beforeend', html);
                    });
                }

                grandTotalDisplay.innerText = 'IDR ' + formatWithDots(grandTotal);

                // Calculate totals for PR Used and Remaining
                let totalPrUsed = 0;
                items.forEach(item => {
                    totalPrUsed += (item.pr_used || 0);
                });
                const totalRemaining = grandTotal - totalPrUsed;

                const grandTotalPrUsed = document.getElementById('grandTotalPrUsed');
                const grandTotalRemaining = document.getElementById('grandTotalRemaining');
                if (grandTotalPrUsed) grandTotalPrUsed.innerText = 'IDR ' + formatWithDots(totalPrUsed);
                if (grandTotalRemaining) {
                    grandTotalRemaining.innerText = 'IDR ' + formatWithDots(totalRemaining);
                    grandTotalRemaining.style.color = totalRemaining < 0 ? '#dc3545' : '#198754';
                }
            }

            @if(auth()->user()->role === 'Super Admin')
                function openTransfer(index) {
                    const item = items[index];
                    if (!item.id) {
                        alert('This item must be saved first before it can be transferred.');
                        return;
                    }

                    document.getElementById('transfer_item_name').textContent = item.name;
                    document.getElementById('transfer_current_io').textContent = 'Current IO: {{ $plan->io_number ?? "N/A" }}';
                    document.getElementById('transfer_target_plan').value = '';
                    document.getElementById('transfer_reason').value = '';

                    const form = document.getElementById('transferItemForm');
                    form.action = '/budget/item/' + item.id + '/transfer';

                    new bootstrap.Modal(document.getElementById('transferItemModal')).show();
                }
            @endif

            // Form submit handler: inject all item hidden inputs from JS array into form
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('budgetForm');
                if (form) {
                    // Track which button was clicked
                    let clickedAction = 'draft';
                    form.querySelectorAll('button[name="action"]').forEach(btn => {
                        btn.addEventListener('click', function () {
                            clickedAction = this.value;
                        });
                    });
                    // Also handle external buttons with form="budgetForm"
                    document.querySelectorAll('button[form="budgetForm"][name="action"]').forEach(btn => {
                        btn.addEventListener('click', function () {
                            clickedAction = this.value;
                        });
                    });

                    form.addEventListener('submit', function (e) {
                        // Inject action as hidden input to ensure it reaches the server
                        let actionInput = form.querySelector('input[name="action"]');
                        if (!actionInput) {
                            actionInput = document.createElement('input');
                            actionInput.type = 'hidden';
                            actionInput.name = 'action';
                            form.appendChild(actionInput);
                        }
                        actionInput.value = clickedAction;

                        // Remove any existing item inputs first
                        form.querySelectorAll('input[name^="items["]').forEach(el => el.remove());

                        // Create hidden inputs from JS items array
                        items.forEach((item, index) => {
                            const fields = {
                                'code': item.code || '',
                                'category': item.category || '',
                                'process': item.process || '',
                                'name': item.name || '',
                                'brand_spec': item.brand_spec || '',
                                'application_process': item.app_process || '',
                                'qty': item.qty || 1,
                                'price': item.price || 0,
                                'condition_status': item.condition_status || '',
                                'condition_notes': item.condition_notes || '',
                                'target_schedule': item.target_schedule || '',
                                'year': item.year || ''
                            };

                            for (const [key, value] of Object.entries(fields)) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = `items[${index}][${key}]`;
                                input.value = value;
                                form.appendChild(input);
                            }

                            // Add breakdown sub-items
                            if (item.has_breakdown && item.breakdown) {
                                item.breakdown.forEach((bd, i) => {
                                    const bdFields = {
                                        'name': bd.name || '',
                                        'brand': bd.brand || '',
                                        'app_process': bd.app_process || '',
                                        'qty': bd.qty || 1,
                                        'price': bd.price || 0
                                    };
                                    for (const [key, value] of Object.entries(bdFields)) {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = `items[${index}][breakdown][${i}][${key}]`;
                                        input.value = value;
                                        form.appendChild(input);
                                    }
                                });
                            }
                        });

                        console.log('Injected ' + items.length + ' items into form');
                    });
                }
            });
        </script>
@endpush