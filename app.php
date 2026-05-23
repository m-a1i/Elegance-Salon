<?php
// ==================== PHP CORE BACKEND ENGINE ====================
// Start session to persist data across requests efficiently (simulating a database)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. INPUT SANITIZATION FUNCTION (Essential Security requirement to prevent XSS/Malicious inputs)
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// 2. SEED INITIAL BACKEND STATE (If session data doesn't exist yet)
if (!isset($_SESSION['clients'])) {
    $_SESSION['clients'] = [
        ['name' => 'Wednesday Addams', 'phone' => '555-1313', 'email' => 'wednesday@nevermore.edu', 'pref' => 'Black colors only. No human interaction.'],
        ['name' => 'Ajax Petropolus', 'phone' => '555-8899', 'email' => 'gorgonboy@nevermore.edu', 'pref' => 'Be careful with styling tools around beanie hat.']
    ];
}

if (!isset($_SESSION['inventory'])) {
    $_SESSION['inventory'] = [
        ['name' => 'Neon Cosmic Pink Hair Dye', 'stock' => 12, 'min' => 4, 'cost' => 18.50, 'supplier' => 'GlitterCo LLC'],
        ['name' => 'Vampire Claw Midnight Nail Polish', 'stock' => 2, 'min' => 5, 'cost' => 9.00, 'supplier' => 'AddamsWholesale'],
        ['name' => 'Pastel Lavender Glow Face Mud', 'stock' => 1, 'min' => 3, 'cost' => 22.00, 'supplier' => 'GorgonBeauty Supply']
    ];
}

if (!isset($_SESSION['staff'])) {
    $_SESSION['staff'] = [
        ['name' => 'Enid Sinclair', 'phone' => '555-0001', 'shift' => 'Sunset Neon (2 PM - 8 PM)', 'comm' => 20],
        ['name' => 'Yoko Tanaka', 'phone' => '555-4422', 'shift' => 'Midnight Wolves (8 PM - 2 AM)', 'comm' => 15]
    ];
}

if (!isset($_SESSION['appointments'])) {
    $_SESSION['appointments'] = [];
}

if (!isset($_SESSION['billing'])) {
    $_SESSION['billing'] = [];
}

if (!isset($_SESSION['feedback'])) {
    $_SESSION['feedback'] = [
        ['name' => 'Wednesday', 'rating' => '1', 'comment' => 'The room had entirely too much pink joy. Horrifying experience.']
    ];
}

if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'guest'; // Default role
}

