<?php
// 1. Start the PHP Session to track the logged-in renter
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Authentication Guard
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 16; 
    $_SESSION['user_name'] = "Yi";
}
$logged_in_user_id = $_SESSION['user_id'];

// 3. Database Connection Configuration
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
<body class="bg-[#f9f8f6] text-stone-800 font-classic antialiased min-h-screen">

<!-- TOP NAVBAR -->
<?php  include 'homepageheader.php'; ?>

<!-- MAIN CONTAINER CONTAINER -->
<div class="max-w-7xl mx-auto px-6 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        
        <!-- LEFT SIDEBAR (Matching Image) -->
        <aside class="lg:col-span-4 space-y-6">
            
            <!-- User Profile Card -->
            <div class="bg-white p-6 rounded-sm border border-stone-200 border-t-4 border-t-amber-800 shadow-xs">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-stone-100 rounded-full flex items-center justify-center text-stone-700 font-bold text-base border border-stone-200">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'YI', 0, 2)) ?>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-stone-900"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Yi') ?></h2>
                        <span class="inline-block bg-amber-50 text-amber-900 text-[9px] font-bold tracking-wider uppercase px-2 py-0.5 rounded-xs border border-amber-200/60 mt-1">
                            VERIFIED RESIDENT
                        </span>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-stone-100 space-y-3">
                    <div>
                        <p class="text-[9px] font-bold text-stone-400 uppercase tracking-widest">EMAIL COMMUNICATION</p>
                        <p class="text-xs font-semibold text-stone-800 mt-0.5">yi@gmail.com</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-stone-400 uppercase tracking-widest">SYSTEM REFERENCE</p>
                        <p class="text-xs text-stone-800 font-mono mt-0.5">#UID-<?= str_pad($logged_in_user_id, 4, '0', STR_PAD_LEFT) ?></p>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Card -->
            <div class="bg-white rounded-sm border border-stone-200 shadow-xs p-6">
                <p class="text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-4 border-b border-stone-100 pb-2">QUICK NAVIGATION</p>
                <nav class="flex flex-col space-y-1 text-xs">
                    <a href="renterdashboard.php" class="flex items-center space-x-3 text-stone-600 hover:bg-stone-50 px-3 py-2 rounded-sm transition">
                        <span>📌</span>
                        <span>Overview</span>
                    </a>
                    <a href="renter_contract.php" class="flex items-center space-x-3 text-stone-600 hover:bg-stone-50 px-3 py-2 rounded-sm transition">
                        <span>📜</span>
                        <span>My Contracts</span>
                    </a>
                    <a href="renter_payment.php" class="flex items-center space-x-3 text-amber-900 bg-amber-50/80 border-l-2 border-amber-800 px-3 py-2 font-bold transition">
                        <span>💳</span>
                        <span>Payment Ledgers</span>
                    </a>
                   
                </nav>
            </div>

            <!-- Automated Ledger Notification Box -->
            <div class="bg-[#1b365d] text-white p-6 rounded-sm shadow-xs space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 title-classic">AUTOMATED LEDGER NOTIFICATION</h3>
                <p class="text-[11px] text-slate-200 leading-relaxed italic">
                    Your statements and installment entries update automatically. For early contract notices or modifications, contact your primary property manager.
                </p>
            </div>

        </aside>

        <!-- RIGHT MAIN TABLE CONTENT -->
        <main class="lg:col-span-8 space-y-6">
            
            <!-- Header Section -->
            <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                <div>
                    <h1 class="text-xl font-bold text-stone-900 tracking-tight uppercase title-classic">PAYMENT HISTORY</h1>
                    <p class="text-stone-400 text-xs uppercase tracking-wider mt-1">မိမိပေးချေခဲ့သော ငွေပေးသွင်းမှုမှတ်တမ်းများနှင့် ရရှိထားသော ပြေစာများစာရင်း</p>
                </div>
               
            </div>

            <?php if (empty($payments)): ?>
                <div class="bg-white rounded-sm p-12 text-center border border-stone-200 shadow-xs">
                    <p class="text-stone-400 text-xs uppercase tracking-wider font-semibold">ငွေပေးသွင်းထားသော မှတ်တမ်း မရှိသေးပါ။</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-sm border border-stone-200 shadow-xs overflow-hidden">
                    
                    <!-- Fixed Table Column Layout to Prevent Horizontal Scroll Clipping -->
                    <div class="max-h-[460px] overflow-y-auto overflow-x-hidden">
                        <table class="w-full text-left border-collapse table-fixed">
                            <thead class="sticky top-0 bg-stone-100 border-b border-stone-200 text-[9px] font-bold uppercase tracking-wider text-stone-500 z-10">
                                <tr>
                                    <th class="py-3 px-3 w-[12%]">Payment ID</th>
                                    <th class="py-3 px-3 w-[28%]">Property / Lease</th>
                                    <th class="py-3 px-3 w-[22%]">Installment Period</th>
                                    <th class="py-3 px-3 w-[15%]">Payment Channel</th>
                                    <th class="py-3 px-3 w-[23%] text-right">Date Paid & Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 text-xs">
                                <?php foreach ($payments as $payment): ?>
                                    <tr class="hover:bg-stone-50/60 transition-colors">
                                        <!-- Payment ID -->
                                        <td class="py-4 px-3 font-mono font-bold text-stone-400 text-[11px]">
                                            #<?= htmlspecialchars($payment['payment_id']) ?>
                                        </td>
                                        
                                        <!-- Property / Lease -->
                                        <td class="py-4 px-3">
                                            <div class="font-bold text-stone-900 truncate text-[12px] tracking-tight title-classic uppercase"><?= htmlspecialchars($payment['house_title']) ?></div>
                                            <div class="text-[10px] text-stone-400 truncate mt-0.5">Contract #<?= htmlspecialchars($payment['contract_id']) ?> (<?= htmlspecialchars($payment['space_type']) ?>)</div>
                                        </td>
                                        
                                        <!-- Period -->
                                        <td class="py-4 px-3">
                                            <div class="font-bold text-stone-800 text-[11px]">Period <?= htmlspecialchars($payment['installment_period']) ?></div>
                                            <div class="text-[10px] text-stone-400 mt-0.5">ကျသင့်ငွေ: <?= number_format($payment['amount_to_pay']) ?> MMK</div>
                                        </td>
                                        
                                        <!-- Payment Channel -->
                                        <td class="py-4 px-3">
                                            <span class="inline-block px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase tracking-wider bg-stone-100 text-stone-700 border border-stone-300">
                                                <?= htmlspecialchars($payment['method_name'] ?? 'Unknown') ?>
                                            </span>
                                        </td>
                                        
                                        <!-- Date Paid & Amount -->
                                        <td class="py-4 px-3 text-right">
                                            <div class="font-bold text-stone-900 text-[12px]">
                                                <?= date('M d, Y', strtotime($payment['paid_at'])) ?>
                                            </div>
                                            <div class="text-[10px] font-semibold text-amber-900 mt-0.5">
                                                <?= number_format($payment['paid_amount']) ?> <span class="text-[9px] text-stone-400 font-normal">MMK</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

</body>
</html>