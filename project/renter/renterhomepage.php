<?php
// ၁။ Session စနစ်အား ဖိုင်၏ ထိပ်ဆုံးတွင် မဖြစ်မနေ စတင်ဖွင့်လှစ်ခြင်း
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ၂။ Null Parameter အမှားများမတက်စေရန် Session Username အား စစ်ဆေးပြင်ဆင်ခြင်း
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "Renter";
}

// User Logged-in စစ်ဆေးခြင်း နှင့် User ID ရယူခြင်း
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['id']);
$loggedInUserId = $isLoggedIn ? ($_SESSION['user_id'] ?? $_SESSION['id']) : null;

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

// ၅။ FILTER & RE-INDEX UNIFIED DATA
$apartments = array_values(array_filter($properties, function($item) { return $item['type'] === 'apartment'; }));
$hostels    = array_values(array_filter($properties, function($item) { return $item['type'] === 'hostel'; }));



// =========================================================================
// ၆။ PAGINATION CONFIGURATION (5 Items Per Page)
// =========================================================================
$itemsPerPage = 6;

// Apartment Pagination Processing
$aptPage = isset($_GET['apt_page']) ? max(1, intval($_GET['apt_page'])) : 1;
$totalApartments = count($apartments);
$totalAptPages = max(1, ceil($totalApartments / $itemsPerPage));
$aptPage = min($aptPage, $totalAptPages); // Prevent out-of-bounds page numbers
$aptOffset = ($aptPage - 1) * $itemsPerPage;
$paginatedApartments = array_slice($apartments, $aptOffset, $itemsPerPage);

