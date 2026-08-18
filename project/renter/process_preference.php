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

// AUTHENTICATION & SESSION HANDLING (FIXED)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Default fallback for development
}

$loggedInUserId = $_SESSION['user_id'] ?? null;
$isLoggedIn     = !empty($loggedInUserId);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Session မှ ရလဒ်များနှင့် မက်ဆေ့ဂျ်များကို ပြန်လည်ရယူခြင်း (PRG Pattern)
$message = $_SESSION['flash_message'] ?? "";
$matching_results = $_SESSION['matching_results'] ?? null;
$search_criteria = $_SESSION['search_criteria'] ?? null;

// Display ပြုလုပ်ပြီးပါက Session ထဲမှ Flash Data များကို ပြန်လည်ဖျက်ဆီးခြင်း
unset($_SESSION['flash_message'], $_SESSION['matching_results'], $_SESSION['search_criteria']);

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
    
    // Gender Filter Parameter စစ်ဆေးခြင်း
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

       
        // 2. Fetch Matching Apartments and Hostels using Dynamic Binding
        $params = [];

        $apt_township_clause = "";
        $hst_township_clause = "";
        $hst_gender_clause   = "";

        if ($township) {
            $apt_township_clause = " AND LOWER(rh.township) LIKE LOWER(:apt_township)";
            $hst_township_clause = " AND LOWER(rh.township) LIKE LOWER(:hst_township)";
        }

        // Gender Preference Dynamic SQL Clause
        if ($gender_preference !== 'any') {
            $hst_gender_clause = " AND (hr.gender_type = :hst_gender OR hr.gender_type = 'any')";
        }

        $apartment_query = "
            SELECT 
                rh.id AS house_id,
                a.id AS unit_id,
                rh.title,
                rh.city,
                rh.township,
                'apartment' AS property_type,
                a.apartment_price AS price,
                CONCAT('Max Occupy: ', a.max_occupy, ' Person(s) | Floor: ', COALESCE(a.floor_level, 'N/A')) AS details,
                'any' AS gender_type,
                a.is_available,
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
                hr.id AS unit_id,
                rh.title,
                rh.city,
                rh.township,
                'hostel' AS property_type,
                hr.monthly_price AS price,
                CONCAT('Room: ', hr.room_num, ' | Type: ', COALESCE(hr.room_type, 'Standard')) AS details,
                hr.gender_type,
                hr.is_available,
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
              $hst_gender_clause
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
            if ($gender_preference !== 'any') $params[':hst_gender'] = $gender_preference;
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
            if ($gender_preference !== 'any') $params[':hst_gender'] = $gender_preference;
        }

        // Order by Price
        $final_query .= " ORDER BY price ASC";

        $search_stmt = $pdo->prepare($final_query);
        $search_stmt->execute($params);
        
        // ရလဒ်များကို Session ထဲသို့ သိမ်းဆည်းခြင်း
        $_SESSION['matching_results'] = $search_stmt->fetchAll(PDO::FETCH_ASSOC);

        $_SESSION['search_criteria'] = [
            'type'      => $rentable_type,
            'city'      => $city,
            'max_price' => $max_price,
            'min_price' => $min_price,
            'gender'    => $gender_preference
        ];

        // POST/REDIRECT/GET (PRG) PATTERN IMPLEMENTATION
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "<div class='mb-4 p-4 text-sm text-red-800 rounded-xl bg-red-100 font-medium'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
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
<body class="bg-[#F8F5EE]">

<?php 
    if (file_exists('homepageheader.php')) { 
        include 'homepageheader.php'; 
    } 
?>

    <div class="max-w-6xl mx-auto space-y-8 py-8 px-4">
        <div class="w-full">
            <?php echo $message; ?>
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
                            $price_diff = isset($search_criteria['max_price']) ? ($search_criteria['max_price'] - $item['price']) : 0;

                            // IMAGE PATH RESOLUTION LOGIC
                            $img_src = '';
                            if (!empty($item['image_url'])) {
                                $raw_img = trim($item['image_url']);
                                
                                if (str_starts_with($raw_img, 'http://') || str_starts_with($raw_img, 'https://')) {
                                    $img_src = $raw_img;
                                } else {
                                    $file_name = basename($raw_img);
                                    
                                    if (file_exists(__DIR__ . '/uploads/' . $file_name)) {
                                        $img_src = 'uploads/' . $file_name;
                                    } 
                                    elseif (file_exists(__DIR__ . '/../uploads/' . $file_name)) {
                                        $img_src = '../uploads/' . $file_name;
                                    } 
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

                                        <!-- Details & Gender Badge Display -->
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-xs text-gray-600 bg-gray-50 p-2 rounded-lg border border-gray-100 flex-1">
                                                <?php echo htmlspecialchars($item['details']); ?>
                                            </span>

                                            <!-- Gender Tag Label -->
                                            <?php if ($item['property_type'] === 'hostel'): ?>
                                                <?php 
                                                    $gender_label = 'ကျား/မ မရွေး';
                                                    $gender_bg = 'bg-purple-50 text-purple-700 border-purple-200';
                                                    if ($item['gender_type'] === 'male_only') {
                                                        $gender_label = 'ကျား သီးသန့်';
                                                        $gender_bg = 'bg-blue-50 text-blue-700 border-blue-200';
                                                    } elseif ($item['gender_type'] === 'female_only') {
                                                        $gender_label = 'မ သီးသန့်';
                                                        $gender_bg = 'bg-pink-50 text-pink-700 border-pink-200';
                                                    }
                                                ?>
                                                <span class="text-[11px] font-semibold px-2 py-1 rounded-md border <?php echo $gender_bg; ?>">
                                                    <?php echo $gender_label; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

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
                                    <!-- Details Button -->
                                    <a href="view_details.php?id=<?= (int)($item['unit_id'] ?? $item['house_id']); ?>&house_id=<?= (int)$item['house_id']; ?>&type=<?= urlencode($item['property_type']); ?>" class="px-3 py-2 bg-white text-slate-800 border border-gray-300 rounded font-medium text-xs hover:bg-stone-50 transition-all text-center flex-1">
                                        Details
                                    </a>

                                    <!-- Book Lease / Login / Reserved Button Logic -->
                                    <?php 
                                    $isAvailable = isset($item['is_available']) ? ((int)$item['is_available'] === 1) : true; 
                                    ?>

                                    <?php if ($isAvailable): ?>
                                        <?php if ($isLoggedIn): ?>
                                            <a href="rentercontract.php?property_id=<?= (int)$item['house_id']; ?>&unit_id=<?= (int)($item['unit_id'] ?? 0); ?>&type=<?= urlencode($item['property_type']); ?>&user_id=<?= urlencode($loggedInUserId); ?>" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">
                                                Book Lease
                                            </a>
                                        <?php else: ?>
                                            <a href="../auth/login.php?redirect=contract" class="px-4 py-2 bg-slate-900 text-white border border-slate-900 rounded font-medium text-xs hover:bg-slate-800 transition-all text-center flex-1">
                                                Book Lease
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button type="button" disabled class="px-4 py-2 bg-gray-100 text-gray-400 border border-gray-200 rounded font-medium text-xs cursor-not-allowed flex-1">
                                            Reserved
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Preference Form -->
    

</body>
</html>