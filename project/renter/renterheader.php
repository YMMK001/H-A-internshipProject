<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Renter');

// --- CONTRACT ID ရှာဖွေရန် ထပ်တိုးကုဒ် ---
$active_contract_id = null;

if (isset($_SESSION['user_id'])) { 
    try {
        $host     = 'localhost';
        $db_name  = 'intern_test'; 
        $username_db = 'root';              
        $password_db = ''; 

        $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username_db, $password_db);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT id FROM contracts WHERE user_id = :user_id AND status = 'active' LIMIT 1");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $contract = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($contract) {
            $active_contract_id = $contract['id'];
        }
    } catch (PDOException $e) {
        // Connection error logged quietly
    }
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
      @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght=0,400..900;1,400..900&family=Noto+Sans:wght@300;400;500;600;700&display=swap');
      .font-sans-classic { font-family: 'Noto Sans', sans-serif; }
      .font-serif-classic { font-family: 'Playfair Display', serif; }
  </style>
</head>
<body class="bg-[#fbfaf7] text-stone-900 antialiased font-sans-classic min-h-screen flex flex-col ">

  <!-- Classic Premium Dark Navigation Bar (Matching image_ce8162.png) -->
  <nav class="bg-[#1c1a10] border-b border-stone-800 sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        
        <!-- Left Section: Identity Framework & Core Menu -->
        <div class="flex items-center gap-10">
          <!-- Logo Block -->
          <a href="renter_profile.php" class="flex items-center gap-3 shrink-0">
            <div class="h-9 w-9 bg-[#1e3a8a] border border-[#d97706] flex items-center justify-center text-amber-100 font-serif-classic font-bold text-xl shadow-sm">R</div>
            <span class="text-xl font-serif-classic font-bold tracking-tight text-white">Rental<span class="text-[#eab308] italic font-normal">Hub</span></span>
          </a>

          <!-- Desktop Navigation Links with Precise Bottom-Border Highlight Indicator -->
          <div class="hidden md:flex items-center space-x-2 font-serif-classic text-[14px]">
          <a href="renter_profile.php" class="px-3 py-2 text-white hover:border-b-2 hover:border-[#d97706] font-medium tracking-wide">
              Profile
            </a>  
          <a href="renterdashboard.php" class="px-3 py-2 text-white hover:border-b-2 hover:border-[#d97706] font-medium tracking-wide">
              Overview
            </a>
            <a href="renter_contract.php" class="px-3 py-2 text-stone-300 hover:border-b-2 hover:border-[#d97706] hover:text-white transition-all tracking-wide">
              Contracts
            </a>
            <?php if ($active_contract_id): ?>
              <a href="renter_payment.php?contract_id=<?= $active_contract_id ?>" class="px-3 py-2 text-stone-300 hover:border-b-2 hover:border-[#d97706] hover:text-white transition-all tracking-wide">
                Payment Ledgers
              </a>
            <?php else: ?>
              <a href="#" onclick="alert('No active lease framework detected to review accounts.'); return false;" class="px-3 py-2 text-stone-500 hover:border-b-2 hover:border-[#d97706] cursor-not-allowed italic tracking-wide">
                Payment Ledgers
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Right Section: Actions, Circular Profile & Sign Out Controls -->
        <div class="hidden md:flex items-center gap-5">
          

          <!-- Architectural Split Line -->
          <div class="h-6 w-[1px] bg-stone-700/60 mx-1"></div>

          <!-- Refined Circular Profile Avatar Block -->
          <div class="flex items-center gap-3">
            <div class="min-w-0 text-right">
              <p class="text-[9px] uppercase tracking-widest text-stone-400 font-bold font-sans-classic">Resident</p>
              <p class="text-sm font-serif-classic font-bold text-stone-200 truncate max-w-[110px]"><?= $username; ?></p>
            </div>
            <!-- Circle Frame Update -->
            <div class="h-10 w-10 rounded-full bg-stone-900 border-2 border-[#d97706] text-[#eab308] flex items-center justify-center font-serif-classic font-bold text-sm shadow-md shrink-0">
                <?php 
                    $initials = !empty($username) ? mb_substr($username, 0, 2) : 'U';
                    echo htmlspecialchars(mb_strtoupper($initials)); 
                ?>
            </div>
          </div>

          <!-- Clean Luxury Sign Out Trigger Button -->
          <a href="../auth/logout.php" class="ml-2 px-3 py-1.5 border border-stone-600 hover:border-red-400 text-stone-400 hover:text-red-400 text-[11px] uppercase tracking-wider font-sans-classic font-medium rounded-sm transition-all bg-stone-900/40">
            Sign Out
          </a>
        </div>

        <!-- Mobile Menu Hamburger Button -->
        <div class="flex md:hidden items-center">
          <button onclick="toggleMobileMenu()" class="text-stone-300 hover:text-white focus:outline-none p-2 border border-stone-700 rounded-sm bg-stone-900/50">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
        </div>

      </div>
    </div>

    <!-- Dropdown Mobile Context Menu -->
    <div id="mobileMenu" class="hidden md:hidden bg-[#242114] border-t border-stone-800 font-serif-classic text-sm">
      <div class="px-2 pt-2 pb-4 space-y-1">
        <a href="renterdashboard.php" class="block px-3 py-2.5 text-white bg-stone-900/40 font-medium">History</a>
       
        <a href="renter_contract.php" class="block px-3 py-2.5 text-stone-200 hover:bg-stone-900/40">Contracts</a>
        <?php if ($active_contract_id): ?>
          <a href="renter_payment.php?contract_id=<?= $active_contract_id ?>" class="block px-3 py-2.5 text-stone-200 hover:bg-stone-900/40">Payment Ledgers</a>
        <?php else: ?>
          <a href="#" onclick="alert('No active lease framework detected.'); return false;" class="block px-3 py-2.5 text-stone-500 italic cursor-not-allowed">Payment Ledgers</a>
        <?php endif; ?>
        
        <!-- Mobile Profile & Sign Out Summary Section -->
        <div class="pt-4 mt-2 border-t border-stone-800 flex items-center justify-between px-3">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-stone-900 border-2 border-[#d97706] text-[#eab308] flex items-center justify-center font-serif-classic font-bold text-xs">
                <?= htmlspecialchars(mb_strtoupper($initials)); ?>
            </div>
            <div>
              <p class="text-[9px] uppercase tracking-wider text-stone-400 font-sans-classic">Resident</p>
              <p class="text-sm font-bold text-stone-200"><?= $username; ?></p>
            </div>
          </div>
          <div class="flex gap-2">
            <a href="rentalhouselist.php" class="px-2.5 py-1.5 text-xs font-sans-classic uppercase tracking-wider text-amber-100 bg-[#1e3a8a] border border-[#d97706]">Search</a>
            <a href="../auth/logout.php" class="px-2.5 py-1.5 text-xs font-sans-classic uppercase tracking-wider text-stone-300 border border-stone-600 bg-stone-900/40">Out</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main View Area Frame Context -->
  
  <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
    }
  </script>
</body>
</html>