<?php
// 1. Start the PHP Session to track the logged-in renter
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Authentication Guard
if (!isset($_SESSION['user_id'])) {
    // TEMPORARY FALLBACK FOR TESTING: Force ID 1 if no session exists
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = "John Doe";
}

$logged_in_user_id = $_SESSION['user_id'];

// 3. Database Connection Configuration
$host     = 'localhost';
$dbname   = 'intern_test'; // ⚠️ Change this to your actual database name
$username = 'root';        // ⚠️ Change this to your database username
$password = '';            // ⚠️ Change this to your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 4. Query to fetch either Apartment or Hostel Room details dynamically
$sql = "SELECT 
            c.id AS contract_id,
            c.start_date,
            c.end_date,
            c.total_deposit_amount,
            CASE 
                WHEN c.apartment_id IS NOT NULL THEN 'Apartment'
                WHEN c.hostel_room_id IS NOT NULL THEN 'Hostel Room'
                ELSE 'Unknown'
            END AS space_type,
            CASE 
                WHEN c.apartment_id IS NOT NULL THEN CONCAT('Floor: ', COALESCE(a.floor_level, 'N/A'))
                WHEN c.hostel_room_id IS NOT NULL THEN CONCAT('Room: ', hr.room_num, IF(hr.sub_unit IS NOT NULL AND hr.sub_unit != '', CONCAT(' (', hr.sub_unit, ')'), ''))
                ELSE 'N/A'
            END AS unit_details,
            CASE 
                WHEN c.apartment_id IS NOT NULL THEN a.apartment_price
                WHEN c.hostel_room_id IS NOT NULL THEN hr.monthly_price
                ELSE 0.00
            END AS monthly_rent,
            COALESCE(rh.title, 'Unnamed Property') AS house_title,
            COALESCE(rh.full_address, 'No Address') AS full_address
        FROM contracts c
        LEFT JOIN apartments a ON c.apartment_id = a.id
        LEFT JOIN hostel_rooms hr ON c.hostel_room_id = hr.id
        LEFT JOIN rental_houses rh ON rh.id = COALESCE(a.rental_house_id, hr.rental_house_id)
        WHERE c.user_id = :user_id
        ORDER BY c.start_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $logged_in_user_id]);
$contracts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rental Contracts List - Classic Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-800 font-classic antialiased min-h-screen relative overflow-hidden">

<!-- Combined Header / Navbar Stack Context -->
<div class="w-full bg-[#1b1816] text-white sticky top-0 z-50 shadow-md">
    <?php if (file_exists('renterheader.php')) { 
        include 'renterheader.php'; 
    } else { ?>
        <!-- Fallback Navigation Matching image_eca008.png Visual Structure if header is dynamic -->
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <div class="text-xl font-serif text-amber-500 font-bold tracking-wider">Rental<span class="text-white">Hub</span></div>
                <nav class="hidden md:flex space-x-6 text-sm font-medium tracking-wide text-stone-300">
                    <a href="#" class="text-white border-b-2 border-amber-500 pb-1">Overview</a>
                    <a href="#" class="hover:text-white transition">Lease Agreements</a>
                    <a href="#" class="hover:text-white transition">Payment Ledgers</a>
                </nav>
            </div>
            <div class="hidden md:flex items-center space-x-4">
                <button class="bg-blue-700 text-xs font-bold uppercase tracking-wider px-4 py-2 hover:bg-blue-800 rounded-sm">Find Accommodation</button>
                <span class="text-xs text-stone-400 font-mono">Resident: John Doe</span>
                <button class="border border-stone-600 px-3 py-1 text-xs uppercase tracking-wider hover:bg-white/5">Sign Out</button>
            </div>
            <!-- Mobile Bar Menu Trigger Button -->
            <button onclick="toggleMobileMenu()" class="md:hidden text-stone-300 hover:text-white text-lg p-2">
                ☰
            </button>
        </div>
    <?php } ?>

    <!-- Unified Mobile Overlay Dropdown Menu (Overlay Style with Background Blur) -->
    <div id="mobileDropdownMenu" class="hidden absolute top-full left-0 w-full min-h-screen bg-stone-900/60 backdrop-blur-md z-50 transition-all duration-200">
        <div class="bg-[#1b1816] w-full p-6 border-t border-stone-800 shadow-xl space-y-4">
            <p class="text-[10px] uppercase font-bold tracking-widest text-stone-500 border-b border-stone-800 pb-2">Navigation Links</p>
            <nav class="flex flex-col space-y-3 font-medium text-sm text-stone-300">
                <a href="#" class="text-white bg-stone-800/40 px-3 py-2 rounded-sm">Overview</a>
                <a href="renter_contract.php" class="hover:text-white px-3 py-1 transition">Contracts</a>
                <a href="renter_payment.php" class="hover:text-white px-3 py-1 transition">Payment Ledgers</a>
                
                <a href="renterhomepage" class="text-red-400 hover:text-red-300 px-3 py-1 transition border-t border-stone-800 pt-3">Sign Out</a>
            </nav>
        </div>
    </div>
