<?php
// 1. SESSION INITIALIZATION & VISITOR GUARD
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security redirect to login if the user is unauthenticated or an administrative user
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: login.php?redirect=homepage");
    exit;
}

$renter_id        = $_SESSION['user_id'];
$username_session = htmlspecialchars($_SESSION['username'] ?? 'Renter');
$renter_email     = $_SESSION['email'] ?? 'Not Specified';

// 2. DATABASE CONFIGURATION & CONNECTION (PDO)
$host        = 'localhost';
$db_name     = 'intern_test'; 
$username_db = 'root';              
$password_db = ''; 

$active_contract_id   = null;
$contracts            = [];
$active_lease         = null;
$past_leases          = [];
$installments         = [];
$overdue_installments = [];
$today                = date('Y-m-d');

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- COMPREHENSIVE CONTRACT AND LEASED UNIT DETAILS WITH OWNER CONTACT ---
    $contract_query = "
        SELECT 
            c.id AS contract_id,
            c.start_date,
            c.end_date,
            c.total_deposit_amount,
            rh.title AS property_title,
            rh.township,
            rh.city,
            rh.full_address,
            ap.id AS apartment_id,
            ap.floor_level,
            ap.apartment_price AS ap_price,
            hr.id AS hostel_room_id,
            hr.room_num,
            hr.room_type,
            hr.monthly_price AS hr_price,
            u.username AS owner_name,
            u.phone AS owner_phone,
            u.email AS owner_email
        FROM contracts c
        LEFT JOIN apartments ap ON c.apartment_id = ap.id
        LEFT JOIN hostel_rooms hr ON c.hostel_room_id = hr.id
        LEFT JOIN rental_houses rh ON (ap.rental_house_id = rh.id OR hr.rental_house_id = rh.id)
        LEFT JOIN users u ON rh.user_id = u.id
        WHERE c.user_id = :user_id
        ORDER BY c.end_date DESC
    ";

    $stmt = $db->prepare($contract_query);
    $stmt->execute([':user_id' => $renter_id]);
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($contracts as $lease) {
        if ($lease['start_date'] <= $today && $lease['end_date'] >= $today) {
            $active_lease = $lease;
            $active_contract_id = $lease['contract_id'];
        } else {
            $past_leases[] = $lease;
        }
    }

    // --- FETCH UPCOMING PAYMENTS FOR ACTIVE CONTRACT (WITH PROPERTY/UNIT INFO) ---
    if ($active_contract_id) {
        $inst_query = "
            SELECT 
                i.id, 
                i.installment_period, 
                i.amount_to_pay, 
                i.due_date, 
                i.status,
                rh.title AS property_title,
                ap.floor_level,
                hr.room_num
            FROM installments i
            JOIN contracts c ON i.contract_id = c.id
            LEFT JOIN apartments ap ON c.apartment_id = ap.id
            LEFT JOIN hostel_rooms hr ON c.hostel_room_id = hr.id
            LEFT JOIN rental_houses rh ON (ap.rental_house_id = rh.id OR hr.rental_house_id = rh.id)
            WHERE i.contract_id = :contract_id 
            ORDER BY i.due_date ASC
        ";
        $stmt_inst = $db->prepare($inst_query);
        $stmt_inst->execute([':contract_id' => $active_contract_id]);
        $installments = $stmt_inst->fetchAll(PDO::FETCH_ASSOC);

        // Filter out overdue installments for quick action section
        foreach ($installments as $inst) {
            if (strtolower($inst['status']) !== 'paid' && $inst['due_date'] < $today) {
                $overdue_installments[] = $inst;
            }
        }
    }

} catch (PDOException $e) {
    $db_error = $e->getMessage();
}

// Check if request is an AJAX call (Returns only main overview body)
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    renderDashboardBody($overdue_installments, $active_lease, $installments, $past_leases, $today, $active_contract_id);
    exit;
}

