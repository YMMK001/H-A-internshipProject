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

// 2. Handle Status Toggle Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $item_id = (int)$_POST['item_id'];
    $type = $_POST['type'];
    $current_status = (int)$_POST['current_status'];
    $new_status = $current_status === 1 ? 0 : 1;

    if ($type === 'Apartment') {
        $update_sql = "UPDATE apartments SET is_available = :new_status WHERE id = :item_id";
    } else {
        $update_sql = "UPDATE hostel_rooms SET is_available = :new_status WHERE id = :item_id";
    }

    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([':new_status' => $new_status, ':item_id' => $item_id]);
    
    header("Location: owner_dashboard.php");
    exit;
}

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

    <!-- Layout structure with stable sidebar allocation -->
    <div class="flex h-full w-full overflow-hidden">
        
        <!-- Sidebar Wrapper injection -->
        <div class="flex-shrink-0 h-full">
            <?php include 'ownerheader.php'; ?>
        </div>

        <!-- Scrollable Main Container Workspace -->
        <div class="flex-1 h-full overflow-y-auto">
            <div class="w-full max-w-7xl mx-auto  ">
                
                <!-- Navbar Header Header -->
               <div class="sticky top-0 z-20 bg-white border-b border-gray-300 shadow-sm px-4 py-3 mb-6 flex items-center justify-between font-sans rounded-sm">
    <div class="flex items-center space-x-3">
        <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
            ☰ Menu
        </button>
        <div class="hidden sm:flex items-center space-x-2 text-xs text-gray-500">
            <span class="text-gray-800 font-bold text-lg">Executive Dashboard</span>
        </div>
    </div>

    <div class="flex items-center space-x-4 divide-x divide-gray-200">
        <div class="pl-4 flex items-center space-x-2">
            <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-white text-xs font-bold shadow-inner border border-slate-700">
                AD
            </div>
            <div class="hidden lg:block leading-none">
                <p class="text-xs font-bold text-gray-900">အိမ်ပိုင်ရှင် မန်နေဂျာ</p>
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
                    <a href="egcreate.php" class="bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs uppercase tracking-wider px-4 py-2.5 rounded shadow-sm transition-colors self-start sm:self-auto border border-slate-700 font-sans">
                        [+] Add New Rental House
                    </a>
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
                    
                    <!-- Line Chart: Revenue over time -->
                    <div class="bg-white border border-gray-300 shadow-sm p-4 lg:col-span-2">
                        <div class="border-b border-gray-200 pb-2 mb-4">
                            <h3 class="text-xs uppercase font-bold tracking-wider text-gray-600">Revenue Performance Over Time</h3>
                        </div>
                        <div class="w-full relative h-[250px]">
                            <canvas id="revenueTimelineChart"></canvas>
                        </div>
                    </div>

                    <!-- Donut Chart: Payment Methods Distribution -->
                    <div class="bg-white border border-gray-300 shadow-sm p-4">
                        <div class="border-b border-gray-200 pb-2 mb-4">
                            <h3 class="text-xs uppercase font-bold tracking-wider text-gray-600">Payment Gateway Distribution</h3>
                        </div>
                        <div class="w-full relative h-[250px] flex items-center justify-center">
                            <canvas id="paymentMethodChart"></canvas>
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
                                    <th class="p-3 text-center w-[20%] border border-gray-700">Action / Status</th>
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

                                        <td class="p-3 text-center overflow-hidden text-ellipsis">
                                            <form action="" method="POST" class="inline-block w-full">
                                                <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">
                                                <input type="hidden" name="type" value="<?= htmlspecialchars($row['type']) ?>">
                                                <input type="hidden" name="current_status" value="<?= $row['availability'] ?>">
                                                <input type="hidden" name="toggle_status" value="1">
                                                
                                                <?php if ($is_available): ?>
                                                    <button type="submit" class="w-full bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-300 text-[11px] font-bold px-2.5 py-1 rounded transition-colors tracking-wide">
                                                        ● Available
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="w-full bg-stone-100 text-stone-600 hover:bg-stone-200 border border-stone-400 text-[11px] font-bold px-2.5 py-1 rounded transition-colors tracking-wide">
                                                        ■ Rented
                                                    </button>
                                                <?php endif; ?>
                                            </form>
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
            
            // --- Line Chart Configuration (Revenue Timeline) ---
            const timelineCtx = document.getElementById('revenueTimelineChart').getContext('2d');
            const timelineLabels = <?php echo json_encode($months); ?>;
            const timelineData = <?php echo json_encode($revenues); ?>;

            new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: timelineLabels.length ? timelineLabels : ["No Data"],
                    datasets: [{
                        label: 'Monthly Payments Received',
                        data: timelineData.length ? timelineData : [0],
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
                                callback: value => value.toLocaleString() + ' MMK'
                            }
                        },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });

            // --- Donut Chart Configuration (Payment Gateways) ---
            const methodCtx = document.getElementById('paymentMethodChart').getContext('2d');
            const methodLabels = <?php echo json_encode($methods); ?>;
            const methodData = <?php echo json_encode($method_shares); ?>;

            new Chart(methodCtx, {
                type: 'doughnut',
                data: {
                    labels: methodLabels.length ? methodLabels : ["No Records"],
                    datasets: [{
                        data: methodData.length ? methodData : [1],
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