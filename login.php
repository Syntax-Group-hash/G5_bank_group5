<?php
session_start();

// Make sure users array exists
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (isset($_SESSION['users'][$email])) {
        $user = $_SESSION['users'][$email];
        if (password_verify($password, $user['password'])) {
            $_SESSION['current_user'] = $email; // log in
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with this email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>G5 Bank - Login</title>
<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:linear-gradient(135deg,#0f172a,#1e293b);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.card{
    background:white;
    padding:40px;
    width:360px;
    border-radius:30px;
    box-shadow:0 25px 50px rgba(0,0,0,0.25);
    text-align:center;
}
h1{
    margin-bottom:20px;
    color:#2563eb;
}
input{
    width:100%;
    padding:14px;
    margin-bottom:15px;
    border-radius:20px;
    border:1px solid #ddd;
    outline:none;
}
input:focus{
    border-color:#2563eb;
}
button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:25px;
    background:#0f172a;
    color:white;
    font-weight:bold;
    cursor:pointer;
}
button:hover{
    background:#000;
}
.link{
    text-align:center;
    margin-top:15px;
}
.link a{
    text-decoration:none;
    color:#0f172a;
    font-weight:bold;
}
.error{
    color:red;
    margin-bottom:15px;
}
</style>
</head>
<body>

<div class="card">
    <h1>G5 Bank</h1>
    <h2>Login</h2>

    <?php if($error) echo "<div class='error'>$error</div>"; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <div class="link">
        Don't have an account? <a href="register.php">Register</a>
    </div>
</div>

</body>
</html>