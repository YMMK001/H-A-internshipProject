<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Renter');
// Dynamic fallback to user_id = 16 for testing purposes
$renter_id = $_SESSION['user_id'] ?? 16; 

$active_contract_id = null;
$rentals = [];

try {
    $host        = 'localhost';
    $db_name     = 'intern_test'; 
    $username_db = 'root';              
    $password_db = ''; 

    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Fetch any active contract ID for this user to link to the global Installments page
    $stmt = $db->prepare("SELECT id FROM contracts WHERE user_id = :user_id LIMIT 1");
    $stmt->execute([':user_id' => $renter_id]);
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contract) {
        $active_contract_id = $contract['id'];
    }

    // 2. Main Query: Fetch all private properties (Apartments / Hostels) rented by this specific renter
    $query = "
        SELECT 
            c.id AS contract_id, c.start_date, c.end_date, c.total_deposit_amount,
            a.id AS apartment_id, a.floor_level, a.apartment_price,
            h.id AS hostel_room_id, h.room_num, h.room_type, h.monthly_price,
            rh.title AS house_title, rh.city, rh.township, rh.full_address, rh.rentable_type
        FROM contracts c
        LEFT JOIN apartments a ON c.apartment_id = a.id
        LEFT JOIN hostel_rooms h ON c.hostel_room_id = h.id
        LEFT JOIN rental_houses rh ON (a.rental_house_id = rh.id OR h.rental_house_id = rh.id)
        WHERE c.user_id = :renter_id
        ORDER BY c.id DESC
    ";

    $stmt_rentals = $db->prepare($query);
    $stmt_rentals->execute([':renter_id' => $renter_id]);
    $rentals = $stmt_rentals->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalHub - Tenant Dashboard Overview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-stone-900 antialiased font-classic min-h-screen relative">

