@extends('layouts.app')

@php
    function formatRupiahTrend($amount)
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
    </style>

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a5f;">Monthly Spending Trend</h4>
            <p class="text-muted mb-0 small">Tren pengeluaran PR & PO per bulan.</p>
        </div>
        @include('analysis._filter_bar', ['filterAction' => route('analysis.monthly_trend'), 'showProjectFilter' => true])
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card-stat">
                <div class="text-label">Total PR Spending</div>
                <div class="text-value" style="color: #3b82f6;">{{ formatRupiahTrend($totalPR) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-stat">
                <div class="text-label">Total PO Spending</div>
                <div class="text-value" style="color: #0d6832;">{{ formatRupiahTrend($totalPO) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-stat">
                <div class="text-label">Avg Monthly PR</div>
                <div class="text-value">{{ formatRupiahTrend($avgMonthlyPR) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-stat">
                <div class="text-label">Peak Month</div>
                <div class="text-value">{{ $peakMonth }} {{ $selectedYear }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Chart --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Spending Trend {{ $selectedYear }}</h6>
                    <div style="height: 350px; position: relative;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaction Count Chart --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Transaction Volume</h6>
                    <div style="height: 280px; position: relative;">
                        <canvas id="countChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Breakdown Table --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <h6 class="fw-bold px-4 pt-3 pb-2 mb-0" style="color: #1e293b;">Monthly Breakdown</h6>
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-custom mb-0">
                            <thead style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Month</th>
                                    <th class="text-end">PR Amount</th>
                                    <th class="text-end">PO Amount</th>
                                    <th class="text-center">PR #</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($months as $i => $month)
                                    <tr>
                                        <td class="fw-bold">{{ $month }}</td>
                                        <td class="text-end text-nowrap" style="color: #3b82f6;">Rp
                                            {{ number_format($prData[$i], 0, ',', '.') }}
                                        </td>
                                        <td class="text-end text-nowrap" style="color: #0d6832;">Rp
                                            {{ number_format($poData[$i], 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"
                                                style="font-size: 0.75rem;">{{ $prCounts[$i] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
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
            const months = {!! json_encode($months) !!};
            const prData = {!! json_encode($prData) !!};
            const poData = {!! json_encode($poData) !!};
            const prCounts = {!! json_encode($prCounts) !!};
            const poCounts = {!! json_encode($poCounts) !!};

            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'PR Spending',
                            data: prData,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2.5,
                            pointBackgroundColor: '#3b82f6',
                            pointRadius: 4,
                            pointHoverRadius: 7,
                        },
                        {
                            label: 'PO Spending',
                            data: poData,
                            borderColor: '#0d6832',
                            backgroundColor: 'rgba(13,104,50,0.06)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2.5,
                            pointBackgroundColor: '#0d6832',
                            pointRadius: 4,
                            pointHoverRadius: 7,
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } },
                        tooltip: {
                            callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.raw.toLocaleString('id-ID') }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: { grid: { color: '#f1f5f9' }, ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt', font: { size: 10 } } }
                    }
                }
            });

            new Chart(document.getElementById('countChart'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        { label: 'PR Count', data: prCounts, backgroundColor: '#3b82f6', borderRadius: 4, barPercentage: 0.7 },
                        { label: 'PO Count', data: poCounts, backgroundColor: '#0d6832', borderRadius: 4, barPercentage: 0.7 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1, font: { size: 10 } } }
                    }
                }
            });
        });
    </script>
@endpush