</div>

<!-- Main App Content Container wrapper layout -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        
    <!-- Header Section -->
    <div class="border-b border-stone-200 pb-5">
        <h1 class="text-2xl font-bold text-stone-900 tracking-tight uppercase title-classic">Contract Directory</h1>
        <p class="text-stone-400 text-xs uppercase tracking-wider mt-1 font-medium">မိမိ၏ လက်ရှိနှင့် ယခင်ငှားရမ်းခဲ့သော စာချုပ်ချုပ်ဆိုမှုမှတ်တမ်းများစာရင်း</p>
    </div>

    <?php if (empty($contracts)): ?>
        <!-- Empty State Container Layout -->
        <div class="bg-white rounded-sm p-16 text-center border border-stone-200 shadow-sm max-w-xl mx-auto">
            <p class="text-stone-400 text-xs uppercase tracking-wider font-semibold">ချုပ်ဆိုထားသော စာချုပ်မှတ်တမ်း မရှိသေးပါ။</p>
            <div class="mt-2 text-stone-400 font-mono text-[11px]">ID Reference: #<?= htmlspecialchars($logged_in_user_id) ?></div>
        </div>
    <?php else: ?>
        <!-- Classic Layout Table Container -->
        <div class="bg-white rounded-sm border border-stone-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto overflow-y-auto max-h-[400px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-stone-50/70 border-b border-stone-200 text-[10px] font-bold uppercase tracking-widest text-stone-500">
                            <th class="py-4 px-6">ID</th>
                            <th class="py-4 px-6">Property / Address</th>
                            <th class="py-4 px-6">Space Unit</th>
                            <th class="py-4 px-6">Duration</th>
                            <th class="py-4 px-6">Rent / Deposit</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-xs">
                        <?php foreach ($contracts as $contract): 
                            $today = date('Y-m-d');
                            $is_active = ($today >= $contract['start_date'] && $today <= $contract['end_date']);
                        ?>
                            <tr class="odd:bg-white even:bg-stone-50/40 hover:bg-stone-50/80 transition-colors duration-150">
                                <!-- Contract ID -->
                                <td class="py-5 px-6 font-mono font-bold text-stone-400">
                                    #<?= htmlspecialchars($contract['contract_id']) ?>
                                </td>
                                
                                <!-- Property Details -->
                                <td class="py-5 px-6 max-w-xs">
                                    <div class="font-bold text-stone-900 truncate text-[14px] tracking-tight title-classic uppercase"><?= htmlspecialchars($contract['house_title']) ?></div>
                                    <div class="text-[11px] text-stone-400 truncate mt-1">📍 <?= htmlspecialchars($contract['full_address']) ?></div>
                                </td>
                                
                                <!-- Space Unit Type -->
                                <td class="py-5 px-6">
                                    <span class="font-bold text-stone-700 block text-[13px]"><?= htmlspecialchars($contract['space_type']) ?></span>
                                    <span class="text-[11px] text-stone-500 font-medium mt-0.5 block"><?= htmlspecialchars($contract['unit_details']) ?></span>
                                </td>
                                
                                <!-- Duration Dates -->
                                <td class="py-5 px-6 font-medium text-stone-600 whitespace-nowrap">
                                    <div class="font-semibold text-stone-800"><?= date('M d, Y', strtotime($contract['start_date'])) ?></div>
                                    <div class="text-[11px] text-stone-400 mt-0.5">to <?= date('M d, Y', strtotime($contract['end_date'])) ?></div>
                                </td>
                                
                                <!-- Rent & Deposit Prices -->
                                <td class="py-5 px-6 whitespace-nowrap">
                                    <div class="font-bold text-stone-900 text-[13px]"><?= number_format($contract['monthly_rent']) ?> <span class="text-[10px] text-stone-400 font-normal">MMK/လ</span></div>
                                    <div class="text-[11px] text-stone-500 mt-0.5">စရန်ငွေ: <?= number_format($contract['total_deposit_amount']) ?> MMK</div>
                                </td>
                                
                                <!-- Status Badges -->
                                <td class="py-5 px-6 whitespace-nowrap">
                                    <?php if ($is_active): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider bg-stone-100 text-stone-800 border border-stone-300">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider bg-gray-50 text-gray-400 border border-gray-200">
                                            Expired
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Action Button -->
                                <td class="py-5 px-6 text-right whitespace-nowrap">
                                    <a href="installment_list.php?contract_id=<?= $contract['contract_id'] ?>" 
                                       class="inline-flex items-center justify-center px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-stone-900 bg-stone-50 hover:bg-stone-100 border border-stone-300 rounded-sm transition-all shadow-sm">
                                        💵 Installments
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileDropdownMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }
</script>
</body>
</html>