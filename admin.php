<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";
include "header.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    echo "<div class='container'><div class='card' style='text-align: center; border-left: 5px solid red; padding: 30px;'><h3 style='color: red; margin-top: 0;'>Access Denied</h3><p>Only authorized administrators can access this.</p></div></div>";
    include "footer.php";
    exit();
}

$message = "";

// --- HANDLE ADDING CONTENT ---
if (isset($_POST['add_club'])) {
    $club_name = trim($_POST['club_name']);
    $description = trim($_POST['description']);
    $stmt = $conn->prepare("INSERT INTO clubs (club_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $club_name, $description);
    if ($stmt->execute()) $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>✅ Club created successfully!</p>";
    $stmt->close();
}

if (isset($_POST['add_event'])) {
    $event_name = trim($_POST['event_name']);
    $event_description = trim($_POST['event_description']);
    $stmt = $conn->prepare("INSERT INTO events (event_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $event_name, $event_description);
    if ($stmt->execute()) $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>✅ Event created successfully!</p>";
    $stmt->close();
}

if (isset($_POST['add_general_announcement'])) {
    $title = trim($_POST['title']);
    $ann_message = trim($_POST['ann_message']);
    $stmt = $conn->prepare("INSERT INTO general_announcements (title, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $ann_message);
    if ($stmt->execute()) $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>📢 Campus announcement published!</p>";
    $stmt->close();
}

// --- HANDLE DELETING CONTENT ---
if (isset($_POST['delete_club'])) {
    $club_id = intval($_POST['club_id']);
    $stmt = $conn->prepare("DELETE FROM clubs WHERE id = ?");
    $stmt->bind_param("i", $club_id);
    if ($stmt->execute()) $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>🗑️ Club deleted!</p>";
    $stmt->close();
}

if (isset($_POST['delete_event'])) {
    $event_id = intval($_POST['event_id']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    if ($stmt->execute()) $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>🗑️ Event deleted!</p>";
    $stmt->close();
}

if (isset($_POST['delete_announcement'])) {
    $ann_id = intval($_POST['ann_id']);
    $stmt = $conn->prepare("DELETE FROM general_announcements WHERE id = ?");
    $stmt->bind_param("i", $ann_id);
    if ($stmt->execute()) $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px;'>🗑️ Announcement deleted!</p>";
    $stmt->close();
}

// Fetch Stats & Content
$user_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$club_count = $conn->query("SELECT COUNT(*) AS total FROM clubs")->fetch_assoc()['total'];
$event_count = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'];

$clubs = $conn->query("SELECT * FROM clubs ORDER BY id DESC");
$events = $conn->query("SELECT * FROM events ORDER BY id DESC");
$general_announcements = $conn->query("SELECT * FROM general_announcements ORDER BY id DESC");
?>

<div class="container">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin-bottom: 5px;">Admin Control Panel</h2>
        <span style="background: #007bff; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em;">
            Logged in as: <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $_SESSION['role']))); ?>
        </span>
    </div>

    <?php echo $message; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin-bottom: 40px;">
        <div class="card" style="flex: 1; min-width: 200px; padding: 20px; text-align: center; border-top: 4px solid #007bff;">
            <h3 style="margin-top: 0; color: #666;">Total Users</h3>
            <p style="font-size: 2.5em; font-weight: bold; margin: 10px 0; color: var(--text-color);"><?php echo $user_count; ?></p>

        </div>

        <div class="card" style="flex: 1; min-width: 200px; padding: 20px; text-align: center; border-top: 4px solid #28a745;">
            <h3 style="margin-top: 0; color: #666;">Active Clubs</h3>
            <p style="font-size: 2.5em; font-weight: bold; margin: 10px 0; color: var(--text-color);"><?php echo $club_count; ?></p>
        </div>

        <div class="card" style="flex: 1; min-width: 200px; padding: 20px; text-align: center; border-top: 4px solid #ffc107;">
            <h3 style="margin-top: 0; color: #666;">Scheduled Events</h3>
            <p style="font-size: 2.5em; font-weight: bold; margin: 10px 0; color: var(--text-color);"><?php echo $event_count; ?></p>
        </div>
    </div>

    <hr style="border: 1px solid #ddd; margin: 40px 0;">

    <div style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center;">

        <div class="card" style="flex: 1; min-width: 280px; padding: 30px; border-top: 4px solid #28a745;">
            <h3 style="border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-top: 0;">Add a New Club</h3>
            <form method="POST">
                <input type="text" name="club_name" placeholder="Club Name" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <textarea name="description" placeholder="Club Description..." required rows="4" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>
                <button name="add_club" style="width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Create Club</button>
            </form>
        </div>

        <div class="card" style="flex: 1; min-width: 280px; padding: 30px; border-top: 4px solid #007bff;">
            <h3 style="border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-top: 0;">Add a New Event</h3>
            <form method="POST">
                <input type="text" name="event_name" placeholder="Event Name" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <textarea name="event_description" placeholder="Event Description..." required rows="4" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>
                <button name="add_event" style="width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Create Event</button>
            </form>
        </div>

        <div class="card" style="flex: 1; min-width: 280px; padding: 30px; border-top: 4px solid #6c757d;">
            <h3 style="border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-top: 0;">Campus Announcement</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="Notice Title (e.g., Holiday Notice)" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <textarea name="ann_message" placeholder="Type the campus-wide announcement here..." required rows="4" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>
                <button name="add_general_announcement" style="width: 100%; padding: 10px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Publish Notice</button>
            </form>
        </div>

    </div>

    <hr style="border: 1px solid #ddd; margin: 40px 0;">

    <h2 style="text-align: center;">Manage Existing Content</h2>
    <div style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center;">

        <div class="card" style="flex: 1; min-width: 300px; padding: 20px; overflow-x: auto;">
            <h3 style="margin-top: 0; border-bottom: 2px solid #28a745; padding-bottom: 5px;">Active Clubs</h3>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <?php while ($club = $clubs->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; font-weight: bold;"><?php echo htmlspecialchars($club['club_name']); ?></td>
                        <td style="padding: 10px; width: 120px;">
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <a href="manage_club.php?id=<?php echo $club['id']; ?>" style="display: block; background: #17a2b8; color: white; padding: 8px; border-radius: 4px; text-decoration: none; font-size: 14px; text-align: center;">Members</a>
                                <a href="edit_club.php?id=<?php echo $club['id']; ?>" style="display: block; background: #ffc107; color: #333; padding: 8px; border-radius: 4px; text-decoration: none; font-size: 14px; text-align: center;">Edit</a>
                                <form method="POST" style="margin: 0;" onsubmit="return confirm('Delete this club entirely?');">
                                    <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                    <button name="delete_club" style="display: block; width: 100%; background: #dc3545; color: white; border: none; padding: 8px; border-radius: 4px; cursor: pointer; font-size: 14px;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="card" style="flex: 1; min-width: 300px; padding: 20px; overflow-x: auto;">
            <h3 style="margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 5px;">Scheduled Events</h3>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <?php while ($event = $events->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; font-weight: bold;"><?php echo htmlspecialchars($event['event_name']); ?></td>
                        <td style="padding: 10px; width: 120px;">
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <a href="manage_event.php?name=<?php echo urlencode($event['event_name']); ?>" style="display: block; background: #17a2b8; color: white; padding: 8px; border-radius: 4px; text-decoration: none; font-size: 14px; text-align: center;">Attendees</a>
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" style="display: block; background: #ffc107; color: #333; padding: 8px; border-radius: 4px; text-decoration: none; font-size: 14px; text-align: center;">Edit</a>
                                <form method="POST" style="margin: 0;" onsubmit="return confirm('Delete this event entirely?');">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <button name="delete_event" style="display: block; width: 100%; background: #dc3545; color: white; border: none; padding: 8px; border-radius: 4px; cursor: pointer; font-size: 14px;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="card" style="flex: 1; min-width: 300px; padding: 20px; overflow-x: auto;">
            <h3 style="margin-top: 0; border-bottom: 2px solid #6c757d; padding-bottom: 5px;">Campus Announcements</h3>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <?php while ($ann = $general_announcements->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; font-weight: bold;"><?php echo htmlspecialchars($ann['title']); ?></td>
                        <td style="padding: 10px; width: 80px; text-align: right;">
                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Delete this announcement?');">
                                <input type="hidden" name="ann_id" value="<?php echo $ann['id']; ?>">
                                <button name="delete_announcement" style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 14px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>
</div>

<?php include "footer.php"; ?>