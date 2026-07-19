<?php
session_start();

// 1. Redirect to login if user session is missing
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Database configuration connection variables
$host     = "localhost";
$db_user  = "root";
$db_pass  = "";
$db_name  = "intern_test"; 

$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['unit'])) {
    
    $form_user_id = intval($_POST['user_id']);
    $selected_unit = $_POST['unit']; 

    // စတင်ချိန်တွင် Database ထဲသို့ NULL တိုက်ရိုက်ဝင်နိုင်ရန် သတ်မှတ်ထားပါ
    $apartment_id = null;
    $hostel_room_id = null;

    // အခန်းအမျိုးအစားအလိုက် ID ကို သေချက်ာခွဲထုတ်ခြင်း
    if (strpos($selected_unit, 'apartment_') === 0) {
        $apartment_id = intval(str_replace('apartment_', '', $selected_unit));
    } elseif (strpos($selected_unit, 'hostel_') === 0) {
        $hostel_room_id = intval(str_replace('hostel_', '', $selected_unit));
    }

    // FIX: Capture user-submitted dates directly from the form post array
    $start_date = $_POST['start_date'] ?? null; 
    $end_date = $_POST['end_date'] ?? null; 
    
    // စရန်ငွေကို float အဖြစ် ပြောင်းလဲခြင်း
    $total_deposit_amount = isset($_POST['total_deposit']) ? floatval($_POST['total_deposit']) : 0.00;

    // SQL Query
    $insert_query = "INSERT INTO contracts (user_id, apartment_id, hostel_room_id, start_date, end_date, total_deposit_amount) 
                     VALUES (?, ?, ?, ?, ?, ?)";

    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iiissd", $form_user_id, $apartment_id, $hostel_room_id, $start_date, $end_date, $total_deposit_amount);
    
    if ($insert_stmt->execute()) {
        // အသစ်ဝင်သွားတဲ့ Contract ရဲ့ ID ကို ယူခြင်း
        $new_contract_id = $conn->insert_id; 

        header("Location: installment_list.php?contract_id=" . $new_contract_id);
        exit();
    
    } else {
        echo "<div style='color:red; padding:15px; background:#ffebee; border:1px solid red; margin:10px 0;'>
                ဒေတာသိမ်းဆည်းရာတွင် အမှားအယွင်းရှိနေပါသည်: " . htmlspecialchars($insert_stmt->error) . "
              </div>";
    }
    $insert_stmt->close();
}

// 2. Fetch current user details and verify role
$user_id = $_SESSION['user_id'];
$user_query = "SELECT name, role FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// 3. Strict Role Access Control Check
if (!$user_data || $user_data['role'] !== 'renter') {
    die("Access Denied: This dashboard is reserved for Renters only.");
}

$renter_name = $user_data['name'];

// --- Capture Targeted URL Parameters from Clicking "Contract" ---
$target_id   = isset($_GET['item_id']) ? intval($_GET['item_id']) : null;
$target_type = isset($_GET['type']) ? $_GET['type'] : null;
$target_key  = ($target_id && $target_type) ? $target_type . "_" . $target_id : "";

// 4. Fetch available Apartments joined with their parent Rental House info & First Image
$apartments_query = "SELECT a.id, a.apartment_price, a.floor_level, a.max_occupy, a.deposit_amount, rh.title, rh.township, rh.amenities, img.image_url 
                     FROM apartments a
                     JOIN rental_houses rh ON a.rental_house_id = rh.id
                     LEFT JOIN (
                         SELECT rental_house_id, image_url FROM rental_house_images 
                         WHERE id IN (SELECT MIN(id) FROM rental_house_images GROUP BY rental_house_id)
                     ) img ON rh.id = img.rental_house_id
                     WHERE a.is_available = 1";
$apartments_result = $conn->query($apartments_query);

