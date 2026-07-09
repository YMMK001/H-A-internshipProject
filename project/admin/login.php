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

// Capture the redirection source from the URL parameters (defaults to 'homepage')
$redirect_to = $_GET['redirect'] ?? 'homepage';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['user_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['user_password'] ?? '';
    
    // Retrieve the source tracking from the submitted form data
    $redirect_to = $_POST['redirect_to'] ?? 'homepage';

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

                // Hybrid Verification Strategy
                if ($user) {
                    $is_valid = false;

                    if (strpos($user['password'], '$2y$10$') === 0) {
                        // Secure hash match validation
                        if (hash_equals($user['password'], crypt($password, $user['password']))) {
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
                            header("Location: owner_dashboard.php");
                            exit;
                        } else {
                            // CONVENIENT ROUTING MATCHED WITH HOMEPAGE TRACKERS
                            if ($redirect_to === 'contract') {
                                header("Location: ../renter/rentercontract.php");
                            } else {
                                header("Location: renter_profile.php");
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
<body class="bg-[#fbfaf7] text-stone-900 antialiased min-h-screen flex flex-col justify-center py-12">
      
    <!-- Classic Container Card -->
    <div class="bg-white w-[440px] mx-auto p-10 border border-stone-300 shadow-sm relative">
        
        <!-- Subtle Classic Header Line -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-blue-900"></div>
        
        <!-- Identity / Logo Token -->
        <div class="flex justify-center mb-6">
            <div class="h-12 w-12 bg-blue-900 border border-amber-600 flex items-center justify-center text-amber-100 font-serif font-bold text-2xl">R</div>
        </div>

        <h1 class="font-serif font-normal text-3xl text-center tracking-tight text-stone-900">Welcome Back</h1>
        <p class="text-stone-500 font-serif italic text-sm text-center mt-2 mb-8">
            Please sign in to access your dashboard account.
        </p>
        
        <!-- Display Alert Feedback Banner if login fails -->
        <?php if (!empty($error)): ?>
            <div class="bg-stone-50 border-l-4 border-amber-700 text-stone-800 px-4 py-3 mb-6 text-sm font-serif italic" role="alert">
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="flex flex-col gap-5">
            
            <!-- Hidden input field tracking where the user initiated the login -->
            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirect_to); ?>">

            <!-- Email Input Group -->
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="useremail">Email Address</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all placeholder-stone-400" 
                       type="email" 
                       id="useremail" 
                       name="user_email" 
                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                       required 
                       placeholder="name@domain.com">
            </div>
            
            <!-- Password Input Group -->
            <div class="flex flex-col gap-1.5">
                <label class="text-stone-700 text-xs font-semibold uppercase tracking-wider" for="userpassword">Password</label>
                <input class="p-3 bg-[#faf9f6] border border-stone-300 focus:outline-none focus:border-blue-900 focus:bg-white text-stone-900 font-sans transition-all" 
                       type="password" 
                       id="userpassword" 
                       name="user_password" 
                       required>
            </div>
            
            <!-- Utilities Section -->
            <div class="flex justify-between items-center text-sm font-serif mt-1">
                <div class="flex items-center">  
                    <input type="checkbox" id="remember" class="accent-blue-950 h-4 w-4 border-stone-300 rounded-none cursor-pointer">
                    <label for="remember" class="ml-2 text-stone-600 select-none cursor-pointer">Remember execution credentials</label>
                </div> 
                <a href="#" class="text-amber-800 hover:text-blue-900 hover:underline transition-colors">Forgot Password?</a>
            </div>
            
            <!-- Submit Action -->
            <div class="pt-4">
                <button type="submit" class="bg-blue-900 w-full text-base font-serif font-medium p-3.5 text-amber-100 hover:bg-blue-950 border border-amber-800 shadow-sm transition-all tracking-wide">
                    Authenticate Account &rarr;
                </button>
            </div>
        </form>

        <!-- Subtle Classic Footer Link -->
        <div class="mt-8 pt-6 border-t border-stone-200 text-center text-xs font-serif text-stone-500">
            Do not have an active portal? <a href="register.php" class="text-blue-900 hover:underline font-medium">Create an account</a>.
        </div>
    </div>
    
</body>
</html>