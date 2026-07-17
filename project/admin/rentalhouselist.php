<?php
// ၁။ Session စနစ်အား ဖိုင်၏ ထိပ်ဆုံးတွင် မဖြစ်မနေ စတင်ဖွင့်လှစ်ခြင်း
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ၂။ Null Parameter အမှားများမတက်စေရန် Session Username အား စစ်ဆေးပြင်ဆင်ခြင်း
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "Renter"; 
}

// ၃။ DATABASE CONFIGURATION & CONNECTION (PDO)
$host     = 'localhost';
$db_name  = 'intern_test'; 
$username = 'root';              
$password = '';                  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='color:red; font-weight:bold; padding:20px;'>Database Connection Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

// =========================================================================
// BACKGROUND AUTO-SYNC (စာချုပ်သက်တမ်းကုန်ဆုံးပါက အလိုအလျောက် Available ပြန်ပြောင်းပေးမည့် စနစ်)
// =========================================================================
try {
    $today = date('Y-m-d');

    // ၁။ စာချုပ်ကုန်ဆုံးရက် ရောက်နေပြီဖြစ်သော စာချုပ်များကို Status 'expired' ဟု ပြောင်းလဲခြင်း
    $expire_sql = "UPDATE contracts SET status = 'expired' WHERE end_date < :today AND status = 'active'";
    $stmt_exp = $pdo->prepare($expire_sql);
    $stmt_exp->execute([':today' => $today]);

    // ၂။ တက်ကြွဆဲ စာချုပ် (active) ရှိနေသော အခန်းများကို Locked (is_available = 0) ဟု ပြောင်းခြင်း
    $sync_ap_sql = "UPDATE apartments SET is_available = 0 WHERE id IN (SELECT apartment_id FROM contracts WHERE status = 'active' AND apartment_id IS NOT NULL)";
    $pdo->exec($sync_ap_sql);

    $sync_hr_sql = "UPDATE hostel_rooms SET is_available = 0 WHERE id IN (SELECT hostel_room_id FROM contracts WHERE status = 'active' AND hostel_room_id IS NOT NULL)";
    $pdo->exec($sync_hr_sql);

    // ၃။ သက်တမ်းကုန်သွားသော သို့မဟုတ် စာချုပ်မရှိသော အခန်းများကို Available (is_available = 1) ပြန်ဖွင့်ပေးခြင်း
    $reset_ap_sql = "UPDATE apartments SET is_available = 1 WHERE id NOT IN (SELECT apartment_id FROM contracts WHERE status = 'active' AND apartment_id IS NOT NULL)";
    $pdo->exec($reset_ap_sql);

    $reset_hr_sql = "UPDATE hostel_rooms SET is_available = 1 WHERE id NOT IN (SELECT hostel_room_id FROM contracts WHERE status = 'active' AND hostel_room_id IS NOT NULL)";
    $pdo->exec($reset_hr_sql);

} catch (PDOException $e) {
    // Error Block
}

// ၄။ COMBINED POLYMORPHIC UNION QUERY (rental_house_images မှ ပထမဆုံးပုံ သို့မဟုတ် cover ပုံကို ယူရန် ပြင်ဆင်ထားပါသည်)
$query = "
    SELECT 
        ap.id AS id,
        rh.title,
        rh.township,
        rh.city,
        ap.apartment_price AS price,
        CONCAT('Floor: ', ap.floor_level, ' | 👥 Max: ', ap.max_occupy, ' ဦး') AS unit_details,
        ap.is_available,
        img.image_url,
        'apartment' AS type
    FROM rental_houses rh
    INNER JOIN apartments ap ON rh.id = ap.rental_house_id
    LEFT JOIN (
        SELECT rental_house_id, image_url 
        FROM rental_house_images 
        WHERE id IN (
            SELECT MIN(id) FROM rental_house_images GROUP BY rental_house_id
        )
    ) img ON rh.id = img.rental_house_id
    WHERE rh.is_active = 1

    UNION ALL

    SELECT 
        hr.id AS id,
        rh.title,
        rh.township,
        rh.city,
        hr.monthly_price AS price,
        CONCAT('Room: ', hr.room_num, ' | ', hr.room_type, ' (', hr.sub_unit, ')') AS unit_details,
        hr.is_available,
        img.image_url,
        'hostel' AS type
    FROM rental_houses rh
    INNER JOIN hostel_rooms hr ON rh.id = hr.rental_house_id
    LEFT JOIN (
        SELECT rental_house_id, image_url 
        FROM rental_house_images 
        WHERE id IN (
            SELECT MIN(id) FROM rental_house_images GROUP BY rental_house_id
        )
    ) img ON rh.id = img.rental_house_id
    WHERE rh.is_active = 1
