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

// 2. URL ကလာတဲ့ installment_id ကို ဖတ်မယ်
$installment_id = $_GET['installment_id'] ?? null;

if (!$installment_id) {
    die("Invalid Request: Missing Installment ID");
}

// 💡 3. Dynamic Context Flow Tracking State (Homepage Link vs Profile Overview)
$from_context = $_GET['from'] ?? '';
$is_profile_context = ($from_context === 'profile');

// 4. သက်ဆိုင်ရာ အရစ်ကျ အချက်အလက်ကို Database ထဲက ဆွဲထုတ်မယ်
$stmt = $pdo->prepare("SELECT * FROM installments WHERE id = :id");
$stmt->execute([':id' => $installment_id]);
$installment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$installment) {
    die("Installment record not found!");
}

// 💡 Build Dynamic Back/Success Redirect URL based on Context
if ($is_profile_context) {
    $back_url = "renter_profile.php"; 
} else {
    $back_url = "installment_list.php?contract_id=" . urlencode($installment['contract_id']);
}

// 5. Owner ထည့်ထားတဲ့ အခကြေးငွေလက်ခံမည့် Payment Methods တွေကို ဆွဲထုတ်မယ် (Active ဖြစ်တာတွေပဲ)
$method_stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE is_active = 1");
$method_stmt->execute();
$payment_methods = $method_stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Form Submission (ငွေလွှဲပြေစာ တင်ခြင်းကို ကိုင်တွယ်ခြင်း)
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method_id = $_POST['payment_method_id'];
    $paid_amount       = $_POST['paid_amount'];
    
    $image_name = null;
    if (isset($_FILES['payment_image']) && $_FILES['payment_image']['error'] === 0) {
        $target_dir  = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $image_name  = time() . '_' . basename($_FILES["payment_image"]["name"]);
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES["payment_image"]["tmp_name"], $target_file);
    }

    try {
        $pdo->beginTransaction();

        $insert_pay = $pdo->prepare("INSERT INTO payments (installment_id, payment_method_id, paid_amount, payment_image) 
                                     VALUES (:ins_id, :method_id, :amount, :image)");
        $insert_pay->execute([
            ':ins_id'    => $installment_id,
            ':method_id' => $payment_method_id,
            ':amount'    => $paid_amount,
            ':image'     => $image_name
        ]);

        $pdo->commit();
        
        // Dynamic JavaScript Redirect after successful alert notice
        echo "<script>
            alert('ငွေပေးချေမှု အောင်မြင်ပါသည်။ ပိုင်ရှင်မှ အတည်ပြုပေးသည်အထိ စောင့်ဆိုင်းပေးပါ။');
            window.location.href = '" . $back_url . "';
        </script>";
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment - Classic Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-800 font-classic antialiased min-h-screen relative overflow-hidden">

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
    <p class="text-[10px] uppercase font-bold tracking-widest text-stone-500 border-b border-stone-800 pb-2">Navigation Context</p>
    <nav class="flex flex-col space-y-3 font-medium text-sm text-stone-300">
        <a href="<?= $back_url ?>" class="hover:text-white transition">← Cancel and Go Back</a>
    </nav>
</div>

<div class="flex flex-col min-h-screen w-full">
    <div class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md bg-white p-8 rounded-sm border border-gray-200 shadow-sm">
            
            <div class="border-b border-gray-100 pb-4 mb-5">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight uppercase title-classic">Process Payment</h2>
                <p class="text-gray-400 text-[11px] uppercase tracking-wider mt-1">အရစ်ကျအပိုင်း - Month <?= htmlspecialchars($installment['installment_period']) ?> အတွက် Ngwe Pay Yan</p>
            </div>

            <?php if(!empty($message)): ?>
                <div class="mb-5 p-3.5 bg-stone-50 border border-gray-300 text-slate-800 rounded-sm text-xs font-medium">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
                
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">ပေးချေရမည့် ပမာဏ</label>
                    <input type="text" value="<?= number_format($installment['amount_to_pay']) ?> MMK" readonly
                           class="w-full bg-stone-50 px-4 py-2.5 border border-gray-200 rounded-sm text-slate-900 font-bold text-sm focus:outline-none">
                    <input type="hidden" name="paid_amount" value="<?= $installment['amount_to_pay'] ?>">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">ငွေလွှဲမည့် စနစ်ကို ရွေးချယ်ပါ</label>
                    <div class="relative">
                        <select name="payment_method_id" required
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-sm text-gray-700 font-medium focus:outline-none focus:border-slate-900 appearance-none cursor-pointer">
                            <option value="">-- ရွေးချယ်ရန် --</option>
                            <?php foreach ($payment_methods as $method): ?>
                                <option value="<?= $method['id'] ?>">
                                    <?= htmlspecialchars($method['name']) ?> (Acc Name: <?= htmlspecialchars($method['account_name']) ?> | နံပါတ်: <?= htmlspecialchars($method['account_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="fill-current h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">ငွေလွှဲပြေစာ (Slip) တင်ရန်</label>
                    <div class="border border-dashed border-gray-300 bg-stone-50/50 p-4 rounded-sm text-center">
                        <input type="file" name="payment_image" required accept="image/*"
                               class="w-full block text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-sm file:border file:border-gray-300 file:text-[11px] file:font-bold file:uppercase file:tracking-wider file:bg-white file:text-slate-800 hover:file:bg-stone-50 cursor-pointer">
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="<?= $back_url ?>" 
                       class="w-1/2 text-center bg-stone-100 hover:bg-stone-200 border border-gray-300 text-slate-900 font-bold uppercase tracking-wider py-2.5 rounded-sm transition-all text-[11px] shadow-sm">
                        နောက်သို့
                    </a>
                    <button type="submit" 
                            class="w-1/2 bg-slate-900 hover:bg-slate-800 text-white font-bold uppercase tracking-wider py-2.5 rounded-sm transition-all text-[11px] shadow-sm">
                        ပြေစာတင်မည်
                    </button>
                </div>
            </form>
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