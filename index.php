<?php
session_start();
$appointmentsFile = 'appointments.json';
$feedbackFile = 'feedback.json';

// Simple "laddered" logic for booking appointments
if (isset($_POST['book_appointment'])) {
    $data = json_decode(file_get_contents($appointmentsFile), true) ?? [];
    $data[] = [
        'client' => $_POST['name'],
        'service' => $_POST['service'],
        'date' => $_POST['date'],
        'time' => $_POST['time'],
        'status' => 'Pending'
    ];
    file_put_contents($appointmentsFile, json_encode($data, JSON_PRETTY_PRINT));
    echo "<script>alert('Appointment Booked! Confirmation sent via notification simulation.');</script>";
}

// Submit Feedback [cite: 81]
if (isset($_POST['submit_feedback'])) {
    $fb = json_decode(file_get_contents($feedbackFile), true) ?? [];
    $fb[] = ['user' => $_POST['fb_name'], 'msg' => $_POST['fb_msg']];
    file_put_contents($feedbackFile, json_encode($fb));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Elegance Salon | Premium Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <div style="color:var(--gold); font-weight:bold;">ELEGANCE</div>
        <a href="#home">Home</a>
        <a href="#services">Services</a>
        <a href="#book">Book Now</a>
        <a href="#feedback">Feedback</a>
        <a href="admin.php" style="border: 1px solid var(--gold); padding: 5px 10px;">Admin Panel</a>
    </nav>

    <header class="hero" id="home">
        <h1>Welcome to Elegance Salon</h1>
        [cite_start]<p>Expert hair styling, manicures, and facials[cite: 37].</p>
        <button class="btn" onclick="location.href='#book'">Reserve Your Slot</button>
    </header>

    <section class="section" id="book">
        [cite_start]<h2>Appointment Management [cite: 49]</h2>
        <form method="POST" class="card" style="max-width:500px; margin:auto;">
            <input type="text" name="name" placeholder="Full Name" required style="width:100%; padding:10px; margin:5px 0;">
            <select name="service" style="width:100%; padding:10px; margin:5px 0;">
                <option>Hair Styling</option>
                <option>Manicure</option>
                <option>Facial</option>
            </select>
            <input type="date" name="date" required style="width:100%; padding:10px; margin:5px 0;">
            <input type="time" name="time" required style="width:100%; padding:10px; margin:5px 0;">
            <button type="submit" name="book_appointment" class="btn">Book Now</button>
        </form>
    </section>

    <section class="section" id="feedback">
        [cite_start]<h2>Submit Feedback [cite: 81]</h2>
        <form method="POST" class="card">
            <input type="text" name="fb_name" placeholder="Your Name" required><br><br>
            <textarea name="fb_msg" placeholder="Your Experience" required style="width:100%; height:100px;"></textarea><br><br>
            <button type="submit" name="submit_feedback" class="btn">Send Feedback</button>
        </form>
    </section>

    <footer class="section" style="background:var(--dark); color:white; text-align:center;">
        [cite_start]<p><strong>Contact Us[cite: 79]:</strong> DevelopTeam | info@pakturk-it.com | Karachi, PK</p>
    </footer>
</body>
</html>