";

try {
    $stmt = $pdo->query($query);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='color:red; font-weight:bold; padding:20px;'>Query execution error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

// ၅။ FILTER UNIFIED DATA INTO SEPARATED VISUAL TRACK
$apartments = array_filter($properties, function($item) { return $item['type'] === 'apartment'; });
$hostels    = array_filter($properties, function($item) { return $item['type'] === 'hostel'; });
?>


<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Finder - Classic Style</title>
    <script src="https://tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#fcfbf9] font-classic h-screen overflow-hidden flex text-gray-800">

<?php include 'renterheader.php'; ?>

<div class="flex-1 flex flex-col h-screen overflow-y-auto">
    <div class="sm:hidden mb-4">
        <button onclick="toggleMobileMenu()" class="bg-[#292515] text-white text-xs font-serif px-3 py-2 shadow-sm border border-stone-700">
            ☰ Menu
        </button>
      </div>
    <!-- Header Section -->
   <!-- Added sticky, top-0, and z-50 here -->
<header class="bg-white border-b border-gray-200 py-5 px-6 lg:px-8 flex-shrink-0 shadow-sm sticky top-0 z-50">
     <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 title-classic">THE RENTAL HUB</h1>
                <p class="text-xs uppercase tracking-wider text-gray-400 mt-0.5">Premium Property & Accommodation Management</p>
            </div>
            <div class="flex items-center gap-1 bg-gray-100 p-1 border border-gray-200 rounded-md self-start">
                <button id="cardViewBtn" onclick="switchView('card')" class="px-4 py-1.5 bg-white text-slate-900 font-medium border border-gray-200 rounded text-xs shadow-sm transition-all">
                    Card View
                </button>
                <button id="tableViewBtn" onclick="switchView('table')" class="px-4 py-1.5 text-gray-500 hover:text-slate-900 font-medium rounded text-xs transition-all">
                    Table View
                </button>
            </div>
        </div>
</header>
    <main class="flex-1  bg-[#faf9f6]">
        <div class="max-w-6xl px-6 w-full py-10 mx-auto">
            
            <!-- Search Bar -->
            <div class="mb-8 bg-white px-4 py-2.5 border border-gray-200 rounded-md shadow-sm flex items-center max-w-md focus-within:border-slate-400 transition-all">
                <svg class="w-4 h-4 text-gray-400 mr-2.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
                </svg>
                <input id="citySearchInput" onkeyup="filterByCity()" type="search" class="w-full text-xs outline-none bg-transparent text-gray-700 placeholder-gray-400 tracking-wide" placeholder="ရှာဖွေရန် (ဥပမာ - ရန်ကုန်၊ လှိုင်)...">
            </div>

            <!-- Card Layout Mode -->
            <div id="cardLayout" class="space-y-14 block">
                
                <!-- Apartments Section -->
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-5 border-b border-gray-200 pb-2">Apartments / တိုက်ခန်းများ</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if (empty($apartments)): ?>
                            <p class="text-gray-400 text-xs italic col-span-full py-4">ငှားရန်တိုက်ခန်းမရှိသေးပါ။</p>
                        <?php else: ?>
                            <?php foreach ($apartments as $row): 
                                $isAvailable = ($row['is_available'] == 1);
                                $statusText  = $isAvailable ? 'AVAILABLE' : 'LEASED';
                                $statusClass = $isAvailable ? 'text-emerald-700 border-emerald-200 bg-emerald-50' : 'text-stone-400 border-stone-200 bg-stone-50';
                                $imagePath = (!empty($row['image_url'])) ? htmlspecialchars($row['image_url']) : 'uploads/default.jpg';
                            ?>
                            <div class="property-card bg-white border border-gray-200 rounded-md overflow-hidden hover:shadow-md transition-all flex flex-col justify-between opacity-<?= $isAvailable ? '100' : '80' ?>" data-city="<?= htmlspecialchars(strtolower($row['city'])) ?>">
                                <div>
                                    <div class="relative h-48 w-full overflow-hidden bg-stone-100">
                                        <img src="<?= $imagePath ?>" alt="Apartment" class="w-full h-full object-cover grayscale-[15%] hover:grayscale-0 transition-all duration-300">
                                        <span class="absolute top-3 left-3 bg-slate-900 text-white text-[9px] uppercase font-bold tracking-widest px-2 py-0.5 rounded-sm">Apartment</span>
                                    </div>
                                    <div class="p-5">
                                        <h3 class="font-bold text-slate-800 text-base line-clamp-1 tracking-tight"><?= htmlspecialchars($row['title']) ?></h3>
                                        <p class="text-[11px] text-gray-400 mt-1 uppercase tracking-wider">📍 <?= htmlspecialchars($row['township']) ?>, <?= htmlspecialchars($row['city']) ?></p>
                                        
                                        <div class="my-4 bg-stone-50 p-3 rounded border border-stone-200/60 text-xs text-gray-600 font-medium">
                                            <?= htmlspecialchars($row['unit_details']) ?>
                                        </div>
                                        
                                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                            <div>
                                                <span class="text-lg font-bold text-slate-900"><?= number_format($row['price']) ?></span>
                                                <span class="text-[10px] text-gray-400 font-bold ml-0.5">MMK / Month</span>
                                            </div>
                                            <span class="text-[9px] tracking-wider font-bold border px-1.5 py-0.5 rounded-sm <?= $statusClass ?>"><?= $statusText ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-5 pb-5 bg-white flex justify-between items-center gap-2">
                                    <a href="view_details.php?id=<?= (int)$row['id']; ?>&type=apartment" class="px-3 py-2 bg-white text-slate-800 border border-gray-300 rounded font-medium text-xs hover:bg-stone-50 transition-all text-center flex-1">
                                        Details
                                    </a>
                                    <?php if ($isAvailable): ?>
                                        <a href="rentercontract.php?item_id=<?= (int)$row['id']; ?>&type=apartment" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">
                                            Book Lease
                                        </a>
                                    <?php else: ?>
                                        <button type="button" disabled class="px-4 py-2 bg-gray-100 text-gray-400 border border-gray-200 rounded font-medium text-xs cursor-not-allowed flex-1">
                                            Reserved
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hostels Section -->
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-5 border-b border-gray-200 pb-2">Hostels / အဆောင်များ</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if (empty($hostels)): ?>
                            <p class="text-gray-400 text-xs italic col-span-full py-4">ငှားရန်အဆောင်အခန်းမရှိသေးပါ။</p>
                        <?php else: ?>
                            <?php foreach ($hostels as $row): 
                                $isAvailable = ($row['is_available'] == 1);
                                $statusText  = $isAvailable ? 'AVAILABLE' : 'LEASED';
                                $statusClass = $isAvailable ? 'text-amber-800 border-amber-200 bg-amber-50' : 'text-stone-400 border-stone-200 bg-stone-50';
                                $imagePath = (!empty($row['image_url'])) ? htmlspecialchars($row['image_url']) : 'uploads/default.jpg';
                            ?>
                            <div class="property-card bg-white border border-gray-200 rounded-md overflow-hidden hover:shadow-md transition-all flex flex-col justify-between opacity-<?= $isAvailable ? '100' : '80' ?>" data-city="<?= htmlspecialchars(strtolower($row['city'])) ?>">
                                <div>
                                    <div class="relative h-48 w-full overflow-hidden bg-stone-100">
                                        <img src="<?= $imagePath ?>" alt="Hostel" class="w-full h-full object-cover grayscale-[15%] hover:grayscale-0 transition-all duration-300">
                                        <span class="absolute top-3 left-3 bg-stone-700 text-white text-[9px] uppercase font-bold tracking-widest px-2 py-0.5 rounded-sm">Hostel</span>
                                    </div>
                                    <div class="p-5">
                                        <h3 class="font-bold text-slate-800 text-base line-clamp-1 tracking-tight"><?= htmlspecialchars($row['title']) ?></h3>
                                        <p class="text-[11px] text-gray-400 mt-1 uppercase tracking-wider">📍 <?= htmlspecialchars($row['township']) ?>, <?= htmlspecialchars($row['city']) ?></p>
                                        
                                        <div class="my-4 bg-stone-50 p-3 rounded border border-stone-200/60 text-xs text-gray-600 font-medium">
                                            <?= htmlspecialchars($row['unit_details']) ?>
                                        </div>
                                        
                                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                            <div>
                                                <span class="text-lg font-bold text-slate-900"><?= number_format($row['price']) ?></span>
                                                <span class="text-[10px] text-gray-400 font-bold ml-0.5">MMK / Month</span>
                                            </div>
                                            <span class="text-[9px] tracking-wider font-bold border px-1.5 py-0.5 rounded-sm <?= $statusClass ?>"><?= $statusText ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-5 pb-5 bg-white flex justify-between items-center gap-2">
                                    <a href="view_details.php?id=<?= (int)$row['id']; ?>&type=hostel" class="px-3 py-2 bg-white text-slate-800 border border-gray-300 rounded font-medium text-xs hover:bg-stone-50 transition-all text-center flex-1">
                                        Details
                                    </a>
                                    <?php if ($isAvailable): ?>
                                        <a href="rentercontract.php?item_id=<?= (int)$row['id']; ?>&type=hostel" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">
                                            Book Lease
                                        </a>
                                    <?php else: ?>
                                        <button type="button" disabled class="px-4 py-2 bg-gray-100 text-gray-400 border border-gray-200 rounded font-medium text-xs cursor-not-allowed flex-1">
                                            Reserved
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Table Layout Mode -->
            <div id="tableLayout" class="hidden bg-white border border-gray-200 rounded shadow-sm overflow-hidden mt-4">
                <div class="overflow-x-auto max-h-[500px]">
                    <table class="w-full text-left border-collapse text-xs tracking-wide">
                        <thead>
                            <tr class="bg-stone-50 text-slate-700 font-bold uppercase tracking-wider border-b border-gray-200">
                                <th class="p-4">Property Title</th>
                                <th class="p-4">Type</th>
                                <th class="p-4">Location</th>
                                <th class="p-4">Specifications</th>
                                <th class="p-4">Monthly Rent</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-600">
                            <?php if (empty($properties)): ?>
                                <tr>
                                    <td colspan="7" class="p-4 text-center text-gray-400 italic">No rental properties found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($properties as $row): 
                                    $isAvail = ($row['is_available'] == 1);
                                    $badgeColor = $isAvail ? 'text-emerald-800 bg-emerald-50 border-emerald-200' : 'text-stone-400 bg-stone-50 border-stone-200';
                                    $badgeText  = $isAvail ? 'AVAILABLE' : 'LEASED';
                                ?>
                                <tr class="property-row hover:bg-stone-50/50 duration-150 transition-colors bg-white" data-city="<?= htmlspecialchars(strtolower($row['city'])) ?>">
                                    <td class="p-4 font-bold text-slate-800">
                                        <div class="flex items-center gap-3">
                                            <img src="<?= (!empty($row['image_url'])) ? htmlspecialchars($row['image_url']) : 'uploads/default.jpg' ?>" class="w-9 h-9 rounded object-cover border border-gray-200">
                                            <span><?= htmlspecialchars($row['title']) ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4 uppercase text-[10px]">
                                        <span class="px-2 py-0.5 border font-semibold rounded-sm <?= ($row['type'] === 'apartment') ? 'border-blue-200 bg-blue-50/40' : 'border-stone-300 text-stone-700 bg-stone-100' ?>">
                                            <?= htmlspecialchars($row['type']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4"><?= htmlspecialchars($row['township']) ?>, <span class="font-medium text-gray-800"><?= htmlspecialchars($row['city']) ?></span></td>
                                    <td class="p-4 text-stone-500 font-mono"><?= htmlspecialchars($row['unit_details']) ?></td>
                                    <td class="p-4 font-bold text-slate-900"><?= number_format($row['price']) ?> MMK</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 border text-[10px] font-bold rounded-sm <?= $badgeColor ?>"><?= $badgeText ?></span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="inline-flex items-center justify-center gap-1.5">
                                            <a href="view_details.php?id=<?= (int)$row['id']; ?>&type=<?= $row['type']; ?>" class="px-2.5 py-1 bg-white text-gray-700 border border-gray-300 rounded hover:bg-stone-50 transition-all font-medium">
                                                View
                                            </a>
                                            <?php if ($isAvail): ?>
                                                <a href="rentercontract.php?item_id=<?= (int)$row['id']; ?>&type=<?= $row['type']; ?>" class="px-2.5 py-1 bg-slate-900 text-white rounded hover:bg-slate-800 transition-all font-medium">
                                                    Lease
                                                </a>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 bg-gray-50 text-gray-400 border border-gray-200 rounded font-medium cursor-not-allowed">
                                                    Locked
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>
                    
    <script>
        function filterByCity() {
            const input = document.getElementById('citySearchInput');
            const filterValue = input.value.toLowerCase().trim();
            
            const cards = document.querySelectorAll('.property-card');
            cards.forEach(card => {
                const cardText = card.textContent || card.innerText;
                card.style.display = cardText.toLowerCase().includes(filterValue) ? "" : "none";
            });

            const rows = document.querySelectorAll('.property-row');
            rows.forEach(row => {
                const rowText = row.textContent || row.innerText;
                row.style.display = rowText.toLowerCase().includes(filterValue) ? "" : "none";
            });
        }

        function switchView(viewType) {
            const cardLayout = document.getElementById('cardLayout');
            const tableLayout = document.getElementById('tableLayout');
            const cardViewBtn = document.getElementById('cardViewBtn');
            const tableViewBtn = document.getElementById('tableViewBtn');

            if (viewType === 'card') {
                cardLayout.classList.remove('hidden');
                tableLayout.classList.add('hidden');
                cardViewBtn.className = "px-4 py-1.5 bg-white text-slate-900 font-medium border border-gray-200 rounded text-xs shadow-sm transition-all";
                tableViewBtn.className = "px-4 py-1.5 text-gray-500 hover:text-slate-900 font-medium rounded text-xs transition-all";
            } else {
                cardLayout.classList.add('hidden');
                tableLayout.classList.remove('hidden');
                tableViewBtn.className = "px-4 py-1.5 bg-white text-slate-900 font-medium border border-gray-200 rounded text-xs shadow-sm transition-all";
                cardViewBtn.className = "px-4 py-1.5 text-gray-500 hover:text-slate-900 font-medium rounded text-xs transition-all";
            }
        }
    </script>
    <script>
      function toggleMobileMenu() {
          const sidebar = document.getElementById('tenantSidebar');
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