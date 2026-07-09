<?php
// 1. Start the PHP Session to track the logged-in renter
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Authentication Guard
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
    $_SESSION['user_name'] = "John Doe";
}
$logged_in_user_id = $_SESSION['user_id'];

// 3. Database Connection
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

// 4. Query Payments made by this specific User
$sql = "SELECT 
            p.id AS payment_id,
            p.paid_amount,
            p.payment_image,
            p.paid_at,
            pm.name AS method_name,
            i.installment_period,
            i.amount_to_pay,
            c.id AS contract_id,
            COALESCE(rh.title, 'Unnamed Property') AS house_title,
            CASE 
                WHEN c.apartment_id IS NOT NULL THEN 'Apartment'
                WHEN c.hostel_room_id IS NOT NULL THEN 'Hostel Room'
                ELSE 'Space'
            END AS space_type
        FROM payments p
        INNER JOIN payment_methods pm ON p.payment_method_id = pm.id
        INNER JOIN installments i ON p.installment_id = i.id
        INNER JOIN contracts c ON i.contract_id = c.id
        LEFT JOIN apartments a ON c.apartment_id = a.id
        LEFT JOIN hostel_rooms hr ON c.hostel_room_id = hr.id
        LEFT JOIN rental_houses rh ON rh.id = COALESCE(a.rental_house_id, hr.rental_house_id)
        WHERE c.user_id = :user_id
        ORDER BY p.paid_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $logged_in_user_id]);
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payments - Classic Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-800 font-classic antialiased min-h-screen relative">

<div class="w-full bg-[#1b1816] text-white sticky top-0 z-50 shadow-md">
    <?php if (file_exists('renterheader.php')) { 
        include 'renterheader.php'; 
    } else { ?>
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <div class="text-xl font-serif text-amber-500 font-bold tracking-wider">Rental<span class="text-white">Hub</span></div>
                <nav class="hidden md:flex space-x-6 text-sm font-medium tracking-wide text-stone-300">
                    <a href="" class="hover:text-white transition">Overview</a>
                    <a href="renter_contract.php" class="hover:text-white transition">Lease Agreements</a>
                    <a href="renter_payment.php" class="text-white border-b-2 border-amber-500 pb-1">Payment Ledgers</a>
                </nav>
            </div>
            <div class="hidden md:flex items-center space-x-4">
                <button class="bg-blue-700 text-xs font-bold uppercase tracking-wider px-4 py-2 hover:bg-blue-800 rounded-sm">Find Accommodation</button>
                <span class="text-xs text-stone-400 font-mono">Resident: John Doe</span>
                <button class="border border-stone-600 px-3 py-1 text-xs uppercase tracking-wider hover:bg-white/5">Sign Out</button>
            </div>
            <button onclick="toggleMobileMenu()" class="md:hidden text-stone-300 hover:text-white text-lg p-2 focus:outline-none">
                ☰
            </button>
        </div>
    <?php } ?>

    <div id="mobileDropdownMenu" class="hidden fixed top-16 left-0 w-full h-[calc(100vh-4rem)] bg-stone-900/50 backdrop-blur-md z-50 transition-all duration-200">
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

<div id="mainAppContent" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 transition-all duration-200">
        
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-stone-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 tracking-tight uppercase title-classic flex items-center gap-2">
                Payment History
            </h1>
            <p class="text-stone-400 text-xs uppercase tracking-wider mt-1.5 font-medium">မိမိပေးချေခဲ့သော ငွေပေးသွင်းမှုမှတ်တမ်းများနှင့် ရရှိထားသော ပြေစာများစာရင်း</p>
        </div>
        <a href="renter_contract.php" class="self-start sm:self-center text-xs font-bold uppercase tracking-wider text-stone-900 bg-white hover:bg-stone-50 border border-stone-300 px-4 py-2.5 rounded-sm transition-all shadow-sm">
            &larr; View Contracts
        </a>
    </div>

    <?php if (empty($payments)): ?>
        <div class="bg-white rounded-sm p-16 text-center border border-stone-200 shadow-sm max-w-xl mx-auto">
            <p class="text-stone-400 text-xs uppercase tracking-wider font-semibold">ငွေပေးသွင်းထားသော မှတ်တမ်း မရှိသေးပါ။</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-sm border border-stone-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto overflow-y-auto max-h-[460px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-stone-50/70 border-b border-stone-200 text-[10px] font-bold uppercase tracking-widest text-stone-500">
                            <th class="py-4 px-6">Payment ID</th>
                            <th class="py-4 px-6">Property / Lease</th>
                            <th class="py-4 px-6">Installment Period</th>
                            <th class="py-4 px-6">Payment Channel</th>
                            <th class="py-4 px-6">Date Paid</th>
                            <th class="py-4 px-6">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-xs">
                        <?php foreach ($payments as $payment): ?>
                            <tr class="odd:bg-white even:bg-stone-50/40 hover:bg-stone-50/80 transition-colors duration-150">
                                <td class="py-5 px-6 font-mono font-bold text-stone-400">
                                    #<?= htmlspecialchars($payment['payment_id']) ?>
                                </td>
                                
                                <td class="py-5 px-6 max-w-xs">
                                    <div class="font-bold text-stone-900 truncate text-[14px] tracking-tight title-classic uppercase"><?= htmlspecialchars($payment['house_title']) ?></div>
                                    <div class="text-[11px] text-stone-400 truncate mt-1">Contract #<?= htmlspecialchars($payment['contract_id']) ?> (<?= htmlspecialchars($payment['space_type']) ?>)</div>
                                </td>
                                
                                <td class="py-5 px-6 font-medium text-stone-600">
                                    <div class="font-semibold text-stone-800 text-[13px]">Period <?= htmlspecialchars($payment['installment_period']) ?></div>
                                    <div class="text-[11px] text-stone-500 mt-0.5">ကျသင့်ငွေ: <?= number_format($payment['amount_to_pay']) ?> MMK</div>
                                </td>
                                
                                <td class="py-5 px-6 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider bg-stone-100 text-stone-800 border border-stone-300">
                                        <?= htmlspecialchars($payment['method_name'] ?? 'Unknown Method') ?>
                                    </span>
                                </td>
                                
                                <td class="py-5 px-6 text-stone-600 whitespace-nowrap">
                                    <div class="font-semibold text-stone-800"><?= date('M d, Y', strtotime($payment['paid_at'])) ?></div>
                                    <div class="text-[11px] text-stone-400 mt-0.5"><?= date('h:i A', strtotime($payment['paid_at'])) ?></div>
                                </td>
                                
                                <td class="py-5 px-6 whitespace-nowrap">
                                    <div class="font-bold text-stone-900 text-[14px]">
                                        <?= number_format($payment['paid_amount']) ?> <span class="text-[11px] text-stone-400 font-normal">MMK</span>
                                    </div>
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
        const mainAppContent = document.getElementById('mainAppContent');
        
        if (menu) {
            const isHidden = menu.classList.contains('hidden');
            
            if (isHidden) {
                menu.classList.remove('hidden');
                mainAppContent.classList.add('blur-sm', 'pointer-events-none');
                document.body.classList.add('overflow-hidden');
            } else {
                menu.classList.add('hidden');
                mainAppContent.classList.remove('blur-sm', 'pointer-events-none');
                document.body.classList.remove('overflow-hidden');
            }
        }
    }
</script>
</body>
</html>