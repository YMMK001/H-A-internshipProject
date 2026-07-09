<?php
// 1. Database Connection Configuration
$host    = 'localhost';
$db      = 'intern_test';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// 2. SQL Query to combine both Apartments and Hostel Rooms (Updated to include unique Item IDs)
$sql = "
    SELECT 
        a.id AS item_id, 
        h.title, 
        h.township, 
        h.city, 
        h.amenities,
        'Apartment' AS type,
        a.floor_level AS unit_placement,
        a.max_occupy AS capacity_info,
        a.apartment_price AS price,
        a.is_available AS availability
    FROM rental_houses h
    INNER JOIN apartments a ON h.id = a.rental_house_id

    UNION ALL

    SELECT 
        r.id AS item_id, 
        h.title, 
        h.township, 
        h.city, 
        h.amenities,
        'Hostel' AS type,
        CONCAT(r.room_num, ' (', r.room_type, ')') AS unit_placement,
        r.sub_unit AS capacity_info,
        r.monthly_price AS price,
        r.is_available AS availability
    FROM rental_houses h
    INNER JOIN hostel_rooms r ON h.id = r.rental_house_id
";

$stmt = $pdo->query($sql);
$rentals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Listings Table</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen py-8">

    <div class="max-w-6xl mx-auto px-4">
        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">📊 နေရာစုံ ငှားရမ်းမှုစာရင်း ဇယား</h1>
                <p class="text-sm text-gray-500 mt-1">အိမ်ငှားများနှင့် ပိုင်ရှင်များ တိုက်ရိုက်နှိုင်းယှဉ်ကြည့်ရှုနိုင်သော ဇယားကွက်</p>
            </div>
            
            <a href="egcreate.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition-colors self-start sm:self-auto">
                ➕ အခန်းသစ်တင်ရန်
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs font-bold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="p-4 pl-6">အမျိုးအစား</th>
                            <th class="p-4">ခေါင်းစဉ် / တည်နေရာ</th>
                            <th class="p-4">အထပ် / အခန်း</th>
                            <th class="p-4">ပါဝင်သော ဝန်ဆောင်မှု (Amenities)</th>
                            <th class="p-4">လစဉ်ဈေးနှုန်း</th>
                            <th class="p-4">အခြေအနေ</th>
                            <th class="p-4 pr-6 text-center">လုပ်ဆောင်ချက်</th>
                        </tr>
                    </thead>
                    
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        <?php foreach ($rentals as $row): ?>
                            <?php 
                                // Handle row look opacity if rented out
                                $is_available = (int)$row['availability'] === 1;
                                $opacity_class = $is_available ? '' : 'opacity-60';
                            ?>
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="p-4 pl-6">
                                    <?php if ($row['type'] === 'Apartment'): ?>
                                        <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-md">🏢 Apartment</span>
                                    <?php else: ?>
                                        <span class="bg-purple-50 text-purple-700 text-xs font-semibold px-2.5 py-1 rounded-md">🏫 Hostel (အဆောင်)</span>
                                    <?php endif; ?>
                                </td>

                                <td class="p-4">
                                    <div class="font-semibold text-gray-900 <?= $opacity_class ?>"><?= htmlspecialchars($row['title']) ?></div>
                                    <div class="text-xs text-gray-400 mt-0.5">📍 <?= htmlspecialchars($row['township']) ?>၊ <?= htmlspecialchars($row['city']) ?>။</div>
                                </td>

                                <td class="p-4 <?= $opacity_class ?>">
                                    <span class="font-medium"><?= htmlspecialchars($row['unit_placement']) ?></span>
                                    <?php if ($row['type'] === 'Apartment' && $row['capacity_info']): ?>
                                        <span class="text-xs text-gray-400 block mt-0.5">Max: <?= htmlspecialchars($row['capacity_info']) ?> ဦး</span>
                                    <?php elseif ($row['type'] === 'Hostel' && $row['capacity_info']): ?>
                                        <div class="text-xs text-purple-500 mt-0.5">Sub-unit: <?= htmlspecialchars($row['capacity_info']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="p-4 <?= $opacity_class ?>">
                                    <div class="flex gap-1">
                                        <?php 
                                        // Splits words if stored comma separated e.g. "Aircon, Wi-Fi"
                                        $amenities = array_filter(explode(',', $row['amenities'] ?? ''));
                                        foreach ($amenities as $amenity): 
                                        ?>
                                            <span class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600"><?= htmlspecialchars(trim($amenity)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>

                                <td class="p-4 font-bold text-gray-900 <?= $opacity_class ?>">
                                    <?= number_format($row['price']) ?> <span class="text-xs font-normal text-gray-400">MMK</span>
                                </td>

                                <td class="p-4">
                                    <?php if ($is_available): ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-1 rounded-full">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> အားပါသည်
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                            🔒 ငှားရမ်းပြီး
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="p-4 pr-6 text-center">
                                    <?php if ($is_available): ?>
                                        <a href="view_details.php?id=<?= $row['item_id'] ?>&type=<?= $row['type'] ?>" 
                                           class="inline-block text-blue-600 hover:text-blue-800 font-semibold text-xs bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                                            ကြည့်ရန်
                                        </a>
                                    <?php else: ?>
                                        <button disabled class="text-gray-400 bg-gray-100 font-semibold text-xs px-3 py-1.5 rounded-lg cursor-not-allowed">
                                            ပိတ်ထားသည်
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>