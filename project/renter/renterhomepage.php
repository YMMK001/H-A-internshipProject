
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
    // Suppressed background error to keep app stable
}

// ၄။ COMBINED POLYMORPHIC UNION QUERY
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
    <title>The Rental Hub - Find Your Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#fcfbf9] font-classic h-screen overflow-hidden flex text-gray-800">

<div class="flex-1 flex flex-col h-screen overflow-y-auto">
    
    <nav class="bg-white border-b border-stone-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between py-4 items-center gap-4">
                
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="h-10 w-10 bg-blue-900 border border-amber-600 flex items-center justify-center text-amber-100 font-serif font-bold text-xl">R</div>
                    <span class="text-2xl font-serif font-bold tracking-tight text-stone-900">Rental<span class="text-blue-900 italic font-normal">Hub</span></span>
                </a>

                <div class="flex flex-wrap items-center gap-2 bg-stone-50 border border-stone-200 p-1.5 rounded-md w-full md:w-auto max-w-xl flex-1">
                    <input type="text" id="citySearchInput" onkeyup="filterByCity()" placeholder="Search title or keyword..." class="bg-white border border-stone-200 text-xs px-3 py-1.5 rounded outline-none focus:border-blue-900 flex-1 min-w-[120px]">
                    
                    <select id="typeSelect" onchange="filterProperties()" class="bg-white border border-stone-200 text-xs px-2 py-1.5 rounded outline-none cursor-pointer focus:border-blue-900">
                        <option value="">All Types</option>
                        <option value="apartment">Apartment</option>
                        <option value="hostel">Hostel</option>
                    </select>
                </div>

                <div class="bg-white border border-stone-200 text-xs px-2 py-1.5 rounded outline-none cursor-pointer focus:border-blue-900"><a href="renterhomepage.php" class="hover:text-blue-800 transition-colors">Home</a></div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1 bg-stone-100 p-1 border border-stone-200 rounded">
                        <button id="cardViewBtn" onclick="switchView('card')" class="px-3 py-1 bg-white text-slate-900 font-medium rounded text-[11px] shadow-sm transition-all">Card</button>
                        <button id="tableViewBtn" onclick="switchView('table')" class="px-3 py-1 text-stone-500 hover:text-slate-900 font-medium rounded text-[11px] transition-all">Table</button>
                    </div>
                    <div class="hidden sm:flex items-center gap-4 border-l border-stone-200 pl-4">
                        <a href="../auth/register.php" class="text-xs font-medium text-stone-600 hover:text-blue-900 hover:underline transition-all">Register</a>
                        <a href="../auth/login.php?redirect=homepage" class="px-4 py-1.5 text-xs font-serif font-medium text-amber-100 bg-blue-900 hover:bg-blue-950 border border-amber-700 shadow-sm transition-all">Sign In</a>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    <header class="bg-white border-b border-stone-200 py-14 text-center">
        <div class="max-w-4xl mx-auto px-6">
            <span class="inline-block uppercase tracking-widest text-[10px] font-semibold text-amber-800 border-b border-amber-800 pb-1 mb-4">Established Property Management</span>
            <h1 class="text-3xl sm:text-4xl font-serif font-normal text-stone-900 max-w-3xl mx-auto leading-tight">
                One platform. Perfect harmony for <span class="italic text-blue-900">Renters</span> & <span class="italic text-blue-900">Owners</span>.
            </h1>
            <div class="w-12 h-0.5 bg-amber-700 mx-auto my-4"></div>
            <p class="text-xs text-stone-500 font-serif">
                Quick Search: 
                <button onclick="quickSearch('ရန်ကုန်')" class="text-blue-900 underline mx-1 hover:text-amber-800">ရန်ကုန်</button> | 
                <button onclick="quickSearch('မန္တလေး')" class="text-blue-900 underline mx-1 hover:text-amber-800">မန္တလေး</button> | 
                <button onclick="quickSearch('AVAILABLE')" class="text-emerald-700 underline mx-1 hover:text-emerald-900 font-bold">Available Now</button>
            </p>
        </div>
    </header>

    <main class="flex-1 bg-[#faf9f6]">
        <div class="max-w-6xl px-6 w-full py-12 mx-auto">
            
            <div id="cardLayout" class="space-y-14 block">
                
                <div id="apartmentCardSection">
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
                            <div class="property-card bg-white border border-gray-200 rounded-md overflow-hidden hover:shadow-md transition-all flex flex-col justify-between opacity-<?= $isAvailable ? '100' : '80' ?>" 
                                 data-city="<?= htmlspecialchars(strtolower($row['city'])) ?>" 
                                 data-township="<?= htmlspecialchars(strtolower($row['township'])) ?>"
                                 data-type="apartment"
                                 data-status="<?= $statusText ?>">
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
                                    <a href="view_details.php?id=<?= (int)$row['id']; ?>&type=apartment" class="px-3 py-2 bg-white text-slate-800 border border-gray-300 rounded font-medium text-xs hover:bg-stone-50 transition-all text-center flex-1">Details</a>
                                    <?php if ($isAvailable): ?>
                                        <a href="../auth/login.php?redirect=contract" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">Book Lease</a>
                                    <?php else: ?>
                                        <button type="button" disabled class="px-4 py-2 bg-gray-100 text-gray-400 border border-gray-200 rounded font-medium text-xs cursor-not-allowed flex-1">Reserved</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="hostelCardSection">
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
                            <div class="property-card bg-white border border-gray-200 rounded-md overflow-hidden hover:shadow-md transition-all flex flex-col justify-between opacity-<?= $isAvailable ? '100' : '80' ?>" 
                                 data-city="<?= htmlspecialchars(strtolower($row['city'])) ?>" 
                                 data-township="<?= htmlspecialchars(strtolower($row['township'])) ?>"
                                 data-type="hostel"
                                 data-status="<?= $statusText ?>">
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
                                    <a href="view_details.php?id=<?= (int)$row['id']; ?>&type=hostel" class="px-3 py-2 bg-white text-slate-800 border border-gray-300 rounded font-medium text-xs hover:bg-stone-50 transition-all text-center flex-1">Details</a>
                                    <?php if ($isAvailable): ?>
                                        <a href="../auth/login.php?redirect=contract" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">Book Lease</a>
                                    <?php else: ?>
                                        <button type="button" disabled class="px-4 py-2 bg-gray-100 text-gray-400 border border-gray-200 rounded font-medium text-xs cursor-not-allowed flex-1">Reserved</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

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
                                <tr class="property-row hover:bg-stone-50/50 duration-150 transition-colors bg-white" 
                                    data-city="<?= htmlspecialchars(strtolower($row['city'])) ?>"
                                    data-township="<?= htmlspecialchars(strtolower($row['township'])) ?>"
                                    data-type="<?= htmlspecialchars($row['type']) ?>"
                                    data-status="<?= $badgeText ?>">
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
                                            <a href="view_details.php?id=<?= (int)$row['id']; ?>&type=<?= $row['type']; ?>" class="px-2.5 py-1 bg-white text-gray-700 border border-gray-300 rounded hover:bg-stone-50 transition-all font-medium">View</a>
                                            <?php if ($isAvail): ?>
                                                <a href="../auth/login.php?redirect=contract" class="px-2.5 py-1 bg-slate-900 text-white rounded hover:bg-slate-800 transition-all font-medium">Lease</a>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 bg-gray-50 text-gray-400 border border-gray-200 rounded font-medium cursor-not-allowed">Locked</span>
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

            <section class="mt-20 border-t border-stone-200 pt-14">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-3">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-amber-800">Get In Touch</span>
                        <h3 class="text-xl font-serif text-stone-900">Contact Management</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">လူကြီးမင်းတို့၏ အိမ်၊ ခြံ၊ မြေ နှင့် အဆောင်အခန်းများ ငှားရမ်းခြင်းကိစ္စရပ်များအတွက် ယုံကြည်စိတ်ချစွာ ဆက်သွယ်နိုင်ပါသည်။</p>
                    </div>
                    <div class="bg-white border border-stone-200 p-6 rounded space-y-4 shadow-sm md:col-span-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="space-y-1">
                                <span class="text-stone-400 font-bold block">📍 OFFICE ADDRESS</span>
                                <p class="text-stone-700 font-medium">အမှတ် (၁၂၀)၊ ကမ္ဘာအေးဘုရားလမ်း၊ ဗဟန်းမြို့နယ်၊ ရန်ကုန်မြို့။</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 font-bold block">📞 PHONE & HOTLINE</span>
                                <p class="text-stone-700 font-mono font-medium">+95 9 123 456 789<br>+95 1 234 567</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 block font-bold">✉️ EMAIL SUPPORT</span>
                                <p class="text-blue-900 font-medium underline">support@therentalhub.com</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 block font-bold">⏰ WORKING HOURS</span>
                                <p class="text-stone-700 font-medium">Mon - Sat | 9:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <footer class="bg-stone-900 text-stone-400 text-xs border-t border-stone-800 mt-auto">
        <div class="max-w-6xl mx-auto px-6 py-10">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-6 border-b border-stone-800 pb-8">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 bg-amber-700 flex items-center justify-center text-stone-100 font-serif font-bold text-base">R</div>
                    <span class="text-lg font-serif font-bold tracking-tight text-white">Rental<span class="text-amber-600 italic font-normal">Hub</span></span>
                </div>
                <div class="flex flex-wrap justify-center gap-6 text-[11px] font-medium tracking-wide">
                    <a href="renterhomepage.php" class="hover:text-white transition-colors">Home</a>
                    <a href="#apartmentCardSection" class="hover:text-white transition-colors">Apartments</a>
                    <a href="#hostelCardSection" class="hover:text-white transition-colors">Hostels</a>
                    <a href="../auth/login.php?redirect=homepage" class="hover:text-white transition-colors">Admin Panel</a>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 text-[11px] text-stone-500 font-serif">
                <p>&copy; <?= date('Y'); ?> The Rental Hub Co., Ltd. All rights reserved.</p>
                <p class="italic">Crafted for Quality Property Environments.</p>
            </div>
        </div>
    </footer>