// FUNCTION TO RENDER MAIN CONTENT AREA
function renderDashboardBody($overdue_installments, $active_lease, $installments, $past_leases, $today, $active_contract_id) {
?>
    <!-- OVERDUE PAYMENTS ALERT SECTION -->
    <?php if (!empty($overdue_installments)): ?>
        <section class="bg-red-50/60 border-l-4 border-red-700 p-5 rounded-none shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-700"></span>
                    </span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-red-900 font-sans">
                        Action Required: Overdue Payments Detected (<?= count($overdue_installments) ?>)
                    </h3>
                </div>
                <span class="text-[10px] font-bold text-red-800 uppercase bg-red-100/80 px-2 py-0.5 border border-red-200">
                    Urgent Notice
                </span>
            </div>

            <div class="divide-y divide-red-200/60 font-sans text-xs">
                <?php foreach ($overdue_installments as $overdue): ?>
                    <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="font-bold text-stone-900 block font-serif">
                                Month #<?= (int)$overdue['installment_period'] ?> &mdash; <?= htmlspecialchars($overdue['property_title']) ?>
                            </span>
                            <span class="text-stone-500 text-[11px]">
                                Due Target: <strong class="text-red-700 font-serif italic"><?= date('d M Y', strtotime($overdue['due_date'])) ?></strong>
                            </span>
                        </div>

                        <div class="flex items-center gap-4 self-end sm:self-auto">
                            <span class="font-bold text-stone-900 text-sm">
                                <?= number_format($overdue['amount_to_pay']) ?> MMK
                            </span>
                            <a href="pay_installment.php?contract_id=<?= $active_contract_id ?>&installment_id=<?= $overdue['id'] ?>&from=profile" 
   class="px-3 py-1.5 bg-red-800 text-stone-100 font-serif text-[11px] hover:bg-red-900 transition shadow-xs font-semibold">
    Pay Overdue Bill &rarr;
</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- ACTIVE LEASE FRAMEWORK -->
    <section>
        <h3 class="text-[11px] font-bold uppercase tracking-widest text-stone-400 mb-3 border-b border-stone-200 pb-2">Active Lease Framework</h3>
        
        <?php if (!$active_lease): ?>
            <div class="bg-white border border-stone-300 p-8 text-center rounded-none shadow-xs">
                <p class="text-sm font-serif italic text-stone-500">No current active property or lease framework found linked to this portal account.</p>
                <a href="renterhomepage.php"  class="inline-block mt-4 text-xs font-serif text-[#0f172a] font-bold border-b border-[#0f172a] pb-0.5 hover:text-amber-700 hover:border-amber-700 transition-colors">Find Available Units &rarr;</a>
            </div>
        <?php else: 
            $is_ap = !empty($active_lease['apartment_id']);
            $monthly_rent = $is_ap ? $active_lease['ap_price'] : $active_lease['hr_price'];
            $specs = $is_ap ? "Floor level: " . $active_lease['floor_level'] : "Room Num: " . $active_lease['room_num'] . " (" . $active_lease['room_type'] . ")";
            
            // Format owner phone for Viber link (+959 format)
            $raw_phone = $active_lease['owner_phone'] ?? '';
            $viber_phone = preg_replace('/^09/', '+959', preg_replace('/\s+/', '', $raw_phone));
        ?>
            <div class="bg-white border border-stone-300 shadow-xs p-6 space-y-6 rounded-none">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                    <div>
                        <span class="text-[9px] tracking-wider font-bold border px-2 py-0.5 rounded-none <?= $is_ap ? 'text-blue-900 border-blue-200 bg-blue-50/50' : 'text-amber-900 border-amber-200 bg-amber-50/50' ?> uppercase">
                            <?= $is_ap ? 'Apartment Unit' : 'Hostel Space' ?>
                        </span>
                        <h4 class="text-2xl font-serif font-normal text-stone-900 mt-2"><?= htmlspecialchars($active_lease['property_title']) ?></h4>
                        <p class="text-xs text-stone-500 mt-1">📍 <?= htmlspecialchars($active_lease['full_address']) ?>, <?= htmlspecialchars($active_lease['township']) ?>, <?= htmlspecialchars($active_lease['city']) ?></p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-2xl font-bold text-stone-900 font-sans tracking-tight"><?= number_format($monthly_rent) ?></span>
                        <span class="text-[10px] text-stone-400 font-bold block uppercase tracking-wider">MMK / Month</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-[#fbfaf7] p-4 border border-stone-200 text-xs">
                    <div>
                        <span class="block text-stone-400 text-[10px] uppercase font-bold tracking-wider">Specifications</span>
                        <span class="font-medium text-stone-800"><?= htmlspecialchars($specs) ?></span>
                    </div>
                    <div>
                        <span class="block text-stone-400 text-[10px] uppercase font-bold tracking-wider">Total Deposit</span>
                        <span class="font-medium text-stone-800 font-sans"><?= number_format($active_lease['total_deposit_amount']) ?> MMK</span>
                    </div>
                    <div>
                        <span class="block text-stone-400 text-[10px] uppercase font-bold tracking-wider">Start Date</span>
                        <span class="font-medium text-stone-800 font-sans"><?= date('d M Y', strtotime($active_lease['start_date'])) ?></span>
                    </div>
                    <div>
                        <span class="block text-stone-400 text-[10px] uppercase font-bold tracking-wider">Maturity Expiry</span>
                        <span class="font-medium text-emerald-700 font-bold font-sans"><?= date('d M Y', strtotime($active_lease['end_date'])) ?></span>
                    </div>
                </div>

                <!-- OWNER CONTACT INFORMATION BOX -->
                <div class="p-4 border border-amber-200 bg-amber-50/40 text-xs rounded-none">
                    <span class="block text-amber-900 text-[10px] uppercase font-bold tracking-wider mb-2">Property Manager / Landlord Contact</span>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <span class="font-serif font-bold text-stone-900 text-sm block"><?= htmlspecialchars($active_lease['owner_name'] ?? 'Property Owner') ?></span>
                            <?php if (!empty($active_lease['owner_email'])): ?>
                                <span class="text-stone-500 text-[11px] block"><?= htmlspecialchars($active_lease['owner_email']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($raw_phone)): ?>
                            <div class="flex items-center gap-2">
                                <a href="tel:<?= htmlspecialchars($raw_phone) ?>" class="px-3 py-1.5 bg-stone-900 text-stone-100 font-sans font-semibold text-[11px] hover:bg-black transition flex items-center gap-1.5 shadow-xs">
                                    📞 Call <?= htmlspecialchars($raw_phone) ?>
                                </a>
                                <a href="viber://chat?number=<?= urlencode($viber_phone) ?>" class="px-3 py-1.5 bg-[#7360f2] text-white font-sans font-semibold text-[11px] hover:bg-[#5945d8] transition flex items-center gap-1.5 shadow-xs">
                                    💬 Viber Chat
                                </a>
                            </div>
                        <?php else: ?>
                            <span class="text-stone-400 italic text-[11px]">No contact number provided</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- INSTALLMENT DUE SCHEDULE TABLE -->
                <div class="pt-2">
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="text-[10px] font-bold uppercase tracking-widest text-stone-400">Installment Due Schedule</h5>
                        <span class="text-[10px] font-bold text-amber-800 bg-amber-50 border border-amber-200/80 px-2 py-0.5">
                            For: <?= htmlspecialchars($active_lease['property_title']) ?>
                        </span>
                    </div>

                    <div class="border border-stone-200 overflow-x-auto text-xs">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-stone-100 border-b border-stone-200 font-bold text-stone-700 text-[11px]">
                                    <th class="p-3">Period Cycle</th>
                                    <th class="p-3">Property / Unit</th>
                                    <th class="p-3">Due Target</th>
                                    <th class="p-3">Amount Due</th>
                                    <th class="p-3">Status Label</th>
                                    <th class="p-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 font-sans text-stone-600">
                                <?php if (empty($installments)): ?>
                                    <tr>
                                        <td colspan="6" class="p-4 text-center text-stone-400 italic font-serif">No computed cycles recorded for this block.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($installments as $inst): 
                                        $status = strtolower($inst['status']);
                                        $badge_style = "text-stone-400 bg-stone-50 border-stone-200";
                                        if($status === 'paid') $badge_style = "text-emerald-800 bg-emerald-50 border-emerald-200";
                                        if($status === 'partially_paid') $badge_style = "text-amber-800 bg-amber-50 border-amber-200";
                                        if($status === 'unpaid' && $inst['due_date'] < $today) $badge_style = "text-red-700 bg-red-50 border-red-200 font-bold";
                                        
                                        $unit_info = !empty($inst['floor_level']) ? "Floor " . $inst['floor_level'] : (!empty($inst['room_num']) ? "Room " . $inst['room_num'] : "");
                                    ?>
                                    <tr class="hover:bg-stone-50 transition-colors">
                                        <td class="p-3 font-medium">Month #<?= (int)$inst['installment_period'] ?></td>
                                        <td class="p-3">
                                            <span class="font-bold text-stone-800 font-serif block"><?= htmlspecialchars($inst['property_title']) ?></span>
                                            <?php if($unit_info): ?>
                                                <span class="text-[10px] text-stone-400 block"><?= htmlspecialchars($unit_info) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3 <?= ($status !== 'paid' && $inst['due_date'] < $today) ? 'text-red-700 font-bold' : '' ?>">
                                            <?= date('d M Y', strtotime($inst['due_date'])) ?>
                                            <?= ($status !== 'paid' && $inst['due_date'] < $today) ? ' <span class="text-[9px] uppercase tracking-tighter text-red-700">(Overdue)</span>' : '' ?>
                                        </td>
                                        <td class="p-3 font-bold text-stone-900"><?= number_format($inst['amount_to_pay']) ?> MMK</td>
                                        <td class="p-3">
                                            <span class="px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider <?= $badge_style ?>">
                                                <?= str_replace('_', ' ', $status) ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-right">
                                            <?php if ($status !== 'paid'): ?>
                                                <a href="renter_payment.php?contract_id=<?= $active_contract_id ?>&installment_id=<?= $inst['id'] ?>" class="px-3 py-1 bg-[#0f172a] text-[#fef3c7] text-[11px] font-serif hover:bg-slate-900 transition-all shadow-xs">Settle Bill</a>
                                            <?php else: ?>
                                                <span class="text-stone-400 text-[11px] font-serif italic">Settled ✔</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </section>

    <!-- HISTORIC TENANCY LEDGER ARCHIVE -->
    <section>
        <h3 class="text-[11px] font-bold uppercase tracking-widest text-stone-400 mb-3 border-b border-stone-200 pb-2">Historic Tenancy Ledger Archive</h3>
        <div class="bg-white border border-stone-300 rounded-none overflow-x-auto text-xs shadow-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-stone-100 border-b border-stone-200 font-bold text-stone-700 text-[11px]">
                        <th class="p-3">Property Reference</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Tenancy Timeline</th>
                        <th class="p-3">Historical Deposit</th>
                        <th class="p-3 text-right">Record State</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 text-stone-600">
                    <?php if (empty($past_leases)): ?>
                        <tr>
                            <td colspan="5" class="p-4 text-center text-stone-400 italic font-serif">No historical tracking records matched to your profile index.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($past_leases as $past): 
                            $past_is_ap = !empty($past['apartment_id']);
                        ?>
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="p-3 font-medium text-stone-800 font-serif"><?= htmlspecialchars($past['property_title']) ?></td>
                            <td class="p-3 uppercase text-[10px] font-semibold text-stone-500"><?= $past_is_ap ? 'Apartment' : 'Hostel' ?></td>
                            <td class="p-3 font-sans"><?= date('M Y', strtotime($past['start_date'])) ?> &mdash; <?= date('M Y', strtotime($past['end_date'])) ?></td>
                            <td class="p-3 font-sans"><?= number_format($past['total_deposit_amount']) ?> MMK</td>
                            <td class="p-3 text-right">
                                <span class="text-[10px] font-bold border border-stone-200 bg-stone-100 text-stone-500 px-2 py-0.5 uppercase tracking-wider">Matured</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalHub - Tenant Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fbfaf7] text-stone-900 antialiased min-h-screen flex flex-col justify-between">

    <!-- STICKY HEADER CONTAINER (EXACTLY MATCHES MAIN CONTENT CONTAINER WIDTH) -->
    <header class="sticky top-0 z-50 w-full bg-white border-b border-stone-300 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (file_exists('homepageheader.php')) { 
                include 'homepageheader.php'; 
            } else { ?>
                <div class="h-16 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 bg-[#0f172a] border border-[#b45309] flex items-center justify-center text-[#fef3c7] font-serif font-bold text-lg">
                            R
                        </div>
                        <span class="font-serif font-bold text-xl tracking-tight text-slate-900">
                            Rental<span class="italic text-amber-700">Hub</span>
                        </span>
                    </div>
                    <button onclick="toggleMobileMenu()" class="lg:hidden text-stone-700 hover:text-black p-2 text-sm font-semibold">
                        ☰ Menu
                    </button>
                </div>
            <?php } ?>
        </div>
    </header>

    <!-- Mobile Slide-out Menu -->
    <div id="mobileMenu" class="hidden fixed inset-0 top-16 bg-[#0f172a]/30 backdrop-blur-xs z-50 transition-all duration-200">
        <div class="bg-white border-b border-stone-300 shadow-xl p-6 space-y-4 max-h-[calc(100vh-4rem)] overflow-y-auto">
            <p class="text-[10px] uppercase font-bold tracking-widest text-stone-400 border-b border-stone-100 pb-2">Navigation Panel</p>
            <nav class="flex flex-col space-y-3 font-serif font-medium text-sm text-stone-800">
                <a href="renterdashboard.php" onclick="loadContent(event, 'renterdashboard.php')" class="text-amber-800 font-bold">Dashboard Overview</a>
                <a href="renterhomepage.php" onclick="loadContent(event, 'renterhomepage.php')" class="hover:text-amber-800 transition">Find Available Units</a>
                <a href="renter_contract.php" onclick="loadContent(event, 'renter_contract.php')" class="hover:text-amber-800 transition">My Contracts</a>
                <a href="renter_payment.php" onclick="loadContent(event, 'renter_payment.php')" class="hover:text-amber-800 transition">Payment Ledgers</a>
                <a href="../auth/logout.php" class="text-red-700 hover:text-red-900 transition font-sans text-xs pt-2">Sign Out Account</a>
            </nav>
        </div>
    </div>

    <!-- MAIN PAGE WRAPPER (Aligned max-w-7xl) -->
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- LEFT SIDEBAR -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Profile Card -->
            <div class="bg-white border border-stone-300 p-6 shadow-xs relative rounded-none">
                <div class="absolute top-0 left-0 right-0 h-1 bg-[#0f172a]"></div>
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 bg-stone-100 border border-stone-300 rounded-full flex items-center justify-center font-serif text-xl font-bold text-stone-700 uppercase">
                        <?= mb_substr($username_session, 0, 2) ?>
                    </div>
                    <div>
                        <h2 class="font-serif font-bold text-xl text-stone-900 leading-tight"><?= $username_session ?></h2>
                        <span class="text-[9px] uppercase font-bold tracking-wider text-amber-900 bg-amber-100/70 border border-amber-300/60 px-1.5 py-0.5 rounded-none mt-1 inline-block">
                            Verified Resident
                        </span>
                    </div>
                </div>

                <div class="border-t border-stone-200 pt-4 space-y-3 text-xs">
                    <div>
                        <span class="block text-stone-400 uppercase tracking-widest text-[9px] font-bold">Email Communication</span>
                        <span class="font-medium text-stone-800 font-sans"><?= htmlspecialchars($renter_email) ?></span>
                    </div>
                    <div>
                        <span class="block text-stone-400 uppercase tracking-widest text-[9px] font-bold">System Reference</span>
                        <span class="font-mono text-stone-600">#UID-00<?= (int)$renter_id ?></span>
                    </div>
                </div>
            </div>

            <!-- Portal Desktop Navigation -->
            <div class="bg-white border border-stone-300 p-4 shadow-xs rounded-none">
                <p class="text-[10px] uppercase font-bold tracking-widest text-stone-400 mb-3 border-b border-stone-100 pb-2">Quick Navigation</p>
                <nav class="flex flex-col space-y-1 text-xs font-serif" id="navLinks">
                    <a href="renterdashboard.php" 
                       onclick="loadContent(event, 'renterdashboard.php')" 
                       data-url="renterdashboard.php"
                       class="nav-item px-3 py-2.5 text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors">
                       📌 Overview
                    </a>
                    <a href="renter_contract.php" 
                       onclick="loadContent(event, 'renter_contract.php')" 
                       data-url="renter_contract.php"
                       class="nav-item px-3 py-2.5 text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors">
                       📄 My Contracts
                    </a>
                    <a href="renter_payment.php"
   onclick="loadContent(event, 'renter_payment.php')"
   data-url="renter_payment.php"
   class="nav-item px-3 py-2.5 text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors">
    💳 Payment Ledgers
