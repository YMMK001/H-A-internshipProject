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

// =========================================================================
// NOTIFICATION DATA FETCHING
// =========================================================================
$unread_notif_count = 0;
$recent_notifications = [];

if (isset($_SESSION['user_id'])) {
    try {
        // Unread Notifications အရေအတွက် ယူခြင်း
        $notif_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $notif_count_stmt->execute([':user_id' => $_SESSION['user_id']]);
        $unread_notif_count = $notif_count_stmt->fetchColumn();

        // နောက်ဆုံးရ အသိပေးချက် ၅ ခု ရယူခြင်း
        $notif_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
        $notif_stmt->execute([':user_id' => $_SESSION['user_id']]);
        $recent_notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Notifications table မရှိသေးပါက error မတက်အောင် ထိန်းပေးထားခြင်း
    }
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
<body class="bg-[#fcfbf9] font-classic h-screen overflow-hidden flex flex-col text-gray-800">

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
                        <option value="">All Locations & Types</option>
                        <optgroup label="Townships">
                            <option value="Ahlone">Ahlone</option>
                            <option value="Bahan">Bahan</option>
                            <option value="Botahtaung">Botahtaung</option>
                            <option value="Dagon">Dagon</option>
                            <option value="Kamayut">Kamayut</option>
                            <option value="Kyauktada">Kyauktada</option>
                            <option value="Lanmadaw">Lanmadaw</option>
                            <option value="Latha">Latha</option>
                            <option value="Pabedan">Pabedan</option>
                            <option value="Sanchaung">Sanchaung</option>
                            <option value="Dawbon">Dawbon</option>
                            <option value="Mingala Taungyunt">Mingala Taungyunt</option>
                            <option value="Pazundaung">Pazundaung</option>
                            <option value="Tamwe">Tamwe</option>
                            <option value="Thaketa">Thaketa</option>
                            <option value="Thingangyun">Thingangyun</option>
                            <option value="Yankin">Yankin</option>
                            <option value="Hlaing">Hlaing</option>
                            <option value="Insein">Insein</option>
                            <option value="Mayangone">Mayangone</option>
                            <option value="Mingaladon">Mingaladon</option>
                            <option value="North Okkalapa">North Okkalapa</option>
                            <option value="Shwepyita">Shwepyita</option>
                            <option value="South Okkalapa">South Okkalapa</option>
                            <option value="Dagon Seikkan">Dagon Seikkan</option>
                            <option value="East Dagon">East Dagon</option>
                            <option value="North Dagon">North Dagon</option>
                            <option value="South Dagon">South Dagon</option>
                            <option value="Hlaingthaya East">Hlaingthaya East</option>
                            <option value="Hlaingthaya West">Hlaingthaya West</option>
                            <option value="Dala">Dala</option>
                            <option value="Seikkyi Kanaungto">Seikkyi Kanaungto</option>
                            <option value="Hlegu">Hlegu</option>
                            <option value="Hmawbi">Hmawbi</option>
                            <option value="Htantabin">Htantabin</option>
                            <option value="Taikkyi">Taikkyi</option>
                            <option value="Kawhmu">Kawhmu</option>
                            <option value="Kayan">Kayan</option>
                            <option value="Kungyangon">Kungyangon</option>
                            <option value="Kyauktan">Kyauktan</option>
                            <option value="Thanlyin">Thanlyin</option>
                            <option value="Thongwa">Thongwa</option>
                        </optgroup>
                        <optgroup label="Property Types">
                            <option value="apartment">Apartment</option>
                            <option value="hostel">Hostel</option>
                        </optgroup>
                    </select>

                    <a href="../renter/renterhomepage.php" class="bg-white border border-stone-200 text-xs text-stone-700 hover:text-amber-900 hover:border-amber-800/40 px-3 py-1.5 rounded-md font-medium transition-all shadow-2xs">
                        Home
                    </a>
                </div>

                <!-- USER AUTH & PROFILE DROPDOWN MENU -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 border-l border-stone-200 pl-4">
                        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
                            
                            <!-- NOTIFICATION DROPDOWN MENU -->
                            <div class="relative">
                                <button type="button" onclick="toggleNotificationDropdown()" class="relative p-2 text-stone-600 hover:text-amber-900 focus:outline-none transition-colors group cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <?php if ($unread_notif_count > 0): ?>
                                        <span class="absolute top-1 right-1 flex h-2 w-2">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                          <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                        </span>
                                    <?php endif; ?>
                                </button>

                                <!-- Notifications Box -->
                                <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-72 md:w-80 bg-white border border-stone-200 rounded-xl shadow-xl py-2 z-50">
                                    <div class="px-4 py-2 border-b border-stone-100 flex justify-between items-center bg-stone-50/50 rounded-t-xl">
                                        <span class="text-xs font-bold text-slate-800">အသိပေးချက်များ</span>
                                        <?php if ($unread_notif_count > 0): ?>
                                            <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold"><?= $unread_notif_count ?> မဖတ်ရသေး</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="max-h-64 overflow-y-auto divide-y divide-stone-100">
                                        <?php if (!empty($recent_notifications)): ?>
                                            <?php foreach ($recent_notifications as $notif): ?>
                                                <div class="p-3 text-xs <?= $notif['is_read'] ? 'bg-white' : 'bg-amber-50/40' ?> hover:bg-stone-50 transition-colors">
                                                    <p class="font-bold text-slate-900 mb-0.5"><?= htmlspecialchars($notif['title']) ?></p>
                                                    <p class="text-stone-600 line-clamp-2"><?= htmlspecialchars($notif['message']) ?></p>
                                                    <span class="text-[9px] text-stone-400 mt-1 block"><?= date('d M, h:i A', strtotime($notif['created_at'])) ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="p-4 text-center text-xs text-stone-400">
                                                အသိပေးချက် မရှိသေးပါ။
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <a href="notifications.php" class="block text-center text-xs font-semibold text-amber-800 hover:text-amber-950 py-2 border-t border-stone-100 bg-stone-50/30 rounded-b-xl">
                                        အသိပေးချက် အားလုံးကြည့်မည် →
                                    </a>
                                </div>
                            </div>

                            <!-- DROPDOWN PROFILE MENU -->
                            <div class="relative">
                                <button type="button" onclick="toggleProfileDropdown()" class="flex items-center gap-2.5 focus:outline-none cursor-pointer group">
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
                                <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-52 bg-white border border-stone-200 rounded-xl shadow-xl py-1.5 z-50">
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

<script>
    // Toggle Profile Dropdown
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        const notifDropdown = document.getElementById('notificationDropdown');
        if (notifDropdown) notifDropdown.classList.add('hidden'); 
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    // Toggle Notification Dropdown
    function toggleNotificationDropdown() {
        const notifDropdown = document.getElementById('notificationDropdown');
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) profileDropdown.classList.add('hidden'); 
        if (notifDropdown) {
            notifDropdown.classList.toggle('hidden');
        }
    }

    // Fixed Dropdown Close Handler (prevents closing when clicking inside dropdown content)
    window.addEventListener('click', function(e) {
        const profileDropdown = document.getElementById('profileDropdown');
        const notifDropdown = document.getElementById('notificationDropdown');

        if (!e.target.closest('#profileDropdown') && !e.target.closest('button[onclick*="toggleProfileDropdown"]')) {
            if (profileDropdown) profileDropdown.classList.add('hidden');
        }
        if (!e.target.closest('#notificationDropdown') && !e.target.closest('button[onclick*="toggleNotificationDropdown"]')) {
            if (notifDropdown) notifDropdown.classList.add('hidden');
        }
    });

    function filterByCity() { 
        // Implement client-side or AJAX filter logic here
    }
    function filterProperties() { 
        // Implement property/township filter logic here
    }
</script>
</body>
</html>