// 3. POST CONTROLLER ARCHITECTURE (Handles all incoming form submissions on the server side)
$system_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Action: User Authentication (Access Control)
    if ($action === 'login') {
        $email = strtolower(trim($_POST['email']));
        if (strpos($email, 'admin') !== false) {
            $_SESSION['user_role'] = 'admin';
            $system_message = "🎉 Welcome back, Alpha Admin Boss! Full access unlocked.";
        } elseif (strpos($email, 'receptionist') !== false) {
            $_SESSION['user_role'] = 'receptionist';
            $system_message = "📅 Front Desk Reception access granted! Let's schedule, bestie!";
        } elseif (strpos($email, 'stylist') !== false) {
            $_SESSION['user_role'] = 'stylist';
            $system_message = "🎨 Creative Stylist view active. Go paint the town rainbow!";
        } else {
            $system_message = "❌ Authentication Failure: Unrecognized Bestie Credentials.";
        }
    }

    // Action: Logout
    if ($action === 'logout') {
        $_SESSION['user_role'] = 'guest';
        $system_message = "🔒 Logged out cleanly! Come back soon!";
    }

    // Action: Create Client Profile
    if ($action === 'create_client' && $_SESSION['user_role'] !== 'guest') {
        $_SESSION['clients'][] = [
            'name' => sanitize_input($_POST['name']),
            'phone' => sanitize_input($_POST['phone']),
            'email' => sanitize_input($_POST['email']),
            'pref' => sanitize_input($_POST['pref'] ?: 'No special requirements listed!')
        ];
        $system_message = "✨ Success: Added new Bestie Profile to the main registry!";
    }

    // Action: Create Inventory Item
    if ($action === 'create_inventory' && $_SESSION['user_role'] === 'admin') {
        $_SESSION['inventory'][] = [
            'name' => sanitize_input($_POST['name']),
            'stock' => intval($_POST['stock']),
            'min' => intval($_POST['min']),
            'cost' => floatval($_POST['cost']),
            'supplier' => sanitize_input($_POST['supplier'])
        ];
        $system_message = "📦 Stock Sheet parameters updated seamlessly!";
    }

    // Action: Create Staff Member
    if ($action === 'create_staff' && $_SESSION['user_role'] === 'admin') {
        $_SESSION['staff'][] = [
            'name' => sanitize_input($_POST['name']),
            'phone' => sanitize_input($_POST['phone']),
            'shift' => sanitize_input($_POST['shift']),
            'comm' => intval($_POST['comm'])
        ];
        $system_message = "💇‍♀️ Dream Team active roster appended successfully!";
    }

    // Action: Book Appointment + Generate Auto Invoice/Notifications
    if ($action === 'book_appointment' && $_SESSION['user_role'] !== 'guest') {
        $client = sanitize_input($_POST['client']);
        $service_data = explode('|', $_POST['service']); // Splitting name and price
        $service_name = sanitize_input($service_data[0]);
        $service_price = floatval($service_data[1]);
        $stylist = sanitize_input($_POST['stylist']);
        $time = sanitize_input($_POST['time']);

        $_SESSION['appointments'][] = [
            'client' => $client,
            'service' => $service_name,
            'stylist' => $stylist,
            'time' => $time,
            'status' => 'Confirmed & Active'
        ];

        // Simultaneous Automated Invoicing & Billing calculation engine
        $invoice_id = "INV-" . rand(100000, 900000);
        $_SESSION['billing'][] = [
            'id' => $invoice_id,
            'client' => $client,
            'service' => $service_name,
            'amount' => $service_price,
            'status' => 'Pending'
        ];

        $system_message = "🚨 AUTOMATED NOTIFICATIONS DISPATCHED! 💬 SMS confirmation out to $client. 📅 Sync pushed to $stylist for $time!";
    }

    // Action: Process Invoice Payment
    if ($action === 'pay_invoice' && $_SESSION['user_role'] !== 'guest') {
        $idx = intval($_POST['invoice_idx']);
        if (isset($_SESSION['billing'][$idx])) {
            $_SESSION['billing'][$idx]['status'] = 'Paid';
            $system_message = "💸 Cha-Ching! Payment processed. Receipt generated successfully.";
        }
    }

    // Action: Drop Application Feedback
    if ($action === 'submit_feedback') {
        $_SESSION['feedback'][] = [
            'name' => sanitize_input($_POST['name']),
            'rating' => sanitize_input($_POST['rating']),
            'comment' => sanitize_input($_POST['comment'])
        ];
        $system_message = "🦄 Thank you for your feedback! It was saved securely with server-side validation filters active.";
    }
}

// 4. COMPUTED REAL-TIME REPORTING & ANALYTICS DATA
$low_stock_alerts_array = [];
$total_revenue_accumulated = 0;
foreach ($_SESSION['inventory'] as $item) {
    if ($item['stock'] <= $item['min']) {
        $low_stock_alerts_array[] = "🚨 <strong>AUTO PO LOGGED:</strong> '" . $item['name'] . "' is low! Sent restock request to <strong>" . $item['supplier'] . "</strong>!";
    }
}
foreach ($_SESSION['billing'] as $bill) {
    if ($bill['status'] === 'Paid') {
        $total_revenue_accumulated += $bill['amount'];
    }
}

// Determine most popular service based on current appointments counter matrix
$popular_service_calculated = "No Data Booked Yet";
if (!empty($_SESSION['appointments'])) {
    $counts = [];
    foreach ($_SESSION['appointments'] as $app) {
        $counts[$app['service']] = ($counts[$app['service']] ?? 0) + 1;
    }
    arsort($counts);
    $popular_service_calculated = key($counts) . " (" . current($counts) . " Bookings)";
}

