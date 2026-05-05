@extends('layouts.admin', ['title' => 'Reports'])

@section('title', 'Reports')

@section('content')
    @php
        $periodKey = request('period', '6months');
        $reportIncomeLabels = collect($monthlyIncome)->pluck('month')->values()->all();
        $reportIncomeAmounts = collect($monthlyIncome)->pluck('amount')->map(fn ($v) => (float) $v)->values()->all();
        $reportIncomeCounts = collect($monthlyIncome)->pluck('payments_count')->values()->all();
        $propertyChartLabels = $propertyBreakdown->pluck('name')->values()->all();
        $propertyChartValues = $propertyBreakdown->pluck('total_collected')->map(fn ($v) => (float) $v)->values()->all();
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div class="flex items-center gap-2">
                <div class="h-8 w-1 rounded-full bg-indigo-500"></div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reports</h1>
                    <p class="text-sm text-slate-400 mt-0.5">Analytics and insights for your portfolio</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.reports.export.excel') . '?' . http_build_query(request()->all()) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 active:scale-[0.98] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125V3.375m17.25 0v.75a3 3 0 0 1-3 3h-15a3 3 0 0 1-3-3v-.75m16.5 0h1.5m-1.5 0h-9m9 0h-.008v.008h-.008V12Zm-9 4.5h6" />
                    </svg>
                    Export Excel
                </a>
                <a href="{{ route('admin.reports.export.pdf') . '?' . http_build_query(request()->all()) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 active:scale-[0.98] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="rounded-2xl bg-white p-4 ring-1 ring-slate-100 mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Quick Select</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach([
                            '30days' => 'Last 30 Days',
                            '3months' => 'Last 3 Months',
                            '6months' => 'Last 6 Months',
                            '12months' => 'Last 12 Months',
                        ] as $value => $label)
                            <button type="submit" name="period" value="{{ $value }}"
                                class="rounded-xl px-3 py-2 text-sm font-semibold transition-all
                                       {{ $periodKey === $value
                                          ? 'bg-indigo-600 text-white shadow-sm'
                                          : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-end gap-2 flex-1 flex-wrap">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">From</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">To</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                    </div>
                    <button type="submit" name="period" value="custom"
                        class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition-all">
                        Apply
                    </button>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <p class="text-sm font-medium text-slate-500">Total Collected</p>
                <p class="mt-2 text-2xl font-bold text-indigo-700">₱{{ number_format($totalCollected, 2) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <p class="text-sm font-medium text-slate-500">Total Units</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalUnits) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <p class="text-sm font-medium text-slate-500">Occupancy Rate</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $occupancyRate }}%</p>
            </div>
        </div>

        {{-- Monthly income --}}
        <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100 mt-6">
            <h2 class="text-base font-semibold text-slate-900">Monthly Rent Collected</h2>
            <p class="text-sm text-slate-400 mt-0.5 mb-6">Paid rent recorded in each month</p>
            <div class="relative h-72 mb-8">
                <canvas id="reportIncomeChart"></canvas>
            </div>
            <div class="overflow-x-auto rounded-xl ring-1 ring-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Month</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Amount Collected</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600"># Payments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @foreach($monthlyIncome as $row)
                            <tr>
                                <td class="px-4 py-3 text-slate-800">{{ $row['month'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-indigo-700">₱{{ number_format($row['amount'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">{{ $row['payments_count'] }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-indigo-50/80 font-bold text-indigo-900">
                            <td class="px-4 py-3">Total</td>
                            <td class="px-4 py-3 text-right">₱{{ number_format($totalCollected, 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ array_sum($reportIncomeCounts) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @isset($paymentStatusSummary)
            <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100 mt-6">
                <h2 class="text-base font-semibold text-slate-900">Payment Status Summary</h2>
                <p class="text-sm text-slate-400 mt-0.5 mb-4">Counts by status for payments with due dates in the selected range</p>
                @php
                    $psLabels = [
                        'pending' => 'Pending',
                        'verifying' => 'Verifying',
                        'verifying_late' => 'Verifying (Late)',
                        'paid' => 'Paid',
                        'late' => 'Late',
                        'rejected' => 'Rejected',
                    ];
                @endphp
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach($psLabels as $key => $label)
                        <div class="rounded-xl bg-slate-50 ring-1 ring-slate-100 p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
                            <p class="mt-1 text-xl font-bold text-slate-900">{{ number_format($paymentStatusSummary[$key] ?? 0) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endisset

        {{-- Property breakdown --}}
        <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100 mt-4">
            <h2 class="text-base font-semibold text-slate-900">Income by Property</h2>
            <p class="text-sm text-slate-400 mt-0.5 mb-6">Collected rent in the selected period</p>
            @if($propertyBreakdown->isEmpty())
                <p class="text-sm text-slate-500 italic py-8 text-center">No properties yet.</p>
            @else
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div class="relative h-64 min-h-[16rem]">
                        <canvas id="propertyChart"></canvas>
                    </div>
                    <div class="overflow-x-auto rounded-xl ring-1 ring-slate-100 max-h-80 overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Property</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">City</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-slate-600">Units</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-slate-600">Occ.</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-slate-600">Vac.</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Rate</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600">Collected</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($propertyBreakdown as $row)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-slate-800">{{ $row['name'] }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $row['city'] }}</td>
                                        <td class="px-3 py-2 text-center">{{ $row['total_units'] }}</td>
                                        <td class="px-3 py-2 text-center">{{ $row['occupied_units'] }}</td>
                                        <td class="px-3 py-2 text-center">{{ $row['vacant_units'] }}</td>
                                        <td class="px-3 py-2 min-w-[120px]">
                                            <div class="flex items-center gap-2">
                                                <div class="h-2 flex-1 rounded-full bg-slate-100 overflow-hidden min-w-[48px]">
                                                    <div class="h-2 rounded-full bg-indigo-500" style="width: {{ min(100, (int) $row['occupancy_rate']) }}%"></div>
                                                </div>
                                                <span class="text-xs font-semibold text-slate-700 whitespace-nowrap">{{ $row['occupancy_rate'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-indigo-700 whitespace-nowrap">₱{{ number_format($row['total_collected'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Occupancy trend --}}
        <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100 mt-4">
            <h2 class="text-base font-semibold text-slate-900">Occupancy Trend</h2>
            <p class="text-sm text-slate-400 mt-0.5 mb-6">Active leases vs total units by month</p>
            <div class="overflow-x-auto rounded-xl ring-1 ring-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Month</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Active Leases</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Total Units</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Occupancy Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($occupancyTrend as $row)
                            <tr>
                                <td class="px-4 py-3">{{ $row['month'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $row['active_leases'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $totalUnits }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-indigo-700">{{ $row['occupancy_rate'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const reportIncomeCtx = document.getElementById('reportIncomeChart');
    if (reportIncomeCtx) {
        new Chart(reportIncomeCtx, {
            type: 'bar',
            data: {
                labels: @json($reportIncomeLabels),
                datasets: [{
                    label: 'Income (₱)',
                    data: @json($reportIncomeAmounts),
                    backgroundColor: 'rgba(99, 102, 241, 0.15)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => '₱' + Number(ctx.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            callback: (val) => '₱' + (val / 1000).toFixed(0) + 'k',
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }

    const propertyCtx = document.getElementById('propertyChart');
    if (propertyCtx && @json(count($propertyChartLabels)) > 0) {
        new Chart(propertyCtx, {
            type: 'bar',
            data: {
                labels: @json($propertyChartLabels),
                datasets: [{
                    label: 'Collected (₱)',
                    data: @json($propertyChartValues),
                    backgroundColor: 'rgba(99, 102, 241, 0.2)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => '₱' + Number(ctx.parsed.x).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            callback: (val) => '₱' + (val / 1000).toFixed(0) + 'k',
                            font: { size: 10 }
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }
})();
</script>
@endpush