<!-- Combined Header / Navbar Stack Context -->
<div class="w-full bg-[#1b1816] text-white sticky top-0 z-50 shadow-md">
    <?php if (file_exists('renterheader.php')) { 
        include 'renterheader.php'; 
    } else { ?>
        <!-- Fallback Navigation Matching UI Layout Guidelines -->
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <div class="text-xl font-serif text-amber-500 font-bold tracking-wider">Rental<span class="text-white">Hub</span></div>
                <nav class="hidden md:flex space-x-6 text-sm font-medium tracking-wide text-stone-300">
                    <a href="#" class="text-white border-b-2 border-amber-500 pb-1">History</a>
                    <a href="renter_contract.php" class="hover:text-white transition">Lease Agreements</a>
                    <a href="payment_history.php" class="hover:text-white transition">Payment Ledgers</a>
                </nav>
            </div>
            <div class="hidden md:flex items-center space-x-4">
                <button class="bg-blue-700 text-xs font-bold uppercase tracking-wider px-4 py-2 hover:bg-blue-800 rounded-sm">Find Accommodation</button>
                <span class="text-xs text-stone-400 font-mono">Resident: <?= $username ?></span>
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
                <a href="renter_contract.php" class="hover:text-white px-3 py-1 transition">Lease Agreements</a>
                <a href="payment_history.php" class="hover:text-white px-3 py-1 transition">Payment Ledgers</a>
                <a href="#" class="hover:text-white px-3 py-1 transition">Find Accommodation</a>
                <a href="#" class="text-red-400 hover:text-red-300 px-3 py-1 transition border-t border-stone-800 pt-3">Sign Out</a>
            </nav>
        </div>
    </div>
</div>

<!-- Main App Content Container Layout -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        
    <!-- Welcome Header Section -->
    <div class="border-b border-stone-200 pb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900 title-classic uppercase">Welcome Back, <?= $username ?></h1>
            <p class="text-stone-400 text-xs uppercase tracking-wider mt-1.5 font-medium">Manage your privately rented properties and monitor scheduled monthly payments.</p>
        </div>
        <div class="flex items-center gap-3 bg-white border border-stone-200 rounded-sm px-4 py-2 self-start md:self-auto shadow-sm">
            <div class="h-8 w-8 bg-stone-900 text-amber-500 flex items-center justify-center font-serif font-bold text-xs rounded-sm">
                <?php echo htmlspecialchars(mb_strtoupper(!empty($username) ? mb_substr($username, 0, 2) : 'U')); ?>
            </div>
            <div>
                <p class="text-[10px] uppercase font-bold tracking-wider text-stone-400 leading-none">Resident Token</p>
                <p class="text-xs font-mono font-bold text-stone-700 mt-1">ID: #<?= htmlspecialchars((string)($renter_id ?? '')) ?></p>
            </div>
        </div>
    </div>

    <!-- Section Split Header Line -->
    <div class="flex items-center justify-between pt-2">
        <h2 class="text-xs uppercase tracking-widest text-stone-400 font-bold flex items-center gap-2">
            Rentals Directory / ငှားရမ်းထားသော စာရင်းများ
        </h2>
        <span class="text-[10px] tracking-wider font-bold bg-stone-900 px-2.5 py-1 rounded-sm text-white uppercase shadow-sm">
            Active Records: <?= count($rentals) ?>
        </span>
    </div>

    <!-- Scrollable Content Layout Grid -->
    <div class="space-y-6">
        <?php if (!empty($rentals)): ?>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                <?php foreach ($rentals as $row): ?>
                    <?php 
                        $is_apartment = !empty($row['apartment_id']);
                        $type_badge = $is_apartment ? 'Apartment' : 'Hostel Room';
                        $price = $is_apartment ? ($row['apartment_price'] ?? 0) : ($row['monthly_price'] ?? 0);
                        
                        if ($is_apartment) {
                            $unit_detail = "Floor: " . ($row['floor_level'] ?? 'N/A');
                        } else {
                            $room_num = $row['room_num'] ?? 'N/A';
                            $room_type = $row['room_type'] ?? 'N/A';
                            $unit_detail = "Room No: " . $room_num . " (" . $room_type . ")";
                        }
                    ?>
                    
                    <!-- Classic Property Card Container wrapper -->
                    <div class="bg-white border border-stone-200 rounded-sm overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                        
                        <!-- Card Top Header -->
                        <div class="p-5 border-b border-stone-100 bg-stone-50/50 flex justify-between items-start gap-4">
                            <div>
                                <span class="inline-block text-[9px] font-bold tracking-widest uppercase border px-2 py-0.5 rounded-sm mb-2
                                    <?= $is_apartment ? 'border-blue-200 text-blue-800 bg-blue-50/40' : 'border-stone-300 text-stone-700 bg-stone-100' ?>">
                                    <?= $type_badge ?>
                                </span>
                                <h3 class="font-bold text-stone-900 text-base line-clamp-1 tracking-tight title-classic uppercase"><?= htmlspecialchars($row['house_title'] ?? 'Untitled Property') ?></h3>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-[9px] uppercase tracking-wider text-stone-400 block font-bold">Monthly Rent</span>
                                <span class="text-base font-bold text-stone-900"><?= number_format((float)$price) ?> <span class="text-[10px] font-medium text-stone-400">MMK</span></span>
                            </div>
                        </div>

                        <!-- Card Body (Details Layout) -->
                        <div class="p-5 flex-grow space-y-4 text-xs">
                            <!-- Location Information -->
                            <div class="flex items-start gap-3 text-stone-600">
                                <span class="text-stone-400 text-sm mt-0.5">📍</span>
                                <div>
                                    <p class="font-bold text-stone-800 uppercase tracking-wide text-[11px]"><?= htmlspecialchars($row['township'] ?? 'Unknown Township') . ", " . htmlspecialchars($row['city'] ?? 'Unknown City') ?></p>
                                    <p class="text-stone-400 mt-1 leading-relaxed"><?= htmlspecialchars($row['full_address'] ?? 'No address provided') ?></p>
                                </div>
                            </div>

                            <!-- Unit Specifications -->
                            <div class="flex items-center gap-3 text-stone-600 bg-stone-50 p-2.5 rounded-sm border border-stone-200/60 font-medium">
                                <span class="text-stone-400 text-sm">🔑</span>
                                <span>Specification: <span class="text-stone-900 font-bold"><?= htmlspecialchars($unit_detail) ?></span></span>
                            </div>

                            <!-- Lease Dates & Security Deposit Grid Layout -->
                            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-stone-100 text-stone-500">
                                <div>
                                    <span class="block text-[10px] uppercase tracking-wider text-stone-400 font-bold mb-1">Lease Term</span>
                                    <div class="font-medium text-stone-800 space-y-0.5">
                                        <p><span class="text-stone-400 font-normal">From:</span> <?= !empty($row['start_date']) ? date('d-M-Y', strtotime($row['start_date'])) : 'N/A' ?></p>
                                        <p><span class="text-stone-400 font-normal">To:</span> <?= !empty($row['end_date']) ? date('d-M-Y', strtotime($row['end_date'])) : 'N/A' ?></p>
                                    </div>
                                </div>
                                <div class="border-l border-stone-100 pl-4">
                                    <span class="block text-[10px] uppercase tracking-wider text-stone-400 font-bold mb-1">Security Deposit</span>
                                    <span class="font-bold text-stone-900 text-sm block mt-1">
                                        <?= number_format((float)($row['total_deposit_amount'] ?? 0)) ?> <span class="text-[9px] text-stone-400">MMK</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Operations Bar -->
                        <div class="px-5 pb-5 bg-white text-right">
                            <a href="installment_list.php?contract_id=<?= $row['contract_id'] ?>" 
                               class="w-full inline-flex items-center justify-center bg-stone-50 border border-stone-300 text-stone-900 hover:bg-stone-100 rounded-sm font-bold text-[10px] py-2.5 shadow-sm transition-all gap-1.5 uppercase tracking-widest">
                                View Installment Schedule
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State Layout Container -->
            <div class="bg-white border border-stone-200 rounded-sm p-16 text-center max-w-sm mx-auto mt-10 shadow-sm">
                <span class="text-3xl block mb-3 text-stone-300">🏠</span>
                <h3 class="font-bold text-stone-800 text-base mb-1 tracking-tight uppercase title-classic">No Active Leases</h3>
                <p class="text-xs text-stone-400 mb-6 leading-relaxed">You have not entered into any active contractual leasing agreements yet.</p>
                <a href="#" class="inline-block bg-stone-900 hover:bg-stone-800 text-white px-5 py-2.5 rounded-sm font-bold text-[10px] uppercase tracking-widest transition-all shadow-sm">
                    Browse Properties
                </a>
            </div>
        <?php endif; ?>
    </div>
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