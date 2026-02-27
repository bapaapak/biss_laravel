@extends('layouts.app')

@section('content')
<style>
    .footer-action-bar {
        position: fixed;
        bottom: 0;
        left: 280px;
        width: calc(100% - 280px);
        z-index: 1050;
        transition: left 0.3s ease-in-out, width 0.3s ease-in-out;
    }
    @media (max-width: 991.98px) {
        .footer-action-bar {
            left: 0;
            width: 100%;
        }
    }

    /* Timeline Styling */
    .workflow-tracker {
        position: relative;
        padding-left: 35px;
        margin-top: 10px;
    }
    .workflow-tracker::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
        z-index: 0;
    }
    .workflow-step {
        position: relative;
        padding-bottom: 25px;
    }
    .workflow-step:last-child {
        padding-bottom: 0;
    }
    .step-indicator {
        position: absolute;
        left: -35px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        font-size: 10px;
        color: #94a3b8;
    }
    .step-indicator.completed {
        background: #10b981;
        border-color: #10b981;
        color: #fff;
    }
    .step-indicator.active {
        background: #fff;
        border-color: #3b82f6;
        color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    .step-content {
        padding-top: 2px;
    }
    .step-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }
    .step-meta {
        font-size: 0.75rem;
        color: #64748b;
    }
    .extra-small {
        font-size: 0.7rem;
    }
</style>
<!-- Form Wrapper if Editable -->
@if($is_editable)
<form action="{{ route('pr.update', $items->first()->id) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="pr_number" value="{{ $pr_number }}">
@endif

<div class="row align-items-center mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-0 text-dark">{{ $is_editable ? 'Edit Purchase Request' : 'View Purchase Request' }}</h4>
                <div class="text-muted small mt-1">{{ $pr_number }}</div>
            </div>
            @php
                $status = $items->first()->status;
                $badgeClass = match($status) {
                    'Approved' => 'bg-success-subtle text-success border border-success',
                    'Rejected' => 'bg-danger-subtle text-danger border border-danger',
                    default => 'bg-warning-subtle text-warning border border-warning'
                };
                $icon = match($status) {
                    'Approved' => 'fa-check-circle',
                    'Rejected' => 'fa-times-circle',
                    default => 'fa-clock'
                };
            @endphp
            <div class="badge {{ $badgeClass }} rounded-pill px-3 py-2 d-flex align-items-center gap-2">
                <i class="fas {{ $icon }}"></i> {{ $status }}
            </div>
        </div>
    </div>
</div>

<!-- Alert for Read Only -->
@if(!$is_editable && ($status == 'Approved' || $status == 'Rejected'))
<div class="alert alert-warning border-warning bg-warning-subtle text-warning-emphasis d-flex align-items-center gap-2 mb-4">
    <i class="fas fa-lock"></i>
    <strong>Read Only Mode:</strong> This record is {{ $status }} and cannot be edited.
