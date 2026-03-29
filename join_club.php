<?php
include "db.php";

// Check if user_id and club_id are provided
if(!isset($_GET['user_id']) || !isset($_GET['club_id'])){
    $message = "User or club not selected!";
} else {
    $user_id = intval($_GET['user_id']);
    $club_id = intval($_GET['club_id']);

    // Check if already joined
    $check = $conn->query("SELECT * FROM club_members WHERE user_id='$user_id' AND club_id='$club_id'");
    if($check->num_rows > 0){
        $message = "You have already joined this club!";
    } else {
        // Insert membership
        if($conn->query("INSERT INTO club_members (user_id, club_id) VALUES ('$user_id', '$club_id')")){
            $club = $conn->query("SELECT name FROM clubs WHERE id='$club_id'")->fetch_assoc();
            $message = "You joined <strong>".$club['name']."</strong>!";
        } else {
            $message = "Error joining club!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Join Club</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background-color: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 400px;
        }

        .card h2 {
            color: #333;
        }

        .message {
            margin: 20px 0;
            font-size: 18px;
            color: #555;
        }

        .back-btn {
            display: inline-block;
            text-decoration: none;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            transition: background-color 0.2s;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        strong {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Club Membership</h2>
        <div class="message"><?php echo $message; ?></div>
        <a class="back-btn" href="clubs.php">Back to Clubs</a>
    </div>
</body>
</html>