@extends('layouts.app')

@section('content')
    <style>
        .card-stat {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .text-label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .text-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 5px;
        }

        /* === Folder styles (matching budget_evaluation_index) === */
        .ba-group {
            margin-bottom: 1rem;
        }

        .ba-folder-header {
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

        .ba-folder-header:hover {
            background: #e3eaf4;
        }

        .ba-folder-header .folder-icon {
            color: #e6a817;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .ba-folder-header .folder-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #0a1628;
            flex-grow: 1;
        }

        .ba-folder-header .badge-count {
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

        .ba-children {
            border: 1px solid #d5dde8;
            border-top: none;
            border-radius: 0 0 6px 6px;
            overflow: hidden;
        }

        .ba-children.hidden-list {
            display: none;
        }

        /* === File items (matching plan-item style) === */
        .ba-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px 11px 36px;
            border-bottom: 1px solid #edf0f5;
            background: #fff;
            transition: background 0.12s;
        }

        .ba-item:last-child {
            border-bottom: none;
        }

        .ba-item:hover {
            background: #f5f8ff;
        }

        .ba-item .file-icon {
            color: #dc2626;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .ba-item .item-info {
            flex-grow: 1;
            min-width: 0;
        }

        .ba-item .item-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e3a5f;
            margin-bottom: 2px;
        }

        .ba-item .item-meta {
            font-size: 0.73rem;
            color: #888;
        }

        .ba-item .item-flow {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.73rem;
            color: #475569;
            margin-top: 2px;
        }

        .ba-item .item-flow .flow-arrow {
            color: #e6a817;
            font-weight: 700;
        }

        .ba-item .item-actions {
            flex-shrink: 0;
        }

        /* Filter bar (matching _filter_bar pill style) */
        .ba-filter-container {
            flex-shrink: 0;
        }

        .ba-filter-form {
            flex-wrap: nowrap !important;
        }

        @media (max-width: 991.98px) {
            .ba-header-section {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.75rem;
            }

            .ba-filter-container {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .ba-filter-form {
                flex-wrap: nowrap !important;
                width: max-content;
                gap: 0.5rem !important;
            }

            .ba-filter-form .form-select,
            .ba-filter-form .btn,
            .ba-filter-form a {
                font-size: 0.8rem;
            }
        }
    </style>

    {{-- Header + Filters --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2 ba-header-section">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a5f;">Berita Acara</h4>
            <p class="text-muted mb-0 small">Dokumen berita acara transfer item antar budget plan.</p>
        </div>

        {{-- Filter Bar (matching existing _filter_bar style) --}}
        <div class="ba-filter-container">
            <form action="{{ route('analysis.berita_acara') }}" method="GET" class="d-flex gap-2 ba-filter-form">
                <a href="{{ route('analysis.berita_acara') }}"
                    class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 d-flex align-items-center justify-content-center"
                    style="background-color: #fff; height: 38px; white-space: nowrap; flex-shrink: 0;" title="Reset Filters">
                    <i class="fas fa-sync-alt me-2 text-primary"></i> Default
                </a>

                <select name="year" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
                    style="background-color: #fff; cursor: pointer; height: 38px; min-width: 100px; max-width: 130px;"
                    onchange="this.form.submit()">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>

                <select name="customer" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
                    style="background-color: #fff; cursor: pointer; height: 38px; min-width: 150px; max-width: 200px;"
                    onchange="this.form.submit()">
                    <option value="">All Customers</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust }}" {{ $selectedCustomer == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                    @endforeach
                </select>

                <select name="category" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
                    style="background-color: #fff; cursor: pointer; height: 38px; min-width: 200px; max-width: 280px;"
                    onchange="this.form.submit()">
                    <option value="">All Business Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $selectedCategory == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>

                <select name="project" class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3"
                    style="background-color: #fff; cursor: pointer; height: 38px; min-width: 150px; max-width: 240px;"
                    onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj }}" {{ $selectedProject == $proj ? 'selected' : '' }}>{{ $proj }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card-stat">
                <div class="text-label">Total Documents</div>
                <div class="text-value" style="color: #1e3a5f;">{{ $totalTransfers }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stat">
                <div class="text-label">Fiscal Years</div>
                <div class="text-value" style="color: #1e3a5f;">{{ $totalYears }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stat">
                <div class="text-label">Customer</div>
                <div class="text-value" style="color: #0d6832;">{{ $transfers->pluck('customer')->filter()->unique()->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stat">
                <div class="text-label">Project</div>
                <div class="text-value" style="color: #3b82f6;">{{ $transfers->pluck('source_project_name')->merge($transfers->pluck('target_project_name'))->filter()->unique()->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Folder View --}}
    @if($transfers->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
            <p>Belum ada dokumen berita acara.</p>
            <small>Dokumen akan muncul setelah ada transfer item budget.</small>
        </div>
    @else
        @foreach($groupedTransfers->sortKeysDesc() as $year => $yearTransfers)
            <div class="ba-group">
                {{-- Folder Header --}}
                <div class="ba-folder-header" onclick="toggleFolder(this)">
                    <i class="fas fa-chevron-down chevron"></i>
                    <i class="fas fa-folder-open folder-icon"></i>
                    <span class="folder-name">Tahun Fiskal {{ $year ?? 'Tidak Diketahui' }}</span>
                    <span class="badge-count">{{ $yearTransfers->count() }} dokumen</span>
                </div>

                {{-- Folder Children --}}
                <div class="ba-children">
                    @foreach($yearTransfers as $transfer)
                        <div class="ba-item">
                            <i class="fas fa-file-pdf file-icon"></i>
                            <div class="item-info">
                                <div class="item-title">
                                    {{ $transfer->item_name }}
                                    @if($transfer->customer)
                                        <span class="badge bg-secondary ms-2" style="font-size: 0.65rem;">{{ $transfer->customer }}</span>
                                    @endif
                                </div>
                                <div class="item-flow">
                                    <i class="fas fa-sign-out-alt text-danger"></i>
                                    <span>{{ $transfer->source_project_name ?? '-' }}</span>
                                    <small class="text-muted">({{ $transfer->source_io_number ?? '-' }})</small>
                                    <span class="flow-arrow"><i class="fas fa-long-arrow-alt-right"></i></span>
                                    <i class="fas fa-sign-in-alt text-success"></i>
                                    <span>{{ $transfer->target_project_name ?? '-' }}</span>
                                    <small class="text-muted">({{ $transfer->target_io_number ?? '-' }})</small>
                                </div>
                                <div class="item-meta">
                                    <i class="fas fa-comment-alt me-1" style="font-size:.65rem;"></i>{{ Str::limit($transfer->reason, 50) }}
                                    <span class="mx-1">|</span>
                                    <i class="far fa-user me-1" style="font-size:.65rem;"></i>{{ $transfer->user_name ?? 'System' }}
                                    <span class="mx-1">|</span>
                                    <i class="far fa-clock me-1" style="font-size:.65rem;"></i>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $transfer->berita_acara_path) }}" target="_blank"
                                class="btn btn-sm btn-outline-danger" title="Download PDF"
                                style="border-radius: 6px; font-weight: 500; font-size: 0.75rem;">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
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