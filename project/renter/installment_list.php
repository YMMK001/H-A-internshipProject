<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Database Connection
$host     = 'localhost';
$db_name  = 'intern_test'; 
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 2. Fetch contract_id from GET query parameter
$contract_id = $_GET['contract_id'] ?? null;

if (!$contract_id) {
    die("Error: Contract ID missing. Please specify a contract ID in the URL parameter (e.g., ?contract_id=10).");
}

// 3. Navigation Context Detection
$referrer = $_SERVER['HTTP_REFERER'] ?? '';
$url_from = $_GET['from'] ?? '';

if (!empty($url_from)) {
    $is_profile_context = ($url_from === 'profile');
} else {
    $is_profile_context = (
        strpos($referrer, 'renter_profile.php') !== false || 
        strpos($referrer, 'renterdashboard.php') !== false ||
        strpos($referrer, 'renter_contract.php') !== false
    );
}

// Context string parameter for deep links
$current_context_string = $is_profile_context ? 'profile' : 'homepage';
$back_url = $is_profile_context ? 'renter_contract.php' : 'renterhomepage.php';

// 4. Fetch existing installments for this contract
$check_stmt = $pdo->prepare("SELECT * FROM installments WHERE contract_id = :contract_id ORDER BY installment_period ASC");
$check_stmt->execute([':contract_id' => $contract_id]);
$installments = $check_stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Generate installments if none currently exist
if (empty($installments)) {
    
    $contract_query = "
        SELECT c.*, a.apartment_price, h.monthly_price 
        FROM contracts c
        LEFT JOIN apartments a ON c.apartment_id = a.id
        LEFT JOIN hostel_rooms h ON c.hostel_room_id = h.id
        WHERE c.id = :contract_id
    ";
    
    $contract_stmt = $pdo->prepare($contract_query);
    $contract_stmt->execute([':contract_id' => $contract_id]);
    $contract = $contract_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        die("Error: Contract record not found.");
    }

    $monthly_rent = 0;
    if (!empty($contract['apartment_id'])) {
        $monthly_rent = floatval($contract['apartment_price'] ?? 0);
    } elseif (!empty($contract['hostel_room_id'])) {
        $monthly_rent = floatval($contract['monthly_price'] ?? 0);
    }

    $deposit_amount = floatval($contract['total_deposit_amount'] ?? 0);
    $start_date     = $contract['start_date']; 
    $end_date       = $contract['end_date'];

    // Calculate duration in months
    $d1 = new DateTime($start_date);
    $d2 = new DateTime($end_date);
    $months_diff = $d1->diff($d2);
    $total_months = ($months_diff->y * 12) + $months_diff->m;

    if ($total_months <= 0) {
        $total_months = 1;
    }

    for ($i = 1; $i <= $total_months; $i++) {
        $months_to_add = $i - 1;
        $due_date = date('Y-m-d', strtotime("+$months_to_add month", strtotime($start_date)));

        if ($i === 1) {
            $amount_to_pay = $monthly_rent + $deposit_amount;
        } else {
            $amount_to_pay = $monthly_rent;
        }

        $insert_stmt = $pdo->prepare("
            INSERT INTO installments (contract_id, installment_period, amount_to_pay, due_date, status) 
            VALUES (:contract_id, :period, :amount, :due_date, 'unpaid')
        ");
        
        $insert_stmt->execute([
            ':contract_id' => $contract_id,
            ':period'      => $i,
            ':amount'      => $amount_to_pay,
            ':due_date'    => $due_date
        ]);
    }

    // Refresh installment records
    $check_stmt->execute([':contract_id' => $contract_id]);
    $installments = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Render AJAX main area if requested via XMLHttp
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    renderMainContent($installments, $contract_id, $back_url, $is_profile_context, $current_context_string);
    exit;
}

// Helper to compute user initials
$display_username = $_SESSION['username'] ?? 'Mokky';
$name_parts = explode(' ', trim($display_username));
if (count($name_parts) >= 2) {
    $initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
} else {
    $initials = strtoupper(substr($display_username, 0, 2));
}

