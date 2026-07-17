<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// MATCHED: Now reads '?id=' directly from your browser's URL query string
$renter_id = $_GET['id'] ?? null;

if (!$renter_id) {
    die("Error: Renter identification token parameter missing.");
}

try {
    $host        = 'localhost';
    $db_name     = 'intern_test'; 
    $username_db = 'root';              
    $password_db = ''; 

    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Fetch Profile info for the targeted Renter matching their user column configuration
    $stmt_user = $db->prepare("SELECT id, name, phone, email, nrc, role FROM users WHERE id = :id AND role = 'RENTER' LIMIT 1");
    $stmt_user->execute([':id' => $renter_id]);
    $renter_profile = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$renter_profile) {
        die("Error: Active renter record not found.");
    }

    // 2. Fetch all rented properties mapped to this user account
    $query_rentals = "
        SELECT 
            c.id AS contract_id, c.start_date, c.end_date, c.total_deposit_amount,
            a.id AS apartment_id, a.floor_level, a.apartment_price,
            h.id AS hostel_room_id, h.room_num, h.room_type, h.monthly_price,
            COALESCE(ra.title, rh.title) AS house_title, 
            COALESCE(ra.city, rh.city) AS city, 
            COALESCE(ra.township, rh.township) AS township,
            COALESCE(ra.full_address, rh.full_address) AS full_address
        FROM contracts c
        LEFT JOIN apartments a ON c.apartment_id = a.id
        LEFT JOIN hostel_rooms h ON c.hostel_room_id = h.id
        LEFT JOIN rental_houses ra ON a.rental_house_id = ra.id
        LEFT JOIN rental_houses rh ON h.rental_house_id = rh.id
        WHERE c.user_id = :renter_id
        ORDER BY c.id DESC
    ";

    $stmt_rentals = $db->prepare($query_rentals);
    $stmt_rentals->execute([':renter_id' => $renter_id]);
    $rented_items = $stmt_rentals->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Admin - Rental Overview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', 'Noto Sans Myanmar', sans-serif; }
    </style>
