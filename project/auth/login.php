<?php
// Start a secure session to keep users logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'intern_test'); 
define('DB_USER', 'root');        
define('DB_PASS', '');            

$error = '';
$email = ''; // Initialized to prevent undefined variable notices in HTML value attribute

// Capture parameters from GET or POST
$redirect_to  = $_REQUEST['redirect'] ?? $_REQUEST['redirect_to'] ?? 'homepage';
$property_id  = $_REQUEST['property_id'] ?? '';
$type         = $_REQUEST['type'] ?? '';

// Dynamic Query String ပြန်လည်တည်ဆောက်ခြင်း (Register Link သို့မဟုတ် Form Action အတွက်)
$queryParams = [];
if (!empty($redirect_to)) $queryParams['redirect'] = $redirect_to;
if (!empty($property_id)) $queryParams['property_id'] = $property_id;
if (!empty($type))        $queryParams['type'] = $type;

$queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['user_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['user_password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address format.';
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            $error = 'Database Connection Error: ' . $conn->connect_error;
        } else {
            $conn->set_charset("utf8mb4");

            $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user) {
                    $is_valid = false;

                    // Check if the saved database password is a standard bcrypt hash
                    if (strpos($user['password'], '$2y$') === 0) {
                        // Secure hash match validation
                        if (password_verify($password, $user['password'])) {
                            $is_valid = true;
                        }
                    } else {
                        // Plain-text match fallback validation
                        if ($password === $user['password']) {
                            $is_valid = true;
                        }
                    }

                    if ($is_valid) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['name']; 
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];     

                        $stmt->close();
                        $conn->close();

                        // Admin Routing
                        if ($user['role'] === 'admin') {
                            header("Location: ../admin/owner_dashboard.php");
                            exit;
                        } else {
                            // Convenience routing matched with homepage trackers
                            if ($redirect_to === 'contract') {
                                // Redirect URL တွင် property_id နှင့် type တို့ ပါမပါ စစ်ဆေးပြီး ပေါင်းစပ်ပေးခြင်း
                                $contractParams = [];
                                if (!empty($property_id)) $contractParams['property_id'] = $property_id;
                                if (!empty($type))        $contractParams['type'] = $type;

                                $contractQuery = !empty($contractParams) ? '?' . http_build_query($contractParams) : '';

                                header("Location: ../renter/rentercontract.php" . $contractQuery);
                            } else {
                                header("Location: ../renter/renter_profile.php");
                            }
                            exit;
                        }
                    } else {
                        $error = 'Incorrect email or password.';
                    }
                } else {
                    $error = 'Incorrect email or password.';
                }
                if (isset($stmt) && $stmt !== false) { $stmt->close(); }
            } else {
                $error = 'Database Statement Preparation Failed.';
            }
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalHub - Authentication Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fbfaf7] text-stone-900 antialiased min-h-screen flex flex-col justify-between">

    <!-- STABLE TOP NAVIGATION HEADER -->
    <header class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-md border-b border-stone-200 shadow-xs">
        <?php include '../renter/homepageheader.php'; ?>
    </header>

    <!-- MAIN CONTAINER FOR LOGIN FORM -->
    <main class="flex-1 flex items-center justify-center py-4 px-4 sm:px-6">
        <div class="bg-white w-full max-w-[440px] p-8 sm:p-10 border border-stone-300 shadow-md relative rounded-sm">
            
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-slate-900"></div>
            
            <div class="flex justify-center mb-6">
                <div class="h-12 w-12 bg-slate-900 border border-amber-600 flex items-center justify-center text-amber-100 font-serif font-bold text-2xl shadow-xs">
                    R
                </div>
            </div>

            <h1 class="font-serif font-normal text-3xl text-center tracking-tight text-stone-900">Welcome Back</h1>
            <p class="text-stone-500 font-serif italic text-sm text-center mt-2 mb-8">
                Please sign in to access your dashboard account.
            </p>
            
            <?php if (!empty($error)): ?>
                <div class="bg-amber-50 border-l-4 border-amber-700 text-red-800 px-4 py-3 mb-6 text-sm font-serif italic rounded-r-sm" role="alert">
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . $queryString); ?>" method="POST" class="flex flex-col gap-5">
                
                <!-- HIDDEN INPUTS TO PRESERVE PARAMETERS -->
                <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirect_to); ?>">
                <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property_id); ?>">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">

                <div class="flex flex-col gap-1.5">
                    <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="useremail">Email Address</label>
                    <input class="p-2 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-slate-900 focus:bg-white text-stone-900 font-sans transition-all placeholder-stone-400 rounded-xs" 
                           type="email" 
                           id="useremail" 
                           name="user_email" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                           required 
                           placeholder="name@domain.com">
                </div>
                
                <div class="flex flex-col gap-1.5">
                    <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="userpassword">Password</label>
                    <input class="p-2 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-slate-900 focus:bg-white text-stone-900 font-sans transition-all rounded-xs" 
                           type="password" 
                           id="userpassword" 
                           name="user_password" 
                           required>
                </div>
                
                <div class="flex justify-between items-center text-sm font-serif mt-1">
                    <div class="flex items-center">  
                        <input type="checkbox" id="remember" class="accent-slate-900 h-4 w-4 border-stone-300 rounded-none cursor-pointer">
                        <label for="remember" class="ml-2 text-stone-600 select-none cursor-pointer">Remember execution credentials</label>
                    </div> 
                    <a href="#" class="text-amber-800 hover:text-slate-900 hover:underline transition-colors">Forgot Password?</a>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="bg-slate-900 w-full text-base font-serif font-medium p-3.5 text-amber-100 hover:bg-slate-950 border border-amber-800/50 shadow-xs transition-all tracking-wide cursor-pointer">
                        Authenticate Account &rarr;
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-stone-200 text-center text-xs font-serif text-stone-500">
                Do not have an active portal? <a href="register.php<?php echo $queryString; ?>" class="text-slate-900 hover:underline font-bold">Create an account</a>.
            </div>
        </div>
    </main>

    <!-- FOOTER PLACEHOLDER OR BOTTOM MARGIN -->
    <footer class="py-4 text-center text-xs text-stone-400 font-serif">
        &copy; <?php echo date('Y'); ?> RentalHub. All rights reserved.
    </footer>

</body>
</html>