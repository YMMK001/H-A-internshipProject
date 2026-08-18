<!DOCTYPE html>
<html lang="my">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>အိမ်ငှားခ ပေးချေရန် စာမျက်နှာ</title>
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Pyidaungsu & Padauk (မြန်မာစာလုံးဒီဇိုင်းအတွက်) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Padauk', sans-serif;
    }
  </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6">

  <!-- Payment Card Container (ကတ်တစ်ခုလုံး၏ အကျယ်အဝန်းနှင့် နောက်ခံ) -->
  <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-200/80 transition-all">
    
    <!-- ခေါင်းစဉ်ပိုင်း (Header Section) -->
    <div class="bg-indigo-600 p-6 text-white relative overflow-hidden">
      <!-- အလှဆင်အလင်းဝိုင်း (Background Decorative Blur) -->
      <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-500/50 rounded-full blur-2xl"></div>
      
      <div class="relative z-10">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold tracking-wider uppercase text-indigo-200">လစဉ် အိမ်ငှားခ ပေးချေရန်</p>
            <h1 class="text-2xl font-bold mt-1">အခန်း ၄-ဘီ (Sunset Heights)</h1>
          </div>
          <!-- ရက်စွဲသတိပေးချက် -->
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-400 text-amber-950 shadow-sm">
            ၃ ရက်သာ လိုပါတော့သည်
          </span>
        </div>

        <div class="mt-6 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold tracking-tight">၁,၈၅၀,၀၀၀</span>
          <span class="text-indigo-200 text-sm font-medium">ကျပ် / လ</span>
        </div>
      </div>
    </div>

    <!-- အသေးစိတ် အချက်အလက်များ (Main Content Body) -->
    <div class="p-6 space-y-6">
      
      <!-- ကျသင့်ငွေ ခွဲခြားပြသမှု (Fee Breakdown) -->
      <div class="space-y-2 text-sm border-b border-slate-100 pb-4">
        <div class="flex justify-between text-slate-500">
          <span>မူလ အိမ်ငှားခ</span>
          <span class="font-medium text-slate-800">၁,၈၀၀,၀၀၀ ကျပ်</span>
        </div>
        <div class="flex justify-between text-slate-500">
          <span>အမှိုက်နှင့် ရေခ</span>
          <span class="font-medium text-slate-800">၅၀,၀၀၀ ကျပ်</span>
        </div>
        <div class="flex justify-between text-slate-900 font-bold pt-2 border-t border-slate-100 text-base">
          <span>စုစုပေါင်း ပေးချေရမည့်ငွေ</span>
          <span class="text-indigo-600">၁,၈၅၀,၀၀၀ ကျပ်</span>
        </div>
      </div>

      <!-- QR Code ဖြင့် ငွေပေးချေရန်နေရာ (QR Code Scanner Box) -->
      <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 flex flex-col items-center text-center">
        <p class="text-base font-bold text-slate-800">Mobile Banking ဖြင့် စကန်ဖတ်၍ ပေးချေပါ</p>
        <p class="text-xs text-slate-500 mt-0.5 mb-4">KPay, CB Pay, AYA Pay သို့မဟုတ် Bank App များဖြင့် ပေးချေနိုင်ပါသည်။</p>
        
        <!-- Scalable SVG QR Code Container -->
        <div class="bg-white p-3.5 rounded-xl shadow-sm border border-slate-200 relative group transition-transform duration-200 hover:scale-[1.02]">
          <svg class="w-44 h-44" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Background -->
            <rect width="100" height="100" fill="white"/>
            
            <!-- Top-Left Alignment Corner -->
            <rect x="5" y="5" width="25" height="25" fill="#1E293B"/>
            <rect x="9" y="9" width="17" height="17" fill="white"/>
            <rect x="13" y="13" width="9" height="9" fill="#1E293B"/>
            
            <!-- Top-Right Alignment Corner -->
            <rect x="70" y="5" width="25" height="25" fill="#1E293B"/>
            <rect x="74" y="9" width="17" height="17" fill="white"/>
            <rect x="78" y="13" width="9" height="9" fill="#1E293B"/>
            
            <!-- Bottom-Left Alignment Corner -->
            <rect x="5" y="70" width="25" height="25" fill="#1E293B"/>
            <rect x="9" y="74" width="17" height="17" fill="white"/>
            <rect x="13" y="78" width="9" height="9" fill="#1E293B"/>

            <!-- QR Data Matrix Pattern -->
            <path d="M35 5h5v10h-5zM45 5h10v5h-10zM60 5h5v5h-5zM35 20h10v5h-10zM50 15h5v10h-5z" fill="#1E293B"/>
            <path d="M5 35h10v5H5zM20 35h5v10h-5zM30 35h15v5H30zM50 35h5v5h-5zM60 35h10v5H60zM75 35h20v5H75z" fill="#1E293B"/>
            <path d="M5 45h5v10H5zM15 50h10v5H15zM35 45h5v5h-5zM45 45h10v10H45zM60 45h5v5h-5zM70 45h15v5H70zM90 45h5v10h-5z" fill="#1E293B"/>
            <path d="M10 60h15v5H10zM30 60h5v10h-5zM40 60h10v5H40zM55 60h10v5H55zM70 60h25v5H70z" fill="#1E293B"/>
            <path d="M35 75h5v15h-5zM45 70h5v10h-5zM55 70h10v5H55zM70 70h10v5H70zM85 70h10v10H85z" fill="#1E293B"/>
            <path d="M45 85h15v5H45zM65 80h10v10H65zM80 85h15v5H80zM50 90h10v5H50zM70 95h25v5H70z" fill="#1E293B"/>
          </svg>
          
          <!-- အလယ်မှ Icon ပုံရုပ် -->
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="bg-indigo-600 text-white p-2 rounded-lg shadow-md ring-4 ring-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
            </div>
          </div>
        </div>

        <p class="text-xs text-slate-400 mt-3 font-mono">ပြေစာအမှတ်: #RENT-2026-08-4B</p>
      </div>

      <!-- လုပ်ဆောင်ချက် ခလုတ်များ (Action Buttons) -->
      <div class="space-y-2.5">
        <button 
          onclick="alert('ဘဏ်ငွေပေးချေမှုစနစ်သို့ ချိတ်ဆက်နေပါသည်...')" 
          class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-3.5 px-4 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 text-base">
          <span>တိုက်ရိုက် ငွေပေးချေမည် (၁,၈၅၀,၀၀၀ ကျပ်)</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </button>
        
        <button 
          onclick="alert('ပြေစာ PDF ကို ဒေါင်းလုဒ်ရယူနေပါသည်...')" 
          class="w-full bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-medium py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2 text-sm">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <span>ပြေစာ PDF ရယူရန်</span>
        </button>
      </div>

    </div>

    <!-- လုံခြုံရေးဆိုင်ရာ အောက်ခြေစာကြောင်း (Security Footer) -->
    <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 text-center">
      <p class="text-xs text-slate-400 flex items-center justify-center gap-1">
        <svg class="w-3.5 h-3.5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
        </svg>
        <span>၂၅၆-ဘစ် စနစ်ဖြင့် လုံခြုံစွာ ကာကွယ်ထားပါသည်</span>
      </p>
    </div>

  </div>

</body>
</html>




<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DATABASE CONFIGURATION & CONNECTION (PDO)
$host     = 'localhost';
$db_name  = 'intern_test';
$username = 'root';      
$password = '';               

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$matching_results = null;
$search_criteria = null;

