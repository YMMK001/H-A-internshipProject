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
     
    <div class="bg-white w-[460px] mx-auto p-10 border border-stone-300 shadow-sm relative">
        
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-blue-900"></div>

        <div class="flex justify-center mb-6">
            <div class="h-12 w-12 bg-blue-900 border border-amber-600 flex items-center justify-center text-amber-100 font-serif font-bold text-2xl">R</div>
        </div>
        
        <h1 class="font-serif font-normal text-3xl text-center tracking-tight text-stone-900">Create Account</h1>
        <p class="text-stone-500 font-serif italic text-sm text-center mt-2 mb-8">
            Or <a href="login.php" class="text-blue-900 hover:underline font-medium">sign in</a> to your existing profile
        </p>
        
        <?php if (!empty($error)): ?>
            <div class="bg-stone-50 border-l-4 border-amber-700 text-stone-800 px-4 py-3 mb-6 text-sm font-serif italic" role="alert">
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-stone-50 border-l-4 border-emerald-700 text-stone-800 px-4 py-3 mb-6 text-sm font-serif italic" role="alert">
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="flex flex-col gap-5">
            
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="name">Full Name</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all placeholder-stone-400" 
                       type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required placeholder="e.g., Alexander Mercer">
            </div>
            
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="user_email">Email Address</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all placeholder-stone-400" 
                       type="email" id="user_email" name="user_email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="name@domain.com">
            </div>
            
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="phone">Phone Number</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all placeholder-stone-400" 
                       type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required placeholder="+95...">
            </div>
            
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider">NRC Number Documentation</label>
                <div class="grid grid-cols-3 gap-2">
                    
                    <select id="nrc_region" name="nrc_region" class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-sm text-stone-800 transition-all cursor-pointer" required>
                        <option value="" disabled selected>Code</option>
                        <?php for ($i = 1; $i <= 14; $i++): ?>
                            <option value="<?php echo $i; ?>/" <?php echo (isset($_POST['nrc_region']) && $_POST['nrc_region'] == "$i/") ? 'selected' : ''; ?>>
                                <?php echo $i; ?>/
                            </option>
                        <?php endfor; ?>
                    </select>

                    <select id="nrc_township" name="nrc_township" class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-sm text-stone-800 transition-all cursor-pointer" required disabled>
                        <option value="" disabled selected>Township</option>
                        
                        <option data-region="1/" value="bmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'bmn') ? 'selected' : ''; ?>>BAMANA (Bhamo)</option>
                        <option data-region="1/" value="khf" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'khf') ? 'selected' : ''; ?>>KHAPHANA (Chipwi)</option>
                        <option data-region="1/" value="mkn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mkn') ? 'selected' : ''; ?>>MAKHANA (Myitkyina)</option>
                        <option data-region="1/" value="mgn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mgn') ? 'selected' : ''; ?>>MAGHANA (Mogaung)</option>
                        <option data-region="1/" value="mvn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'mvn') ? 'selected' : ''; ?>>MANYANA (Mohnyin)</option>
                        <option data-region="1/" value="ptn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'ptn') ? 'selected' : ''; ?>>PATANA (Putao)</option>
                        <option data-region="1/" value="wmn" <?php echo (isset($_POST['nrc_township']) && $_POST['nrc_township'] == 'wmn') ? 'selected' : ''; ?>>WAMANA (Waingmaw)</option>

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
                    </select>

                    <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-mono transition-all placeholder-stone-400 text-sm" 
                           type="text" name="nrc_number" pattern="\d{6}" maxlength="6" required placeholder="123456">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="user_password">Password</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all" 
                       type="password" id="user_password" name="user_password" required minlength="8">
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="confirm_password">Confirm Password</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all" 
                       type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            
            <button type="submit" class="w-full bg-blue-900 hover:bg-blue-950 text-white font-serif tracking-wide py-3.5 transition-colors shadow-sm mt-3">
                Register Account
            </button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const regionSelect = document.getElementById("nrc_region");
            const townshipSelect = document.getElementById("nrc_township");
            
            // Store all original township options systematically
            const allTownships = Array.from(townshipSelect.options);

            function filterTownships() {
                const selectedRegion = regionSelect.value;
                
                // Clear out existing options
                townshipSelect.innerHTML = "";
                
                // Add the baseline prompt option back
                const defaultOption = document.createElement("option");
                defaultOption.value = "";
                defaultOption.disabled = true;
                defaultOption.selected = true;
                defaultOption.textContent = "Township";
                townshipSelect.appendChild(defaultOption);

                if (selectedRegion) {
                    // Enable select control frame
                    townshipSelect.disabled = false;
                    
                    // Filter matching entries
                    const filtered = allTownships.filter(opt => opt.getAttribute("data-region") === selectedRegion);
                    
                    filtered.forEach(opt => {
                        // Re-apply state if post-back values match preserved options
                        if ("<?php echo isset($_POST['nrc_township']) ? $_POST['nrc_township'] : ''; ?>" === opt.value) {
                            opt.selected = true;
                            defaultOption.selected = false;
                        }
                        townshipSelect.appendChild(opt);
                    });
                } else {
                    townshipSelect.disabled = true;
                }
            }

            // Run instantly on load to protect form re-submissions/saved errors
            if (regionSelect.value) {
                filterTownships();
            }

            // Fire on manual structural changes
            regionSelect.addEventListener("change", filterTownships);
        });
    </script>
</body>
</html>