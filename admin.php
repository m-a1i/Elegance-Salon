<?php
// Initializing data files
$inventoryFile = 'inventory.json';
$staffFile = 'staff.json';
$appointments = json_decode(file_get_contents('appointments.json'), true) ?? [];

if (!file_exists($inventoryFile)) {
    file_put_contents($inventoryFile, json_encode([['item' => 'Hair Gel', 'stock' => 2, 'cost' => 15]]));
}
$inventory = json_decode(file_get_contents($inventoryFile), true);

// Inventory Management logic [cite: 57]
$lowStockAlert = false;
foreach ($inventory as $item) {
    if ($item['stock'] < 5) $lowStockAlert = true; // Alert for low inventory [cite: 59]
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard | Elegance Salon</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav style="background:#000;">
        <a href="index.php">View Website</a>
        <span style="color:var(--gold);">ADMIN SYSTEM</span>
    </nav>

    <div class="section">
        [cite_start]<h1>Management Console [cite: 77]</h1>

        <?php if($lowStockAlert): ?>
            [cite_start]<div class="alert">⚠️ <strong>Critical Update:</strong> Some items are low in inventory! [cite: 59, 76]</div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                [cite_start]<h3>Scheduled Appointments [cite: 66]</h3>
                <ul>
                    <?php foreach($appointments as $app): ?>
                        <li><?php echo "{$app['client']} - {$app['service']} ({$app['time']})"; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h3>Inventory Control [cite: 58]</h3>
                <table width="100%">
                    <tr><th>Item</th><th>Stock</th></tr>
                    <?php foreach($inventory as $inv): ?>
                        <tr><td><?php echo $inv['item']; ?></td><td><?php echo $inv['stock']; ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div class="card">
                <h3>Staff & Commissions [cite: 64]</h3>
                <p>Stylist A: $150 (10% Commission)</p>
                <p>Stylist B: $200 (10% Commission)</p>
            </div>
        </div>

        <div class="card" style="margin-top:20px;">
            <h3>Reporting & Analytics [cite: 69]</h3>
            <p><strong>Popular Service:</strong> Hair Styling [cite: 70]</p>
            <p><strong>Total Sales:</strong> Simulation Mode Active</p>
        </div>
    </div>
</body>
</html>