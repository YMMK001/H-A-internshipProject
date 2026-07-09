<?php

// 1. Database Configuration Setup
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
     die("Database connection failure: " . $e->getMessage());
}

// Helper function to handle secure file uploads
function uploadListingImage($fileArray) {
    if (!isset($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        return null; 
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileTmpPath = $fileArray['tmp_name'];
    $fileName = $fileArray['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = md5(time() . mt_rand()) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            return $destPath; 
        }
    }
    return null;
}

// 2. Process Entry Form Data On Post Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $user_id = 1;

    $title         = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description   = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $city          = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $township      = filter_input(INPUT_POST, 'township', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $full_address  = filter_input(INPUT_POST, 'full_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $rentable_type = filter_input(INPUT_POST, 'rentable_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   
    $amenities_array = $_POST['amenities'] ?? [];
    $amenities       = !empty($amenities_array) ? implode(', ', array_map('htmlspecialchars', $amenities_array)) : null;

    if (empty($title) || empty($city) || empty($township) || empty($full_address) || empty($rentable_type)) {
        die("Error processing request: All required general information must be provided.");
    }

    try {
        $pdo->beginTransaction();

        $sql_house = "INSERT INTO rental_houses (user_id, title, description, city, township, full_address, rentable_type, amenities, is_active)
                      VALUES (:user_id, :title, :description, :city, :township, :full_address, :rentable_type, :amenities, 1)";
       
        $stmt_house = $pdo->prepare($sql_house);
        $stmt_house->execute([
            ':user_id'       => $user_id,
            ':title'         => $title,
            ':description'   => $description,
            ':city'          => $city,
            ':township'      => $township,
            ':full_address'  => $full_address,
            ':rentable_type' => $rentable_type,
            ':amenities'     => $amenities
        ]);

        $rental_house_id = $pdo->lastInsertId();

        $sql_img = "INSERT INTO rental_house_images (rental_house_id, image_url, is_cover) VALUES (:rental_house_id, :image_url, 0)";
        $stmt_img = $pdo->prepare($sql_img);

        if (isset($_FILES['property_gallery']['name']) && is_array($_FILES['property_gallery']['name'])) {
            foreach ($_FILES['property_gallery']['name'] as $index => $name) {
                if ($_FILES['property_gallery']['error'][$index] === UPLOAD_ERR_OK) {
                    $individualFile = [
                        'name'     => $_FILES['property_gallery']['name'][$index],
                        'type'     => $_FILES['property_gallery']['type'][$index],
                        'tmp_name' => $_FILES['property_gallery']['tmp_name'][$index],
                        'error'    => $_FILES['property_gallery']['error'][$index],
                        'size'     => $_FILES['property_gallery']['size'][$index]
                    ];
                    
                    $gallery_img_path = uploadListingImage($individualFile);
                    if ($gallery_img_path) {
                        $stmt_img->execute([
                            ':rental_house_id' => $rental_house_id,
                            ':image_url'       => $gallery_img_path
                        ]);
                    }
                }
            }
        }

        if ($rentable_type === 'Apartment') {
           
            $max_occupy      = isset($_POST['max_occupy']) ? (int)$_POST['max_occupy'] : 0;
            $floor_level     = $_POST['floor_level'] ?? '';
            $apartment_price = isset($_POST['apartment_price']) ? (float)$_POST['apartment_price'] : 0;
            $deposit_amount  = isset($_POST['deposit_amount_apt']) ? (float)$_POST['deposit_amount_apt'] : 0;
            $is_available    = isset($_POST['is_available_apt']) ? (int)$_POST['is_available_apt'] : 1;

            $image_url = uploadListingImage($_FILES['apartment_image'] ?? null);
            if ($image_url) {
                $stmt_img->execute([
                    ':rental_house_id' => $rental_house_id,
                    ':image_url'       => $image_url
                ]);
            }

            $sql = "INSERT INTO apartments 
                    (rental_house_id, max_occupy, floor_level, apartment_price, deposit_amount, is_available)
                    VALUES 
                    (:rental_house_id, :max_occupy, :floor_level, :apartment_price, :deposit_amount, :is_available)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':rental_house_id' => $rental_house_id,
                ':max_occupy'      => $max_occupy,
                ':floor_level'     => $floor_level,
                ':apartment_price' => $apartment_price,
                ':deposit_amount'  => $deposit_amount,
                ':is_available'    => $is_available
            ]);
        }
        elseif ($rentable_type === 'Hostel') {

            if (isset($_POST['rooms']) && is_array($_POST['rooms'])) {

                foreach ($_POST['rooms'] as $index => $room) {

                    $room_num       = htmlspecialchars($room['room_num']);
                    $room_type      = htmlspecialchars($room['room_type']);
                    $sub_unit       = htmlspecialchars($room['sub_unit']);
                    $monthly_price  = (float)$room['monthly_price'];
                    $deposit_amount = (float)$room['deposit_amount'];

                    $roomFileFallback = null;
                    if (isset($_FILES['rooms']['name'][$index]['room_image'])) {
                        $roomFileFallback = [
                            'name'     => $_FILES['rooms']['name'][$index]['room_image'],
                            'type'     => $_FILES['rooms']['type'][$index]['room_image'],
                            'tmp_name' => $_FILES['rooms']['tmp_name'][$index]['room_image'],
                            'error'    => $_FILES['rooms']['error'][$index]['room_image'],
                            'size'     => $_FILES['rooms']['size'][$index]['room_image']
                        ];
                    }
                    
                    $image_url = uploadListingImage($roomFileFallback);
                    if ($image_url) {
                        $stmt_img->execute([
                            ':rental_house_id' => $rental_house_id,
                            ':image_url'       => $image_url
                        ]);
                    }

                    $sql_hostel = "
                        INSERT INTO hostel_rooms (rental_house_id, room_num, room_type, sub_unit, monthly_price, deposit_amount, is_available)
                        VALUES (:rental_house_id, :room_num, :room_type, :sub_unit, :monthly_price, :deposit_amount, 1)
                    ";

                    $stmt_hostel = $pdo->prepare($sql_hostel);
                    $stmt_hostel->execute([
                        ':rental_house_id' => $rental_house_id,
                        ':room_num'        => $room_num,
                        ':room_type'       => $room_type,
                        ':sub_unit'        => $sub_unit,
                        ':monthly_price'   => $monthly_price,
                        ':deposit_amount'  => $deposit_amount
                    ]);
                }
            }
        }

        $pdo->commit();
        echo "<script>alert('Listing recorded successfully!'); window.location.href='hostellist.php';</script>";

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Critical system submission rollback execution triggered: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Create Listing Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex">

    <!-- Fixed Sidebar Section -->
    <div class="flex-shrink-0 h-screen sticky top-0 z-50">
        <?php include 'ownerheader.php'; ?>
    </div>

    <!-- Main Dynamic Content Area -->
    <main class="flex-1 p-6 overflow-y-auto">
        <div class="w-full max-w-5xl mx-auto">
            <div class="sm:hidden mb-4">
            <button onclick="toggleMobileMenu()" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-sans font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                ☰ Menu
            </button>
            </div>
            <!-- Section Header: Traditional Ledger Style -->
            <div class="mb-6 pb-4 border-b-2 border-gray-800 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-600 block mb-1">Owner Portal</span>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 font-sans">🏠 အိမ်/အဆောင်အသစ်တင်ရန်ပုံစံ</h1>
                    <p class="text-xs text-gray-600 mt-1 italic">Fill out the multi-step ledger form to register your property listing into the system.</p>
                </div>
                
                <!-- Traditional Boxy Stepper Component -->
                <div class="flex items-center bg-white border border-gray-300 shadow-sm font-sans text-xs font-bold uppercase tracking-wider">
                    <div id="step1-badge" class="px-4 py-2.5 bg-gray-800 text-white border-r border-gray-300 flex items-center gap-2 transition-all">
                        <span class="font-mono bg-white text-gray-900 w-4 h-4 inline-flex items-center justify-center text-[10px]">1</span>
                        <span>အခြေခံအချက်အလက်</span>
                    </div>
                    <div id="step2-badge" class="px-4 py-2.5 text-gray-400 flex items-center gap-2 transition-all">
                        <span class="font-mono bg-gray-100 border border-gray-300 text-gray-400 w-4 h-4 inline-flex items-center justify-center text-[10px]">2</span>
                        <span>အခန်းအသေးစိတ်</span>
                    </div>
                </div>
            </div>

            <!-- Central Form Container Card: Classic Sharp Border Box -->
            <div class="bg-white border border-gray-300 shadow-sm p-6 md:p-8 font-sans">
                <form id="listingForm" method="POST" enctype="multipart/form-data">

                    <!-- STEP 1 VIEWPORT -->
                    <div id="step1-section" class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ပို့စ်ခေါင်းစဉ် <span class="text-red-600">*</span></label>
                            <input type="text" name="title" required placeholder="ဥပမာ - စမ်းချောင်းမြို့နယ်ရှိ အဆင့်မြင့်ပြင်ဆင်ပြီး တိုက်ခန်း"
                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-none focus:border-gray-800 outline-none transition-all placeholder:text-gray-400 bg-white text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">အသေးစိတ်ဖော်ပြချက် (Description)</label>
                            <textarea name="description" rows="4" placeholder="အိမ် သို့မဟုတ် အဆောင်အကြောင်း အသေးစိတ် သိရှိလိုသည်များကို ရေးသားရန်..." 
                                      class="w-full px-3 py-2.5 border border-gray-300 rounded-none outline-none focus:border-gray-800 transition-all placeholder:text-gray-400 bg-white text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">ငှားရမ်းမည့် အမျိုးအစား ရွေးချယ်ပါ <span class="text-red-600">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="border-2 border-gray-800 bg-stone-50 p-4 flex items-start gap-3 cursor-pointer transition-all" id="type-apartment-card">
                                    <input type="radio" name="rentable_type" value="Apartment" checked class="w-4 h-4 text-gray-800 focus:ring-0 mt-0.5 accent-gray-800">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 text-sm">🏢 Apartment</span>
                                        <span class="text-[11px] text-gray-600 mt-1">တိုက်ခန်းတစ်ပြင်လုံး (သို့) ကွန်ဒိုတစ်ခန်းလုံး ငှားရန်</span>
                                    </div>
                                </label>
                                
                                <label class="border border-gray-300 bg-white p-4 flex items-start gap-3 cursor-pointer transition-all hover:border-gray-400" id="type-hostel-card">
                                    <input type="radio" name="rentable_type" value="Hostel" class="w-4 h-4 text-gray-800 focus:ring-0 mt-0.5 accent-gray-800">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 text-sm">🏫 Hostel</span>
                                        <span class="text-[11px] text-gray-600 mt-1">အဆောင်ခန်းများ၊ အိပ်ဆောင်ကုတင်များ သီးသန့်စီခွဲငှားရန်</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">မြို့ <span class="text-red-600">*</span></label>
                                <select name="city" class="w-full px-3 py-2.5 border border-gray-300 rounded-none outline-none bg-white focus:border-gray-800 transition-all text-sm">
                                    <option value="Yangon">ရန်ကုန်</option>
                                    <option value="Mandalay">မန္တလေး</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">မြို့နယ် <span class="text-red-600">*</span></label>
                                <select name="township" class="w-full px-3 py-2.5 border border-gray-300 rounded-none outline-none bg-white focus:border-gray-800 transition-all text-sm">
                                
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
                                     <option value="Thingsngyun">Thingsngyun</option>
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
                                    <option value="Seikkyi Kanaungto">Seikkyi Kanaungt</option>
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
        
                                    
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">လိပ်စာအပြည့်အစုံ <span class="text-red-600">*</span></label>
                            <textarea name="full_address" required rows="3" placeholder="လမ်းအမည်၊ အိမ်နံပါတ်၊ အနီးနားအမှတ်အသားများ အပြည့်အစုံရေးပါ..." 
                                      class="w-full px-3 py-2.5 border border-gray-300 rounded-none outline-none focus:border-gray-800 transition-all placeholder:text-gray-400 bg-white text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">ပါဝင်သော ဝန်ဆောင်မှုများ (Amenities)</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <label class="flex items-center gap-2 p-3 bg-stone-50 hover:bg-stone-100 border border-gray-300 cursor-pointer text-xs font-bold text-gray-700 transition-colors">
                                    <input type="checkbox" name="amenities[]" value="Aircon" class="w-4 h-4 rounded-none border-gray-300 accent-gray-800"> <span>❄️ Aircon</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 bg-stone-50 hover:bg-stone-100 border border-gray-300 cursor-pointer text-xs font-bold text-gray-700 transition-colors">
                                    <input type="checkbox" name="amenities[]" value="Wi-Fi" class="w-4 h-4 rounded-none border-gray-300 accent-gray-800"> <span>🌐 Wi-Fi</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 bg-stone-50 hover:bg-stone-100 border border-gray-300 cursor-pointer text-xs font-bold text-gray-700 transition-colors">
                                    <input type="checkbox" name="amenities[]" value="Parking" class="w-4 h-4 rounded-none border-gray-300 accent-gray-800"> <span>🚗 Parking</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 bg-stone-50 hover:bg-stone-100 border border-gray-300 cursor-pointer text-xs font-bold text-gray-700 transition-colors">
                                    <input type="checkbox" name="amenities[]" value="Generator" class="w-4 h-4 rounded-none border-gray-300 accent-gray-800"> <span>⚡ Generator</span>
                                </label>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-300">
                            <button type="button" onclick="goToStep2()" class="w-full border border-gray-800 bg-gray-900 text-white font-bold uppercase tracking-wider py-3 px-6 hover:bg-gray-800 active:bg-gray-900 transition-all text-xs flex items-center justify-center gap-2 cursor-pointer">
                                <span>ရှေ့သို့ ဆက်သွားမည်</span>
                                <span class="font-mono">➔</span>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2 VIEWPORT -->
                    <div id="step2-section" class="space-y-6 hidden">
                        
                        <!-- APARTMENT DETAIL SECTION -->
                        <div id="apartment-fields" class="space-y-6">
                            <div class="pb-2 border-b-2 border-gray-800">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">🏢 Apartment အသေးစိတ်အချက်အလက်</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ဘယ်နှစ်လွှာ (Floor Level) <span class="text-red-600">*</span></label>
                                    <select name="floor_level" class="w-full px-3 py-2.5 border border-gray-300 bg-white focus:border-gray-800 transition-all text-sm">
                                        <option value="Ground Floor">မြေညီထပ်</option>
                                        <option value="1st Floor">ပထမထပ်</option>
                                        <option value="2nd Floor">ဒုတိယထပ်</option>
                                        <option value="3rd Floor">တတိယထပ်</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">နေထိုင်နိုင်မည့် လူဦးရေ (Max Occupants) <span class="text-red-600">*</span></label>
                                    <input type="number" name="max_occupy" min="1" value="1" class="w-full px-3 py-2.5 border border-gray-300 focus:border-gray-800 outline-none transition-all bg-white font-mono text-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">လစဉ်ငှားရမ်းခ (MMK) <span class="text-red-600">*</span></label>
                                    <input type="number" id="apt_price" name="apartment_price" placeholder="ဥပမာ - 500000" class="w-full px-3 py-2.5 border border-gray-300 focus:border-gray-800 outline-none transition-all bg-white font-mono text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">စရန်ငွေ / အာမခံကြေး (MMK) <span class="text-red-600">*</span></label>
                                    <input type="number" id="apt_deposit" name="deposit_amount_apt" placeholder="လစဉ်ကြေးဖြည့်လျှင် အလိုအလျောက်တွက်ပေးမည်" class="w-full px-3 py-2.5 border border-gray-300 bg-stone-50 outline-none font-mono text-sm" readonly>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">အခန်းအခြေအနေ <span class="text-red-600">*</span></label>
                                    <select name="is_available_apt" class="w-full px-3 py-2.5 border border-gray-300 bg-white focus:border-gray-800 transition-all text-sm">
                                        <option value="1">Available (အားလုံးငှားရန်ရှိသည်)</option>
                                        <option value="0">Rented (ငှားရမ်းပြီးသားဖြစ်သည်)</option>
                                    </select>
                                </div>
                                <div class="p-4 bg-stone-50 border border-gray-300">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">📸 ဓာတ်ပုံများတင်ရန် (Gallery Images)</label>
                                    <input type="file" name="property_gallery[]" accept="image/*" multiple 
                                        class="w-full text-xs text-gray-600 file:mr-4 file:py-1.5 file:px-3 file:border file:border-gray-800 file:bg-gray-900 file:text-white hover:file:bg-gray-800 file:cursor-pointer cursor-pointer border border-gray-300 p-1 bg-white">
                                    <p class="text-[10px] text-gray-500 italic mt-1.5">ပုံအများကြီးကို တစ်ပြိုင်နက်တည်း ရွေးချယ်တင်နိုင်ပါသည်။</p>
                                </div>
                            </div>
                        </div>

                        <!-- HOSTEL DETAIL SECTION -->
                        <div id="hostel-fields" class="space-y-6 hidden">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end pb-2 border-b-2 border-gray-800 gap-3">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">🏫 Hostel အဆောင်ခန်းများ ထည့်သွင်းရန်</h3>
                                <button type="button" onclick="addHostelRow()" class="border border-gray-800 bg-gray-950 text-white px-3 py-1.5 text-xs font-bold uppercase tracking-wider hover:bg-gray-800 transition-colors cursor-pointer">
                                    ＋ အဆောင်ခန်းထပ်တိုးရန်
                                </button>
                            </div>

                            <div class="bg-white border border-gray-300 overflow-hidden w-full">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs min-w-[800px] border-collapse">
                                        <thead class="bg-gray-800 text-white font-semibold uppercase tracking-wider">
                                            <tr>
                                                <th class="p-3 border border-gray-700 w-[12%]">အခန်းနံပါတ်</th>
                                                <th class="p-3 border border-gray-700 w-[15%]">အမျိုးအစား</th>
                                                <th class="p-3 border border-gray-700 w-[10%]">အခန်းခွဲ (Sub)</th>
                                                <th class="p-3 border border-gray-700 w-[20%]">လစဉ်ကြေး (MMK)</th>
                                                <th class="p-3 border border-gray-700 w-[20%]">စရန်ငွေ (MMK)</th>
                                                <th class="p-3 border border-gray-700 w-[15%]">အခန်းပုံ (Image)</th>
                                                <th class="p-3 border border-gray-700 w-[8%] text-center">ဖျက်ရန်</th>
                                            </tr>
                                        </thead>
                                        <tbody id="hostel-rooms-tbody" class="divide-y divide-gray-300 bg-white">
                                            <tr class="room-row hover:bg-gray-50 transition-colors">
                                                <td class="p-2 border-r border-gray-200"><input type="text" name="rooms[0][room_num]" placeholder="101" class="w-full px-2 py-1.5 border border-gray-300 outline-none focus:border-gray-800 text-xs font-mono"></td>
                                                <td class="p-2 border-r border-gray-200">
                                                    <select name="rooms[0][room_type]" class="w-full px-2 py-1.5 border border-gray-300 bg-white outline-none focus:border-gray-800 text-xs">
                                                        <option value="Single">Single</option>
                                                        <option value="Double">Double</option>
                                                        <option value="Master">Master</option>
                                                    </select>
                                                </td>
                                                <td class="p-2 border-r border-gray-200"><input type="text" name="rooms[0][sub_unit]" placeholder="A" class="w-full px-2 py-1.5 border border-gray-300 outline-none focus:border-gray-800 text-xs font-mono"></td>
                                                <td class="p-2 border-r border-gray-200"><input type="number" name="rooms[0][monthly_price]" placeholder="150000" class="hostel-price w-full px-2 py-1.5 border border-gray-300 outline-none focus:border-gray-800 text-xs font-mono"></td>
                                                <td class="p-2 border-r border-gray-200"><input type="number" name="rooms[0][deposit_amount]" placeholder="အလိုအလျောက်တွက်မည်" class="hostel-deposit w-full px-2 py-1.5 border border-gray-300 bg-stone-50 outline-none text-xs font-mono" readonly></td>
                                                <td class="p-2 border-r border-gray-200"> 
                                                    <input type="file" name="property_gallery[]" accept="image/*" multiple 
                                                        class="w-full text-[11px] text-gray-600 file:mr-1 file:py-1 file:px-2 file:border file:border-gray-400 file:bg-gray-100 file:text-gray-800 border border-gray-300 p-0.5 bg-white">
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button" onclick="removeHostelRow(this)" class="w-6 h-6 text-red-700 border border-gray-300 hover:text-red-900 hover:bg-red-50 flex items-center justify-center font-bold transition-all mx-auto cursor-pointer">✕</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 Navigation Actions -->
                        <div class="flex items-center gap-4 pt-6 border-t border-gray-300">
                            <button type="button" onclick="goToStep1()" class="w-1/3 border border-gray-400 bg-gray-100 text-gray-800 font-bold uppercase tracking-wider py-3 px-6 hover:bg-gray-200 transition-all text-xs flex items-center justify-center gap-2 cursor-pointer">
                                <span>⏪ နောက်သို့</span>
                            </button>
                            <button type="submit" class="w-2/3 border border-emerald-800 bg-emerald-700 text-white font-bold uppercase tracking-wider py-3 px-6 hover:bg-emerald-800 active:bg-emerald-900 transition-all text-xs flex items-center justify-center gap-2 cursor-pointer">
                                <span>💾 အချက်အလက်များသိမ်းဆည်းမည်</span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    


    <script>
    const step1Section = document.getElementById('step1-section');
    const step2Section = document.getElementById('step2-section');
    const step1Badge = document.getElementById('step1-badge');
    const step2Badge = document.getElementById('step2-badge');
    
    const apartmentFields = document.getElementById('apartment-fields');
    const hostelFields = document.getElementById('hostel-fields');
    const hostelTbody = document.getElementById('hostel-rooms-tbody');

    const typeApartmentCard = document.getElementById('type-apartment-card');
    const typeHostelCard = document.getElementById('type-hostel-card');
    const rentableTypeInputs = document.querySelectorAll('input[name="rentable_type"]');

    let roomIndex = 1;

    // Toggle Visual State for Radio Button Cards cleanly
    rentableTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'Apartment') {
                typeApartmentCard.className = "group relative border-2 border-blue-600 bg-blue-50/20 rounded-2xl p-5 flex items-start space-x-4 cursor-pointer transition-all";
                typeHostelCard.className = "group relative border border-gray-200 rounded-2xl p-5 flex items-start space-x-4 cursor-pointer transition-all hover:border-gray-300 hover:bg-gray-50/50";
            } else {
                typeHostelCard.className = "group relative border-2 border-blue-600 bg-blue-50/20 rounded-2xl p-5 flex items-start space-x-4 cursor-pointer transition-all";
                typeApartmentCard.className = "group relative border border-gray-200 rounded-2xl p-5 flex items-start space-x-4 cursor-pointer transition-all hover:border-gray-300 hover:bg-gray-50/50";
            }
        });
    });

    // Automatic deposit calculation for Apartment
    document.getElementById('apt_price').addEventListener('input', function() {
        const price = parseFloat(this.value) || 0;
        document.getElementById('apt_deposit').value = price / 5;
    });

    // Automatic deposit calculation for Hostel rows
    hostelTbody.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('hostel-price')) {
            const priceInput = e.target;
            const row = priceInput.closest('tr');
            const depositInput = row.querySelector('.hostel-deposit');
            
            const price = parseFloat(priceInput.value) || 0;
            depositInput.value = price / 5;
        }
    });

    function goToStep2() {
        // Validation check for Step 1 required inputs
        const step1Inputs = step1Section.querySelectorAll('input[required], textarea[required]');
        let isValid = true;
        step1Inputs.forEach(input => {
            if(!input.value.trim()){
                isValid = false;
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500');
            }
        });

        if(!isValid) {
            alert('ကျေးဇူးပြု၍ မဖြစ်မနေဖြည့်ရမည့် အချက်အလက်များကို ဖြည့်စွက်ပေးပါ။');
            return;
        }

        const selectedType = document.querySelector('input[name="rentable_type"]:checked').value;
        
        if (selectedType === 'Apartment') {
            apartmentFields.classList.remove('hidden');
            hostelFields.classList.add('hidden');
            
            toggleApartmentRequired(true);
            toggleHostelRequired(false);
        } else {
            hostelFields.classList.remove('hidden');
            apartmentFields.classList.add('hidden');
            
            toggleApartmentRequired(false);
            toggleHostelRequired(true);
        }

        step1Section.classList.add('hidden');
        step2Section.classList.remove('hidden');
        
        if(step1Badge && step2Badge) {
            step1Badge.className = "flex items-center space-x-2 px-4 py-2 text-gray-400 font-medium rounded-xl transition-all";
            step1Badge.querySelector('span').className = "w-6 h-6 flex items-center justify-center bg-gray-100 text-gray-400 rounded-lg text-xs";
            
            step2Badge.className = "flex items-center space-x-2 px-4 py-2 bg-blue-50 text-blue-600 font-semibold rounded-xl transition-all";
            step2Badge.querySelector('span').className = "w-6 h-6 flex items-center justify-center bg-blue-600 text-white rounded-lg text-xs";
        }
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function goToStep1() {
        step2Section.classList.add('hidden');
        step1Section.classList.remove('hidden');
        
        if(step2Badge && step1Badge) {
            step2Badge.className = "flex items-center space-x-2 px-4 py-2 text-gray-400 font-medium rounded-xl transition-all";
            step2Badge.querySelector('span').className = "w-6 h-6 flex items-center justify-center bg-gray-100 text-gray-400 rounded-lg text-xs";
            
            step1Badge.className = "flex items-center space-x-2 px-4 py-2 bg-blue-50 text-blue-600 font-semibold rounded-xl transition-all";
            step1Badge.querySelector('span').className = "w-6 h-6 flex items-center justify-center bg-blue-600 text-white rounded-lg text-xs";
        }
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function toggleApartmentRequired(shouldBeRequired) {
        const inputs = apartmentFields.querySelectorAll('input[name="apartment_price"], input[name="deposit_amount_apt"], input[name="max_occupy"]');
        inputs.forEach(input => {
            if (shouldBeRequired) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });
    }

    function toggleHostelRequired(shouldBeRequired) {
        const inputs = hostelFields.querySelectorAll('input[name*="[room_num]"], input[name*="[monthly_price]"], input[name*="[deposit_amount]"]');
        inputs.forEach(input => {
            if (shouldBeRequired) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });
    }

    function addHostelRow() {
        const newRow = document.createElement('tr');
        newRow.className = 'room-row transition-colors hover:bg-gray-50/50';
        newRow.innerHTML = `
            <td class="p-3"><input type="text" name="rooms[${roomIndex}][room_num]" placeholder="102" required class="w-20 px-3 py-2 border rounded-xl outline-none focus:border-blue-500 transition-all"></td>
            <td class="p-3">
                <select name="rooms[${roomIndex}][room_type]" class="w-28 px-3 py-2 border rounded-xl bg-white outline-none focus:border-blue-500 transition-all">
                    <option value="Single">Single</option>
                    <option value="Double">Double</option>
                    <option value="Master">Master</option>
                </select>
            </td>
            <td class="p-3"><input type="text" name="rooms[${roomIndex}][sub_unit]" placeholder="B" class="w-16 px-3 py-2 border rounded-xl outline-none focus:border-blue-500 transition-all"></td>
            <td class="p-3"><input type="number" name="rooms[${roomIndex}][monthly_price]" placeholder="150000" required class="hostel-price w-28 px-3 py-2 border rounded-xl outline-none focus:border-blue-500 transition-all"></td>
            <td class="p-3"><input type="number" name="rooms[${roomIndex}][deposit_amount]" placeholder="အလိုအလျောက်တွက်မည်" required class="hostel-deposit w-32 px-3 py-2 border rounded-xl bg-gray-50 outline-none"></td>
            <td class="p-3"><input type="file" name="rooms[${roomIndex}][room_image]" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700 border p-1 rounded-xl bg-white"></td>
            <td class="p-3 text-center">
                <button type="button" onclick="removeHostelRow(this)" class="w-8 h-8 rounded-full text-red-500 hover:text-red-700 hover:bg-red-50 flex items-center justify-center font-bold transition-all mx-auto">✕</button>
            </td>
        `;
        hostelTbody.appendChild(newRow);
        roomIndex++;
    }

    function removeHostelRow(button) {
        const row = button.closest('tr');
        if (row) {
            row.remove();
        }
    }
</script>
<script>
        function toggleMobileMenu() {
            // This grabs the aside container inside your ownerheader file
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('mobMenuOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
    </script>
</body>
</html>