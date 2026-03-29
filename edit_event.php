<?php
// 1. LOGIC FIRST: Start the session and connect to the database securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

// 2. STRICT SECURITY CHECK: Bounce them immediately if they aren't an admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    header("Location: dashboard.php");
    exit();
}

// 3. Check if an ID was actually passed in the URL
if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$event_id = intval($_GET['id']);
$message = "";

// 4. Handle the Update Form Submission
if (isset($_POST['update_event'])) {
    $event_name = trim($_POST['event_name']);
    $event_description = trim($_POST['event_description']);

    $update_stmt = $conn->prepare("UPDATE events SET event_name = ?, description = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $event_name, $event_description, $event_id);
    if ($update_stmt->execute()) {
        $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>✅ Event updated successfully!</p>";
    }
    $update_stmt->close();
}

// 5. Fetch the current event data to pre-fill the form
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 6. UI SECOND: NOW it is safe to include the visual header and draw the page
include "header.php";
?>

<div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 60vh;">
    <div class="card" style="width: 100%; max-width: 500px; padding: 30px; border-radius: 8px; border-top: 4px solid #007bff;">
        <h2 style="margin-top: 0; text-align: center;">Edit Event</h2>

        <?php echo $message; ?>

        <form method="POST">
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Event Name</label>
            <input type="text" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Description</label>
            <textarea name="event_description" required rows="6" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"><?php echo htmlspecialchars($event['description']); ?></textarea>

            <button name="update_event" style="width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 16px;">Save Changes</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="admin.php" style="color: #007bff; text-decoration: none; font-weight: bold;">&larr; Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>