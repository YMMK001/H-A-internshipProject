<?php
// 1. Start the PHP Session to track the logged-in renter
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Authentication Guard & Session Hydration
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    // Fallback for isolated testing environment
    $_SESSION['user_id'] = $_SESSION['user_id'] ?? 16;
    $_SESSION['user_name'] = $_SESSION['user_name'] ?? "Yi";
    $_SESSION['user_email'] = $_SESSION['user_email'] ?? "yi@gmail.com";
}

$logged_in_user_id = $_SESSION['user_id'];
$user_name         = $_SESSION['user_name'] ?? 'Yi';
$email             = $_SESSION['user_email'] ?? 'yi@gmail.com';

// 3. Database Connection Configuration (PDO)
$host     = 'localhost';
$dbname   = 'intern_test'; 
$username = 'root';        
$password = '';            

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 4. Query to fetch Contract Directory with dynamically mapped unit details
$sql = "SELECT 
            c.id AS contract_id,
            c.start_date,
            c.end_date,
            COALESCE(c.total_deposit_amount, 0.00) AS total_deposit_amount,
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
                WHEN c.apartment_id IS NOT NULL THEN COALESCE(a.apartment_price, 0.00)
                WHEN c.hostel_room_id IS NOT NULL THEN COALESCE(hr.monthly_price, 0.00)
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

// Handle AJAX Request if required by dynamic dashboard loading wrapper
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    renderContractTable($contracts, $logged_in_user_id);
    exit;
}

