@extends('layouts.app')

@php
    // Helper function to format currency with jt (million) and M (billion)
    function formatRupiahDashboard($amount)
    {
        if ($amount >= 1000000000) {
            // Billions - use M (Milyar)
            return 'Rp ' . number_format($amount / 1000000000, 1, ',', '.') . ' M';
        } elseif ($amount >= 1000000) {
            // Millions - use jt (juta)
            return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . ' jt';
        } else {
            // Below million - show full number
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
    }
@endphp

@section('content')
    <style>
        /* Register custom property so browser can smoothly interpolate the angle */
        @property --reveal-angle {
            syntax: '<angle>';
            initial-value: 0deg;
            inherits: false;
        }

        .animate-slide-up {
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(40px);
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Pie chart clockwise reveal — buttery smooth with @property */
        .pie-clock-reveal {
            --reveal-angle: 0deg;
            -webkit-mask-image: conic-gradient(from -90deg, #000 var(--reveal-angle), transparent var(--reveal-angle));
            mask-image: conic-gradient(from -90deg, #000 var(--reveal-angle), transparent var(--reveal-angle));
            animation: clockReveal 2s cubic-bezier(0.4, 0, 0.2, 1) 0.5s forwards;
        }

        @keyframes clockReveal {
            from {
                --reveal-angle: 0deg;
            }

            to {
                --reveal-angle: 360deg;
            }
        }

        /* Staggered delays for a cascade effect */
        .delay-100 {
            animation-delay: 100ms;
        }

        .delay-200 {
            animation-delay: 200ms;
        }

        .delay-300 {
            animation-delay: 300ms;
        }

        .delay-400 {
            animation-delay: 400ms;
        }

        /* Filter controls - desktop defaults */
        .filter-select {
            height: 38px;
            min-width: 120px;
        }

        .filter-btn {
            height: 38px;
            white-space: nowrap;
        }

        select[name="customer"] {
            min-width: 150px;
        }

        select[name="category"] {
            min-width: 280px;
        }

        select[name="project_id"] {
            min-width: 160px;
            max-width: 220px;
        }

        .filter-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .stat-icon-circle {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
        }

        .pie-chart-wrapper {
            height: 280px;
        }

        /* === TABLET (sidebar hidden, content full-width) === */
        @media (max-width: 991.98px) {
            .dashboard-header {
                flex-direction: column;
                align-items: stretch !important;
                gap: 0.75rem;
            }

            .filter-container {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .filter-form {
                flex-wrap: wrap;
                width: 100%;
                gap: 0.5rem !important;
            }

            .filter-form .form-select,
            .filter-form .btn,
            .filter-form a {
                min-width: 0 !important;
                width: calc(50% - 0.25rem) !important;
                flex: 0 0 calc(50% - 0.25rem) !important;
                font-size: 0.8rem;
            }

            .filter-form select[name="customer"],
            .filter-form select[name="category"],
            .filter-form select[name="year"],
            .filter-form select[name="project_id"] {
                min-width: 0 !important;
                max-width: none !important;
            }

            .pie-chart-wrapper {
                height: 220px;
            }

            .stat-icon-circle {
                width: 50px;
                height: 50px;
            }
        }

        /* === MOBILE PHONE === */
        @media (max-width: 576px) {
            .dashboard-header {
                flex-direction: column;
                align-items: stretch !important;
                gap: 0.75rem;
                margin-bottom: 1rem !important;
            }

            .dashboard-header h4 {
                font-size: 1.15rem;
            }

            .dashboard-header p {
                font-size: 0.75rem;
            }

            .filter-container {
                width: 100%;
            }

            .filter-form {
                flex-direction: column;
                width: 100%;
                gap: 0.5rem !important;
            }

            .filter-form .form-select,
            .filter-form .btn,
            .filter-form a {
                width: 100% !important;
                min-width: 0 !important;
                flex: 0 0 100% !important;
                height: 36px !important;
                font-size: 0.8rem;
            }

            .filter-form select[name="customer"],
            .filter-form select[name="category"],
            .filter-form select[name="year"] {
                min-width: 0 !important;
                width: 100% !important;
            }

            /* Stats cards */
            .card-body h3 {
                font-size: 1.25rem !important;
                word-break: break-all;
            }

            .card-body {
                padding: 0.85rem !important;
            }

            /* Pie chart */
            .pie-chart-wrapper {
                height: 180px !important;
            }

            #pieChartLegend {
                gap: 0.35rem !important;
            }

            #pieChartLegend>div {
                font-size: 0.65rem !important;
            }

            /* Tables */
            .table-responsive {
                border: 0;
                margin: 0 -0.5rem;
            }

            .table thead th {
                font-size: 0.7rem;
                padding: 0.4rem 0.3rem !important;
                white-space: nowrap;
            }

            .table tbody td {
                font-size: 0.7rem;
                padding: 0.4rem 0.3rem !important;
            }

            /* Project list */
            .list-group-item {
                padding: 0.65rem 0.75rem !important;
                font-size: 0.85rem;
            }

            /* Progress bar text */
            .usage-stats .small {
                font-size: 0.7rem;
            }

            /* Icon circles on stat cards */
            .stat-icon-circle {
                width: 42px !important;
                height: 42px !important;
                padding: 0.5rem !important;
            }

            .stat-icon-circle i {
                font-size: 1rem !important;
            }

            /* Badge on stat card */
            .card-body .badge {
                font-size: 0.65rem !important;
                max-width: 160px;
            }

            /* Section titles */
            .card-body h5,
            .card-body h6 {
                font-size: 0.85rem !important;
            }

            /* Reorder: stats first, pie chart second on mobile */
            .section-pie {
                order: 2;
            }

            .section-stats {
                order: 1;
            }

            /* Project list shorter on mobile */
            .project-list-scroll {
                max-height: 250px !important;
            }

            /* Bar chart shorter on mobile */
            .chart-container {
                min-height: 180px !important;
            }

            /* Row gaps tighter on mobile */
            .row.g-3 {
                --bs-gutter-y: 0.75rem;
                --bs-gutter-x: 0.75rem;
            }
        }
    </style>

    <div class="d-flex justify-content-between align-items-end mb-4 dashboard-header">
        <div>
            <h4 class="fw-bold mb-1">Dashboard Overview</h4>
            <p class="text-muted mb-0 small">Financial Summary & Operational Status</p>
        </div>
        <div class="filter-container">
            <form action="{{ route('dashboard') }}" method="GET" id="filterForm" class="d-flex gap-2 filter-form">
                <a href="{{ route('dashboard') }}"
                    class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 d-flex align-items-center justify-content-center filter-btn"
                    style="background-color: #fff;" title="Reset Filters">
                    <i class="fas fa-sync-alt me-2 text-primary"></i> Default
                </a>

                <select name="year" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3 filter-select"
                    style="background-color: #fff; cursor: pointer;" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>

                <select name="customer"
                    class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3 filter-select"
                    style="background-color: #fff; cursor: pointer;" onchange="this.form.submit()">
                    <option value="">All Customers</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust }}" {{ $selectedCustomer == $cust ? 'selected' : '' }}>
                            {{ $cust }}
                        </option>
                    @endforeach
                </select>

                <select name="category"
                    class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3 filter-select"
                    style="background-color: #fff; cursor: pointer;" onchange="this.form.submit()">
                    @if($canSeeAllCategories)
                        <option value="">All Business Categories</option>
                    @endif
                    @foreach($businessCategories as $cat)
                        <option value="{{ $cat->category_name }}" {{ $selectedCategory == $cat->category_name ? 'selected' : '' }}>
                            {{ $cat->category_name }}
                        </option>
                    @endforeach
                </select>

                <select name="project_id"
                    class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3 filter-select"
                    style="background-color: #fff; cursor: pointer;" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($allProjects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                            {{ $proj->project_name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Section 1: Project List, Pie Chart & Stats Cards -->
    <div class="row g-3 mb-3">
        <!-- Project List Card -->
        <div class="col-lg-4 d-flex">
            <div class="card border-0 shadow-sm w-100 mb-0 animate-slide-up delay-100">
                <div class="card-body p-0 d-flex flex-column h-100">
                    <h5 class="fw-bold p-3 mb-0 pb-2" style="color: #1e293b;">
                        {{ $selectedCategory ?: 'Top' }} Project List
                    </h5>
                    <div class="list-group list-group-flush border-top flex-grow-1 overflow-auto project-list-scroll"
                        style="max-height: 400px;">
                        @forelse($topProjects as $project)
                            @php
                                $isActive = $selectedProject && $selectedProject->id == $project->id;
                                $targetUrl = $isActive
                                    ? route('dashboard', ['category' => $selectedCategory, 'year' => $selectedYear])
                                    : route('dashboard', ['project_id' => $project->id, 'category' => $selectedCategory, 'year' => $selectedYear]);
                            @endphp
                            <a href="{{ $targetUrl }}"
                                class="list-group-item list-group-item-action d-flex flex-column align-items-start px-4 py-3 {{ $isActive ? 'shadow-sm active-project' : '' }}"
                                style="{{ $isActive ? 'background-color: #f0fdfa; border-left: 4px solid #0d9488; z-index: 1;' : 'border-left: 4px solid transparent;' }}">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <div class="fw-bold {{ $isActive ? 'text-dark' : 'text-secondary' }}"
                                        style="font-size: 0.95rem; line-height: 1.2;">
                                        {{ $project->project_name }}
                                    </div>
                                    @php
                                        $pStatus = $project->budget_status ?? $project->status ?? 'Active';
                                        $statusClass = match ($pStatus) {
                                            'Approved' => 'bg-success',
                                            'Draft' => 'bg-secondary',
                                            'Rejected' => 'bg-danger',
                                            'Submitted' => 'bg-warning',
                                            default => 'bg-info'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill"
                                        style="font-size: 0.65rem; padding: 0.3em 0.6em;">{{ $pStatus }}</span>
                                </div>
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <span class="text-muted small">Total Budget:</span>
                                    <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                                        Rp {{ number_format($project->total_budget, 0, ',', '.') }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted">No projects found.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- All Projects Pie Chart -->
        <div class="col-lg-4 d-flex section-pie">
            <div class="card border-0 shadow-sm rounded-4 w-100 flex-grow-1 mb-0 animate-slide-up delay-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">
                        {{ $selectedCategory ?: 'All Projects' }} Budget Allocation
                    </h6>
                    <div class="pie-clock-reveal pie-chart-wrapper" style="position: relative; width: 100%;">
                        <canvas id="allProjectsPieChart"></canvas>
                    </div>
                    <!-- Static Legend (outside the animation mask) -->
                    <div id="pieChartLegend" class="mt-2 d-flex flex-wrap justify-content-center gap-1"></div>
                </div>
            </div>
        </div>

        <!-- Top Stats Cards -->
        <div class="col-lg-4 section-stats">
            <div class="row g-2 h-100">
                <!-- Total Budget Plan -->
                <div class="col-12 d-flex">
                    <div class="card border-0 shadow-sm rounded-4 w-100 flex-grow-1 animate-slide-up delay-200"
                        style="background-color: #21325b;">
                        <div class="card-body text-white d-flex justify-content-between align-items-center p-3">
                            @php
                                $showProjectStats = request()->filled('project_id');
                                $displayBudget = $showProjectStats ? $selectedProjectBudget : $summaryTotalBudget;
                                $displayRealization = $showProjectStats ? $selectedProjectRealization : $summaryTotalRealization;
                                $displayBadge = $showProjectStats ? ($selectedProject ? $selectedProject->project_name : 'Selected Project') : ($selectedCategory ?: 'All Business Categories');
                            @endphp
                            <div>
                                <p class="mb-1 opacity-75 small fw-bold text-uppercase ls-1">
                                    {{ $showProjectStats ? 'PROJECT BUDGET' : 'TOTAL BUDGET' }}
                                </p>
                                <h3 class="fw-bold mb-0">Rp {{ number_format($displayBudget, 0, ',', '.') }}</h3>
                                <div class="mt-2 text-truncate" style="max-width: 100%;">
                                    <span class="badge bg-white bg-opacity-10 rounded-pill fw-normal px-2 py-1"
                                        style="font-size: 0.75rem;">
                                        <i
                                            class="fas fa-{{ $showProjectStats ? 'project-diagram' : 'globe' }} me-1 text-info"></i>
                                        {{ $displayBadge }}
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-end">
                                    <span class="d-block opacity-75 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">Total Projects</span>
                                    <span class="fw-bold" style="font-size: 2rem;">{{ $totalProjectCount }}</span>
                                </div>
                                <div class="rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm stat-icon-circle"
                                    style="background: rgba(255,255,255,0.1);">
                                    <i class="fas fa-layer-group fa-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total PR Usage with Animation -->
                <div class="col-12 d-flex">
                    <div class="card border-0 shadow-sm rounded-4 w-100 flex-grow-1 animate-slide-up delay-300"
                        style="background-color: #1a7a72;">
                        <div class="card-body text-white p-3 d-flex flex-column justify-content-center">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <p class="mb-1 opacity-75 small fw-bold text-uppercase ls-1">
                                        {{ $showProjectStats ? 'PROJECT PR USAGE' : 'TOTAL PR USAGE' }}
                                    </p>
                                    <h3 class="fw-bold mb-0">Rp {{ number_format($displayRealization, 0, ',', '.') }}</h3>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-end">
                                        <span class="d-block opacity-75 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">Total PR</span>
                                        <span class="fw-bold" style="font-size: 2rem;">{{ $totalPrCount }}</span>
                                    </div>
                                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm stat-icon-circle"
                                        style="background: rgba(255,255,255,0.1);">
                                        <i class="fas fa-file-invoice fa-xl"></i>
                                    </div>
                                </div>
                            </div>

                            @php
                                $usagePercentage = $displayBudget > 0 ? ($displayRealization / $displayBudget) * 100 : 0;
                                $barColor = $usagePercentage > 100 ? '#ef4444' : '#fbbf24';
                            @endphp

                            <div class="usage-stats mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-bold">Budget Usage Progress</span>
                                    <span
                                        class="badge bg-white text-dark rounded-pill">{{ number_format($usagePercentage, 1) }}%</span>
                                </div>
                                <div class="progress rounded-pill shadow-inner"
                                    style="height: 10px; background: rgba(0,0,0,0.15);">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated rounded-pill"
                                        role="progressbar"
                                        style="width: 0%; transition: width 1.5s ease-in-out; background-color: {{ $barColor }};"
                                        id="usageProgressBar">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total PO Usage with Animation -->
                <div class="col-12 d-flex">
                    <div class="card border-0 shadow-sm rounded-4 w-100 flex-grow-1 animate-slide-up delay-400"
                        style="background-color: #7c3a1a;">
                        <div class="card-body text-white p-3 d-flex flex-column justify-content-center">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    @php
                                        $displayPoUsage = $showProjectStats ? $selectedProjectPoUsage : $summaryTotalPoUsage;
                                    @endphp
                                    <p class="mb-1 opacity-75 small fw-bold text-uppercase ls-1">
                                        {{ $showProjectStats ? 'PROJECT PO USAGE' : 'TOTAL PO USAGE' }}
                                    </p>
                                    <h3 class="fw-bold mb-0">Rp {{ number_format($displayPoUsage, 0, ',', '.') }}</h3>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-end">
                                        <span class="d-block opacity-75 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">Total PO</span>
                                        <span class="fw-bold" style="font-size: 2rem;">{{ $totalPoCount }}</span>
                                    </div>
                                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm stat-icon-circle"
                                        style="background: rgba(255,255,255,0.1);">
                                        <i class="fas fa-shopping-cart fa-xl"></i>
                                    </div>
                                </div>
                            </div>

                            @php
                                $poUsagePercentage = $displayRealization > 0 ? ($displayPoUsage / $displayRealization) * 100 : 0;
                                $poBarColor = $poUsagePercentage > 100 ? '#ef4444' : '#fbbf24';
                            @endphp

                            <div class="usage-stats mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-bold">PR Budget Usage Progress</span>
                                    <span
                                        class="badge bg-white text-dark rounded-pill">{{ number_format($poUsagePercentage, 1) }}%</span>
                                </div>
                                <div class="progress rounded-pill shadow-inner"
                                    style="height: 10px; background: rgba(0,0,0,0.15);">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated rounded-pill"
                                        role="progressbar"
                                        style="width: 0%; transition: width 1.5s ease-in-out; background-color: {{ $poBarColor }};"
                                        id="poUsageProgressBar">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(() => {
                    const progressBar = document.getElementById('usageProgressBar');
                    if (progressBar) {
                        progressBar.style.width = '{{ min(100, $usagePercentage) }}%';
                    }
                    const poProgressBar = document.getElementById('poUsageProgressBar');
                    if (poProgressBar) {
                        poProgressBar.style.width = '{{ min(100, $poUsagePercentage) }}%';
                    }
                }, 300);
            });
        </script>
    @endpush

    <!-- Section 2: Task Chart -->
    <div class="row g-3 mb-3">
        <!-- Task Bar Chart -->
        <div class="col-lg-12 d-flex">
            <div class="card border-0 shadow-sm w-100 mb-0 animate-slide-up delay-300">
                <div class="card-body p-3 d-flex flex-column">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Budget Allocation by Machine / Equipment</h6>
                    <div class="flex-grow-1 chart-container" style="min-height: 250px; position: relative; width: 100%;">
                        <canvas id="budgetTaskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Project Overview & Detail Table -->
    <div class="row g-3">
        <!-- Project Overview Card -->
        <div class="col-lg-4 d-flex">
            <div class="card border-0 shadow-sm w-100 mb-0 animate-slide-up delay-400">
                <div class="card-body p-3">
                    <h5 class="fw-bold mb-3" style="color: #1e293b;">Project Overview</h5>
                    @if($selectedProject)
                        <div class="mb-3 border-bottom pb-3">
                            <h5 class="fw-bold text-dark mb-0" style="line-height: 1.4; letter-spacing: -0.01em;">
                                {{ $selectedProject->project_name }}
                            </h5>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">Start Date</span>
                            <span
                                class="fw-bold">{{ $selectedProject->start_date ? \Carbon\Carbon::parse($selectedProject->start_date)->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">End Date</span>
                            <span
                                class="fw-bold">{{ $selectedProject->end_date ? \Carbon\Carbon::parse($selectedProject->end_date)->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="fw-bold text-dark">Total Budget</span>
                            <span class="fw-bold text-dark">Rp {{ number_format($selectedProjectBudget, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">Total PR Usage</span>
                            <span class="fw-bold text-nowrap"
                                style="color: {{ $selectedProjectRealization > $selectedProjectBudget ? '#dc3545' : ($selectedProjectRealization < $selectedProjectBudget ? '#198754' : '#1e293b') }};">Rp
                                {{ number_format($selectedProjectRealization, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Remaining Balance</span>
                            <span class="fw-bold text-nowrap"
                                style="color: {{ ($selectedProjectBudget - $selectedProjectRealization) < 0 ? '#dc3545' : (($selectedProjectBudget - $selectedProjectRealization) > 0 ? '#198754' : '#1e293b') }};">Rp
                                {{ number_format($selectedProjectBudget - $selectedProjectRealization, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <p class="text-muted text-center my-4">No project selected or available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Detail Table Card -->
        <div class="col-lg-8 d-flex">
            <div class="card border-0 shadow-sm w-100 mb-0 animate-slide-up delay-400">
                <div class="card-body p-0 d-flex flex-column">
                    <h6 class="fw-bold p-3 mb-0 pb-2" style="color: #1e293b;">
                        {{ $selectedProject ? 'Detail per Machine / Equipment' : ($selectedCategory ? 'Project Breakdown in ' . $selectedCategory : 'Budget Detail per Category') }}
                    </h6>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead style="background-color: #21325b; color: white;">
                                <tr>
                                    <th class="ps-4 py-3 border-0 rounded-start">
                                        {{ $selectedProject ? 'Machine / Equipment List' : ($selectedCategory ? 'Project Name' : 'Business Category') }}
                                    </th>
                                    <th class="text-center py-3 border-0">Total Projects</th>
                                    <th class="text-center py-3 border-0">Total PR</th>
                                    <th class="text-center py-3 border-0">Total PO</th>
                                    <th class="text-center py-3 border-0">Nominal Budget</th>
                                    <th class="text-center py-3 border-0">PR Usage</th>
                                    <th class="text-center py-3 border-0 d-none d-md-table-cell">Remaining</th>
                                    <th class="text-center py-3 border-0 d-none d-xl-table-cell">Percentage Used</th>
                                    <th class="pe-4 py-3 border-0 rounded-end d-none d-md-table-cell">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($selectedProject)
                                    @forelse($projectTasks as $task)
                                        <tr class="border-bottom">
                                            <td class="ps-4 py-3 fw-bold text-dark">{{ $task->task_name }}</td>
                                            <td class="text-center text-muted">-</td>
                                            <td class="text-center fw-semibold text-dark">{{ $task->pr_count ?? 0 }}</td>
                                            <td class="text-center fw-semibold text-dark">{{ $task->po_count ?? 0 }}</td>
                                            <td class="text-center fw-semibold text-dark text-nowrap">Rp
                                                {{ number_format($task->nominal_budget, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center fw-semibold text-nowrap"
                                                style="color: {{ $task->expenditure > $task->nominal_budget ? '#dc3545' : ($task->expenditure < $task->nominal_budget ? '#198754' : '#1e293b') }};">
                                                Rp {{ number_format($task->expenditure, 0, ',', '.') }}</td>
                                            <td class="text-center fw-semibold d-none d-md-table-cell"
                                                style="color: {{ $task->remaining < 0 ? '#dc3545' : ($task->remaining > 0 ? '#198754' : '#1e293b') }};">
                                                Rp {{ number_format($task->remaining, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center text-dark d-none d-xl-table-cell">
                                                {{ number_format($task->percentage_used, 2, ',', '.') }}%
                                            </td>
                                            <td class="pe-4 py-3 text-muted text-truncate d-none d-md-table-cell"
                                                style="max-width: 150px;">
                                                {{ $task->remarks }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-5">No machine / equipment found for
                                                this project
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                    @forelse($categorySummary as $summary)
                                        <tr class="border-bottom">
                                            <td class="ps-4 py-3 fw-bold text-dark">{{ $summary->item_name }}</td>
                                            <td class="text-center fw-semibold text-dark">{{ $summary->project_count ?? '-' }}</td>
                                            <td class="text-center fw-semibold text-dark">{{ $summary->pr_count ?? '-' }}</td>
                                            <td class="text-center fw-semibold text-dark">{{ $summary->po_count ?? '-' }}</td>
                                            <td class="text-center fw-semibold text-dark text-nowrap">Rp
                                                {{ number_format($summary->total_budget, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center fw-semibold text-nowrap"
                                                style="color: {{ $summary->total_realization > $summary->total_budget ? '#dc3545' : ($summary->total_realization < $summary->total_budget ? '#198754' : '#1e293b') }};">
                                                Rp {{ number_format($summary->total_realization, 0, ',', '.') }}</td>
                                            <td class="text-center fw-semibold d-none d-md-table-cell"
                                                style="color: {{ $summary->remaining < 0 ? '#dc3545' : ($summary->remaining > 0 ? '#198754' : '#1e293b') }};">
                                                Rp {{ number_format($summary->remaining, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center text-dark d-none d-xl-table-cell">
                                                {{ number_format($summary->percentage_used, 2, ',', '.') }}%
                                            </td>
                                            <td class="pe-4 py-3 text-muted d-none d-md-table-cell">
                                                <span class="badge bg-light text-secondary border">Summary View</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-5">No category data found.</td>
                                        </tr>
                                    @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Include chartjs-plugin-datalabels for value labels on bars -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // NOTE: Do NOT register ChartDataLabels globally!
            // Global registration breaks doughnut chart's native rotate animation.
            // Instead, we pass it as a local plugin to the bar chart only.

            const ctx = document.getElementById('budgetTaskChart').getContext('2d');

            const labels = {!! json_encode($chartData['labels']) !!};
            const budgetData = {!! json_encode($chartData['budget']) !!};
            const realizedData = {!! json_encode($chartData['realized']) !!};

            // Ensure we have at least some labels
            if (labels.length === 0) {
                labels.push('No Data');
                budgetData.push(0);
                realizedData.push(0);
            }

            // Convert nominal data to percentage for the chart
            const percentageBudgetData = budgetData.map(b => b > 0 ? 100 : 0);
            const percentageRealizedData = realizedData.map((r, i) => {
                const b = budgetData[i];
                return b > 0 ? (r / b) * 100 : 0;
            });

            // Custom formatter for datalabels
            const formatter = (value, context) => {
                if (value === 0 && context.datasetIndex === 1) return '0%';
                return Math.round(value) + '%';
            };

            new Chart(ctx, {
                type: 'bar',
                plugins: [ChartDataLabels], // Register datalabels LOCALLY for bar chart only
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Budget Plan',
                            data: percentageBudgetData,
                            backgroundColor: '#3b82f6',
                            borderWidth: 0,
                            barPercentage: 1.0,
                            categoryPercentage: 1.0,
                        },
                        {
                            label: 'PR Usage',
                            data: percentageRealizedData,
                            backgroundColor: function (context) {
                                const value = context.dataset.data[context.dataIndex];
                                return value > 100 ? '#ef4444' : '#10b981';
                            },
                            borderWidth: 0,
                            barPercentage: 1.0,
                            categoryPercentage: 1.0,
                        }
                    ]
                },
                options: {
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart',
                        y: {
                            from: 500
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: window.innerWidth < 576 ? 25 : 40,
                            bottom: 5
                        }
                    },
                    scales: {
                        x: {
                            stacked: false,
                            grid: { display: false },
                            ticks: {
                                font: { size: window.innerWidth < 576 ? 8 : 10, weight: 'bold' },
                                maxRotation: window.innerWidth < 576 ? 45 : 0,
                                minRotation: 0,
                                autoSkip: false,
                                callback: function (value, index) {
                                    const label = labels[index];
                                    if (window.innerWidth < 576 && label && label.length > 12) {
                                        return label.substring(0, 12) + '…';
                                    }
                                    return label;
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: Math.max(120, ...percentageRealizedData) + 10,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function (value) {
                                    return value + '%';
                                },
                                font: { size: 10 }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                padding: window.innerWidth < 576 ? 8 : 15,
                                font: { size: window.innerWidth < 576 ? 9 : 11, weight: 'bold' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': ' + context.raw.toFixed(1) + '%';
                                }
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#475569',
                            offset: 2,
                            font: {
                                weight: 'bold',
                                size: window.innerWidth < 576 ? 8 : 10
                            },
                            formatter: formatter,
                            textAlign: 'center'
                        }
                    }
                }
            });

            // --- All Projects Pie (Doughnut) Chart ---
            // Visual animation is handled by CSS class "pie-clock-reveal" (conic-gradient mask)
            const pieCtx = document.getElementById('allProjectsPieChart').getContext('2d');
            const pieChartRaw = {!! json_encode($pieChartData) !!};
            const pieLabels = pieChartRaw.map(item => item.label);
            const pieData = pieChartRaw.map(item => Number(item.budget) || 0);
            const pieRealization = pieChartRaw.map(item => Number(item.realization) || 0);
            const totalProjectBudget = pieData.reduce((a, b) => a + b, 0);

            // Center Text Plugin
            const centerTextPlugin = {
                id: 'centerText',
                afterDraw: (chart) => {
                    if (chart.config.type !== 'doughnut') return;
                    const { ctx, chartArea: { left, top, right, bottom } } = chart;
                    ctx.save();
                    const centerX = (left + right) / 2;
                    const centerY = (top + bottom) / 2;

                    ctx.font = 'bold 12px Inter, system-ui, sans-serif';
                    ctx.fillStyle = '#64748b';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('TOTAL BUDGET', centerX, centerY - 15);

                    ctx.font = 'bold 18px Inter, system-ui, sans-serif';
                    ctx.fillStyle = '#1e293b';

                    let displayTotal = '';
                    if (totalProjectBudget >= 1000000000) {
                        displayTotal = 'Rp ' + (totalProjectBudget / 1000000000).toFixed(1) + ' M';
                    } else if (totalProjectBudget >= 1000000) {
                        displayTotal = 'Rp ' + (totalProjectBudget / 1000000).toFixed(1) + ' jt';
                    } else {
                        displayTotal = 'Rp ' + totalProjectBudget.toLocaleString('id-ID');
                    }

                    ctx.fillText(displayTotal, centerX, centerY + 10);
                    ctx.restore();
                }
            };

            new Chart(pieCtx, {
                type: 'doughnut',
                plugins: [centerTextPlugin],
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: [
                            '#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed',
                            '#db2777', '#0891b2', '#4f46e5', '#ea580c', '#65a30d',
                            '#9333ea', '#0284c7'
                        ],
                        borderWidth: 0,
                        hoverOffset: 25,
                        borderRadius: 6,
                        spacing: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    animation: false, // Disabled — CSS handles the clock reveal
                    plugins: {
                        legend: {
                            display: false // Hide internal legend to use external static HTML legend
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: true,
                            callbacks: {
                                label: function (context) {
                                    const value = context.raw;
                                    const index = context.dataIndex;
                                    const realization = pieRealization[index] || 0;
                                    const percentage = totalProjectBudget > 0 ? ((value / totalProjectBudget) * 100).toFixed(1) + '%' : '0%';

                                    return [
                                        ' Budget: Rp ' + value.toLocaleString('id-ID') + ' (' + percentage + ')',
                                        ' Usage: Rp ' + realization.toLocaleString('id-ID')
                                    ];
                                }
                            }
                        },
                        datalabels: {
                            display: false
                        }
                    }
                }
            });

            // Populate External Static Legend
            const legendContainer = document.getElementById('pieChartLegend');
            const colors = [
                '#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed',
                '#db2777', '#0891b2', '#4f46e5', '#ea580c', '#65a30d',
                '#9333ea', '#0284c7'
            ];

            pieLabels.forEach((label, i) => {
                const value = pieData[i];
                const realization = pieRealization[i];
                const percentage = totalProjectBudget > 0 ? ((value / totalProjectBudget) * 100).toFixed(1) + '%' : '0%';

                const legendItem = document.createElement('div');
                legendItem.className = 'd-flex align-items-center me-3 mb-1';
                legendItem.style.fontSize = '11px';
                legendItem.style.fontWeight = '500';
                legendItem.style.color = '#475569';

                legendItem.innerHTML = `
                                    <span style="width: 10px; height: 10px; background-color: ${colors[i % colors.length]}; border-radius: 50%; display: inline-block; margin-right: 6px;"></span>
                                    <span>${label} <span class="text-muted" style="font-size: 0.65rem;">(${percentage})</span></span>
                                `;
                legendContainer.appendChild(legendItem);
            });
        });
    </script>
@endpush