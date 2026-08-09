<?php
session_start();
require_once '../config/db.php'; 

// Renter login ဝင်မဝင် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ==========================================
// 1. AUTOMATED NOTIFICATION GENERATOR
// ==========================================
function createNotificationIfNotExists($conn, $user_id, $type, $title, $message) {
    $check_stmt = $conn->prepare("SELECT id FROM notifications WHERE user_id = ? AND type = ? AND DATE(created_at) = CURDATE()");
    $check_stmt->bind_param("is", $user_id, $type);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        $insert_stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $insert_stmt->bind_param("isss", $user_id, $type, $title, $message);
        $insert_stmt->execute();
    }
}

// A. စာချုပ် သက်တမ်းကုန်ဆုံးမှုများ စစ်ဆေးခြင်း (30, 14, 3, 0 Days)
$sql_contracts = "
    SELECT id, end_date, DATEDIFF(end_date, CURDATE()) AS days_left 
    FROM contracts 
    WHERE user_id = ? AND end_date >= CURDATE()
";
$stmt_contracts = $conn->prepare($sql_contracts);
$stmt_contracts->bind_param("i", $user_id);
$stmt_contracts->execute();
$result_contracts = $stmt_contracts->get_result();

while ($row = $result_contracts->fetch_assoc()) {
    $days = intval($row['days_left']);

    if ($days === 30) {
        createNotificationIfNotExists(
            $conn, $user_id, 'lease_inquiry_30',
            'စာချုပ်သက်တမ်းတိုးခြင်း စုံစမ်းမေးမြန်းခြင်း (၃၀ ရက်အလို)',
            'သင်၏ အိမ်ငှားစာချုပ်သည် ရက်ပေါင်း ၃၀ အတွင်း သက်တမ်းကုန်ဆုံးတော့မည်ဖြစ်ပါသည်။ စာချုပ်ဆက်လက်တိုးမည် သို့မဟုတ် ထွက်ခွာမည်ကို အကြောင်းပြန်ပေးပါရန်။'
        );
    } elseif ($days === 14) {
        createNotificationIfNotExists(
            $conn, $user_id, 'lease_reminder_14',
            'စာချုပ်သက်တမ်းတိုး သို့မဟုတ် အခန်းအပ်နှံခြင်း အသိပေးချက် (၁၄ ရက်အလို)',
            'စာချုပ်သက်တမ်းကုန်ရန် ၁၄ ရက်သာ လိုပါတော့သည်။ စာချုပ်သက်တမ်းတိုးရန် သို့မဟုတ် အခန်းအပ်နှံ စစ်ဆေးခြင်းဆိုင်ရာ အသေးစိတ်အချက်အလက်များကို ပြုလုပ်ပေးပါရန်။'
        );
    } elseif ($days === 3) {
        createNotificationIfNotExists(
            $conn, $user_id, 'lease_final_3',
            'နောက်ဆုံး သတိပေးချက် (၃ ရက်အလို)',
            'စာချုပ်သက်တမ်းကုန်ရန် ၃ ရက်သာ လိုပါတော့သည်။ စာချုပ်သက်တမ်းကုန်ဆုံးမည့် ရက်စွဲနှင့် အခန်းလွှဲပြောင်းပေးအပ်ရမည့် အချိန်ဇယားကို အတည်ပြုပေးပါရန်။'
        );
    } elseif ($days === 0) {
        createNotificationIfNotExists(
            $conn, $user_id, 'lease_expired',
            'စာချုပ်သက်တမ်း ကုန်ဆုံးသွားပါပြီ',
            'သင်၏ အိမ်ငှားစာချုပ် သက်တမ်း ကုန်ဆုံးသွားပြီ ဖြစ်ပါသည်။'
        );
    }
}

// B. ငွေပေးချေရန် ရက်စွဲ သတိပေးချက်များ (၃ ရက်အလို နှင့် ရက်လွန် ပေးချေမှုများ)
$sql_payments = "
    SELECT i.id, i.amount_to_pay, i.due_date, DATEDIFF(i.due_date, CURDATE()) AS days_left 
    FROM installments i
    JOIN contracts c ON i.contract_id = c.id
    WHERE c.user_id = ? 
      AND i.status IN ('unpaid', 'partially_paid') 
      AND (DATEDIFF(i.due_date, CURDATE()) = 3 OR i.due_date < CURDATE())
";
$stmt_payments = $conn->prepare($sql_payments);
$stmt_payments->bind_param("i", $user_id);
$stmt_payments->execute();
$result_payments = $stmt_payments->get_result();

while ($row = $result_payments->fetch_assoc()) {
    $amount = number_format($row['amount_to_pay']);
    $due_date = date('d M Y', strtotime($row['due_date']));
    $days_left = intval($row['days_left']);

    if ($days_left === 3) {
        // 3 Days Before Due Date
        createNotificationIfNotExists(
            $conn, $user_id, 'payment_due',
            'ငွေပေးချေရန် သတိပေးချက် (၃ ရက်အလို)',
            "ကျန်ရှိနေသော အိမ်ငှားခ ငွေပမာဏ {$amount} MMK ကို {$due_date} မတိုင်မီ ပေးချေပေးပါရန် အကြောင်းကြားအပ်ပါသည်။"
        );
    } elseif ($days_left < 0) {
        // Overdue Payment
        $overdue_days = abs($days_left);
        createNotificationIfNotExists(
            $conn, $user_id, 'payment_overdue',
            'ရက်လွန် ငွေပေးချေရန် အကြောင်းကြားချက်',
            "ကျန်ရှိနေသော အိမ်ငှားခ ငွေပမာဏ {$amount} MMK သည် {$due_date} တွင် ပေးချေရမည်ဖြစ်ပြီး {$overdue_days} ရက် ရက်လွန်နေပြီ ဖြစ်ပါသည်။ ကျေးဇူးပြု၍ အမြန်ဆုံး ပေးချေပေးပါရန်။"
        );
    }
}

