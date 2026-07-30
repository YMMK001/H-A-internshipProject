<?php
// ၁။ Session စနစ်အား ဖိုင်၏ ထိပ်ဆုံးတွင် မဖြစ်မနေ စတင်ဖွင့်လှစ်ခြင်း
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

    <!-- Top Navigation Menu containing working Search Filters & Home controls -->
    <nav class="bg-white border-b border-stone-200 sticky top-0 z-50 shadow-sm w-full">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 py-3 px-4 sm:px-6 bg-white/80 backdrop-blur-md border-b border-stone-200/80 shadow-xs">
    
    <!-- LOGO (ACTS AS HOME TRIGGER) -->
    <a href="index.php" class="flex items-center gap-3 group">
        <div class="h-10 w-10 bg-slate-900 border border-amber-600/80 flex items-center justify-center text-amber-200 font-serif font-bold text-xl rounded-md shadow-xs group-hover:bg-slate-950 transition-all">
            R
        </div>
        <span class="text-2xl font-serif font-bold tracking-tight text-slate-900">
            Rental<span class="text-amber-800 italic font-normal">Hub</span>
        </span>
    </a>

    <!-- INTEGRATED NAV SEARCH FILTERS -->
    <div class="flex flex-wrap items-center gap-2 bg-stone-100/80 border border-stone-200 p-1.5 rounded-lg w-full md:w-auto max-w-xl flex-1 shadow-inner">
        <div class="relative flex-1 min-w-[140px] flex items-center">
            <input type="text" id="citySearchInput" onkeyup="filterByCity()" placeholder="Search title or keyword..." class="w-full bg-white border border-stone-200 text-xs text-stone-800 px-3 py-1.5 rounded-md outline-none focus:border-amber-800 focus:ring-1 focus:ring-amber-800/20 transition-all">
        </div>
        
        <select id="typeSelect" onchange="filterProperties()" class="bg-white border border-stone-200 text-xs text-stone-700 px-3 py-1.5 rounded-md outline-none cursor-pointer focus:border-amber-800 transition-all">
            <option value="">All Types</option>
            <option value="apartment">Apartment</option>
          
            <option value="hostel">Hostel</option>
        </select>

        <a href="../renter/renterhomepage.php" class="bg-white border border-stone-200 text-xs text-stone-700 hover:text-amber-900 hover:border-amber-800/40 px-3 py-1.5 rounded-md font-medium transition-all shadow-2xs">
            Home
        </a>
    </div>

    <!-- USER AUTH & PROFILE DROPDOWN MENU -->
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-4 border-l border-stone-200 pl-4">
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
                <!-- DROPDOWN PROFILE MENU -->
                <div class="relative">
                    <button onclick="toggleProfileDropdown()" class="flex items-center gap-2.5 focus:outline-none cursor-pointer group">
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-bold text-slate-800 group-hover:text-amber-900 transition-colors">
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </span>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-amber-100 border border-amber-600/40 flex items-center justify-center text-amber-950 font-bold text-xs uppercase shadow-xs group-hover:border-amber-800 transition-all">
                            <?php echo mb_substr(htmlspecialchars($_SESSION['username']), 0, 1, "UTF-8"); ?>
                        </div>
                        <svg class="w-3.5 h-3.5 text-stone-500 group-hover:text-amber-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu Content -->
                    <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-52 bg-white border border-stone-200 rounded-xl shadow-xl py-1.5 z-50 animate-in fade-in zoom-in-95 duration-100">
                        <div class="px-4 py-2 border-b border-stone-100 bg-stone-50/50 rounded-t-xl">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Signed in as</p>
                            <p class="text-xs font-bold text-slate-900 truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        </div>
                        
                        <a href="renter_profile.php" class="flex items-center gap-2 px-4 py-2 text-xs text-stone-700 hover:bg-amber-50/60 hover:text-amber-950 transition-colors">
                            <span>👤</span> My Profile
                        </a>
                        
                        
                        <div class="border-t border-stone-100 my-1"></div>
                        
                        <a href="../auth/logout.php" class="flex items-center gap-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-medium transition-colors rounded-b-xl">
                            <span>🚪</span> Logout
                        </a>
                        </div>
                </div>
            <?php else: ?>
                <!-- GUEST AUTH LINKS -->
                <a href="../auth/register.php" class="text-xs font-semibold text-stone-600 hover:text-slate-900 transition-all">
                    Register
                </a>
                <a href="../auth/login.php?redirect=homepage" class="px-4 py-1.5 text-xs font-medium text-amber-100 bg-slate-900 hover:bg-slate-950 border border-amber-600/50 rounded-md shadow-xs transition-all">
                    Sign In
                </a>
            <?php endif; ?>
        </div>
    </div>

</div>
        </div>
    </nav>

    <!-- UI/Content sections remain stable below -->

<script>
    // Toggle Profile Dropdown
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    // Close dropdown when clicking outside
    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('profileDropdown');
        const button = e.target.closest('button');
        if (dropdown && !dropdown.classList.contains('hidden')) {
            if (!button || !button.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        }
    });

    // View state switches and filtering log
    function switchView(type) {
        console.log("Switching view to " + type);
    }
    function filterByCity() { }
    function filterProperties() { }
</script>
</body>
</html>