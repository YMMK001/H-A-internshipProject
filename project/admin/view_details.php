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

// 2. GET Parameters ကို ဖတ်ယူပြီး Validation လုပ်ခြင်း
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type    = isset($_GET['type']) ? trim($_GET['type']) : '';

$type_lower = strtolower($type); 

if ($item_id <= 0 || !in_array($type_lower, ['apartment', 'hostel'])) {
    die("Error: တောင်းဆိုမှု မှားယွင်းနေပါသည်။");
}

$details = null;

if ($type_lower === 'apartment') {
    $sql = "
        SELECT 
            h.*, 
            a.id AS apartment_id,
            a.max_occupy, 
            a.floor_level, 
            a.apartment_price AS price, 
            a.deposit_amount, 
            a.is_available,
            c.end_date AS reopen_date
        FROM apartments a
        INNER JOIN rental_houses h ON a.rental_house_id = h.id
        LEFT JOIN contracts c ON a.id = c.apartment_id AND c.status = 'active'
        WHERE a.id = :item_id
        ORDER BY c.id DESC LIMIT 1
    ";
} else {
    $sql = "
        SELECT 
            h.*, 
            r.id AS room_id,
            r.room_num, 
            r.room_type, 
            r.sub_unit, 
            r.monthly_price AS price, 
            r.deposit_amount, 
            r.is_available,
            c.end_date AS reopen_date
        FROM hostel_rooms r
        INNER JOIN rental_houses h ON r.rental_house_id = h.id
        LEFT JOIN contracts c ON r.id = c.hostel_room_id AND c.status = 'active'
        WHERE r.id = :item_id
        ORDER BY c.id DESC LIMIT 1
    ";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([':item_id' => $item_id]);
$details = $stmt->fetch();

if (!$details) {
    die("Error: ရှာဖွေနေသော အိမ်/အခန်း အချက်အလက် မတွေ့ရှိပါ။");
}

$house_id = $details['id']; 
$img_sql = "SELECT image_url FROM rental_house_images WHERE rental_house_id = :house_id ORDER BY id ASC";
$img_stmt = $pdo->prepare($img_sql);
$img_stmt->execute([':house_id' => $house_id]);
$images = $img_stmt->fetchAll(PDO::FETCH_COLUMN);

$is_available = (int)$details['is_available'] === 1;
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($details['title']) ?> - Classic Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#faf9f6] font-classic flex h-screen text-stone-800 overflow-hidden">

    <!-- Stable Sidebar Area -->
    
<div class="sm:hidden mb-4">
        <button onclick="toggleMobileMenu()" class="bg-[#292515] text-white text-xs font-serif px-3 py-2 shadow-sm border border-stone-700">
            ☰ Menu
        </button>
      </div>
    <!-- Independent Scroll Main Content Area -->
    <div class="flex-1 h-full overflow-y-auto py-10 px-4">
        
        <div class="max-w-3xl mx-auto space-y-6">
            
            <!-- Minimal Back Navigation Button -->
            <div>
                <a href="javascript:history.back()" class="text-xs font-bold uppercase tracking-widest text-stone-500 hover:text-stone-950 transition-colors flex items-center gap-2">
                    ← Back / နောက်သို့
                </a>
            </div>

            <!-- Master Classic Structural Layout Container -->
            <div class="bg-white rounded-md shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- IMAGE SLIDESHOW (CAROUSEL) SECTION -->
                <div class="relative w-full h-[320px] md:h-[380px] bg-stone-100 group overflow-hidden border-b border-gray-200">
                    <?php if (!empty($images)): ?>
                        <!-- Slides Container -->
                        <div id="slides-container" class="w-full h-full flex transition-transform duration-500 ease-out">
                            <?php foreach ($images as $img_url): ?>
                                <div class="w-full h-full flex-shrink-0">
                                    <img src="<?= htmlspecialchars($img_url) ?>" alt="Property Image" class="w-full h-full object-cover">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Classic Slide Controls -->
                        <button onclick="moveSlide(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 border border-gray-200 text-stone-900 w-10 h-10 rounded flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity font-mono text-sm focus:outline-none z-10 hover:bg-stone-50 shadow-sm">
                            PREV
                        </button>
                        <button onclick="moveSlide(1)" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 border border-gray-200 text-stone-900 w-10 h-10 rounded flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity font-mono text-sm focus:outline-none z-10 hover:bg-stone-50 shadow-sm">
                            NEXT
                        </button>

                        <!-- Minimal Corner Counter Indicators -->
                        <div class="absolute bottom-4 right-4 bg-stone-900/90 text-white font-mono text-[10px] tracking-widest px-2.5 py-1 rounded shadow-sm z-10 uppercase">
                            Gallery Slides
                        </div>
                        
                        <!-- Dots Indicators -->
                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5 z-10">
                            <?php foreach ($images as $index => $img_url): ?>
                                <button onclick="currentSlide(<?= $index ?>)" class="slide-dot w-2 h-2 rounded-full bg-stone-400/40 hover:bg-stone-800 transition-all focus:outline-none" data-slide="<?= $index ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center text-stone-400 bg-stone-50 font-medium tracking-widest text-xs uppercase">
                            <span>No Images Available</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Premium Header Title Block -->
                <div class="p-6 md:p-8 bg-stone-50 border-b border-gray-200 relative">
                    <div class="absolute top-6 right-6">
                        <?php if ($type_lower === 'apartment'): ?>
                            <span class="bg-white text-stone-800 border border-stone-300 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-sm">Apartment</span>
                        <?php else: ?>
                            <span class="bg-white text-stone-800 border border-stone-300 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-sm">Hostel Room</span>
                        <?php endif; ?>
                    </div>

                    <h1 class="text-xl md:text-2xl font-bold tracking-tight text-stone-900 title-classic max-w-[75%] uppercase"><?= htmlspecialchars($details['title']) ?></h1>
                    <p class="text-stone-400 text-xs mt-2 flex items-center gap-1 font-medium tracking-wide">
                        📍 <?= htmlspecialchars($details['full_address'] ?? '') ?>၊ <?= htmlspecialchars($details['township']) ?>မြို့နယ်။
                    </p>
                </div>

                <!-- Content Matrix Segment -->
                <div class="p-6 md:p-8 space-y-8">
                    
                    <!-- Pricing Metric Widgets -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-stone-50 border border-gray-200 rounded-sm">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block mb-1">လစဉ်ငှားရမ်းခ / Rent Price</span>
                            <span class="text-lg font-bold text-stone-900"><?= number_format($details['price']) ?></span> <span class="text-xs font-bold text-stone-500">MMK</span>
                        </div>
                        <div class="p-4 bg-stone-50 border border-gray-200 rounded-sm">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block mb-1">အာမခံစရန်ငွေ / Deposit</span>
                            <span class="text-lg font-bold text-stone-900"><?= number_format($details['deposit_amount']) ?></span> <span class="text-xs font-bold text-stone-500">MMK</span>
                        </div>
                        <div class="p-4 bg-stone-50 border border-gray-200 rounded-sm flex flex-col justify-center">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block mb-1">လက်ရှိအခြေအနေ / Status</span>
                            <div class="mt-0.5">
                                <?php if ($is_available): ?>
                                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wide border border-emerald-300 text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-sm">
                                        AVAILABLE / အားပါသည်
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wide border border-rose-300 text-rose-800 bg-rose-50 px-2.5 py-0.5 rounded-sm">
                                        OCCUPIED / ငှားရမ်းပြီး
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reopen Warning Prompt -->
                    <?php if (!$is_available): ?>
                        <div class="p-4 bg-stone-50 border border-stone-200 rounded-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-stone-800">📅 စာချုပ်သက်တမ်းပြည့်မြောက်မည့်ရက် (Reopen Date)</h4>
                                <p class="text-[11px] text-stone-400">ဤအခန်းသည် စာချုပ်သက်တမ်းအရ အောက်ပါရက်စွဲတွင် ပြန်လည်အားလပ်လာနိုင်ပါသည်။</p>
                            </div>
                            <div class="bg-white border border-stone-300 px-3 py-1.5 rounded-sm text-center min-w-[120px]">
                                <span class="block text-sm font-bold text-stone-900 font-mono">
                                    <?= !empty($details['reopen_date']) ? date('d-M-Y', strtotime($details['reopen_date'])) : 'N/A' ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Structural Specifications List -->
                    <div class="space-y-2">
                        <h3 class="text-xs font-bold text-stone-900 uppercase tracking-widest border-b border-gray-100 pb-1">📐 Specifications / အခန်းအသေးစိတ်</h3>
                        <div class="bg-white border border-gray-200 rounded-sm divide-y divide-gray-100">
                            <?php if ($type_lower === 'apartment'): ?>
                                <div class="p-3.5 flex justify-between text-xs font-medium"><span class="text-stone-400 uppercase tracking-wider">Floor Level / အထပ်အဆင့်</span> <span class="font-bold text-stone-800"><?= htmlspecialchars($details['floor_level']) ?></span></div>
                                <div class="p-3.5 flex justify-between text-xs font-medium"><span class="text-stone-400 uppercase tracking-wider">Max Occupants / နေထိုင်နိုင်မည့် ဦးရေ</span> <span class="font-bold text-stone-800"><?= htmlspecialchars($details['max_occupy']) ?> ဦး</span></div>
                            <?php else: ?>
                                <div class="p-3.5 flex justify-between text-xs font-medium"><span class="text-stone-400 uppercase tracking-wider">Room Number / အဆောင်ခန်းနံပါတ်</span> <span class="font-bold text-stone-800">Room <?= htmlspecialchars($details['room_num']) ?></span></div>
                                <div class="p-3.5 flex justify-between text-xs font-medium"><span class="text-stone-400 uppercase tracking-wider">Room Type / အခန်းအမျိုးအစား</span> <span class="font-bold text-stone-800"><?= htmlspecialchars($details['room_type']) ?></span></div>
                                <div class="p-3.5 flex justify-between text-xs font-medium"><span class="text-stone-400 uppercase tracking-wider">Sub-Unit / အခန်းခွဲယူနစ်</span> <span class="font-bold text-stone-800"><?= $details['sub_unit'] ? htmlspecialchars($details['sub_unit']) : '-' ?></span></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Room Detailed Description -->
                    <?php if (!empty($details['description'])): ?>
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold text-stone-900 uppercase tracking-widest border-b border-gray-100 pb-1">📝 Description / ဖော်ပြချက်</h3>
                            <p class="text-xs text-stone-600 bg-stone-50/60 border border-gray-100 p-4 rounded-sm leading-relaxed whitespace-pre-line">
                                <?= htmlspecialchars($details['description']) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Amenities Collection Blocks -->
                    <div class="space-y-2">
                        <h3 class="text-xs font-bold text-stone-900 uppercase tracking-widest border-b border-gray-100 pb-1">✨ Amenities / ပါဝင်သော ဝန်ဆောင်မှုများ</h3>
                        <div class="flex flex-wrap gap-2 pt-1">
                            <?php 
                            $amenities = array_filter(explode(',', $details['amenities'] ?? ''));
                            if (!empty($amenities)):
                                foreach ($amenities as $amenity): 
                            ?>
                                    <span class="text-[11px] font-bold uppercase tracking-wider bg-stone-50 text-stone-700 px-3 py-1.5 rounded-sm border border-stone-200">
                                        ✓ <?= htmlspecialchars(trim($amenity)) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-xs text-stone-400 italic">သီးသန့်ဖော်ပြထားသော ဝန်ဆောင်မှုမရှိပါ။</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Footer Action Callout Box -->
                    <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="text-[10px] text-stone-400 uppercase tracking-wider font-medium">
                            * စာချုပ်သစ်ချုပ်ဆိုရန် လျှောက်ထားလွှာစာမျက်နှာသို့ ဆက်သွားမည်။
                        </div>
                        <div>
                            <?php if ($is_available): ?>
                                <a href="apply_contract.php?select_unit=<?= $type_lower ?>_<?= $item_id ?>" 
                                   class="bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs px-6 py-3 rounded-sm uppercase tracking-wider transition-all shadow-sm inline-block text-center">
                                    🚀 Apply Contract / စာချုပ်စတင်လျှောက်ထားမည်
                                </a>
                            <?php else: ?>
                                <button disabled class="bg-stone-200 text-stone-400 font-bold text-xs px-6 py-3 rounded-sm uppercase tracking-wider cursor-not-allowed">
                                    🔒 Unavailable / ငှားရမ်းမှုပိတ်ထားပါသည်
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
    </div>

    <!-- SLIDESHOW JAVASCRIPT -->
    <script>
        let currentSlideIndex = 0;
        const totalSlides = <?= count($images); ?>;

        function updateSlidePosition() {
            const container = document.getElementById('slides-container');
            if(container) {
                container.style.transform = `translateX(-${currentSlideIndex * 100}%)`;
            }
            
            const dots = document.querySelectorAll('.slide-dot');
            dots.forEach((dot, idx) => {
                if (idx === currentSlideIndex) {
                    dot.className = "slide-dot w-4 h-2 rounded-full bg-stone-800 transition-all focus:outline-none";
                } else {
                    dot.className = "slide-dot w-2 h-2 rounded-full bg-stone-400/40 transition-all focus:outline-none";
                }
            });
        }

        function moveSlide(direction) {
            currentSlideIndex += direction;
            if (currentSlideIndex >= totalSlides) {
                currentSlideIndex = 0;
            } else if (currentSlideIndex < 0) {
                currentSlideIndex = totalSlides - 1;
            }
            updateSlidePosition();
        }

        function currentSlide(index) {
            currentSlideIndex = index;
            updateSlidePosition();
        }

        document.addEventListener('DOMContentLoaded', () => {
            if(totalSlides > 0) updateSlidePosition();
        });
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