// 5. Fetch available Hostel Rooms joined with their parent Rental House info & First Image
$hostels_query = "SELECT h.id, h.monthly_price, h.room_num, h.room_type, h.sub_unit, h.deposit_amount, rh.title, rh.township, rh.amenities, img.image_url 
                  FROM hostel_rooms h
                  JOIN rental_houses rh ON h.rental_house_id = rh.id
                  LEFT JOIN (
                      SELECT rental_house_id, image_url FROM rental_house_images 
                      WHERE id IN (SELECT MIN(id) FROM rental_house_images GROUP BY rental_house_id)
                  ) img ON rh.id = img.rental_house_id
                  WHERE h.is_available = 1";
$hostels_result = $conn->query($hostels_query);

// 6. Build Arrays directly to prevent cursor exhausting issues during loops
$unit_metadata = [];
$apartments_list = [];
if ($apartments_result && $apartments_result->num_rows > 0) {
    while($row = $apartments_result->fetch_assoc()) {
        $apartments_list[] = $row;
        $key = "apartment_" . $row['id'];
        $unit_metadata[$key] = [
            'title' => htmlspecialchars($row['title'] . " (Apartment)"),
            'location' => htmlspecialchars($row['township'] . "မြို့နယ်။"),
            'floor' => htmlspecialchars($row['floor_level'] ?? '-'),
            'occupy' => htmlspecialchars($row['max_occupy']) . " ဦး",
            'price' => number_format($row['apartment_price']) . " MMK",
            'deposit' => $row['deposit_amount'],
            'amenities' => htmlspecialchars($row['amenities'] ?? 'None'),
            'image_url' => $row['image_url'] ? htmlspecialchars($row['image_url']) : ''
        ];
    }
}

