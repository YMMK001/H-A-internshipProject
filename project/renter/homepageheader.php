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
    
    <!-- Top Navigation Menu containing working Search Filters & Home controls -->
    <nav class="bg-white border-b border-stone-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between py-4 items-center gap-4">
                
                <!-- Logo (Acts as Home Trigger) -->
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="h-10 w-10 bg-blue-900 border border-amber-600 flex items-center justify-center text-amber-100 font-serif font-bold text-xl">R</div>
                    <span class="text-2xl font-serif font-bold tracking-tight text-stone-900">Rental<span class="text-blue-900 italic font-normal">Hub</span></span>
                </a>

                <!-- INTEGRATED NAV SEARCH FILTERS -->
                <div class="flex flex-wrap items-center gap-2 bg-stone-50 border border-stone-200 p-1.5 rounded-md w-full md:w-auto max-w-xl flex-1">
                    <input type="text" id="citySearchInput" onkeyup="filterByCity()" placeholder="Search title or keyword..." class="bg-white border border-stone-200 text-xs px-3 py-1.5 rounded outline-none focus:border-blue-900 flex-1 min-w-[120px]">
                    
                    <select id="typeSelect" onchange="filterProperties()" class="bg-white border border-stone-200 text-xs px-2 py-1.5 rounded outline-none cursor-pointer focus:border-blue-900">
                        <option value="">All Types</option>
                        <option value="apartment">Apartment</option>
                        <option value="hostel">Hostel</option>
                    </select>
                </div>

                
                <div class="bg-white border border-stone-200 text-xs px-2 py-1.5 rounded outline-none cursor-pointer focus:border-blue-900"><a href="renterhomepage.php" class="hover:text-blue-800 transition-colors">Home</a></div>

                <!-- Interface View Switches & Auth Links -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1 bg-stone-100 p-1 border border-stone-200 rounded">
                        <button id="cardViewBtn" onclick="switchView('card')" class="px-3 py-1 bg-white text-slate-900 font-medium rounded text-[11px] shadow-sm transition-all">Card</button>
                        <button id="tableViewBtn" onclick="switchView('table')" class="px-3 py-1 text-stone-500 hover:text-slate-900 font-medium rounded text-[11px] transition-all">Table</button>
                    </div>
                    <div class="hidden sm:flex items-center gap-4 border-l border-stone-200 pl-4">
                        <a href="register.php" class="text-xs font-medium text-stone-600 hover:text-blue-900 hover:underline transition-all">Register</a>
                        <!-- Sign In from Header passes 'homepage' redirect query -->
                        <a href="../admin/login.php?redirect=homepage" class="px-4 py-1.5 text-xs font-serif font-medium text-amber-100 bg-blue-900 hover:bg-blue-950 border border-amber-700 shadow-sm transition-all">Sign In</a>
                    </div>
                </div>

            </div>
        </div>
    </nav>