// Modular Render Function for Content Block
function renderContractTable($contracts, $logged_in_user_id) {
?>
    <div class="border-b border-stone-200 pb-4 mb-6">
        <h1 class="text-2xl font-serif font-bold text-stone-900 tracking-tight uppercase">CONTRACT DIRECTORY</h1>
        <p class="text-stone-500 text-xs uppercase tracking-wider mt-1 font-medium font-classic">မိမိ၏ လက်ရှိနှင့် ယခင်ငှားရမ်းခဲ့သော စာချုပ်ချုပ်ဆိုမှုမှတ်တမ်းများစာရင်း</p>
    </div>

    <?php if (empty($contracts)): ?>
        <div class="bg-white rounded-none p-12 text-center border border-stone-300 shadow-xs max-w-xl mx-auto">
            <p class="text-stone-500 text-xs uppercase tracking-wider font-semibold font-classic">ချုပ်ဆိုထားသော စာချုပ်မှတ်တမ်း မရှိသေးပါ။</p>
            <div class="mt-2 text-stone-400 font-mono text-[11px]">ID Reference: #UID-<?= str_pad((string)$logged_in_user_id, 4, '0', STR_PAD_LEFT) ?></div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-none border border-stone-300 shadow-xs overflow-hidden">
            <div class="overflow-x-auto overflow-y-auto max-h-[550px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-stone-100/80 border-b border-stone-200 text-[10px] font-bold uppercase tracking-widest text-stone-600">
                            <th class="py-3.5 px-4">Ref ID</th>
                            <th class="py-3.5 px-4">Property / Address</th>
                            <th class="py-3.5 px-4">Space Unit</th>
                            <th class="py-3.5 px-4">Duration</th>
                            <th class="py-3.5 px-4">Rent / Deposit</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 text-xs font-classic text-stone-700">
                        <?php foreach ($contracts as $contract): 
                            $today = date('Y-m-d');
                            $is_active = ($today >= $contract['start_date'] && $today <= $contract['end_date']);
                        ?>
                            <tr class="hover:bg-stone-50 transition-colors duration-150">
                                <td class="py-4 px-4 font-mono font-bold text-stone-500">
                                    #<?= htmlspecialchars($contract['contract_id']) ?>
                                </td>
                                
                                <td class="py-4 px-4 max-w-[200px]">
                                    <div class="font-serif font-bold text-stone-900 truncate text-[13px] tracking-tight uppercase"><?= htmlspecialchars($contract['house_title']) ?></div>
                                    <div class="text-[10px] text-stone-400 truncate mt-0.5">📍 <?= htmlspecialchars($contract['full_address']) ?></div>
                                </td>
                                
                                <td class="py-4 px-4">
                                    <span class="font-bold text-stone-800 block text-[12px]"><?= htmlspecialchars($contract['space_type']) ?></span>
                                    <span class="text-[10px] text-stone-500 font-medium mt-0.5 block"><?= htmlspecialchars($contract['unit_details']) ?></span>
                                </td>
                                
                                <td class="py-4 px-4 font-medium text-stone-600 whitespace-nowrap">
                                    <div class="font-semibold text-stone-800 text-[11px] font-sans"><?= date('M d, Y', strtotime($contract['start_date'])) ?></div>
                                    <div class="text-[10px] text-stone-400 mt-0.5 font-sans">to <?= date('M d, Y', strtotime($contract['end_date'])) ?></div>
                                </td>
                                
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-stone-900 text-[12px] font-sans">
                                        <?= number_format((float)($contract['monthly_rent'] ?? 0)) ?> 
                                        <span class="text-[9px] text-stone-400 font-normal">MMK/လ</span>
                                    </div>
                                    <div class="text-[10px] text-stone-500 mt-0.5 font-sans">
                                        စရန်ငွေ: <?= number_format((float)($contract['total_deposit_amount'] ?? 0)) ?> MMK
                                    </div>
                                </td>
                                
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <?php if ($is_active): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-none text-[9px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-none text-[9px] font-bold uppercase tracking-wider bg-stone-100 text-stone-500 border border-stone-200">
                                            Matured
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <a href="installment_list.php?contract_id=<?= $contract['contract_id'] ?>" 
                                       class="inline-flex items-center justify-center px-3 py-1.5 text-[10px] font-bold font-serif uppercase tracking-wider text-[#0f172a] bg-stone-100 hover:bg-[#0f172a] hover:text-[#fef3c7] border border-stone-300 rounded-none transition-all shadow-xs">
                                        💳 Installments &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rental Contracts - Editorial Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .font-serif { font-family: 'Playfair Display', Georgia, serif; }
    </style>
</head>
<body class="bg-[#fbfaf7] text-stone-900 font-classic antialiased min-h-screen flex flex-col justify-between">

<!-- STICKY TOP HEADER NAVBAR CONTAINER -->
<header class="sticky top-0 z-50 w-full bg-white border-b border-stone-300 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (file_exists('homepageheader.php')) { 
            include 'homepageheader.php'; 
        } else { ?>
            <div class="h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 bg-[#0f172a] border border-[#b45309] flex items-center justify-center text-[#fef3c7] font-serif font-bold text-lg">
                        R
                    </div>
                    <span class="font-serif font-bold text-xl tracking-tight text-slate-900">
                        Rental<span class="italic text-amber-700">Hub</span>
                    </span>
                </div>
                <button onclick="toggleMobileMenu()" class="lg:hidden text-stone-700 hover:text-black p-2 text-sm font-semibold">
                    ☰ Menu
                </button>
            </div>
        <?php } ?>
    </div>
</header>

<!-- Mobile Slide-out Menu -->
<div id="mobileDropdownMenu" class="hidden fixed inset-0 top-16 bg-[#0f172a]/30 backdrop-blur-xs z-50 transition-all duration-200">
    <div class="bg-white border-b border-stone-300 shadow-xl p-6 space-y-4">
        <p class="text-[10px] uppercase font-bold tracking-widest text-stone-400 border-b border-stone-100 pb-2">Navigation Panel</p>
        <nav class="flex flex-col space-y-3 font-serif font-medium text-sm text-stone-800">
            <a href="renterdashboard.php" class="hover:text-amber-800 transition">Overview</a>
            <a href="renter_contract.php" class="text-amber-800 font-bold">My Contracts</a>
            <a href="renter_payment.php" class="hover:text-amber-800 transition">Payment Ledgers</a>
            <a href="../auth/logout.php" class="text-red-700 hover:text-red-900 transition font-sans text-xs pt-2">Sign Out Account</a>
        </nav>
    </div>
</div>

<!-- MAIN PAGE CONTAINER (Perfect 12-Column Layout Alignment) -->
<div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- LEFT ASIDE SIDEBAR -->
        <aside class="lg:col-span-4 space-y-6">
            
            <!-- User Info Card -->
            <div class="bg-white p-6 rounded-none border border-stone-300 relative shadow-xs">
                <div class="absolute top-0 left-0 right-0 h-1 bg-[#0f172a]"></div>
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-stone-100 rounded-full flex items-center justify-center text-stone-700 font-serif font-bold text-xl uppercase border border-stone-300">
                        <?= htmlspecialchars(mb_substr($user_name, 0, 2)) ?>
                    </div>
                    <div>
                        <h2 class="text-xl font-serif font-bold text-stone-900 leading-tight"><?= htmlspecialchars($user_name) ?></h2>
                        <span class="inline-block bg-amber-100/70 text-amber-900 text-[9px] font-bold tracking-wider uppercase px-2 py-0.5 rounded-none border border-amber-300/60 mt-1">
                            VERIFIED RESIDENT
                        </span>
                    </div>
                </div>

                <div class="space-y-3 border-t border-stone-200 pt-4 text-xs mt-5">
                    <div>
                        <p class="uppercase text-[9px] font-bold text-stone-400 tracking-widest">Email Communication</p>
                        <p class="font-bold text-stone-800 mt-0.5 font-sans"><?= htmlspecialchars($email) ?></p>
                    </div>
                    <div>
                        <p class="uppercase text-[9px] font-bold text-stone-400 tracking-widest">System Reference</p>
                        <p class="font-mono text-stone-600 mt-0.5">#UID-<?= str_pad((string)$logged_in_user_id, 4, '0', STR_PAD_LEFT) ?></p>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Card -->
            <div class="bg-white rounded-none border border-stone-300 shadow-xs p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-3 border-b border-stone-100 pb-2">QUICK NAVIGATION</p>
                <nav class="flex flex-col space-y-1 text-xs font-serif">
                    <a href="renterdashboard.php" class="flex items-center space-x-3 text-stone-700 hover:bg-stone-50 px-3 py-2.5 rounded-none transition">
                        <span>📌</span>
                        <span>Overview</span>
                    </a>
                    <a href="renter_contract.php" class="flex items-center space-x-3 text-amber-900 bg-amber-50/80 border-l-2 border-amber-700 px-3 py-2.5 font-bold transition">
                        <span>📜</span>
                        <span>My Contracts</span>
                    </a>
                    <a href="renter_payment.php" class="flex items-center space-x-3 text-stone-700 hover:bg-stone-50 px-3 py-2.5 rounded-none transition">
                        <span>💳</span>
                        <span>Payment Ledgers</span>
                    </a>
                    
                </nav>
            </div>

            <!-- Notification Box (Dark Slate Luxury Card) -->
            <div class="bg-[#0f172a] text-[#fef3c7] p-6 rounded-none border border-[#b45309]/50 shadow-xs space-y-2 font-serif">
                <h3 class="text-[10px] font-bold uppercase tracking-widest text-amber-400">AUTOMATED LEDGER NOTIFICATION</h3>
                <p class="text-xs text-stone-300 leading-relaxed italic">
                    Your statements and installment entries update automatically. For early contract notices or modifications, contact your primary property manager.
                </p>
            </div>

        </aside>

        <!-- RIGHT MAIN CONTENT (Contract Table Container) -->
        <main id="mainContent" class="lg:col-span-8 space-y-6">
            <?php renderContractTable($contracts, $logged_in_user_id); ?>
        </main>

    </div>
</div>

<footer class="bg-white border-t border-stone-300 py-6 text-center text-xs font-serif text-stone-400 mt-12">
    &copy; 2026 RentalHub Platform. All contract operations follow standard property management policies.
</footer>

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