// Form Processing & Price Comparison Query Logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_preference'])) {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security Error: Invalid CSRF Token.");
    }

    $user_id = (int)$_SESSION['user_id'];
    
    $allowed_types = ['apartment', 'hostel', 'both'];
    $rentable_type = isset($_POST['rentable_type']) && in_array($_POST['rentable_type'], $allowed_types) ? $_POST['rentable_type'] : 'both';
    
    $city = trim($_POST['city']);
    $township = !empty($_POST['township']) ? trim($_POST['township']) : NULL;
    $min_price = !empty($_POST['min_price']) ? floatval($_POST['min_price']) : 0.00;
    $max_price = floatval($_POST['max_price']);
    $room_type = !empty($_POST['room_type']) ? trim($_POST['room_type']) : NULL;
    $max_occupy = (!empty($_POST['max_occupy']) && is_numeric($_POST['max_occupy'])) ? (int)$_POST['max_occupy'] : NULL;
    
    $allowed_genders = ['any', 'male_only', 'female_only'];
    $gender_preference = isset($_POST['gender_preference']) && in_array($_POST['gender_preference'], $allowed_genders) ? $_POST['gender_preference'] : 'any';

    $preferred_move_in_date = !empty($_POST['preferred_move_in_date']) ? $_POST['preferred_move_in_date'] : NULL;
    $note = !empty($_POST['note']) ? trim($_POST['note']) : NULL;

    $desired_amenities = NULL;
    if (isset($_POST['amenities']) && is_array($_POST['amenities'])) {
        $clean_amenities = array_map('trim', $_POST['amenities']);
        $desired_amenities = json_encode($clean_amenities, JSON_UNESCAPED_UNICODE);
    }

    try {
        // 1. Save Preference Request Into Database
        $sql = "INSERT INTO `preference_requests` 
                (`user_id`, `rentable_type`, `city`, `township`, `min_price`, `max_price`, `room_type`, `max_occupy`, `preferred_move_in_date`, `desired_amenities`, `note`, `status`) 
                VALUES (:user_id, :rentable_type, :city, :township, :min_price, :max_price, :room_type, :max_occupy, :preferred_move_in_date, :desired_amenities, :note, 'pending')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':rentable_type' => $rentable_type,
            ':city' => $city,
            ':township' => $township,
            ':min_price' => $min_price,
            ':max_price' => $max_price,
            ':room_type' => $room_type,
            ':max_occupy' => $max_occupy,
            ':preferred_move_in_date' => $preferred_move_in_date,
            ':desired_amenities' => $desired_amenities,
            ':note' => $note
        ]);

        // 2. Fetch Matching Apartments and Hostels using UNION Query
        $params = [
            ':min_price' => $min_price,
            ':max_price' => $max_price,
            ':city' => '%' . $city . '%'
        ];

        $township_clause = "";
        if ($township) {
            $township_clause = " AND LOWER(rh.township) LIKE LOWER(:township)";
            $params[':township'] = '%' . $township . '%';
        }

        $apartment_query = "
            SELECT 
                rh.id AS house_id,
                rh.title,
                rh.city,
                rh.township,
                'apartment' AS property_type,
                a.apartment_price AS price,
                CONCAT('Max Occupy: ', a.max_occupy, ' Person(s) | Floor: ', COALESCE(a.floor_level, 'N/A')) AS details,
                'any' AS gender_type,
                rhi.image_url
            FROM rental_houses rh
            JOIN apartments a ON rh.id = a.rental_house_id
            LEFT JOIN rental_house_images rhi ON rh.id = rhi.rental_house_id AND rhi.is_cover = 1
            WHERE rh.is_active = 1 
              AND a.is_available = 1 
              AND a.apartment_price BETWEEN :min_price AND :max_price
              AND LOWER(rh.city) LIKE LOWER(:city)
              $township_clause
        ";

        $hostel_query = "
            SELECT 
                rh.id AS house_id,
                rh.title,
                rh.city,
                rh.township,
                'hostel' AS property_type,
                hr.monthly_price AS price,
                CONCAT('Room: ', hr.room_num, ' | Type: ', COALESCE(hr.room_type, 'Standard')) AS details,
                hr.gender_type,
                rhi.image_url
            FROM rental_houses rh
            JOIN hostel_rooms hr ON rh.id = hr.rental_house_id
            LEFT JOIN rental_house_images rhi ON rh.id = rhi.rental_house_id AND rhi.is_cover = 1
            WHERE rh.is_active = 1 
              AND hr.is_available = 1 
              AND hr.monthly_price BETWEEN :min_price AND :max_price
              AND LOWER(rh.city) LIKE LOWER(:city)
              $township_clause
        ";

        if ($rentable_type === 'apartment') {
            $final_query = $apartment_query;
        } elseif ($rentable_type === 'hostel') {
            $final_query = $hostel_query;
        } else {
            $final_query = "($apartment_query) UNION ALL ($hostel_query)";
        }

        // ဈေးနှုန်း အနည်းဆုံးမှ အများဆုံးသို့ စီစဉ်ခြင်း
        $final_query .= " ORDER BY price ASC";

        $search_stmt = $pdo->prepare($final_query);
        $search_stmt->execute($params);
        $matching_results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);

        $search_criteria = [
            'type' => $rentable_type,
            'city' => $city,
            'max_price' => $max_price,
            'min_price' => $min_price
        ];

    } catch (PDOException $e) {
        $message = "<div class='mb-4 p-4 text-sm text-red-800 rounded-xl bg-red-100 font-medium'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property & Hostel Price Comparison Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F8F5EE] min-h-screen p-6">

    <div class="max-w-6xl mx-auto space-y-8">
        <div class="w-full">
            <?php echo $message; ?>
        </div>

        <!-- Header Controls -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-[#E8E2D5] shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-[#2D2319]">အခန်း ရှာဖွေရေးနှင့် ဈေးနှုန်း နှိုင်းယှဉ်စနစ်</h1>
                <p class="text-sm text-[#5C4D3C] mt-1">သင့် ဘတ်ဂျက်အတွင်းရှိ အပါတ်မန့် နှင့် အဆောင်များကို ယှဉ်ပြိုင်ကြည့်ရှုပါ</p>
            </div>
            <button type="button" onclick="openPreferenceModal()" class="px-6 py-3 bg-[#2D2319] text-white font-medium rounded-xl shadow-md hover:bg-[#423425] transition active:scale-95 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#D9A362]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                တောင်းဆိုချက် အသစ်ထည့်မည်
            </button>
        </div>

        <!-- Price Comparison Results Display -->
        <?php if ($matching_results !== null): ?>
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-[#E8E2D5] pb-3">
                    <h2 class="text-lg font-bold text-[#2D2319] flex items-center gap-2">
                        <span>ဈေးနှုန်း နှိုင်းယှဉ်ချက် ရလဒ်များ</span>
                        <span class="text-xs bg-[#2D2319] text-white px-2.5 py-0.5 rounded-full"><?php echo count($matching_results); ?> ခုတွေ့ရှိသည်</span>
                    </h2>
                    <span class="text-xs text-gray-500">အနည်းဆုံး ဈေးနှုန်းမှ စတင်စီစဉ်ထားသည်</span>
                </div>

                <?php if (empty($matching_results)): ?>
                    <div class="bg-white rounded-2xl p-8 text-center border border-[#E8E2D5]">
                        <p class="text-gray-500 font-medium">သင့် သတ်မှတ်ချက်များနှင့် ကိုက်ညီသော အခန်း/အဆောင် မတွေ့ရှိသေးပါ။</p>
                        <p class="text-xs text-gray-400 mt-1">တောင်းဆိုချက်ကို သိမ်းဆည်းထားပြီး ဖြစ်ပါသည်၊ အသစ်ရောက်ရှိလာပါက အကြောင်းကြားပေးပါမည်။</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($matching_results as $index => $item): 
                            $price_diff = $search_criteria['max_price'] - $item['price'];
                        ?>
                            <div class="bg-white rounded-2xl border border-[#E8E2D5] overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <!-- Image Thumbnail -->
                                    <div class="h-44 bg-gray-200 relative overflow-hidden">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Cover Image" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="flex items-center justify-center h-full text-gray-400 text-xs font-medium">No Image Available</div>
                                        <?php endif; ?>

                                        <span class="absolute top-3 left-3 px-2.5 py-1 text-xs font-bold rounded-lg uppercase tracking-wide shadow-sm <?php echo $item['property_type'] === 'hostel' ? 'bg-orange-500 text-white' : 'bg-blue-600 text-white'; ?>">
                                            <?php echo ucfirst($item['property_type']); ?>
                                        </span>

                                        <?php if ($index === 0): ?>
                                            <span class="absolute top-3 right-3 bg-emerald-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm">
                                                အသက်သာဆုံး
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="p-5 space-y-3">
                                        <h3 class="font-bold text-[#2D2319] text-lg leading-snug">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </h3>
                                        
                                        <p class="text-xs text-gray-500 flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <?php echo htmlspecialchars($item['township'] . '၊ ' . $item['city']); ?>
                                        </p>

                                        <p class="text-xs text-gray-600 bg-gray-50 p-2 rounded-lg border border-gray-100">
                                            <?php echo htmlspecialchars($item['details']); ?>
                                        </p>

                                        <!-- Price Comparison Block -->
                                        <div class="p-3 bg-[#FCFAF7] rounded-xl border border-[#E8E2D5] space-y-1">
                                            <div class="text-xs text-gray-500">လစဉ် ငှားရမ်းခ</div>
                                            <div class="text-xl font-bold text-[#2D2319]">
                                                <?php echo number_format($item['price']); ?> <span class="text-xs font-normal text-gray-500">MMK</span>
                                            </div>
                                            <?php if ($price_diff > 0): ?>
                                                <p class="text-[11px] text-emerald-700 font-medium">
                                                    သင့် ဘတ်ဂျက်ထက် <?php echo number_format($price_diff); ?> MMK သက်သာပါသည်
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-5 pt-0">
                                    <button class="w-full py-2.5 bg-[#2D2319] hover:bg-[#423425] text-white text-xs font-bold uppercase rounded-xl transition">
                                        အသေးစိတ် ကြည့်ရှုမည်
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Preference Form -->
    <div id="preferenceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity duration-300">
        <div class="relative w-full max-w-2xl bg-[#FCFAF7] rounded-3xl shadow-2xl overflow-hidden border border-[#E8E2D5] transition-all transform scale-100">
            
            <div class="flex items-center justify-between px-8 py-6 bg-[#2D2319] text-white">
                <div>
                    <span class="inline-block px-3 py-1 text-xs font-semibold tracking-wider text-[#D9A362] bg-[#3E3225] rounded-full uppercase mb-1">
                        Matching & Comparison Engine
                    </span>
                    <h3 class="text-xl font-bold text-[#F8F5EE] tracking-wide">
                        သင့်စိတ်ကြိုက် အခန်းတောင်းဆိုရန်
                    </h3>
                </div>
                <button type="button" onclick="closePreferenceModal()" class="flex items-center justify-center w-9 h-9 text-gray-400 hover:text-white bg-[#3E3225] hover:bg-[#4E3F30] rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="p-8 space-y-5 max-h-[80vh] overflow-y-auto">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                        ငှားရမ်းလိုသည့် အမျိုးအစား (Property Type)
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="rentable_type" value="both" checked class="peer hidden">
                            <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                                အားလုံး (All)
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="rentable_type" value="apartment" class="peer hidden">
                            <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                                Apartment
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="rentable_type" value="hostel" class="peer hidden">
                            <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                                Hostel
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            မြို့နယ်/မြို့ (City) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="city" required placeholder="ဥပမာ - ရန်ကုန်၊ မိတ္ထီလာ" 
                            class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            မြို့နယ်ခွဲ (Township)
                        </label>
                        <input type="text" name="township" placeholder="ဥပမာ - ကမာရွတ်" 
                            class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            အနည်းဆုံး ဘတ်ဂျက် (Min Price)
                        </label>
                        <div class="relative">
                            <input type="number" name="min_price" placeholder="50000" 
                                class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                            <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            အများဆုံး ဘတ်ဂျက် (Max Price) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="max_price" required placeholder="300000" 
                                class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                            <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            အခန်း အမျိုးအစား (Room Type)
                        </label>
                        <input type="text" name="room_type" placeholder="ဥပမာ - Single, Studio, Shared" 
                            class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            နေထိုင်မည့် လူဦးရေ (Max Occupants)
                        </label>
                        <select name="max_occupy" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                            <option value="">မသတ်မှတ်ထားပါ (Any)</option>
                            <option value="1">၁ ဦး (1 Person)</option>
                            <option value="2">၂ ဦး (2 Persons)</option>
                            <option value="3">၃ ဦး (3 Persons)</option>
                            <option value="4">၄ ဦး (4 Persons)</option>
                            <option value="5">၅ ဦး (5 Persons)</option>
                            <option value="6">၆ ဦး သို့မဟုတ် ထိုထက်ပိုသော (6+ Persons)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            စတင်နေထိုင်မည့် ရက်စွဲ (Move-in Date)
                        </label>
                        <input type="date" name="preferred_move_in_date" 
                            class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            ကျား/မ သတ်မှတ်ချက် (Gender Preference)
                        </label>
                        <select name="gender_preference" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                            <option value="any">မည်သူမဆို ရသည် (Any)</option>
                            <option value="male_only">အမျိုးသားသီးသန့် (Male Only)</option>
                            <option value="female_only">အမျိုးသမီးသီးသန့် (Female Only)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                        လိုချင်သော အဆောက်အအုံ စည်းစိမ်များ (Amenities)
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="wifi" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Wi-Fi</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="ac" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Aircon</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="generator" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Generator</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="laundry" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Laundry</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="parking" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Car Parking</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="security" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">24/7 Security</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        ဖြည့်စွက် အချက်အလက် (Note)
                    </label>
                    <textarea name="note" rows="2" placeholder="အခြား လိုအပ်ချက်များရှိပါက ရေးသားပေးပါ..." 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-[#E8E2D5]">
                    <button type="button" onclick="closePreferenceModal()" 
                        class="px-5 py-2.5 text-xs font-bold tracking-wider text-[#5C4D3C] uppercase bg-transparent hover:bg-[#EAE3D2] rounded-xl transition">
                        မလုပ်တော့ပါ
                    </button>
                    <button type="submit" name="submit_preference" 
                        class="px-6 py-2.5 text-xs font-bold tracking-wider text-white uppercase bg-[#2D2319] hover:bg-[#423425] rounded-xl shadow-md transition transform active:scale-95">
                        ရှာဖွေပြီး နှိုင်းယှဉ်မည်
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPreferenceModal() {
            const modal = document.getElementById('preferenceModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closePreferenceModal() {
            const modal = document.getElementById('preferenceModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('preferenceModal');
            if (event.target === modal) {
                closePreferenceModal();
            }
        }
    </script>
</body>
</html>



<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DATABASE CONFIGURATION & CONNECTION (PDO)
$host     = 'localhost';
$db_name  = 'intern_test';
$username = 'root';      
$password = '';               

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$matching_results = null;
$search_criteria = null;

// Form Processing & Price Comparison Query Logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_preference'])) {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security Error: Invalid CSRF Token.");
    }

    $user_id = (int)$_SESSION['user_id'];
    
    $allowed_types = ['apartment', 'hostel', 'both'];
    $rentable_type = isset($_POST['rentable_type']) && in_array($_POST['rentable_type'], $allowed_types) ? $_POST['rentable_type'] : 'both';
    
    // Safety check for inputs
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $township = !empty($_POST['township']) ? trim($_POST['township']) : NULL;
    $min_price = !empty($_POST['min_price']) ? floatval($_POST['min_price']) : 0.00;
    $max_price = !empty($_POST['max_price']) ? floatval($_POST['max_price']) : 0.00;
    $room_type = !empty($_POST['room_type']) ? trim($_POST['room_type']) : NULL;
    $max_occupy = (!empty($_POST['max_occupy']) && is_numeric($_POST['max_occupy'])) ? (int)$_POST['max_occupy'] : NULL;
    
    $allowed_genders = ['any', 'male_only', 'female_only'];
    $gender_preference = isset($_POST['gender_preference']) && in_array($_POST['gender_preference'], $allowed_genders) ? $_POST['gender_preference'] : 'any';

    $preferred_move_in_date = !empty($_POST['preferred_move_in_date']) ? $_POST['preferred_move_in_date'] : NULL;
    $note = !empty($_POST['note']) ? trim($_POST['note']) : NULL;

    $desired_amenities = NULL;
    if (isset($_POST['amenities']) && is_array($_POST['amenities'])) {
        $clean_amenities = array_map('trim', $_POST['amenities']);
        $desired_amenities = json_encode($clean_amenities, JSON_UNESCAPED_UNICODE);
    }

    try {
        // 1. Save Preference Request Into Database
        $sql = "INSERT INTO `preference_requests` 
                (`user_id`, `rentable_type`, `city`, `township`, `min_price`, `max_price`, `room_type`, `max_occupy`, `preferred_move_in_date`, `desired_amenities`, `note`, `status`) 
                VALUES (:user_id, :rentable_type, :city, :township, :min_price, :max_price, :room_type, :max_occupy, :preferred_move_in_date, :desired_amenities, :note, 'pending')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':rentable_type' => $rentable_type,
            ':city' => $city,
            ':township' => $township,
            ':min_price' => $min_price,
            ':max_price' => $max_price,
            ':room_type' => $room_type,
            ':max_occupy' => $max_occupy,
            ':preferred_move_in_date' => $preferred_move_in_date,
            ':desired_amenities' => $desired_amenities,
            ':note' => $note
        ]);

        $message = "<div class='mb-4 p-4 text-sm text-emerald-800 rounded-xl bg-emerald-100 font-medium'>သင့် တောင်းဆိုချက်ကို အောင်မြင်စွာ သိမ်းဆည်းပြီးပါပြီ။</div>";

        // 2. Fetch Matching Apartments and Hostels using Dynamic Binding
        $params = [];

        $apt_township_clause = "";
        $hst_township_clause = "";

        if ($township) {
            $apt_township_clause = " AND LOWER(rh.township) LIKE LOWER(:apt_township)";
            $hst_township_clause = " AND LOWER(rh.township) LIKE LOWER(:hst_township)";
        }

        // Subquery JOINs for cleaner image retrieval
        $apartment_query = "
            SELECT 
                rh.id AS house_id,
                rh.title,
                rh.city,
                rh.township,
                'apartment' AS property_type,
                a.apartment_price AS price,
                CONCAT('Max Occupy: ', a.max_occupy, ' Person(s) | Floor: ', COALESCE(a.floor_level, 'N/A')) AS details,
                'any' AS gender_type,
                COALESCE(rhi_cover.image_url, rhi_any.image_url) AS image_url
            FROM rental_houses rh
            JOIN apartments a ON rh.id = a.rental_house_id
            LEFT JOIN rental_house_images rhi_cover ON rh.id = rhi_cover.rental_house_id AND rhi_cover.is_cover = 1
            LEFT JOIN (
                SELECT rental_house_id, MIN(image_url) AS image_url 
                FROM rental_house_images 
                GROUP BY rental_house_id
            ) rhi_any ON rh.id = rhi_any.rental_house_id
            WHERE rh.is_active = 1 
              AND a.is_available = 1 
              AND a.apartment_price BETWEEN :apt_min_price AND :apt_max_price
              AND LOWER(rh.city) LIKE LOWER(:apt_city)
              $apt_township_clause
            GROUP BY rh.id, a.id
        ";

        $hostel_query = "
            SELECT 
                rh.id AS house_id,
                rh.title,
                rh.city,
                rh.township,
                'hostel' AS property_type,
                hr.monthly_price AS price,
                CONCAT('Room: ', hr.room_num, ' | Type: ', COALESCE(hr.room_type, 'Standard')) AS details,
                hr.gender_type,
                COALESCE(rhi_cover.image_url, rhi_any.image_url) AS image_url
            FROM rental_houses rh
            JOIN hostel_rooms hr ON rh.id = hr.rental_house_id
            LEFT JOIN rental_house_images rhi_cover ON rh.id = rhi_cover.rental_house_id AND rhi_cover.is_cover = 1
            LEFT JOIN (
                SELECT rental_house_id, MIN(image_url) AS image_url 
                FROM rental_house_images 
                GROUP BY rental_house_id
            ) rhi_any ON rh.id = rhi_any.rental_house_id
            WHERE rh.is_active = 1 
              AND hr.is_available = 1 
              AND hr.monthly_price BETWEEN :hst_min_price AND :hst_max_price
              AND LOWER(rh.city) LIKE LOWER(:hst_city)
              $hst_township_clause
            GROUP BY rh.id, hr.id
        ";

        if ($rentable_type === 'apartment') {
            $final_query = $apartment_query;
            $params[':apt_min_price'] = $min_price;
            $params[':apt_max_price'] = $max_price;
            $params[':apt_city']      = '%' . $city . '%';
            if ($township) $params[':apt_township'] = '%' . $township . '%';
        } elseif ($rentable_type === 'hostel') {
            $final_query = $hostel_query;
            $params[':hst_min_price'] = $min_price;
            $params[':hst_max_price'] = $max_price;
            $params[':hst_city']      = '%' . $city . '%';
            if ($township) $params[':hst_township'] = '%' . $township . '%';
        } else {
            $final_query = "($apartment_query) UNION ALL ($hostel_query)";
            $params[':apt_min_price'] = $min_price;
            $params[':apt_max_price'] = $max_price;
            $params[':apt_city']      = '%' . $city . '%';
            if ($township) $params[':apt_township'] = '%' . $township . '%';

            $params[':hst_min_price'] = $min_price;
            $params[':hst_max_price'] = $max_price;
            $params[':hst_city']      = '%' . $city . '%';
            if ($township) $params[':hst_township'] = '%' . $township . '%';
        }

        // Order by Price
        $final_query .= " ORDER BY price ASC";

        $search_stmt = $pdo->prepare($final_query);
        $search_stmt->execute($params);
        $matching_results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);

        $search_criteria = [
            'type' => $rentable_type,
            'city' => $city,
            'max_price' => $max_price,
            'min_price' => $min_price
        ];

    } catch (PDOException $e) {
        $message = "<div class='mb-4 p-4 text-sm text-red-800 rounded-xl bg-red-100 font-medium'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property & Hostel Price Comparison Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F8F5EE] min-h-screen p-6">

    <div class="max-w-6xl mx-auto space-y-8">
        <div class="w-full">
            <?php echo $message; ?>
        </div>

        <!-- Header Controls -->
        <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-[#E8E2D5] shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-[#2D2319]">အဆောင်နှင့် အခန်းများ ရှာဖွေနှိုင်းယှဉ်စနစ်</h1>
                <p class="text-xs text-gray-500">သင့်စိတ်ကြိုက် သတ်မှတ်ချက်များဖြင့် အခန်းများကို ရှာဖွေနှိုင်းယှဉ်နိုင်ပါသည်။</p>
            </div>
            <button type="button" onclick="openPreferenceModal()" class="px-5 py-2.5 text-xs font-bold tracking-wider text-white uppercase bg-[#2D2319] hover:bg-[#423425] rounded-xl shadow-md transition">
                ရှာဖွေမည် / Filter Preferences
            </button>
        </div>

        <!-- Price Comparison Results Display -->
        <?php if ($matching_results !== null): ?>
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-[#E8E2D5] pb-3">
                    <h2 class="text-lg font-bold text-[#2D2319] flex items-center gap-2">
                        <span>ဈေးနှုန်း နှိုင်းယှဉ်ချက် ရလဒ်များ</span>
                        <span class="text-xs bg-[#2D2319] text-white px-2.5 py-0.5 rounded-full"><?php echo count($matching_results); ?> ခုတွေ့ရှိသည်</span>
                    </h2>
                    <span class="text-xs text-gray-500">အနည်းဆုံး ဈေးနှုန်းမှ စတင်စီစဉ်ထားသည်</span>
                </div>

                <?php if (empty($matching_results)): ?>
                    <div class="bg-white rounded-2xl p-8 text-center border border-[#E8E2D5]">
                        <p class="text-gray-500 font-medium">သင့် သတ်မှတ်ချက်များနှင့် ကိုက်ညီသော အခန်း/အဆောင် မတွေ့ရှိသေးပါ။</p>
                        <p class="text-xs text-gray-400 mt-1">တောင်းဆိုချက်ကို သိမ်းဆည်းထားပြီး ဖြစ်ပါသည်၊ အသစ်ရောက်ရှိလာပါက အကြောင်းကြားပေးပါမည်။</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($matching_results as $index => $item): 
                            $price_diff = $search_criteria['max_price'] - $item['price'];

                            // FIXED IMAGE PATH RESOLUTION LOGIC
                            $img_src = '';
                            if (!empty($item['image_url'])) {
                                $raw_img = trim($item['image_url']);
                                
                                if (str_starts_with($raw_img, 'http://') || str_starts_with($raw_img, 'https://')) {
                                    $img_src = $raw_img;
                                } else {
                                    $file_name = basename($raw_img);
                                    
                                    // 1. Check if file exists inside 'renter/uploads/'
                                    if (file_exists(__DIR__ . '/uploads/' . $file_name)) {
                                        $img_src = 'uploads/' . $file_name;
                                    } 
                                    // 2. Check if file exists inside root 'uploads/'
                                    elseif (file_exists(__DIR__ . '/../uploads/' . $file_name)) {
                                        $img_src = '../uploads/' . $file_name;
                                    } 
                                    // Fallback Path
                                    else {
                                        $img_src = 'uploads/' . $file_name;
                                    }
                                }
                            }
                        ?>
                            <div class="bg-white rounded-2xl border border-[#E8E2D5] overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <!-- Image Thumbnail -->
                                    <div class="h-44 bg-gray-200 relative overflow-hidden">
                                        <?php if (!empty($img_src)): ?>
                                            <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Cover Image" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="flex items-center justify-center h-full text-gray-400 text-xs font-medium">No Image Available</div>
                                        <?php endif; ?>

                                        <span class="absolute top-3 left-3 px-2.5 py-1 text-xs font-bold rounded-lg uppercase tracking-wide shadow-sm <?php echo $item['property_type'] === 'hostel' ? 'bg-orange-500 text-white' : 'bg-blue-600 text-white'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($item['property_type'])); ?>
                                        </span>

                                        <?php if ($index === 0): ?>
                                            <span class="absolute top-3 right-3 bg-emerald-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm">
                                                အသက်သာဆုံး
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="p-5 space-y-3">
                                        <h3 class="font-bold text-[#2D2319] text-lg leading-snug">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </h3>
                                        
                                        <p class="text-xs text-gray-500 flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <?php echo htmlspecialchars(($item['township'] ? $item['township'] . '၊ ' : '') . $item['city']); ?>
                                        </p>

                                        <p class="text-xs text-gray-600 bg-gray-50 p-2 rounded-lg border border-gray-100">
                                            <?php echo htmlspecialchars($item['details']); ?>
                                        </p>

                                        <!-- Price Comparison Block -->
                                        <div class="p-3 bg-[#FCFAF7] rounded-xl border border-[#E8E2D5] space-y-1">
                                            <div class="text-xs text-gray-500">လစဉ် ငှားရမ်းခ</div>
                                            <div class="text-xl font-bold text-[#2D2319]">
                                                <?php echo number_format($item['price']); ?> <span class="text-xs font-normal text-gray-500">MMK</span>
                                            </div>
                                            <?php if ($price_diff > 0): ?>
                                                <p class="text-[11px] text-emerald-700 font-medium">
                                                    သင့် ဘတ်ဂျက်ထက် <?php echo number_format($price_diff); ?> MMK သက်သာပါသည်
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-5 pb-5 bg-white flex justify-between items-center gap-2">
                                    <a href="view_details.php?id=<?= (int)$item['house_id']; ?>&type=<?= urlencode($item['property_type']); ?>" class="px-3 py-2 bg-white text-slate-800 border border-gray-300 rounded font-medium text-xs hover:bg-stone-50 transition-all text-center flex-1">
                                        Details
                                    </a>

                                    <a href="rentercontract.php?property_id=<?= (int)$item['house_id']; ?>&type=<?= urlencode($item['property_type']); ?>" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">
                                        Book Lease
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Default Welcome Banner when no search action is performed yet -->
            <div class="bg-white rounded-2xl p-10 text-center border border-[#E8E2D5] shadow-sm">
                <h3 class="text-lg font-bold text-[#2D2319] mb-2">အဆောင်နှင့် အခန်းများ စတင်ရှာဖွေပါ</h3>
                <p class="text-xs text-gray-500 mb-6">အထက်ပါ "ရှာဖွေမည်" Button ကိုနှိပ်၍ သင့်စိတ်ကြိုက် ဘတ်ဂျက်နှင့် မြို့နယ်များကို သတ်မှတ်ရှာဖွေနိုင်ပါသည်။</p>
                <button type="button" onclick="openPreferenceModal()" class="px-6 py-3 text-xs font-bold tracking-wider text-white uppercase bg-[#2D2319] hover:bg-[#423425] rounded-xl shadow-md transition">
                    ရှာဖွေမှု စတင်မည်
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Preference Form -->
    <div id="preferenceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity duration-300">
        <div class="relative w-full max-w-2xl bg-[#FCFAF7] rounded-3xl shadow-2xl overflow-hidden border border-[#E8E2D5] transition-all transform scale-100">
            
            <div class="flex items-center justify-between px-8 py-6 bg-[#2D2319] text-white">
                <div>
                    <span class="inline-block px-3 py-1 text-xs font-semibold tracking-wider text-[#D9A362] bg-[#3E3225] rounded-full uppercase mb-1">
                        Matching & Comparison Engine
                    </span>
                    <h3 class="text-xl font-bold text-[#F8F5EE] tracking-wide">
                        သင့်စိတ်ကြိုက် အခန်းတောင်းဆိုရန်
                    </h3>
                </div>
                <button type="button" onclick="closePreferenceModal()" class="flex items-center justify-center w-9 h-9 text-gray-400 hover:text-white bg-[#3E3225] hover:bg-[#4E3F30] rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="p-8 space-y-5 max-h-[80vh] overflow-y-auto">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                        ငှားရမ်းလိုသည့် အမျိုးအစား (Property Type)
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="rentable_type" value="both" checked class="peer hidden">
                            <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                                အားလုံး (All)
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="rentable_type" value="apartment" class="peer hidden">
                            <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                                Apartment
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="rentable_type" value="hostel" class="peer hidden">
                            <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                                Hostel
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            မြို့နယ်/မြို့ (City) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="city" required placeholder="ဥပမာ - ရန်ကုန်၊ မိတ္ထီလာ" 
                            class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            မြို့နယ်ခွဲ (Township)
                        </label>
                        <input type="text" name="township" placeholder="ဥပမာ - ကမာရွတ်" 
                            class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            အနည်းဆုံး ဘတ်ဂျက် (Min Price)
                        </label>
                        <div class="relative">
                            <input type="number" name="min_price" placeholder="50000" 
                                class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                            <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            အများဆုံး ဘတ်ဂျက် (Max Price) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="max_price" required placeholder="300000" 
                                class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                            <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            အခန်း အမျိုးအစား (Room Type)
                        </label>
                        <input type="text" name="room_type" placeholder="ဥပမာ - Single, Studio, Shared" 
                            class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            နေထိုင်မည့် လူဦးရေ (Max Occupants)
                        </label>
                        <select name="max_occupy" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                            <option value="">မသတ်မှတ်ထားပါ (Any)</option>
                            <option value="1">၁ ဦး (1 Person)</option>
                            <option value="2">၂ ဦး (2 Persons)</option>
                            <option value="3">၃ ဦး (3 Persons)</option>
                            <option value="4">၄ ဦး (4 Persons)</option>
                            <option value="5">၅ ဦး (5 Persons)</option>
                            <option value="6">၆ ဦး သို့မဟုတ် ထိုထက်ပိုသော (6+ Persons)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            စတင်နေထိုင်မည့် ရက်စွဲ (Move-in Date)
                        </label>
                        <input type="date" name="preferred_move_in_date" 
                            class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                            ကျား/မ သတ်မှတ်ချက် (Gender Preference)
                        </label>
                        <select name="gender_preference" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                            <option value="any">မည်သူမဆို ရသည် (Any)</option>
                            <option value="male_only">အမျိုးသားသီးသန့် (Male Only)</option>
                            <option value="female_only">အမျိုးသမီးသီးသန့် (Female Only)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                        လိုချင်သော အဆောက်အအုံ စည်းစိမ်များ (Amenities)
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="wifi" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Wi-Fi</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="ac" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Aircon</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="generator" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Generator</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="laundry" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Laundry</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="parking" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">Car Parking</span>
                        </label>
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                            <input type="checkbox" name="amenities[]" value="security" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                            <span class="text-xs font-medium text-[#4A3E31]">24/7 Security</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        ဖြည့်စွက် အချက်အလက် (Note)
                    </label>
                    <textarea name="note" rows="2" placeholder="အခြား လိုအပ်ချက်များရှိပါက ရေးသားပေးပါ..." 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-[#E8E2D5]">
                    <button type="button" onclick="closePreferenceModal()" 
                        class="px-5 py-2.5 text-xs font-bold tracking-wider text-[#5C4D3C] uppercase bg-transparent hover:bg-[#EAE3D2] rounded-xl transition">
                        မလုပ်တော့ပါ
                    </button>
                    <button type="submit" name="submit_preference" 
                        class="px-6 py-2.5 text-xs font-bold tracking-wider text-white uppercase bg-[#2D2319] hover:bg-[#423425] rounded-xl shadow-md transition transform active:scale-95">
                        ရှာဖွေပြီး နှိုင်းယှဉ်မည်
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPreferenceModal() {
            const modal = document.getElementById('preferenceModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closePreferenceModal() {
            const modal = document.getElementById('preferenceModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('preferenceModal');
            if (event.target === modal) {
                closePreferenceModal();
            }
        }
    </script>
</body>
</html>



 <div id="preferenceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity duration-300">
    <div class="relative w-full max-w-2xl bg-[#FCFAF7] rounded-3xl shadow-2xl overflow-hidden border border-[#E8E2D5] transition-all transform scale-100">
        
        <div class="flex items-center justify-between px-8 py-6 bg-[#2D2319] text-white">
            <div>
                <span class="inline-block px-3 py-1 text-xs font-semibold tracking-wider text-[#D9A362] bg-[#3E3225] rounded-full uppercase mb-1">
                    Matching & Comparison Engine
                </span>
                <h3 class="text-xl font-bold text-[#F8F5EE] tracking-wide">
                    သင့်စိတ်ကြိုက် အခန်းတောင်းဆိုရန်
                </h3>
            </div>
            <button type="button" onclick="closePreferenceModal()" class="flex items-center justify-center w-9 h-9 text-gray-400 hover:text-white bg-[#3E3225] hover:bg-[#4E3F30] rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="process_preference.php" method="POST" class="p-8 space-y-5 max-h-[80vh] overflow-y-auto">
            <!-- Warning မတက်အောင် Null Coalescing Operator (?? '') သုံးပေးထားပါသည် -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

            <div>
                <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                    ငှားရမ်းလိုသည့် အမျိုးအစား (Property Type)
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="rentable_type" value="both" checked class="peer hidden">
                        <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                            အားလုံး (All)
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rentable_type" value="apartment" class="peer hidden">
                        <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                            Apartment
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rentable_type" value="hostel" class="peer hidden">
                        <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                            Hostel
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        မြို့နယ်/မြို့ (City) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="city" required placeholder="ဥပမာ - ရန်ကုန်၊ မိတ္ထီလာ" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        မြို့နယ်ခွဲ (Township)
                    </label>
                    <input type="text" name="township" placeholder="ဥပမာ - ကမာရွတ်" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        အနည်းဆုံး ဘတ်ဂျက် (Min Price)
                    </label>
                    <div class="relative">
                        <input type="number" name="min_price" placeholder="50000" 
                            class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        အများဆုံး ဘတ်ဂျက် (Max Price) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="max_price" required placeholder="300000" 
                            class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        အခန်း အမျိုးအစား (Room Type)
                    </label>
                    <input type="text" name="room_type" placeholder="ဥပမာ - Single, Studio, Shared" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        နေထိုင်မည့် လူဦးရေ (Max Occupants)
                    </label>
                    <select name="max_occupy" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <option value="">မသတ်မှတ်ထားပါ (Any)</option>
                        <option value="1">၁ ဦး (1 Person)</option>
                        <option value="2">၂ ဦး (2 Persons)</option>
                        <option value="3">၃ ဦး (3 Persons)</option>
                        <option value="4">၄ ဦး (4 Persons)</option>
                        <option value="5">၅ ဦး (5 Persons)</option>
                        <option value="6">၆ ဦး သို့မဟုတ် ထိုထက်ပိုသော (6+ Persons)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        စတင်နေထိုင်မည့် ရက်စွဲ (Move-in Date)
                    </label>
                    <input type="date" name="preferred_move_in_date" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        ကျား/မ သတ်မှတ်ချက် (Gender Preference)
                    </label>
                    <select name="gender_preference" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <option value="any">မည်သူမဆို ရသည် (Any)</option>
                        <option value="male_only">အမျိုးသားသီးသန့် (Male Only)</option>
                        <option value="female_only">အမျိုးသမီးသီးသန့် (Female Only)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                    လိုချင်သော အဆောက်အအုံ စည်းစိမ်များ (Amenities)
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="wifi" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Wi-Fi</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="ac" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Aircon</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="generator" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Generator</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="laundry" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Laundry</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="parking" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Car Parking</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="security" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">24/7 Security</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                    ဖြည့်စွက် အချက်အလက် (Note)
                </label>
                <textarea name="note" rows="2" placeholder="အခြား လိုအပ်ချက်များရှိပါက ရေးသားပေးပါ..." 
                    class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-[#E8E2D5]">
                <button type="button" onclick="closePreferenceModal()" 
                    class="px-5 py-2.5 text-xs font-bold tracking-wider text-[#5C4D3C] uppercase bg-transparent hover:bg-[#EAE3D2] rounded-xl transition">
                    မလုပ်တော့ပါ
                </button>
                
                <button type="submit" name="submit_preference" value="1" class="bg-[#2D2319] hover:bg-[#423425] text-[#F8F5EE] text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all active:scale-95 cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Match Preferences
                </button>
            </div>
        </form>
    </div>


        
         <section class="mt-20 border-t border-stone-200 pt-14 max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-3">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-amber-800">Get In Touch</span>
                        <h3 class="text-xl font-serif text-stone-900">Contact Management</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">လူကြီးမင်းတို့၏ အိမ်၊ ခြံ၊ မြေ နှင့် အဆောင်အခန်းများ ငှားရမ်းခြင်းကိစ္စရပ်များအတွက် ယုံကြည်စိတ်ချစွာ ဆက်သွယ်နိုင်ပါသည်။</p>
                    </div>
                    <div class="bg-white border border-stone-200 p-6 rounded space-y-4 shadow-sm md:col-span-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="space-y-1">
                                <span class="text-stone-400 font-bold block">📍 OFFICE ADDRESS</span>
                                <p class="text-stone-700 font-medium">အမှတ် (၁၂၀)၊ ကမ္ဘာအေးဘုရားလမ်း၊ ဗဟန်းမြို့နယ်၊ ရန်ကုန်မြို့။</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 font-bold block">📞 PHONE & HOTLINE</span>
                                <p class="text-stone-700 font-mono font-medium">+95 9 123 456 789<br>+95 1 234 567</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 block font-bold">✉️ EMAIL SUPPORT</span>
                                <p class="text-blue-900 font-medium underline">support@therentalhub.com</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 block font-bold">⏰ WORKING HOURS</span>
                                <p class="text-stone-700 font-medium">Mon - Sat | 9:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-stone-900 text-stone-400 text-xs border-t border-stone-800 mt-auto">
        <div class="max-w-6xl mx-auto px-6 py-10">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-6 border-b border-stone-800 pb-8">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 bg-amber-700 flex items-center justify-center text-stone-100 font-serif font-bold text-base">R</div>
                    <span class="text-lg font-serif font-bold tracking-tight text-white">Rental<span class="text-amber-600 italic font-normal">Hub</span></span>
                </div>
                <div class="flex flex-wrap justify-center gap-6 text-[11px] font-medium tracking-wide">
                    <a href="renterhomepage.php" class="hover:text-white transition-colors">Home</a>
                    <a href="#apartmentCardSection" class="hover:text-white transition-colors">Apartments</a>
                    <a href="#hostelCardSection" class="hover:text-white transition-colors">Hostels</a>
                    <a href="../auth/login.php?redirect=homepage" class="hover:text-white transition-colors">Admin Panel</a>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 text-[11px] text-stone-500 font-serif">
                <p>&copy; <?= date('Y'); ?> The Rental Hub Co., Ltd. All rights reserved.</p>
                <p class="italic">Crafted for Quality Property Environments.</p>
            </div>
        </div>
    </footer>
     <!-- Contact Section -->


      <header class="relative bg-gradient-to-b from-[#fcfbf9] to-[#f4f1ea] rounded-3xl border border-stone-300/80 py-16 sm:py-20 text-center overflow-hidden font-serif shadow-2xl transition-all duration-500">
            <div class="absolute inset-0 z-0 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?q=80&w=1600&auto=format&fit=crop" 
                     alt="Modern Luxury Condo Interior" 
                     class="w-full h-full object-cover opacity-40 transform scale-105 transition-transform duration-1000 ease-out hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-[#f4f1ea] via-[#f4f1ea]/40 to-[#fcfbf9]/80"></div>
            </div>

            <div class="relative z-10 max-w-3xl mx-auto px-6">
                <div class="inline-flex items-center gap-2.5 bg-white/90 border border-emerald-600/30 px-4 py-1.5 rounded-full mb-6 shadow-md backdrop-blur-md">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-600"></span>
                    </span>
                    <span class="uppercase tracking-widest text-[10px] font-sans font-bold text-emerald-950">
                       Established Property Management
                    </span>
                </div>

               <h1 class="text-3xl sm:text-4xl md:text-5xl font-serif font-semibold text-stone-900 max-w-2xl mx-auto leading-snug title-classic tracking-tight">
                    One platform. Perfect harmony for <span class="italic font-normal text-amber-900">Renters</span> &amp; <span class="italic font-normal text-amber-900">Owners</span>.
                </h1>

                <div class="flex items-center justify-center gap-3 my-6">
                    <div class="w-12 h-[1px] bg-amber-800/40"></div>
                    <div class="h-1.5 w-1.5 rotate-45 border border-amber-800 bg-amber-900"></div>
                    <div class="w-12 h-[1px] bg-amber-800/40"></div>
                </div>

                <div class="mt-4 inline-flex flex-wrap items-center justify-center gap-3 bg-white/95 border border-stone-300/80 backdrop-blur-xl px-7 py-3.5 rounded-2xl shadow-xl text-xs font-sans text-stone-800 transform hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center gap-1.5 text-stone-500 font-bold uppercase tracking-wider text-[10px]">
                        <i class="fa-solid fa-city text-amber-900"></i>
                        <span>Quick Search:</span>
                    </div>
                    
                    <div class="h-4 w-[1px] bg-stone-300 hidden sm:block"></div>

                    <button onclick="quickSearch('Yangon')" class="text-stone-900 hover:text-amber-900 font-bold underline underline-offset-4 decoration-amber-800/30 hover:decoration-amber-900 transition-all px-1.5">
                        ရန်ကုန်
                    </button> 
                    <span class="text-stone-300">|</span>
                    
                    
                    
                    
                    <button onclick="quickSearch('AVAILABLE')" class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 font-bold px-3 py-1 rounded-lg transition-all shadow-xs">
                        <i class="fa-solid fa-building text-[10px]"></i>
                        <span>Available</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- SHOWCASE SECTION -->
        <section class="max-w-6xl mx-auto px-6 w-full pt-10 pb-12">
            <div class="bg-[#f5f2eb] rounded-xl shadow-sm overflow-hidden border border-stone-300/60 text-stone-800">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 p-6 lg:p-8 items-center">
                    
                    <div class="lg:col-span-5 space-y-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[9px] uppercase font-bold tracking-wider bg-amber-700/10 text-amber-800 border border-amber-700/20">
                            <i class="fa-solid fa-feather-pointed mr-1 text-amber-700"></i> Elite Core Premium
                        </span>
                        <h2 class="text-2xl lg:text-3xl font-normal leading-tight text-stone-900 title-classic">
                            ယုံကြည်စိတ်ချရသော <br>
                            <span class="text-blue-900 font-semibold italic">အိမ်ရာစီမံခန့်ခွဲမှုဗဟို</span>
                        </h2>
                        <p class="text-stone-600 text-xs leading-relaxed font-light">
                            အဆောင်နှင့် တိုက်ခန်းငှားရမ်းခြင်းလုပ်ငန်းများကို ခေတ်မီစနစ်များဖြင့် ဒစ်ဂျစ်တယ်စနစ်သို့ ပြောင်းလဲလိုက်ပါ။ ပိုင်ရှင်နှင့် အိမ်ငှားကြား စာရွက်စာတမ်းရှုပ်ထွေးမှုများကို ဘေးကင်းလုံခြုံစွာ ဖြေရှင်းပေးပါသည်။
                        </p>
                        
                        <div class="space-y-3 pt-3 border-t border-stone-300/60">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Auto-Sync Digital Contracts</h4>
                                    <p class="text-[10px] text-stone-500">စာချုပ်သက်တမ်းကုန်ဆုံးပါက အခန်းများကို Available အဖြစ် အလိုအလျောက်ပြောင်းလဲပေးခြင်း။</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Polymorphic Core Tracking</h4>
                                    <p class="text-[10px] text-stone-500">တိုက်ခန်းများနှင့် အဆောင်ဒေတာများကို ပေါင်းစည်းထားသော ရလဒ်ထွက်စနစ်ဖြင့် စနစ်တကျပြသပေးခြင်း။</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7 grid grid-cols-12 gap-3 h-[220px] lg:h-[260px]">
                        <div class="col-span-7 relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80" 
                                 alt="Modern Room Architecture" 
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                            <span class="absolute bottom-2.5 left-2.5 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-2 py-0.5 rounded-sm text-[9px] uppercase tracking-wider font-bold text-amber-300">
                                Premium Spaces
                            </span>
                        </div>
                        <div class="col-span-5 grid grid-rows-2 gap-3">
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=400&q=80" 
                                     alt="Cozy Interior View" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Cozy Hostels
                                </span>
                            </div>
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=400&q=80" 
                                     alt="Verified Apartment" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Verified Units
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>



       <?php
// ၁။ Session စနစ်အား ဖိုင်၏ ထိပ်ဆုံးတွင် မဖြစ်မနေ စတင်ဖွင့်လှစ်ခြင်း
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ၃။ DATABASE CONFIGURATION & CONNECTION (PDO)
$host     = 'localhost';
$db_name  = 'intern_test';
$username = 'root';                     
$password = '';                         

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='color:red; font-weight:bold; padding:20px;'>Database Connection Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

// =========================================================================
// BACKGROUND AUTO-SYNC (စာချုပ်သက်တမ်းကုန်ဆုံးပါက အလိုအလျောက် Available ပြန်ပြောင်းပေးမည့် စနစ်)
// =========================================================================
try {
    $today = date('Y-m-d');

    // ၁။ စာချုပ်ကုန်ဆုံးရက် ရောက်နေပြီဖြစ်သော စာချုပ်များကို Status 'expired' ဟု ပြောင်းလဲခြင်း
    $expire_sql = "UPDATE contracts SET status = 'expired' WHERE end_date < :today AND status = 'active'";
    $stmt_exp = $pdo->prepare($expire_sql);
    $stmt_exp->execute([':today' => $today]);

    // ၂။ တက်ကြွဆဲ စာချုပ် (active) ရှိနေသော အခန်းများကို Locked (is_available = 0) ဟု ပြောင်းခြင်း
    $sync_ap_sql = "UPDATE apartments SET is_available = 0 WHERE id IN (SELECT apartment_id FROM contracts WHERE status = 'active' AND apartment_id IS NOT NULL)";
    $pdo->exec($sync_ap_sql);

    $sync_hr_sql = "UPDATE hostel_rooms SET is_available = 0 WHERE id IN (SELECT hostel_room_id FROM contracts WHERE status = 'active' AND hostel_room_id IS NOT NULL)";
    $pdo->exec($sync_hr_sql);

    // ၃။ သက်တမ်းကုန်သွားသော သို့မဟုတ် စာချုပ်မရှိသော အခန်းများကို Available (is_available = 1) ပြန်ဖွင့်ပေးခြင်း
    $reset_ap_sql = "UPDATE apartments SET is_available = 1 WHERE id NOT IN (SELECT apartment_id FROM contracts WHERE status = 'active' AND apartment_id IS NOT NULL)";
    $pdo->exec($reset_ap_sql);

    $reset_hr_sql = "UPDATE hostel_rooms SET is_available = 1 WHERE id NOT IN (SELECT hostel_room_id FROM contracts WHERE status = 'active' AND hostel_room_id IS NOT NULL)";
    $pdo->exec($reset_hr_sql);

} catch (PDOException $e) {
    // Suppressed background error to keep app stable
}

// =========================================================================
// NOTIFICATION DATA FETCHING
// =========================================================================
$unread_notif_count = 0;
$recent_notifications = [];

if (isset($_SESSION['user_id'])) {
    try {
        // Unread Notifications အရေအတွက် ယူခြင်း
        $notif_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $notif_count_stmt->execute([':user_id' => $_SESSION['user_id']]);
        $unread_notif_count = $notif_count_stmt->fetchColumn();

        // နောက်ဆုံးရ အသိပေးချက် ၅ ခု ရယူခြင်း
        $notif_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
        $notif_stmt->execute([':user_id' => $_SESSION['user_id']]);
        $recent_notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Notifications table မရှိသေးပါက error မတက်အောင် ထိန်းပေးထားခြင်း
    }
}

// ၄။ COMBINED POLYMORPHIC UNION QUERY
$query = "
    SELECT
        ap.id AS id,
        rh.title,
        rh.township,
        rh.city,
        ap.apartment_price AS price,
        CONCAT('Floor: ', ap.floor_level, ' | 👥 Max: ', ap.max_occupy, ' ဦး') AS unit_details,
        ap.is_available,
        img.image_url,
        'apartment' AS type
    FROM rental_houses rh
    INNER JOIN apartments ap ON rh.id = ap.rental_house_id
    LEFT JOIN (
        SELECT rental_house_id, image_url
        FROM rental_house_images
        WHERE id IN (
            SELECT MIN(id) FROM rental_house_images GROUP BY rental_house_id
        )
    ) img ON rh.id = img.rental_house_id
    WHERE rh.is_active = 1

    UNION ALL

    SELECT
        hr.id AS id,
        rh.title,
        rh.township,
        rh.city,
        hr.monthly_price AS price,
        CONCAT('Room: ', hr.room_num, ' | ', hr.room_type, ' (', hr.sub_unit, ')') AS unit_details,
        hr.is_available,
        img.image_url,
        'hostel' AS type
    FROM rental_houses rh
    INNER JOIN hostel_rooms hr ON rh.id = hr.rental_house_id
    LEFT JOIN (
        SELECT rental_house_id, image_url
        FROM rental_house_images
        WHERE id IN (
            SELECT MIN(id) FROM rental_house_images GROUP BY rental_house_id
        )
    ) img ON rh.id = img.rental_house_id
    WHERE rh.is_active = 1
";

try {
    $stmt = $pdo->query($query);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='color:red; font-weight:bold; padding:20px;'>Query execution error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

// ၅။ FILTER UNIFIED DATA INTO SEPARATED VISUAL TRACK
$apartments = array_filter($properties, function($item) { return $item['type'] === 'apartment'; });
$hostels    = array_filter($properties, function($item) { return $item['type'] === 'hostel'; });
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Rental Hub - Find Your Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#fcfbf9] font-classic min-h-screen flex flex-col text-gray-800">

    <!-- Top Navigation Menu containing working Search Filters & Home controls -->
    <nav class="bg-white/90 backdrop-blur-md border-b border-stone-200 sticky top-0 z-50 w-full shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4 py-2">
    
                <!-- LOGO (ACTS AS HOME TRIGGER) -->
                <a href="index.php" class="flex items-center gap-2.5 shrink-0 group">
                    <div class="h-9 w-9 bg-slate-900 border border-amber-600/80 flex items-center justify-center text-amber-200 font-serif font-bold text-lg rounded-lg shadow-xs group-hover:bg-slate-950 transition-all">
                        R
                    </div>
                    <span class="text-xl font-serif font-bold tracking-tight text-slate-900">
                        Rental<span class="text-amber-800 italic font-normal">Hub</span>
                    </span>
                </a>

                <!-- INTEGRATED NAV SEARCH FILTERS WITH FLEXIBLE PREFERENCE BUTTON -->
                <div class="hidden md:flex items-center gap-2 bg-stone-100/80 border border-stone-200 p-1.5 rounded-xl w-full lg:w-auto flex-1 max-w-3xl shadow-inner">
                    <div class="relative flex-1 min-w-[150px]">
                        <input type="text" id="citySearchInput" onkeyup="filterByCity()" placeholder="Search title or keyword..." class="w-full bg-white border border-stone-200 text-xs text-stone-800 px-3 py-2 rounded-lg outline-none focus:border-amber-800 focus:ring-2 focus:ring-amber-800/10 transition-all">
                    </div>
                    
                    <select id="typeSelect" onchange="filterProperties()" class="bg-white border border-stone-200 text-xs text-stone-700 px-3 py-2 rounded-lg outline-none cursor-pointer focus:border-amber-800 transition-all shrink-0">
                        <option value="">All Locations & Types</option>
                        <optgroup label="Townships">
                            <option value="Ahlone">Ahlone</option>
                            <option value="Bahan">Bahan</option>
                            <option value="Botahtaung">Botahtaung</option>
                            <option value="Dagon">Dagon</option>
                            <option value="Kamayut">Kamayut</option>
                            <option value="Kyauktada">Kyauktada</option>
                            <option value="Lanmadaw">Lanmadaw</option>
                            <option value="Latha">Latha</option>
                            <option value="Pabedan">Pabedan</option>
                            <option value="Sanchaung">Sanchaung</option>
                            <option value="Dawbon">Dawbon</option>
                            <option value="Mingala Taungyunt">Mingala Taungyunt</option>
                            <option value="Pazundaung">Pazundaung</option>
                            <option value="Tamwe">Tamwe</option>
                            <option value="Thaketa">Thaketa</option>
                            <option value="Thingangyun">Thingangyun</option>
                            <option value="Yankin">Yankin</option>
                            <option value="Hlaing">Hlaing</option>
                            <option value="Insein">Insein</option>
                            <option value="Mayangone">Mayangone</option>
                            <option value="Mingaladon">Mingaladon</option>
                            <option value="North Okkalapa">North Okkalapa</option>
                            <option value="Shwepyita">Shwepyita</option>
                            <option value="South Okkalapa">South Okkalapa</option>
                            <option value="Dagon Seikkan">Dagon Seikkan</option>
                            <option value="East Dagon">East Dagon</option>
                            <option value="North Dagon">North Dagon</option>
                            <option value="South Dagon">South Dagon</option>
                            <option value="Hlaingthaya East">Hlaingthaya East</option>
                            <option value="Hlaingthaya West">Hlaingthaya West</option>
                            <option value="Dala">Dala</option>
                            <option value="Seikkyi Kanaungto">Seikkyi Kanaungto</option>
                            <option value="Hlegu">Hlegu</option>
                            <option value="Hmawbi">Hmawbi</option>
                            <option value="Htantabin">Htantabin</option>
                            <option value="Taikkyi">Taikkyi</option>
                            <option value="Kawhmu">Kawhmu</option>
                            <option value="Kayan">Kayan</option>
                            <option value="Kungyangon">Kungyangon</option>
                            <option value="Kyauktan">Kyauktan</option>
                            <option value="Thanlyin">Thanlyin</option>
                            <option value="Thongwa">Thongwa</option>
                        </optgroup>
                        <optgroup label="Property Types">
                            <option value="apartment">Apartment</option>
                            <option value="hostel">Hostel</option>
                        </optgroup>
                    </select>

                    <a href="../renter/renterhomepage.php" class="bg-white border border-stone-200 text-xs text-stone-700 hover:text-amber-900 hover:border-amber-800/40 px-3 py-2 rounded-lg font-medium transition-all shrink-0">
                        Home
                    </a>

                    <button type="button" onclick="openPreferenceModal()" class="px-6 py-3 text-xs font-bold tracking-wider text-white uppercase bg-[#2D2319] hover:bg-[#423425] rounded-xl shadow-md transition">
                        ရှာဖွေမှု စတင်မည်
                    </button>
                </div>

                <!-- USER AUTH & PROFILE DROPDOWN MENU -->
                <div class="flex items-center gap-3 shrink-0">
                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
                        
                        <!-- NOTIFICATION DROPDOWN MENU -->
                        <div class="relative">
                            <button type="button" onclick="toggleNotificationDropdown()" class="relative p-2 text-stone-600 hover:text-amber-900 focus:outline-none transition-colors group cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <?php if ($unread_notif_count > 0): ?>
                                    <span class="absolute top-1 right-1 flex h-2 w-2">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                    </span>
                                <?php endif; ?>
                            </button>

                            <!-- Notifications Box -->
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-72 md:w-80 bg-white border border-stone-200 rounded-xl shadow-xl py-2 z-50">
                                <div class="px-4 py-2 border-b border-stone-100 flex justify-between items-center bg-stone-50/50 rounded-t-xl">
                                    <span class="text-xs font-bold text-slate-800">အသိပေးချက်များ</span>
                                    <?php if ($unread_notif_count > 0): ?>
                                        <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold"><?= $unread_notif_count ?> မဖတ်ရသေး</span>
                                    <?php endif; ?>
                                </div>

                                <div class="max-h-64 overflow-y-auto divide-y divide-stone-100">
                                    <?php if (!empty($recent_notifications)): ?>
                                        <?php foreach ($recent_notifications as $notif): ?>
                                            <div class="p-3 text-xs <?= $notif['is_read'] ? 'bg-white' : 'bg-amber-50/40' ?> hover:bg-stone-50 transition-colors">
                                                <p class="font-bold text-slate-900 mb-0.5"><?= htmlspecialchars($notif['title']) ?></p>
                                                <p class="text-stone-600 line-clamp-2"><?= htmlspecialchars($notif['message']) ?></p>
                                                <span class="text-[9px] text-stone-400 mt-1 block"><?= date('d M, h:i A', strtotime($notif['created_at'])) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="p-4 text-center text-xs text-stone-400">
                                            အသိပေးချက် မရှိသေးပါ။
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <a href="notifications.php" class="block text-center text-xs font-semibold text-amber-800 hover:text-amber-950 py-2 border-t border-stone-100 bg-stone-50/30 rounded-b-xl">
                                    အသိပေးချက် အားလုံးကြည့်မည် →
                                </a>
                            </div>
                        </div>

                        <!-- DROPDOWN PROFILE MENU -->
                        <div class="relative">
                            <button type="button" onclick="toggleProfileDropdown()" class="flex items-center gap-2.5 focus:outline-none cursor-pointer group">
                                <div class="flex flex-col items-end">
                                    <span class="text-xs font-bold text-slate-800 group-hover:text-amber-900 transition-colors">
                                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                                    </span>
                                </div>
                                <div class="h-8 w-8 rounded-full bg-amber-100 border border-amber-600/40 flex items-center justify-center text-amber-950 font-bold text-xs uppercase shadow-xs group-hover:border-amber-800 transition-all">
                                    <?php echo mb_substr(htmlspecialchars($_SESSION['username']), 0, 1, "UTF-8"); ?>
                                </div>
                                <svg class="w-3.5 h-3.5 text-stone-500 group-hover:text-amber-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu Content -->
                            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-52 bg-white border border-stone-200 rounded-xl shadow-xl py-1.5 z-50">
                                <div class="px-4 py-2 border-b border-stone-100 bg-stone-50/50 rounded-t-xl">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Signed in as</p>
                                    <p class="text-xs font-bold text-slate-900 truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                </div>
                                
                                <a href="renter_profile.php" class="flex items-center gap-2 px-4 py-2 text-xs text-stone-700 hover:bg-amber-50/60 hover:text-amber-950 transition-colors">
                                    <span>👤</span> My Profile
                                </a>
                                
                                <div class="border-t border-stone-100 my-1"></div>
                                
                                <a href="../auth/logout.php" class="flex items-center gap-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-medium transition-colors rounded-b-xl">
                                    <span>🚪</span> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- GUEST AUTH LINKS -->
                        <a href="../auth/register.php" class="text-xs font-semibold text-stone-600 hover:text-slate-900 px-2 py-1.5 transition-all">
                            Register
                        </a>
                        <a href="../auth/login.php?redirect=homepage" class="px-3.5 py-2 text-xs font-medium text-amber-100 bg-slate-900 hover:bg-slate-950 border border-amber-600/50 rounded-lg shadow-2xs transition-all">
                            Sign In
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </nav>
 <div id="preferenceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity duration-300">
    <div class="relative w-full max-w-2xl bg-[#FCFAF7] rounded-3xl shadow-2xl overflow-hidden border border-[#E8E2D5] transition-all transform scale-100">
        
        <div class="flex items-center justify-between px-8 py-6 bg-[#2D2319] text-white">
            <div>
                <span class="inline-block px-3 py-1 text-xs font-semibold tracking-wider text-[#D9A362] bg-[#3E3225] rounded-full uppercase mb-1">
                    Matching & Comparison Engine
                </span>
                <h3 class="text-xl font-bold text-[#F8F5EE] tracking-wide">
                    သင့်စိတ်ကြိုက် အခန်းတောင်းဆိုရန်
                </h3>
            </div>
            <button type="button" onclick="closePreferenceModal()" class="flex items-center justify-center w-9 h-9 text-gray-400 hover:text-white bg-[#3E3225] hover:bg-[#4E3F30] rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="process_preference.php" method="POST" class="p-8 space-y-5 max-h-[80vh] overflow-y-auto">
            <!-- Warning မတက်အောင် Null Coalescing Operator (?? '') သုံးပေးထားပါသည် -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

            <div>
                <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                    ငှားရမ်းလိုသည့် အမျိုးအစား (Property Type)
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="rentable_type" value="both" checked class="peer hidden">
                        <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                            အားလုံး (All)
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rentable_type" value="apartment" class="peer hidden">
                        <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                            Apartment
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rentable_type" value="hostel" class="peer hidden">
                        <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                            Hostel
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        မြို့နယ်/မြို့ (City) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="city" required placeholder="ဥပမာ - ရန်ကုန်၊ မိတ္ထီလာ" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        မြို့နယ်ခွဲ (Township)
                    </label>
                    <input type="text" name="township" placeholder="ဥပမာ - ကမာရွတ်" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        အနည်းဆုံး ဘတ်ဂျက် (Min Price)
                    </label>
                    <div class="relative">
                        <input type="number" name="min_price" placeholder="50000" 
                            class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        အများဆုံး ဘတ်ဂျက် (Max Price) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="max_price" required placeholder="300000" 
                            class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        အခန်း အမျိုးအစား (Room Type)
                    </label>
                    <input type="text" name="room_type" placeholder="ဥပမာ - Single, Studio, Shared" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        နေထိုင်မည့် လူဦးရေ (Max Occupants)
                    </label>
                    <select name="max_occupy" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <option value="">မသတ်မှတ်ထားပါ (Any)</option>
                        <option value="1">၁ ဦး (1 Person)</option>
                        <option value="2">၂ ဦး (2 Persons)</option>
                        <option value="3">၃ ဦး (3 Persons)</option>
                        <option value="4">၄ ဦး (4 Persons)</option>
                        <option value="5">၅ ဦး (5 Persons)</option>
                        <option value="6">၆ ဦး သို့မဟုတ် ထိုထက်ပိုသော (6+ Persons)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        စတင်နေထိုင်မည့် ရက်စွဲ (Move-in Date)
                    </label>
                    <input type="date" name="preferred_move_in_date" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        ကျား/မ သတ်မှတ်ချက် (Gender Preference)
                    </label>
                    <select name="gender_preference" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <option value="any">မည်သူမဆို ရသည် (Any)</option>
                        <option value="male_only">အမျိုးသားသီးသန့် (Male Only)</option>
                        <option value="female_only">အမျိုးသမီးသီးသန့် (Female Only)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                    လိုချင်သော အဆောက်အအုံ စည်းစိမ်များ (Amenities)
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="wifi" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Wi-Fi</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="ac" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Aircon</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="generator" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Generator</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="laundry" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Laundry</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="parking" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Car Parking</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="security" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">24/7 Security</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                    ဖြည့်စွက် အချက်အလက် (Note)
                </label>
                <textarea name="note" rows="2" placeholder="အခြား လိုအပ်ချက်များရှိပါက ရေးသားပေးပါ..." 
                    class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-[#E8E2D5]">
                <button type="button" onclick="closePreferenceModal()" 
                    class="px-5 py-2.5 text-xs font-bold tracking-wider text-[#5C4D3C] uppercase bg-transparent hover:bg-[#EAE3D2] rounded-xl transition">
                    မလုပ်တော့ပါ
                </button>
                
                <button type="submit" name="submit_preference" value="1" class="bg-[#2D2319] hover:bg-[#423425] text-[#F8F5EE] text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all active:scale-95 cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Match Preferences
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    function openPreferenceModal() {
        const modal = document.getElementById('preferenceModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closePreferenceModal() {
        const modal = document.getElementById('preferenceModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    window.onclick = function(event) {
        const modal = document.getElementById('preferenceModal');
        if (event.target === modal) {
            closePreferenceModal();
        }
    }
    
    // Toggle Profile Dropdown
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        const notifDropdown = document.getElementById('notificationDropdown');
        if (notifDropdown) notifDropdown.classList.add('hidden'); 
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    // Toggle Notification Dropdown
    function toggleNotificationDropdown() {
        const notifDropdown = document.getElementById('notificationDropdown');
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) profileDropdown.classList.add('hidden'); 
        if (notifDropdown) {
            notifDropdown.classList.toggle('hidden');
        }
    }

    // Fixed Dropdown Close Handler
    window.addEventListener('click', function(e) {
        const profileDropdown = document.getElementById('profileDropdown');
        const notifDropdown = document.getElementById('notificationDropdown');

        if (!e.target.closest('#profileDropdown') && !e.target.closest('button[onclick*="toggleProfileDropdown"]')) {
            if (profileDropdown) profileDropdown.classList.add('hidden');
        }
        if (!e.target.closest('#notificationDropdown') && !e.target.closest('button[onclick*="toggleNotificationDropdown"]')) {
            if (notifDropdown) notifDropdown.classList.add('hidden');
        }
    });

    function filterByCity() { 
        // Implement client-side or AJAX filter logic here
    }
    function filterProperties() { 
        // Implement property/township filter logic here
    }
</script>
</body>
</html>


 <!-- HEADER / HERO INTRO -->
        <header class="relative bg-gradient-to-b from-[#fcfbf9] to-[#f4f1ea] rounded-3xl border border-stone-300/80 py-16 sm:py-20 text-center overflow-hidden font-serif shadow-2xl transition-all duration-500">
            <div class="absolute inset-0 z-0 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?q=80&w=1600&auto=format&fit=crop" 
                     alt="Modern Luxury Condo Interior" 
                     class="w-full h-full object-cover opacity-40 transform scale-105 transition-transform duration-1000 ease-out hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-[#f4f1ea] via-[#f4f1ea]/40 to-[#fcfbf9]/80"></div>
            </div>

            <div class="relative z-10 max-w-3xl mx-auto px-6">
                <div class="inline-flex items-center gap-2.5 bg-white/90 border border-emerald-600/30 px-4 py-1.5 rounded-full mb-6 shadow-md backdrop-blur-md">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-600"></span>
                    </span>
                    <span class="uppercase tracking-widest text-[10px] font-sans font-bold text-emerald-950">
                       Established Property Management
                    </span>
                </div>

               <h1 class="text-3xl sm:text-4xl md:text-5xl font-serif font-semibold text-stone-900 max-w-2xl mx-auto leading-snug title-classic tracking-tight">
                    One platform. Perfect harmony for <span class="italic font-normal text-amber-900">Renters</span> &amp; <span class="italic font-normal text-amber-900">Owners</span>.
                </h1>

                <div class="flex items-center justify-center gap-3 my-6">
                    <div class="w-12 h-[1px] bg-amber-800/40"></div>
                    <div class="h-1.5 w-1.5 rotate-45 border border-amber-800 bg-amber-900"></div>
                    <div class="w-12 h-[1px] bg-amber-800/40"></div>
                </div>

                <div class="mt-4 inline-flex flex-wrap items-center justify-center gap-3 bg-white/95 border border-stone-300/80 backdrop-blur-xl px-7 py-3.5 rounded-2xl shadow-xl text-xs font-sans text-stone-800 transform hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center gap-1.5 text-stone-500 font-bold uppercase tracking-wider text-[10px]">
                        <i class="fa-solid fa-city text-amber-900"></i>
                        <span>Quick Search:</span>
                    </div>
                    
                    <div class="h-4 w-[1px] bg-stone-300 hidden sm:block"></div>

                    <button onclick="quickSearch('Yangon')" class="text-stone-900 hover:text-amber-900 font-bold underline underline-offset-4 decoration-amber-800/30 hover:decoration-amber-900 transition-all px-1.5">
                        ရန်ကုန်
                    </button> 
                    <span class="text-stone-300">|</span>
                    
                    
                    
                    
                    <button onclick="quickSearch('AVAILABLE')" class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 font-bold px-3 py-1 rounded-lg transition-all shadow-xs">
                        <i class="fa-solid fa-building text-[10px]"></i>
                        <span>Available</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- SHOWCASE SECTION -->
        <section class="max-w-6xl mx-auto px-6 w-full pt-10 pb-12">
            <div class="bg-[#f5f2eb] rounded-xl shadow-sm overflow-hidden border border-stone-300/60 text-stone-800">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 p-6 lg:p-8 items-center">
                    
                    <div class="lg:col-span-5 space-y-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[9px] uppercase font-bold tracking-wider bg-amber-700/10 text-amber-800 border border-amber-700/20">
                            <i class="fa-solid fa-feather-pointed mr-1 text-amber-700"></i> Elite Core Premium
                        </span>
                        <h2 class="text-2xl lg:text-3xl font-normal leading-tight text-stone-900 title-classic">
                            ယုံကြည်စိတ်ချရသော <br>
                            <span class="text-blue-900 font-semibold italic">အိမ်ရာစီမံခန့်ခွဲမှုဗဟို</span>
                        </h2>
                        <p class="text-stone-600 text-xs leading-relaxed font-light">
                            အဆောင်နှင့် တိုက်ခန်းငှားရမ်းခြင်းလုပ်ငန်းများကို ခေတ်မီစနစ်များဖြင့် ဒစ်ဂျစ်တယ်စနစ်သို့ ပြောင်းလဲလိုက်ပါ။ ပိုင်ရှင်နှင့် အိမ်ငှားကြား စာရွက်စာတမ်းရှုပ်ထွေးမှုများကို ဘေးကင်းလုံခြုံစွာ ဖြေရှင်းပေးပါသည်။
                        </p>
                        
                        <div class="space-y-3 pt-3 border-t border-stone-300/60">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Auto-Sync Digital Contracts</h4>
                                    <p class="text-[10px] text-stone-500">စာချုပ်သက်တမ်းကုန်ဆုံးပါက အခန်းများကို Available အဖြစ် အလိုအလျောက်ပြောင်းလဲပေးခြင်း။</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Polymorphic Core Tracking</h4>
                                    <p class="text-[10px] text-stone-500">တိုက်ခန်းများနှင့် အဆောင်ဒေတာများကို ပေါင်းစည်းထားသော ရလဒ်ထွက်စနစ်ဖြင့် စနစ်တကျပြသပေးခြင်း။</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7 grid grid-cols-12 gap-3 h-[220px] lg:h-[260px]">
                        <div class="col-span-7 relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80" 
                                 alt="Modern Room Architecture" 
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                            <span class="absolute bottom-2.5 left-2.5 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-2 py-0.5 rounded-sm text-[9px] uppercase tracking-wider font-bold text-amber-300">
                                Premium Spaces
                            </span>
                        </div>
                        <div class="col-span-5 grid grid-rows-2 gap-3">
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=400&q=80" 
                                     alt="Cozy Interior View" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Cozy Hostels
                                </span>
                            </div>
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=400&q=80" 
                                     alt="Verified Apartment" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Verified Units
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>




        <div id="preferenceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity duration-300">
    <div class="relative w-full max-w-2xl bg-[#FCFAF7] rounded-3xl shadow-2xl overflow-hidden border border-[#E8E2D5] transition-all transform scale-100">
        
        <div class="flex items-center justify-between px-8 py-6 bg-[#2D2319] text-white">
            <div>
                <span class="inline-block px-3 py-1 text-xs font-semibold tracking-wider text-[#D9A362] bg-[#3E3225] rounded-full uppercase mb-1">
                    Matching & Comparison Engine
                </span>
                <h3 class="text-xl font-bold text-[#F8F5EE] tracking-wide">
                    သင့်စိတ်ကြိုက် အခန်းတောင်းဆိုရန်
                </h3>
            </div>
            <button type="button" onclick="closePreferenceModal()" class="flex items-center justify-center w-9 h-9 text-gray-400 hover:text-white bg-[#3E3225] hover:bg-[#4E3F30] rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="process_preference.php" method="POST" class="p-8 space-y-5 max-h-[80vh] overflow-y-auto">
            <!-- Warning မတက်အောင် Null Coalescing Operator (?? '') သုံးပေးထားပါသည် -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

            <div>
                <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                    ငှားရမ်းလိုသည့် အမျိုးအစား (Property Type)
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="rentable_type" value="both" checked class="peer hidden">
                        <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                            အားလုံး (All)
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rentable_type" value="apartment" class="peer hidden">
                        <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                            Apartment
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="rentable_type" value="hostel" class="peer hidden">
                        <div class="py-2.5 px-4 text-center text-sm font-medium text-[#4A3E31] bg-white border border-[#E0D8C8] rounded-xl peer-checked:bg-[#2D2319] peer-checked:text-white peer-checked:border-[#2D2319] transition-all shadow-sm">
                            Hostel
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        မြို့နယ်/မြို့ (City) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="city" required placeholder="ဥပမာ - ရန်ကုန်၊ မိတ္ထီလာ" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        မြို့နယ်ခွဲ (Township)
                    </label>
                    <input type="text" name="township" placeholder="ဥပမာ - ကမာရွတ်" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        အနည်းဆုံး ဘတ်ဂျက် (Min Price)
                    </label>
                    <div class="relative">
                        <input type="number" name="min_price" placeholder="50000" 
                            class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        အများဆုံး ဘတ်ဂျက် (Max Price) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="max_price" required placeholder="300000" 
                            class="w-full pl-4 pr-12 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400">MMK</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        အခန်း အမျိုးအစား (Room Type)
                    </label>
                    <input type="text" name="room_type" placeholder="ဥပမာ - Single, Studio, Shared" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        နေထိုင်မည့် လူဦးရေ (Max Occupants)
                    </label>
                    <select name="max_occupy" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <option value="">မသတ်မှတ်ထားပါ (Any)</option>
                        <option value="1">၁ ဦး (1 Person)</option>
                        <option value="2">၂ ဦး (2 Persons)</option>
                        <option value="3">၃ ဦး (3 Persons)</option>
                        <option value="4">၄ ဦး (4 Persons)</option>
                        <option value="5">၅ ဦး (5 Persons)</option>
                        <option value="6">၆ ဦး သို့မဟုတ် ထိုထက်ပိုသော (6+ Persons)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        စတင်နေထိုင်မည့် ရက်စွဲ (Move-in Date)
                    </label>
                    <input type="date" name="preferred_move_in_date" 
                        class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                        ကျား/မ သတ်မှတ်ချက် (Gender Preference)
                    </label>
                    <select name="gender_preference" class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition">
                        <option value="any">မည်သူမဆို ရသည် (Any)</option>
                        <option value="male_only">အမျိုးသားသီးသန့် (Male Only)</option>
                        <option value="female_only">အမျိုးသမီးသီးသန့် (Female Only)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-2">
                    လိုချင်သော အဆောက်အအုံ စည်းစိမ်များ (Amenities)
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="wifi" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Wi-Fi</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="ac" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Aircon</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="generator" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Generator</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="laundry" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Laundry</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="parking" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">Car Parking</span>
                    </label>
                    <label class="flex items-center space-x-2.5 p-2.5 bg-white border border-[#E0D8C8] rounded-xl cursor-pointer hover:border-[#8C6D46] transition">
                        <input type="checkbox" name="amenities[]" value="security" class="w-4 h-4 text-[#2D2319] rounded border-gray-300 focus:ring-[#8C6D46]">
                        <span class="text-xs font-medium text-[#4A3E31]">24/7 Security</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#5C4D3C] uppercase tracking-wider mb-1.5">
                    ဖြည့်စွက် အချက်အလက် (Note)
                </label>
                <textarea name="note" rows="2" placeholder="အခြား လိုအပ်ချက်များရှိပါက ရေးသားပေးပါ..." 
                    class="w-full px-4 py-2.5 bg-white border border-[#E0D8C8] rounded-xl text-sm text-[#2D2319] placeholder-gray-400 focus:ring-2 focus:ring-[#8C6D46] focus:border-transparent outline-none transition resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-[#E8E2D5]">
                <button type="button" onclick="closePreferenceModal()" 
                    class="px-5 py-2.5 text-xs font-bold tracking-wider text-[#5C4D3C] uppercase bg-transparent hover:bg-[#EAE3D2] rounded-xl transition">
                    မလုပ်တော့ပါ
                </button>
                
                <button type="submit" name="submit_preference" value="1" class="bg-[#2D2319] hover:bg-[#423425] text-[#F8F5EE] text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all active:scale-95 cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Match Preferences
                </button>
            </div>
        </form>
    </div>
</div>






  <section class="mt-20 border-t border-stone-200 pt-14 max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-3">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-amber-800">Get In Touch</span>
                        <h3 class="text-xl font-serif text-stone-900">Contact Management</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">လူကြီးမင်းတို့၏ အိမ်၊ ခြံ၊ မြေ နှင့် အဆောင်အခန်းများ ငှားရမ်းခြင်းကိစ္စရပ်များအတွက် ယုံကြည်စိတ်ချစွာ ဆက်သွယ်နိုင်ပါသည်။</p>
                    </div>
                    <div class="bg-white border border-stone-200 p-6 rounded space-y-4 shadow-sm md:col-span-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="space-y-1">
                                <span class="text-stone-400 font-bold block">📍 OFFICE ADDRESS</span>
                                <p class="text-stone-700 font-medium">အမှတ် (၁၂၀)၊ ကမ္ဘာအေးဘုရားလမ်း၊ ဗဟန်းမြို့နယ်၊ ရန်ကုန်မြို့။</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 font-bold block">📞 PHONE & HOTLINE</span>
                                <p class="text-stone-700 font-mono font-medium">+95 9 123 456 789<br>+95 1 234 567</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 block font-bold">✉️ EMAIL SUPPORT</span>
                                <p class="text-blue-900 font-medium underline">support@therentalhub.com</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 block font-bold">⏰ WORKING HOURS</span>
                                <p class="text-stone-700 font-medium">Mon - Sat | 9:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-stone-900 text-stone-400 text-xs border-t border-stone-800 mt-auto">
        <div class="max-w-6xl mx-auto px-6 py-10">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-6 border-b border-stone-800 pb-8">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 bg-amber-700 flex items-center justify-center text-stone-100 font-serif font-bold text-base">R</div>
                    <span class="text-lg font-serif font-bold tracking-tight text-white">Rental<span class="text-amber-600 italic font-normal">Hub</span></span>
                </div>
                <div class="flex flex-wrap justify-center gap-6 text-[11px] font-medium tracking-wide">
                    <a href="renterhomepage.php" class="hover:text-white transition-colors">Home</a>
                    <a href="#apartmentCardSection" class="hover:text-white transition-colors">Apartments</a>
                    <a href="#hostelCardSection" class="hover:text-white transition-colors">Hostels</a>
                    <a href="../auth/login.php?redirect=homepage" class="hover:text-white transition-colors">Admin Panel</a>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 text-[11px] text-stone-500 font-serif">
                <p>&copy; <?= date('Y'); ?> The Rental Hub Co., Ltd. All rights reserved.</p>
                <p class="italic">Crafted for Quality Property Environments.</p>
            </div>
        </div>
    </footer>


     <header class="relative bg-gradient-to-b from-[#fcfbf9] to-[#f4f1ea] rounded-3xl border border-stone-300/80 py-16 sm:py-20 text-center overflow-hidden font-serif shadow-2xl transition-all duration-500">
            <div class="absolute inset-0 z-0 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?q=80&w=1600&auto=format&fit=crop" 
                     alt="Modern Luxury Condo Interior" 
                     class="w-full h-full object-cover opacity-40 transform scale-105 transition-transform duration-1000 ease-out hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-[#f4f1ea] via-[#f4f1ea]/40 to-[#fcfbf9]/80"></div>
            </div>

            <div class="relative z-10 max-w-3xl mx-auto px-6">
                <div class="inline-flex items-center gap-2.5 bg-white/90 border border-emerald-600/30 px-4 py-1.5 rounded-full mb-6 shadow-md backdrop-blur-md">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-600"></span>
                    </span>
                    <span class="uppercase tracking-widest text-[10px] font-sans font-bold text-emerald-950">
                       Established Property Management
                    </span>
                </div>

               <h1 class="text-3xl sm:text-4xl md:text-5xl font-serif font-semibold text-stone-900 max-w-2xl mx-auto leading-snug title-classic tracking-tight">
                    One platform. Perfect harmony for <span class="italic font-normal text-amber-900">Renters</span> &amp; <span class="italic font-normal text-amber-900">Owners</span>.
                </h1>

                <div class="flex items-center justify-center gap-3 my-6">
                    <div class="w-12 h-[1px] bg-amber-800/40"></div>
                    <div class="h-1.5 w-1.5 rotate-45 border border-amber-800 bg-amber-900"></div>
                    <div class="w-12 h-[1px] bg-amber-800/40"></div>
                </div>

                <div class="mt-4 inline-flex flex-wrap items-center justify-center gap-3 bg-white/95 border border-stone-300/80 backdrop-blur-xl px-7 py-3.5 rounded-2xl shadow-xl text-xs font-sans text-stone-800 transform hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center gap-1.5 text-stone-500 font-bold uppercase tracking-wider text-[10px]">
                        <i class="fa-solid fa-city text-amber-900"></i>
                        <span>Quick Search:</span>
                    </div>
                    
                    <div class="h-4 w-[1px] bg-stone-300 hidden sm:block"></div>

                    <button onclick="quickSearch('Yangon')" class="text-stone-900 hover:text-amber-900 font-bold underline underline-offset-4 decoration-amber-800/30 hover:decoration-amber-900 transition-all px-1.5">
                        ရန်ကုန်
                    </button> 
                    <span class="text-stone-300">|</span>
                    
                    
                    
                    
                    <button onclick="quickSearch('AVAILABLE')" class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 font-bold px-3 py-1 rounded-lg transition-all shadow-xs">
                        <i class="fa-solid fa-building text-[10px]"></i>
                        <span>Available</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- SHOWCASE SECTION -->
        <section class="max-w-6xl mx-auto px-6 w-full pt-10 pb-12">
            <div class="bg-[#f5f2eb] rounded-xl shadow-sm overflow-hidden border border-stone-300/60 text-stone-800">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 p-6 lg:p-8 items-center">
                    
                    <div class="lg:col-span-5 space-y-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[9px] uppercase font-bold tracking-wider bg-amber-700/10 text-amber-800 border border-amber-700/20">
                            <i class="fa-solid fa-feather-pointed mr-1 text-amber-700"></i> Elite Core Premium
                        </span>
                        <h2 class="text-2xl lg:text-3xl font-normal leading-tight text-stone-900 title-classic">
                            ယုံကြည်စိတ်ချရသော <br>
                            <span class="text-blue-900 font-semibold italic">အိမ်ရာစီမံခန့်ခွဲမှုဗဟို</span>
                        </h2>
                        <p class="text-stone-600 text-xs leading-relaxed font-light">
                            အဆောင်နှင့် တိုက်ခန်းငှားရမ်းခြင်းလုပ်ငန်းများကို ခေတ်မီစနစ်များဖြင့် ဒစ်ဂျစ်တယ်စနစ်သို့ ပြောင်းလဲလိုက်ပါ။ ပိုင်ရှင်နှင့် အိမ်ငှားကြား စာရွက်စာတမ်းရှုပ်ထွေးမှုများကို ဘေးကင်းလုံခြုံစွာ ဖြေရှင်းပေးပါသည်။
                        </p>
                        
                        <div class="space-y-3 pt-3 border-t border-stone-300/60">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Auto-Sync Digital Contracts</h4>
                                    <p class="text-[10px] text-stone-500">စာချုပ်သက်တမ်းကုန်ဆုံးပါက အခန်းများကို Available အဖြစ် အလိုအလျောက်ပြောင်းလဲပေးခြင်း။</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Polymorphic Core Tracking</h4>
                                    <p class="text-[10px] text-stone-500">တိုက်ခန်းများနှင့် အဆောင်ဒေတာများကို ပေါင်းစည်းထားသော ရလဒ်ထွက်စနစ်ဖြင့် စနစ်တကျပြသပေးခြင်း။</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7 grid grid-cols-12 gap-3 h-[220px] lg:h-[260px]">
                        <div class="col-span-7 relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80" 
                                 alt="Modern Room Architecture" 
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                            <span class="absolute bottom-2.5 left-2.5 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-2 py-0.5 rounded-sm text-[9px] uppercase tracking-wider font-bold text-amber-300">
                                Premium Spaces
                            </span>
                        </div>
                        <div class="col-span-5 grid grid-rows-2 gap-3">
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=400&q=80" 
                                     alt="Cozy Interior View" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Cozy Hostels
                                </span>
                            </div>
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=400&q=80" 
                                     alt="Verified Apartment" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Verified Units
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
           