<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Database Connection
$host = 'localhost';
$db_name = 'intern_test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


// 2. Read installment_id from URL query string
$installment_id = $_GET['installment_id'] ?? $_GET['id'] ?? null;

if (!$installment_id) {
    die("Invalid Request: Missing Installment ID");
}


// 3. Context Flow Tracking State
$from_context = $_GET['from'] ?? '';
$is_profile_context = ($from_context === 'profile');

// Dynamic Back / Success Redirect URL
if ($is_profile_context) {
    $back_url = "renter_profile.php";
} else {
    $back_url = "installment_list.php?contract_id=" . urlencode($installment['contract_id'] ?? '');
}


// 4. Fetch installment details from DB
$stmt = $pdo->prepare("SELECT * FROM installments WHERE id = :id");

$stmt->execute([
    ':id' => $installment_id
]);

$installment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$installment) {
    die("Installment record not found!");
}


// Rebuild back URL after installment is available
if (!$is_profile_context) {
    $back_url = "installment_list.php?contract_id=" . urlencode($installment['contract_id']);
}


// 5. Fetch active payment methods configured by owner
$method_stmt = $pdo->prepare(
    "SELECT * FROM payment_methods WHERE is_active = 1"
);

$method_stmt->execute();

$payment_methods = $method_stmt->fetchAll(PDO::FETCH_ASSOC);