// Client Side Tab Controller helper state
$current_tab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'home';
if (isset($_POST['action']) && $_POST['action'] === 'login' && $system_message !== "❌ Authentication Failure: Unrecognized Bestie Credentials.") {
    $current_tab = 'home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✨ Elegance Salon x Enid Energy (PHP Edition) ✨</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --enid-pink: #FFB7B2;
            --enid-neon: #FF4081;
            --enid-blue: #B5EAD7;
            --enid-purple: #E0BBE4;
            --enid-yellow: #FFF2CC;
            --dark-text: #3D3D3D;
            --white: #FFFFFF;
        }

        * { box-sizing: border-box; font-family: 'Quicksand', sans-serif; margin: 0; padding: 0; }
        body { background-color: #FAF5F9; color: var(--dark-text); overflow-x: hidden; }
        h1, h2, h3 { font-family: 'Fredoka One', cursive; letter-spacing: 1px; }

        header {
            background: linear-gradient(135deg, var(--enid-pink), var(--enid-purple));
            color: var(--white); padding: 25px; text-align: center; border-bottom: 5px solid var(--enid-neon);
        }
        header h1 { font-size: 2.5rem; text-shadow: 3px 3px 0px var(--enid-neon); }

        nav { background-color: var(--white); display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; padding: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .nav-btn {
            background-color: transparent; border: 2px solid transparent; color: var(--dark-text);
            font-weight: 700; padding: 8px 16px; border-radius: 20px; cursor: pointer; transition: all 0.2s ease;
        }
        .nav-btn.active, .nav-btn:hover { background-color: var(--enid-pink); border-color: var(--enid-neon); color: var(--white); }

        #auth-status-bar {
            background-color: var(--enid-yellow); padding: 8px 20px; display: flex; justify-content: space-between; align-items: center; font-weight: 700; border-bottom: 2px dashed var(--enid-neon);
        }
        .logout-btn { background-color: var(--enid-neon); color: var(--white); border: none; padding: 5px 12px; border-radius: 12px; cursor: pointer; font-weight: 700; }

        .page { display: none; max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .page.active { display: block; }

        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .card { background-color: var(--white); border-radius: 20px; padding: 25px; box-shadow: 6px 6px 0px var(--enid-pink); border: 2px solid var(--enid-purple); position: relative; margin-bottom: 20px;}
        .card h3 { margin-bottom: 15px; color: var(--enid-neon); border-bottom: 2px solid var(--enid-purple); padding-bottom: 8px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 12px; border: 2px solid var(--enid-purple); border-radius: 12px; font-size: 1rem; background-color: #FCF9FC; }
        input:focus, select:focus, textarea:focus { border-color: var(--enid-neon); outline: none; }
        .btn-submit { background: linear-gradient(to right, var(--enid-neon), #FF6B8B); color: var(--white); border: none; width: 100%; padding: 14px; border-radius: 15px; font-size: 1.1rem; font-weight: 700; cursor: pointer; }

        .table-container { overflow-x: auto; margin-top: 15px; border-radius: 12px; border: 2px solid var(--enid-purple); }
        table { width: 100%; border-collapse: collapse; background-color: var(--white); text-align: left; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #EEE; }
        th { background-color: var(--enid-pink); color: var(--white); font-weight: 700; }
        tr:nth-child(even) { background-color: #FAF6FA; }

        .badge { display: inline-block; padding: 4px 8px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; }
        .badge-success { background-color: var(--enid-blue); color: #2E6F40; }
        .badge-danger { background-color: #FFC0CB; color: #B32424; }
        .system-alert { background-color: #FFEBEE; border-left: 5px solid #FF1744; color: #C62828; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-weight: 700; }
        .salon-gallery { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        footer { background-color: var(--dark-text); color: var(--white); padding: 30px; text-align: center; margin-top: 60px; font-size: 0.95rem; }
    </style>
</head>
<body>

    <header>
        <h1>✨ Elegance Salon PHP Engine ✨</h1>
        <p>🌈 Hyper-Optimized Server-Side Architecture Infused with Neon Sparkles! 🐾💅</p>
    </header>

    <!-- NAVIGATION MECHANISM VIA POST STATE PRESERVATION -->
    <nav>
        <button class="nav-btn <?php echo $current_tab === 'home' ? 'active' : ''; ?>" onclick="navTo('home')">🏠 Main Stage</button>
        <button class="nav-btn <?php echo $current_tab === 'auth' ? 'active' : ''; ?>" onclick="navTo('auth')">🎟️ Backstage Pass</button>
        
        <?php if ($_SESSION['user_role'] !== 'guest'): ?>
            <button class="nav-btn <?php echo $current_tab === 'appointments' ? 'active' : ''; ?>" onclick="navTo('appointments')">📅 Social Calendar</button>
            <button class="nav-btn <?php echo $current_tab === 'clients' ? 'active' : ''; ?>" onclick="navTo('clients')">👯‍♀️ Besties DB</button>
            <button class="nav-btn <?php echo $current_tab === 'billing' ? 'active' : ''; ?>" onclick="navTo('billing')">💸 Cha-Ching Tracker</button>
        <?php endif; ?>

        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <button class="nav-btn <?php echo $current_tab === 'inventory' ? 'active' : ''; ?>" onclick="navTo('inventory')">💅 Glam Supply</button>
            <button class="nav-btn <?php echo $current_tab === 'staff' ? 'active' : ''; ?>" onclick="navTo('staff')">💇‍♀️ Dream Team</button>
            <button class="nav-btn <?php echo $current_tab === 'analytics' ? 'active' : ''; ?>" onclick="navTo('analytics')">📊 Brainiac Insights</button>
        <?php endif; ?>
    </nav>

    <div id="auth-status-bar">
        <span>Current Bestie Mode: 
            <?php 
                if ($_SESSION['user_role'] === 'admin') echo '<span class="badge badge-danger">👑 System Admin Main Boss</span>';
                elseif ($_SESSION['user_role'] === 'receptionist') echo '<span class="badge badge-success">📅 Front Desk Receptionist</span>';
                elseif ($_SESSION['user_role'] === 'stylist') echo '<span class="badge" style="background-color: var(--enid-purple)">🎨 Creative Glam Stylist</span>';
                else echo '<span>Guest Browsing 👀</span>';
            ?>
        </span>
        <?php if ($_SESSION['user_role'] !== 'guest'): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="logout-btn">Eject! 🚀</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- DYNAMIC SYSTEM SYSTEM ALERT BOXES -->
    <div style="max-width: 1200px; margin: 15px auto 0 auto; padding: 0 20px;">
        <?php if (!empty($system_message)): ?>
            <div class="system-alert" style="background-color: #E8F5E9; border-left: 5px solid #2E7D32; color: #1B5E20;">
                <?php echo $system_message; ?>
            </div>
        <?php endif; ?>
        <?php foreach ($low_stock_alerts_array as $alert) { echo $alert; } ?>
    </div>

    <!-- Hidden Master Form to orchestrate client side button clicks to PHP router state -->
    <form id="navigation-form" method="POST" style="display:none;">
        <input type="hidden" name="current_tab" id="navigation-target">
    </form>

    <!-- ==================== PHP PAGE VIEW CORES ==================== -->

    <!-- VIEW: HOME PAGE -->
    <div class="page <?php echo $current_tab === 'home' ? 'active' : ''; ?>">
        <div class="card">
            <h2>🌸 Welcome to Elegance Salon, Bestie!</h2>
            <p style="margin-top: 10px; line-height: 1.6;">
                We deliver exceptional custom styles, flawless manicures, precision pedicures, and rejuvenating facials! 
                Our backend structure uses state-of-the-art data handling loops to optimize operations for our clients.
            </p>
            <div class="salon-gallery">
                <div style="background-color: var(--enid-pink); text-align: center; line-height: 150px; color: white; border-radius:15px; font-weight:bold;">💇‍♀️ Hair Styling Bay</div>
                <div style="background-color: var(--enid-purple); text-align: center; line-height: 150px; color: white; border-radius:15px; font-weight:bold;">💅 Magic Manicures</div>
                <div style="background-color: var(--enid-blue); text-align: center; line-height: 150px; color: white; border-radius:15px; font-weight:bold;">💆‍♀️ Glow-Up Facials</div>
            </div>
        </div>

        <div class="card">
            <h3>💌 Spill the Tea! Submit App Feedback</h3>
            <form method="POST">
                <input type="hidden" name="action" value="submit_feedback">
                <input type="hidden" name="current_tab" value="home">
                <div class="form-group">
                    <label>Your Name:</label>
                    <input type="text" name="name" required placeholder="Type your beautiful name here...">
                </div>
                <div class="form-group">
                    <label>Rating (1-5 Hearts):</label>
                    <select name="rating" required>
                        <option value="5">💖💖💖💖💖 (Obsessed)</option>
                        <option value="4">💖💖💖💖 (Super Cute)</option>
                        <option value="3">💖💖💖 (It's Okay)</option>
                        <option value="2">💖💖 (Meh)</option>
                        <option value="1">💔 (Scary Addams Vibes)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Your Review Comments:</label>
                    <textarea name="comment" required placeholder="Write a gorgeous comment..."></textarea>
                </div>
                <button type="submit" class="btn-submit">Dispatch Love Note! 🚀✨</button>
            </form>

            <div class="table-container">
                <table>
                    <thead><tr><th>Bestie</th><th>Hearts</th><th>The Tea</th></tr></thead>
                    <tbody>
                        <?php foreach ($_SESSION['feedback'] as $f): ?>
                            <tr>
                                <td><strong><?php echo $f['name']; ?></strong></td>
                                <td><?php echo str_repeat('💖', intval($f['rating'])); ?></td>
                                <td><?php echo $f['comment']; ?></td>
                            </tr>
                        /* <?php endphp ?> */
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- VIEW: AUTH LOGIN -->
    <div class="page <?php echo $current_tab === 'auth' ? 'active' : ''; ?>">
        <div class="card" style="max-width: 450px; margin: 0 auto;">
            <h3>🎟️ VIP Access Control Pass</h3>
            <p style="font-size: 0.85rem; color:#666; margin-bottom: 15px;">
                💡 Log in with: <strong>admin@elegance.com</strong>, <strong>receptionist@elegance.com</strong>, or <strong>stylist@elegance.com</strong>.
            </p>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Username / Email:</label>
                    <input type="email" name="email" required placeholder="bestie@elegance.com">
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn-submit">Unlock the Magic 🌟</button>
            </form>
        </div>
    </div>

    <!-- VIEW: APPOINTMENTS -->
    <div class="page <?php echo $current_tab === 'appointments' ? 'active' : ''; ?>">
        <div class="dashboard-grid">
            <div class="card">
                <h3>📅 Claim Your Time Slot</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="book_appointment">
                    <input type="hidden" name="current_tab" value="appointments">
                    <div class="form-group">
                        <label>Select Bestie Client:</label>
                        <select name="client" required>
                            <?php foreach ($_SESSION['clients'] as $c) { echo "<option value='{$c['name']}'>{$c['name']}</option>"; } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Glamour Service Type:</label>
                        <select name="service" required>
                            <option value="Neon Hair Styling|120">Neon Cosmic Hair Dye ($120)</option>
                            <option value="Glitter Manicure|45">Glitter Manicure ($45)</option>
                            <option value="Wolf-Out Pedicure|55">Wolf-Out Pedicure ($55)</option>
                            <option value="Pastel Glow Facial|80">Pastel Glow Facial ($80)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assigned Dream-Team Artist:</label>
                        <select name="stylist" required>
                            <?php foreach ($_SESSION['staff'] as $s) { echo "<option value='{$s['name']}'>{$s['name']}</option>"; } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date & Target Time:</label>
                        <input type="datetime-local" name="time" required>
                    </div>
                    <button type="submit" class="btn-submit">Secure Booking & Send Alerts! 🦄</button>
                </form>
            </div>

            <div class="card" style="grid-column: span 2;">
                <h3>🗺️ Main Grid & Calendar Log</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Client</th><th>Service</th><th>Stylist</th><th>Date/Time</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['appointments'] as $idx => $a): ?>
                                <tr>
                                    <td>#00<?php echo $idx+1; ?></td>
                                    <td><strong><?php echo $a['client']; ?></strong></td>
                                    <td><?php echo $a['service']; ?></td>
                                    <td><?php echo $a['stylist']; ?></td>
                                    <td><code><?php echo str_replace('T', ' ', $a['time']); ?></code></td>
                                    <td><span class="badge badge-success"><?php echo $a['status']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW: CLIENTS -->
    <div class="page <?php echo $current_tab === 'clients' ? 'active' : ''; ?>">
        <div class="dashboard-grid">
            <div class="card">
                <h3>✍️ Add New Client Profile</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create_client">
                    <input type="hidden" name="current_tab" value="clients">
                    <div class="form-group">
                        <label>Full Legal Name:</label>
                        <input type="text" name="name" required placeholder="Wednesday Addams">
                    </div>
                    <div class="form-group">
                        <label>Phone Line:</label>
                        <input type="tel" name="phone" required placeholder="555-0192">
                    </div>
                    <div class="form-group">
                        <label>Email Address:</label>
                        <input type="email" name="email" required placeholder="darkness@nevermore.edu">
                    </div>
                    <div class="form-group">
                        <label>Style Preferences:</label>
                        <textarea name="pref" placeholder="Refuses any color palette containing pink..."></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Welcome into the Circle! 👯‍♀️</button>
                </form>
            </div>

            <div class="card" style="grid-column: span 2;">
                <h3>🔍 Centralized Client Database</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Name</th><th>Phone</th><th>Email</th><th>Style Preferences</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['clients'] as $c): ?>
                                <tr>
                                    <td><strong><?php echo $c['name']; ?></strong></td>
                                    <td><?php echo $c['phone']; ?></td>
                                    <td><?php echo $c['email']; ?></td>
                                    <td><em><?php echo $c['pref']; ?></em></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW: INVENTORY -->
    <div class="page <?php echo $current_tab === 'inventory' ? 'active' : ''; ?>">
        <div class="dashboard-grid">
            <div class="card">
                <h3>📦 Log New Stock Items</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create_inventory">
                    <input type="hidden" name="current_tab" value="inventory">
                    <div class="form-group">
                        <label>Item Name:</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Level:</label>
                        <input type="number" name="stock" required>
                    </div>
                    <div class="form-group">
                        <label>Alert Level:</label>
                        <input type="number" name="min" required>
                    </div>
                    <div class="form-group">
                        <label>Unit Cost ($):</label>
                        <input type="number" name="cost" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Supplier Wholesaler:</label>
                        <input type="text" name="supplier" required>
                    </div>
                    <button type="submit" class="btn-submit">Vault Stock Entry 🔒</button>
                </form>
            </div>

            <div class="card" style="grid-column: span 2;">
                <h3>🚨 Active Stock Sheets & Alerts</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Item Block Name</th><th>Stock</th><th>Minimum Alert</th><th>Unit Cost</th><th>Wholesaler</th><th>Health Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['inventory'] as $item): 
                                $isLow = $item['stock'] <= $item['min'];
                            ?>
                                <tr>
                                    <td><strong><?php echo $item['name']; ?></strong></td>
                                    <td><?php echo $item['stock']; ?> units</td>
                                    <td><?php echo $item['min']; ?> units</td>
                                    <td>$<?php echo number_format($item['cost'], 2); ?></td>
                                    <td><?php echo $item['supplier']; ?></td>
                                    <td><?php echo $isLow ? '<span class="badge badge-danger">🚨 LOW STOCK ALERT</span>' : '<span class="badge badge-success">Healthy Solid</span>'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW: STAFF MANAGEMENT -->
    <div class="page <?php echo $current_tab === 'staff' ? 'active' : ''; ?>">
        <div class="dashboard-grid">
            <div class="card">
                <h3>🏷️ Recruit Stylist Profile</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create_staff">
                    <input type="hidden" name="current_tab" value="staff">
                    <div class="form-group">
                        <label>Stylist Call Name:</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Line:</label>
                        <input type="tel" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label>Assigned Shift Blocks:</label>
                        <select name="shift" required>
                            <option value="Morning Sparkle (8 AM - 2 PM)">Morning Sparkle (8 AM - 2 PM)</option>
                            <option value="Sunset Neon (2 PM - 8 PM)">Sunset Neon (2 PM - 8 PM)</option>
                            <option value="Midnight Wolves (8 PM - 2 AM)">Midnight Wolves (8 PM - 2 AM)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Commission Rate (%):</label>
                        <input type="number" name="comm" required>
                    </div>
                    <button type="submit" class="btn-submit">Authorize Roster Addition!</button>
                </form>
            </div>

            <div class="card" style="grid-column: span 2;">
                <h3>💇‍♀️ Dream Team Roster & Commission Statements</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Name</th><th>Contact Line</th><th>Shift Block</th><th>Commission Cut</th><th>Calculated Commission Payout</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['staff'] as $s): 
                                $calculated_comm_payout = 0;
                                foreach ($_SESSION['appointments'] as $a) {
                                    if ($a['stylist'] === $s['name']) {
                                        // Simple lookup calculation logic based on standard pricing matching
                                        $price = 50;
                                        if (strpos($a['service'], 'Hair') !== false) $price = 120;
                                        if (strpos($a['service'], 'Manicure') !== false) $price = 45;
                                        if (strpos($a['service'], 'Pedicure') !== false) $price = 55;
                                        if (strpos($a['service'], 'Facial') !== false) $price = 80;
                                        $calculated_comm_payout += ($price * ($s['comm'] / 100));
                                    }
                                }
                            ?>
                                <tr>
                                    <td><strong><?php echo $s['name']; ?></strong></td>
                                    <td><?php echo $s['phone']; ?></td>
                                    <td><span class="badge" style="background-color: var(--enid-yellow)"><?php echo $s['shift']; ?></span></td>
                                    <td><?php echo $s['comm']; ?>% cut</td>
                                    <td style="color: var(--enid-neon); font-weight:700;">$<?php echo number_format($calculated_comm_payout, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW: BILLING/INVOICING -->
    <div class="page <?php echo $current_tab === 'billing' ? 'active' : ''; ?>">
        <div class="card">
            <h3>💸 Live Transaction Ledger & Invoices</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>Invoice Code</th><th>Target Client</th><th>Service Value</th><th>Gross Owed</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['billing'] as $idx => $b): ?>
                            <tr>
                                <td><code><?php echo $b['id']; ?></code></td>
                                <td><?php echo $b['client']; ?></td>
                                <td><?php echo $b['service']; ?></td>
                                <td><strong>$<?php echo number_format($b['amount'], 2); ?></strong></td>
                                <td><span class="badge <?php echo $b['status'] === 'Paid' ? 'badge-success' : 'badge-danger'; ?>"><?php echo $b['status']; ?></span></td>
                                <td>
                                    <?php if ($b['status'] === 'Pending'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="pay_invoice">
                                            <input type="hidden" name="invoice_idx" value="<?php echo $idx; ?>">
                                            <input type="hidden" name="current_tab" value="billing">
                                            <button type="submit" style="background-color:var(--enid-blue); border:none; padding:5px 10px; border-radius:8px; font-weight:700; cursor:pointer;">Process Payment 💳</button>
                                        </form>
                                    <?php else: ?>
                                        🧾 Receipt Dispatched
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- VIEW: ANALYTICS REPORTS -->
    <div class="page <?php echo $current_tab === 'analytics' ? 'active' : ''; ?>">
        <div class="dashboard-grid">
            <div class="card">
                <h3>👑 Peak Booking Service</h3>
                <h1 style="color: var(--enid-neon); font-size: 1.8rem; margin-top:10px;"><?php echo $popular_service_calculated; ?></h1>
            </div>
            <div class="card">
                <h3>💰 Total Revenue Accumulated</h3>
                <h1 style="color: var(--enid-blue); font-size: 2rem; margin-top:10px; text-shadow: 1px 1px black;">$<?php echo number_format($total_revenue_accumulated, 2); ?></h1>
            </div>
            <div class="card">
                <h3>🚨 Low Stock Supplies</h3>
                <h1 style="color: red; font-size: 2rem; margin-top:10px;"><?php echo count($low_stock_alerts_array); ?> Items</h1>
            </div>
        </div>
    </div>

    <footer>
        <p>✨ <strong>Elegance Salon Engine Enterprise Edition (PHP Runtime)</strong> — Infused with pure optimization & neon sparks ✨</p>
        <p style="margin-top: 10px; font-size: 0.8rem; opacity: 0.8;">
            Developer Headquarters Communications Hub: 
            An application by Bestie Web Designs & Co. | 📧 support@bestiewebdesigns.local | 📍 101 Nevermore Academy Lane, Quad Wing B
        </p>
    </footer>

    <!-- HELPER CLIENT SIDE APP ROUTER FORM TRIGGER -->
    <script>
        function navTo(tabId) {
            document.getElementById('navigation-target').value = tabId;
            document.getElementById('navigation-form').submit();
        }
    </script>
</body>
</html>