</div>
@endif

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-12">
        <!-- Header Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted mb-1">DEPARTMENT</label>
                        <select name="department" class="form-select bg-light" {{ !$is_editable ? 'disabled' : '' }}>
                            <option value="">-- Select Dept --</option>
                            @foreach($depts as $d)
                            <option value="{{ $d->dept_name }}" {{ $header['department'] == $d->dept_name ? 'selected' : '' }}>{{ $d->dept_code }} | {{ $d->dept_name }}</option>
                            @endforeach
                            @if(!collect($depts)->contains('dept_name', $header['department']))
                                <option value="{{ $header['department'] }}" selected>{{ $header['department'] }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted mb-1">BUSINESS CATEGORY</label>
                        <select name="category" class="form-select bg-light" {{ !$is_editable ? 'disabled' : '' }}>
                            <option value="">-- Select Category --</option>
                            @foreach($cats as $c)
                            <option value="{{ $c->category_name }}" {{ isset($header['category']) && $header['category'] == $c->category_name ? 'selected' : '' }}>{{ $c->category_code }} | {{ $c->category_name }}</option>
                            @endforeach
                            @if(isset($header['category']) && $header['category'] !== '-' && !collect($cats)->contains('category_name', $header['category']))
                                <option value="{{ $header['category'] }}" selected>{{ $header['category'] }}</option>
                            @endif
                        </select>
                    </div>
                </div>
                <!-- ... (Repeat logic for IO, Cost Center, Plant) ... -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted mb-1">IO NUMBER</label>
                        <select name="io_number" class="form-select bg-light" {{ !$is_editable ? 'disabled' : '' }}>
                             <option value="">-- Select IO --</option>
                            @foreach($ios as $io)
                            <option value="{{ $io->io_number }}" {{ $header['io_number'] == $io->io_number ? 'selected' : '' }}>{{ $io->io_number }} - {{ $io->description }}</option>
                            @endforeach
                             @if(!collect($ios)->contains('io_number', $header['io_number']))
                                <option value="{{ $header['io_number'] }}" selected>{{ $header['io_number'] }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold text-muted mb-1">COST CENTER</label>
                        <select name="cost_center" class="form-select bg-light" {{ !$is_editable ? 'disabled' : '' }}>
                            <option value="">-- Select Cost Center --</option>
                            @foreach($ccs as $cc)
                            <option value="{{ $cc->cc_code }}" {{ isset($header['cost_center']) && $header['cost_center'] == $cc->cc_code ? 'selected' : '' }}>{{ $cc->cc_code }} - {{ $cc->cc_name }}</option>
                            @endforeach
                            @if(isset($header['cost_center']) && $header['cost_center'] !== '-' && !collect($ccs)->contains('cc_code', $header['cost_center']))
                                <option value="{{ $header['cost_center'] }}" selected>{{ $header['cost_center'] }}</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-1">PLANT</label>
                        <select name="plant" class="form-select bg-light" {{ !$is_editable ? 'disabled' : '' }}>
                            <option value="">-- Select Plant --</option>
                            @foreach($plants as $p)
                            <option value="{{ $p->plant_code }}" {{ $header['plant'] == $p->plant_code ? 'selected' : '' }}>{{ $p->plant_code }} - {{ $p->plant_name }}</option>
                            @endforeach
                            @if(!collect($plants)->contains('plant_code', $header['plant']))
                                <option value="{{ $header['plant'] }}" selected>{{ $header['plant'] }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-1">S.LOC</label>
                        <select name="storage_location" class="form-select bg-light" {{ !$is_editable ? 'disabled' : '' }}>
                            <option value="">-- Select S.Loc --</option>
                            @foreach($slocs as $sloc)
                            <option value="{{ $sloc->sloc }}" {{ isset($header['storage_location']) && $header['storage_location'] == $sloc->sloc ? 'selected' : '' }}>{{ $sloc->sloc }} | {{ $sloc->description }}</option>
                            @endforeach
                            @if(isset($header['storage_location']) && $header['storage_location'] !== '' && !collect($slocs)->contains('sloc', $header['storage_location']))
                                <option value="{{ $header['storage_location'] }}" selected>{{ $header['storage_location'] }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-1">DUE DATE</label>
                        <input type="date" name="due_date" class="form-control bg-light" value="{{ $header['due_date'] ?? '' }}" {{ !$is_editable ? 'disabled' : '' }}>
                    </div>
                </div>
            </div>
        </div>

        <!-- PR Items Section -->
        <div class="d-flex align-items-center mb-3 mt-4">
            <div style="width: 4px; height: 20px; background-color: #10b981; margin-right: 10px; border-radius: 2px;"></div>
            <h6 class="fw-bold m-0" style="color: #0f172a;">PR Items</h6>
        </div>

@if($is_editable)
    <div id="deleted-items-container"></div>
@endif

@if($is_editable)
<div class="card p-4 mb-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
    <div class="d-flex flex-nowrap overflow-auto gap-2 pb-3 align-items-end" style="width: 100%;">
        <div style="min-width: 350px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">A11 Link</label>
            <select id="newItemBudget" class="form-select form-select-sm">
                <option value="">-- No Link --</option>
                @foreach($budget_items as $b)
                <option value="{{ $b->id }}" data-io="{{ $b->io_number }}" data-price="{{ $b->estimated_price }}" data-remaining="{{ $b->remaining }}">
                    {{ $b->io_number }} - Poin {{ $b->item_code ?? '-' }} ({{ $b->item_name }}) @if($b->model) | {{ $b->model }} @endif | Sisa: Rp {{ number_format($b->remaining, 0, ',', '.') }}
                </option>
                @endforeach
            </select>
        </div>
        <div style="min-width: 150px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">G/L Account</label>
            <input type="text" id="newItemGl" class="form-control form-control-sm" placeholder="G/L">
        </div>
        <div style="min-width: 150px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Asset No (AUC)</label>
            <input type="text" id="newItemAuc" class="form-control form-control-sm" placeholder="AUC">
        </div>
        <div style="min-width: 200px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Item ID (Master)</label>
            <select id="newItemMaster" class="form-select form-select-sm">
                <option value="">-- Master Item --</option>
                @foreach($m_items as $m)
                <option value="{{ $m->item_code }}" data-name="{{ $m->item_name }}">{{ $m->item_code }} - {{ $m->item_name }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width: 250px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Description</label>
            <input type="text" id="newItemDesc" class="form-control form-control-sm" placeholder="Description">
        </div>
        <div style="min-width: 80px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Qty</label>
            <input type="number" id="newItemQty" class="form-control form-control-sm" value="1" min="1">
        </div>
        <div style="min-width: 80px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">UoM</label>
            <input type="text" id="newItemUom" class="form-control form-control-sm" value="Unit">
        </div>
        <div style="min-width: 200px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Cost/Unit</label>
            <div class="input-group input-group-sm">
                <select id="newItemCurr" class="form-select" style="width: 80px; flex: 0 0 auto;">
                    <option value="IDR">IDR</option>
                    <option value="USD">USD</option>
                </select>
                <input type="text" id="newItemCost" class="form-control" placeholder="0" oninput="formatPriceInput(this)">
            </div>
        </div>
        <div style="min-width: 200px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">Peruntukan</label>
            <input type="text" id="newItemPurpose" class="form-control form-control-sm" placeholder="Purpose of this item">
        </div>
        <div style="min-width: 150px;">
            <label class="form-header-label mb-1 text-muted" style="font-size: 0.65rem;">PIC</label>
            <input type="text" id="newItemPic" class="form-control form-control-sm" placeholder="Person In Charge">
        </div>
        <div style="min-width: 120px;">
            <button type="button" class="btn btn-dark-custom btn-sm w-100" onclick="addItem()"
                style="background-color: #1e293b; color: white; height: 31px;">
                <i class="fas fa-plus me-1"></i> Add Item
            </button>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="pr-items-table">
            <thead class="bg-light text-muted small uppercase">
                <tr>
                    <th class="ps-4 py-3" style="font-weight: 600;">A11 Link</th>
                    <th class="py-3" style="font-weight: 600;">G/L Account</th>
                    <th class="py-3" style="font-weight: 600;">AUC</th>
                    <th class="py-3" style="font-weight: 600;">Item ID</th>
                    <th class="py-3" style="font-weight: 600; min-width: 250px;">Description</th>
                    <th class="py-3" style="font-weight: 600; min-width: 150px;">Peruntukan</th>
                    <th class="py-3" style="font-weight: 600;">PIC</th>
                    <th class="py-3" style="font-weight: 600; width: 80px;">Qty</th>
                    <th class="py-3" style="font-weight: 600; width: 80px;">UoM</th>
                    <th class="py-3" style="font-weight: 600; min-width: 150px;">Cost/Unit</th>
                    <th class="text-end pe-4 py-3" style="font-weight: 600; min-width: 150px;">Total</th>
                    <th class="text-end py-3" style="font-weight: 600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($items as $item)
                @php 
                    $total = $item->qty_req * $item->estimated_price; 
                    $grandTotal += $total;
                    $parts = explode(' | Item: ', $item->notes);
                    $desc = count($parts) > 1 ? $parts[1] : $item->notes;
                @endphp
                <tr data-item-id="{{ $item->id }}">
                    <td class="ps-4 fw-medium small">
                        @if($is_editable)
                            <select name="items[{{ $item->id }}][budget_item_id]" class="form-select form-select-sm budget-link-select" onchange="updatePriceFromBudget(this)">
                                <option value="">-- No Link --</option>
                                @foreach($budget_items as $b_item)
                                    <option value="{{ $b_item->id }}" 
                                        data-io="{{ $b_item->io_number }}" 
                                        data-price="{{ $b_item->estimated_price }}"
                                        {{ $item->budget_item_id == $b_item->id ? 'selected' : '' }}>
                                        {{ $b_item->io_number }} - Poin {{ $b_item->item_code ?? '-' }} ({{ $b_item->item_name }})
                                    </option>
                                @endforeach
                            </select>
                        @else
                            @if($item->budget_item_id)
                                <?php $b = $item->item; ?>
                                @if($b)
                                    <div class="text-dark fw-bold">{{ $item->io_number }} - Poin {{ $b->item_code ?? '-' }}</div>
                                    <div class="text-muted" style="font-size: 0.70rem;">{{ $b->item_name }}</div>
                                @else
                                    <div class="text-success small fst-italic mt-1"><i class="fas fa-check-circle me-1" style="font-size: 10px;"></i>A11 Linked</div>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($is_editable)
                            <input type="text" name="items[{{ $item->id }}][gl_account]" class="form-control form-control-sm" value="{{ $item->gl_account ?? '' }}">
                        @else
                            <div class="text-dark small">{{ $item->gl_account ?? '-' }}</div>
                        @endif
                    </td>
                    <td>
                        @if($is_editable)
                            <input type="text" name="items[{{ $item->id }}][asset_no]" class="form-control form-control-sm" value="{{ $item->asset_no ?? '' }}">
                        @else
                            <div class="text-dark small">{{ $item->asset_no ?? '-' }}</div>
                        @endif
                    </td>
                    <td class="text-primary fw-medium small">
                        @if($is_editable)
                            <select name="items[{{ $item->id }}][item_code]" class="form-select form-select-sm">
                                <option value="">-</option>
                                @foreach($m_items as $m)
                                    <option value="{{ $m->item_code }}" {{ $item->item_code == $m->item_code ? 'selected' : '' }}>
                                        {{ $m->item_code }} - {{ $m->item_name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            {{ $item->item_code ?? ($item->item->item_code ?? '-') }}
                        @endif
                        <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                    </td>
                    <td>
                        @if($is_editable)
                            <input type="text" name="items[{{ $item->id }}][description]" class="form-control form-control-sm" value="{{ $desc }}">
                        @else
                            <div class="fw-bold text-dark">{{ $desc }}</div>
                        @endif

                        @if($item->budget_item_id || $item->item)
                        <div class="d-flex align-items-center gap-1 mt-1 text-success small">
                            <i class="fas fa-check-circle" style="font-size: 10px;"></i>
                            <span style="font-size: 11px;">A11 Linked</span>
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($is_editable)
                            <input type="text" name="items[{{ $item->id }}][purpose]" class="form-control form-control-sm" value="{{ $item->purpose }}">
                        @else
                            <div class="text-dark small">{{ $item->purpose ?? '-' }}</div>
                        @endif
                    </td>
                    <td>
                        @if($is_editable)
                            <input type="text" name="items[{{ $item->id }}][pic]" class="form-control form-control-sm" value="{{ $item->pic }}">
                        @else
                            <div class="text-dark small">{{ $item->pic ?? '-' }}</div>
                        @endif
                    </td>
                    <td width="80px">
                        @if($is_editable)
                            <input type="number" name="items[{{ $item->id }}][qty]" class="form-control form-control-sm qty-input" value="{{ $item->qty_req }}" oninput="calculateRowTotal(this)">
                        @else
                            {{ $item->qty_req }}
                        @endif
                    </td>
                    <td width="80px">
                        @if($is_editable)
                            <input type="text" name="items[{{ $item->id }}][uom]" class="form-control form-control-sm" value="{{ $item->uom ?? 'Unit' }}">
                        @else
                            <div class="text-dark small">{{ $item->uom ?? 'Unit' }}</div>
                        @endif
                    </td>
                    <td class="text-muted" width="150px">
                        @if($is_editable)
                            <input type="text" name="items[{{ $item->id }}][price]" class="form-control form-control-sm price-input" value="{{ number_format($item->estimated_price, 0, ',', '.') }}" oninput="formatPriceInput(this); calculateRowTotal(this)">
                        @else
                            IDR {{ number_format($item->estimated_price, 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-end pe-4 fw-bold text-dark row-total">IDR {{ number_format($total, 0, ',', '.') }}</td>
                    <td class="text-end pe-3">
                        @if($is_editable)
                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="deleteItem(this, {{ $item->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <td colspan="8" class="text-end py-3 text-muted fw-bold">Grand Total (Est)</td>
                    <td class="text-end pe-4 py-3 fw-bold text-dark h6 mb-0" id="grand-total" colspan="2">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

    </div> <!-- End Main Content Col -->

    <!-- Sidebar -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm bg-white mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4 text-dark"><i class="fas fa-tasks me-2"></i>Approval History & Status</h6>
                
                <div class="position-relative">
                    <div class="position-absolute start-0 w-100" style="height: 2px; background: #e2e8f0; top: 12px; z-index: 0;"></div>
                    <div class="row position-relative" style="z-index: 1;">
                        @php
                            $pr = $items->first();
                            $stages = [
                                ['label' => 'Submitted', 'role' => 'Requester', 'id' => $pr->requester_id, 'date' => $pr->request_date, 'user' => $pr->requester],
                                ['label' => 'Dept Head', 'role' => 'Dept Head', 'id' => $pr->dept_head_id, 'date' => $pr->dept_head_approved_at, 'user' => $pr->deptHeadApprover],
                                ['label' => 'Finance', 'role' => 'Finance', 'id' => $pr->finance_id, 'date' => $pr->finance_approved_at, 'user' => $pr->financeApprover],
                                ['label' => 'Division Head', 'role' => 'Division Head', 'id' => $pr->div_head_id, 'date' => $pr->div_head_approved_at, 'user' => $pr->divHeadApprover],
                                ['label' => 'Purchasing', 'role' => 'Purchasing', 'id' => $pr->purchasing_id, 'date' => $pr->purchasing_executed_at, 'user' => $pr->purchasingExecutor],
                            ];
                            
                            $currentRole = $pr->current_approver_role;
                            $status = $pr->status;
                        @endphp

                        @foreach($stages as $stage)
                            @php
                                $isCompleted = !empty($stage['date']);
                                $isActive = !$isCompleted && ($currentRole === $stage['role'] || ($stage['label'] === 'Submitted' && $status === 'Draft'));
                                $isNext = !$isCompleted && !$isActive;
                                
                                $badgeClass = $isCompleted ? 'bg-success' : ($isActive ? 'bg-warning text-dark' : 'bg-light text-muted border');
                                $iconBgClass = $isCompleted ? 'bg-success' : ($isActive ? 'bg-warning' : 'bg-secondary');
                                $iconClass = $isCompleted ? 'check' : ($isActive ? 'clock' : 'circle');
                                $badgeText = $isCompleted ? ($stage['label'] === 'Submitted' ? 'Submitted' : 'Approved') : ($isActive ? 'Waiting Approval' : 'Pending');
                                
                                if($status === 'Rejected' && !$isCompleted) {
                                    $badgeClass = 'bg-light text-muted border';
                                    $iconBgClass = 'bg-secondary';
                                    $iconClass = 'times';
                                    $badgeText = 'Cancelled';
                                }
                                if($stage['label'] === 'Submitted' && $status === 'Draft') {
                                    $iconBgClass = 'bg-secondary';
                                    $badgeClass = 'bg-secondary';
                                    $iconClass = 'pencil-alt';
                                    $badgeText = 'Draft';
                                }
                            @endphp
                            <div class="col text-center" style="min-width: 150px;">
                                <div class="{{ $iconBgClass }} text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 24px; height: 24px;">
                                    <i class="fas fa-{{ $iconClass }}" style="font-size: 12px;"></i>
                                </div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">{{ $stage['label'] }}</h6>
                                <div class="badge {{ $badgeClass }} mb-2">{{ $badgeText }}</div>
                                @if($isCompleted)
                                    <p class="small text-muted mb-0">
                                        <i class="fas fa-user me-1"></i> {{ $stage['user']->full_name ?? 'User ID: '.$stage['id'] }}
                                    </p>
                                    <p class="text-xs text-muted">
                                        {{ \Carbon\Carbon::parse($stage['date'])->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                                    </p>
                                @elseif($isActive)
                                    <p class="small text-muted fst-italic mt-2">Pending review...</p>
                                @else
                                    <p class="small text-muted mb-0"><i class="fas fa-user me-1"></i> - </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</div> <!-- End Row -->

<!-- Footer -->
<div class="bg-white border-top p-3 shadow-lg mt-5 footer-action-bar">
    <div class="d-flex justify-content-between align-items-center px-4">
        <span class="text-muted small">
            @if($is_editable)
                <i class="fas fa-edit text-primary me-1"></i> <strong>Admin Edit Mode</strong>
            @else
                <i class="fas fa-eye text-secondary me-1"></i> Read-Only View
            @endif
        </span>
        <div class="d-flex gap-2">
            @php
                $pRow = $items->first();
                $pStatus = $pRow->status ?? '';
                $pRole = $pRow->current_approver_role ?? '';
                $canApv = auth()->user() && auth()->user()->canApprovePR($pRole);
            @endphp
            @if($pStatus != 'Approved' && $pStatus != 'Rejected' && $canApv)
            <a href="{{ route('pr.approve', $pr_number) }}" class="btn btn-success fw-bold px-3">
                <i class="fas fa-check me-1"></i> Approve
            </a>
            <a href="{{ route('pr.reject', $pr_number) }}" class="btn btn-danger fw-bold px-3" onclick="return confirm('Reject this PR?')">
                <i class="fas fa-times me-1"></i> Reject
            </a>
            @endif
            <a href="{{ route('pr.index') }}" class="btn btn-light fw-bold text-secondary border px-4">
                <i class="fas fa-times me-1"></i> Close
            </a>
            <a href="{{ route('pr.print', $pr_number) }}" target="_blank" class="btn btn-outline-dark fw-bold px-4">
                <i class="fas fa-print me-1"></i> Print PR
            </a>
            @if($is_editable)
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
            @endif
        </div>
    </div>
</div>
<!-- Spacer for fixed footer -->
<div style="height: 80px;"></div>

<script>
    function formatMoney(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }

    function formatPriceInput(input) {
        let value = input.value.replace(/\D/g, '');
        if (value === "") {
            input.value = "";
            return;
        }
        input.value = formatMoney(value);
    }

    function calculateRowTotal(element) {
        let row = element.closest('tr');
        let qty = row.querySelector('.qty-input').value || 0;
        let priceStr = row.querySelector('.price-input').value || "0";
        let price = parseInt(priceStr.replace(/\./g, '')) || 0;
        
        let total = qty * price;
        row.querySelector('.row-total').innerText = "IDR " + formatMoney(total);
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('#pr-items-table tbody tr').forEach(row => {
            let qty = row.querySelector('.qty-input')?.value || 0;
            let priceStr = row.querySelector('.price-input')?.value || "0";
            if(!row.querySelector('.qty-input')) {
                 return;
            }
            let price = parseInt(priceStr.replace(/\./g, '')) || 0;
            grandTotal += (qty * price);
        });
        document.getElementById('grand-total').innerText = "Rp " + formatMoney(grandTotal);
    }

    function deleteItem(btn, itemId) {
        if(confirm('Hapus item ini? Simpan perubahan untuk memproses.')) {
            let row = btn.closest('tr');
            if(itemId) {
                // Add to hidden deleted list
                let container = document.getElementById('deleted-items-container');
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleted_items[]';
                input.value = itemId;
                container.appendChild(input);
            }
            row.remove();
            calculateGrandTotal();
        }
    }

    function addItem() {
        const mMaster = document.getElementById('newItemMaster');
        const mBudget = document.getElementById('newItemBudget');
        const desc = document.getElementById('newItemDesc');
        const purpose = document.getElementById('newItemPurpose');
        const glAccount = document.getElementById('newItemGl');
        const aucField = document.getElementById('newItemAuc');
        const qty = document.getElementById('newItemQty');
        const uom = document.getElementById('newItemUom');
        const cost = document.getElementById('newItemCost');

        if (!desc.value && mMaster.value === '') {
            alert("Harap pilih Master Item atau masukkan Deskripsi.");
            return;
        }
        if (qty.value <= 0) {
            alert("Qty harus lebih besar dari 0.");
            return;
        }

        let displayDesc = desc.value;
        if(mMaster.value) {
            const masterText = mMaster.options[mMaster.selectedIndex].dataset.name;
            if(!displayDesc) displayDesc = masterText;
        }

        let itemCode = mMaster.value || '-';
        let q = parseFloat(qty.value);
        let cStr = cost.value.replace(/\./g, '');
        let c = parseFloat(cStr) || 0;
        let t = q * c;

        if(mBudget.value) {
            const selectedOption = mBudget.options[mBudget.selectedIndex];
            const remaining = parseFloat(selectedOption.dataset.remaining) || 0;
            if(t > remaining) {
                alert("Gagal menambahkan item: Total Biaya (IDR " + formatMoney(t) + ") melebihi sisa budget (IDR " + formatMoney(remaining) + ").");
                return;
            }
        }

        let tbody = document.querySelector('#pr-items-table tbody');
        let newId = 'new_' + Date.now();
        
        let row = `
            <tr data-item-id="${newId}">
                <td class="ps-4">
                    <div class="text-dark fw-bold small">${mBudget.value ? mBudget.options[mBudget.selectedIndex].text.split(' | ')[0] : '-'}</div>
                    <input type="hidden" name="items[${newId}][budget_item_id]" value="${mBudget.value}">
                </td>
                <td>
                    <input type="text" name="items[${newId}][gl_account]" class="form-control form-control-sm" value="${glAccount ? glAccount.value : ''}">
                </td>
                <td>
                    <input type="text" name="items[${newId}][asset_no]" class="form-control form-control-sm" value="${aucField ? aucField.value : ''}">
                </td>
                <td class="text-primary fw-medium small">
                    ${itemCode}
                </td>
                <td>
                    <input type="text" name="items[${newId}][description]" class="form-control form-control-sm" value="${displayDesc}">
                    ${mBudget.value ? '<div class="text-success small fst-italic mt-1"><i class="fas fa-check-circle me-1" style="font-size: 10px;"></i>A11 Linked</div>' : ''}
                </td>
                <td>
                    <input type="text" name="items[${newId}][purpose]" class="form-control form-control-sm" value="${purpose.value}">
                </td>
                <td>
                    <input type="text" name="items[${newId}][pic]" class="form-control form-control-sm" value="${document.getElementById('newItemPic').value}">
                </td>
                <td width="80px">
                    <input type="number" name="items[${newId}][qty]" class="form-control form-control-sm qty-input" value="${q}" oninput="calculateRowTotal(this)">
                </td>
                <td width="80px">
                    <input type="text" name="items[${newId}][uom]" class="form-control form-control-sm" value="${uom ? uom.value : 'Unit'}">
                </td>
                <td class="text-muted" width="150px">
                    <input type="text" name="items[${newId}][price]" class="form-control form-control-sm price-input" value="${formatMoney(c)}" oninput="formatPriceInput(this); calculateRowTotal(this)">
                </td>
                <td class="text-end pe-4 fw-bold text-dark row-total">IDR ${formatMoney(t)}</td>
                <td class="text-end pe-3">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="deleteItem(this, null)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', row);
        calculateGrandTotal();

        desc.value = '';
        purpose.value = '';
        if(glAccount) glAccount.value = '';
        if(aucField) aucField.value = '';
        qty.value = 1;
        cost.value = '';
        mMaster.value = '';
        mBudget.value = '';
    }    
        // Hide empty row msg if exists? We don't have one here but logic handles it.
    }

    // A11 Link Listener for new items
    document.getElementById('newItemBudget').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const p = selectedOption ? selectedOption.dataset.price : '';
        if(p) {
            document.getElementById('newItemCost').value = formatMoney(p);
        } else {
            document.getElementById('newItemCost').value = '';
        }
    });

    // A11 Link Listener for existing items
    function updatePriceFromBudget(select) {
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption ? selectedOption.dataset.price : '';
        const row = select.closest('tr');
        const priceInput = row.querySelector('.price-input');
        
        if (price && priceInput) {
            priceInput.value = formatMoney(price);
            calculateRowTotal(priceInput);
        }
    }

    // Filter budget links by selected IO Number
    const ioNumberSelect = document.querySelector('select[name="io_number"]');
    if (ioNumberSelect) {
        ioNumberSelect.addEventListener('change', function() {
            const selectedIo = this.value;
            
            // 1. Filter the "New Item" budget select
            const newBudgetSelect = document.getElementById('newItemBudget');
            if(newBudgetSelect) {
                newBudgetSelect.value = "";
                const costInput = document.getElementById('newItemCost');
                if(costInput) costInput.value = "";
                
                Array.from(newBudgetSelect.options).forEach(opt => {
                    if (opt.value === "" || opt.dataset.io === selectedIo) {
                        opt.hidden = false;
                        opt.disabled = false;
                    } else {
                        opt.hidden = true;
                        opt.disabled = true;
                    }
                });
            }

            // 2. Filter all "Existing Item" budget selects in the table
            document.querySelectorAll('.budget-link-select').forEach(select => {
                Array.from(select.options).forEach(opt => {
                    if (opt.value === "" || opt.dataset.io === selectedIo) {
                        opt.hidden = false;
                        opt.disabled = false;
                    } else {
                        opt.hidden = true;
                        opt.disabled = true;
                    }
                });
            });
        });
        
        // Initial trigger to hide unrelated items
        ioNumberSelect.dispatchEvent(new Event('change'));
    }
</script>
@if($is_editable)
</form>
@endif
@endsection