// 6. Form Submission Handling
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payment_method_id = $_POST['payment_method_id'];
    $paid_amount = $_POST['paid_amount'];

    // Payment Receipt Image Upload
    $image_name = null;

    if (isset($_FILES['payment_image']) && $_FILES['payment_image']['error'] === 0) {

        $target_dir = "../admin/uploads/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . '_' . basename($_FILES["payment_image"]["name"]);
        $target_file = $target_dir . $image_name;

        move_uploaded_file(
            $_FILES["payment_image"]["tmp_name"],
            $target_file
        );
    }

    try {

        $pdo->beginTransaction();

        // Record Payment
        $insert_pay = $pdo->prepare(
            "INSERT INTO payments
            (
                installment_id,
                payment_method_id,
                paid_amount,
                payment_image
            )
            VALUES
            (
                :ins_id,
                :method_id,
                :amount,
                :image
            )"
        );

        $insert_pay->execute([
            ':ins_id' => $installment_id,
            ':method_id' => $payment_method_id,
            ':amount' => $paid_amount,
            ':image' => $image_name
        ]);


        // Update Installment Status
        $update_stmt = $pdo->prepare(
            "UPDATE installments
             SET status = 'pending'
             WHERE id = :installment_id"
        );

        $update_stmt->execute([
            ':installment_id' => $installment_id
        ]);

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


// ======================================================
// PAYMENT FORM
// ======================================================

function renderPaymentForm($installment, $payment_methods, $back_url, $message)
{
?>

    <div class="w-full bg-white p-6 sm:p-8 rounded-none border border-stone-300 shadow-xs space-y-6">

        <!-- HEADER -->
        <div class="border-b border-stone-200 pb-4 flex justify-between items-start">

            <div>

                <h2 class="text-xl font-serif font-bold text-stone-900 uppercase tracking-wide">
                    PROCESS PAYMENT
                </h2>

                <p class="text-xs text-stone-500 font-serif italic mt-1">
                    အရစ်ကျအပိုင်း - MONTH
                    <?= htmlspecialchars($installment['installment_period']) ?>
                    အတွက် ငွေပေးရန်
                </p>

            </div>

            <a href="<?= $back_url ?>" class="text-[11px] font-serif text-stone-500 hover:text-stone-900 uppercase tracking-wider transition">
                &larr; BACK
            </a>

        </div>


        <!-- ERROR MESSAGE -->
        <?php if (!empty($message)): ?>

            <div class="p-3 bg-red-50 border-l-4 border-red-700 text-xs text-red-800 font-sans">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <!-- FORM -->
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">

            <!-- Hidden input keeps the original POST field name -->
            <input type="hidden" id="paymentMethodInput" name="payment_method_id" value="" required>


            <!-- TWO COLUMN LAYOUT -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

                <!-- LEFT COLUMN -->
                <div class="space-y-6">

                    <!-- AMOUNT PAYABLE -->
                    <div>

                        <label class="block text-xs font-serif text-stone-500 italic mb-2">
                            ပေးချေရမည့် ပမာဏ
                        </label>

                        <input
                            type="text"
                            value="<?= number_format($installment['amount_to_pay']) ?> MMK"
                            readonly
                            class="w-full bg-stone-50 border border-stone-200 px-4 py-3 text-stone-900 font-bold font-sans text-sm rounded-none focus:outline-none cursor-not-allowed"
                        >

                        <input
                            type="hidden"
                            name="paid_amount"
                            value="<?= $installment['amount_to_pay'] ?>"
                        >

                    </div>


                    <!-- PAYMENT METHOD CARDS -->
                    <div>

                        <label class="block text-xs font-serif text-stone-500 italic mb-3">
                            ငွေလွှဲမည့် စနစ်ကို ရွေးချယ်ပါ
                        </label>

                        <div class="grid grid-cols-2 gap-3" id="paymentMethodCards">

                            <?php foreach ($payment_methods as $method): ?>

                                <?php

                                $img_src = '';

                                if (!empty($method['image'])) {

                                    $dbPath = trim($method['image']);
                                    $dbPath = ltrim($dbPath, '/');

                                    if (strpos($dbPath, 'admin/') === 0) {

                                        $img_src = '../' . $dbPath;

                                    } elseif (strpos($dbPath, 'uploads/') === 0) {

                                        $img_src = '../admin/' . $dbPath;

                                    } else {

                                        $img_src = '../admin/uploads/' . $dbPath;
                                    }
                                }

                                ?>

                                <button
                                    type="button"
                                    class="payment-method-card w-full min-h-[96px] border border-stone-200 bg-white px-4 py-4 text-left transition-all duration-200 hover:border-amber-600 hover:shadow-sm focus:outline-none"
                                    data-method-id="<?= $method['id'] ?>"
                                    data-image="<?= htmlspecialchars($img_src) ?>"
                                    onclick="selectPaymentMethod(this)"
                                >

                                    <div class="flex flex-col justify-center h-full">

                                        <span class="font-serif font-bold text-sm text-stone-800">
                                            <?= htmlspecialchars($method['name']) ?>
                                        </span>

                                        <span class="text-[9px] text-stone-400 mt-2 leading-relaxed">
                                            <?= htmlspecialchars($method['account_name']) ?>
                                        </span>

                                        <span class="text-[10px] text-stone-500 font-mono mt-1">
                                            <?= htmlspecialchars($method['account_number']) ?>
                                        </span>

                                    </div>

                                </button>

                            <?php endforeach; ?>

                        </div>

                        <p id="paymentMethodError" class="hidden text-[10px] text-red-600 mt-2">
                            ငွေလွှဲမည့် စနစ်ကို ရွေးချယ်ပါ။
                        </p>

                    </div>

                </div>


                <!-- RIGHT COLUMN: FIXED IMAGE PREVIEW -->
                <div class="w-full">

                    <!-- PLACEHOLDER -->
                    <div id="qrPlaceholder" class="w-full h-[330px] border border-dashed border-stone-200 bg-stone-50/50 flex flex-col items-center justify-center text-center p-6">

                        <div class="w-12 h-12 border border-stone-200 bg-white flex items-center justify-center mb-4">

                            <svg class="w-6 h-6 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 001 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z"
                                ></path>
                            </svg>

                        </div>

                        <p class="text-xs text-stone-500 italic font-serif">
                            Payment Method ကို ရွေးချယ်ပါ
                        </p>

                        <p class="text-[10px] text-stone-400 mt-1">
                            ရွေးချယ်ထားသော Payment QR Code
                            သို့မဟုတ် Image ကို ဤနေရာတွင် ပြသပါမည်
                        </p>

                    </div>


                    <!-- FIXED IMAGE CONTAINER -->
                    <div id="qrCodeContainer" class="hidden w-full h-[330px] border border-stone-200 bg-white p-5 flex flex-col">

                        <div class="flex items-center justify-between border-b border-stone-100 pb-3 mb-3">

                            <p id="selectedPaymentTitle" class="text-[10px] font-bold text-stone-700 uppercase tracking-wider">
                                PAYMENT QR CODE
                            </p>

                            <span class="text-[9px] text-amber-700 font-serif italic">
                                Scan & Pay
                            </span>

                        </div>


                        <!-- Stable Image Area -->
                        <div class="w-full flex-1 flex items-center justify-center overflow-hidden">

                            <img
                                id="qrCodeImage"
                                src=""
                                alt="Payment QR Code"
                                class="w-full h-full object-contain cursor-pointer hover:opacity-90 transition"
                                onclick="openQrModal(this.src)"
                                onerror="handleImageError()"
                                title="Click to enlarge"
                            >

                        </div>

                        <p id="qrHelperText" class="text-[9px] text-stone-400 mt-3 text-center italic font-serif">
                            ပုံကို နှိပ်၍ အကြီးကြည့်ရှုနိုင်ပါသည်။
                        </p>

                    </div>

                </div>

            </div>


            <!-- PAYMENT RECEIPT UPLOAD -->
            <div>

                <label class="block text-xs font-serif text-stone-500 italic mb-2">
                    ငွေလွှဲပြေစာ (SLIP) တင်ရန်
                </label>

                <div class="border border-dashed border-stone-300 p-6 bg-stone-50/30 flex flex-col items-center justify-center gap-3">

                    <label class="cursor-pointer bg-stone-100 hover:bg-stone-200 border border-stone-300 px-4 py-2 text-xs font-serif font-bold text-stone-800 shadow-xs uppercase tracking-wider transition">

                        CHOOSE FILE

                        <input
                            type="file"
                            name="payment_image"
                            required
                            accept="image/*"
                            class="hidden"
                            onchange="previewPaymentReceipt(this)"
                        >

                    </label>

                    <span id="fileName" class="text-xs text-stone-400 italic font-serif">
                        No file chosen
                    </span>

                    <div id="receiptPreviewContainer" class="hidden mt-2">

                        <img
                            id="receiptPreviewImage"
                            src=""
                            alt="Receipt Preview"
                            class="max-h-48 border border-stone-300 p-1 bg-white shadow-xs object-contain"
                        >

                    </div>

                </div>

            </div>


            <!-- BUTTONS -->
            <div class="grid grid-cols-2 gap-4 pt-4">

                <a
                    href="<?= $back_url ?>"
                    class="w-full text-center py-3 bg-stone-100 hover:bg-stone-200 text-stone-800 font-serif text-xs font-semibold border border-stone-300 transition"
                >
                    နောက်သို့
                </a>

                <button
                    type="submit"
                    class="w-full py-3 bg-[#0f172a] hover:bg-slate-900 text-[#fef3c7] font-serif text-xs font-semibold shadow-xs transition"
                >
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

        .font-classic {
            font-family: 'Noto Sans Myanmar', sans-serif;
        }

        .title-classic {
            font-family: 'Playfair Display', 'Noto Sans Myanmar', serif;
        }

        .font-serif-classic {
            font-family: 'Playfair Display', Georgia, serif;
        }

        /* Only the payment method card design */
        .payment-method-card {
            position: relative;
        }

        .payment-method-card.is-selected {
            border-color: #b45309;
            background-color: #fffbeb;
            box-shadow: 0 0 0 1px #b45309;
        }

        .payment-method-card.is-selected::after {
            content: "✓";
            position: absolute;
            top: 10px;
            right: 12px;
            font-size: 12px;
            font-weight: bold;
            color: #b45309;
        }

    </style>

</head>


<body class="bg-[#fcfbf9] text-stone-800 font-classic antialiased min-h-screen flex flex-col justify-between">


<?php

if (file_exists('homepageheader.php')) {
    include 'homepageheader.php';
} else {

?>

    <header class="sticky top-0 z-50 w-full bg-white border-b border-stone-300 shadow-xs">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="h-16 flex items-center justify-between gap-4">

                <a href="renterhomepage.php" class="flex items-center gap-3">

                    <div class="h-9 w-9 bg-[#0f172a] border border-[#b45309] flex items-center justify-center text-[#fef3c7] font-serif font-bold text-lg">
                        R
                    </div>

                    <span class="font-serif font-bold text-xl tracking-tight text-slate-900">
                        Rental
                        <span class="italic text-amber-700">Hub</span>
                    </span>

                </a>


                <div class="hidden md:flex items-center gap-2 flex-1 max-w-md mx-6">

                    <input
                        type="text"
                        placeholder="Search title or keyword..."
                        class="w-full text-xs px-3 py-2 border border-stone-300 rounded-none bg-stone-50/50 focus:bg-white focus:outline-none"
                    >

                    <select class="text-xs px-2 py-2 border border-stone-300 bg-stone-50/50 rounded-none text-stone-600 focus:outline-none">
                        <option>All Types</option>
                    </select>

                    <a
                        href="renterhomepage.php"
                        class="text-xs px-3 py-2 border border-stone-300 bg-stone-50 hover:bg-stone-100 font-serif"
                    >
                        Home
                    </a>

                </div>


                <button
                    onclick="toggleMobileMenu()"
                    class="lg:hidden text-stone-700 hover:text-black p-2 text-sm font-semibold"
                >
                    ☰ Menu
                </button>

            </div>

        </div>

    </header>

<?php
}
?>


<div id="mobileDropdownMenu" class="hidden absolute top-16 left-0 w-full bg-stone-900/95 backdrop-blur-md z-50 border-t border-stone-800 shadow-xl p-6 space-y-4">

    <p class="text-[10px] uppercase font-bold tracking-widest text-stone-500 border-b border-stone-800 pb-2">
        Navigation Context
    </p>

    <nav class="flex flex-col space-y-3 font-medium text-sm text-stone-300">

        <a href="<?= $back_url ?>" class="hover:text-white transition">
            &larr; Cancel and Go Back
        </a>

    </nav>

</div>


<div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php if ($is_profile_context): ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <aside class="lg:col-span-1 space-y-6">

                <div class="bg-white border border-stone-300 p-6 shadow-xs relative rounded-none">

                    <div class="absolute top-0 left-0 right-0 h-1 bg-[#0f172a]"></div>

                    <div class="flex items-center gap-4 mb-6">

                        <div class="w-14 h-14 bg-stone-100 border border-stone-300 rounded-full flex items-center justify-center font-serif text-xl font-bold text-stone-700 uppercase shrink-0">

                            <?= strtoupper(substr($_SESSION['username'] ?? 'MO', 0, 2)) ?>

                        </div>

                        <div>

                            <h2 class="font-serif font-bold text-xl text-stone-900 leading-tight">
                                <?= htmlspecialchars($_SESSION['username'] ?? 'Mokky') ?>
                            </h2>

                            <span class="text-[9px] uppercase font-bold tracking-wider text-amber-900 bg-amber-100/70 border border-amber-300/60 px-1.5 py-0.5 rounded-none mt-1 inline-block">
                                VERIFIED RESIDENT
                            </span>

                        </div>

                    </div>


                    <div class="border-t border-stone-200 pt-4 space-y-3 text-xs">

                        <div>

                            <span class="block text-stone-400 uppercase tracking-widest text-[9px] font-bold">
                                EMAIL COMMUNICATION
                            </span>

                            <span class="font-medium text-stone-800 font-sans">
                                <?= htmlspecialchars($_SESSION['email'] ?? 'mokky@gmail.com') ?>
                            </span>

                        </div>

                        <div>

                            <span class="block text-stone-400 uppercase tracking-widest text-[9px] font-bold">
                                SYSTEM REFERENCE
                            </span>

                            <span class="font-mono text-stone-600">
                                #UID-<?= str_pad($_SESSION['user_id'] ?? '0022', 4, '0', STR_PAD_LEFT) ?>
                            </span>

                        </div>

                    </div>

                </div>


                <div class="bg-white border border-stone-300 p-4 shadow-xs rounded-none">

                    <p class="text-[10px] uppercase font-bold tracking-widest text-stone-400 mb-3 border-b border-stone-100 pb-2">
                        QUICK NAVIGATION
                    </p>

                    <nav class="flex flex-col space-y-1 text-xs font-serif">

                        <a
                            href="renter_profile.php"
                            class="px-3 py-2.5 text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors"
                        >
                            📌 Overview
                        </a>

                        <a
                            href="renter_contract.php"
                            class="px-3 py-2.5 text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors"
                        >
                            📄 My Contracts
                        </a>

                        <a
                            href="installment_list.php?contract_id=<?= urlencode($installment['contract_id']) ?>&from=profile"
                            class="px-3 py-2.5 bg-amber-50/80 text-amber-900 font-bold border-l-2 border-amber-700"
                        >
                            💳 Payment Ledgers
                        </a>

                    </nav>

                </div>


                <div class="bg-[#0f172a] text-[#fef3c7] p-6 border border-[#b45309]/50 shadow-xs font-serif">

                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-amber-400 mb-2">
                        AUTOMATED LEDGER NOTIFICATION
                    </h3>

                    <p class="text-xs text-stone-300 leading-relaxed italic">
                        Your statements and installment entries
                        update automatically. For early contract
                        notices or modifications, contact your
                        primary property manager.
                    </p>

                </div>

            </aside>


            <main class="lg:col-span-2">

                <?php
                renderPaymentForm(
                    $installment,
                    $payment_methods,
                    $back_url,
                    $message
                );
                ?>

            </main>

        </div>

    <?php else: ?>

        <main class="max-w-2xl mx-auto">

            <?php
            renderPaymentForm(
                $installment,
                $payment_methods,
                $back_url,
                $message
            );
            ?>

        </main>

    <?php endif; ?>

</div>


<!-- QR IMAGE MODAL -->
<div
    id="qrModal"
    class="hidden fixed inset-0 bg-black/80 backdrop-blur-xs z-50 flex items-center justify-center p-4 cursor-pointer"
    onclick="closeQrModal()"
>

    <div
        class="relative max-w-sm w-full bg-white p-4 rounded-none shadow-2xl text-center"
        onclick="event.stopPropagation()"
    >

        <img
            id="modalQrImage"
            src=""
            alt="Enlarged QR Code"
            class="w-full object-contain max-h-[75vh] mx-auto"
        >

        <button
            onclick="closeQrModal()"
            class="mt-4 w-full bg-[#0f172a] text-[#fef3c7] font-serif text-xs font-semibold py-2 uppercase tracking-wider"
        >
            ပိတ်မည်
        </button>

    </div>

</div>


<footer class="bg-white border-t border-stone-300 py-6 text-center text-xs font-serif text-stone-400 mt-12">

    &copy; 2026 RentalHub Platform.
    All dashboard data operations follow
    local leasing terms.

</footer>


<script>

// =====================================
// PAYMENT METHOD CARD SELECTION
// =====================================

function selectPaymentMethod(card) {

    const cards = document.querySelectorAll('.payment-method-card');

    cards.forEach(function(item) {
        item.classList.remove('is-selected');
    });

    card.classList.add('is-selected');

    const methodId = card.getAttribute('data-method-id');
    const imageUrl = card.getAttribute('data-image');
    const methodName = card.querySelector('span').innerText;

    // Keep the original POST field name/value
    document.getElementById('paymentMethodInput').value = methodId;

    document.getElementById('paymentMethodError').classList.add('hidden');

    const placeholder = document.getElementById('qrPlaceholder');
    const container = document.getElementById('qrCodeContainer');
    const img = document.getElementById('qrCodeImage');
    const helperText = document.getElementById('qrHelperText');
    const title = document.getElementById('selectedPaymentTitle');

    title.innerText = methodName + ' PAYMENT';

    if (imageUrl && imageUrl.trim() !== '') {

        img.style.display = 'block';
        img.src = imageUrl;

        helperText.innerText = 'ပုံကို နှိပ်၍ အကြီးကြည့်ရှုနိုင်ပါသည်။';

        placeholder.classList.add('hidden');
        container.classList.remove('hidden');

    } else {

        img.src = '';

        container.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
}


// =====================================
// FORM VALIDATION FOR CARD SELECTION
// =====================================

document.addEventListener('DOMContentLoaded', function() {

    const form = document.querySelector('form');

    if (form) {

        form.addEventListener('submit', function(event) {

            const paymentInput = document.getElementById('paymentMethodInput');

            if (!paymentInput.value) {

                event.preventDefault();

                document.getElementById('paymentMethodError').classList.remove('hidden');
            }

        });
    }

});


// =====================================
// IMAGE ERROR
// =====================================

function handleImageError() {

    const img = document.getElementById('qrCodeImage');
    const helperText = document.getElementById('qrHelperText');

    img.style.display = 'none';

    helperText.innerText =
        'QR Code ပုံရိပ် မရှိသေးပါ။ Account နံပါတ်သို့ တိုက်ရိုက် ငွေလွှဲပေးပါရန်။';
}


// =====================================
// OPEN QR MODAL
// =====================================

function openQrModal(src) {

    if (!src) return;

    document.getElementById('modalQrImage').src = src;

    document.getElementById('qrModal').classList.remove('hidden');
}


// =====================================
// CLOSE QR MODAL
// =====================================

function closeQrModal() {

    document.getElementById('qrModal').classList.add('hidden');
}


// =====================================
// MOBILE MENU
// =====================================

function toggleMobileMenu() {

    const menu = document.getElementById('mobileDropdownMenu');

    if (menu) {
        menu.classList.toggle('hidden');
    }
}


// =====================================
// PAYMENT RECEIPT PREVIEW
// =====================================

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
        };

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