</head>
<body class="bg-[#f4f6f9] text-slate-800 antialiased min-h-screen flex">

     <?php include 'ownerheader.php'; ?>

        <main class=" flex-grow">
            
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

                <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-4 gap-4">
                
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Account File View</p>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight mt-0.5">
                        Rentals For: <?= htmlspecialchars($renter_profile['name']) ?>
                    </h2>
                </div>
                <a href="view_renter.php" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-sm text-xs font-bold uppercase tracking-wider shadow-sm transition">
                    ↩️ Return to Renter Directory
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white border border-slate-200 p-4 rounded-sm shadow-xs text-xs">
                <div class="p-2 border-r border-slate-100">
                    <div class="text-slate-400 uppercase tracking-wider font-semibold text-[10px]">Primary ID Token</div>
                    <div class="text-sm font-mono font-bold text-slate-800 mt-1">#<?= htmlspecialchars($renter_profile['id']) ?></div>
                </div>
                <div class="p-2 border-r border-slate-100">
                    <div class="text-slate-400 uppercase tracking-wider font-semibold text-[10px]">Phone Information</div>
                    <div class="text-sm font-bold text-slate-800 mt-1">📞 <?= htmlspecialchars($renter_profile['phone'] ?? 'N/A') ?></div>
                </div>
                <div class="p-2 border-r border-slate-100">
                    <div class="text-slate-400 uppercase tracking-wider font-semibold text-[10px]">Email Contact</div>
                    <div class="text-sm font-bold text-slate-800 mt-1 truncate">✉️ <?= htmlspecialchars($renter_profile['email'] ?? 'N/A') ?></div>
                </div>
                <div class="p-2">
                    <div class="text-slate-400 uppercase tracking-wider font-semibold text-[10px]">NRC Registration</div>
                    <div class="text-sm font-bold text-slate-800 mt-1">💳 <?= htmlspecialchars($renter_profile['nrc'] ?? 'Unverified') ?></div>
                </div>
            </div>

            <div class="space-y-4 py-4">
                

                <?php if (!empty($rented_items)): ?>
                    <div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                                <thead class="bg-[#242d3c] text-white font-semibold uppercase tracking-wider">
                                    <tr>
                                        <th class="p-4 w-[10%] border border-slate-700">Contract ID</th>
                                        <th class="p-4 w-[25%] border border-slate-700">Property Details</th>
                                        <th class="p-4 w-[15%] border border-slate-700">Classification</th>
                                        <th class="p-4 w-[15%] border border-slate-700">Pricing Grid</th>
                                        <th class="p-4 w-[20%] border border-slate-700">Lease Validation Dates</th>
                                        <th class="p-4 text-center w-[15%] border border-slate-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 text-slate-700 bg-white">
                                    <?php foreach ($rented_items as $row): ?>
                                        <?php 
                                            $is_apartment = !empty($row['apartment_id']);
                                            $type_badge = $is_apartment ? 'Apartment' : 'Hostel Room';
                                            $spec = $is_apartment ? "Floor: " . $row['floor_level'] : "Room: " . $row['room_num'] . " (" . $row['room_type'] . ")";
                                            $price = ($is_apartment ? $row['apartment_price'] : $row['monthly_price']) ?? 0;
                                        ?>
                                        <tr class="hover:bg-slate-50/80 transition-colors odd:bg-slate-50/30">
                                            
                                            <td class="p-4 font-mono text-slate-400">
                                                #<?= htmlspecialchars($row['contract_id']) ?>
                                            </td>

                                            <td class="p-4 truncate">
                                                <div class="font-bold text-slate-900 uppercase tracking-tight text-[13px]">
                                                    <?= htmlspecialchars($row['house_title'] ?? 'Unknown Property Set') ?>
                                                </div>
                                                <div class="text-[11px] text-slate-400 mt-1 truncate">
                                                    📍 <?= htmlspecialchars($row['full_address'] ?? ($row['township'] . ', ' . $row['city'])) ?>
                                                </div>
                                            </td>

                                            <td class="p-4">
                                                <span class="inline-block text-[8px] font-extrabold px-2 py-0.5 mb-1 rounded-xs tracking-wide uppercase border 
                                                    <?= $is_apartment ? 'border-blue-200 bg-blue-50 text-blue-800' : 'border-slate-300 bg-slate-100 text-slate-700' ?>">
                                                    <?= $type_badge ?>
                                                </span>
                                                <div class="font-semibold text-slate-600"><?= htmlspecialchars($spec) ?></div>
                                            </td>

                                            <td class="p-4 font-sans">
                                                <div class="font-bold text-slate-900 text-[13px]">
                                                    <?= number_format($price) ?> <span class="text-[9px] font-normal text-slate-400">MMK/mo</span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 mt-0.5">
                                                    Deposit: <?= number_format($row['total_deposit_amount'] ?? 0) ?> MMK
                                                </div>
                                            </td>

                                            <td class="p-4 text-slate-600 font-medium">
                                                <div class="space-y-0.5">
                                                    <div><span class="text-slate-400 font-normal">Start:</span> <?= date('d-M-Y', strtotime($row['start_date'])) ?></div>
                                                    <div><span class="text-slate-400 font-normal">End:</span> <?= date('d-M-Y', strtotime($row['end_date'])) ?></div>
                                                </div>
                                            </td>

                                            <td class="p-4 text-center">
                                                <a href="view_payments.php?contract_id=<?= $row['contract_id'] ?>" 
                                                   class="inline-block text-center text-[10px] font-bold tracking-widest uppercase px-3 py-1.5 border border-slate-400 text-slate-700 rounded-sm hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all shadow-2xs">
                                                    View Payments
                                                </a>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-white border border-slate-200 rounded-sm p-12 text-center shadow-xs">
                        <div class="text-3xl mb-2">🏢</div>
                        <h4 class="font-bold text-slate-800 text-sm uppercase tracking-tight">No Active Leases Documented</h4>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto mt-1">This user is not currently bound to any active system apartment or hostel room rentals.</p>
                    </div>
                <?php endif; ?>
            </div>
</div>
        </main>
    </div>

</body>
</html>