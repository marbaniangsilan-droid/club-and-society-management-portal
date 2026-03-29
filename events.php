<?php
// 1. LOGIC FIRST: Start the session and connect to the database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php"; // <--- This fixes your $conn error!

$message = "";

// 2. Handle "Register for Event" Action securely via POST
if (isset($_POST['register_event']) && isset($_SESSION['user'])) {
    $event_name = $_POST['event_name'];
    $user_email = $_SESSION['user'];

    // Double-check they haven't already registered
    $check_stmt = $conn->prepare("SELECT id FROM event_registration WHERE event_name = ? AND user_email = ?");
    $check_stmt->bind_param("ss", $event_name, $user_email);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows === 0) {
        // Insert the new registration
        $reg_stmt = $conn->prepare("INSERT INTO event_registration (event_name, user_email) VALUES (?, ?)");
        $reg_stmt->bind_param("ss", $event_name, $user_email);
        if ($reg_stmt->execute()) {
            $message = "<p style='color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 5px; max-width: 600px; margin: 0 auto 20px auto;'>✅ Successfully registered for event!</p>";
        }
        $reg_stmt->close();
    }
    $check_stmt->close();
}

// 3. Fetch all scheduled events
$events = $conn->query("SELECT * FROM events ORDER BY id DESC");

// 4. If logged in, fetch an array of events the user has ALREADY registered for
$registered_events = [];
if (isset($_SESSION['user'])) {
    $stmt = $conn->prepare("SELECT event_name FROM event_registration WHERE user_email = ?");
    $stmt->bind_param("s", $_SESSION['user']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $registered_events[] = $row['event_name'];
    }
    $stmt->close();
}

// 5. UI SECOND: Now draw the webpage
include "header.php";
?>

<div class="container">
    <h2 style="text-align: center; margin-bottom: 30px;">Upcoming Campus Events</h2>

    <?php echo $message; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center;">

        <?php if ($events && $events->num_rows > 0): ?>
            <?php while ($event = $events->fetch_assoc()): ?>

                <div class="card" style="flex: 1; min-width: 300px; max-width: 400px; padding: 30px; border-radius: 8px; display: flex; flex-direction: column;">

                    <h3 style="margin-top: 0; border-bottom: 2px solid var(--header-bg); padding-bottom: 10px;">
                        <?php echo htmlspecialchars($event['event_name']); ?>
                    </h3>

                    <p style="flex-grow: 1; color: var(--text-color); line-height: 1.6; opacity: 0.9;">
                        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                    </p>

                    <div style="margin-top: 20px;">
                        <?php if (!isset($_SESSION['user'])): ?>
                            <a href="login.php" style="display: block; text-align: center; width: 100%; padding: 10px; background: #6c757d; color: white; border-radius: 4px; text-decoration: none; font-weight: bold; box-sizing: border-box;">Login to Register</a>

                        <?php elseif (in_array($event['event_name'], $registered_events)): ?>
                            <button disabled style="width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: not-allowed; box-sizing: border-box;">✅ You are Registered</button>

                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>">
                                <button name="register_event" style="width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; box-sizing: border-box;">Register for Event</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666; width: 100%;">
                <h3>No upcoming events.</h3>
                <p>Keep an eye out for new announcements!</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include "footer.php"; ?>