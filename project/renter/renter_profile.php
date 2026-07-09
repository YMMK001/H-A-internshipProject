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
$host         = 'localhost';
$db_name     = 'intern_test'; 
$username_db = 'root';              
$password_db = ''; 

$active_contract_id = null;
$contracts = [];
$active_lease = null;
$past_leases  = [];
$installments = [];
$today        = date('Y-m-d');

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- COMPREHENSIVE CONTRACT AND LEASED UNIT DETAILS ---
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
            hr.monthly_price AS hr_price
        FROM contracts c
        LEFT JOIN apartments ap ON c.apartment_id = ap.id
        LEFT JOIN hostel_rooms hr ON c.hostel_room_id = hr.id
        LEFT JOIN rental_houses rh ON (ap.rental_house_id = rh.id OR hr.rental_house_id = rh.id)
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

    // --- FETCH UPCOMING PAYMENTS FOR ACTIVE CONTRACT ---
    if ($active_contract_id) {
        $inst_query = "
            SELECT id, installment_period, amount_to_pay, due_date, status 
            FROM installments 
            WHERE contract_id = :contract_id 
            ORDER BY due_date ASC
        ";
        $stmt_inst = $db->prepare($inst_query);
        $stmt_inst->execute([':contract_id' => $active_contract_id]);
        $installments = $stmt_inst->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalHub - Tenant Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
      @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans:wght@300;400;500;600;700&display=swap');
      .font-sans-classic { font-family: 'Noto Sans', sans-serif; }
      .font-serif-classic { font-family: 'Playfair Display', serif; }
  </style>
</head>
<body class="bg-[#fbfaf7] text-stone-900 antialiased font-sans-classic min-h-screen flex flex-col">

  <?php if (file_exists('renterheader.php')) { include 'renterheader.php'; } else { ?>
      <div class="w-full bg-[#1b1816] text-white px-6 h-16 flex items-center justify-between shadow-sm">
          <div class="text-lg font-serif-classic text-amber-500 font-bold tracking-wider">Rental<span class="text-white">Hub</span></div>
          <button onclick="toggleMobileMenu()" class="sm:hidden text-stone-300 hover:text-white p-2 text-sm font-medium">
              ☰ Menu
          </button>
      </div>
  <?php } ?>

  <div id="mobileMenu" class="hidden fixed inset-0 top-16 bg-[#1b1816]/40 backdrop-blur-md z-50 transition-all duration-200">
      <div class="bg-white border-b border-stone-200 shadow-xl p-6 space-y-4 max-h-[calc(100vh-4rem)] overflow-y-auto">
          <p class="text-[10px] uppercase font-bold tracking-widest text-stone-400 border-b border-stone-100 pb-2">Navigation Panel</p>
          <nav class="flex flex-col space-y-4 font-serif-classic font-medium text-sm text-stone-800">
              <a href="renterhomepage.php" class="hover:text-amber-800 transition">Portal Homepage</a>
              <a href="renterhomepage.php" class="hover:text-amber-800 transition">Find Available Units</a>
              <a href="renterhomepage.php" class="text-red-700 hover:text-red-900 transition font-sans text-xs pt-2">Sign Out Account</a>
          </nav>
      </div>
  </div>

  <main id="mainContent" class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-3 gap-8 transition duration-200">
    
    <div class="space-y-6 lg:col-span-1">
        <div class="bg-white border border-stone-200 p-6 shadow-sm relative">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-700"></div>
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-stone-100 border border-stone-300 rounded-full flex items-center justify-center font-serif-classic text-xl font-bold text-stone-600 uppercase">
                    <?= substr($username_session, 0, 2) ?>
                </div>
                <div>
                    <h2 class="font-serif-classic font-bold text-lg text-stone-900 leading-tight"><?= $username_session ?></h2>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded-sm">Verified Resident</span>
                </div>
            </div>

            <div class="border-t border-stone-100 pt-4 space-y-3 text-xs">
                <div>
                    <span class="block text-stone-400 uppercase tracking-widest text-[9px] font-bold">Email Communication</span>
                    <span class="font-medium text-stone-800 font-sans"><?= htmlspecialchars($renter_email) ?></span>
                </div>
                <div>
                    <span class="block text-stone-400 uppercase tracking-widest text-[9px] font-bold">System reference</span>
                    <span class="font-mono text-stone-600">#UID-00<?= (int)$renter_id ?></span>
                </div>
            </div>
        </div>

        <div class="bg-blue-900 text-amber-50 p-6 border border-amber-800 shadow-sm font-serif-classic">
            <h4 class="text-xs uppercase tracking-widest text-amber-300 font-bold mb-2">Automated Ledger Notification</h4>
            <p class="text-xs italic leading-relaxed text-stone-200">
                Your statements and installment entries update automatically. For early contract notices or modifications, contact your primary property manager.
            </p>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-8">
        
        <section>
            <h3 class="text-xs font-bold uppercase tracking-widest text-stone-400 mb-4 border-b border-stone-200 pb-2">Active Lease Framework</h3>
            
            <?php if (!$active_lease): ?>
                <div class="bg-white border border-stone-200 p-8 text-center rounded-sm">
                    <p class="text-sm font-serif-classic italic text-stone-500">No current active property or lease framework found linked to this portal account.</p>
                    <a href="renterhomepage.php" class="inline-block mt-4 text-xs font-serif-classic text-blue-900 font-bold border-b border-blue-900 pb-0.5 hover:text-amber-800 hover:border-amber-800 transition-colors">Find Available Units &rarr;</a>
                </div>
            <?php else: 
                $is_ap = !empty($active_lease['apartment_id']);
                $monthly_rent = $is_ap ? $active_lease['ap_price'] : $active_lease['hr_price'];
                $specs = $is_ap ? "Floor level: " . $active_lease['floor_level'] : "Room Num: " . $active_lease['room_num'] . " (" . $active_lease['room_type'] . ")";
            ?>
                <div class="bg-white border border-stone-200 shadow-sm p-6 space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                        <div>
                            <span class="text-[9px] tracking-wider font-bold border px-1.5 py-0.5 rounded-sm <?= $is_ap ? 'text-blue-900 border-blue-200 bg-blue-50' : 'text-amber-900 border-amber-200 bg-amber-50' ?> uppercase">
                                <?= $is_ap ? 'Apartment Unit' : 'Hostel Space' ?>
                            </span>
                            <h4 class="text-xl font-serif-classic font-normal text-stone-900 mt-2"><?= htmlspecialchars($active_lease['property_title']) ?></h4>
                            <p class="text-xs text-stone-400 mt-1">📍 <?= htmlspecialchars($active_lease['full_address']) ?>, <?= htmlspecialchars($active_lease['township']) ?>, <?= htmlspecialchars($active_lease['city']) ?></p>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-2xl font-bold text-stone-900 font-sans"><?= number_format($monthly_rent) ?></span>
                            <span class="text-[10px] text-stone-400 font-bold block uppercase tracking-wide">MMK / Month</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-stone-50 p-4 border border-stone-200 rounded-sm text-xs">
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

                    <div class="pt-2">
                        <h5 class="text-[11px] font-bold uppercase tracking-widest text-stone-500 mb-3">Installment Due Schedule</h5>
                        <div class="border border-stone-200 rounded-sm overflow-hidden text-xs">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-stone-100 border-b border-stone-200 font-bold text-stone-700">
                                        <th class="p-3">Period Cycle</th>
                                        <th class="p-3">Due Target</th>
                                        <th class="p-3">Amount Due</th>
                                        <th class="p-3">Status Label</th>
                                        <th class="p-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-200 font-sans text-stone-600">
                                    <?php if (empty($installments)): ?>
                                        <tr>
                                            <td colspan="5" class="p-3 text-center text-stone-400 italic font-serif-classic">No computed cycles recorded for this block.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($installments as $inst): 
                                            $status = strtolower($inst['status']);
                                            $badge_style = "text-stone-400 bg-stone-50 border-stone-200";
                                            if($status === 'paid') $badge_style = "text-emerald-700 bg-emerald-50 border-emerald-200";
                                            if($status === 'partially_paid') $badge_style = "text-amber-700 bg-amber-50 border-amber-200";
                                            if($status === 'unpaid' && $inst['due_date'] < $today) $badge_style = "text-red-700 bg-red-50 border-red-200 font-bold animate-pulse";
                                        ?>
                                        <tr class="hover:bg-stone-50/50 transition-colors">
                                            <td class="p-3 font-medium">Month #<?= (int)$inst['installment_period'] ?></td>
                                            <td class="p-3 <?= ($status !== 'paid' && $inst['due_date'] < $today) ? 'text-red-600 font-bold' : '' ?>">
                                                <?= date('d M Y', strtotime($inst['due_date'])) ?>
                                                <?= ($status !== 'paid' && $inst['due_date'] < $today) ? ' <span class="text-[9px] uppercase tracking-tighter text-red-700">(Overdue)</span>' : '' ?>
                                            </td>
                                            <td class="p-3 font-bold text-stone-900"><?= number_format($inst['amount_to_pay']) ?> MMK</td>
                                            <td class="p-3">
                                                <span class="px-2 py-0.5 border text-[10px] font-medium rounded-sm uppercase tracking-wide <?= $badge_style ?>">
                                                    <?= str_replace('_', ' ', $status) ?>
                                                </span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <?php if ($status !== 'paid'): ?>
                                                    <a href="renter_payment.php?contract_id=<?= $active_contract_id ?>&installment_id=<?= $inst['id'] ?>" class="px-2.5 py-1 bg-blue-900 text-amber-50 text-[10px] font-serif-classic hover:bg-blue-950 transition-all rounded-sm shadow-sm">Settle Bill</a>
                                                <?php else: ?>
                                                    <span class="text-stone-400 text-[11px] italic">Settled ✔</span>
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

        <section>
            <h3 class="text-xs font-bold uppercase tracking-widest text-stone-400 mb-4 border-b border-stone-200 pb-2">Historic Tenancy Ledger Archive</h3>
            <div class="bg-white border border-stone-200 rounded-sm overflow-hidden text-xs">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 font-bold text-stone-700">
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
                                <td colspan="5" class="p-4 text-center text-stone-400 italic font-serif-classic">No historical tracking records matched to your profile index.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($past_leases as $past): 
                                $past_is_ap = !empty($past['apartment_id']);
                            ?>
                            <tr class="hover:bg-stone-50/40 transition-colors">
                                <td class="p-3 font-medium text-stone-800"><?= htmlspecialchars($past['property_title']) ?></td>
                                <td class="p-3 uppercase text-[10px] font-semibold text-stone-500"><?= $past_is_ap ? 'Apartment' : 'Hostel' ?></td>
                                <td class="p-3 font-sans"><?= date('M Y', strtotime($past['start_date'])) ?> &mdash; <?= date('M Y', strtotime($past['end_date'])) ?></td>
                                <td class="p-3 font-sans"><?= number_format($past['total_deposit_amount']) ?> MMK</td>
                                <td class="p-3 text-right">
                                    <span class="text-[10px] font-bold border border-stone-200 bg-stone-100 text-stone-500 px-1.5 py-0.5 rounded-sm uppercase tracking-wider">Matured</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
  </main>

  <footer class="bg-white border-t border-stone-200 py-6 mt-12 text-center text-xs font-serif-classic text-stone-400">
      &copy; 2026 RentalHub Platform. All dashboard data operations follow local leasing terms.
  </footer>

  <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const mainContent = document.getElementById('mainContent');
        
        if (menu.classList.contains('hidden')) {
            // Open Menu
            menu.classList.remove('hidden');
            // Blur underlying page body content
            mainContent.classList.add('blur-sm', 'pointer-events-none');
            // Prevent main page view scroll
            document.body.classList.add('overflow-hidden');
        } else {
            // Close Menu
            menu.classList.add('hidden');
            // Remove blur effect and re-enable actions
            mainContent.classList.remove('blur-sm', 'pointer-events-none');
            // Allow main page view scroll again
            document.body.classList.remove('overflow-hidden');
        }
    }
  </script>
</body>
</html>