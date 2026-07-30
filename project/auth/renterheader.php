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

    <option data-region="2/" value="dmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dmn') ? 'selected' : ''; ?>>DAMANA (Demawso)</option>
                        <option data-region="2/" value="lkn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lkn') ? 'selected' : ''; ?>>LAKANA (Loikaw)</option>
                        <option data-region="2/" value="msn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'msn') ? 'selected' : ''; ?>>MASANA (Mese)</option>
                        <option data-region="2/" value="ytn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ytn') ? 'selected' : ''; ?>>YATHANA (Bawlakhe)</option>

                        <option data-region="3/" value="ban" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ban') ? 'selected' : ''; ?>>BAAHNA (Hpa-an)</option>
                        <option data-region="3/" value="kky" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kky') ? 'selected' : ''; ?>>KAKAYA (Kawkareik)</option>
                        <option data-region="3/" value="mwt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mwt') ? 'selected' : ''; ?>>MAWATHTA (Myawaddy)</option>
                        <option data-region="3/" value="ppn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ppn') ? 'selected' : ''; ?>>PAPANA (Hpapun)</option>
                        <option data-region="3/" value="ttn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ttn') ? 'selected' : ''; ?>>THATANA (Thaton)</option>

                        <option data-region="4/" value="hkh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hkh') ? 'selected' : ''; ?>>HAKHANA (Hakha)</option>
                        <option data-region="4/" value="hpn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hpn') ? 'selected' : ''; ?>>HPANA (Htantlang)</option>
                        <option data-region="4/" value="mtn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mtn') ? 'selected' : ''; ?>>MATANA (Matupi)</option>
                        <option data-region="4/" value="pln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pln') ? 'selected' : ''; ?>>PALANA (Paletwa)</option>
                        <option data-region="4/" value="fnn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'fnn') ? 'selected' : ''; ?>>PHANANA (Falam)</option>

                        <option data-region="5/" value="atn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'atn') ? 'selected' : ''; ?>>AHTANA (Ayadaw)</option>
                        <option data-region="5/" value="kln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kln') ? 'selected' : ''; ?>>KALANA (Kalay)</option>
                        <option data-region="5/" value="ktn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ktn') ? 'selected' : ''; ?>>KATHANA (Katha)</option>
                        <option data-region="5/" value="mln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mln') ? 'selected' : ''; ?>>MALANA (Monywa)</option>
                        <option data-region="5/" value="skn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'skn') ? 'selected' : ''; ?>>SAKANA (Sagaing)</option>
                        <option data-region="5/" value="sbn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'sbn') ? 'selected' : ''; ?>>SHABANA (Shwebo)</option>
                        <option data-region="5/" value="tmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tmn') ? 'selected' : ''; ?>>TAMANA (Tamu)</option>

                        <option data-region="6/" value="dth" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dth') ? 'selected' : ''; ?>>DATHANA (Dawei)</option>
                        <option data-region="6/" value="kth" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kth') ? 'selected' : ''; ?>>KATHANA (Kawthaung)</option>
                        <option data-region="6/" value="mmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mmn') ? 'selected' : ''; ?>>MAMANA (Myeik)</option>
                        <option data-region="6/" value="plw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'plw') ? 'selected' : ''; ?>>PALANA (Palaw)</option>

                        <option data-region="7/" value="bkn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bkn') ? 'selected' : ''; ?>>BAKANA (Bago)</option>
                        <option data-region="7/" value="ddn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ddn') ? 'selected' : ''; ?>>DADANA (Daik-U)</option>
                        <option data-region="7/" value="kwn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kwn') ? 'selected' : ''; ?>>KAAHNNA (Kawa)</option>
                        <option data-region="7/" value="nln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nln') ? 'selected' : ''; ?>>NYALANA (Nyaunglebin)</option>
                        <option data-region="7/" value="thn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'thn') ? 'selected' : ''; ?>>THANAPA (Thanatpin)</option>
                        <option data-region="7/" value="tgo" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgo') ? 'selected' : ''; ?>>TAHANA (Taungoo)</option>

                        <option data-region="8/" value="aln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'aln') ? 'selected' : ''; ?>>AHLANA (Aunglan)</option>
                        <option data-region="8/" value="ckn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ckn') ? 'selected' : ''; ?>>CHAKHANA (Chauk)</option>
                        <option data-region="8/" value="mbn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mbn') ? 'selected' : ''; ?>>MABANA (Magway)</option>
                        <option data-region="8/" value="mbw" <?php echo (isset($POST['nrc_township']) && $_POST['nrc_township'] == 'mbw') ? 'selected' : ''; ?>>MALANA (Minbu)</option>
                        <option data-region="8/" value="nmm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nmm') ? 'selected' : ''; ?>>NAMANA (Natmauk)</option>
                        <option data-region="8/" value="pku" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pku') ? 'selected' : ''; ?>>PAKHANA (Pakokku)</option>
                        <option data-region="8/" value="ttg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ttg') ? 'selected' : ''; ?>>TATHANA (Taungdwingyi)</option>

                        <option data-region="9/" value="amy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'amy') ? 'selected' : ''; ?>>AHMAYA (Amarapura)</option>
                        <option data-region="9/" value="amz" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'amz') ? 'selected' : ''; ?>>AHMAZA (Aungmyethazan)</option>
                        <option data-region="9/" value="cmt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'cmt') ? 'selected' : ''; ?>>CHANMYATHA (Chanmyathazi)</option>
                        <option data-region="9/" value="kpd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kpd') ? 'selected' : ''; ?>>KAPANA (Kyaukpadaung)</option>
                        <option data-region="9/" value="mam" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mam') ? 'selected' : ''; ?>>MAHAMA (Maha Aungmye)</option>
                        <option data-region="9/" value="mgk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgk') ? 'selected' : ''; ?>>MAHHANA (Mogok)</option>
                        <option data-region="9/" value="mdy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mdy') ? 'selected' : ''; ?>>MALANA (Mandalay Core)</option>
                        <option data-region="9/" value="mty" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mty') ? 'selected' : ''; ?>>MATANA (Mattaya)</option>
                        <option data-region="9/" value="mtl" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mtl') ? 'selected' : ''; ?>>METHTANA (Meiktila)</option>
                        <option data-region="9/" value="nyu" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nyu') ? 'selected' : ''; ?>>NYAOUNA (Nyaung-U / Bagan)</option>
                        <option data-region="9/" value="pgt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pgt') ? 'selected' : ''; ?>>PYIGYITHA (Pyigyidagun)</option>

                        <option data-region="10/" value="bln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bln') ? 'selected' : ''; ?>>BILANA (Bilin)</option>
                        <option data-region="10/" value="kmw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kmw') ? 'selected' : ''; ?>>KAMANA (Kyaikmaraw)</option>
                        <option data-region="10/" value="kto" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kto') ? 'selected' : ''; ?>>KHANANA (Kyaikto)</option>
                        <option data-region="10/" value="mlm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mlm') ? 'selected' : ''; ?>>MALANA (Mawlamyine)</option>
                        <option data-region="10/" value="mdn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mdn') ? 'selected' : ''; ?>>MUDANA (Mudon)</option>
                        <option data-region="10/" value="png" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'png') ? 'selected' : ''; ?>>PATHANA (Paung)</option>

                        <option data-region="11/" value="stw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'stw') ? 'selected' : ''; ?>>SATANA (Sittwe)</option>
                        <option data-region="11/" value="btd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'btd') ? 'selected' : ''; ?>>BATHANA (Buthidaung)</option>
                        <option data-region="11/" value="gwa" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'gwa') ? 'selected' : ''; ?>>GAAHNNA (Gwa)</option>
                        <option data-region="11/" value="kpp" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kpp') ? 'selected' : ''; ?>>KAPANA (Kyaukpyu)</option>
                        <option data-region="11/" value="kta" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kta') ? 'selected' : ''; ?>>KATANA (Kyauktaw)</option>
                        <option data-region="11/" value="mtd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mtd') ? 'selected' : ''; ?>>MAAHNA (Maungdaw)</option>
                        <option data-region="11/" value="mnb" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mnb') ? 'selected' : ''; ?>>MABANA (Minbya)</option>
                        <option data-region="11/" value="mpu" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mpu') ? 'selected' : ''; ?>>MAPANA (Myebon)</option>
                        <option data-region="11/" value="mru" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mru') ? 'selected' : ''; ?>>MAOUNA (Mrauk-U)</option>
                        <option data-region="11/" value="mra" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mra') ? 'selected' : ''; ?>>MAKANA (Manaung)</option>
                        <option data-region="11/" value="png" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'png') ? 'selected' : ''; ?>>PANANA (Ponnagyun)</option>
                        <option data-region="11/" value="pauk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pauk') ? 'selected' : ''; ?>>PATANA (Pauktaw)</option>
                        <option data-region="11/" value="ram" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ram') ? 'selected' : ''; ?>>RAMANA (Ramree)</option>
                        <option data-region="11/" value="rtd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'rtd') ? 'selected' : ''; ?>>YATHANA (Rathedaung)</option>
                        <option data-region="11/" value="tdw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tdw') ? 'selected' : ''; ?>>THANANA (Thandwe)</option>
                        <option data-region="11/" value="tgo" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgo') ? 'selected' : ''; ?>>TAKANA (Toungup)</option>      

                        <option data-region="12/" value="aln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'aln') ? 'selected' : ''; ?>>ALANA (Ahlone)</option>
                        <option data-region="12/" value="bhn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bhn') ? 'selected' : ''; ?>>BAHANA (Bahan)</option>
                        <option data-region="12/" value="btt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'btt') ? 'selected' : ''; ?>>BATAHHTA (Botahtaung)</option>
                        <option data-region="12/" value="coc" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'coc') ? 'selected' : ''; ?>>COCOKHA (Cocokyun)</option>
                        <option data-region="12/" value="dgn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dgn') ? 'selected' : ''; ?>>DAGANA (Dagon)</option>
                        <option data-region="12/" value="dgm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dgm') ? 'selected' : ''; ?>>DAGAMA (Dagon Seikkan)</option>
                        <option data-region="12/" value="dge" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dge') ? 'selected' : ''; ?>>DAGANA (East Dagon)</option>
                        <option data-region="12/" value="dgn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dgn') ? 'selected' : ''; ?>>DAGANA (North Dagon)</option>
                        <option data-region="12/" value="sdg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'sdg') ? 'selected' : ''; ?>>DAGATA (South Dagon)</option>
                        <option data-region="12/" value="dla" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dla') ? 'selected' : ''; ?>>DALANA (Dala)</option>
                        <option data-region="12/" value="dab" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dab') ? 'selected' : ''; ?>>DAWANA (Dawbon)</option>
                        <option data-region="12/" value="hlg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hlg') ? 'selected' : ''; ?>>LATHANA (Hlaing)</option>
                        <option data-region="12/" value="hlt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hlt') ? 'selected' : ''; ?>>HLATHA (Hlaingthaya)</option>
                        <option data-region="12/" value="hlg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hlg') ? 'selected' : ''; ?>>HLAGANA (Hlegu)</option>
                        <option data-region="12/" value="hmb" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hmb') ? 'selected' : ''; ?>>HMAWBI (Hmawbi)</option>
                        <option data-region="12/" value="htb" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'htb') ? 'selected' : ''; ?>>HTATANA (Htantabin)</option>
                        <option data-region="12/" value="isn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'isn') ? 'selected' : ''; ?>>AHSANA (Insein)</option>
                        <option data-region="12/" value="kmy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kmy') ? 'selected' : ''; ?>>KAMAYA (Kamayut)</option>
                        <option data-region="12/" value="khm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'khm') ? 'selected' : ''; ?>>KHAMANA (Kawhmu)</option>
                        <option data-region="12/" value="kya" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kya') ? 'selected' : ''; ?>>KAYANA (Kayan)</option>
                        <option data-region="12/" value="ktd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ktd') ? 'selected' : ''; ?>>KATAFA (Kyauktada)</option>
                        <option data-region="12/" value="ktn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ktn') ? 'selected' : ''; ?>>KATANA (Kyauktan)</option>
                        <option data-region="12/" value="kya" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kya') ? 'selected' : ''; ?>>KAGANA (Kungyangon)</option>
                        <option data-region="12/" value="lmd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lmd') ? 'selected' : ''; ?>>LAMANA (Lanmadaw)</option>
                        <option data-region="12/" value="lth" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lth') ? 'selected' : ''; ?>>LATHA (Latha)</option>
                        <option data-region="12/" value="myg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'myg') ? 'selected' : ''; ?>>MAYAKA (Mayangone)</option>
                        <option data-region="12/" value="mgl" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgl') ? 'selected' : ''; ?>>MAGADA (Mingaladon)</option>
                        <option data-region="12/" value="mgt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgt') ? 'selected' : ''; ?>>MGTANA (Mingala Taungnyunt)</option>
                        <option data-region="12/" value="nok" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nok') ? 'selected' : ''; ?>>OKKANA (North Okkalapa)</option>
                        <option data-region="12/" value="pbd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pbd') ? 'selected' : ''; ?>>PABADA (Pabedan)</option>
                        <option data-region="12/" value="pzd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pzd') ? 'selected' : ''; ?>>PAZANA (Pazundaung)</option>
                        <option data-region="12/" value="scg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'scg') ? 'selected' : ''; ?>>SATHANA (Sanchaung)</option>
                        <option data-region="12/" value="skk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'skk') ? 'selected' : ''; ?>>SAKANA (Seikkyi Kanaungto)</option>
                        <option data-region="12/" value="spt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'spt') ? 'selected' : ''; ?>>SAKANA (Shwepyitha)</option>
                        <option data-region="12/" value="sok" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'sok') ? 'selected' : ''; ?>>OKTANA (South Okkalapa)</option>
                        <option data-region="12/" value="tky" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tky') ? 'selected' : ''; ?>>TAFAKA (Taikkyi)</option>
                        <option data-region="12/" value="tmw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tmw') ? 'selected' : ''; ?>>TAMANA (Tamwe)</option>
                        <option data-region="12/" value="tly" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tly') ? 'selected' : ''; ?>>THALANA (Thanlyin)</option>
                        <option data-region="12/" value="tkt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tkt') ? 'selected' : ''; ?>>THAKATA (Thaketa)</option>
                        <option data-region="12/" value="tgw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgw') ? 'selected' : ''; ?>>THAGANA (Thongwa)</option>
                        <option data-region="12/" value="tgk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgk') ? 'selected' : ''; ?>>THAGANA (Thingangyun)</option>
                        <option data-region="12/" value="twn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'twn') ? 'selected' : ''; ?>>TWATANA (Twante)</option>
                        <option data-region="12/" value="ykn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ykn') ? 'selected' : ''; ?>>YAKANA (Yankin)</option>

                        <option data-region="13/" value="tgy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgy') ? 'selected' : ''; ?>>TAYANA (Taunggyi)</option>
                        <option data-region="13/" value="klw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'klw') ? 'selected' : ''; ?>>KALANA (Kalaw)</option>
                        <option data-region="13/" value="hho" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hho') ? 'selected' : ''; ?>>HEHONA (Heho)</option>
                        <option data-region="13/" value="hop" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hop') ? 'selected' : ''; ?>>HAPANA (Hopong)</option>
                        <option data-region="13/" value="hsh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hsh') ? 'selected' : ''; ?>>HASANA (Hsihseng)</option>
                        <option data-region="13/" value="lsk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lsk') ? 'selected' : ''; ?>>LASANA (Lawksawk)</option>
                        <option data-region="13/" value="llo" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'llo') ? 'selected' : ''; ?>>LOLANA (Loilen)</option>
                        <option data-region="13/" value="nsh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nsh') ? 'selected' : ''; ?>>NASANA (Nyaungshwe)</option>
                        <option data-region="13/" value="pkh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pkh') ? 'selected' : ''; ?>>PAKHANA (Pekon)</option>
                        <option data-region="13/" value="pdy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pdy') ? 'selected' : ''; ?>>PADANA (Pindaya)</option>
                        <option data-region="13/" value="plg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'plg') ? 'selected' : ''; ?>>PALANA (Pinlaung)</option>
                        <option data-region="13/" value="ywa" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ywa') ? 'selected' : ''; ?>>YAWANA (Ywangan)</option>
                        <option data-region="13/" value="lso" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lso') ? 'selected' : ''; ?>>LALANA (Lashio)</option>
                        <option data-region="13/" value="hsn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hsn') ? 'selected' : ''; ?>>HASANA (Hsenwi)</option>
                        <option data-region="13/" value="hsp" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hsp') ? 'selected' : ''; ?>>HASAPA (Hsipaw)</option>

                        <option data-region="14/" value="pat" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pat') ? 'selected' : ''; ?>>PATHANA (Pathein)</option>
                        <option data-region="14/" value="hth" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hth') ? 'selected' : ''; ?>>HATHANA (Hinthada)</option>
                        <option data-region="14/" value="mya" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mya') ? 'selected' : ''; ?>>MYANA (Myaungmya)</option>
                        <option data-region="14/" value="lab" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lab') ? 'selected' : ''; ?>>LATANA (Labutta)</option>
                        <option data-region="14/" value="mgn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgn') ? 'selected' : ''; ?>>MAGANA (Maubin)</option>
                        <option data-region="14/" value="pyn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pyn') ? 'selected' : ''; ?>>PYANANA (Pyapon)</option>
                        <option data-region="14/" value="bgh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bgh') ? 'selected' : ''; ?>>BAGALANA (Boghale)</option>
                        <option data-region="14/" value="dad" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dad') ? 'selected' : ''; ?>>DADANA (Dedaye)</option>
                        <option data-region="14/" value="fap" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'fap') ? 'selected' : ''; ?>>HAPANA (Phapon)</option>
                        <option data-region="14/" value="kan" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kan') ? 'selected' : ''; ?>>KATANA (Kyangin)</option>
                        <option data-region="14/" value="kda" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kda') ? 'selected' : ''; ?>>KADANA (Kyaiklat)</option>
                        <option data-region="14/" value="kln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kln') ? 'selected' : ''; ?>>KALANA (Kalaung)</option>
                        <option data-region="14/" value="kpt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kpt') ? 'selected' : ''; ?>>KAPANA (Kyongpyaw)</option>
                        <option data-region="14/" value="lap" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lap') ? 'selected' : ''; ?>>LAPANA (Lemyethna)</option>
                        <option data-region="14/" value="mam" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mam') ? 'selected' : ''; ?>>MAMANA (Mawlamyinegyun)</option>
                        <option data-region="14/" value="mna" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mna') ? 'selected' : ''; ?>>MANANA (Myanaung)</option>
                        <option data-region="14/" value="nga" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nga') ? 'selected' : ''; ?>>NGAPANA (Ngapudaw)</option>
                        <option data-region="14/" value="ntn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ntn') ? 'selected' : ''; ?>>NATANA (Nyaungdon)</option>
                        <option data-region="14/" value="ptn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ptn') ? 'selected' : ''; ?>>PATANA (Pantannaw)</option>
                        <option data-region="14/" value="ttn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ttn') ? 'selected' : ''; ?>>THATANA (Thabaung)</option>
                        <option data-region="14/" value="wkm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'wkm') ? 'selected' : ''; ?>>WAKAMA (Wakema)</option>
                        <option data-region="14/" value="yna" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'yna') ? 'selected' : ''; ?>>YANAUNA (Yegyi)</option>
                   