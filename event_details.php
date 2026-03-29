<?php
include "header.php";

// 1. Ensure an ID was passed in the URL
if (!isset($_GET['id'])) {
    echo "<div class='container'><p>Invalid event selected. <a href='events.php'>Go back</a></p></div>";
    include "footer.php";
    exit();
}

$event_id = intval($_GET['id']);

// 2. Fetch the specific event details safely
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container'><p>Event not found. <a href='events.php'>Go back</a></p></div>";
    include "footer.php";
    exit();
}

$event = $result->fetch_assoc();
$event_name = $event['event_name'];
$is_member = false;

// 3. Handle Registration Logic (Only if logged in)
if (isset($_SESSION['user'])) {
    $email = $_SESSION['user']; // Using the email stored during login

    // If the user clicked the Join button
    if (isset($_POST['join'])) {
        $insert_stmt = $conn->prepare("INSERT INTO event_registration (event_name, user_email) VALUES (?, ?)");
        $insert_stmt->bind_param("ss", $event_name, $email);
        if ($insert_stmt->execute()) {
            $is_member = true;
        }
        $insert_stmt->close();
    } else {
        // Just checking if they already registered previously
        $check_stmt = $conn->prepare("SELECT * FROM event_registration WHERE event_name = ? AND user_email = ?");
        $check_stmt->bind_param("ss", $event_name, $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $is_member = true;
        }
        $check_stmt->close();
    }
}
?>

<div class="card" style="max-width: 600px; margin: 40px auto; padding: 40px; text-align: center; width: auto;">
    <h2 style="margin-top: 0;"><?php echo htmlspecialchars($event['event_name']); ?></h2>

    <p style="margin: 20px 0; line-height: 1.6; font-size: 16px;">
        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
    </p>

    <?php if (!isset($_SESSION['user'])): ?>
        <div style="margin-top: 30px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
            <p>Please <a href="login.php" style="color: #007bff; text-decoration: none; font-weight: bold;">Login</a> to register for this event.</p>
        </div>
    <?php elseif ($is_member): ?>
        <p style="color: #28a745; font-weight: bold; margin-top: 30px; padding: 15px; border: 2px solid #28a745; border-radius: 5px;">
            ✅ You are registered for this event!
        </p>
    <?php else: ?>
        <form method="POST" style="margin-top: 30px;">
            <button name="join" style="background: #007bff; color: white; padding: 12px 25px; border: none; cursor: pointer; border-radius: 5px; font-size: 16px; font-weight: bold; transition: 0.2s;">
                Register for Event
            </button>
        </form>
    <?php endif; ?>

    <br><br>
    <a href="events.php" style="display: inline-block; color: #888; text-decoration: none;">&larr; Back to Events</a>
</div>

<?php include "footer.php"; ?>