$hostels_list = [];
if ($hostels_result && $hostels_result->num_rows > 0) {
    while($row = $hostels_result->fetch_assoc()) {
        $hostels_list[] = $row;
        $key = "hostel_" . $row['id'];
        $unit_metadata[$key] = [
            'title' => htmlspecialchars($row['title'] . " (Hostel Room)"),
            'location' => htmlspecialchars($row['township'] . "မြို့နယ်။"),
            'floor' => "Room " . htmlspecialchars($row['room_num'] . " (" . $row['room_type'] . ")"),
            'occupy' => htmlspecialchars($row['sub_unit']),
            'price' => number_format($row['monthly_price']) . " MMK",
            'deposit' => $row['deposit_amount'],
            'amenities' => htmlspecialchars($row['amenities'] ?? 'None'),
            'image_url' => $row['image_url'] ? htmlspecialchars($row['image_url']) : ''
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renter Dashboard - Classic Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
        .title-classic { font-family: 'Playfair Display', 'Noto Sans Myanmar', serif; }
    </style>
</head>
<body class="bg-[#faf9f6] font-classic flex flex-col h-screen text-gray-800 overflow-hidden">

            <?php include 'homepageheader.php';?>

   <div class="flex-1 h-full w-full max-w-6xl mx-auto py-10 px-4 space-y-8 overflow-y-auto">
        <div class="bg-white border border-gray-200 rounded-md p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="md:hidden mb-4">
                <button onclick="toggleMobileMenu()" class="bg-[#292515] text-white text-xs font-serif px-3 py-2 shadow-sm border border-stone-700">
                    ☰ Menu
                </button>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 title-classic">THE RENTER PORTAL</h1>
                <p class="text-[11px] uppercase tracking-wider text-gray-400 mt-1">စာချုပ်အသစ်တောင်းဆိုခြင်းနှင့် လခပေးချေမှုများကို တစ်နေရာတည်းတွင် ဆောင်ရွက်ပါ</p>
            </div>
        </div>

        <div id="contractTabContent" class="bg-white rounded-md shadow-sm border border-gray-200 overflow-hidden transition-all">
            <div class="bg-stone-50 border-b border-gray-200 px-6 py-4 text-slate-900 text-xs font-bold uppercase tracking-widest">
                📝 Сontract Application Form / စာချုပ်လျှောက်ထားလွှာပုံစံ
            </div>
            
            <form class="p-6 space-y-6" action="" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

                <div class="bg-stone-50/60 border border-gray-200 rounded-md p-4 flex items-center justify-between">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Renter Name</label>
                        <span class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($renter_name); ?></span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Account Role</label>
                        <span class="text-[10px] font-bold border border-gray-300 text-gray-600 bg-white px-2 py-0.5 rounded-sm uppercase tracking-wide"><?php echo htmlspecialchars($user_data['role']); ?></span>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest border-b border-gray-100 pb-1">🏢 ၁။ ငှားရမ်းမည့် အခန်းရွေးချယ်ရန်</h3>
                    <select id="unitSelector" name="unit" onchange="updatePreviewCard()" required class="w-full text-xs px-3 py-2.5 rounded-sm border border-gray-300 focus:outline-none focus:border-slate-500 bg-white text-gray-700 font-medium tracking-wide">
                      
                        <?php if (!empty($apartments_list)): ?>
                            <optgroup label="တိုက်ခန်းများ (Apartments)">
                                <?php foreach ($apartments_list as $row): 
                                    $key = "apartment_" . $row['id'];
                                    $isSelected = ($key === $target_key) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $key; ?>" <?php echo $isSelected; ?>>
                                        <?php echo htmlspecialchars($row['title'] . " - Floor " . $row['floor_level'] . " (ID: " . $row['id'] . ")"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>

                        <?php if (!empty($hostels_list)): ?>
                            <optgroup label="အဆောင်များ (Hostels)">
                                <?php foreach ($hostels_list as $row): 
                                    $key = "hostel_" . $row['id'];
                                    $isSelected = ($key === $target_key) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $key; ?>" <?php echo $isSelected; ?>>
                                        <?php echo htmlspecialchars($row['title'] . " - Room " . $row['room_num'] . " (ID: " . $row['id'] . ")"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- 🖼 DYNAMIC UNIT PREVIEW CARD WITH IMAGE -->
                <div class="bg-white border border-gray-200 rounded-md p-4 relative overflow-hidden shadow-sm">
                    <div class="absolute top-0 right-0 bg-slate-900 text-white text-[9px] font-bold px-2.5 py-0.5 rounded-bl uppercase tracking-wider">
                        Unit Preview
                    </div>
                    <div class="flex flex-col sm:flex-row gap-5">
                        <!-- Image Element Container -->
                        <div id="imagePreviewContainer" class="w-full sm:w-28 h-24 bg-stone-100 border border-gray-200 rounded overflow-hidden flex items-center justify-center shrink-0">
                            <img id="previewImage" src="" alt="Property" class="w-full h-full object-cover hidden">
                            <span id="noImageText" class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">No Image</span>
                        </div>
                        <div class="flex-1 space-y-1.5">
                            <h4 id="previewTitle" class="font-bold text-slate-800 text-sm tracking-tight">ပြင်ဆင်ပြီး တိုက်ခန်း/အဆောင်ခန်း</h4>
                            <p id="previewLocation" class="text-[11px] text-gray-400 uppercase tracking-wide">📍 တည်နေရာ ပြသရန်</p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-[11px] text-gray-600 pt-2 border-t border-gray-100">
                                <div>Floor/Type: <span id="previewFloor" class="font-bold text-slate-800">-</span></div>
                                <div>Occupancy: <span id="previewOccupy" class="font-bold text-slate-800">-</span></div>
                                <div>Rent: <span id="previewPrice" class="font-bold text-slate-900">-</span></div>
                                <div>Amenities: <span id="previewAmenities" class="font-medium text-gray-500">-</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest border-b border-gray-100 pb-1">📅 ၂။ စာချုပ်ရက်စွဲနှင့် စရန်ငွေ</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-1">စတင်နေထိုင်မည့်ရက် (Start Date) *</label>
                            <input type="date" name="start_date" min="<?php echo date('Y-m-d'); ?>" required class="w-full text-xs px-3 py-2 rounded-sm border border-gray-300 focus:outline-none focus:border-slate-500 bg-white text-gray-700">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-1">စာချုပ်ကုန်ဆုံးမည့်ရက် (End Date) *</label>
                            <input type="date" name="end_date" min="<?php echo date('Y-m-d'); ?>" required class="w-full text-xs px-3 py-2 rounded-sm border border-gray-300 focus:outline-none focus:border-slate-500 bg-white text-gray-700">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold text-gray-500 mb-1">လွှဲအပ်မည့် စရန်ငွေပမာဏ (Total Deposit Amount) *</label>
                            <div class="relative flex items-center">
                                <input type="number" 
                                       id="total_deposit_input"
                                       name="total_deposit" 
                                       readonly
                                       required 
                                       class="w-full text-xs px-3 py-2.5 pl-14 rounded-sm border border-gray-200 bg-stone-100 font-bold text-slate-700 select-none focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] font-bold select-none pointer-events-none">
                                    MMK
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-6 py-3 rounded-sm uppercase tracking-wider transition-all shadow-sm">
                        🚀 စာချုပ်တောင်းဆိုချက် ပေးပို့မည်
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const unitData = <?php echo json_encode($unit_metadata); ?>;

        function updatePreviewCard() {
            const selector = document.getElementById('unitSelector');
            if (!selector) return;

            const selectedKey = selector.value;
            
            const pTitle     = document.getElementById('previewTitle');
            const pLocation  = document.getElementById('previewLocation');
            const pFloor     = document.getElementById('previewFloor');
            const pOccupy    = document.getElementById('previewOccupy');
            const pPrice     = document.getElementById('previewPrice');
            const pAmenities = document.getElementById('previewAmenities');
            const depositInput = document.getElementById('total_deposit_input');
            
            // Image Preview Elements
            const pImage     = document.getElementById('previewImage');
            const noImageText = document.getElementById('noImageText');

            if (unitData && unitData[selectedKey]) {
                const data = unitData[selectedKey];

                pTitle.innerText     = data.title;
                pLocation.innerText  = "📍 " + data.location;
                pFloor.innerText     = data.floor;
                pOccupy.innerText    = data.occupy;
                pPrice.innerText     = data.price;
                pAmenities.innerText = data.amenities;
                
                if (depositInput) {
                    depositInput.value = data.deposit ? data.deposit : 0;
                }

                // Handle Image Loading Dynamically
                if (data.image_url && data.image_url !== "") {
                    pImage.src = data.image_url;
                    pImage.classList.remove('hidden');
                    noImageText.classList.add('hidden');
                } else {
                    pImage.src = "";
                    pImage.classList.add('hidden');
                    noImageText.classList.remove('hidden');
                }
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const targetId = urlParams.get('item_id');
            const targetType = urlParams.get('type');
            const selector = document.getElementById('unitSelector');

            if (selector) {
                if (targetId && targetType) {
                    // URL parameter ပါလာပါက ၎င်းတန်ဖိုးကို select လုပ်သည်
                    const generatedKey = targetType + "_" + targetId;
                    selector.value = generatedKey;
                    
                    // Fallback: အကယ်၍ matched မဖြစ်ဘဲ တန်ဖိုးလွတ်နေပါက ပထမဆုံး option ကို auto ရွေးရန်
                    if (selector.selectedIndex === -1) {
                        selector.selectedIndex = 0;
                    }
                } else {
                    // URL parameter မပါလာပါက ပထမဦးဆုံး option ကို auto ရွေးချယ်ပေးထားရန်
                    selector.selectedIndex = 0;
                }
            }
            // ရွေးချယ်ပြီးသား data ဖြင့် Preview Card ကို ချက်ချင်း Update ပြုလုပ်ရန်
            updatePreviewCard();
        });
    </script>
    <script>
      function toggleMobileMenu() {
          const sidebar = document.getElementById('tenantSidebar');
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