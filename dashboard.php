<?php
// 1. LOGIC FIRST: Start the session and connect to the database.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

// 2. SECURITY CHECK: If they are not logged in, bounce them to the login page immediately.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 3. SMART ROUTING: If an Admin accidentally clicks "Dashboard", instantly bounce them to the Admin Panel.
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin')) {
    header("Location: admin.php");
    exit();
}

// 4. FETCH DATA: Get the user's name.
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 5. UI SECOND: It is now safe to draw the webpage.
include "header.php";
?>

<div class="container" style="min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">

    <h1 style="font-size: 2.5em; margin-bottom: 10px; color: var(--text-color);">Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h1>

    <p style="font-size: 1.2em; color: var(--text-color); opacity: 0.8; margin-bottom: 40px;">What would you like to explore today?</p>

    <div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">

        <a href="clubs.php" class="card" style="padding: 30px; min-width: 200px; text-decoration: none; color: var(--text-color); border-top: 4px solid #007bff; transition: transform 0.2s;">
            <div style="font-size: 2.5em; margin-bottom: 10px;">👥</div>
            <h3 style="margin: 0; color: inherit;">Browse Clubs</h3>
            <p style="color: inherit; opacity: 0.7; font-size: 0.9em; margin-top: 10px;">Find your community.</p>
        </a>

        <a href="events.php" class="card" style="padding: 30px; min-width: 200px; text-decoration: none; color: var(--text-color); border-top: 4px solid #28a745; transition: transform 0.2s;">
            <div style="font-size: 2.5em; margin-bottom: 10px;">📅</div>
            <h3 style="margin: 0; color: inherit;">Upcoming Events</h3>
            <p style="color: inherit; opacity: 0.7; font-size: 0.9em; margin-top: 10px;">See what's happening.</p>
        </a>

        <a href="profile.php" class="card" style="padding: 30px; min-width: 200px; text-decoration: none; color: var(--text-color); border-top: 4px solid #ffc107; transition: transform 0.2s;">
            <div style="font-size: 2.5em; margin-bottom: 10px;">⚙️</div>
            <h3 style="margin: 0; color: inherit;">My Profile</h3>
            <p style="color: inherit; opacity: 0.7; font-size: 0.9em; margin-top: 10px;">Manage your RSVPs.</p>
        </a>

    </div>
</div>

<style>
    /* Ensures the card itself doesn't have a white background in dark mode */
    .card {
        background: var(--card-bg);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
    }
</style>

<?php include "footer.php"; ?>