// Hostel Pagination Processing
$hostelPage = isset($_GET['hostel_page']) ? max(1, intval($_GET['hostel_page'])) : 1;
$totalHostels = count($hostels);
$totalHostelPages = max(1, ceil($totalHostels / $itemsPerPage));
$hostelPage = min($hostelPage, $totalHostelPages); // Prevent out-of-bounds page numbers
$hostelOffset = ($hostelPage - 1) * $itemsPerPage;
$paginatedHostels = array_slice($hostels, $hostelOffset, $itemsPerPage);
?>
<!DOCTYPE html>
<html lang="my" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Rental Hub - Find Your Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CLASSIC FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
        
        /* Subtle Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f5f2eb; }
        ::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a29e; }
    </style>
</head>
<body class="bg-[#fcfbf9] text-stone-800 min-h-screen flex flex-col antialiased">

    <!-- HEADER / NAVIGATION INCLUDE -->
    <?php 
        if (file_exists('homepageheader.php')) { 
            include 'homepageheader.php'; 
        } 
    ?>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="flex-1 w-full">
        
        <!-- HEADER / HERO INTRO -->
        <header class="relative bg-gradient-to-b from-[#fcfbf9] to-[#f4f1ea] rounded-3xl border border-stone-300/80 py-16 sm:py-20 text-center overflow-hidden font-serif shadow-2xl transition-all duration-500">
            <div class="absolute inset-0 z-0 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?q=80&w=1600&auto=format&fit=crop" 
                     alt="Modern Luxury Condo Interior" 
                     class="w-full h-full object-cover opacity-40 transform scale-105 transition-transform duration-1000 ease-out hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-[#f4f1ea] via-[#f4f1ea]/40 to-[#fcfbf9]/80"></div>
            </div>

            <div class="relative z-10 max-w-3xl mx-auto px-6">
                <div class="inline-flex items-center gap-2.5 bg-white/90 border border-emerald-600/30 px-4 py-1.5 rounded-full mb-6 shadow-md backdrop-blur-md">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-600"></span>
                    </span>
                    <span class="uppercase tracking-widest text-[10px] font-sans font-bold text-emerald-950">
                       Established Property Management
                    </span>
                </div>

               <h1 class="text-3xl sm:text-4xl md:text-5xl font-serif font-semibold text-stone-900 max-w-2xl mx-auto leading-snug title-classic tracking-tight">
                    One platform. Perfect harmony for <span class="italic font-normal text-amber-900">Renters</span> &amp; <span class="italic font-normal text-amber-900">Owners</span>.
                </h1>

                <div class="flex items-center justify-center gap-3 my-6">
                    <div class="w-12 h-[1px] bg-amber-800/40"></div>
                    <div class="h-1.5 w-1.5 rotate-45 border border-amber-800 bg-amber-900"></div>
                    <div class="w-12 h-[1px] bg-amber-800/40"></div>
                </div>

                <div class="mt-4 inline-flex flex-wrap items-center justify-center gap-3 bg-white/95 border border-stone-300/80 backdrop-blur-xl px-7 py-3.5 rounded-2xl shadow-xl text-xs font-sans text-stone-800 transform hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center gap-1.5 text-stone-500 font-bold uppercase tracking-wider text-[10px]">
                        <i class="fa-solid fa-city text-amber-900"></i>
                        <span>Quick Search:</span>
                    </div>
                    
                    <div class="h-4 w-[1px] bg-stone-300 hidden sm:block"></div>

                    <button onclick="quickSearch('Yangon')" class="text-stone-900 hover:text-amber-900 font-bold underline underline-offset-4 decoration-amber-800/30 hover:decoration-amber-900 transition-all px-1.5">
                        ရန်ကုန်
                    </button> 
                    <span class="text-stone-300">|</span>
                    
                    <button onclick="quickSearch('Mandalay')" class="text-stone-900 hover:text-amber-900 font-bold underline underline-offset-4 decoration-amber-800/40 hover:decoration-amber-900 transition-all px-1.5">
                        မန္တလေး
                    </button> 
                    <span class="text-stone-300">|</span>
                    
                    <button onclick="quickSearch('AVAILABLE')" class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 font-bold px-3 py-1 rounded-lg transition-all shadow-xs">
                        <i class="fa-solid fa-building text-[10px]"></i>
                        <span>Available</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- SHOWCASE SECTION -->
        <section class="max-w-6xl mx-auto px-6 w-full pt-10 pb-12">
            <div class="bg-[#f5f2eb] rounded-xl shadow-sm overflow-hidden border border-stone-300/60 text-stone-800">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 p-6 lg:p-8 items-center">
                    
                    <div class="lg:col-span-5 space-y-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[9px] uppercase font-bold tracking-wider bg-amber-700/10 text-amber-800 border border-amber-700/20">
                            <i class="fa-solid fa-feather-pointed mr-1 text-amber-700"></i> Elite Core Premium
                        </span>
                        <h2 class="text-2xl lg:text-3xl font-normal leading-tight text-stone-900 title-classic">
                            ယုံကြည်စိတ်ချရသော <br>
                            <span class="text-blue-900 font-semibold italic">အိမ်ရာစီမံခန့်ခွဲမှုဗဟို</span>
                        </h2>
                        <p class="text-stone-600 text-xs leading-relaxed font-light">
                            အဆောင်နှင့် တိုက်ခန်းငှားရမ်းခြင်းလုပ်ငန်းများကို ခေတ်မီစနစ်များဖြင့် ဒစ်ဂျစ်တယ်စနစ်သို့ ပြောင်းလဲလိုက်ပါ။ ပိုင်ရှင်နှင့် အိမ်ငှားကြား စာရွက်စာတမ်းရှုပ်ထွေးမှုများကို ဘေးကင်းလုံခြုံစွာ ဖြေရှင်းပေးပါသည်။
                        </p>
                        
                        <div class="space-y-3 pt-3 border-t border-stone-300/60">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Auto-Sync Digital Contracts</h4>
                                    <p class="text-[10px] text-stone-500">စာချုပ်သက်တမ်းကုန်ဆုံးပါက အခန်းများကို Available အဖြစ် အလိုအလျောက်ပြောင်းလဲပေးခြင်း။</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Polymorphic Core Tracking</h4>
                                    <p class="text-[10px] text-stone-500">တိုက်ခန်းများနှင့် အဆောင်ဒေတာများကို ပေါင်းစည်းထားသော ရလဒ်ထွက်စနစ်ဖြင့် စနစ်တကျပြသပေးခြင်း။</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7 grid grid-cols-12 gap-3 h-[220px] lg:h-[260px]">
                        <div class="col-span-7 relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80" 
                                 alt="Modern Room Architecture" 
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                            <span class="absolute bottom-2.5 left-2.5 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-2 py-0.5 rounded-sm text-[9px] uppercase tracking-wider font-bold text-amber-300">
                                Premium Spaces
                            </span>
                        </div>
                        <div class="col-span-5 grid grid-rows-2 gap-3">
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=400&q=80" 
                                     alt="Cozy Interior View" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Cozy Hostels
                                </span>
                            </div>
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=400&q=80" 
                                     alt="Verified Apartment" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Verified Units
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- MAIN PROPERTIES SECTION -->
        <div class="max-w-6xl px-6 w-full py-12 mx-auto">
            <div id="cardLayout" class="space-y-14 block">
                
                <!-- Apartments Section -->
                <div id="apartmentCardSection">
                    <h2 class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-5 border-b border-gray-200 pb-2">Apartments / တိုက်ခန်းများ</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if (empty($paginatedApartments)): ?>
                            <p class="text-gray-400 text-xs italic col-span-full py-4">ငှားရန်တိုက်ခန်းမရှိသေးပါ။</p>
                        <?php else: ?>
                            <?php foreach ($paginatedApartments as $row): 
                                $isAvailable = ($row['is_available'] == 1);
                                $statusText  = $isAvailable ? 'AVAILABLE' : 'LEASED';
                                $statusClass = $isAvailable ? 'text-emerald-700 border-emerald-200 bg-emerald-50' : 'text-stone-400 border-stone-200 bg-stone-50';
                                $imagePath   = (!empty($row['image_url'])) ? htmlspecialchars($row['image_url']) : 'uploads/default.jpg';
                                $opacityClass = $isAvailable ? 'opacity-100' : 'opacity-80';
                            ?>
                            <div class="property-card bg-white border border-gray-200 rounded-md overflow-hidden hover:shadow-md transition-all flex flex-col justify-between <?php echo $opacityClass; ?>" 
                                 data-city="<?= htmlspecialchars(strtolower($row['city'])) ?>" 
                                 data-township="<?= htmlspecialchars(strtolower($row['township'])) ?>"
                                 data-type="apartment"
                                 data-status="<?= $statusText ?>">
                                <div>
                                    <div class="relative h-48 w-full overflow-hidden bg-stone-100">
                                        <img src="<?= $imagePath ?>" alt="Apartment" class="w-full h-full object-cover grayscale-[15%] hover:grayscale-0 transition-all duration-300" onerror="this.onerror=null; this.src='uploads/default.jpg';">
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
                                        <?php if ($isLoggedIn): ?>
                                            <a href="rentercontract.php?property_id=<?= (int)$row['id']; ?>&type=apartment&user_id=<?= urlencode($loggedInUserId); ?>" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">Book Lease</a>
                                        <?php else: ?>
                                            <a href="../auth/login.php?redirect=contract" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">Book Lease</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button type="button" disabled class="px-4 py-2 bg-gray-100 text-gray-400 border border-gray-200 rounded font-medium text-xs cursor-not-allowed flex-1">Reserved</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Apartment Pagination Bar -->
                    <?php if ($totalAptPages > 1): ?>
                        <div class="flex items-center justify-center space-x-1 mt-8">
                            <a href="?apt_page=<?= max(1, $aptPage - 1) ?>&hostel_page=<?= $hostelPage ?>#apartmentCardSection" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs font-semibold text-gray-600 hover:bg-gray-50 <?= $aptPage <= 1 ? 'pointer-events-none opacity-50' : '' ?>">Prev</a>
                            
                            <?php for ($i = 1; $i <= $totalAptPages; $i++): ?>
                                <a href="?apt_page=<?= $i ?>&hostel_page=<?= $hostelPage ?>#apartmentCardSection" class="px-3 py-1 border rounded text-xs font-semibold <?= $i === $aptPage ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <a href="?apt_page=<?= min($totalAptPages, $aptPage + 1) ?>&hostel_page=<?= $hostelPage ?>#apartmentCardSection" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs font-semibold text-gray-600 hover:bg-gray-50 <?= $aptPage >= $totalAptPages ? 'pointer-events-none opacity-50' : '' ?>">Next</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Hostels Section -->
                <div id="hostelCardSection">
                    <h2 class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-5 border-b border-gray-200 pb-2">Hostels / အဆောင်များ</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if (empty($paginatedHostels)): ?>
                            <p class="text-gray-400 text-xs italic col-span-full py-4">ငှားရန်အဆောင်အခန်းမရှိသေးပါ။</p>
                        <?php else: ?>
                            <?php foreach ($paginatedHostels as $row): 
                                $isAvailable = ($row['is_available'] == 1);
                                $statusText  = $isAvailable ? 'AVAILABLE' : 'LEASED';
                                $statusClass = $isAvailable ? 'text-emerald-700 border-emerald-200 bg-emerald-50' : 'text-stone-400 border-stone-200 bg-stone-50';
                                $imagePath   = (!empty($row['image_url'])) ? htmlspecialchars($row['image_url']) : 'uploads/default.jpg';
                                $opacityClass = $isAvailable ? 'opacity-100' : 'opacity-80';
                            ?>
                            <div class="property-card bg-white border border-gray-200 rounded-md overflow-hidden hover:shadow-md transition-all flex flex-col justify-between <?php echo $opacityClass; ?>" 
                                 data-city="<?= htmlspecialchars(strtolower($row['city'])) ?>" 
                                 data-township="<?= htmlspecialchars(strtolower($row['township'])) ?>"
                                 data-type="hostel"
                                 data-status="<?= $statusText ?>">
                                <div>
                                    <div class="relative h-48 w-full overflow-hidden bg-stone-100">
                                        <img src="<?= $imagePath ?>" alt="Hostel" class="w-full h-full object-cover grayscale-[15%] hover:grayscale-0 transition-all duration-300" onerror="this.onerror=null; this.src='uploads/default.jpg';">
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
                                        <?php if ($isLoggedIn): ?>
                                            <a href="rentercontract.php?property_id=<?= (int)$row['id']; ?>&type=hostel&user_id=<?= urlencode($loggedInUserId); ?>" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">Book Lease</a>
                                        <?php else: ?>
                                            <a href="../auth/login.php?redirect=contract" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">Book Lease</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button type="button" disabled class="px-4 py-2 bg-gray-100 text-gray-400 border border-gray-200 rounded font-medium text-xs cursor-not-allowed flex-1">Reserved</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Hostel Pagination Bar -->
                    <?php if ($totalHostelPages > 1): ?>
                        <div class="flex items-center justify-center space-x-1 mt-8">
                            <a href="?hostel_page=<?= max(1, $hostelPage - 1) ?>&apt_page=<?= $aptPage ?>#hostelCardSection" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs font-semibold text-gray-600 hover:bg-gray-50 <?= $hostelPage <= 1 ? 'pointer-events-none opacity-50' : '' ?>">Prev</a>
                            
                            <?php for ($i = 1; $i <= $totalHostelPages; $i++): ?>
                                <a href="?hostel_page=<?= $i ?>&apt_page=<?= $aptPage ?>#hostelCardSection" class="px-3 py-1 border rounded text-xs font-semibold <?= $i === $hostelPage ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <a href="?hostel_page=<?= min($totalHostelPages, $hostelPage + 1) ?>&apt_page=<?= $aptPage ?>#hostelCardSection" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs font-semibold text-gray-600 hover:bg-gray-50 <?= $hostelPage >= $totalHostelPages ? 'pointer-events-none opacity-50' : '' ?>">Next</a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    
    
            <!-- Table Layout Section -->
            

            <!-- Contact Section -->
            <section class="mt-20 border-t border-stone-200 pt-14 max-w-6xl mx-auto">
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

    <!-- FOOTER -->
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
    function filterProperties() {
    const textValue = document.getElementById('citySearchInput').value.toLowerCase().trim();
    const selectValue = document.getElementById('typeSelect').value.toLowerCase().trim();

    // 1. Cards စစ်ထုတ်ခြင်း (.property-card)
    const cards = document.querySelectorAll('.property-card');
    cards.forEach(card => {
        const cardText = card.textContent.toLowerCase();
        const type = (card.getAttribute('data-type') || "").toLowerCase();
        const status = (card.getAttribute('data-status') || "").toLowerCase();

        // Search Input ဖြင့် စစ်ဆေးခြင်း
        const matchText = textValue === "" || cardText.includes(textValue) || status.includes(textValue);

        // Dropdown (Property Type သို့မဟုတ် Township) ဖြင့် စစ်ဆေးခြင်း
        let matchType = false;
        if (selectValue === "") {
            matchType = true;
        } else if (selectValue === "apartment" || selectValue === "hostel") {
            // Property Type ရွေးထားပါက data-type ဖြင့် တိုက်စစ်မည်
            matchType = (type === selectValue);
        } else {
            // Township (မြို့နယ်) ရွေးထားပါက Card ထဲရှိ စာသား (Township Name) ကို တိုက်စစ်မည်
            matchType = cardText.includes(selectValue);
        }

        card.style.display = (matchText && matchType) ? "" : "none";
    });

    // 2. Table Rows စစ်ထုတ်ခြင်း (.property-row)
    const rows = document.querySelectorAll('.property-row');
    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        const type = (row.getAttribute('data-type') || "").toLowerCase();
        const status = (row.getAttribute('data-status') || "").toLowerCase();

        const matchText = textValue === "" || rowText.includes(textValue) || status.includes(textValue);

        let matchType = false;
        if (selectValue === "") {
            matchType = true;
        } else if (selectValue === "apartment" || selectValue === "hostel") {
            matchType = (type === selectValue);
        } else {
            matchType = rowText.includes(selectValue);
        }

        row.style.display = (matchText && matchType) ? "" : "none";
    });
}

function filterByCity() {
    filterProperties();
}

function quickSearch(keyword) {
    const searchInput = document.getElementById('citySearchInput');
    const typeSelect = document.getElementById('typeSelect');

    searchInput.value = keyword;
    typeSelect.value = "";
    filterProperties();
}
</script>
</body>
</html>