</div>

<script>
    // Unified filter engine handles all structural selections on input change
    function filterProperties() {
        const textValue = document.getElementById('citySearchInput').value.toLowerCase().trim();
        const typeValue = document.getElementById('typeSelect').value.toLowerCase();

        // Filter Card View Elements
        const cards = document.querySelectorAll('.property-card');
        cards.forEach(card => {
            const cardText = card.textContent.toLowerCase();
            const type = card.getAttribute('data-type') || "";
            const status = card.getAttribute('data-status') || "";

            const matchText = cardText.includes(textValue) || status.toLowerCase().includes(textValue);
            const matchType = typeValue === "" || type === typeValue;

            card.style.display = (matchText && matchType) ? "" : "none";
        });

        // Filter Table View Elements
        const rows = document.querySelectorAll('.property-row');
        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const type = row.getAttribute('data-type') || "";
            const status = row.getAttribute('data-status') || "";

            const matchText = rowText.includes(textValue) || status.toLowerCase().includes(textValue);
            const matchType = typeValue === "" || type === typeValue;

            row.style.display = (matchText && matchType) ? "" : "none";
        });
    }

    function filterByCity() {
        filterProperties();
    }

    // Quick filter click linkages
    function quickSearch(keyword) {
        const searchInput = document.getElementById('citySearchInput');
        const typeSelect = document.getElementById('typeSelect');

        searchInput.value = keyword;
        typeSelect.value = "";
        filterProperties();
    }

    function switchView(viewType) {
        const cardLayout = document.getElementById('cardLayout');
        const tableLayout = document.getElementById('tableLayout');
        const cardViewBtn = document.getElementById('cardViewBtn');
        const tableViewBtn = document.getElementById('tableViewBtn');

        if (viewType === 'card') {
            cardLayout.classList.remove('hidden');
            tableLayout.classList.add('hidden');
            cardViewBtn.className = "px-3 py-1 bg-white text-slate-900 font-medium rounded text-[11px] shadow-sm transition-all";
            tableViewBtn.className = "px-3 py-1 text-stone-500 hover:text-slate-900 font-medium rounded text-[11px] transition-all";
        } else {
            cardLayout.classList.add('hidden');
            tableLayout.classList.remove('hidden');
            tableViewBtn.className = "px-3 py-1 bg-white text-slate-900 font-medium rounded text-[11px] shadow-sm transition-all";
            cardViewBtn.className = "px-3 py-1 text-stone-500 hover:text-slate-900 font-medium rounded text-[11px] transition-all";
        }
    }
</script>
</body>
</html>