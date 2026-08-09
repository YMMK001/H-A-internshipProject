<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fallback user ID if not set in session (ID 16 for Yi)
$renter_id = $_SESSION['user_id'] ?? 16; 

$active_contract_id = null;
$rentals = [];

// Profile Information Variables
$display_name = 'Yi';
$email = '';

// ==========================================
// PAGINATION SETTINGS
// ==========================================
$items_per_page = 3; // တစ်မျက်နှာမှာ ပြသချင်သည့် အရေအတွက်
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;
$total_pages = 1;
$total_records = 0;

try {
    $host        = 'localhost';
    $db_name     = 'intern_test'; 
    $db_user     = 'root';
    $db_pass     = ''; 

    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 1. Fetch user profile data from 'users' table
    $user_stmt = $db->prepare("SELECT name, email FROM users WHERE id = :user_id LIMIT 1");
    $user_stmt->execute([':user_id' => $renter_id]);
    $user_data = $user_stmt->fetch();

    if ($user_data) {
        $display_name = !empty($user_data['name']) ? $user_data['name'] : ($_SESSION['username'] ?? 'Yi');
        $email        = $user_data['email'] ?? ($_SESSION['email'] ?? 'yi@gmail.com');
    } else {
        $display_name = $_SESSION['username'] ?? 'Yi';
        $email        = $_SESSION['email'] ?? 'yi@gmail.com';
    }

    // 2. Fetch active contract ID
    $stmt = $db->prepare("SELECT id FROM contracts WHERE user_id = :user_id LIMIT 1");
    $stmt->execute([':user_id' => $renter_id]);
    $contract = $stmt->fetch();

    if ($contract) {
        $active_contract_id = $contract['id'];
    }

    // 3. Count total active rentals for pagination (Expired ဖြစ်ပြီး ပေးရန်မကျန်တော့ပါက ဖျောက်မည်)
    $count_query = "
        SELECT COUNT(*) 
        FROM contracts c 
        WHERE c.user_id = :renter_id 
          AND (
            c.end_date >= CURDATE() 
            OR EXISTS (
                SELECT 1 FROM installments i 
                WHERE i.contract_id = c.id AND i.status != 'paid'
            )
          )
    ";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute([':renter_id' => $renter_id]);
    $total_records = (int)$count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $items_per_page);

    // 4. Fetch paginated rentals for this user (Expired ဖြစ်ပြီး ပေးရန်မကျန်တော့ပါက ဖျောက်မည်)
    $query = "
        SELECT 
            c.id AS contract_id, c.start_date, c.end_date, c.total_deposit_amount,
            a.id AS apartment_id, a.floor_level, a.apartment_price,
            h.id AS hostel_room_id, h.room_num, h.room_type, h.monthly_price,
            rh.title AS house_title, rh.city, rh.township, rh.full_address, rh.rentable_type
        FROM contracts c
        LEFT JOIN apartments a ON c.apartment_id = a.id
        LEFT JOIN hostel_rooms h ON c.hostel_room_id = h.id
        LEFT JOIN rental_houses rh ON (a.rental_house_id = rh.id OR h.rental_house_id = rh.id)
        WHERE c.user_id = :renter_id
          AND (
            c.end_date >= CURDATE() 
            OR EXISTS (
                SELECT 1 FROM installments i 
                WHERE i.contract_id = c.id AND i.status != 'paid'
            )
          )
        ORDER BY c.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt_rentals = $db->prepare($query);
    $stmt_rentals->bindValue(':renter_id', $renter_id, PDO::PARAM_INT);
    $stmt_rentals->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt_rentals->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt_rentals->execute();
    $rentals = $stmt_rentals->fetchAll();

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// Check if request is an AJAX call
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    renderMainContent($rentals, $active_contract_id, $current_page, $total_pages, $total_records);
    exit;
}

