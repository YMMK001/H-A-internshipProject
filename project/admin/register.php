<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'intern_test'); 
define('DB_USER', 'root');      
define('DB_PASS', '');      

// Initialize variables at the top to prevent page-load notices
$error = '';
$success = '';
$name = '';
$email = '';
$phone = '';
$nrc = '';

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form inputs
    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['user_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['user_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $nrc_region = $_POST['nrc_region'] ?? '';     // e.g., "8/"
    $nrc_township = trim($_POST['nrc_township'] ?? ''); // e.g., "pku"
    $nrc_number = trim($_POST['nrc_number'] ?? '');     // e.g., "5898539"
    
    // Concatenate to look exactly like your database layout: "8/pku /5898539"
    $nrc = $nrc_region . $nrc_township . " /" . $nrc_number;
    $role = 'renter'; // Default role

    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($nrc_region) || empty($nrc_township) || empty($nrc_number)) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^\d{6}$/', $nrc_number)) {
        $error = 'NRC Serial Number must be exactly 6 digits.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check connection
        if ($conn->connect_error) {
            $error = 'Database Connection Failed: ' . $conn->connect_error;
        } else {
            $conn->set_charset("utf8mb4");

            // Check if Email already exists using MySQLi prepared statements
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error = 'Email is already registered.';
            }
            $check_stmt->close();

            // Insert new user if no registration conflicts exist
            if (empty($error)) {
                $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, nrc, role) VALUES (?, ?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("ssssss", $name, $email, $password, $phone, $nrc, $role);

                if ($insert_stmt->execute()) {
                    $success = 'Account created successfully! You can now sign in.';
                    $name = $email = $phone = $nrc = '';
                } else {
                    $error = 'Execution Error: ' . $insert_stmt->error;
                }
                $insert_stmt->close();
            }
            
            $conn->close();
            
            if (empty($error)) {
                header('Location: login.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalHub - Registry Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fbfaf7] text-stone-900 antialiased min-h-screen flex flex-col justify-center py-16">
     
    <!-- Classic Registry Card Container -->
    <div class="bg-white w-[460px] mx-auto p-10 border border-stone-300 shadow-sm relative">
        
        <!-- Subtle Classic Header Line -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-blue-900"></div>

        <!-- Identity / Logo Token -->
        <div class="flex justify-center mb-6">
            <div class="h-12 w-12 bg-blue-900 border border-amber-600 flex items-center justify-center text-amber-100 font-serif font-bold text-2xl">R</div>
        </div>
        
        <h1 class="font-serif font-normal text-3xl text-center tracking-tight text-stone-900">Create Account</h1>
        <p class="text-stone-500 font-serif italic text-sm text-center mt-2 mb-8">
            Or <a href="login.php" class="text-blue-900 hover:underline font-medium">sign in</a> to your existing profile
        </p>
        
        <!-- Error Alert Layout -->
        <?php if (!empty($error)): ?>
            <div class="bg-stone-50 border-l-4 border-amber-700 text-stone-800 px-4 py-3 mb-6 text-sm font-serif italic" role="alert">
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Success Alert Layout -->
        <?php if (!empty($success)): ?>
            <div class="bg-stone-50 border-l-4 border-emerald-700 text-stone-800 px-4 py-3 mb-6 text-sm font-serif italic" role="alert">
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="flex flex-col gap-5">
            
            <!-- Full Name Form Group -->
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="name">Full Name</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all placeholder-stone-400" 
                       type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required placeholder="e.g., Alexander Mercer">
            </div>
            
            <!-- Email Address Form Group -->
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="user_email">Email Address</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all placeholder-stone-400" 
                       type="email" id="user_email" name="user_email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="name@domain.com">
            </div>
            
            <!-- Phone Number Form Group -->
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="phone">Phone Number</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all placeholder-stone-400" 
                       type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required placeholder="+95...">
            </div>
            
            <!-- NRC Number Layout -->
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider">NRC Number Documentation</label>
                <div class="grid grid-cols-3 gap-2">
                    
                    <!-- 1. Region/State Code Dropdown -->
                    <select name="nrc_region" class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-sm text-stone-800 transition-all cursor-pointer" required>
                        <option value="" disabled selected>Code</option>
                        <?php for ($i = 1; $i <= 14; $i++): ?>
                            <option value="<?php echo $i; ?>/" <?php echo (isset($_POST['nrc_region']) && $_POST['nrc_region'] == "$i/") ? 'selected' : ''; ?>>
                                <?php echo $i; ?>/
                            </option>
                        <?php endfor; ?>
                    </select>

                    <!-- 2. Township Code Dropdown -->
                    <select name="nrc_township" class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-sm text-stone-800 transition-all cursor-pointer" required>
                        <!-- 1 / KACHIN STATE -->
                <option value="bmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bmn') ? 'selected' : ''; ?>>BAMANA (Bhamo)</option>
                <option value="khf" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'khf') ? 'selected' : ''; ?>>KHAPHANA (Chipwi)</option>
                <option value="mkn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mkn') ? 'selected' : ''; ?>>MAKHANA (Myitkyina)</option>
                <option value="mgn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgn') ? 'selected' : ''; ?>>MAGHANA (Mogaung)</option>
                <option value="mvn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mvn') ? 'selected' : ''; ?>>MANYANA (Mohnyin)</option>
                <option value="ptn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ptn') ? 'selected' : ''; ?>>PATANA (Putao)</option>
                <option value="wmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'wmn') ? 'selected' : ''; ?>>WAMANA (Waingmaw)</option>

                <!-- 2 / KAYAH STATE -->
                <option value="dmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dmn') ? 'selected' : ''; ?>>DAMANA (Demawso)</option>
                <option value="lkn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lkn') ? 'selected' : ''; ?>>LAKANA (Loikaw)</option>
                <option value="msn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'msn') ? 'selected' : ''; ?>>MASANA (Mese)</option>
                <option value="ytn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ytn') ? 'selected' : ''; ?>>YATHANA (Bawlakhe)</option>

                <!-- 3 / KAYIN STATE -->
                <option value="ban" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ban') ? 'selected' : ''; ?>>BAAHNA (Hpa-an)</option>
                <option value="kky" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kky') ? 'selected' : ''; ?>>KAKAYA (Kawkareik)</option>
                <option value="mwt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mwt') ? 'selected' : ''; ?>>MAWATHTA (Myawaddy)</option>
                <option value="ppn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ppn') ? 'selected' : ''; ?>>PAPANA (Hpapun)</option>
                <option value="ttn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ttn') ? 'selected' : ''; ?>>THATANA (Thaton)</option>

                <!-- 4 / CHIN STATE -->
                <option value="hkh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hkh') ? 'selected' : ''; ?>>HAKHANA (Hakha)</option>
                <option value="hpn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hpn') ? 'selected' : ''; ?>>HPANA (Htantlang)</option>
                <option value="mtn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mtn') ? 'selected' : ''; ?>>MATANA (Matupi)</option>
                <option value="pln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pln') ? 'selected' : ''; ?>>PALANA (Paletwa)</option>
                <option value="fnn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'fnn') ? 'selected' : ''; ?>>PHANANA (Falam)</option>

                <!-- 5 / SAGAING REGION -->
                <option value="atn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'atn') ? 'selected' : ''; ?>>AHTANA (Ayadaw)</option>
                <option value="kln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kln') ? 'selected' : ''; ?>>KALANA (Kalay)</option>
                <option value="ktn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ktn') ? 'selected' : ''; ?>>KATHANA (Katha)</option>
                <option value="mln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mln') ? 'selected' : ''; ?>>MALANA (Monywa)</option>
                <option value="skn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'skn') ? 'selected' : ''; ?>>SAKANA (Sagaing)</option>
                <option value="sbn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'sbn') ? 'selected' : ''; ?>>SHABANA (Shwebo)</option>
                <option value="tmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tmn') ? 'selected' : ''; ?>>TAMANA (Tamu)</option>

                <!-- 6 / TANINTHARYI REGION -->
                <option value="dth" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dth') ? 'selected' : ''; ?>>DATHANA (Dawei)</option>
                <option value="kth" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kth') ? 'selected' : ''; ?>>KATHANA (Kawthaung)</option>
                <option value="mmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mmn') ? 'selected' : ''; ?>>MAMANA (Myeik)</option>
                <option value="plw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'plw') ? 'selected' : ''; ?>>PALANA (Palaw)</option>

                <!-- 7 / BAGO REGION -
                <option value="bkn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bkn') ? 'selected' : ''; ?>>BAKANA (Bago)</option>
                <option value="ddn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ddn') ? 'selected' : ''; ?>>DADANA (Daik-U)</option>
                <option value="kwn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kwn') ? 'selected' : ''; ?>>KAAHNNA (Kawa)</option>
                <option value="nln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nln') ? 'selected' : ''; ?>>NYALANA (Nyaunglebin)</option>
                <option value="thn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'thn') ? 'selected' : ''; ?>>THANAPA (Thanatpin)</option>
                <option value="tgo" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgo') ? 'selected' : ''; ?>>TAHANA (Taungoo)</option>

                <!-- 8 / MAGWAY REGION -->
                <option value="aln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'aln') ? 'selected' : ''; ?>>AHLANA (Aunglan)</option>
                <option value="ckn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ckn') ? 'selected' : ''; ?>>CHAKHANA (Chauk)</option>
                <option value="mbn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mbn') ? 'selected' : ''; ?>>MABANA (Magway)</option>
                <option value="mbw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mbw') ? 'selected' : ''; ?>>MALANA (Minbu)</option>
                <option value="nmm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nmm') ? 'selected' : ''; ?>>NAMANA (Natmauk)</option>
                <option value="pku" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pku') ? 'selected' : ''; ?>>PAKHANA (Pakokku)</option>
                <option value="ttg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ttg') ? 'selected' : ''; ?>>TATHANA (Taungdwingyi)</option>

                <!-- 9 / MANDALAY REGION -->
                <option value="amy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'amy') ? 'selected' : ''; ?>>AHMAYA (Amarapura)</option>
                <option value="amz" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'amz') ? 'selected' : ''; ?>>AHMAZA (Aungmyethazan)</option>
                <option value="cmt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'cmt') ? 'selected' : ''; ?>>CHANMYATHA (Chanmyathazi)</option>
                <option value="kpd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kpd') ? 'selected' : ''; ?>>KAPANA (Kyaukpadaung)</option>
                <option value="mam" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mam') ? 'selected' : ''; ?>>MAHAMA (Maha Aungmye)</option>
                <option value="mgk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgk') ? 'selected' : ''; ?>>MAHHANA (Mogok)</option>
                <option value="mdy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mdy') ? 'selected' : ''; ?>>MALANA (Mandalay Core)</option>
                <option value="mty" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mty') ? 'selected' : ''; ?>>MATANA (Mattaya)</option>
                <option value="mtl" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mtl') ? 'selected' : ''; ?>>METHTANA (Meiktila)</option>
                <option value="nyu" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nyu') ? 'selected' : ''; ?>>NYAOUNA (Nyaung-U / Bagan)</option>
                <option value="pgt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pgt') ? 'selected' : ''; ?>>PYIGYITHA (Pyigyidagun)</option>

                <!-- 10 / MON STATE -->
                <option value="bln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bln') ? 'selected' : ''; ?>>BILANA (Bilin)</option>
                <option value="kmw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kmw') ? 'selected' : ''; ?>>KAMANA (Kyaikmaraw)</option>
                <option value="kto" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kto') ? 'selected' : ''; ?>>KHANANA (Kyaikto)</option>
                <option value="mlm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mlm') ? 'selected' : ''; ?>>MALANA (Mawlamyine)</option>
                <option value="mdn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mdn') ? 'selected' : ''; ?>>MUDANA (Mudon)</option>
                <option value="png" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'png') ? 'selected' : ''; ?>>PATHANA (Paung)</option>

                <!-- 11 / RAKHINE STATE -->
                <!-- ========================================== -->
                <!-- 11 / RAKHINE STATE (ရခိုင်ပြည်နယ်)          -->
                <!-- ========================================== -->
                <option value="stw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'stw') ? 'selected' : ''; ?>>SATANA (Sittwe)</option>
                <option value="btd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'btd') ? 'selected' : ''; ?>>BATHANA (Buthidaung)</option>
                <option value="gwa" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'gwa') ? 'selected' : ''; ?>>GAAHNNA (Gwa)</option>
                <option value="kpp" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kpp') ? 'selected' : ''; ?>>KAPANA (Kyaukpyu)</option>
                <option value="kta" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kta') ? 'selected' : ''; ?>>KATANA (Kyauktaw)</option>
                <option value="mtd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mtd') ? 'selected' : ''; ?>>MAAHNA (Maungdaw)</option>
                <option value="mnb" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mnb') ? 'selected' : ''; ?>>MABANA (Minbya)</option>
                <option value="mpu" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mpu') ? 'selected' : ''; ?>>MAPANA (Myebon)</option>
                <option value="mru" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mru') ? 'selected' : ''; ?>>MAOUNA (Mrauk-U)</option>
                <option value="mra" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mra') ? 'selected' : ''; ?>>MAKANA (Manaung)</option>
                <option value="png" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'png') ? 'selected' : ''; ?>>PANANA (Ponnagyun)</option>
                <option value="pauk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pauk') ? 'selected' : ''; ?>>PATANA (Pauktaw)</option>
                <option value="ram" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ram') ? 'selected' : ''; ?>>RAMANA (Ramree)</option>
                <option value="rtd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'rtd') ? 'selected' : ''; ?>>YATHANA (Rathedaung)</option>
                <option value="tdw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tdw') ? 'selected' : ''; ?>>THANANA (Thandwe)</option>
                <option value="tgo" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgo') ? 'selected' : ''; ?>>TAKANA (Toungup)</option      


                <!-- ========================================== -->
                <!-- 12 / YANGON REGION (ရန်ကုန်)               -->
                <!-- ========================================== -->
                <option value="aln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'aln') ? 'selected' : ''; ?>>ALANA (Ahlone)</option>
                <option value="bhn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bhn') ? 'selected' : ''; ?>>BAHANA (Bahan)</option>
                <option value="btt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'btt') ? 'selected' : ''; ?>>BATAHHTA (Botahtaung)</option>
                <option value="coc" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'coc') ? 'selected' : ''; ?>>COCOKHA (Cocokyun)</option>
                <option value="dgn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dgn') ? 'selected' : ''; ?>>DAGANA (Dagon)</option>
                <option value="dgm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dgm') ? 'selected' : ''; ?>>DAGAMA (Dagon Seikkan)</option>
                <option value="dge" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dge') ? 'selected' : ''; ?>>DAGANA (East Dagon)</option>
                <option value="dgn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dgn') ? 'selected' : ''; ?>>DAGANA (North Dagon)</option>
                <option value="sdg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'sdg') ? 'selected' : ''; ?>>DAGATA (South Dagon)</option>
                <option value="dla" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dla') ? 'selected' : ''; ?>>DALANA (Dala)</option>
                <option value="dab" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dab') ? 'selected' : ''; ?>>DAWANA (Dawbon)</option>
                <option value="hlg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hlg') ? 'selected' : ''; ?>>LATHANA (Hlaing)</option>
                <option value="hlt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hlt') ? 'selected' : ''; ?>>HLATHA (Hlaingthaya)</option>
                <option value="hlg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hlg') ? 'selected' : ''; ?>>HLAGANA (Hlegu)</option>
                <option value="hmb" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hmb') ? 'selected' : ''; ?>>HMAWBI (Hmawbi)</option>
                <option value="htb" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'htb') ? 'selected' : ''; ?>>HTATANA (Htantabin)</option>
                <option value="isn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'isn') ? 'selected' : ''; ?>>AHSANA (Insein)</option>
                <option value="kmy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kmy') ? 'selected' : ''; ?>>KAMAYA (Kamayut)</option>
                <option value="khm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'khm') ? 'selected' : ''; ?>>KHAMANA (Kawhmu)</option>
                <option value="kya" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kya') ? 'selected' : ''; ?>>KAYANA (Kayan)</option>
                <option value="ktd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ktd') ? 'selected' : ''; ?>>KATAFA (Kyauktada)</option>
                <option value="ktn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ktn') ? 'selected' : ''; ?>>KATANA (Kyauktan)</option>
                <option value="kya" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kya') ? 'selected' : ''; ?>>KAGANA (Kungyangon)</option>
                <option value="lmd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lmd') ? 'selected' : ''; ?>>LAMANA (Lanmadaw)</option>
                <option value="lth" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lth') ? 'selected' : ''; ?>>LATHA (Latha)</option>
                <option value="myg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'myg') ? 'selected' : ''; ?>>MAYAKA (Mayangone)</option>
                <option value="mgl" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgl') ? 'selected' : ''; ?>>MAGADA (Mingaladon)</option>
                <option value="mgt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgt') ? 'selected' : ''; ?>>MGTANA (Mingala Taungnyunt)</option>
                <option value="nok" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nok') ? 'selected' : ''; ?>>OKKANA (North Okkalapa)</option>
                <option value="pbd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pbd') ? 'selected' : ''; ?>>PABADA (Pabedan)</option>
                <option value="pzd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pzd') ? 'selected' : ''; ?>>PAZANA (Pazundaung)</option>
                <option value="scg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'scg') ? 'selected' : ''; ?>>SATHANA (Sanchaung)</option>
                <option value="skk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'skk') ? 'selected' : ''; ?>>SAKANA (Seikkyi Kanaungto)</option>
                <option value="spt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'spt') ? 'selected' : ''; ?>>SAKANA (Shwepyitha)</option>
                <option value="sok" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'sok') ? 'selected' : ''; ?>>OKTANA (South Okkalapa)</option>
                <option value="tky" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tky') ? 'selected' : ''; ?>>TAFAKA (Taikkyi)</option>
                <option value="tmw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tmw') ? 'selected' : ''; ?>>TAMANA (Tamwe)</option>
                <option value="tly" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tly') ? 'selected' : ''; ?>>THALANA (Thanlyin)</option>
                <option value="tkt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tkt') ? 'selected' : ''; ?>>THAKATA (Thaketa)</option>
                <option value="tgw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgw') ? 'selected' : ''; ?>>THAGANA (Thongwa)</option>
                <option value="tgk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgk') ? 'selected' : ''; ?>>THAGANA (Thingangyun)</option>
                <option value="twn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'twn') ? 'selected' : ''; ?>>TWATANA (Twante)</option>
                <option value="ykn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ykn') ? 'selected' : ''; ?>>YAKANA (Yankin)</option>

                <!-- ========================================== -->
                <!-- 13 / SHAN STATE (ရှမ်းပြည်နယ်)              -->
                <!-- ========================================== -->
                <!-- SHAN SOUTH -->
                <option value="tgy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgy') ? 'selected' : ''; ?>>TAYANA (Taunggyi)</option>
                <option value="klw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'klw') ? 'selected' : ''; ?>>KALANA (Kalaw)</option>
                <option value="hho" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hho') ? 'selected' : ''; ?>>HEHONA (Heho)</option>
                <option value="hop" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hop') ? 'selected' : ''; ?>>HAPANA (Hopong)</option>
                <option value="hsh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hsh') ? 'selected' : ''; ?>>HASANA (Hsihseng)</option>
                <option value="lsk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lsk') ? 'selected' : ''; ?>>LASANA (Lawksawk)</option>
                <option value="llo" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'llo') ? 'selected' : ''; ?>>LOLANA (Loilen)</option>
                <option value="nsh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nsh') ? 'selected' : ''; ?>>NASANA (Nyaungshwe)</option>
                <option value="pkh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pkh') ? 'selected' : ''; ?>>PAKHANA (Pekon)</option>
                <option value="pdy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pdy') ? 'selected' : ''; ?>>PADANA (Pindaya)</option>
                <option value="plg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'plg') ? 'selected' : ''; ?>>PALANA (Pinlaung)</option>
                <option value="ywa" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ywa') ? 'selected' : ''; ?>>YAWANA (Ywangan)</option>

                <!-- SHAN NORTH -->
                <option value="lso" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lso') ? 'selected' : ''; ?>>LALANA (Lashio)</option>
                <option value="hsn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hsn') ? 'selected' : ''; ?>>HASANA (Hsenwi)</option>
                <option value="hsp" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hsp') ? 'selected' : ''; ?>>HASAPA (Hsipaw)</option>
                <option value="kmk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kmk') ? 'selected' : ''; ?>>KAMANA (Kyaukme)</option>
                <option value="kkl" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kkl') ? 'selected' : ''; ?>>KAKANA (Kunlong)</option>
                <option value="ktk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ktk') ? 'selected' : ''; ?>>KATANA (Kutkai)</option>
                <option value="mbi" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mbi') ? 'selected' : ''; ?>>MABANA (Mabein)</option>
                <option value="mmt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mmt') ? 'selected' : ''; ?>>MAMATA (Momeik)</option>
                <option value="myi" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'myi') ? 'selected' : ''; ?>>MAYANA (Mongyai)</option>
                <option value="mus" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mus') ? 'selected' : ''; ?>>MASANA (Muse)</option>
                <option value="nhk" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nhk') ? 'selected' : ''; ?>>NAKANA (Nanhkan)</option>
                <option value="ntu" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ntu') ? 'selected' : ''; ?>>NATANA (Namtu)</option>
                <option value="nhc" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nhc') ? 'selected' : ''; ?>>NAKHA (Nawnghkio)</option>
                <option value="tgy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tgy') ? 'selected' : ''; ?>>TAKANA (Tangyan)</option>

                <!-- SHAN EAST -->
                <option value="ktg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ktg') ? 'selected' : ''; ?>>KATHTANA (Kengtung)</option>
                <option value="tcl" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tcl') ? 'selected' : ''; ?>>TAKANA (Tachileik)</option>
                <option value="mkh" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mkh') ? 'selected' : ''; ?>>MAKHANA (Mongkhet)</option>
                <option value="mpy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mpy') ? 'selected' : ''; ?>>MAPANA (Mongpyin)</option>
                <option value="mya" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mya') ? 'selected' : ''; ?>>MAYANA (Mongyan)</option>
                <option value="mhs" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mhs') ? 'selected' : ''; ?>>MASANA (Mong Hsat)</option>
                <option value="mtn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mtn') ? 'selected' : ''; ?>>MATANA (Mong Ton)</option>

                <!-- SELF-ADMINISTERED ZONES & DIVISIONS -->
                <option value="lka" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lka') ? 'selected' : ''; ?>>LAKANA (Laukkaing - Kokang)</option>
                <option value="kkj" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kkj') ? 'selected' : ''; ?>>KAKANA (Konkyan - Kokang)</option>
                <option value="nsn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nsn') ? 'selected' : ''; ?>>NASANA (Namhsan - Palaung)</option>
                <option value="mtn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mtn') ? 'selected' : ''; ?>>MATANA (Manton - Palaung)</option>
                <option value="hpb" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hpb') ? 'selected' : ''; ?>>HAPATA (Hopang - Wa)</option>
                <option value="mka" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mka') ? 'selected' : ''; ?>>MAKANA (Mong Kung)</option>


                <!-- 14 / AYEYARWADY REGION (COMPLETE TOWNSHIP DIRECTORY) -->
                <option value="bgl" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bgl') ? 'selected' : ''; ?>>BAKANA (Bogale)</option>
                <option value="dbu" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'dbu') ? 'selected' : ''; ?>>DANAPHA (Danubyu)</option>
                <option value="ddy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ddy') ? 'selected' : ''; ?>>DADAYA (Dedaye)</option>
                <option value="ahm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ahm') ? 'selected' : ''; ?>>AHMANA (Ahmar)</option>
                <option value="eim" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'eim') ? 'selected' : ''; ?>>AHLANA (Einme)</option>
                <option value="hgi" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'hgi') ? 'selected' : ''; ?>>HATHANA (Hinthada)</option>
                <option value="igp" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'igp') ? 'selected' : ''; ?>>AINGAPANA (Ingapu)</option>
                <option value="kgy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kgy') ? 'selected' : ''; ?>>KAGHANA (Kangyidaunt)</option>
                <option value="klb" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'klb') ? 'selected' : ''; ?>>KAPANA (Kyaiklat)</option>
                <option value="kgi" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kgi') ? 'selected' : ''; ?>>KANYANA (Kyangin)</option>
                <option value="kgg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kgg') ? 'selected' : ''; ?>>KAGANA (Kyaunggon)</option>
                <option value="kpw" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'kpw') ? 'selected' : ''; ?>>KAPANA (Kyonpyaw)</option>
                <option value="lbt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lbt') ? 'selected' : ''; ?>>LABATA (Labutta)</option>
                <option value="lmt" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'lmt') ? 'selected' : ''; ?>>LAMANA (Lemyethna)</option>
                <option value="mub" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mub') ? 'selected' : ''; ?>>MAAHNNA (Maubin)</option>
                <option value="mgy" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgy') ? 'selected' : ''; ?>>MALANA (Mawlamyinegyun)</option>
                <option value="mna" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mna') ? 'selected' : ''; ?>>MANANA (Myanaung)</option>
                <option value="mym" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mym') ? 'selected' : ''; ?>>MYANANA (Myaungmya)</option>
                <option value="ngp" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ngp') ? 'selected' : ''; ?>>NGAPANA (Ngapudaw)</option>
                <option value="nyd" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'nyd') ? 'selected' : ''; ?>>NYADANA (Nyaungdon)</option>
                <option value="ptn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ptn') ? 'selected' : ''; ?>>PATANA (Pantanaw)</option>
                <option value="pat" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pat') ? 'selected' : ''; ?>>PATHANA (Pathein)</option>
                <option value="pyp" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'pyp') ? 'selected' : ''; ?>>PHAPANA (Pyapon)</option>
                <option value="psl" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'psl') ? 'selected' : ''; ?>>PASALA (Pyinsalu)</option>
                <option value="tbg" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'tbg') ? 'selected' : ''; ?>>THABANA (Thabaung)</option>
                <option value="wkm" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'wkm') ? 'selected' : ''; ?>>WAKHAMA (Wakema)</option>
                <option value="ygj" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ygj') ? 'selected' : ''; ?>>YAKANA (Yegyi)</option>
                <option value="zln" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'zln') ? 'selected' : ''; ?>>ZALANA (Zalun)</option>

                    </select>

                    <!-- 3. Serial Number Input -->
                    <input type="text" name="nrc_number" pattern="\d{6}" maxlength="6" 
                           value="<?php echo htmlspecialchars($_POST['nrc_number'] ?? ''); ?>" 
                           class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-sm text-stone-900 tracking-wider transition-all placeholder-stone-400" 
                           required placeholder="5898539">
                </div>
            </div>
            
            <!-- Password Form Group -->
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="user_password">Password</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all" 
                       type="password" id="user_password" name="user_password" required placeholder="••••••••">
            </div>
            
            <!-- Confirm Password Form Group -->
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="confirm_password">Confirm Password</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all" 
                       type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">
            </div>
            
            <!-- Submit Button Trigger -->
            <div class="pt-4">
                <button type="submit" class="bg-blue-900 w-full text-base font-serif font-medium p-3.5 text-amber-100 hover:bg-blue-950 border border-amber-800 shadow-sm transition-all tracking-wide">
                    Register Credentials &rarr;
                </button>
            </div>
        </form>
    </div>
    
</body>
</html>