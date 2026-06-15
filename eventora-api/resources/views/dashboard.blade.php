<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Organizer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Revenue Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                    <div class="p-4 bg-green-100 rounded-xl text-green-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Gross Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">{{ strtoupper($organization->currency) }} {{ number_format($grossRevenue, 2) }}</p>
                    </div>
                </div>

                <!-- Tickets Sold -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                    <div class="p-4 bg-blue-100 rounded-xl text-blue-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tickets Sold</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($ticketsSold) }}</p>
                    </div>
                </div>

                <!-- Check-ins -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                    <div class="p-4 bg-purple-100 rounded-xl text-purple-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Check-ins</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCheckins) }}</p>
                    </div>
                </div>

                <!-- Active Events -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                    <div class="p-4 bg-orange-100 rounded-xl text-orange-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active Events</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($activeEventsCount) }}</p>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Trend Line Chart -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 lg:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Revenue Trend (30 Days)</h3>
                    <div class="relative h-72 w-full">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Sales by Event Pie Chart -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Top 5 Events by Revenue</h3>
                    <div class="relative h-64 w-full flex justify-center">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Recent Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order / Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Buyer</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentOrders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $order->order_number }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-800">{{ $order->event->title ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $order->buyer_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->buyer_email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $order->status === 'paid' ? 'green' : ($order->status === 'pending' ? 'yellow' : 'red') }}-100 text-{{ $order->status === 'paid' ? 'green' : ($order->status === 'pending' ? 'yellow' : 'red') }}-800">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900 text-sm">
                                    {{ strtoupper($order->currency) }} {{ number_format($order->total, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No orders found yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Trend Line Chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            
            // Create a gradient for the line chart fill
            let gradient = trendCtx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)'); // Indigo-600 at 20%
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'), $dates)) !!},
                    datasets: [{
                        label: 'Revenue',
                        data: {!! json_encode($trendData) !!},
                        borderColor: '#4f46e5', // Indigo-600
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4 // Smooth curves
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#1f2937',
                            padding: 10,
                            titleFont: { size: 13 },
                            bodyFont: { size: 14, weight: 'bold' },
                            callbacks: {
                                label: function(context) {
                                    return ' Revenue: {{ strtoupper($organization->currency) }} ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6', drawBorder: false },
                            ticks: { font: { family: "'Inter', sans-serif" } }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { maxTicksLimit: 10, maxRotation: 0, font: { family: "'Inter', sans-serif" } }
                        }
                    },
                    interaction: { mode: 'nearest', axis: 'x', intersect: false }
                }
            });

            // Pie Chart (Sales by Event)
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            const pieLabels = {!! json_encode($pieLabels) !!};
            const pieData = {!! json_encode($pieData) !!};
            
            // If no data, show empty chart
            if (pieData.length === 0) {
                pieLabels.push('No Sales');
                pieData.push(1);
            }

            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: [
                            '#4f46e5', // Indigo
                            '#06b6d4', // Cyan
                            '#10b981', // Emerald
                            '#f59e0b', // Amber
                            '#ef4444', // Red
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { family: "'Inter', sans-serif", size: 12 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label !== 'No Sales') {
                                        return ` ${label}: {{ strtoupper($organization->currency) }} ` + context.parsed.toLocaleString();
                                    }
                                    return ' No data available';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
