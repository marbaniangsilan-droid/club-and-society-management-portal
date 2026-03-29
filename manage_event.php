<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['name'])) {
    header("Location: admin.php");
    exit();
}

$event_name = urldecode($_GET['name']);
$message = "";

// 1. Handle Sending an Announcement
if (isset($_POST['send_announcement'])) {
    $announcement_text = trim($_POST['announcement_text']);

    if (!empty($announcement_text)) {
        $ann_stmt = $conn->prepare("INSERT INTO event_announcements (event_name, message) VALUES (?, ?)");
        $ann_stmt->bind_param("ss", $event_name, $announcement_text);
        if ($ann_stmt->execute()) {
            $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>📢 Announcement broadcasted to all attendees!</p>";
        } else {
            $message = "<p style='color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px;'>❌ Error sending announcement.</p>";
        }
        $ann_stmt->close();
    }
}

// 2. Handle Removing an Attendee
if (isset($_POST['remove_attendee'])) {
    $email_to_remove = $_POST['user_email'];
    $remove_stmt = $conn->prepare("DELETE FROM event_registration WHERE event_name = ? AND user_email = ?");
    $remove_stmt->bind_param("ss", $event_name, $email_to_remove);
    if ($remove_stmt->execute()) {
        $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>✅ Attendee removed from event.</p>";
    }
    $remove_stmt->close();
}

// 3. Fetch Attendee Roster
$attendees_stmt = $conn->prepare("
    SELECT users.name, users.email, users.department 
    FROM users 
    JOIN event_registration ON users.email = event_registration.user_email 
    WHERE event_registration.event_name = ?
    ORDER BY users.name ASC
");
$attendees_stmt->bind_param("s", $event_name);
$attendees_stmt->execute();
$attendees = $attendees_stmt->get_result();
$attendees_stmt->close();

include "header.php";
?>

<div class="container">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2>Manage: <?php echo htmlspecialchars($event_name); ?></h2>
        <a href="admin.php" style="color: #007bff; text-decoration: none;">&larr; Back to Admin Dashboard</a>
    </div>

    <?php echo $message; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; margin-bottom: 30px;">

        <div class="card" style="flex: 1; min-width: 300px; padding: 20px; border-top: 4px solid #17a2b8;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Broadcast Announcement</h3>
            <p style="font-size: 0.9em; color: #666;">Send a message to everyone who RSVP'd for this event.</p>

            <form method="POST">
                <textarea name="announcement_text" placeholder="Type your notice, schedule change, or update here..." required rows="4" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>
                <button name="send_announcement" style="width: 100%; padding: 10px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Send to Attendees</button>
            </form>
        </div>

        <div class="card" style="flex: 2; min-width: 300px; padding: 20px; overflow-x: auto;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Current Attendees</h3>

            <?php if ($attendees->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <tr style="background-color: var(--header-bg); color: white;">
                        <th style="padding: 10px;">Name</th>
                        <th style="padding: 10px;">Email</th>
                        <th style="padding: 10px; text-align: right;">Action</th>
                    </tr>
                    <?php while ($attendee = $attendees->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #ccc;">
                            <td style="padding: 10px;"><?php echo htmlspecialchars($attendee['name']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($attendee['email']); ?></td>
                            <td style="padding: 10px; text-align: right;">
                                <form method="POST" onsubmit="return confirm('Remove this student from the RSVP list?');">
                                    <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($attendee['email']); ?>">
                                    <button name="remove_attendee" style="background: #ffc107; color: #333; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.9em;">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">No students have RSVP'd for this event yet.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include "footer.php"; ?>