@extends('layouts.app')

@section('content')
    <style>
        .eval-index-header {
            color: #1e3a5f;
            margin-bottom: 0.25rem;
        }

        /* === Level 1: Customer folder === */
        .customer-group {
            margin-bottom: 1rem;
        }

        .customer-folder-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: #f0f4fa;
            border: 1px solid #d5dde8;
            border-radius: 6px 6px 0 0;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s;
        }

        .customer-folder-header:hover {
            background: #e3eaf4;
        }

        .customer-folder-header .folder-icon {
            color: #e6a817;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .customer-folder-header .customer-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #0a1628;
            flex-grow: 1;
        }

        .customer-folder-header .badge-count {
            background: #1a3a6c;
            color: #fff;
            font-size: 0.7rem;
            padding: 3px 10px;
            border-radius: 10px;
            font-weight: 600;
        }

        .chevron {
            color: #64748b;
            font-size: 0.75rem;
            transition: transform 0.25s ease;
        }

        .collapsed .chevron {
            transform: rotate(-90deg);
        }

        /* Customer children wrapper */
        .customer-children {
            border: 1px solid #d5dde8;
            border-top: none;
            border-radius: 0 0 6px 6px;
            overflow: hidden;
        }

        .customer-children.hidden-list {
            display: none;
        }

        /* === Level 2: Model folder === */
        .model-folder-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px 9px 36px;
            background: #f8fafc;
            border-bottom: 1px solid #edf0f5;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s;
        }

        .model-folder-header:hover {
            background: #eef3fa;
        }

        .model-folder-header .folder-icon {
            color: #e6a817;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .model-folder-header .model-name {
            font-weight: 600;
            font-size: 0.88rem;
            color: #1e3a5f;
            flex-grow: 1;
        }

        .model-folder-header .badge-count {
            background: #64748b;
            color: #fff;
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* Model children (budget plan items) */
        .model-children {
            overflow: hidden;
        }

        .model-children.hidden-list {
            display: none;
        }

        /* === Level 3: Budget Plan items === */
        .plan-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px 11px 64px;
            border-bottom: 1px solid #edf0f5;
            background: #fff;
            text-decoration: none;
            color: inherit;
            transition: background 0.12s;
        }

        .plan-item:last-child {
            border-bottom: none;
        }

        .plan-item:hover {
            background: #f5f8ff;
            text-decoration: none;
            color: inherit;
        }

        .plan-item .file-icon {
            color: #3b82f6;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .plan-item .plan-info {
            flex-grow: 1;
        }

        .plan-item .plan-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e3a5f;
            margin-bottom: 2px;
        }

        .plan-item .plan-meta {
            font-size: 0.73rem;
            color: #888;
        }

        .plan-item .plan-stats {
            text-align: right;
            flex-shrink: 0;
        }

        .plan-item .plan-budget {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1a3a6c;
            font-family: 'Consolas', monospace;
        }

        .plan-item .plan-utilization {
            font-size: 0.7rem;
            margin-top: 2px;
        }

        .plan-item .plan-arrow {
            color: #c0c9d6;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        /* Progress mini bar */
        .mini-progress {
            width: 80px;
            height: 5px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-right: 6px;
        }

        .mini-progress-bar {
            height: 100%;
            border-radius: 3px;
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="fw-bold eval-index-header">Evaluasi Budget vs PR</h4>
            <p class="text-muted mb-0 small">Pilih customer dan model untuk melihat evaluasi budget.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Folder View Content -->
    @if(count($customerGroups) > 0)
        @foreach($customerGroups as $customerName => $modelGroups)
            @php
                $totalPlans = $modelGroups->flatten(1)->count();
            @endphp
            <div class="customer-group">
                <!-- Level 1: Customer Folder -->
                <div class="customer-folder-header collapsed" onclick="toggleFolder(this)">
                    <i class="fas fa-chevron-down chevron"></i>
                    <i class="fas fa-folder folder-icon"></i>
                    <span class="customer-name">{{ $customerName }}</span>
                    <span class="badge-count">{{ $totalPlans }} Budget Plan{{ $totalPlans > 1 ? 's' : '' }}</span>
                </div>

                <!-- Customer Children -->
                <div class="customer-children hidden-list">
                    @foreach($modelGroups as $modelName => $plans)
                        <!-- Level 2: Model Folder -->
                        <div class="model-folder-header collapsed" onclick="toggleFolder(this)">
                            <i class="fas fa-chevron-down chevron"></i>
                            <i class="fas fa-folder folder-icon"></i>
                            <span class="model-name">{{ $modelName }}</span>
                            <span class="badge-count">{{ count($plans) }}</span>
                        </div>

                        <!-- Model Children (Budget Plans) -->
                        <div class="model-children hidden-list">
                            @foreach($plans as $plan)
                                @php
                                    $utilization = $plan->total_budget > 0 ? ($plan->total_realized / $plan->total_budget) * 100 : 0;
                                    $progressColor = $utilization >= 90 ? '#0d6832' : ($utilization >= 50 ? '#e67e22' : '#3b82f6');
                                @endphp
                                <!-- Level 3: Budget Plan File -->
                                <a href="{{ route('analysis.budget_evaluation.detail', $plan->plan_id) }}" class="plan-item">
                                    <i class="fas fa-file-invoice file-icon"></i>
                                    <div class="plan-info">
                                        <div class="plan-title">
                                            BP-{{ $plan->fiscal_year }}-{{ str_pad($plan->plan_id, 3, '0', STR_PAD_LEFT) }}
                                            <span
                                                class="badge bg-{{ $plan->status == 'Approved' ? 'success' : ($plan->status == 'Submitted' ? 'warning' : 'secondary') }} ms-2"
                                                style="font-size: 0.65rem;">
                                                {{ $plan->status }}
                                            </span>
                                        </div>
                                        <div class="plan-meta">
                                            <i class="fas fa-project-diagram me-1" style="font-size:.65rem;"></i>{{ $plan->project_code }} •
                                            {{ $plan->project_name }}
                                            <span class="mx-1">|</span>
                                            <i class="fas fa-boxes me-1" style="font-size:.65rem;"></i>{{ $plan->item_count }}
                                            item(s)
                                        </div>
                                    </div>
                                    <div class="plan-stats">
                                        <div class="plan-budget">Rp {{ number_format($plan->total_budget, 0, ',', '.') }}</div>
                                        <div class="plan-utilization">
                                            <span class="mini-progress">
                                                <span class="mini-progress-bar"
                                                    style="width:{{ min($utilization, 100) }}%;background:{{ $progressColor }};"></span>
                                            </span>
                                            <span style="color:{{ $progressColor }};font-weight:600;">{{ round($utilization) }}%</span>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right plan-arrow"></i>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
            <p>Belum ada Budget Plan yang disetujui (Approved) untuk dievaluasi.</p>
        </div>
    @endif

    <script>
        function toggleFolder(header) {
            const isCollapsed = header.classList.toggle('collapsed');
            const folderIcon = header.querySelector('.folder-icon');
            const childrenDiv = header.nextElementSibling;

            if (isCollapsed) {
                folderIcon.classList.remove('fa-folder-open');
                folderIcon.classList.add('fa-folder');
                childrenDiv.classList.add('hidden-list');
            } else {
                folderIcon.classList.remove('fa-folder');
                folderIcon.classList.add('fa-folder-open');
                childrenDiv.classList.remove('hidden-list');
            }
        }
    </script>
@endsection