// ==========================================
// 2. AJAX REQUEST HANDLING
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'mark_as_read' && isset($_POST['notification_id'])) {
        $notif_id = intval($_POST['notification_id']);
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notif_id, $user_id);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
        exit();
    }

    if ($_POST['action'] === 'mark_all_read') {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
        exit();
    }
}

// Unread Count
$count_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$unread_count = $count_stmt->get_result()->fetch_assoc()['unread_count'];

// Fetch Notifications List
$filter = isset($_GET['filter']) && $_GET['filter'] === 'unread' ? "AND is_read = 0" : "";
$sql = "SELECT * FROM notifications WHERE user_id = ? {$filter} ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>အသိပေးချက်များ (Notifications)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <?php 
        if (file_exists('homepageheader.php')) {
            include 'homepageheader.php';
        }
    ?>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-bell text-indigo-600"></i> အသိပေးချက်များ
                    <?php if ($unread_count > 0): ?>
                        <span id="unread-badge" class="bg-red-500 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full">
                            <?= $unread_count ?> မဖတ်ရသေးပါ
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="text-sm text-gray-500 mt-1">စာချုပ်နှင့် ငွေပေးချေမှုဆိုင်ရာ နောက်ဆုံးရ အကြောင်းကြားချက်များ</p>
            </div>

            <button onclick="markAllAsRead()" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition">
                <i class="fa-solid fa-check-double"></i> အားလုံးကို ဖတ်ပြီးသားလုပ်မည်
            </button>
        </div>

        <div class="flex border-b border-gray-200 mb-6">
            <a href="?" class="py-2 px-4 font-medium text-sm border-b-2 <?= !isset($_GET['filter']) ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                အားလုံး
            </a>
            <a href="?filter=unread" class="py-2 px-4 font-medium text-sm border-b-2 <?= (isset($_GET['filter']) && $_GET['filter'] === 'unread') ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                မဖတ်ရသေးသည်များ
            </a>
        </div>

        <div class="space-y-3" id="notification-container">
            <?php if ($notifications->num_rows > 0): ?>
                <?php while ($row = $notifications->fetch_assoc()): ?>
                    <?php 
                        $iconClass = "fa-bell text-gray-500 bg-gray-100";
                        $type = $row['type'];

                        if ($type === 'lease_inquiry_30') {
                            $iconClass = "fa-calendar-check text-blue-600 bg-blue-100";
                        } elseif ($type === 'lease_reminder_14') {
                            $iconClass = "fa-file-signature text-amber-600 bg-amber-100";
                        } elseif ($type === 'lease_final_3') {
                            $iconClass = "fa-triangle-exclamation text-orange-600 bg-orange-100";
                        } elseif ($type === 'lease_expired') {
                            $iconClass = "fa-file-circle-xmark text-rose-600 bg-rose-100";
                        } elseif ($type === 'payment_due') {
                            $iconClass = "fa-clock text-amber-600 bg-amber-100";
                        } elseif ($type === 'payment_overdue') {
                            $iconClass = "fa-circle-exclamation text-red-600 bg-red-100";
                        } elseif ($type === 'payment_success') {
                            $iconClass = "fa-circle-check text-emerald-600 bg-emerald-100";
                        }
                    ?>
                    
                    <div id="notif-<?= $row['id'] ?>" 
                         onclick="markAsRead(<?= $row['id'] ?>)"
                         class="p-4 rounded-xl border transition cursor-pointer flex items-start gap-4 shadow-sm hover:shadow-md <?= $row['is_read'] ? 'bg-white border-gray-200' : 'bg-indigo-50/60 border-indigo-200' ?>">
                        
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 <?= $iconClass ?>">
                            <i class="fa-solid <?= explode(' ', $iconClass)[0] ?> text-lg"></i>
                        </div>

                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="font-semibold text-gray-900 <?= $row['is_read'] ? '' : 'text-indigo-950 font-bold' ?>">
                                    <?= htmlspecialchars($row['title']) ?>
                                </h3>
                                <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                                    <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                <?= nl2br(htmlspecialchars($row['message'])) ?>
                            </p>
                        </div>

                        <?php if (!$row['is_read']): ?>
                            <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full flex-shrink-0 mt-2" id="dot-<?= $row['id'] ?>"></div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-xl border border-gray-100">
                    <i class="fa-regular fa-bell-slash text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500 font-medium">အသိပေးချက်များ မရှိသေးပါ။</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function markAsRead(id) {
            const card = document.getElementById(`notif-${id}`);
            const dot = document.getElementById(`dot-${id}`);
            
            if (dot) {
                const formData = new FormData();
                formData.append('action', 'mark_as_read');
                formData.append('notification_id', id);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        card.classList.remove('bg-indigo-50/60', 'border-indigo-200');
                        card.classList.add('bg-white', 'border-gray-200');
                        dot.remove();
                        updateBadgeCount();
                    }
                })
                .catch(err => console.error(err));
            }
        }

        function markAllAsRead() {
            const formData = new FormData();
            formData.append('action', 'mark_all_read');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(err => console.error(err));
        }

        function updateBadgeCount() {
            const badge = document.getElementById('unread-badge');
            if (badge) {
                let current = parseInt(badge.innerText);
                if (current > 1) {
                    badge.innerText = `${current - 1} မဖတ်ရသေးပါ`;
                } else {
                    badge.remove();
                }
            }
        }
    </script>
</body>
</html>