function renderMainContent($rentals, $active_contract_id, $current_page = 1, $total_pages = 1, $total_records = 0) {
    $script_name = basename($_SERVER['PHP_SELF']);
?>
    <!-- ACTIVE LEASE FRAMEWORK SECTION -->
    <section class="space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-widest text-stone-400">Active Lease Framework</h2>
            <?php if ($total_records > 0): ?>
                <span class="text-[10px] text-stone-400 font-mono">Total Units: <?= $total_records ?></span>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($rentals)): ?>
            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($rentals as $row): ?>
                    <?php 
                        $is_apartment = !empty($row['apartment_id']);
                        $type_badge = $is_apartment ? 'Apartment' : 'Hostel Room';
                        $price = $is_apartment ? ($row['apartment_price'] ?? 0) : ($row['monthly_price'] ?? 0);
                        $unit_detail = $is_apartment ? "Floor: " . ($row['floor_level'] ?? 'N/A') : "Room No: " . ($row['room_num'] ?? 'N/A');
                    ?>
                    <div class="bg-white border border-stone-300 rounded-none p-6 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="space-y-1">
                            <span class="inline-block text-[9px] font-bold tracking-widest uppercase border px-2 py-0.5 rounded-none <?= $is_apartment ? 'border-amber-300 text-amber-900 bg-amber-50' : 'border-stone-300 text-stone-700 bg-stone-100' ?>">
                                <?= $type_badge ?>
                            </span>
                            <h3 class="font-serif font-bold text-stone-900 text-base uppercase tracking-tight"><?= htmlspecialchars($row['house_title'] ?? 'Untitled Property') ?></h3>
                            <p class="text-xs text-stone-500">📍 <?= htmlspecialchars(($row['township'] ?? '') . ', ' . ($row['city'] ?? '')) ?> (<?= htmlspecialchars($unit_detail) ?>)</p>
                            <p class="text-xs text-stone-400 font-mono">Term: <?= date('d M Y', strtotime($row['start_date'])) ?> — <?= date('d M Y', strtotime($row['end_date'])) ?></p>
                        </div>
                        <div class="text-right flex flex-col items-start md:items-end w-full md:w-auto">
                            <span class="text-base font-bold text-stone-900"><?= number_format((float)$price) ?> <span class="text-xs font-normal text-stone-400">MMK/mo</span></span>
                            <a href="installment_list.php?contract_id=<?= $row['contract_id'] ?>&from=profile" onclick="loadContent(event, 'installment_list.php?contract_id=<?= $row['contract_id'] ?>&from=profile')" class="mt-3 inline-block bg-[#0f172a] hover:bg-slate-800 text-[#fef3c7] font-bold text-[10px] uppercase tracking-widest px-4 py-2 rounded-none transition">
                                View Installments & Ledgers
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- PAGINATION CONTROLS -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-6 flex justify-between items-center bg-white border border-stone-300 p-3 rounded-none text-xs">
                    <div>
                        <?php if ($current_page > 1): ?>
                            <a href="<?= $script_name ?>?page=<?= $current_page - 1 ?>" 
                               onclick="loadContent(event, '<?= $script_name ?>?page=<?= $current_page - 1 ?>')" 
                               class="px-3 py-1.5 border border-stone-300 bg-stone-50 text-stone-700 hover:bg-stone-100 font-serif transition">
                                &larr; Previous
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-1.5 border border-stone-200 bg-stone-100 text-stone-400 font-serif cursor-not-allowed">
                                &larr; Previous
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="font-mono text-stone-500 text-[11px]">
                        Page <span class="font-bold text-stone-900"><?= $current_page ?></span> of <span class="font-bold text-stone-900"><?= $total_pages ?></span>
                    </div>

                    <div>
                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?= $script_name ?>?page=<?= $current_page + 1 ?>" 
                               onclick="loadContent(event, '<?= $script_name ?>?page=<?= $current_page + 1 ?>')" 
                               class="px-3 py-1.5 border border-stone-300 bg-stone-50 text-stone-700 hover:bg-stone-100 font-serif transition">
                                Next &rarr;
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-1.5 border border-stone-200 bg-stone-100 text-stone-400 font-serif cursor-not-allowed">
                                Next &rarr;
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="bg-white border border-stone-300 p-12 text-center rounded-none shadow-xs">
                <p class="text-xs font-serif italic text-stone-500 mb-4">No current active property or lease framework found linked to this portal account.</p>
                <a href="renterhomepage.php" class="text-xs text-amber-800 font-serif underline hover:text-amber-900">Find Available Units &rarr;</a>
            </div>
        <?php endif; ?>
    </section>
