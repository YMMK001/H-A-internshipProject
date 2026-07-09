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
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management System - Executive Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                            <span class="text-gray-800 font-bold text-lg">Dashboard</span>
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

                <div class="px-6">
                <div class="mb-6 pb-4 border-b-2 border-gray-800 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-600 block mb-1">Internal Management Console</span>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 font-sans">အိမ်ခြံမြေနှင့် အခန်းများ စီမံခန့်ခွဲမှုစနစ်</h1>
                        <p class="text-xs text-gray-600 mt-1 italic">စာရင်းသွင်းထားသော အိမ်၊ ခြံ၊ မြေနှင့် အခန်းအခြေအနေများအား စစ်ဆေးပြင်ဆင်ရန် နေရာဖြစ်ပါသည်။</p>
                    </div>
                    
                    <a href="egcreate.php" class="bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs uppercase tracking-wider px-4 py-2.5 rounded shadow-sm transition-colors self-start sm:self-auto border border-slate-700 font-sans">
                        [+] အခန်းအသစ် ဖြည့်စွက်ရန်
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 font-sans">
                    <div class="bg-white p-4 border border-gray-300 shadow-sm">
                        <span class="text-xs uppercase font-bold tracking-wider text-gray-500 block">စုစုပေါင်း ပိုင်ဆိုင်မှု အရေအတွက်</span>
                        <span class="text-2xl font-bold text-gray-800 mt-2 block"><?= count($rentals) ?> ယူနစ်</span>
                    </div>
                    
                    <div class="bg-white p-4 border border-gray-300 shadow-sm border-l-4 border-l-emerald-700">
                        <span class="text-xs uppercase font-bold tracking-wider text-gray-500 block">လတ်တလော အားလပ်သည့် အခန်း</span>
                        <span class="text-2xl font-bold text-emerald-800 mt-2 block">
                            <?= count(array_filter($rentals, function($r) { return (int)$r['availability'] === 1; })) ?> ယူနစ်
                        </span>
                    </div>

                    <div class="bg-white p-4 border border-gray-300 shadow-sm border-l-4 border-l-amber-700">
                        <span class="text-xs uppercase font-bold tracking-wider text-gray-500 block">ငှားရမ်းမှု ပြီးမြောက်ပြီး စာရင်း</span>
                        <span class="text-2xl font-bold text-amber-800 mt-2 block">
                            <?= count(array_filter($rentals, function($r) { return (int)$r['availability'] === 0; })) ?> ယူနစ်
                        </span>
                    </div>
                </div>

                <div class="bg-white border border-gray-300 shadow-sm overflow-hidden mb-8 w-full font-sans">
                    <div class="overflow-x-auto max-h-[460px]">
                        <table class="w-full text-left border-collapse whitespace-nowrap table-fixed border-gray-300">
                            <thead class="bg-gray-800 text-white text-xs font-semibold uppercase tracking-wider sticky top-0 z-10">
                                <tr>
                                    <th class="p-3 pl-4 w-[15%] border border-gray-700">အမျိုးအစား</th>
                                    <th class="p-3 w-[35%] border border-gray-700">အိမ်ခြံမြေအမည် / တည်နေရာ</th> 
                                    <th class="p-3 w-[15%] border border-gray-700">အထပ် / အခန်းအမှတ်</th>
                                    <th class="p-3 w-[15%] border border-gray-700 text-right">သတ်မှတ်ဈေးနှုန်း</th>
                                    <th class="p-3 text-center w-[20%] border border-gray-700">လုပ်ဆောင်ချက် / အခြေအနေ</th>
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

                                        <td class="p-3 font-bold text-gray-900 text-right overflow-hidden text-ellipsis border-r border-gray-200 tracking-tight">
                                            <?= number_format($row['price']) ?> <span class="text-[10px] font-normal text-gray-500">MMK</span>
                                        </td>

                                        <td class="p-3 text-center overflow-hidden text-ellipsis">
                                            <form action="" method="POST" class="inline-block">
                                                <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">
                                                <input type="hidden" name="type" value="<?= htmlspecialchars($row['type']) ?>">
                                                <input type="hidden" name="current_status" value="<?= $row['availability'] ?>">
                                                <input type="hidden" name="toggle_status" value="1">
                                                
                                                <?php if ($is_available): ?>
                                                    <button type="submit" class="w-full bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-300 text-[11px] font-bold px-2.5 py-1 rounded transition-colors tracking-wide">
                                                        ● အားလပ်ပါသည် (ပြောင်းရန်နှိပ်ပါ)
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="w-full bg-stone-100 text-stone-600 hover:bg-stone-200 border border-stone-400 text-[11px] font-bold px-2.5 py-1 rounded transition-colors tracking-wide">
                                                        ■ ငှားရမ်းပြီး (ပြောင်းရန်နှိပ်ပါ)
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                        
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($rentals)): ?>
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-gray-400 italic bg-gray-50">
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
    </div>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('mobMenuOverlay');
            
            if (sidebar && overlay) {
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
        }
    </script>
</body>
</html>