</a>
                    
                </nav>
            </div>

            <!-- Ledger Notification Box (Matches Dark Slate Styling) -->
            <div class="bg-[#0f172a] text-[#fef3c7] p-6 border border-[#b45309]/50 shadow-xs font-serif">
                <h4 class="text-[10px] uppercase tracking-widest text-amber-400 font-bold mb-2">Automated Ledger Notification</h4>
                <p class="text-xs italic leading-relaxed text-stone-300">
                    Your statements and installment entries update automatically. For early contract notices or modifications, contact your primary property manager.
                </p>
            </div>
        </div>

        <!-- MAIN DASHBOARD CONTENT DYNAMIC CONTAINER -->
        <main id="mainContent" class="lg:col-span-2 space-y-8 transition-all duration-200">
            <?php renderDashboardBody($overdue_installments, $active_lease, $installments, $past_leases, $today, $active_contract_id); ?>
        </main>

    </div>

    <footer class="bg-white border-t border-stone-300 py-6 text-center text-xs font-serif text-stone-400 mt-12">
        &copy; 2026 RentalHub Platform. All dashboard data operations follow local leasing terms.
    </footer>

    <!-- AJAX SCRIPT -->
    <script>
    async function loadContent(event, pageUrl, pushState = true) {
        if (event) event.preventDefault();

        const mainContainer = document.getElementById('mainContent');
        if (!mainContainer) return;
        
        mainContainer.innerHTML = `
            <div class="bg-white border border-stone-300 p-12 text-center shadow-xs">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-amber-700 mb-4"></div>
                <p class="text-xs font-serif text-stone-500 italic">Fetching updated records, please wait...</p>
            </div>
        `;

        try {
            const response = await fetch(pageUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const htmlText = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');
            
            const fetchedContent = doc.getElementById('mainContent') || doc.querySelector('main') || doc.body;

            mainContainer.innerHTML = fetchedContent.innerHTML;

            // Update Active UI Navigation State
            updateActiveNavUI(pageUrl);

            // Close Mobile Menu if open
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                toggleMobileMenu();
            }

            if (pushState) {
                window.history.pushState({ path: pageUrl }, '', pageUrl);
            }

        } catch (error) {
            console.error('AJAX Load Error:', error);
            mainContainer.innerHTML = `
                <div class="bg-red-50 border-l-4 border-red-700 p-6 text-xs font-sans text-red-900">
                    <p class="font-bold mb-1">Error Loading Content</p>
                    <p>Unable to fetch requested view. Please check target URL or server connection.</p>
                </div>
            `;
        }
    }

    function updateActiveNavUI(pageUrl) {
        document.querySelectorAll('#navLinks .nav-item').forEach(link => {
            const linkUrl = link.getAttribute('data-url') || link.getAttribute('href');
            if (linkUrl === pageUrl) {
                link.className = 'nav-item px-3 py-2.5 bg-amber-50/80 text-amber-900 font-bold border-l-2 border-amber-700';
            } else {
                link.className = 'nav-item px-3 py-2.5 text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors';
            }
        });
    }

    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const mainWrapper = document.getElementById('mainContent');
        
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            if(mainWrapper) mainWrapper.classList.add('blur-xs', 'pointer-events-none');
            document.body.classList.add('overflow-hidden');
        } else {
            menu.classList.add('hidden');
            if(mainWrapper) mainWrapper.classList.remove('blur-xs', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');
        }
    }

    window.addEventListener('popstate', (event) => {
        if (event.state && event.state.path) {
            loadContent(null, event.state.path, false);
        }
    });
    </script>
</body>
</html>