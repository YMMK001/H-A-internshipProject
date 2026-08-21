<?php
session_start();

// ==========================================
// 1. CHECK USER LOGIN
// ==========================================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// ==========================================
// 2. DATABASE CONNECTION
// ==========================================
$host    = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "intern_test";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ==========================================
// 3. FETCH CURRENT USER
// ==========================================
$user_id = intval($_SESSION['user_id']);

$user_query = "
    SELECT id, name, role
    FROM users
    WHERE id = ?
";

$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

$stmt->close();

// ==========================================
// 4. CHECK USER ROLE
// ==========================================
$user_role = strtolower(trim($user_data['role'] ?? ''));

if (!$user_data || $user_role !== 'renter') {
    header("Location: ../auth/login.php?error=unauthorized_role");
    exit();
}

$renter_name = $user_data['name'];

// ==========================================
// 5. GET SELECTED PROPERTY FROM BOOK LEASE
// ==========================================
$target_id = null;

if (isset($_GET['item_id']) && !empty($_GET['item_id'])) {
    $target_id = intval($_GET['item_id']);
} elseif (isset($_GET['property_id']) && !empty($_GET['property_id'])) {
    $target_id = intval($_GET['property_id']);
}

$target_type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : null;

$selected_unit = null;
$selected_unit_key = '';
$error_message = '';

