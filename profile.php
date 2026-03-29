<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

// Security Check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user'];
$profile_msg = "";

// --- NEW: Handle Leaving a Club ---
if (isset($_POST['leave_club'])) {
    $club_id_to_leave = intval($_POST['club_id']);
    $leave_stmt = $conn->prepare("DELETE FROM club_members WHERE user_id = ? AND club_id = ?");
    $leave_stmt->bind_param("ii", $user_id, $club_id_to_leave);
    if ($leave_stmt->execute()) {
        $profile_msg = "<p style='color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; text-align: center;'>✅ Successfully left the club.</p>";
    }
    $leave_stmt->close();
}

// --- NEW: Handle Canceling an Event RSVP ---
if (isset($_POST['cancel_event'])) {
    $event_to_cancel = $_POST['event_name'];
    $cancel_stmt = $conn->prepare("DELETE FROM event_registration WHERE user_email = ? AND event_name = ?");
    $cancel_stmt->bind_param("ss", $user_email, $event_to_cancel);
    if ($cancel_stmt->execute()) {
        $profile_msg = "<p style='color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; text-align: center;'>✅ RSVP canceled.</p>";
    }
    $cancel_stmt->close();
}

// Fetch User's Personal Details
$stmt = $conn->prepare("SELECT name, email, phone, department, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch User's Clubs (Now pulling the club ID as well so we know which one to leave)
$clubs_stmt = $conn->prepare("
    SELECT c.id, c.club_name 
    FROM clubs c 
    JOIN club_members cm ON c.id = cm.club_id 
    WHERE cm.user_id = ?
");
$clubs_stmt->bind_param("i", $user_id);
$clubs_stmt->execute();
$my_clubs = $clubs_stmt->get_result();
$clubs_stmt->close();

// Fetch User's Events
$events_stmt = $conn->prepare("SELECT event_name FROM event_registration WHERE user_email = ?");
$events_stmt->bind_param("s", $user_email);
$events_stmt->execute();
$my_events = $events_stmt->get_result();
$events_stmt->close();

// Fetch Recent Notifications (Limit to top 5)
$notif_query = "
    (SELECT 'Club' AS type, ca.message, ca.created_at, c.club_name AS source_name 
    FROM club_announcements ca
    JOIN clubs c ON ca.club_id = c.id
    JOIN club_members cm ON c.id = cm.club_id
    WHERE cm.user_id = ?)
    
    UNION
    
    (SELECT 'Event' AS type, ea.message, ea.created_at, ea.event_name AS source_name
    FROM event_announcements ea
    JOIN event_registration er ON ea.event_name = er.event_name
    WHERE er.user_email = ?)
    
    ORDER BY created_at DESC
    LIMIT 5
";
$notif_stmt = $conn->prepare($notif_query);
$notif_stmt->bind_param("is", $user_id, $user_email);
$notif_stmt->execute();
$notifications = $notif_stmt->get_result();
$notif_stmt->close();

// Now include the visual header
include "header.php";
?>

<div class="container" style="min-height: 70vh;">
    <h2 style="text-align: center; margin-bottom: 20px;">My Profile Overview</h2>

    <?php echo $profile_msg; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px; align-items: flex-start;">

        <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: 20px;">

            <div class="card" style="padding: 20px; border-top: 4px solid var(--text-color); border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background: var(--bg-color);">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="width: 80px; height: 80px; background: #007bff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2em; margin: 0 auto 10px auto; font-weight: bold;">
                        <?php echo strtoupper(substr($user_info['name'], 0, 1)); ?>
                    </div>
                    <h3 style="margin: 0; font-size: 1.5em;"><?php echo htmlspecialchars($user_info['name']); ?></h3>
                    <span style="color: #666; font-size: 0.9em;"><?php echo htmlspecialchars($user_info['department']); ?> Department</span>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--header-bg); margin: 15px 0;">

                <p style="margin: 5px 0;"><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
                <p style="margin: 5px 0;"><strong>Phone:</strong> <?php echo htmlspecialchars($user_info['phone'] ?? 'Not provided'); ?></p>
                <p style="margin: 5px 0;"><strong>Account Role:</strong> <?php echo ucwords(str_replace('_', ' ', $user_info['role'])); ?></p>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="edit_profile.php" style="display: inline-block; padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; width: 100%; box-sizing: border-box;">⚙️ Edit Profile Settings</a>
                </div>
            </div>

            <div class="card" style="padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background: var(--bg-color);">

                <h3 style="margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px;">My Clubs</h3>
                <?php if ($my_clubs->num_rows > 0): ?>
                    <ul style="list-style: none; padding: 0; margin-bottom: 25px;">
                        <?php while ($c = $my_clubs->fetch_assoc()): ?>
                            <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #eee;">
                                <span style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($c['club_name']); ?></span>
                                <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to leave this club?');">
                                    <input type="hidden" name="club_id" value="<?php echo $c['id']; ?>">
                                    <button name="leave_club" style="background: #ffc107; color: #333; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.85em; font-weight: bold;">Leave</button>
                                </form>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #666; font-size: 0.9em; margin-bottom: 25px;">You haven't joined any clubs yet. <a href="clubs.php" style="color: #007bff;">Browse Clubs</a></p>
                <?php endif; ?>

                <h3 style="margin-top: 0; border-bottom: 2px solid #28a745; padding-bottom: 10px;">My Events</h3>
                <?php if ($my_events->num_rows > 0): ?>
                    <ul style="list-style: none; padding: 0; margin-bottom: 0;">
                        <?php while ($e = $my_events->fetch_assoc()): ?>
                            <li style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #eee;">
                                <span style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($e['event_name']); ?></span>
                                <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel your RSVP?');">
                                    <input type="hidden" name="event_name" value="<?php echo htmlspecialchars($e['event_name']); ?>">
                                    <button name="cancel_event" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.85em; font-weight: bold;">Cancel</button>
                                </form>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #666; font-size: 0.9em; margin-bottom: 0;">You haven't RSVP'd to any events. <a href="events.php" style="color: #28a745;">View Events</a></p>
                <?php endif; ?>
            </div>

        </div>

        <div class="card" style="flex: 2; min-width: 300px; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background: var(--bg-color);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--header-bg); padding-bottom: 10px; margin-bottom: 20px;">
                <h3 style="margin: 0;">Recent Notifications</h3>
                <span style="font-size: 0.9em; color: #888;">Your Private Feed</span>
            </div>

            <?php if ($notifications->num_rows > 0): ?>

                <?php while ($notif = $notifications->fetch_assoc()): ?>
                    <?php
                    $border_color = ($notif['type'] === 'Club') ? '#007bff' : '#28a745';
                    $badge_bg = ($notif['type'] === 'Club') ? '#e7f1ff' : '#e8f5e9';
                    $badge_text = ($notif['type'] === 'Club') ? '#007bff' : '#28a745';
                    ?>

                    <div style="padding: 15px; margin-bottom: 15px; border-left: 4px solid <?php echo $border_color; ?>; background: #f9f9f9; border-radius: 4px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px; flex-wrap: wrap; gap: 5px;">
                            <strong><span style="color: <?php echo $badge_text; ?>; font-size: 0.85em; text-transform: uppercase; margin-right: 5px;"><?php echo $notif['type']; ?>:</span> <span style="color: #333;"><?php echo htmlspecialchars($notif['source_name']); ?></span></strong>
                            <span style="font-size: 0.8em; color: #888;"><?php echo date("M j, g:i a", strtotime($notif['created_at'])); ?></span>
                        </div>
                        <p style="margin: 0; color: #444; font-size: 0.95em; line-height: 1.4;">
                            <?php echo nl2br(htmlspecialchars($notif['message'])); ?>
                        </p>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div style="text-align: center; padding: 40px 20px;">
                    <p style="color: #888; font-size: 1.1em; margin-bottom: 10px;">You're all caught up!</p>
                    <p style="color: #aaa; font-size: 0.9em;">Any updates from your clubs or events will appear here.</p>
                </div>
            <?php endif; ?>

        </div>

    </div>
</div>

<?php include "footer.php"; ?>