<?php
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalHub - Renter Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Noto+Sans+Myanmar:wght@300;400;500;700&display=swap');
        .font-serif { font-family: 'Playfair Display', Georgia, serif; }
        .font-classic { font-family: 'Noto Sans Myanmar', sans-serif; }
    </style>
</head>
<body class="bg-[#fbfaf7] text-stone-900 antialiased font-classic min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 w-full bg-white border-b border-stone-300 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (file_exists('homepageheader.php')) { include 'homepageheader.php'; } else { ?>
                <div class="h-16 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 bg-[#0f172a] border border-[#b45309] flex items-center justify-center text-[#fef3c7] font-serif font-bold text-lg">R</div>
                        <span class="font-serif font-bold text-xl tracking-tight text-slate-900">Rental<span class="italic text-amber-700">Hub</span></span>
                    </div>
                </div>
            <?php } ?>
        </div>
    </header>

    <!-- Main Container -->
    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT SIDEBAR -->
            <aside class="lg:col-span-4 space-y-6">
                <!-- User Profile Card -->
                <div class="bg-white p-6 rounded-none border border-stone-300 relative shadow-xs">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-[#0f172a]"></div>
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-14 h-14 bg-stone-100 rounded-full flex items-center justify-center text-stone-700 font-serif font-bold text-xl uppercase border border-stone-300">
                            <?= htmlspecialchars(mb_strtoupper(mb_substr($display_name, 0, 2))) ?>
                        </div>
                        <div>
                            <h2 class="text-xl font-serif font-bold text-stone-900 leading-tight">
                                <?= htmlspecialchars($display_name) ?>
                            </h2>
                            <span class="inline-block bg-amber-100/70 text-amber-900 text-[9px] font-bold tracking-wider uppercase px-2 py-0.5 rounded-none border border-amber-300/60 mt-1">Verified Resident</span>
                        </div>
                    </div>
                    
                    <div class="space-y-3 border-t border-stone-200 pt-4 text-xs mt-5">
                        <div>
                            <p class="uppercase text-[9px] font-bold text-stone-400 tracking-widest">EMAIL COMMUNICATION</p>
                            <p class="font-bold text-stone-800 mt-0.5 font-sans"><?= htmlspecialchars($email) ?></p>
                        </div>
                        <div>
                            <p class="uppercase text-[9px] font-bold text-stone-400 tracking-widest">SYSTEM REFERENCE</p>
                            <p class="font-mono text-stone-600 mt-0.5">#UID-<?= str_pad((string)$renter_id, 4, '0', STR_PAD_LEFT) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Sidebar -->
                <div class="bg-white rounded-none border border-stone-300 shadow-xs p-4">
                    <p class="text-[10px] uppercase font-bold tracking-widest text-stone-400 mb-3 border-b border-stone-100 pb-2">Quick Navigation</p>
                    <nav class="flex flex-col space-y-1 text-xs font-serif" id="navLinks">
                        <a href="renterdashboard.php" 
                           onclick="loadContent(event, 'renterdashboard.php')" 
                           data-url="renterdashboard.php"
                           class="nav-item px-3 py-2.5 text-amber-900 bg-amber-50/80 border-l-2 border-amber-700 font-bold transition">
                            📌 Overview
                        </a>
                        <a href="renter_contract.php" 
                           onclick="loadContent(event, 'renter_contract.php')" 
                           data-url="renter_contract.php"
                           class="nav-item px-3 py-2.5 text-stone-700 hover:bg-stone-50 transition rounded-none">
                            📜 My Contracts
                        </a>
                        <a href="installment_list.php<?= $active_contract_id ? '?contract_id='.$active_contract_id.'&from=profile' : '' ?>" 
                           onclick="loadContent(event, 'installment_list.php<?= $active_contract_id ? '?contract_id='.$active_contract_id.'&from=profile' : '' ?>')" 
                           data-url="installment_list.php"
                           class="nav-item px-3 py-2.5 text-stone-700 hover:bg-stone-50 transition rounded-none">
                            💳 Payment Ledgers
                        </a>
                    </nav>
                </div>

                <!-- Ledger Notification Card -->
                <div class="bg-[#0f172a] text-[#fef3c7] p-6 rounded-none border border-[#b45309]/50 shadow-xs space-y-2 font-serif">
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-amber-400">AUTOMATED LEDGER NOTIFICATION</h3>
                    <p class="text-xs text-stone-300 leading-relaxed italic">
                        Your statements and installment entries update automatically. For early contract notices or modifications, contact your primary property manager.
                    </p>
                </div>
            </aside>

            <!-- RIGHT DYNAMIC CONTENT AREA -->
            <main id="dashboardMainContent" class="lg:col-span-8 space-y-6">
                <?php renderMainContent($rentals, $active_contract_id, $current_page, $total_pages, $total_records); ?>
            </main>

        </div>
    </div>

    <footer class="bg-white border-t border-stone-300 py-6 text-center text-xs font-serif text-stone-400 mt-12">
        &copy; 2026 RentalHub Platform. All rights reserved.
    </footer>

    <!-- AJAX Script -->
    <script>
    async function loadContent(event, pageUrl, pushState = true) {
        if (event) event.preventDefault();

        const mainContainer = document.getElementById('dashboardMainContent');
        if (!mainContainer) return;
        
        mainContainer.innerHTML = `
            <div class="bg-white border border-stone-300 p-12 text-center rounded-none shadow-xs">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-amber-700 mb-4"></div>
                <p class="text-xs font-serif text-stone-500 italic">Loading content, please wait...</p>
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
            
            const fetchedContent = doc.getElementById('dashboardMainContent') || doc.querySelector('main') || doc.body;

            mainContainer.innerHTML = fetchedContent.innerHTML;

            updateActiveNavUI(pageUrl);

            if (pushState) {
                window.history.pushState({ path: pageUrl }, '', pageUrl);
            }

        } catch (error) {
            console.error('AJAX Load Error:', error);
            mainContainer.innerHTML = `
                <div class="bg-red-50 border-l-4 border-red-600 p-6 rounded-none text-xs text-red-800">
                    <p class="font-bold mb-1">Error Loading Content</p>
                    <p>Unable to fetch records. Please check database connection or file URL.</p>
                </div>
            `;
        }
    }

    function updateActiveNavUI(pageUrl) {
        const cleanUrl = pageUrl.split('?')[0];
        document.querySelectorAll('#navLinks .nav-item').forEach(link => {
            const linkUrl = (link.getAttribute('data-url') || link.getAttribute('href')).split('?')[0];
            if (cleanUrl.includes(linkUrl)) {
                link.className = 'nav-item px-3 py-2.5 text-amber-900 bg-amber-50/80 border-l-2 border-amber-700 font-bold transition';
            } else {
                link.className = 'nav-item px-3 py-2.5 text-stone-700 hover:bg-stone-50 transition rounded-none';
            }
        });
    }

    window.addEventListener('popstate', (event) => {
        if (event.state && event.state.path) {
            loadContent(null, event.state.path, false);
        }
    });
    </script>
</body>
</html>