// Function to render main content section
function renderMainContent($installments, $contract_id, $back_url, $is_profile_context, $current_context_string) {
?>
    <section class="space-y-6">
        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-stone-200 pb-4">
            <div>
                <h1 class="text-xl font-serif font-bold text-stone-900 tracking-wide uppercase">INSTALLMENT SCHEDULE</h1>
                <p class="text-stone-500 text-xs italic font-serif mt-1">
                    အရစ်ကျ ငွေပေးချေရန်စာရင်းနှင့် အခြေအနေများ
                </p>
            </div>
            
            <!-- Dynamic Navigation Back Button -->
            <a href="<?= $back_url ?>" 
               class="self-start sm:self-auto text-[11px] font-serif text-stone-500 hover:text-stone-900 uppercase tracking-wider transition">
                &larr; <?= $is_profile_context ? 'BACK TO CONTRACTS' : 'BACK TO HOME' ?>
            </a>
        </div>

        <!-- Installments Table -->
        <div class="bg-white rounded-none border border-stone-300 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-[10px] font-bold uppercase tracking-widest text-stone-500 font-serif">
                            <th class="py-3.5 px-4">အရစ်ကျသက်တမ်း</th>
                            <th class="py-3.5 px-4">ပေးရမည့်ပမာဏ</th>
                            <th class="py-3.5 px-4">နောက်ဆုံးရက်</th>
                            <th class="py-3.5 px-4">အခြေအနေ</th>
                            <th class="py-3.5 px-4 text-center">လုပ်ဆောင်ချက်</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 text-xs text-stone-700">
                        <?php if (!empty($installments)): ?>
                            <?php foreach ($installments as $ins): ?>
                                <tr class="odd:bg-white even:bg-stone-50/40 hover:bg-amber-50/30 transition-colors">
                                    
                                    <td class="py-4 px-4 font-semibold text-stone-900">
                                        Month <?= htmlspecialchars($ins['installment_period']) ?>
                                        <?php if ($ins['installment_period'] == 1): ?>
                                            <span class="text-[9px] bg-stone-100 text-stone-700 font-bold border border-stone-300 px-1.5 py-0.5 rounded-none ml-1 uppercase tracking-wider">
                                                + Deposit
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="py-4 px-4 font-bold text-stone-900 text-xs font-sans">
                                        <?= number_format($ins['amount_to_pay']) ?> 
                                        <span class="text-[10px] text-stone-400 font-normal">MMK</span>
                                    </td>
                                    
                                    <td class="py-4 px-4 font-mono text-[11px] text-stone-600">
                                        <?= date('d-M-Y', strtotime($ins['due_date'])) ?>
                                    </td>
                                    
                                    <td class="py-4 px-4 whitespace-nowrap">
    <?php if ($ins['status'] === 'paid'): ?>
        <span class="inline-block border px-2 py-0.5 rounded-none text-[9px] font-bold tracking-widest uppercase border-emerald-300 text-emerald-800 bg-emerald-50">
            Paid
        </span>
    <?php elseif ($ins['status'] === 'pending'): ?>
        <!-- Owner အတည်ပြုချက် စောင့်ဆိုင်းနေသည့် Status Badge -->
        <span class="inline-block border px-2 py-0.5 rounded-none text-[9px] font-bold tracking-widest uppercase border-amber-300 text-amber-800 bg-amber-50">
            Waiting for Approval
        </span>
    <?php elseif ($ins['status'] === 'partially_paid'): ?>
        <span class="inline-block border px-2 py-0.5 rounded-none text-[9px] font-bold tracking-widest uppercase border-amber-300 text-amber-800 bg-amber-50">
            Partial
        </span>
    <?php else: ?>
        <span class="inline-block border px-2 py-0.5 rounded-none text-[9px] font-bold tracking-widest uppercase border-red-300 text-red-700 bg-red-50">
            Unpaid
        </span>
    <?php endif; ?>
</td>

<td class="py-4 px-4 text-center whitespace-nowrap">
    <?php if ($ins['status'] === 'paid'): ?>
        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 inline-flex items-center gap-1 font-serif">
            ✓ ပေးချေပြီး
        </span>
    <?php elseif ($ins['status'] === 'pending'): ?>
        <!-- Pending ဖြစ်နေပါက ငွေထပ်ပေး၍ မရအောင် Button ကို ပိတ်ထားပြီး အသိပေးစာပြပါ -->
        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 inline-flex items-center gap-1 font-serif italic">
            ⏳ စစ်ဆေးအတည်ပြုဆဲ
        </span>
    <?php else: ?>
        <a href="pay_installment.php?id=<?= htmlspecialchars($ins['id']) ?>&from=<?= $current_context_string ?>" 
           class="inline-block bg-[#0f172a] hover:bg-slate-900 text-[#fef3c7] font-serif text-[10px] font-semibold uppercase tracking-wider px-3.5 py-1.5 transition shadow-xs">
            ငွေပေးချေမည်
        </a>
    <?php endif; ?>
</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-stone-400 uppercase tracking-widest text-[11px] font-serif italic">
                                    ဒေတာ မရှိသေးပါ။
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
<?php
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalHub - Installment List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .font-serif-classic { font-family: 'Playfair Display', Georgia, serif; }
    </style>
</head>
<body class="bg-[#fcfbf9] text-stone-800 font-classic antialiased min-h-screen flex flex-col justify-between">

    <!-- Header Navigation Bar -->
    <?php 
    if (file_exists('homepageheader.php')) {
        include 'homepageheader.php';
    } else { ?>
        <header class="sticky top-0 z-50 w-full bg-white border-b border-stone-300 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="h-16 flex items-center justify-between gap-4">
                    <a href="renterhomepage.php" class="flex items-center gap-3">
                        <div class="h-9 w-9 bg-[#0f172a] border border-[#b45309] flex items-center justify-center text-[#fef3c7] font-serif font-bold text-lg">
                            R
                        </div>
                        <span class="font-serif font-bold text-xl tracking-tight text-slate-900">
                            Rental<span class="italic text-amber-700">Hub</span>
                        </span>
                    </a>

                    <div class="hidden md:flex items-center gap-2 flex-1 max-w-md mx-6">
                        <input type="text" placeholder="Search title or keyword..." class="w-full text-xs px-3 py-2 border border-stone-300 rounded-none bg-stone-50/50 focus:bg-white focus:outline-none">
                        <select class="text-xs px-2 py-2 border border-stone-300 bg-stone-50/50 rounded-none text-stone-600 focus:outline-none">
                            <option>All Types</option>
                        </select>
                        <a href="renterhomepage.php" class="text-xs px-3 py-2 border border-stone-300 bg-stone-50 hover:bg-stone-100 font-serif">Home</a>
                    </div>

                    <button onclick="toggleMobileMenu()" class="lg:hidden text-stone-700 hover:text-black p-2 text-sm font-semibold">
                        ☰ Menu
                    </button>
                </div>
            </div>
        </header>
    <?php } ?>

    <!-- Main Responsive Layout Container -->
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($is_profile_context): ?>
            <!-- Two Column Layout when accessed in Profile Context -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Sidebar -->
                <aside class="lg:col-span-1 space-y-6">

                    <!-- Profile Card -->
                    <div class="bg-white border border-stone-300 p-6 shadow-xs relative rounded-none">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-[#0f172a]"></div>
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-14 h-14 bg-stone-100 border border-stone-300 rounded-full flex items-center justify-center font-serif text-xl font-bold text-stone-700 uppercase shrink-0">
                                <?= htmlspecialchars($initials) ?>
                            </div>
                            <div>
                                <h2 class="font-serif font-bold text-xl text-stone-900 leading-tight"><?= htmlspecialchars($display_username) ?></h2>
                                <span class="text-[9px] uppercase font-bold tracking-wider text-amber-900 bg-amber-100/70 border border-amber-300/60 px-1.5 py-0.5 rounded-none mt-1 inline-block">
                                    VERIFIED RESIDENT
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-stone-200 pt-4 space-y-3 text-xs">
                            <div>
                                <span class="block text-stone-400 uppercase tracking-widest text-[9px] font-bold">EMAIL COMMUNICATION</span>
                                <span class="font-medium text-stone-800 font-sans"><?= htmlspecialchars($_SESSION['email'] ?? 'mokky@gmail.com') ?></span>
                            </div>
                            <div>
                                <span class="block text-stone-400 uppercase tracking-widest text-[9px] font-bold">SYSTEM REFERENCE</span>
                                <span class="font-mono text-stone-600">#UID-<?= str_pad($_SESSION['user_id'] ?? '0022', 4, '0', STR_PAD_LEFT) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Navigation Card -->
                    <div class="bg-white border border-stone-300 p-4 shadow-xs rounded-none">
                        <p class="text-[10px] uppercase font-bold tracking-widest text-stone-400 mb-3 border-b border-stone-100 pb-2">QUICK NAVIGATION</p>
                        <nav class="flex flex-col space-y-1 text-xs font-serif">
                            <a href="renter_profile.php" class="px-3 py-2.5 text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors">
                                📌 Overview
                            </a>
                            <a href="renter_contract.php" class="px-3 py-2.5 text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors">
                                📄 My Contracts
                            </a>
                            <a href="installment_list.php?contract_id=<?= urlencode((string)$contract_id) ?>&from=profile" class="px-3 py-2.5 bg-amber-50/80 text-amber-900 font-bold border-l-2 border-amber-700">
                                💳 Payment Ledgers
                            </a>
                        </nav>
                    </div>

                    <!-- Notification Banner -->
                    <div class="bg-[#0f172a] text-[#fef3c7] p-6 border border-[#b45309]/50 shadow-xs font-serif">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-amber-400 mb-2">AUTOMATED LEDGER NOTIFICATION</h3>
                        <p class="text-xs text-stone-300 leading-relaxed italic">
                            Your statements and installment entries update automatically. For early contract notices or modifications, contact your primary property manager.
                        </p>
                    </div>

                </aside>

                <!-- Right Main Area -->
                <main id="dashboardMainContent" class="lg:col-span-2">
                    <?php renderMainContent($installments, $contract_id, $back_url, $is_profile_context, $current_context_string); ?>
                </main>
            </div>
        <?php else: ?>
            <!-- Full Width Container Layout when accessed outside profile context -->
            <main id="dashboardMainContent" class="max-w-5xl mx-auto space-y-8">
                <?php renderMainContent($installments, $contract_id, $back_url, $is_profile_context, $current_context_string); ?>
            </main>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-stone-300 py-6 text-center text-xs font-serif text-stone-400 mt-12">
        &copy; 2026 RentalHub Platform. All dashboard data operations follow local leasing terms.
    </footer>

    <!-- Script Handlers -->
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