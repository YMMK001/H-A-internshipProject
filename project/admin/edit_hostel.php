<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL ကနေ ပြင်ဆင်မယ့် Hostel Room / Apartment ID ကို ဖတ်ယူခြင်း
$room_id = $_GET['id'] ?? $_GET['room_id'] ?? null;

if (!$room_id) {
    die("Error: Missing Identifier Token.");
}

try {
    $host        = 'localhost';
    $db_name     = 'intern_test'; 
    $username_db = 'root';              
    $password_db = ''; 

    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Database Schema Auto-Detection
    $cols = $db->query("DESCRIBE hostel_rooms")->fetchAll(PDO::FETCH_COLUMN);
    
    $id_col      = 'id';
    $house_col   = 'rental_house_id';
    $num_col     = 'room_num';
    $price_col   = 'monthly_price';
    $deposit_col = 'deposit'; 
    $status_col  = null; 

    foreach ($cols as $c) {
        $c_low = strtolower($c);
        if ($c_low === 'room_id' || $c_low === 'h_id' || $c_low === 'id') { $id_col = $c; }
        if (strpos($c_low, 'house') !== false || strpos($c_low, 'property') !== false || strpos($c_low, 'apartment') !== false) { $house_col = $c; }
        if (strpos($c_low, 'num') !== false || strpos($c_low, 'name') !== false) { $num_col = $c; }
        if (strpos($c_low, 'price') !== false || strpos($c_low, 'cost') !== false) { $price_col = $c; }
        if (strpos($c_low, 'deposit') !== false || strpos($c_low, 'security') !== false) { $deposit_col = $c; }
        if ($c_low === 'status' || $c_low === 'room_status' || $c_low === 'availability' || $c_low === 'condition') { 
            $status_col = $c; 
        }
    }

    // 2. အခန်းက စာချုပ်ချုပ်ဆိုပြီး လက်ရှိငှားရမ်းထားဆဲ (Rented/Occupied) ဖြစ်နေသလား စစ်ဆေးခြင်း
    $is_rented = false;
    if ($status_col) {
        $check_contract = $db->prepare("
            SELECT COUNT(*) FROM contracts 
            WHERE hostel_room_id = :room_id 
            AND end_date >= CURDATE() 
            LIMIT 1
        ");
        $check_contract->execute([':room_id' => $room_id]);
        $is_rented = $check_contract->fetchColumn() > 0;
    }

    // 3. Form Submit လုပ်လာပါက Data Update လုပ်မည့်အပိုင်း
    $success_msg = "";
    $error_msg   = "";
    $redirect    = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input_house_id = $_POST['rental_house_id'] ?? null;
        $input_room_num = $_POST['room_num'] ?? '';
        $input_price    = $_POST['monthly_price'] ?? 0;
        $input_deposit  = round($input_price / 5);
        $input_status   = $_POST['status'] ?? 'Available';

        if ($status_col && $is_rented && strtoupper($input_status) === 'AVAILABLE') {
            $error_msg = "ဤအခန်းသည် လက်ရှိတွင် ငှားရမ်းထားဆဲ (Rented) ဖြစ်သောကြောင့် 'Available' အဖြစ် ပြောင်းလဲ၍မရပါ။";
        } elseif (empty($input_room_num) || $input_price === '') {
            $error_msg = "ကျေးဇူးပြု၍ အခန်းနံပါတ်နှင့် လစဉ်စျေးနှုန်းကို ပြည့်စုံစွာထည့်ပါ။";
        } else {
            if ($status_col) {
                $update_query = "
                    UPDATE hostel_rooms 
                    SET {$house_col} = :house_id, 
                        {$num_col} = :room_num, 
                        {$price_col} = :price, 
                        {$deposit_col} = :deposit,
                        {$status_col} = :status 
                    WHERE {$id_col} = :room_id
                ";
                $bind_params = [
                    ':house_id' => $input_house_id,
                    ':room_num' => $input_room_num,
                    ':price'    => $input_price,
                    ':deposit'  => $input_deposit,
                    ':status'   => $input_status,
                    ':room_id'  => $room_id
                ];
            } else {
                $update_query = "
                    UPDATE hostel_rooms 
                    SET {$house_col} = :house_id, 
                        {$num_col} = :room_num, 
                        {$price_col} = :price, 
                        {$deposit_col} = :deposit
                    WHERE {$id_col} = :room_id
                ";
                $bind_params = [
                    ':house_id' => $input_house_id,
                    ':room_num' => $input_room_num,
                    ':price'    => $input_price,
                    ':deposit'  => $input_deposit,
                    ':room_id'  => $room_id
                ];
            }

            $stmt_u = $db->prepare($update_query);
            $stmt_u->execute($bind_params);
            
            $success_msg = "အချက်အလက်များကို အောင်မြင်စွာ ပြင်ဆင်ပြီးပါပြီ။ ခေတ္တစောင့်ဆိုင်းပေးပါ...";
            $redirect = true; 
        }
    }

    // 4. လက်ရှိ အခန်းအချက်အလက်ကို ပြန်ဆွဲထုတ်ခြင်း
    $query_room = "SELECT * FROM hostel_rooms WHERE {$id_col} = :room_id LIMIT 1";
    $stmt_r = $db->prepare($query_room);
    $stmt_r->execute([':room_id' => $room_id]);
    $room = $stmt_r->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        die("Error: Record profile not found.");
    }

    // ဒီအခန်းပိုင်ဆိုင်တဲ့ တူညီတဲ့ ID ရှိတဲ့ Hostel တစ်ခုတည်းကိုပဲ DB ကနေ ဆွဲထုတ်မည်
    $stmt_h = $db->prepare("SELECT id, title FROM rental_houses WHERE id = :house_id LIMIT 1");
    $stmt_h->execute([':house_id' => $room[$house_col] ?? 0]);
    $current_house = $stmt_h->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Admin - Edit Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', 'Noto Sans Myanmar', sans-serif; }
    </style>
    <?php if ($redirect): ?>
        <meta http-equiv="refresh" content="2;url=hostellist.php">
    <?php endif; ?>
