<?php
// ၁။ Session စတင်ခြင်း (Renter ID ကို သိရှိနိုင်ရန်)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
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

// ၂။ Login ဝင်ထားသော Renter ID ကို ယူခြင်း
// 💡 သင့်စနစ်၏ login session key ပေါ်မူတည်၍ ပြောင်းလဲနိုင်ပါသည်။
// လက်ရှိတွင် user_id = 16 ဖြင့် စမ်းသပ်နိုင်ရန် dynamic fallback လုပ်ထားသည်။
$renter_id = $_SESSION['user_id'] ?? 16; 

// ၃။ Renter ငှားရမ်းထားသော သီးသန့် အဆောက်အဦး/အခန်းများကို ရှာဖွေသည့် SQL Query
$query = "
    SELECT 
        c.id AS contract_id,
        c.start_date,
        c.end_date,
        c.total_deposit_amount,
        -- Apartment အချက်အလက်များ
        a.id AS apartment_id,
        a.floor_level,
        a.apartment_price,
        -- Hostel Room အချက်အလက်များ
        h.id AS hostel_room_id,
        h.room_num,
        h.room_type,
        h.monthly_price,
        -- အိမ်/အဆောက်အဦး အချက်အလက်များ
        rh.title AS house_title,
        rh.city,
        rh.township,
        rh.full_address,
        rh.rentable_type
    FROM contracts c
    LEFT JOIN apartments a ON c.apartment_id = a.id
    LEFT JOIN hostel_rooms h ON c.hostel_room_id = h.id
    LEFT JOIN rental_houses rh ON (a.rental_house_id = rh.id OR h.rental_house_id = rh.id)
    WHERE c.user_id = :renter_id
    ORDER BY c.id DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute([':renter_id' => $renter_id]);
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rented Properties</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-4 md:p-8">

    <div class="max-w-6xl mx-auto">
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between border-b pb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">ကျွန်ုပ်ငှားရမ်းထားသော နေရာများ</h1>
                <p class="text-sm text-gray-500 mt-1">မိမိကိုယ်ပိုင် ငှားရမ်းထားသော Apartment နှင့် Hostel စာရင်းများကို ဤနေရာတွင် သီးသန့်ကြည့်ရှုနိုင်ပါသည်။</p>
            </div>
            <div class="mt-4 md:mt-0 bg-blue-50 border border-blue-200 px-4 py-2 rounded-lg text-sm text-blue-700 font-medium">
                Renter ID: #<?= htmlspecialchars($renter_id) ?>
            </div>
        </div>

        <?php if (!empty($rentals)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($rentals as $row): ?>
                    <?php 
                        // အမျိုးအစားခွဲခြားခြင်း (Apartment သို့မဟုတ် Hostel Room)
                        $is_apartment = !empty($row['apartment_id']);
                        $type_badge = $is_apartment ? 'Apartment' : 'Hostel Room';
                        $price = $is_apartment ? $row['apartment_price'] : $row['monthly_price'];
                        $unit_detail = $is_apartment ? "Floor: " . $row['floor_level'] : "Room No: " . $row['room_num'] . " (" . $row['room_type'] . ")";
                    ?>
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition duration-200 flex flex-col">
                        <div class="p-5 border-b border-gray-50 bg-gradient-to-r from-gray-50 to-white">
                            <div class="flex items-start justify-between">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold 
                                    <?= $is_apartment ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-amber-50 text-amber-700 border border-amber-100' ?>">
                                    <?= $type_badge ?>
                                </span>
                                <div class="text-right">
                                    <span class="text-xs text-gray-400 block">လစဉ်အိမ်လခ</span>
                                    <span class="text-lg font-bold text-blue-600"><?= number_format($price, 2) ?> MMK</span>
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mt-3"><?= htmlspecialchars($row['house_title']) ?></h3>
                        </div>

                        <div class="p-5 flex-grow space-y-4">
                            <div class="flex items-start gap-2.5 text-sm">
                                <span class="text-gray-400 mt-0.5">📍</span>
                                <div>
                                    <p class="font-medium text-gray-700"><?= htmlspecialchars($row['township']) . ", " . htmlspecialchars($row['city']) ?></p>
                                    <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($row['full_address']) ?></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2.5 text-sm">
                                <span class="text-gray-400">🔑</span>
                                <p class="text-gray-600 font-medium"><?= htmlspecialchars($unit_detail) ?></p>
                            </div>

                            <hr class="border-gray-100">

                            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-xl text-xs">
                                <div>
                                    <span class="text-gray-400 block mb-1">စာချုပ်သက်တမ်း</span>
                                    <span class="font-semibold text-gray-700 block">
                                        <?= date('d M Y', strtotime($row['start_date'])) ?>
                                    </span>
                                    <span class="text-gray-400 text-[10px]">မှ</span>
                                    <span class="font-semibold text-gray-700 block">
                                        <?= date('d M Y', strtotime($row['end_date'])) ?>
                                    </span>
                                </div>
                                <div class="border-l border-gray-200 pl-4">
                                    <span class="text-gray-400 block mb-1">စုစုပေါင်း စပေါ်ငွေ</span>
                                    <span class="font-bold text-gray-800 text-sm block mt-2">
                                        <?= number_format($row['total_deposit_amount'], 2) ?> MMK
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 border-t border-gray-100 text-right">
                            <a href="installment_list.php?contract_id=<?= $row['contract_id'] ?>" 
                               class="inline-flex items-center justify-center bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                                💳 ငွေပေးချေမှုမှတ်တမ်း ကြည့်ရန်
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white border rounded-2xl p-12 text-center max-w-md mx-auto mt-12 shadow-sm">
                <div class="text-4xl mb-4">🏢</div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">ငှားရမ်းထားသော နေရာမရှိသေးပါ</h3>
                <p class="text-sm text-gray-400 mb-6">သင်သည် မည်သည့် အခန်း သို့မဟုတ် အပါတ်မန့်ကိုမျှ ငှားရမ်းထားခြင်း မရှိသေးပါ။</p>
                <a href="#" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition inline-block">
                    အခန်းများ ရှာဖွေရန်
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>