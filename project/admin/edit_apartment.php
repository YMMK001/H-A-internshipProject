<?php
// 1. Database Connection Configuration
$host    = 'localhost';
$db      = 'intern_test';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// 2. GET Method ဖြင့် ID စစ်ဆေးပြီး Data ဆွဲထုတ်ခြင်း
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Apartment ID is missing.");
}

$apartment_id = (int)$_GET['id'];

$sql = "
    SELECT 
        a.id AS apartment_id,
        a.rental_house_id,
        h.title, 
        h.description,
        h.township, 
        h.city, 
        h.full_address,
        h.amenities,
        a.floor_level,
        a.max_occupy,
        a.apartment_price,
        a.deposit_amount,
        a.is_available
    FROM apartments a
    INNER JOIN rental_houses h ON h.id = a.rental_house_id
    WHERE a.id = :apartment_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':apartment_id' => $apartment_id]);
$apartment = $stmt->fetch();

if (!$apartment) {
    die("Error: Apartment listing not found.");
}

// 3. Form Submit (POST) လုပ်လာလျှင် Data များကို Update လုပ်ခြင်း
$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $max_occupy      = (int)$_POST['max_occupy'];
    $apartment_price = (float)$_POST['apartment_price'];
    $deposit_amount  = $apartment_price / 5; 

    // ရွေးချယ်လိုက်သော Amenities စာရင်းကို လက်ခံပြီး Comma (,) ဖြင့် စာသားပြန်ဖွဲ့ခြင်း
    $posted_amenities = $_POST['amenities'] ?? [];
    $amenities_string = implode(', ', $posted_amenities);

    try {
        $pdo->beginTransaction();

        // 1. apartments table ကို Update လုပ်ခြင်း
        $update_apartment_sql = "
            UPDATE apartments 
            SET max_occupy = :max_occupy, apartment_price = :apartment_price, deposit_amount = :deposit_amount
            WHERE id = :apartment_id
        ";
        $stmt2 = $pdo->prepare($update_apartment_sql);
        $stmt2->execute([
            ':max_occupy'      => $max_occupy,
            ':apartment_price' => $apartment_price,
            ':deposit_amount'  => $deposit_amount,
            ':apartment_id'    => $apartment_id
        ]);

        // 2. rental_houses table ရှိ amenities ကော်လံကိုပါ တစ်ပြိုင်တည်း Update လုပ်ခြင်း
        $update_house_sql = "
            UPDATE rental_houses 
            SET amenities = :amenities 
            WHERE id = :house_id
        ";
        $stmt3 = $pdo->prepare($update_house_sql);
        $stmt3->execute([
            ':amenities' => $amenities_string,
            ':house_id'  => $apartment['rental_house_id']
        ]);

        $pdo->commit();
        
        // Update လုပ်ပြီးနောက် Form တွင် ပြန်လည်ပြသရန် ဒေတာအသစ်ကို ပြန်ဆွဲထုတ်သည်
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':apartment_id' => $apartment_id]);
        $apartment = $stmt->fetch();

        $success_msg = "အချက်အလက်များနှင့် ဝန်ဆောင်မှုများကို အောင်မြင်စွာ ပြင်ဆင်ပြီးပါပြီ။";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "ပြင်ဆင်ခြင်း မအောင်မြင်ပါ။ အမှား - " . $e->getMessage();
    }
}

