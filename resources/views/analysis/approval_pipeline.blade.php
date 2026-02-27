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

        .funnel-step {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .funnel-label {
            min-width: 140px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #334155;
        }

        .funnel-bar-bg {
            flex-grow: 1;
            height: 28px;
            border-radius: 6px;
            background: #f1f5f9;
            overflow: hidden;
        }

        .funnel-bar-fill {
            height: 100%;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 0.75rem;
            min-width: 40px;
            transition: width 1.5s ease;
        }

        .funnel-count {
            min-width: 40px;
            text-align: right;
            font-weight: 700;
            font-size: 0.85rem;
            color: #1e293b;
        }

        .stage-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            padding: 1.25rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .pending-item {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .pending-item:last-child {
            border-bottom: none;
        }

        .table-custom thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem;
        }

        .table-custom tbody td {
            padding: 1rem;
            vertical-align: middle;
            font-size: 0.9rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a5f;">Approval Pipeline & Bottleneck</h4>
            <p class="text-muted mb-0 small">Analisis alur approval & identifikasi hambatan proses.</p>
        </div>
        @include('analysis._filter_bar', ['filterAction' => route('analysis.approval_pipeline')])
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card-stat">
                <div class="text-label">Total PR</div>
                <div class="text-value">
                    {{ ($prStatuses->get('Submitted')->count ?? 0) + ($prStatuses->get('Approved')->count ?? 0) + ($prStatuses->get('Rejected')->count ?? 0) }}
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-stat">
                <div class="text-label">Approved</div>
                <div class="text-value" style="color: #0d6832;">{{ $prStatuses->get('Approved')->count ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-stat">
                <div class="text-label">In Process</div>
                <div class="text-value" style="color: #e67e22;">{{ $prStatuses->get('Submitted')->count ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-stat">
                <div class="text-label">Rejected</div>
                <div class="text-value" style="color: #dc2626;">{{ $rejected }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Approval Funnel --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Approval Funnel</h6>
                    @php
                        $maxCount = collect($funnelData)->max('count') ?: 1;
                        $funnelColors = ['#3b82f6', '#8b5cf6', '#e67e22', '#1a3a6c', '#0d6832'];
                    @endphp
                    @foreach($funnelData as $i => $step)
                        <div class="funnel-step">
                            <div class="funnel-label">{{ $step['stage'] }}</div>
                            <div class="funnel-bar-bg">
                                <div class="funnel-bar-fill"
                                    style="width: {{ $maxCount > 0 ? ($step['count'] / $maxCount) * 100 : 0 }}%; background: {{ $funnelColors[$i] }};">
                                    {{ $step['count'] > 0 ? $step['count'] : '' }}
                                </div>
                            </div>
                            <div class="funnel-count">{{ $step['count'] }}</div>
                        </div>
                    @endforeach
                    @if($rejected > 0)
                        <div class="funnel-step">
                            <div class="funnel-label" style="color: #dc2626;">Rejected</div>
                            <div class="funnel-bar-bg">
                                <div class="funnel-bar-fill"
                                    style="width: {{ $maxCount > 0 ? ($rejected / $maxCount) * 100 : 0 }}%; background: #dc2626;">
                                    {{ $rejected }}
                                </div>
                            </div>
                            <div class="funnel-count" style="color: #dc2626;">{{ $rejected }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Average Approval Time --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color: #1e293b;">Avg. Approval Time per Stage</h6>
                        <span class="badge bg-light text-dark border fw-bold px-3 py-2" style="font-size: 0.8rem;">
                            Total: {{ $totalAvgDays }} hari
                        </span>
                    </div>
                    <div class="row g-3">
                        @php
                            $stageIcons = ['Dept Head' => 'fa-user-tie', 'Finance' => 'fa-calculator', 'Division Head' => 'fa-user-shield', 'Purchasing' => 'fa-shopping-bag'];
                        @endphp
                        @foreach($avgTimes as $stage => $days)
                            <div class="col-6">
                                <div class="stage-card">
                                    <i class="fas {{ $stageIcons[$stage] ?? 'fa-check' }} mb-2"
                                        style="font-size: 1.2rem; color: #1a3a6c;"></i>
                                    <div class="fw-bold" style="font-size: 0.8rem; color: #334155;">{{ $stage }}</div>
                                    <div class="fw-bold mt-1"
                                        style="font-size: 1.5rem; color: {{ $days > 5 ? '#dc2626' : ($days > 2 ? '#e67e22' : '#0d6832') }};">
                                        {{ $days }}
                                        <span class="text-muted" style="font-size: 0.7rem;">hari</span>
                                    </div>
                                    @if($days > 5)
                                        <span class="badge bg-danger text-white mt-1" style="font-size: 0.6rem;">⚠ Bottleneck</span>
                                    @elseif($days > 2)
                                        <span class="badge bg-warning text-white mt-1" style="font-size: 0.6rem;">⏳ Moderate</span>
                                    @else
                                        <span class="badge bg-success text-white mt-1" style="font-size: 0.6rem;">✓ Fast</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Items --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Oldest Pending PRs (Bottleneck)</h6>
                    @forelse($pendingPRs as $pr)
                        <div class="pending-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold" style="font-size: 0.85rem; color: #1e3a5f;">{{ $pr->pr_number }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ Str::limit($pr->purpose, 40) }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning text-white fw-bold px-2 py-1"
                                        style="font-size: 0.65rem; border-radius: 6px;">
                                        {{ $pr->current_approver_role }}
                                    </span>
                                    <div class="text-muted mt-1" style="font-size: 0.65rem;">
                                        {{ \Carbon\Carbon::parse($pr->created_at)->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">Tidak ada PR pending</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Budget Plan Status --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Budget Plan Status Distribution</h6>
                    <div style="height: 280px; position: relative;">
                        <canvas id="bpStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bpData = {!! json_encode($bpStatuses->map(fn($s) => $s->count)) !!};
            const labels = Object.keys(bpData);
            const values = Object.values(bpData);
            const colorMap = { 'Draft': '#94a3b8', 'Submitted': '#e67e22', 'Approved': '#0d6832', 'Rejected': '#dc2626' };
            const colors = labels.map(l => colorMap[l] || '#3b82f6');

            new Chart(document.getElementById('bpStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: values, backgroundColor: colors, borderWidth: 0, hoverOffset: 10 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 11 } } }
                    }
                }
            });
        });
    </script>
@endpush