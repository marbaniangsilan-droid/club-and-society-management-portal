<?php
$servername = "localhost";
$username = "root";   // Default XAMPP username
$password = "";       // Default XAMPP password is blank
$dbname = "club"; // The database you just created

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
