<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $host        = 'localhost';
    $db_name     = 'intern_test'; 
    $username_db = 'root';              
    $password_db = ''; 

    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL: Role က 'renter' ဖြစ်တဲ့ အိမ်ငှားတွေရဲ့ ဒေတာ သီးသန့်ကိုပဲ ဆွဲထုတ်မည်
    $query = "
        SELECT 
            id AS renter_id,
            name AS renter_name,
            email AS renter_email,
            phone AS renter_phone,
            nrc AS renter_nrc,
            role AS renter_role
        FROM users
        WHERE role = 'renter'
        ORDER BY id DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $renters = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management System - Renter Directory</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-serif h-screen overflow-hidden text-gray-900">

    <div id="mobMenuOverlay" class="fixed inset-0 bg-black/40 z-20 hidden transition-opacity" onclick="toggleMobileMenu()"></div>

    <div class="flex h-full w-full overflow-hidden">
        
        <?php include 'ownerheader.php'; ?>

        <div class="flex-1 h-full  overflow-y-auto">
            <div class="w-full max-w-7xl mx-auto">

                <div class="bg-white border border-gray-300 shadow-sm px-4 py-3 mb-6 flex items-center justify-between font-sans rounded-sm">
                    <div class="flex items-center space-x-3">
                        <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                            ☰ Menu
                        </button>
                        <div class="hidden sm:flex items-center space-x-2 text-xs text-gray-500">
                            <span class="text-gray-800 font-bold text-2xl">Renters List</span>
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
                <div class="px-6">
                    <div class="mb-6 pb-4 border-b-2 border-slate-800 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-600 block mb-1">Internal Management Console</span>
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900 font-sans">Renters List Management</h1>
                        </div>
                    
                        <div class="bg-slate-800 border border-slate-700 text-white font-medium text-xs uppercase tracking-wider px-4 py-2.5 rounded shadow-sm font-sans self-start sm:self-auto">
                            Total Renters <?= count($renters) ?>
                        </div>
                    </div>

                    <?php if (!empty($renters)): ?>
                        <div class="bg-white border border-gray-300 shadow-sm overflow-hidden mb-8 w-full font-sans">
                            <div class="overflow-x-auto max-h-[500px]">
                                <table class="w-full text-left border-collapse whitespace-nowrap table-fixed border-gray-300">
                                    <thead class="bg-slate-800 text-white text-xs font-semibold uppercase tracking-wider sticky top-0 z-10">
                                        <tr>
                                            <th class="p-3 pl-4 w-[10%] border border-slate-700">ID</th>
                                            <th class="p-3 w-[20%] border border-slate-700">Renter Name</th> 
                                            <th class="p-3 w-[25%] border border-slate-700">Ph-No</th>
                                            <th class="p-3 w-[20%] border border-slate-700">NRC</th>
                                            <th class="p-3 text-center w-[10%] border border-slate-700">Role</th>
                                            <th class="p-3 text-center w-[15%] border border-slate-700">Action</th>
                                        </tr>
                                    </thead> 
                                    <tbody class="divide-y divide-gray-300 text-xs text-gray-800 bg-white">
                                        <?php foreach ($renters as $renter): ?>
                                            <tr class="hover:bg-gray-50 transition-colors odd:bg-stone-50/50">
                                                
                                                <td class="p-3 pl-4 font-mono text-gray-500 border-r border-gray-200">
                                                    #<?= htmlspecialchars($renter['renter_id']) ?>
                                                </td>

                                                <td class="p-3 font-bold text-gray-900 overflow-hidden text-ellipsis border-r border-gray-200 truncate">
                                                    <?= htmlspecialchars($renter['renter_name']) ?>
                                                </td>

                                                <td class="p-3 overflow-hidden text-ellipsis border-r border-gray-200">
                                                    <div class="font-bold text-gray-900">📞 <?= htmlspecialchars($renter['renter_phone']) ?></div>
                                                    <div class="text-[11px] text-gray-500 mt-0.5">✉️ <?= htmlspecialchars($renter['renter_email'] ?? 'N/A') ?></div>
                                                </td>

                                                <td class="p-3 font-medium text-gray-700 overflow-hidden text-ellipsis border-r border-gray-200">
                                                    🪪 <?= htmlspecialchars($renter['renter_nrc']) ?>
                                                </td>

                                                <td class="p-3 text-center overflow-hidden text-ellipsis border-r border-gray-200">
                                                    <span class="inline-block border border-blue-400 text-blue-900 text-[11px] font-bold px-2 py-0.5 tracking-wide bg-blue-50/50 rounded-sm uppercase">
                                                        <?= htmlspecialchars($renter['renter_role']) ?>
                                                    </span>
                                                </td>

                                                <td class="p-3 text-center overflow-hidden text-ellipsis">
                                                    <a href="rentedlist.php?id=<?= $renter['renter_id'] ?>" 
                                                       class="inline-block text-center text-[11px] font-bold tracking-wide uppercase px-3 py-1.5 border border-emerald-500 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors rounded-sm shadow-sm">
                                                        View Details
                                                    </a>
                                                </td>
                                                
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table> 
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-white border border-gray-300 shadow-sm p-12 text-center max-w-md mx-auto my-6 font-sans">
                            <span class="text-3xl block mb-2">👥</span>
                            <h3 class="font-bold text-gray-800 text-sm mb-1">မည်သည့်အိမ်ငှားစာရင်းမျှ မရှိသေးပါ</h3>
                            <p class="text-xs text-gray-500 italic">There are currently no users with the 'renter' role matching your database filters.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('mobMenuOverlay');
            
            if (sidebar && overlay) {
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
        }
    </script>
</body>
</html>