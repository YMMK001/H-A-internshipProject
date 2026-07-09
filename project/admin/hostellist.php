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
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// 2. SQL Query to combine both Apartments and Hostel Rooms
$sql = "
    SELECT 
        a.id AS item_id, 
        h.title, 
        h.township, 
        h.city, 
        h.amenities,
        'Apartment' AS type,
        a.floor_level AS unit_placement,
        a.max_occupy AS capacity_info,
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
        h.amenities,
        'Hostel' AS type,
        CONCAT(r.room_num, ' (', r.room_type, ')') AS unit_placement,
        r.sub_unit AS capacity_info,
        r.monthly_price AS price,
        r.is_available AS availability
    FROM rental_houses h
    INNER JOIN hostel_rooms r ON h.id = r.rental_house_id
";

$stmt = $pdo->query($sql);
$rentals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Listings Table</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 font-serif h-screen overflow-hidden text-gray-900">

    <div id="mobMenuOverlay" class="fixed inset-0 bg-black/40 z-20 hidden transition-opacity" onclick="toggleMobileMenu()"></div>

    <div class="flex h-full w-full overflow-hidden">
        
        <?php include 'ownerheader.php'; ?>

        <div class="flex-1 h-full  ">
            <div class="w-full max-w-7xl mx-auto">
                        
                <div class="bg-white border border-gray-300 shadow-sm px-4 py-3 mb-6 flex items-center justify-between font-sans rounded-sm">
                    <div class="flex items-center space-x-3">
                        <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                            ☰ Menu
                        </button>
                        <div class="hidden sm:flex items-center space-x-2 text-xs text-gray-500">
                            <span class="text-gray-800 font-bold text-lg">Retal HUb</span>
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
            <!-- Section Header: Traditional Ledger Style -->

            <div class="px-6">
            <div class="mb-6 pb-4 border-b-2 border-gray-800 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                   
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 font-sans">နေရာစုံငှားရမ်းမှုစာရင်း ဇယား</h1>
                   
                </div>
                
                <a href="egcreate.php" class="bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs uppercase tracking-wider px-4 py-2.5 rounded shadow-sm transition-colors self-start sm:self-auto border border-slate-700 font-sans">
                    [+] အခန်းအသစ် ဖြည့်စွက်ရန်
                </a>
            </div>

            <!-- Metrics Grid: Classic Table Data Summaries -->
           

            <!-- Data Ledger Table Component -->
            <div class="bg-white border border-gray-300 shadow-sm overflow-hidden mb-8 w-full font-sans">
                <div class="overflow-x-auto max-h-[560px]">
                    <table class="w-full text-left border-collapse whitespace-nowrap table-fixed border-gray-300">
                        <thead class="bg-gray-800 text-white text-xs font-semibold uppercase tracking-wider sticky top-0 z-10">
                            <tr>
                                <th class="p-3 pl-4 w-[15%] border border-gray-700">အမျိုးအစား</th>
                                <th class="p-3 w-[30%] border border-gray-700">ခေါင်းစဉ် / တည်နေရာ</th> 
                                <th class="p-3 w-[15%] border border-gray-700">အထပ် / အခန်း</th>
                                <th class="p-3 w-[15%] border border-gray-700">ဝန်ဆောင်မှု</th>
                                <th class="p-3 w-[12%] border border-gray-700 text-right">လစဉ်ဈေးနှုန်း</th>
                                <th class="p-3 text-center w-[13%] border border-gray-700">အခြေအနေပြင်ဆင်ရန်</th>
                            </tr>
                        </thead> 
                        <tbody class="divide-y divide-gray-300 text-xs text-gray-800 bg-white">
                            <?php foreach ($rentals as $row): ?>
                                <?php $is_available = (int)$row['availability'] === 1; ?>
                                <tr class="hover:bg-gray-50 transition-colors odd:bg-stone-50/50">
                                    
                                    <!-- Type column with simple outline badges -->
                                    <td class="p-3 pl-4 overflow-hidden text-ellipsis border-r border-gray-200">
                                        <?php if ($row['type'] === 'Apartment'): ?>
                                            <span class="border border-blue-400 text-blue-900 text-[11px] font-bold px-2 py-0.5 tracking-wide bg-blue-50/50">APARTMENT</span>
                                        <?php else: ?>
                                            <span class="border border-purple-400 text-purple-900 text-[11px] font-bold px-2 py-0.5 tracking-wide bg-purple-50/50">HOSTEL</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Title / Location info -->
                                    <td class="p-3 overflow-hidden text-ellipsis border-r border-gray-200">
                                        <div class="font-bold text-gray-900 truncate"><?= htmlspecialchars($row['title']) ?></div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 truncate">📍 <?= htmlspecialchars($row['township']) ?>၊ <?= htmlspecialchars($row['city']) ?>။</div>
                                    </td>

                                    <!-- Floor placement -->
                                    <td class="p-3 font-medium text-gray-700 overflow-hidden text-ellipsis border-r border-gray-200">
                                        <span class="font-bold"><?= htmlspecialchars($row['unit_placement']) ?></span>
                                        <?php if ($row['type'] === 'Apartment' && $row['capacity_info']): ?>
                                            <span class="text-[10px] text-gray-500 block mt-0.5">အများဆုံး: <?= htmlspecialchars($row['capacity_info']) ?> ဦး</span>
                                        <?php elseif ($row['type'] === 'Hostel' && $row['capacity_info']): ?>
                                            <div class="text-[10px] text-gray-500 mt-0.5">အခန်းတွဲခွဲ: <?= htmlspecialchars($row['capacity_info']) ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Amenities Column -->
                                    <td class="p-3 overflow-hidden text-ellipsis border-r border-gray-200">
                                        <div class="flex flex-wrap gap-1">
                                            <?php 
                                            $amenities = array_filter(explode(',', $row['amenities'] ?? ''));
                                            if (!empty($amenities)):
                                                foreach ($amenities as $amenity): 
                                                ?>
                                                    <span class="text-[10px] bg-white border border-gray-300 px-1.5 py-0.5 text-gray-600"><?= htmlspecialchars(trim($amenity)) ?></span>
                                                <?php 
                                                endforeach;
                                            else: ?>
                                                <span class="text-[11px] text-gray-400 italic font-normal">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Financial Data formatting -->
                                    <td class="p-3 font-bold text-gray-900 text-right overflow-hidden text-ellipsis border-r border-gray-200 tracking-tight">
                                        <?= number_format($row['price']) ?> <span class="text-[10px] font-normal text-gray-500">MMK</span>
                                    </td>

                                    <!-- Action Column: Functional status buttons instead of flat badges -->
                                    <td class="p-3 text-center overflow-hidden text-ellipsis">
                                        <form action="" method="POST" class="inline-block w-full">
                                            <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">
                                            <input type="hidden" name="type" value="<?= htmlspecialchars($row['type']) ?>">
                                            <input type="hidden" name="current_status" value="<?= $row['availability'] ?>">
                                            <input type="hidden" name="toggle_status" value="1">
                                            
                                            <?php if ($is_available): ?>
                                                <button type="submit" class="w-full bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-300 text-[11px] font-bold px-2 py-1 rounded transition-colors tracking-wide">
                                                    ● အားလပ်ပါသည်
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="w-full bg-stone-100 text-stone-600 hover:bg-stone-200 border border-stone-400 text-[11px] font-bold px-2 py-1 rounded transition-colors tracking-wide">
                                                    ■ ငှားရမ်းပြီး
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                    
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($rentals)): ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-400 italic bg-gray-50">
                                        လုပ်ငန်းသုံး စာရင်းဇယားများ မရှိသေးပါ။
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table> 
                </div>
            </div>
</div>
        </div>
    </div>
<script>
        function toggleMobileMenu() {
            // This grabs the aside container inside your ownerheader file
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('mobMenuOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
    </script>
</body>
</html>