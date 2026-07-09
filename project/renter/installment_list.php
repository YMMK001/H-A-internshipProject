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

// 2. URL မှ contract_id ကို ဖတ်ခြင်း
$contract_id = $_GET['contract_id'] ?? null;

if (!$contract_id) {
    die("Error: Contract ID ကျန်ခဲ့ပါသည်။ URL တွင် ?contract_id=10 စသဖြင့် ထည့်ပေးပါ။");
}

// 💡 3. Dynamic Context Navigation Mapping Analysis
$referrer = $_SERVER['HTTP_REFERER'] ?? '';
$url_from = $_GET['from'] ?? '';

if (!empty($url_from)) {
    $is_profile_context = ($url_from === 'profile');
} else {
    $is_profile_context = (strpos($referrer, 'renter_profile.php') !== false || strpos($referrer, 'renterdashboard.php') !== false);
}

// Keep tracking context consistent across internal views
$current_context_string = $is_profile_context ? 'profile' : 'homepage';

// 4. ဒီ Contract အတွက် Installments ရှိပြီးသားလား အရင်စစ်ထုတ်မယ်
$check_stmt = $pdo->prepare("SELECT * FROM installments WHERE contract_id = :contract_id ORDER BY installment_period ASC");
$check_stmt->execute([':contract_id' => $contract_id]);
$installments = $check_stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. အကယ်၍ အထဲမှာ Data မရှိသေးရင် အလိုအလျောက် စာချုပ်သက်တမ်းအလိုက် အရစ်ကျတွက်ထုတ်ပေးမည်
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
        die("Error: သတ်မှတ်ထားသော စာချုပ် (Contract) ရှာမတွေ့ပါ။");
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

    // Robust Month Interval Difference Calculations
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

    // Refresh dynamic records variable array
    $check_stmt->execute([':contract_id' => $contract_id]);
    $installments = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installment List - Classic Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-[#292515] font-classic antialiased min-h-screen relative">

<?php 
if ($is_profile_context) {
    if (file_exists('renterheader.php')) {
        include 'renterheader.php';
    } else {
        echo '';
    }
} else {
    if (file_exists('homepageheader.php')) {
        include 'homepageheader.php';
    } else { ?>
        <div class="w-full bg-[#1b1816] text-white px-6 h-16 flex items-center justify-between shadow-sm">
            <div class="text-lg font-serif text-amber-500 font-bold tracking-wider">Rental<span class="text-white">Hub</span></div>
            <button onclick="toggleMobileMenu()" class="sm:hidden text-stone-300 hover:text-white p-2">
                ☰ Menu
            </button>
        </div>
    <?php }
} ?>

<div id="mobileDropdownMenu" class="hidden absolute top-16 left-0 w-full bg-[#1b1816]/95 backdrop-blur-md z-50 border-t border-stone-800 shadow-xl p-6 space-y-4">
    <p class="text-[10px] uppercase font-bold tracking-widest text-stone-500 border-b border-stone-800 pb-2">Quick Access</p>
    <nav class="flex flex-col space-y-3 font-medium text-sm text-stone-300">
        <a href="<?= $is_profile_context ? 'renterdashboard.php' : 'renterhomepage.php' ?>" class="hover:text-white transition">← Return Behind</a>
    </nav>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 min-h-screen">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-stone-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 tracking-tight uppercase title-classic">Installment List</h1>
            <p class="text-stone-400 text-xs uppercase tracking-wider mt-1.5 font-medium">စာချုပ်အရစ်ကျ ငွေပေးချေရန်စာရင်းနှင့် အခြေအနေများ</p>
        </div>
        <a href="<?= $is_profile_context ? 'renterdashboard.php' : 'renterhomepage.php' ?>" class="self-start sm:self-auto text-[11px] font-bold uppercase tracking-widest text-stone-900 bg-white hover:bg-stone-50 border border-stone-300 px-5 py-2.5 rounded-sm transition-all shadow-sm">
            &larr; <?= $is_profile_context ? 'Back to Dashboard' : 'Back to Home' ?>
        </a>
    </div>

    <div class="bg-white rounded-sm border border-stone-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-[10px] font-bold uppercase tracking-widest text-stone-500">
                        <th class="py-4 px-6">အရစ်ကျသက်တမ်း</th>
                        <th class="py-4 px-6">ပေးရမည့်ပမာဏ</th>
                        <th class="py-4 px-6">နောက်ဆုံးရက်</th>
                        <th class="py-4 px-6">အခြေအနေ</th>
                        <th class="py-4 px-6 text-center">လုပ်ဆောင်ချက်</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-xs">
                    <?php if (!empty($installments)): ?>
                        <?php foreach ($installments as $ins): ?>
                            <tr class="odd:bg-white even:bg-stone-50/75 hover:bg-stone-50/40 transition-colors duration-150">
                                
                                <td class="py-4 px-6 font-semibold text-stone-700">
                                    Month <?= htmlspecialchars($ins['installment_period']) ?>
                                    <?php if ($ins['installment_period'] == 1): ?>
                                        <span class="text-[9px] bg-stone-100 text-stone-800 font-bold border border-stone-300 px-2 py-0.5 rounded-sm ml-1.5 uppercase tracking-wide">
                                            + Deposit Included
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="py-4 px-6 font-bold text-stone-900 text-sm">
                                    <?= number_format($ins['amount_to_pay']) ?> <span class="text-[10px] text-stone-400 font-normal">MMK</span>
                                </td>
                                
                                <td class="py-4 px-6 text-stone-600 font-medium">
                                    <?= date('d-M-Y', strtotime($ins['due_date'])) ?>
                                </td>
                                
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <?php if ($ins['status'] === 'paid'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider bg-green-50 text-green-800 border border-green-200">
                                            Paid
                                        </span>
                                    <?php elseif ($ins['status'] === 'partially_paid'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                                            Partial
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-700 border border-red-100">
                                            Unpaid
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <?php if ($ins['status'] !== 'paid'): ?>
                                        <a href="pay_installment.php?installment_id=<?= htmlspecialchars($ins['id']) ?>&from=<?= $current_context_string ?>" 
                                           class="inline-flex items-center justify-center px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-white bg-stone-900 hover:bg-stone-800 rounded-sm transition-all shadow-sm">
                                            ငွေပေးချေမည်
                                        </a>
                                    <?php else: ?>
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-green-700 inline-flex items-center justify-center gap-1">
                                            ✓ ပေးချေပြီး
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-stone-400 uppercase tracking-widest text-[11px] font-medium">
                                ဒေတာ မရှိသေးပါ။
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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