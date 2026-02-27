@extends('layouts.app')

@php
    function formatRupiahAbsorption($amount)
    {
        if ($amount >= 1000000000) {
            return 'Rp ' . number_format($amount / 1000000000, 1, ',', '.') . ' M';
        } elseif ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . ' jt';
        } else {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
    }
@endphp

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

        .table-custom tbody tr:hover {
            background: #f8fafc;
        }

        .absorption-bar {
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .absorption-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a5f;">Budget Absorption Rate</h4>
            <p class="text-muted mb-0 small">Analisis tingkat penyerapan budget per Departemen & Customer.</p>
        </div>
        @include('analysis._filter_bar', ['filterAction' => route('analysis.budget_absorption')])
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-stat">
                <div class="text-label">Total Budget</div>
                <div class="text-value" style="color: #1e3a5f;">{{ formatRupiahAbsorption($grandBudget) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stat">
                <div class="text-label">Total Realisasi</div>
                <div class="text-value" style="color: #0d6832;">{{ formatRupiahAbsorption($grandRealization) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-stat">
                <div class="text-label">Rata-rata Absorption</div>
                <div class="text-value"
                    style="color: {{ $grandAbsorption > 100 ? '#dc2626' : ($grandAbsorption > 75 ? '#d97706' : '#1e3a5f') }};">
                    {{ number_format($grandAbsorption, 1) }}%
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Charts --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Absorption by Department</h6>
                    <div style="height: 300px; position: relative;">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Absorption by Customer</h6>
                    <div style="height: 300px; position: relative;">
                        <canvas id="custChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Department Table --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <h6 class="fw-bold px-4 pt-3 pb-2 mb-0" style="color: #1e293b;">Detail Absorption Rate</h6>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Department / Customer</th>
                                    <th class="text-end">Total Budget</th>
                                    <th class="text-end">Realisasi (PR)</th>
                                    <th class="text-end">Sisa</th>
                                    <th style="min-width: 180px;">Absorption</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($byDepartment as $dept)
                                    @php
                                        $barColor = $dept->absorption_rate > 100 ? '#dc2626' : ($dept->absorption_rate > 75 ? '#d97706' : '#3b82f6');
                                        $statusLabel = $dept->absorption_rate > 100 ? 'Over Budget' : ($dept->absorption_rate > 75 ? 'On Track' : ($dept->absorption_rate > 25 ? 'In Progress' : 'Low'));
                                        $statusClass = $dept->absorption_rate > 100 ? 'bg-danger' : ($dept->absorption_rate > 75 ? 'bg-warning' : ($dept->absorption_rate > 25 ? 'bg-primary' : 'bg-secondary'));
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">{{ $dept->department ?: '-' }}</td>
                                        <td class="text-end text-nowrap">Rp
                                            {{ number_format($dept->total_budget, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end text-nowrap"
                                            style="color: {{ $dept->total_realization > $dept->total_budget ? '#dc2626' : '#0d6832' }};">
                                            Rp {{ number_format($dept->total_realization, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end text-nowrap"
                                            style="color: {{ $dept->remaining < 0 ? '#dc2626' : '#334155' }};">
                                            Rp {{ number_format($dept->remaining, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="absorption-bar flex-grow-1">
                                                    <div class="absorption-fill"
                                                        style="width: {{ min(100, $dept->absorption_rate) }}%; background: {{ $barColor }};">
                                                    </div>
                                                </div>
                                                <span class="fw-bold"
                                                    style="font-size: 0.75rem; min-width: 40px; color: {{ $barColor }};">{{ number_format($dept->absorption_rate, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $statusClass }} text-white"
                                                style="padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem;">{{ $statusLabel }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No data available</td>
                                    </tr>
                                @endforelse
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deptLabels = {!! json_encode($byDepartment->pluck('department')->map(fn($d) => $d ?: 'N/A')) !!};
            const deptBudget = {!! json_encode($byDepartment->pluck('total_budget')) !!};
            const deptReal = {!! json_encode($byDepartment->pluck('total_realization')) !!};

            new Chart(document.getElementById('deptChart'), {
                type: 'bar',
                data: {
                    labels: deptLabels,
                    datasets: [
                        { label: 'Budget', data: deptBudget, backgroundColor: '#3b82f6', borderRadius: 4, barPercentage: 0.6 },
                        { label: 'Realisasi', data: deptReal, backgroundColor: '#0d6832', borderRadius: 4, barPercentage: 0.6 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } } },
                    scales: {
                        x: { grid: { color: '#f1f5f9' }, ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt', font: { size: 10 } } },
                        y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });

            const custLabels = {!! json_encode($byCustomer->pluck('customer_name')->map(fn($c) => $c ?: 'N/A')) !!};
            const custBudget = {!! json_encode($byCustomer->pluck('total_budget')) !!};
            const custReal = {!! json_encode($byCustomer->pluck('total_realization')) !!};

            new Chart(document.getElementById('custChart'), {
                type: 'bar',
                data: {
                    labels: custLabels,
                    datasets: [
                        { label: 'Budget', data: custBudget, backgroundColor: '#3b82f6', borderRadius: 4, barPercentage: 0.6 },
                        { label: 'Realisasi', data: custReal, backgroundColor: '#e67e22', borderRadius: 4, barPercentage: 0.6 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } } },
                    scales: {
                        x: { grid: { color: '#f1f5f9' }, ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt', font: { size: 10 } } },
                        y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        });
    </script>
@endpush