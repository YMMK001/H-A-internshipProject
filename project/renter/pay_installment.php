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

// 2. Read installment_id from URL query string (supports 'installment_id' or 'id')
$installment_id = $_GET['installment_id'] ?? $_GET['id'] ?? null;

if (!$installment_id) {
    die("Invalid Request: Missing Installment ID");
}

// 3. Context Flow Tracking State
$from_context = $_GET['from'] ?? '';
$is_profile_context = ($from_context === 'profile');

// 4. Fetch installment details from DB
$stmt = $pdo->prepare("SELECT * FROM installments WHERE id = :id");
$stmt->execute([':id' => $installment_id]);
$installment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$installment) {
    die("Installment record not found!");
}

// Dynamic Back / Success Redirect URL
if ($is_profile_context) {
    $back_url = "renter_profile.php"; 
} else {
    $back_url = "installment_list.php?contract_id=" . urlencode($installment['contract_id']);
}

// 5. Fetch active payment methods configured by owner
$method_stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE is_active = 1");
$method_stmt->execute();
$payment_methods = $method_stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Form Submission Handling (Payment Receipt Upload)
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method_id = $_POST['payment_method_id'];
    $paid_amount       = $_POST['paid_amount'];
    
    $image_name = null;
    if (isset($_FILES['payment_image']) && $_FILES['payment_image']['error'] === 0) {
        $target_dir  = "../admin/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_name  = time() . '_' . basename($_FILES["payment_image"]["name"]);
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES["payment_image"]["tmp_name"], $target_file);
    }

    try {
        $pdo->beginTransaction();

        // Record the payment submission
        $insert_pay = $pdo->prepare("INSERT INTO payments (installment_id, payment_method_id, paid_amount, payment_image) 
                                     VALUES (:ins_id, :method_id, :amount, :image)");
        $insert_pay->execute([
            ':ins_id'    => $installment_id,
            ':method_id' => $payment_method_id,
            ':amount'    => $paid_amount,
            ':image'     => $image_name
        ]);

        // Update installment status to pending
        $update_stmt = $pdo->prepare("
            UPDATE installments 
            SET status = 'pending'
            WHERE id = :installment_id
        ");
        $update_stmt->execute([':installment_id' => $installment_id]);

        $pdo->commit();
        
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

// Renderable Form Content Function
function renderPaymentForm($installment, $payment_methods, $back_url, $message) {
?>
    <div class="w-full bg-white p-6 sm:p-8 rounded-none border border-stone-300 shadow-xs space-y-6">
        
        <div class="border-b border-stone-200 pb-4 flex justify-between items-start">
            <div>
                <h2 class="text-xl font-serif font-bold text-stone-900 uppercase tracking-wide">PROCESS PAYMENT</h2>
                <p class="text-xs text-stone-500 font-serif italic mt-1">အရစ်ကျအပိုင်း - MONTH <?= htmlspecialchars($installment['installment_period']) ?> အတွက် ငွေပေးရန်</p>
            </div>
            <a href="<?= $back_url ?>" class="text-[11px] font-serif text-stone-500 hover:text-stone-900 uppercase tracking-wider transition">
                &larr; BACK
            </a>
        </div>

        <?php if(!empty($message)): ?>
            <div class="p-3 bg-red-50 border-l-4 border-red-700 text-xs text-red-800 font-sans">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <!-- Left Column: Inputs -->
                <div class="space-y-6">
                    <!-- Amount Payable -->
                    <div>
                        <label class="block text-xs font-serif text-stone-500 italic mb-2">ပေးချေရမည့် ပမာဏ</label>
                        <input type="text" value="<?= number_format($installment['amount_to_pay']) ?> MMK" readonly
                               class="w-full bg-stone-50 border border-stone-200 px-4 py-3 text-stone-900 font-bold font-sans text-sm rounded-none focus:outline-none cursor-not-allowed">
                        <input type="hidden" name="paid_amount" value="<?= $installment['amount_to_pay'] ?>">
                    </div>

                    <!-- Payment Method Dropdown -->
                    <div>
                        <label class="block text-xs font-serif text-stone-500 italic mb-2">ငွေလွှဲမည့် စနစ်ကို ရွေးချယ်ပါ</label>
                        <div class="relative">
                            <select id="paymentMethodSelect" name="payment_method_id" required onchange="updatePaymentDetails()"
                                    class="w-full px-4 py-2.5 bg-white border border-stone-300 rounded-none text-stone-700 focus:outline-none focus:border-stone-500 appearance-none cursor-pointer">
                                <option value="" data-image="">-- ရွေးချယ်ရန် --</option>
                                <?php foreach ($payment_methods as $method): ?>
                                    <?php 
                                        $img_src = '';
                                        if (!empty($method['image'])) {
                                            $dbPath = trim($method['image']);
                                            $dbPath = ltrim($dbPath, '/');
                                            
                                            if (strpos($dbPath, 'admin/') === 0) {
                                                $img_src = '../' . $dbPath;
                                            } else if (strpos($dbPath, 'uploads/') === 0) {
                                                $img_src = '../admin/' . $dbPath;
                                            } else {
                                                $img_src = '../admin/uploads/' . $dbPath;
                                            }
                                        }
                                    ?>
                                    <option value="<?= $method['id'] ?>" data-image="<?= htmlspecialchars($img_src) ?>">
                                        <?= htmlspecialchars($method['name']) ?> (Acc Name: <?= htmlspecialchars($method['account_name']) ?> | နံပါတ်: <?= htmlspecialchars($method['account_number']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-stone-500">
                                <svg class="fill-current h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Placeholder & Dynamic Compact QR Code Container -->
                <div>
                    <!-- Default Placeholder when no payment method is selected -->
                    <div id="qrPlaceholder" class="border border-dashed border-stone-200 bg-stone-50/50 p-6 text-center flex flex-col items-center justify-center min-h-[140px]">
                        <svg class="w-8 h-8 text-stone-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                        <p class="text-xs text-stone-400 italic font-serif">ငွေလွှဲမည့် အကောင့်ကို ရွေးချယ်ပါက QR Code ပေါ်လာပါမည်</p>
                    </div>

                    <!-- Compact QR Code Display Box -->
                    <div id="qrCodeContainer" class="hidden  p-3 rounded-none text-center transition-all">
                        <p class="text-[10px] font-bold text-stone-600 uppercase tracking-wider mb-2">QR Code ကို Scan ဖတ်၍ ငွေလွှဲပါ</p>
                        <div class="flex justify-center">
                            <img id="qrCodeImage" src="" alt="Payment QR Code" 
                                 class="max-h-28 object-contain border border-stone-200 shadow-xs rounded-none bg-white p-1 cursor-pointer hover:opacity-90 transition" 
                                 onclick="openQrModal(this.src)" 
                                 onerror="handleImageError()"
                                 title="Click to enlarge">
                        </div>
                        <p id="qrHelperText" class="text-[9px] text-stone-400 mt-1.5 italic font-serif">ပုံကို နှိပ်၍ အကြီးကြည့်ရှုနိုင်ပါသည်။</p>
                    </div>
                </div>
            </div>

            <!-- File Upload Box -->
            <div>
                <label class="block text-xs font-serif text-stone-500 italic mb-2">ငွေလွှဲပြေစာ (SLIP) တင်ရန်</label>
                <div class="border border-dashed border-stone-300 p-6 bg-stone-50/30 flex flex-col items-center justify-center gap-3">
                    <label class="cursor-pointer bg-stone-100 hover:bg-stone-200 border border-stone-300 px-4 py-2 text-xs font-serif font-bold text-stone-800 shadow-xs uppercase tracking-wider transition">
                        CHOOSE FILE
                        <input type="file" name="payment_image" required accept="image/*" class="hidden" onchange="previewPaymentReceipt(this)">
                    </label>
                    <span id="fileName" class="text-xs text-stone-400 italic font-serif">No file chosen</span>
                    
                    <!-- Image Preview Box -->
                    <div id="receiptPreviewContainer" class="hidden mt-2">
                        <img id="receiptPreviewImage" src="" alt="Receipt Preview" class="max-h-48 border border-stone-300 p-1 bg-white shadow-xs object-contain">
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <a href="<?= $back_url ?>" 
                   class="w-full text-center py-3 bg-stone-100 hover:bg-stone-200 text-stone-800 font-serif text-xs font-semibold border border-stone-300 transition">
                    နောက်သို့
                </a>
                <button type="submit" 
                        class="w-full py-3 bg-[#0f172a] hover:bg-slate-900 text-[#fef3c7] font-serif text-xs font-semibold shadow-xs transition">
                    ပြေစာတင်မည်
                </button>
            </div>
        </form>
    </div>
<?php
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalHub - Process Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
        .font-serif-classic { font-family: 'Playfair Display', Georgia, serif; }
    </style>
</head>
<body class="bg-[#fcfbf9] text-stone-800 font-classic antialiased min-h-screen flex flex-col justify-between">

<?php 
    if (file_exists('homepageheader.php')) {
        include 'homepageheader.php';
    } else { ?>
        <header class="sticky top-0 z-50 w-full bg-white border-b border-stone-300 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="h-16 flex items-center justify-between gap-4">
                    <!-- Logo -->
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
    <?php }
?>

<div id="mobileDropdownMenu" class="hidden absolute top-16 left-0 w-full bg-stone-900/95 backdrop-blur-md z-50 border-t border-stone-800 shadow-xl p-6 space-y-4">
    <p class="text-[10px] uppercase font-bold tracking-widest text-stone-500 border-b border-stone-800 pb-2">Navigation Context</p>
    <nav class="flex flex-col space-y-3 font-medium text-sm text-stone-300">
        <a href="<?= $back_url ?>" class="hover:text-white transition">&larr; Cancel and Go Back</a>
    </nav>
</div>

<!-- Main Layout Container -->
<div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if ($is_profile_context): ?>
        <!-- Profile Context: 2-Column Layout with Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Sidebar -->
            <aside class="lg:col-span-1 space-y-6">

                <!-- Profile Card -->
                <div class="bg-white border border-stone-300 p-6 shadow-xs relative rounded-none">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-[#0f172a]"></div>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-stone-100 border border-stone-300 rounded-full flex items-center justify-center font-serif text-xl font-bold text-stone-700 uppercase shrink-0">
                            <?= strtoupper(substr($_SESSION['username'] ?? 'MO', 0, 2)) ?>
                        </div>
                        <div>
                            <h2 class="font-serif font-bold text-xl text-stone-900 leading-tight"><?= htmlspecialchars($_SESSION['username'] ?? 'Mokky') ?></h2>
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
                        <a href="installment_list.php?contract_id=<?= urlencode($installment['contract_id']) ?>&from=profile" class="px-3 py-2.5 bg-amber-50/80 text-amber-900 font-bold border-l-2 border-amber-700">
                            💳 Payment Ledgers
                        </a>
                    </nav>
                </div>

                <!-- Automated Ledger Notification Banner -->
                <div class="bg-[#0f172a] text-[#fef3c7] p-6 border border-[#b45309]/50 shadow-xs font-serif">
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-amber-400 mb-2">AUTOMATED LEDGER NOTIFICATION</h3>
                    <p class="text-xs text-stone-300 leading-relaxed italic">
                        Your statements and installment entries update automatically. For early contract notices or modifications, contact your primary property manager.
                    </p>
                </div>

            </aside>

            <!-- Right Main Area -->
            <main class="lg:col-span-2">
                <?php renderPaymentForm($installment, $payment_methods, $back_url, $message); ?>
            </main>
        </div>
    <?php else: ?>
        <!-- Homepage Context: Centered Payment Form Only -->
        <main class="max-w-2xl mx-auto">
            <?php renderPaymentForm($installment, $payment_methods, $back_url, $message); ?>
        </main>
    <?php endif; ?>
</div>

<!-- Modal Dialog for Zooming Image -->
<div id="qrModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-xs z-50 flex items-center justify-center p-4 cursor-pointer" onclick="closeQrModal()">
    <div class="relative max-w-sm w-full bg-white p-4 rounded-none shadow-2xl text-center" onclick="event.stopPropagation()">
        <img id="modalQrImage" src="" alt="Enlarged QR Code" class="w-full object-contain max-h-[75vh] mx-auto">
        <button onclick="closeQrModal()" class="mt-4 w-full bg-[#0f172a] text-[#fef3c7] font-serif text-xs font-semibold py-2 uppercase tracking-wider">ပိတ်မည်</button>
    </div>
</div>

<!-- FOOTER -->
<footer class="bg-white border-t border-stone-300 py-6 text-center text-xs font-serif text-stone-400 mt-12">
    &copy; 2026 RentalHub Platform. All dashboard data operations follow local leasing terms.
</footer>

<script>
    function updatePaymentDetails() {
        const select = document.getElementById('paymentMethodSelect');
        const selectedOption = select.options[select.selectedIndex];
        const imageUrl = selectedOption.getAttribute('data-image');
        
        const placeholder = document.getElementById('qrPlaceholder');
        const container = document.getElementById('qrCodeContainer');
        const img = document.getElementById('qrCodeImage');
        const helperText = document.getElementById('qrHelperText');

        if (imageUrl && imageUrl.trim() !== '') {
            img.style.display = "block";
            helperText.innerText = "ပုံကို နှိပ်၍ အကြီးကြည့်ရှုနိုင်ပါသည်။";
            img.src = imageUrl;
            
            if (placeholder) placeholder.classList.add('hidden');
            container.classList.remove('hidden');
        } else {
            img.src = '';
            container.classList.add('hidden');
            if (placeholder) placeholder.classList.remove('hidden');
        }
    }

    function handleImageError() {
        const img = document.getElementById('qrCodeImage');
        const helperText = document.getElementById('qrHelperText');
        img.style.display = "none";
        helperText.innerText = "QR Code ပုံရိပ် မရှိသေးပါ။ Account နံပါတ်သို့ တိုက်ရိုက် ငွေလွှဲပေးပါရန်။";
    }

    function openQrModal(src) {
        if (!src) return;
        document.getElementById('modalQrImage').src = src;
        document.getElementById('qrModal').classList.remove('hidden');
    }

    function closeQrModal() {
        document.getElementById('qrModal').classList.add('hidden');
    }

    function toggleMobileMenu() {
        const menu = document.getElementById('mobileDropdownMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    function previewPaymentReceipt(input) {
        const fileNameSpan = document.getElementById('fileName');
        const container = document.getElementById('receiptPreviewContainer');
        const previewImg = document.getElementById('receiptPreviewImage');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            fileNameSpan.textContent = file.name;

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                container.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        } else {
            fileNameSpan.textContent = 'No file chosen';
            previewImg.src = '';
            container.classList.add('hidden');
        }
    }
</script>
</body>
</html>