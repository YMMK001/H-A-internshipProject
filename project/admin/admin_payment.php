<?php
$host = 'localhost'; $db_name = 'intern_test'; $username = 'root'; $password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Database connection failed: " . $e->getMessage()); }

$message = "";

// --- APPROVE LOGIC (ပိုင်ရှင်က အတည်ပြုခလုတ် နှိပ်လိုက်သောအခါ) ---
if (isset($_POST['approve_payment'])) {
    $installment_id = $_POST['installment_id'];
    
    try {
        // installments table ထဲက status ကို 'paid' သို့ ပြောင်းလဲခြင်း
        $update_stmt = $pdo->prepare("UPDATE installments SET status = 'paid', updated_at = CURRENT_TIMESTAMP() WHERE id = :id");
        $update_stmt->execute([':id' => $installment_id]);
        $message = "ငွေပေးချေမှုကို အောင်မြင်စွာ အတည်ပြုပြီးပါပြီ။";
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// --- ငွေပေးချေထားသော စာရင်းများ ဆွဲထုတ်ခြင်း ---
$query = "
    SELECT p.id AS payment_id, p.paid_amount, p.payment_image, p.paid_at,
           i.id AS installment_id, i.installment_period, i.status,
           u.name AS renter_name,
           pm.name AS method_name
    FROM payments p
    JOIN installments i ON p.installment_id = i.id
    JOIN contracts c ON i.contract_id = c.id
    JOIN users u ON c.user_id = u.id
    JOIN payment_methods pm ON p.payment_method_id = pm.id
    ORDER BY p.paid_at DESC
";
$stmt = $pdo->query($query);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner - Payment Approvals</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex overflow-hidden">

    <div class="flex-shrink-0 h-screen sticky top-0 z-50">
        <?php include 'ownerheader.php'; ?>
    </div>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <div class="bg-white border-b border-gray-300 shadow-sm px-6 py-3 flex items-center justify-between font-sans">
            <div class="flex items-center space-x-3">
                <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                    ☰ Menu
                </button>
                <div class="hidden sm:flex items-center space-x-2 text-xs text-gray-500">
                    <span class="text-gray-800 font-bold text-2xl">Renters List</span>
                </div>
            </div>

            <div class="flex items-center space-x-4 divide-x divide-gray-200">
                <div class="pl-4 flex items-center space-x-2">
                    <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-white text-xs font-bold shadow-inner border border-slate-700">
                        AD
                    </div>
                    <div class="hidden lg:block leading-none">
                        <p class="text-xs font-bold text-gray-900">အိမ်ပိုင်ရှင် မန်နေဂျာ</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Console Role</p>
                    </div>
                </div>
            </div>
        </div>

        <main class="flex-1 p-6 overflow-y-auto">
            <div class="w-full max-w-7xl mx-auto">
                
                <div class="mb-6 pb-4 border-b-2 border-gray-800 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-600 block mb-1">Owner Portal</span>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 font-sans">ဝင်ရောက်လာသော ငွေပေးချေမှုများ စစ်ဆေးခြင်း</h1>
                        <p class="text-xs text-gray-600 mt-1 italic">Review incoming transactions and authorize tenant lease installments.</p>
                    </div>
                    <div class="bg-white p-4 border border-gray-300 shadow-sm border-l-4 border-l-slate-800">
                        <span class="text-xs uppercase font-bold tracking-wider text-gray-500 block">စုစုပေါင်း လွှဲပြောင်းမှု</span>
                        <span class="text-2xl font-bold text-gray-800 mt-2 block"><?= count($payments) ?> ကြိမ်</span>
                    </div>
                </div>

                <?php if(!empty($message)): ?>
                    <div class="mb-6 p-3 bg-emerald-50 text-emerald-900 border-l-4 border border-emerald-300 shadow-sm font-medium text-xs flex items-center gap-2">
                        <span>📢</span> <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white border border-gray-300 shadow-sm overflow-hidden mb-8 w-full font-sans">
                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="w-full text-left border-collapse whitespace-nowrap table-fixed border-gray-300">
                            <thead class="bg-gray-800 text-white text-xs font-semibold uppercase tracking-wider sticky top-0 z-10">
                                <tr>
                                    <th class="p-3 pl-4 w-[20%] border border-gray-700">အိမ်ငှားအမည်</th>
                                    <th class="p-3 w-[15%] border border-gray-700">အရစ်ကျလ</th>
                                    <th class="p-3 w-[15%] border border-gray-700">လွှဲအပ်ငွေ</th>
                                    <th class="p-3 w-[15%] border border-gray-700">ငွေလွှဲစနစ်</th>
                                    <th class="p-3 w-[15%] border border-gray-700">ပြေစာ (Slip)</th>
                                    <th class="p-3 w-[10%] border border-gray-700">အခြေအနေ</th>
                                    <th class="p-3 w-[10%] border border-gray-700 text-center">လုပ်ဆောင်ချက်</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-300 text-xs text-gray-800 bg-white">
                                <?php if(!empty($payments)): ?>
                                    <?php foreach($payments as $pay): ?>
                                        <tr class="hover:bg-gray-50 transition-colors odd:bg-stone-50/50">
                                            
                                            <td class="p-3 pl-4 border-r border-gray-200 overflow-hidden text-ellipsis">
                                                <div class="font-bold text-gray-900 truncate"><?= htmlspecialchars($pay['renter_name']) ?></div>
                                            </td>
                                            
                                            <td class="p-3 border-r border-gray-200 font-semibold text-gray-700">
                                                Month <?= htmlspecialchars($pay['installment_period']) ?>
                                            </td>
                                            
                                            <td class="p-3 border-r border-gray-200 font-bold text-gray-900 tracking-tight">
                                                <?= number_format($pay['paid_amount']) ?> <span class="text-[10px] text-gray-500 font-normal ml-0.5">MMK</span>
                                            </td>
                                            
                                            <td class="p-3 border-r border-gray-200">
                                                <span class="border border-gray-400 text-gray-900 text-[10px] font-bold px-1.5 py-0.5 tracking-wide bg-gray-50 uppercase">
                                                    <?= htmlspecialchars($pay['method_name']) ?>
                                                </span>
                                            </td>
                                            
                                            <td class="p-3 border-r border-gray-200 font-medium">
                                                <?php if(!empty($pay['payment_image'])): ?>
                                                    <a href="uploads/<?= htmlspecialchars($pay['payment_image']) ?>" target="_blank" class="text-blue-800 hover:text-blue-900 font-bold inline-flex items-center gap-1 hover:underline">
                                                        👁 View Slip
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-gray-400 italic">ပုံမရှိပါ</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="p-3 border-r border-gray-200">
                                                <?php if($pay['status'] === 'paid'): ?>
                                                    <span class="inline-block border border-emerald-500 text-emerald-800 bg-emerald-50 text-[10px] font-bold px-2 py-0.5 uppercase tracking-wide">
                                                        Paid
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-block border border-amber-500 text-amber-800 bg-amber-50 text-[10px] font-bold px-2 py-0.5 uppercase tracking-wide">
                                                        Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="p-3 text-center">
                                                <?php if($pay['status'] !== 'paid'): ?>
                                                    <form action="" method="POST" onsubmit="return confirm('ဤငွေပေးချေမှုကို အတည်ပြုရန် သေချာပါသလားူ');" class="inline-block">
                                                        <input type="hidden" name="installment_id" value="<?= htmlspecialchars($pay['installment_id']) ?>">
                                                        <button type="submit" name="approve_payment" class="border border-gray-800 bg-gray-900 hover:bg-gray-800 text-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider transition cursor-pointer">
                                                            Approve
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-emerald-800 font-bold text-xs inline-flex items-center justify-center gap-1">
                                                        ✓ Completed
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-gray-400 italic bg-gray-50">
                                            ငွေပေးချေမှုမှတ်တမ်း မရှိသေးပါ။
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('mobMenuOverlay');
            
            if (sidebar && sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if(overlay) overlay.classList.remove('hidden');
            } else if(sidebar) {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                if(overlay) overlay.classList.add('hidden');
            }
        }
    </script>
</body>
</html>