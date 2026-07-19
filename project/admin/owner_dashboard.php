<?php
// 1. Database Connection Configuration
$host    = 'localhost';
$db      = 'intern_test';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Database connection failure: " . $e->getMessage());
}

// NOTE: Status toggle POST action handler has been removed here 
// to prevent manual status updates by the property owner.

// 3. SQL Query to combine both Apartments and Hostel Rooms
$sql = "
    SELECT 
        a.id AS item_id, 
        h.title, 
        h.township, 
        h.city, 
        'Apartment' AS type,
        a.floor_level AS unit_placement,
        a.apartment_price AS price,
        a.is_available AS availability
    FROM rental_houses h
    INNER JOIN apartments a ON h.id = a.rental_house_id

    UNION ALL

    SELECT 
        r.id AS item_id, 
        h.title, 
        h.township, 
        h.city, 
        'Hostel' AS type,
        r.room_num AS unit_placement, 
        r.monthly_price AS price,
        r.is_available AS availability
    FROM rental_houses h
    INNER JOIN hostel_rooms r ON h.id = r.rental_house_id
";

$stmt = $pdo->query($sql);
$rentals = $stmt->fetchAll();

// 4. Financial Status Overview KPIs
$fin_sql = "
    SELECT 
        SUM(paid_amount) as total_collected,
        (SELECT SUM(amount_to_pay) FROM installments WHERE status != 'paid') as total_pending
    FROM payments
";
$fin_res = $pdo->query($fin_sql)->fetch();
$total_collected = $fin_res['total_collected'] ?? 0;
$total_pending = $fin_res['total_pending'] ?? 0;

// 5. Query 1: Monthly Timeline Collection (Line Chart Data)
$timeline_sql = "
    SELECT 
        DATE_FORMAT(paid_at, '%b %Y') AS payment_month,
        SUM(paid_amount) AS total_revenue
    FROM payments
    GROUP BY DATE_FORMAT(paid_at, '%Y-%m'), DATE_FORMAT(paid_at, '%b %Y')
    ORDER BY DATE_FORMAT(paid_at, '%Y-%m') ASC
    LIMIT 12
";
$timeline_data = $pdo->query($timeline_sql)->fetchAll();

$months = [];
$revenues = [];
foreach ($timeline_data as $row) {
    $months[] = $row['payment_month'];
    $revenues[] = (float)$row['total_revenue'];
}

// 6. Query 2: Payment Gateways Share (Donut / Pie Chart Data)
$method_sql = "
    SELECT 
        pm.name AS method_name,
        SUM(p.paid_amount) AS method_total
    FROM payments p
    INNER JOIN payment_methods pm ON p.payment_method_id = pm.id
    GROUP BY pm.name
";
$method_data = $pdo->query($method_sql)->fetchAll();

$methods = [];
$method_shares = [];
foreach ($method_data as $row) {
    $methods[] = $row['method_name'];
    $method_shares[] = (float)$row['method_total'];
}

