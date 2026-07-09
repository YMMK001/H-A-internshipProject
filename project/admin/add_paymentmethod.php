<?php
// 1. Database Connection Configuration
$host     = 'localhost';
$db_name  = 'intern_test'; // Change to your DB name
$username = 'root';               // Change to your DB username
$password = '';                   // Change to your DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 2. Handle Form Submission
$message = '';
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $name           = trim($_POST['name'] ?? '');
    $account_name   = trim($_POST['account_name'] ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $is_active      = isset($_POST['is_active']) ? 1 : 0;

    // Simple validation
    if (empty($name) || empty($account_name) || empty($account_number)) {
        $message = 'All fields are required!';
    } else {
        try {
            // Prepare SQL injection safe query
            $sql = "INSERT INTO payment_methods (name, account_name, account_number, is_active) 
                    VALUES (:name, :account_name, :account_number, :is_active)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name'           => $name,
                ':account_name'   => $account_name,
                ':account_number' => $account_number,
                ':is_active'      => $is_active
            ]);

            $message = 'Payment method added successfully!';
            $isSuccess = true;
        } catch (PDOException $e) {
            $message = 'Error saving to database: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment Method</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800  flex">

    <div class="flex-shrink-0 h-screen sticky top-0 z-50">
        <?php include 'ownerheader.php'; ?>
    </div>

    <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
        
        <div class="bg-white border-b border-gray-300 shadow-sm px-6 py-3 flex items-center justify-between font-sans">
            <div class="flex items-center space-x-3">
                <button onclick="toggleMobileMenu()" class="sm:hidden bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium uppercase tracking-wider px-3 py-2 rounded shadow-sm border border-slate-700">
                    ☰ Menu
                </button>
                <div class="hidden sm:flex items-center space-x-2 text-xs text-gray-500">
                    <span class="text-gray-800 font-bold text-2xl">Add Payment Method</span>
                </div>
            </div>

            <div class="flex items-center space-x-4 divide-x divide-gray-200">
                <div class="pl-4 flex items-center space-x-2">
                    <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-white text-xs font-bold shadow-inner border border-slate-700">
                        AD
                    </div>
                    <div class="hidden lg:block leading-none">
                        <p class="text-xs font-bold text-gray-900">အိမ်ပိုင်ရှင် မန်နေဂျာ</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Console Role</p>
                    </div>
                </div>
            </div>
        </div>

        <main class="flex-1 flex  p-6 md:p-8 overflow-y-auto">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200/70 overflow-hidden">
                
                <div class="bg-slate-900 p-6 text-center border-b border-slate-800">
                    <h2 class="text-xl font-bold text-white tracking-tight">Add Payment Method</h2>
                    <p class="text-xs text-slate-400 mt-1">Configure systemic transactional vendor details</p>
                </div>

                <div class="p-6 md:p-8">
                    <?php if (!empty($message)): ?>
                        <div class="mb-5 p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $isSuccess ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' ?>">
                            <span><?= $isSuccess ? '✅' : '⚠️' ?></span>
                            <p><?= htmlspecialchars($message) ?></p>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="space-y-5">
                        
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Payment Method Name</label>
                            <input type="text" name="name" placeholder="e.g., Bank Transfer, Stripe, KBZPay" required
                                   class="w-full text-xs font-medium px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 focus:bg-white transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Account Name</label>
                            <input type="text" name="account_name" placeholder="e.g., U Aung Kyaw" required
                                   class="w-full text-xs font-medium px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 focus:bg-white transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Account Number / Details</label>
                            <input type="text" name="account_number" placeholder="e.g., 200-456-7890" required
                                   class="w-full text-xs font-mono px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 focus:bg-white transition-colors">
                        </div>

                        <div class="flex items-center pt-1">
                            <input type="checkbox" name="is_active" id="is_active" checked value="1"
                                   class="w-4 h-4 rounded text-slate-900 border-slate-300 focus:ring-offset-0 focus:ring-0 accent-slate-900 cursor-pointer">
                            <label for="is_active" class="ml-2.5 block text-xs font-semibold text-slate-700 select-none cursor-pointer">
                                Mark as Active immediately
                            </label>
                        </div>

                        <button type="submit" 
                                class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider py-3 px-4 rounded-lg transition-colors shadow-2xs mt-2 cursor-pointer">
                            Save Payment Method
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('mobMenuOverlay');
            
            if (sidebar && sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (overlay) overlay.classList.remove('hidden');
            } else if (sidebar) {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                if (overlay) overlay.classList.add('hidden');
            }
        }
    </script>
</body>
</html>