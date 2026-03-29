<?php
include "db.php";

$message="";

if(isset($_POST['reset'])){

$email=$_POST['email'];
$newpassword=$_POST['password'];

$sql="SELECT * FROM users WHERE email='$email'";
$result=mysqli_query($conn,$sql);

if(mysqli_num_rows($result)>0){

$update="UPDATE users SET password='$newpassword' WHERE email='$email'";
mysqli_query($conn,$update);

$message="Password Updated Successfully. You can login now.";

}else{

$message="Email not found";

}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
</head>

<body>

<h2>Reset Password</h2>

<p style="color:green;"><?php echo $message; ?></p>

<form method="POST">

<input type="email" name="email" placeholder="Enter your email" required><br><br>

<input type="password" name="password" placeholder="New Password" required><br><br>

<button name="reset">Reset Password</button>

</form>

<br>

<a href="login.php">Back to Login</a>

</body>
</html>