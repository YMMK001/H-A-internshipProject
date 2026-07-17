<?php
// 1. Database Configuration
$host     = 'localhost';
$dbName   = 'intern_test';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// 2. Retrieve and Validate the contract_id parameter from URL
$contract_id = isset($_GET['contract_id']) ? intval($_GET['contract_id']) : 0;

if ($contract_id <= 0) {
    die("<div class='p-8 text-red-600 font-semibold text-center'>Error: Invalid or missing Contract ID.</div>");
}

// 3. Fetch Payments and Contract metadata for ONLY this contract_id
$query = "
    SELECT 
        p.id AS payment_id,
        p.paid_amount,
        p.payment_image,
        p.paid_at,
        u.name AS tenant_name,
        u.phone AS tenant_phone,
        pm.name AS payment_method,
        i.installment_period,
        i.due_date,
        c.id AS contract_id,
        c.start_date,
        c.end_date,
        CASE 
            WHEN c.apartment_id IS NOT NULL THEN 'Apartment'
            ELSE 'Hostel Room'
        END AS rental_type,
        COALESCE(rh_apt.title, rh_hst.title) AS property_title,
        COALESCE(rh_apt.full_address, rh_hst.full_address) AS property_address,
        COALESCE(
            CONCAT('Floor ', apt.floor_level), 
            CONCAT('Room ', hst.room_num, ' (', COALESCE(hst.sub_unit, 'Main'), ')')
        ) AS unit_details
    FROM payments p
    INNER JOIN installments i ON p.installment_id = i.id
    INNER JOIN contracts c ON i.contract_id = c.id
    INNER JOIN users u ON c.user_id = u.id
    INNER JOIN payment_methods pm ON p.payment_method_id = pm.id
    LEFT JOIN apartments apt ON c.apartment_id = apt.id
    LEFT JOIN rental_houses rh_apt ON apt.rental_house_id = rh_apt.id
    LEFT JOIN hostel_rooms hst ON c.hostel_room_id = hst.id
    LEFT JOIN rental_houses rh_hst ON hst.rental_house_id = rh_hst.id
    WHERE c.id = :contract_id
    ORDER BY p.paid_at DESC
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute(['contract_id' => $contract_id]);
    $payments = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Query Failed: " . $e->getMessage());
}

// 4. Fallback Info query
$room_info = null;
if (empty($payments)) {
    $info_query = "
        SELECT 
            c.id AS contract_id,
            c.start_date,
            c.end_date,
            u.name AS tenant_name,
            CASE WHEN c.apartment_id IS NOT NULL THEN 'Apartment' ELSE 'Hostel Room' END AS rental_type,
            COALESCE(rh_apt.title, rh_hst.title) AS property_title,
            COALESCE(
                CONCAT('Floor ', apt.floor_level), 
                CONCAT('Room ', hst.room_num, ' (', COALESCE(hst.sub_unit, 'Main'), ')')
            ) AS unit_details
        FROM contracts c
        INNER JOIN users u ON c.user_id = u.id
        LEFT JOIN apartments apt ON c.apartment_id = apt.id
        LEFT JOIN rental_houses rh_apt ON apt.rental_house_id = rh_apt.id
        LEFT JOIN hostel_rooms hst ON c.hostel_room_id = hst.id
        LEFT JOIN rental_houses rh_hst ON hst.rental_house_id = rh_hst.id
        WHERE c.id = :contract_id
    ";
    $stmt_info = $pdo->prepare($info_query);
    $stmt_info->execute(['contract_id' => $contract_id]);
    $room_info = $stmt_info->fetch();
} else {
    $room_info = $payments[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Payments Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex">

    <?php include 'ownerheader.php'; ?>

    <div class="flex-1 min-w-0 flex flex-col">

        <div class="bg-white border-b border-gray-200 shadow-sm px-6 py-4 flex items-center justify-between font-sans">
            <div class="flex items-center space-x-3">
                <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                    ☰ Menu
                </button>
                <div class="hidden sm:flex items-center space-x-2">
                    <span class="text-gray-800 font-bold text-2xl">Payment Details</span>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
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

            <div class="mb-6">
                <a href="javascript:history.back()" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-indigo-600 transition gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Allocations
                </a>
            </div>

            <?php if (!$room_info): ?>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-6 text-center">
                    Contract record #<?= $contract_id ?> not found in system database.
                </div>
            <?php else: ?>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($room_info['property_title']) ?></h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $room_info['rental_type'] === 'Apartment' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' ?>">
                                <?= htmlspecialchars($room_info['rental_type']) ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($room_info['unit_details']) ?></p>
                        <div class="mt-4 text-xs text-gray-400 space-y-1">
                            <div><strong class="text-gray-600 font-medium">Tenant:</strong> <?= htmlspecialchars($room_info['tenant_name']) ?></div>
                            <div><strong class="text-gray-600 font-medium">Lease Period:</strong> <?= date('d M Y', strtotime($room_info['start_date'])) ?> to <?= date('d M Y', strtotime($room_info['end_date'])) ?></div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col justify-center min-w-[200px]">
                        <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Contract ID</span>
                        <span class="text-3xl font-black text-slate-800">#<?= htmlspecialchars($room_info['contract_id']) ?></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="font-bold text-gray-800">Payment Transaction Logs</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <th class="py-4 px-6">Payment ID</th>
                                    <th class="py-4 px-6">Period</th>
                                    <th class="py-4 px-6">Due Date</th>
                                    <th class="py-4 px-6">Method Used</th>
                                    <th class="py-4 px-6 text-right">Paid Amount</th>
                                    <th class="py-4 px-6">Transaction Date</th>
                                    <th class="py-4 px-6 text-center">Receipt File</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                                <?php if (empty($payments)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-16 text-gray-400">
                                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            No payments recorded yet for this room contract.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="py-4 px-6 font-semibold text-indigo-600">
                                                #<?= htmlspecialchars($payment['payment_id']) ?>
                                            </td>
                                            <td class="py-4 px-6 font-medium text-gray-900">
                                                Period <?= htmlspecialchars($payment['installment_period']) ?>
                                            </td>
                                            <td class="py-4 px-6 text-gray-500">
                                                <?= date('d M, Y', strtotime($payment['due_date'])) ?>
                                            </td>
                                            <td class="py-4 px-6 text-gray-600">
                                                <?= htmlspecialchars($payment['payment_method']) ?>
                                            </td>
                                            <td class="py-4 px-6 text-right font-bold text-slate-900">
                                                <?= number_format($payment['paid_amount'], 2) ?> MMK
                                            </td>
                                            <td class="py-4 px-6 text-gray-500">
                                                <?= date('d M, Y h:i A', strtotime($payment['paid_at'])) ?>
                                            </td>
                                            <td class="py-4 px-6 text-center">
                                                <?php if (!empty($payment['payment_image'])): ?>
                                                    <a href="<?= htmlspecialchars($payment['payment_image']) ?>" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                                                        View Receipt
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-xs text-gray-400 italic">No attachment</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div> </div> </body>
</html>