</head>
<body class="bg-[#f4f6f9] text-slate-800 antialiased min-h-screen flex">

    <!-- Owner Sidebar Header -->
    <?php include 'ownerheader.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="flex-1 h-full overflow-y-auto">

        <!-- Top Header with Back to List Button -->
        <div class="sticky top-0 z-20 bg-white border-b border-gray-300 shadow-sm px-4 py-3 mb-6 flex items-center justify-between font-sans rounded-sm">
            <div class="flex items-center space-x-3">
                <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                    ☰ Menu
                </button>
                <div class="hidden sm:flex items-center space-x-2 text-xs text-gray-500">
                    <span class="text-gray-900 font-bold text-2xl">Edit Setup</span>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <!-- ညာဘက်အစွန်းတွင် နေရာချထားသော Back to List Button -->
                <a href="hostellist.php" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded border border-slate-700 text-xs font-bold uppercase tracking-wider shadow-sm transition flex items-center gap-1">
                    ← Back to List
                </a>
                
                <div class="pl-4 flex items-center space-x-2 border-l border-gray-200">
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

        <main class="p-8 max-w-2xl space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Asset Configuration</p>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight mt-0.5">Edit Hostel Form</h2>
                </div>
            </div>

            <?php if (!empty($success_msg)): ?>
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium rounded-sm shadow-xs">
                    ✅ <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium rounded-sm shadow-xs">
                    ⚠️ <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <!-- Form Edit Card Interface -->
            <form method="POST" class="bg-white border border-slate-200 p-6 rounded-sm shadow-sm space-y-5 text-xs">
                
                <!-- Hostel Data Option မဟုတ်ဘဲ လက်ရှိ ID တစ်ခုတည်းကိုသာ စာသားအဖြစ်ပြသခြင်း -->
                <div class="flex flex-col space-y-1.5">
                    <label class="text-[11px] uppercase tracking-wider font-bold text-slate-500">Belongs to Hostel</label>
                    
                    <?php if ($current_house): ?>
                        <!-- User ကို ရွေးချယ်ခွင့်မပေးဘဲ သော့ခတ်ပြသထားသည့် UI -->
                        <div class="w-full bg-slate-100 border border-slate-200 p-2.5 rounded-sm font-medium text-slate-500 cursor-not-allowed select-none flex items-center">
                            🏢 <?= htmlspecialchars($current_house['title']) ?>
                        </div>
                        <!-- POST လုပ်တဲ့အခါ Database ထဲ Data ပြန်ပါသွားစေရန် Hidden Input သုံးထားသည် -->
                        <input type="hidden" name="rental_house_id" value="<?= htmlspecialchars($current_house['id']) ?>">
                    <?php else: ?>
                        <div class="w-full bg-rose-50 border border-rose-200 p-2.5 rounded-sm font-medium text-rose-600">
                            ⚠️ ပိုင်ဆိုင်သည့် အဆောက်အဦး ရှာမတွေ့ပါ။
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col space-y-1.5">
                    <label class="text-[11px] uppercase tracking-wider font-bold text-slate-500">Room / Unit Number (အခန်းနံပါတ်)</label>
                    <input type="text" name="room_num" value="<?= htmlspecialchars($room[$num_col] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 p-2.5 rounded-sm focus:outline-none focus:border-slate-400 focus:bg-white font-semibold text-slate-800" placeholder="e.g., Room 302">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <label class="text-[11px] uppercase tracking-wider font-bold text-slate-500">Monthly Price (လစဉ်ကြေး - MMK)</label>
                        <input type="number" id="monthly_price" name="monthly_price" value="<?= htmlspecialchars($room[$price_col] ?? 0) ?>" class="w-full bg-slate-50 border border-slate-200 p-2.5 rounded-sm focus:outline-none focus:border-slate-400 focus:bg-white font-mono font-bold text-slate-800" placeholder="e.g., 150000">
                    </div>

                    <div class="flex flex-col space-y-1.5">
                        <label class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Deposit Amount (စပေါ်ငွေ - Auto 1/5)</label>
                        <input type="number" id="deposit" name="deposit" value="<?= htmlspecialchars($room[$deposit_col] ?? 0) ?>" readonly class="w-full bg-slate-100 border border-slate-200 p-2.5 rounded-sm font-mono font-bold text-slate-500 cursor-not-allowed select-none" placeholder="0">
                    </div>
                </div>

                <!-- Status Dropdown -->
                <?php if ($status_col): ?>
                <div class="flex flex-col space-y-1.5">
                    <label class="text-[11px] uppercase tracking-wider font-bold text-slate-500">Lease Availability Status</label>
                    <select id="room_status" name="status" class="w-full bg-slate-50 border border-slate-200 p-2.5 rounded-sm focus:outline-none focus:border-slate-400 focus:bg-white font-bold text-slate-700">
                        <?php 
                            $curr_status = strtoupper($room[$status_col] ?? 'AVAILABLE'); 
                            $status_options = ['AVAILABLE' => 'Available (ငှားရန်ရှိသည်)', 'OCCUPIED' => 'Occupied (ငှားရမ်းပြီး)', 'MAINTENANCE' => 'Maintenance (ပြင်ဆင်ဆဲ)'];
                            foreach ($status_options as $key => $label):
                                $is_disabled = ($is_rented && $key === 'AVAILABLE') ? 'disabled class="text-slate-300 bg-slate-100"' : '';
                        ?>
                            <option value="<?= $key ?>" <?= ($curr_status === $key) ? 'selected' : '' ?> <?= $is_disabled ?>>
                                <?= $label ?> <?= ($is_rented && $key === 'AVAILABLE') ? ' [Locked - Room is Rented]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($is_rented): ?>
                        <p class="text-[10px] text-amber-600 font-medium mt-1">⚠️ ဤအခန်းသည် တက်ကြွသော စာချုပ်ရှိနေသဖြင့် Available သို့ ပြောင်း၍မရပါ။</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                    <a href="hostellist.php" class="px-4 py-2.5 border border-slate-200 hover:bg-slate-50 font-bold uppercase tracking-wider rounded-sm text-slate-500 transition text-center">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 font-bold uppercase tracking-wider text-white shadow-sm rounded-sm transition">
                        💾 Save Changes
                    </button>
                </div>

            </form>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.getElementById('monthly_price');
            const depositInput = document.getElementById('deposit');

            if(priceInput.value) {
                depositInput.value = Math.round(parseFloat(priceInput.value) / 5);
            }

            priceInput.addEventListener('input', function() {
                const priceValue = parseFloat(this.value) || 0;
                depositInput.value = Math.round(priceValue / 5);
            });
        });
    </script>

</body>
</html>