$current_amenities = array_filter(explode(',', $apartment['amenities'] ?? ''));
$current_amenities = array_map('trim', $current_amenities);
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Apartment - Rental Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<!-- HTML အပိုင်းကို အောက်ပါအတိုင်း ပြင်ဆင်ပါ -->
<body class="bg-gray-100 h-screen text-gray-900 font-sans flex overflow-hidden">

    <!-- Owner Sidebar Header (Stable ဖြစ်နေမည်) -->
    <?php include 'ownerheader.php'; ?>

    <!-- Main Content Wrapper (ဤနေရာကိုပဲ သီးသန့် Scroll ဆွဲမည်) -->
    <div class="flex-1 h-full overflow-y-auto ">

    <div class="sticky top-0 z-20 bg-white border-b border-gray-300 shadow-sm px-4 py-3 mb-6 flex items-center justify-between font-sans rounded-sm">
                    <div class="flex items-center space-x-3">
                        <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                            ☰ Menu
                        </button>
                        <div class="hidden sm:flex items-center space-x-2 text-xs text-gray-500">
                            <span class="text-gray-900 font-bold text-2xl">Edit Setup</span>
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


        <div class="w-full py-4 px-4 sm:px-6 lg:px-8">
            
            <!-- ကျန်ရှိသော Form အချက်အလက်များ... -->

            <?php if (!empty($success_msg)): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm rounded font-medium shadow-sm">
                    ✅ <?= $success_msg ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="mb-6 p-4 bg-rose-50 border border-rose-300 text-rose-800 text-sm rounded font-medium shadow-sm">
                    ❌ <?= $error_msg ?>
                </div>
            <?php endif; ?>

            <div class="bg-white border border-gray-300 shadow-sm rounded-sm overflow-hidden">
                <div class="bg-slate-800 px-6 py-4 border-b border-gray-700 ">
                    <h2 class="text-lg font-bold text-white uppercase tracking-wider">✏️ Edit Apartment Form</h2>
                   
                </div>

                <form action="" method="POST" class="p-6 space-y-5">
                    
                    <!-- Title (Disabled) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Apartment Title</label>
                        <input type="text" value="<?= htmlspecialchars($apartment['title']) ?>" disabled
                               class="w-full text-sm border border-gray-200 px-3 py-2 rounded bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none">
                    </div>

                    <!-- Description (Disabled) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Description</label>
                        <textarea rows="3" disabled class="w-full text-sm border border-gray-200 px-3 py-2 rounded bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none"><?= htmlspecialchars($apartment['description'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Township (Disabled) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Township</label>
                            <input type="text" value="<?= htmlspecialchars($apartment['township']) ?>" disabled
                                   class="w-full text-sm border border-gray-200 px-3 py-2 rounded bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none">
                        </div>
                        <!-- City (Disabled) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">City</label>
                            <input type="text" value="<?= htmlspecialchars($apartment['city']) ?>" disabled
                                   class="w-full text-sm border border-gray-200 px-3 py-2 rounded bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none">
                        </div>
                    </div>

                    <!-- Full Address (Disabled) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Full Address</label>
                        <textarea rows="2" disabled class="w-full text-sm border border-gray-200 px-3 py-2 rounded bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none"><?= htmlspecialchars($apartment['full_address']) ?></textarea>
                    </div>

                    <hr class="border-gray-200 my-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Floor Level (Disabled) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Floor Level</label>
                            <input type="text" value="<?= htmlspecialchars($apartment['floor_level'] ?? '') ?>" disabled
                                   class="w-full text-sm border border-gray-200 px-3 py-2 rounded bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none">
                        </div>
                        <!-- Max Occupy (EDITABLE) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-700 mb-2">Max Occupy</label>
                            <input type="number" name="max_occupy" value="<?= htmlspecialchars($apartment['max_occupy']) ?>" required min="1"
                                   class="w-full text-sm border border-gray-300 px-3 py-2 rounded focus:outline-none focus:border-slate-800 transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Monthly Price (EDITABLE) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-700 mb-2">Monthly Price (MMK)</label>
                            <input type="number" step="0.01" id="apartment_price" name="apartment_price" value="<?= htmlspecialchars($apartment['apartment_price']) ?>" required min="0"
                                   class="w-full text-sm border border-gray-300 px-3 py-2 rounded focus:outline-none focus:border-slate-800 transition">
                        </div>
                        <!-- Deposit Amount (READONLY) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-500 mb-2">Deposit Amount (MMK)</label>
                            <input type="number" step="0.01" id="deposit_amount" name="deposit_amount" value="<?= htmlspecialchars($apartment['deposit_amount']) ?>" readonly
                                   class="w-full text-sm border border-gray-200 px-3 py-2 rounded bg-gray-150 text-gray-500 cursor-not-allowed focus:outline-none">
                        </div>
                    </div>

                    <!-- Services & Amenities (EDITABLE) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-700 mb-2">Services & Amenities (ဝန်ဆောင်မှုများ)</label>
                        <div class="flex flex-wrap gap-4 p-3 bg-white border border-gray-300 rounded text-sm">
                            <?php 
                            $available_options = ['ရေ', 'မီး', 'Lift', 'Wi-Fi', 'Security', 'Car Parking'];
                            foreach ($available_options as $option): 
                                $checked = in_array($option, $current_amenities) ? 'checked' : '';
                            ?>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="amenities[]" value="<?= $option ?>" <?= $checked ?> class="rounded text-slate-800 focus:ring-slate-500 border-gray-300 cursor-pointer">
                                    <span class="text-gray-700 text-xs font-medium"><?= $option ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Listing Status (Disabled) -->
                    <div class="flex items-center justify-between p-3 bg-gray-100 border border-gray-200 rounded opacity-75">
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wide text-gray-400">Listing Status / အခန်းအခြေအနေ (ပြင်၍မရပါ)</span>
                            <span class="text-xs text-gray-400">ဤအချက်အလက်ကို ပိုင်ရှင်မှ ပြင်ဆင်ခွင့်မရှိပါ။</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-not-allowed">
                            <input type="checkbox" <?= (int)$apartment['is_available'] === 1 ? 'checked' : '' ?> disabled class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <a href="hostellist.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs uppercase tracking-wider px-4 py-2.5 border border-gray-300 rounded transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs uppercase tracking-wider px-5 py-2.5 rounded border border-slate-700 shadow-sm transition-colors">
                            💾 Save Changes
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.getElementById('apartment_price');
            const depositInput = document.getElementById('deposit_amount');

            priceInput.addEventListener('input', function() {
                const monthlyPrice = parseFloat(this.value) || 0;
                const calculatedDeposit = (monthlyPrice / 5).toFixed(2);
                depositInput.value = calculatedDeposit;
            });
        });
    </script>

</body>
</html>