// 7. Data Processing: Calculate Average Pricing Comparison (Apartment vs Hostel)
$apt_prices = [];
$hostel_prices = [];
foreach ($rentals as $row) {
    if ($row['type'] === 'Apartment') {
        $apt_prices[] = (float)$row['price'];
    } else {
        $hostel_prices[] = (float)$row['price'];
    }
}
$avg_apt = count($apt_prices) > 0 ? array_sum($apt_prices) / count($apt_prices) : 0;
$avg_hostel = count($hostel_prices) > 0 ? array_sum($hostel_prices) / count($hostel_prices) : 0;
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management System - Executive Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-serif h-screen overflow-hidden text-gray-900">

    <div id="mobMenuOverlay" class="fixed inset-0 bg-black/40 z-20 hidden transition-opacity" onclick="toggleMobileMenu()"></div>

    <div class="flex h-full w-full overflow-hidden">
        
        <!-- Sidebar Wrapper injection -->
        <div class="flex-shrink-0 h-full">
            <?php include 'ownerheader.php'; ?>
        </div>

        <!-- Scrollable Main Container Workspace -->
        <div class="flex-1 h-full overflow-y-auto">
            <div class="w-full max-w-7xl mx-auto">
                
                <!-- Navbar Header -->
                <div class="sticky top-0 z-20 bg-white border-b border-gray-300 shadow-sm px-4 py-3 mb-6 flex items-center justify-between font-sans rounded-sm">
                    <div class="flex items-center space-x-3">
                        <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                            ☰ Menu
                        </button>
                        <div class="hidden sm:flex items-center space-x-2 text-xs text-gray-500">
                            <span class="text-gray-800 font-bold text-2xl">Executive Dashboard</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 divide-x divide-gray-200">
                        <div class="pl-4 flex items-center space-x-2">
                            <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-white text-xs font-bold shadow-inner border border-slate-700">
                                AD
                            </div>
                            <div class="hidden lg:block leading-none">
                                <p class="text-xs font-bold text-gray-900">Owner</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Console Role</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="py-6 px-4">
                    <!-- Dashboard Title Row -->
                    <div class="mb-6 pb-4 border-b-2 border-gray-800 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                        <div>
                            <span class="text-xl font-bold uppercase tracking-widest text-gray-600 block mb-1">Internal Management Console</span>
                        </div>
                    </div>

                    <!-- KPI Overview Grid Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 font-sans">
                        <div class="bg-white p-4 border border-gray-300 shadow-sm">
                            <span class="text-xs uppercase font-bold tracking-wider text-gray-500 block">Total Number of Assets</span>
                            <span class="text-xl font-bold text-gray-800 mt-2 block"><?= count($rentals) ?> units</span>
                        </div>
                        
                        <div class="bg-white p-4 border border-gray-300 shadow-sm border-l-4 border-l-emerald-700">
                            <span class="text-xs uppercase font-bold tracking-wider text-gray-500 block">Currently Available</span>
                            <span class="text-xl font-bold text-emerald-800 mt-2 block">
                                <?= count(array_filter($rentals, function($r) { return (int)$r['availability'] === 1; })) ?> units
                            </span>
                        </div>

                        <div class="bg-white p-4 border border-gray-300 shadow-sm border-l-4 border-l-slate-700">
                            <span class="text-xs uppercase font-bold tracking-wider text-gray-500 block">Total Revenue Collected</span>
                            <span class="text-xl font-bold text-slate-900 mt-2 block"><?= number_format($total_collected) ?> MMK</span>
                        </div>

                        <div class="bg-white p-4 border border-gray-300 shadow-sm border-l-4 border-l-rose-700">
                            <span class="text-xs uppercase font-bold tracking-wider text-gray-500 block">Outstanding Balance</span>
                            <span class="text-xl font-bold text-rose-800 mt-2 block"><?= number_format($total_pending) ?> MMK</span>
                        </div>
                    </div>

                    <!-- CLASSIC DUAL-CHART BLOCK COMPONENT -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 font-sans">
                        <!-- Revenue Timeline Chart -->
                        <div class="bg-white p-4 border border-gray-300 shadow-sm lg:col-span-2">
                            <h3 class="text-sm font-bold uppercase text-gray-700 mb-4 tracking-wide">Revenue Collection Timeline</h3>
                            <div class="h-64 relative">
                                <canvas id="revenueTimelineChart"></canvas>
                            </div>
                        </div>

                        <!-- Payment Gateways Share -->
                        <div class="bg-white p-4 border border-gray-300 shadow-sm">
                            <h3 class="text-sm font-bold uppercase text-gray-700 mb-4 tracking-wide">Payment Gateways Share</h3>
                            <div class="h-64 relative">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- PRICE COMPARISON BLOCK COMPONENT -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 font-sans">
                        <!-- Visual Rent Comparison Chart -->
                        <div class="bg-white p-4 border border-gray-300 shadow-sm lg:col-span-2">
                            <h3 class="text-sm font-bold uppercase text-gray-700 mb-4 tracking-wide">Rental Rates Comparison (Average Market Price)</h3>
                            <div class="h-64 relative">
                                <canvas id="rentComparisonChart"></canvas>
                            </div>
                        </div>

                        <!-- Data Insights Summary Panel -->
                        <div class="bg-white p-5 border border-gray-300 shadow-sm flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-bold uppercase text-gray-700 mb-4 tracking-wide">Pricing Comparison Summary</h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center border-b pb-2 border-gray-100">
                                        <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider px-2 py-0.5 border border-blue-200 bg-blue-50">Avg Apartment</span>
                                        <span class="font-bold text-gray-900 text-sm"><?= number_format($avg_apt) ?> MMK</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b pb-2 border-gray-100">
                                        <span class="text-xs font-semibold text-purple-900 uppercase tracking-wider px-2 py-0.5 border border-purple-200 bg-purple-50">Avg Hostel Room</span>
                                        <span class="font-bold text-gray-900 text-sm"><?= number_format($avg_hostel) ?> MMK</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 border border-gray-200 mt-4">
                                <p class="text-[11px] text-gray-600 leading-relaxed">
                                    <strong>Insight:</strong> 
                                    <?php if ($avg_apt > $avg_hostel): ?>
                                        Apartments are on average <strong><?= number_format($avg_apt - $avg_hostel) ?> MMK</strong> more expensive per month than hostel units.
                                    <?php else: ?>
                                        Hostel rooms are on average <strong><?= number_format($avg_hostel - $avg_apt) ?> MMK</strong> more expensive per month than apartment units.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table Container -->
                    <div class="bg-white border border-gray-300 shadow-sm overflow-hidden w-full font-sans">
                        <div class="overflow-x-auto max-h-[460px]">
                            <table class="w-full text-left border-collapse whitespace-nowrap table-fixed border-gray-300">
                                <thead class="bg-gray-800 text-white text-xs font-semibold uppercase tracking-wider sticky top-0 z-10">
                                    <tr>
                                        <th class="p-3 pl-4 w-[15%] border border-gray-700">Category</th>
                                        <th class="p-3 w-[35%] border border-gray-700">Apartment & Hostel Name / Location</th> 
                                        <th class="p-3 w-[15%] border border-gray-700">Floor/Room NO</th>
                                        <th class="p-3 w-[15%] border border-gray-700">Price</th>
                                        <th class="p-3 text-center w-[20%] border border-gray-700">Status</th>
                                    </tr>
                                </thead> 
                                <tbody class="divide-y divide-gray-300 text-xs text-gray-800 bg-white">
                                    <?php foreach ($rentals as $row): ?>
                                        <?php $is_available = (int)$row['availability'] === 1; ?>
                                        <tr class="hover:bg-gray-50 transition-colors odd:bg-stone-50/50">
                                            <td class="p-3 pl-4 overflow-hidden text-ellipsis border-r border-gray-200">
                                                <?php if ($row['type'] === 'Apartment'): ?>
                                                    <span class="border border-blue-400 text-blue-900 text-[11px] font-bold px-2 py-0.5 tracking-wide bg-blue-50/50">APARTMENT</span>
                                                <?php else: ?>
                                                    <span class="border border-purple-400 text-purple-900 text-[11px] font-bold px-2 py-0.5 tracking-wide bg-purple-50/50">HOSTEL</span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="p-3 overflow-hidden text-ellipsis border-r border-gray-200">
                                                <div class="font-bold text-gray-900 truncate"><?= htmlspecialchars($row['title']) ?></div>
                                                <div class="text-[11px] text-gray-500 mt-0.5 truncate">📍 <?= htmlspecialchars($row['township']) ?>၊ <?= htmlspecialchars($row['city']) ?>။</div>
                                            </td>

                                            <td class="p-3 font-medium text-gray-700 overflow-hidden text-ellipsis border-r border-gray-200">
                                                <?= htmlspecialchars($row['unit_placement']) ?>
                                            </td>

                                            <td class="p-3 font-bold text-gray-900 overflow-hidden text-ellipsis border-r border-gray-200 tracking-tight">
                                                <?= number_format($row['price']) ?> <span class="text-[10px] font-normal text-gray-500">MMK</span>
                                            </td>

                                            <!-- Render-Only Status Indicators -->
                                            <td class="p-3 text-center overflow-hidden text-ellipsis">
                                                <?php if ($is_available): ?>
                                                    <span class="inline-block w-full text-center bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold px-2.5 py-1 rounded tracking-wide">
                                                        ● Available
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-block w-full text-center bg-stone-100 text-stone-600 border border-stone-200 text-[11px] font-bold px-2.5 py-1 rounded tracking-wide">
                                                        ■ Rented
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Engine Implementation Scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof Chart === 'undefined') {
                console.error("Chart.js failed to load. Check your internet connection or CDN link.");
                return;
            }

            try {
                // --- Line Chart Configuration (Revenue Timeline) ---
                const timelineCtx = document.getElementById('revenueTimelineChart');
                const timelineLabels = <?php echo json_encode(!empty($months) ? $months : ["No Data"]); ?>;
                const timelineData = <?php echo json_encode(!empty($revenues) ? $revenues : [0]); ?>;

                new Chart(timelineCtx, {
                    type: 'line',
                    data: {
                        labels: timelineLabels,
                        datasets: [{
                            label: 'Monthly Payments Received',
                            data: timelineData,
                            borderColor: '#1e293b', 
                            backgroundColor: 'rgba(30, 41, 59, 0.05)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.2,
                            pointBackgroundColor: '#0f172a'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    font: { size: 10 },
                                    callback: function(value) {
                                        return Number(value).toLocaleString() + ' MMK';
                                    }
                                }
                            },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            } catch (e) {
                console.error("Error rendering Timeline Chart:", e);
            }

            try {
                // --- Donut Chart Configuration (Payment Gateways) ---
                const methodCtx = document.getElementById('paymentMethodChart');
                const methodLabels = <?php echo json_encode(!empty($methods) ? $methods : ["No Records"]); ?>;
                const methodData = <?php echo json_encode(!empty($method_shares) ? $method_shares : [1]); ?>;

                new Chart(methodCtx, {
                    type: 'doughnut',
                    data: {
                        labels: methodLabels,
                        datasets: [{
                            data: methodData,
                            backgroundColor: [
                                '#1e293b', 
                                '#0f766e', 
                                '#b45309', 
                                '#4338ca'  
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: { size: 10 }, boxWidth: 10 }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error("Error rendering Payment Method Chart:", e);
            }

            try {
                // --- Bar Chart Configuration (Apartment vs Hostel Price Comparison) ---
                const rentCtx = document.getElementById('rentComparisonChart');
                const avgApt = <?php echo (float)$avg_apt; ?>;
                const avgHostel = <?php echo (float)$avg_hostel; ?>;

                new Chart(rentCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Apartment', 'Hostel Room'],
                        datasets: [{
                            data: [avgApt, avgHostel],
                            backgroundColor: ['#2563eb', '#8b5cf6'],
                            borderWidth: 0,
                            borderRadius: 4,
                            barThickness: 60
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    font: { size: 10 },
                                    callback: function(value) {
                                        return Number(value).toLocaleString() + ' MMK';
                                    }
                                }
                            },
                            x: { grid: { display: false }, ticks: { font: { size: 11, weight: 'bold' } } }
                        }
                    }
                });
            } catch (e) {
                console.error("Error rendering Rent Comparison Chart:", e);
            }
        });

        function toggleMobileMenu() {
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('mobMenuOverlay');
            if (sidebar && overlay) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }
    </script>
</body>
</html>