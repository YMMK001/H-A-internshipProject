
<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addrentalhouse'])) {
        $user_id       = 1; // Replace with $_SESSION['user_id'] after setting up login
        $title         = $_POST['title'];
        $description   = $_POST['description'] ?? null;
        $city          = $_POST['city'];
        $township      = $_POST['township'];
        $full_address  = $_POST['full_address'];
        $rentable_type = $_POST['rentable_type'];
        $is_active     = 1;

        $amenities_array = $_POST['amenities'] ?? [];
        $amenities       = !empty($amenities_array) ? implode(', ', $amenities_array) : null;

       
       
        
        
        $stmt = $conn->prepare('INSERT INTO rental_houses (user_id, title, description, city, township, full_address, rentable_type, is_active, amenities) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssssis', $user_id, $title, $description, $city, $township, $full_address, $rentable_type, $is_active, $amenities);
        $stmt->execute();
        $stmt->close();

        header('Location: rentalhouse.php?success=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing</title>
    <script src="https://cdn.tailwindcss.com"></script> 
</head>
<body class="bg-gray-50 font-sans min-h-screen pb-12 pt-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">🏠 ပိုင်ရှင်အသစ်တင်ရန်ပုံစံ (Create Listing)</h1>
            <div class="flex items-center justify-center space-x-4">
                <div id="step1-badge" class="flex items-center space-x-2 text-blue-600 font-semibold">
                    <span class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-full text-sm">၁</span>
                    <span>အခြေခံအချက်အလက်</span>
                </div>
                <div class="w-16 h-0.5 bg-gray-300"></div>
                <div id="step2-badge" class="flex items-center space-x-2 text-gray-400 font-semibold">
                    <span class="w-8 h-8 flex items-center justify-center bg-gray-200 text-gray-600 rounded-full text-sm">၂</span>
                    <span>အခန်းအသေးစိတ်</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <form id="listingForm" method="POST" action="" enctype="multipart/form-data">

                <div id="step1-section" class="space-y-6">
                    
                    

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">ပို့စ်ခေါင်းစဉ် *</label>
                        <input type="text" name="title" required placeholder="ဥပမာ - စမ်းချောင်းမြို့နယ်ရှိ အဆင့်မြင့်ပြင်ဆင်ပြီး တိုက်ခန်း" 
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">အသေးစိတ်ဖော်ပြချက် (Description)</label>
                        <textarea name="description" rows="3" placeholder="အိမ် သို့မဟုတ် အဆောင်အကြောင်း အသေးစိတ် ရေးသားရန်..." class="w-full px-4 py-2.5 rounded-xl border border-gray-300 outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">ငှားရမ်းမည့် အမျိုးအစား ရွေးချယ်ပါ *</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="border-2 border-gray-200 rounded-xl p-4 flex flex-col items-center cursor-pointer hover:border-blue-500 transition-all relative" id="type-apartment-card">
                                <input type="radio" name="rentable_type" value="Apartment" checked class="absolute top-3 right-3 accent-blue-600">
                                <span class="text-2xl mb-1">🏢</span>
                                <span class="font-bold text-gray-800">Apartment</span>
                                <span class="text-xs text-gray-400 mt-1">တိုက်ခန်းတစ်ပြင်လုံးငှားရန်</span>
                            </label>
                            
                            <label class="border-2 border-gray-200 rounded-xl p-4 flex flex-col items-center cursor-pointer hover:border-blue-500 transition-all relative" id="type-hostel-card">
                                <input type="radio" name="rentable_type" value="Hostel" class="absolute top-3 right-3 accent-blue-600">
                                <span class="text-2xl mb-1">🏫</span>
                                <span class="font-bold text-gray-800">Hostel</span>
                                <span class="text-xs text-gray-400 mt-1">အဆောင်ခန်းများ ခွဲငှားရန်</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">မြို့ *</label>
                            <select name="city" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 outline-none bg-white">
                                <option value="Yangon">ရန်ကုန်</option>
                                <option value="Mandalay">မန္တလေး</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">မြို့နယ် *</label>
                            <select name="township" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 outline-none bg-white">
                                <option value="Sanchaung">စမ်းချောင်း</option>
                                <option value="Hledan">လှည်းတန်း</option>
                                <option value="Kamayut">ကမာရွတ်</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">လိပ်စာအပြည့်အစုံ *</label>
                        <input type="text" name="full_address" required rows="3" placeholder="လမ်းနာမည်၊ အိမ်နံပါတ် အပြည့်အစုံရိုက်ပါ..." class="w-full px-4 py-2.5 rounded-xl border border-gray-300 outline-none focus:border-blue-500"></input>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">ပါဝင်သော ဝန်ဆောင်မှုများ (Amenities)</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="flex items-center space-x-2 p-3 bg-gray-50 rounded-xl cursor-pointer text-sm">
                                <input type="checkbox" name="amenities[]" value="Aircon" class="rounded accent-blue-600"> <span>❄️ Aircon</span>
                            </label>
                            <label class="flex items-center space-x-2 p-3 bg-gray-50 rounded-xl cursor-pointer text-sm">
                                <input type="checkbox" name="amenities[]" value="Wi-Fi" class="rounded accent-blue-600"> <span>🌐 Wi-Fi</span>
                            </label>
                            <label class="flex items-center space-x-2 p-3 bg-gray-50 rounded-xl cursor-pointer text-sm">
                                <input type="checkbox" name="amenities[]" value="Parking" class="rounded accent-blue-600"> <span>🚗 Parking</span>
                            </label>
                            <label class="flex items-center space-x-2 p-3 bg-gray-50 rounded-xl cursor-pointer text-sm">
                                <input type="checkbox" name="amenities[]" value="Generator" class="rounded accent-blue-600"> <span>⚡ Generator</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="addrentalhouse" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                            သိမ်းဆည်းမည် (Submit Listing) ➔
                        </button>
                    </div>
                </div>
            </form>
        </div>

         
                    <div id="apartment-fields" class="space-y-5">
                        <h3 class="text-md font-bold text-gray-800 pb-2 border-b">🏢 Apartment တိုက်ခန်းအသေးစိတ် ဖြည့်စွက်ရန်</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">ဘယ်နှလွှာလဲ (Floor Level) *</label>
                                <select name="floor_level" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white">
                                    <option value="Ground Floor">မြေညီထပ် (Ground Floor)</option>
                                    <option value="1st Floor">ပထမထပ် (1st Floor)</option>
                                    <option value="2nd Floor">ဒုတိယထပ် (2nd Floor)</option>
                                    <option value="3rd Floor">တတိယထပ် (3rd Floor)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">နေနိုင်သည့် လူဦးရေ (Max Occupants) *</label>
                                <input type="number" name="max_occupy" min="1" value="4" class="w-full px-4 py-2.5 rounded-xl border border-gray-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">လစဉ်ငှားရမ်းခ (MMK) *</label>
                                <input type="number" name="apartment_price" placeholder="ဥပမာ - 500000" class="w-full px-4 py-2.5 rounded-xl border border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">လက်ရှိ အခန်းအခြေအနေ *</label>
                                <select name="is_available_apt" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white">
                                    <option value="1">ငှားရန်အားပါသည် (Available)</option>
                                    <option value="0">မအားသေးပါ (Rented)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-4 pt-6 border-t">
                        <button type="button" onclick="goToStep1()" class="w-1/3 bg-gray-100 text-gray-700 font-semibold py-3 rounded-xl hover:bg-gray-200 transition-colors">
                            ⏪ နောက်သို့
                        </button>
                        <button type="submit" class="w-2/3 bg-green-600 text-white font-semibold py-3 rounded-xl hover:bg-green-700 transition-colors shadow-sm">
                            💾 အချက်အလက်များသိမ်းဆည်းမည်
                        </button>
                    </div>

    </div>
</body>
<script>
        const step1Section = document.getElementById('step1-section');
        const step2Section = document.getElementById('step2-section');
        const step1Badge = document.getElementById('step1-badge');
        const step2Badge = document.getElementById('step2-badge');
        
        const apartmentFields = document.getElementById('apartment-fields');
        const hostelFields = document.getElementById('hostel-fields');
        const hostelTbody = document.getElementById('hostel-rooms-tbody');

        let roomIndex = 1; // Dynamic Input Rows အတွက် Index ခြေရာခံရန်

        function goToStep2() {
            const selectedType = document.querySelector('input[name="rentable_type"]:checked').value;
            
            if (selectedType === 'Apartment') {
                apartmentFields.classList.remove('hidden');
                hostelFields.classList.add('hidden');
            } else {
                hostelFields.classList.remove('hidden');
                apartmentFields.classList.add('hidden');
            }

            step1Section.classList.add('hidden');
            step2Section.classList.remove('hidden');
            
            step1Badge.classList.replace('text-blue-600', 'text-gray-400');
            step2Badge.classList.replace('text-gray-400', 'text-blue-600');
        }

        function goToStep1() {
            step2Section.classList.add('hidden');
            step1Section.classList.remove('hidden');
            
            step2Badge.classList.replace('text-blue-600', 'text-gray-400');
            step1Badge.classList.replace('text-gray-400', 'text-blue-600');
        }

        function addHostelRow() {
            const newRow = document.createElement('tr');
            newRow.className = 'room-row';
            newRow.innerHTML = `
                <td class="p-2"><input type="text" name="rooms[${roomIndex}][room_num]" placeholder="102" required class="w-20 px-2 py-1.5 border rounded-lg outline-none"></td>
                <td class="p-2">
                    <select name="rooms[${roomIndex}][room_type]" class="w-28 px-2 py-1.5 border rounded-lg bg-white outline-none">
                        <option value="Single">Single</option>
                        <option value="Double">Double</option>
                        <option value="Master">Master</option>
                    </select>
                </td>
                <td class="p-2"><input type="text" name="rooms[${roomIndex}][sub_unit]" placeholder="B" class="w-16 px-2 py-1.5 border rounded-lg outline-none"></td>
                <td class="p-2"><input type="number" name="rooms[${roomIndex}][monthly_price]" placeholder="150000" required class="w-32 px-2 py-1.5 border rounded-lg outline-none"></td>
                <td class="p-2 text-center">
                    <button type="button" onclick="removeHostelRow(this)" class="text-red-500 hover:text-red-700 font-bold">✕</button>
                </td>
            `;
            hostelTbody.appendChild(newRow);
            roomIndex++;
        }

        function removeHostelRow(button) {
            const rows = hostelTbody.getElementsByClassName('room-row');
            if(rows.length > 1) {
                button.closest('tr').remove();
            } else {
                alert("အနည်းဆုံး အခန်းတစ်ခန်းတော့ ပါဝင်ရပါမည်။");
            }
        }

        document.getElementById('listingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // JavaScript ဖြင့် Form Data စုစည်းပုံ ဥပမာ
            const formData = new FormData(this);
            console.log("Form Data Object ready to send to API!");
            
            alert('🎉 အချက်အလက်များကို Database သို့ ပို့ရန် အဆင်သင့်ဖြစ်ပါပြီ။');
        });
    </script>
</html>
