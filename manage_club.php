<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$club_id = intval($_GET['id']);
$message = "";

// 1. Handle Sending an Announcement
if (isset($_POST['send_announcement'])) {
    $announcement_text = trim($_POST['announcement_text']);

    if (!empty($announcement_text)) {
        $ann_stmt = $conn->prepare("INSERT INTO club_announcements (club_id, message) VALUES (?, ?)");
        $ann_stmt->bind_param("is", $club_id, $announcement_text);
        if ($ann_stmt->execute()) {
            $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>📢 Announcement broadcasted to all members!</p>";
        } else {
            $message = "<p style='color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 5px;'>❌ Error sending announcement.</p>";
        }
        $ann_stmt->close();
    }
}

// 2. Handle Removing a Member
if (isset($_POST['remove_member'])) {
    $user_id_to_remove = intval($_POST['user_id']);
    $remove_stmt = $conn->prepare("DELETE FROM club_members WHERE club_id = ? AND user_id = ?");
    $remove_stmt->bind_param("ii", $club_id, $user_id_to_remove);
    if ($remove_stmt->execute()) {
        $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>✅ Member removed from club.</p>";
    }
    $remove_stmt->close();
}

// Fetch Club Info
$club_stmt = $conn->prepare("SELECT club_name FROM clubs WHERE id = ?");
$club_stmt->bind_param("i", $club_id);
$club_stmt->execute();
$club = $club_stmt->get_result()->fetch_assoc();
$club_stmt->close();

// Fetch Roster using a SQL JOIN
$members_stmt = $conn->prepare("
    SELECT users.id, users.name, users.email, users.department 
    FROM users 
    JOIN club_members ON users.id = club_members.user_id 
    WHERE club_members.club_id = ?
    ORDER BY users.name ASC
");
$members_stmt->bind_param("i", $club_id);
$members_stmt->execute();
$members = $members_stmt->get_result();
$members_stmt->close();

include "header.php";
?>

<div class="container">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2>Manage: <?php echo htmlspecialchars($club['club_name']); ?></h2>
        <a href="admin.php" style="color: #007bff; text-decoration: none;">&larr; Back to Admin Dashboard</a>
    </div>

    <?php echo $message; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; margin-bottom: 30px;">

        <div class="card" style="flex: 1; min-width: 300px; padding: 20px; border-top: 4px solid #007bff;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Broadcast Announcement</h3>
            <p style="font-size: 0.9em; color: #666;">Send a message to all registered members of this club.</p>

            <form method="POST">
                <textarea name="announcement_text" placeholder="Type your notice, meeting time, or update here..." required rows="4" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>
                <button name="send_announcement" style="width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Send to Members</button>
            </form>
        </div>

        <div class="card" style="flex: 2; min-width: 300px; padding: 20px; overflow-x: auto;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Current Roster</h3>

            <?php if ($members->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <tr style="background-color: var(--header-bg); color: white;">
                        <th style="padding: 10px;">Name</th>
                        <th style="padding: 10px;">Email</th>
                        <th style="padding: 10px; text-align: right;">Action</th>
                    </tr>
                    <?php while ($member = $members->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #ccc;">
                            <td style="padding: 10px;"><?php echo htmlspecialchars($member['name']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($member['email']); ?></td>
                            <td style="padding: 10px; text-align: right;">
                                <form method="POST" onsubmit="return confirm('Remove this student from the club?');">
                                    <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                    <button name="remove_member" style="background: #ffc107; color: #333; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.9em;">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">No students have joined this club yet.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include "footer.php"; ?>