// ==========================================
// 6. FETCH SELECTED APARTMENT ONLY
// ==========================================
if ($target_id && $target_type === 'apartment') {
    $selected_unit_key = "apartment_" . $target_id;

    $apartment_query = "
        SELECT
            a.id,
            a.apartment_price,
            a.floor_level,
            a.max_occupy,
            a.deposit_amount,
            rh.title,
            rh.township,
            rh.amenities,
            img.image_url
        FROM apartments a
        JOIN rental_houses rh ON a.rental_house_id = rh.id
        LEFT JOIN (
            SELECT rental_house_id, image_url
            FROM rental_house_images
            WHERE id IN (
                SELECT MIN(id)
                FROM rental_house_images
                GROUP BY rental_house_id
            )
        ) img ON rh.id = img.rental_house_id
        WHERE a.id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($apartment_query);
    $stmt->bind_param("i", $target_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $selected_unit = $result->fetch_assoc();

    $stmt->close();

    if (!$selected_unit) {
        $error_message = "Selected Apartment not found.";
    }
}

// ==========================================
// 7. FETCH SELECTED HOSTEL ONLY
// ==========================================
elseif ($target_id && $target_type === 'hostel') {
    $selected_unit_key = "hostel_" . $target_id;

    $hostel_query = "
        SELECT
            h.id,
            h.monthly_price,
            h.room_num,
            h.room_type,
            h.sub_unit,
            h.deposit_amount,
            rh.title,
            rh.township,
            rh.amenities,
            img.image_url
        FROM hostel_rooms h
        JOIN rental_houses rh ON h.rental_house_id = rh.id
        LEFT JOIN (
            SELECT rental_house_id, image_url
            FROM rental_house_images
            WHERE id IN (
                SELECT MIN(id)
                FROM rental_house_images
                GROUP BY rental_house_id
            )
        ) img ON rh.id = img.rental_house_id
        WHERE h.id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($hostel_query);
    $stmt->bind_param("i", $target_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $selected_unit = $result->fetch_assoc();

    $stmt->close();

    if (!$selected_unit) {
        $error_message = "Selected Hostel Room not found.";
    }
}

// ==========================================
// 8. INVALID PROPERTY
// ==========================================
else {
    $error_message = "No Apartment or Hostel Room selected for rent.";
}

// ==========================================
// 9. FORM SUBMISSION
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $form_user_id = intval($_POST['user_id'] ?? 0);
    $selected_unit_post = $_POST['unit'] ?? '';

    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $total_deposit_amount = isset($_POST['total_deposit']) ? floatval($_POST['total_deposit']) : 0.00;

    // Validate Unit
    $apartment_id = null;
    $hostel_room_id = null;

    if (strpos($selected_unit_post, 'apartment_') === 0) {
        $apartment_id = intval(str_replace('apartment_', '', $selected_unit_post));
    } elseif (strpos($selected_unit_post, 'hostel_') === 0) {
        $hostel_room_id = intval(str_replace('hostel_', '', $selected_unit_post));
    }

    // Validate Dates & Insert
    if (!$apartment_id && !$hostel_room_id) {
        $error_message = "Invalid Apartment or Hostel Room ID.";
    } elseif (!$start_date || !$end_date) {
        $error_message = "Please fill in both Start Date and End Date.";
    } elseif ($end_date <= $start_date) {
        $error_message = "End Date must be after Start Date.";
    } else {
        $insert_query = "
            INSERT INTO contracts (
                user_id,
                apartment_id,
                hostel_room_id,
                start_date,
                end_date,
                total_deposit_amount
            ) VALUES (?, ?, ?, ?, ?, ?)
        ";

        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param(
            "iiissd",
            $form_user_id,
            $apartment_id,
            $hostel_room_id,
            $start_date,
            $end_date,
            $total_deposit_amount
        );

        if ($insert_stmt->execute()) {
            $new_contract_id = $conn->insert_id;
            $insert_stmt->close();

            header("Location: installment_list.php?contract_id=" . $new_contract_id . "&from=homepage");
            exit();
        } else {
            $error_message = "Data save error: " . $insert_stmt->error;
        }

        $insert_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalHub - Contract Application</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Inter:wght@300;400;500;700&display=swap');

        .font-classic {
            font-family: 'Inter', sans-serif;
        }

        .title-classic {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>

<body class="bg-[#faf9f6] font-classic flex flex-col min-h-screen text-gray-800">

    <!-- HEADER -->
    <?php include 'homepageheader.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="flex-1 w-full max-w-6xl mx-auto py-10 px-4">

        <!-- PAGE HEADER -->
        <div class="bg-white border border-gray-200 rounded-md p-6 shadow-sm mb-8">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 title-classic">
                CONTRACT APPLICATION
            </h1>
            <p class="text-[11px] uppercase tracking-wider text-gray-400 mt-1">
                New Contract Request & Rental Unit Information
            </p>
        </div>

        <!-- ERROR MESSAGE -->
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 rounded-md text-sm">
                <?= htmlspecialchars($error_message) ?>
                <div class="mt-3">
                    <a href="renterhomepage.php" class="inline-block bg-slate-900 text-white px-4 py-2 text-xs">
                        ← Back to Home
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- CONTRACT FORM -->
        <?php if ($selected_unit): ?>
            <div id="contractTabContent" class="bg-white rounded-md shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-stone-50 border-b border-gray-200 px-6 py-4 text-slate-900 text-xs font-bold uppercase tracking-widest">
                    📝 Contract Application Form
                </div>

                <form class="p-6 space-y-6" action="" method="POST">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                    <input type="hidden" name="unit" value="<?= htmlspecialchars($selected_unit_key) ?>">

                    <!-- RENTER INFORMATION -->
                    <div class="bg-stone-50/60 border border-gray-200 rounded-md p-4 flex items-center justify-between">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">
                                Renter Name
                            </label>
                            <span class="text-sm font-bold text-slate-800">
                                <?= htmlspecialchars($renter_name) ?>
                            </span>
                        </div>
                        <div class="text-right">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">
                                Account Role
                            </label>
                            <span class="text-[10px] font-bold border border-gray-300 text-gray-600 bg-white px-2 py-0.5 rounded-sm uppercase tracking-wide">
                                <?= htmlspecialchars($user_data['role']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- SELECTED PROPERTY -->
                    <div class="space-y-2">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest border-b border-gray-100 pb-2">
                            🏢 Property Information
                        </h3>

                        <div class="bg-white border border-gray-200 rounded-md p-4 relative overflow-hidden shadow-sm">
                            <div class="absolute top-0 right-0 bg-slate-900 text-white text-[9px] font-bold px-3 py-1 rounded-bl uppercase tracking-wider">
                                <?= $target_type === 'apartment' ? 'Apartment' : 'Hostel Room' ?>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-5">
                                <!-- PROPERTY IMAGE -->
                                <div class="w-full sm:w-32 h-28 bg-stone-100 border border-gray-200 rounded overflow-hidden flex items-center justify-center shrink-0">
                                    <?php if (!empty($selected_unit['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($selected_unit['image_url']) ?>" alt="Property" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">No Image</span>
                                    <?php endif; ?>
                                </div>

                                <!-- PROPERTY DETAILS -->
                                <div class="flex-1 space-y-2">
                                    <h4 class="font-bold text-slate-800 text-base tracking-tight">
                                        <?= htmlspecialchars($selected_unit['title']) ?>
                                    </h4>
                                    <p class="text-[11px] text-gray-400 uppercase tracking-wide">
                                        📍 <?= htmlspecialchars($selected_unit['township']) ?>
                                    </p>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-[11px] text-gray-600 pt-3 border-t border-gray-100">
                                        <?php if ($target_type === 'apartment'): ?>
                                            <div>
                                                Floor: <span class="font-bold text-slate-800"><?= htmlspecialchars($selected_unit['floor_level']) ?></span>
                                            </div>
                                            <div>
                                                Occupancy: <span class="font-bold text-slate-800"><?= htmlspecialchars($selected_unit['max_occupy']) ?> Person(s)</span>
                                            </div>
                                            <div>
                                                Monthly Rent: <span class="font-bold text-slate-900"><?= number_format($selected_unit['apartment_price']) ?> MMK</span>
                                            </div>
                                        <?php elseif ($target_type === 'hostel'): ?>
                                            <div>
                                                Room Number: <span class="font-bold text-slate-800"><?= htmlspecialchars($selected_unit['room_num']) ?></span>
                                            </div>
                                            <div>
                                                Room Type: <span class="font-bold text-slate-800"><?= htmlspecialchars($selected_unit['room_type']) ?></span>
                                            </div>
                                            <div>
                                                Occupancy: <span class="font-bold text-slate-800"><?= htmlspecialchars($selected_unit['sub_unit']) ?></span>
                                            </div>
                                            <div>
                                                Monthly Rent: <span class="font-bold text-slate-900"><?= number_format($selected_unit['monthly_price']) ?> MMK</span>
                                            </div>
                                        <?php endif; ?>

                                        <div>
                                            Amenities: <span class="font-medium text-gray-500"><?= htmlspecialchars($selected_unit['amenities'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CONTRACT DATE AND DEPOSIT -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest border-b border-gray-100 pb-2">
                            📅 Contract Dates & Deposit Amount
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- START DATE -->
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1">
                                    Start Date *
                                </label>
                                <input type="date" name="start_date" min="<?= date('Y-m-d') ?>" required class="w-full text-xs px-3 py-2.5 rounded-sm border border-gray-300 focus:outline-none focus:border-slate-500 bg-white text-gray-700">
                            </div>

                            <!-- END DATE -->
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-1">
                                    End Date *
                                </label>
                                <input type="date" name="end_date" min="<?= date('Y-m-d') ?>" required class="w-full text-xs px-3 py-2.5 rounded-sm border border-gray-300 focus:outline-none focus:border-slate-500 bg-white text-gray-700">
                            </div>

                            <!-- DEPOSIT -->
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-bold text-gray-500 mb-1">
                                    Total Deposit Amount *
                                </label>
                                <div class="relative flex items-center">
                                    <input type="number" id="total_deposit_input" name="total_deposit" readonly required value="<?= htmlspecialchars($selected_unit['deposit_amount'] ?? 0) ?>" class="w-full text-xs px-3 py-2.5 pl-14 rounded-sm border border-gray-200 bg-stone-100 font-bold text-slate-700 select-none focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] font-bold select-none pointer-events-none">
                                        MMK
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex justify-end pt-4 border-t gap-2 border-gray-100">
                        <a href="renterhomepage.php" class="bg-gray-100 hover:bg-slate-900 hover:text-gray-200 text-gray-700 font-semibold text-xs uppercase tracking-wider px-4 py-2.5 border border-gray-300 rounded transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-6 py-3 rounded-sm uppercase tracking-wider transition-all shadow-sm">
                            🚀 Submit Contract Request
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-gray-200 py-6 text-center text-xs text-gray-400">
        &copy; 2026 RentalHub Platform.
    </footer>

</body>

</html>

<?php
$conn->close();
?>