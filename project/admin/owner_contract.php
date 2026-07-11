<?php
session_start();

// Optional: Add your owner role check here if needed
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') { ... }

$host     = "localhost";
$db_user  = "root";
$db_pass  = "";
$db_name  = "intern_test"; 

$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Fetch all contracts with related user, property, and unit info
$sql = "SELECT 
            c.id AS contract_id,
            c.start_date,
            c.end_date,
            c.total_deposit_amount,
            u.name AS renter_name,
            -- Apartment Info
            a.floor_level,
            rh_a.title AS apartment_house_title,
            rh_a.township AS apartment_township,
            -- Hostel Info
            h.room_num,
            h.room_type,
            rh_h.title AS hostel_house_title,
            rh_h.township AS hostel_township
        FROM contracts c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN apartments a ON c.apartment_id = a.id
        LEFT JOIN rental_houses rh_a ON a.rental_house_id = rh_a.id
        LEFT JOIN hostel_rooms h ON c.hostel_room_id = h.id
        LEFT JOIN rental_houses rh_h ON h.rental_house_id = rh_h.id
        ORDER BY c.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Contract Applications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 font-serif h-screen overflow-hidden text-gray-900">

    <div id="mobMenuOverlay" class="fixed inset-0 bg-black/40 z-20 hidden transition-opacity" onclick="toggleMobileMenu()"></div>

    <div class="flex h-full w-full overflow-hidden">
        
        <?php include 'ownerheader.php'; ?>

        <div class="flex-1 h-full  ">
            <div class="w-full max-w-7xl mx-auto">
                        
                <div class="bg-white border border-gray-300 shadow-sm px-4 py-3 mb-6 flex items-center justify-between font-sans rounded-sm">
                    <div class="flex items-center space-x-3">
                        <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                            ☰ Menu
                        </button>
                        <div class="hidden sm:flex items-center space-x-2 text-xs text-gray-500">
                            <span class="text-gray-800 font-bold text-lg">Contracts</span>
                               </div>
                    </div>

                    
                    
                    <div class="flex items-center space-x-4 divide-x divide-gray-200">
                        
                        <div class="pl-4 flex items-center space-x-2">
                            <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-white text-xs font-bold shadow-inner border border-slate-700">
                                AD
                            </div>
                            <div class="hidden lg:block leading-none">
                                <p class="text-xs font-bold text-gray-900">အိမ်ပိုင်ရှင် မန်နေဂျာ</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Console Role</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6">
        <div class="mb-6 pb-4 border-b-2 border-gray-800 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                 <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-600 block mb-1">Owner Portal</span>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 font-sans">Contract Management</h1>
                        </div>
                
                        <div class="bg-slate-800 border border-slate-700 text-white font-medium text-xs uppercase tracking-wider px-4 py-2.5 rounded shadow-sm font-sans self-start sm:self-auto">
                        Total Contracts <span class="font-bold "><?php echo $result ? $result->num_rows : 0; ?></span> 
                    </div>
                
            </div>
            
            <!-- Section Header: Traditional Ledger Style -->
           

            <!-- Metrics Grid: Classic Table Data Summaries -->
            

            <!-- Data Ledger Table Component -->
            <div class="bg-white border border-gray-300 shadow-sm overflow-hidden mb-8 w-full font-sans">
                <div class="overflow-x-auto max-h-[500px]">
                    <table class="w-full text-left border-collapse whitespace-nowrap table-fixed border-gray-300">
                        <thead class="bg-gray-800 text-white text-xs font-semibold uppercase tracking-wider sticky top-0 z-10">
                            <tr>
                                <th class="p-3 pl-4 w-[10%] border border-gray-700">စာချုပ် ID</th>
                                <th class="p-3 w-[20%] border border-gray-700">Renter Name</th>
                                <th class="p-3 w-[35%] border border-gray-700">အိမ်ခြံမြေ / အခန်းအချက်အလက်</th>
                                <th class="p-3 w-[20%] border border-gray-700">ငှားရမ်းသည့်ကာလ (Lease Period)</th>
                                <th class="p-3 w-[15%] border border-gray-700 text-right">စရန်ငွေပမာဏ</th>
                            </tr>
                        </thead> 
                        <tbody class="divide-y divide-gray-300 text-xs text-gray-800 bg-white">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition-colors odd:bg-stone-50/50">
                                        
                                        <!-- ID Column -->
                                        <td class="p-3 pl-4 font-bold text-gray-500 border-r border-gray-200">
                                            #<?php echo $row['contract_id']; ?>
                                        </td>
                                        
                                        <!-- Renter Profile Column -->
                                        <td class="p-3 border-r border-gray-200 overflow-hidden text-ellipsis">
                                            <div class="font-bold text-gray-900 truncate"><?php echo htmlspecialchars($row['renter_name']); ?></div>
                                        </td>

                                        <!-- Property & Specification Details Column -->
                                        <td class="p-3 border-r border-gray-200 overflow-hidden text-ellipsis">
                                            <?php if (!empty($row['apartment_house_title'])): ?>
                                                <div class="mb-1">
                                                    <span class="border border-blue-400 text-blue-900 text-[10px] font-bold px-1.5 py-0.5 tracking-wide bg-blue-50/50 uppercase">Apartment</span>
                                                </div>
                                                <div class="font-bold text-gray-900 truncate"><?php echo htmlspecialchars($row['apartment_house_title']); ?></div>
                                                <div class="text-[11px] text-gray-500 mt-0.5 truncate">🏢 Floor: <?php echo htmlspecialchars($row['floor_level']); ?> <span class="text-gray-300 mx-1">|</span> 📍 <?php echo htmlspecialchars($row['apartment_township']); ?></div>
                                            <?php else: ?>
                                                <div class="mb-1">
                                                    <span class="border border-purple-400 text-purple-900 text-[10px] font-bold px-1.5 py-0.5 tracking-wide bg-purple-50/50 uppercase">Hostel</span>
                                                </div>
                                                <div class="font-bold text-gray-900 truncate"><?php echo htmlspecialchars($row['hostel_house_title']); ?></div>
                                                <div class="text-[11px] text-gray-500 mt-0.5 truncate">🚪 Room: <?php echo htmlspecialchars($row['room_num']); ?> (<?php echo htmlspecialchars($row['room_type']); ?>) <span class="text-gray-300 mx-1">|</span> 📍 <?php echo htmlspecialchars($row['hostel_township']); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Calendar Lease Deadlines Column -->
                                        <td class="p-3 border-r border-gray-200 font-medium text-gray-700">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-emerald-800 font-bold text-[10px] uppercase tracking-wider w-10">Start:</span>
                                                <span class="font-mono border border-gray-300 bg-white px-1.5 py-0.5"><?php echo $row['start_date']; ?></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1.5">
                                                <span class="text-rose-800 font-bold text-[10px] uppercase tracking-wider w-10">End:</span>
                                                <span class="font-mono border border-gray-300 bg-white px-1.5 py-0.5"><?php echo $row['end_date']; ?></span>
                                            </div>
                                        </td>

                                        <!-- Numeric Account Deposit Column -->
                                        <td class="p-3 font-bold text-gray-900 text-right tracking-tight">
                                            <?php echo number_format($row['total_deposit_amount']); ?> <span class="text-[10px] font-normal text-gray-500 ml-0.5">MMK</span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <!-- Fallback State Display row -->
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-400 italic bg-gray-50">
                                        တင်သွင်းထားသော စာချုပ်လျှောက်လွှာ မရှိသေးပါ။
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table> 
                </div>
            </div>

        </div>
    </div>
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