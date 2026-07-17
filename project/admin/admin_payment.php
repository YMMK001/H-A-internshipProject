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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                        <p class="text-xs font-bold text-gray-900">Owner</p>
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
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 font-sans">Verifying/Checking Incoming Payments</h1>
                    </div>
                    
                    <div class="bg-slate-800 border border-slate-700 text-white font-medium text-xs uppercase tracking-wider px-4 py-2.5 rounded shadow-sm font-sans self-start sm:self-auto">
                        Total of <?= count($payments) ?> Transaction
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
                                    <th class="p-3 pl-4 w-[20%] border border-gray-700">Renter Name</th>
                                    <th class="p-3 w-[15%] border border-gray-700">Installment Month</th>
                                    <th class="p-3 w-[15%] border border-gray-700">Transferrd Amount</th>
                                    <th class="p-3 w-[15%] border border-gray-700">Payment Method</th>
                                    <th class="p-3 w-[15%] border border-gray-700">Slip</th>
                                    <th class="p-3 w-[10%] border border-gray-700">Status</th>
                                    <th class="p-3 w-[10%] border border-gray-700 text-center">Action</th>
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
                                                  <!-- ပြင်ဆင်ရန်ပုံစံ -->
<button type="button" onclick="openSlipModal('uploads/<?= htmlspecialchars($pay['payment_image']) ?>')" class="text-blue-800 hover:text-blue-900 font-bold inline-flex items-center gap-1 hover:underline cursor-pointer">
    👁 View Slip
</button>
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

    <!-- --- BLUR BACKDROP SLIP VIEW MODAL --- -->
    <div id="slipModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-md p-4 transition-all duration-300">
        <div class="relative bg-white p-2 max-w-md w-full rounded-md shadow-2xl border border-slate-200 transform scale-95 transition-transform duration-300 flex flex-col">
            <!-- Close Cross Button (✕) -->
            <button type="button" onclick="closeSlipModal()" class="absolute -top-3 -right-3 bg-rose-600 hover:bg-rose-700 text-white w-7 h-7 flex items-center justify-center rounded-full shadow-md font-bold text-sm border border-rose-500 transition cursor-pointer z-50">
                ✕
            </button>
            <!-- Slip Image Display -->
            <div class="w-full overflow-hidden rounded bg-slate-100 flex items-center justify-center max-h-[80vh]">
                <img id="modalImage" src="" alt="Payment Slip" class="max-w-full max-h-[75vh] object-contain">
            </div>
        </div>
    </div>

    <script>
        // Modal ဖွင့်ရန် Function
        function openSlipModal(imageSrc) {
            const modal = document.getElementById('slipModal');
            const modalImg = document.getElementById('modalImage');
            
            modalImg.src = imageSrc;
            modal.classList.remove('hidden');
            // Animation Dynamic Scale Smooth ဖြစ်စေရန်
            setTimeout(() => {
                modal.querySelector('.transform').classList.remove('scale-95');
                modal.querySelector('.transform').classList.add('scale-100');
            }, 10);
        }

        // Modal ပိတ်ရန် Function
        function closeSlipModal() {
            const modal = document.getElementById('slipModal');
            modal.querySelector('.transform').classList.remove('scale-100');
            modal.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 150);
        }

        // Modal ရဲ့ Background (အပြင်ဘက်) ကို နှိပ်ရင်လည်း ပိတ်သွားစေရန်
        document.getElementById('slipModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSlipModal();
            }
        });

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