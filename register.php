<?php
session_start();

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

$error = "";

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $national_id = trim($_POST['national_id']);
    $account_type = $_POST['account_type'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // PROFILE PICTURE
    $profilePath = "";
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {

        $allowedImageTypes = ['image/jpeg', 'image/png'];
        $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);

        if (!in_array($fileType, $allowedImageTypes)) {
            $error = "Profile picture must be JPG or PNG.";
        } else {
            $profileName = uniqid("profile_", true) . ".jpg";
            $profilePath = $uploadDir . $profileName;
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profilePath);
        }
    }

    // PERSONAL DOCUMENT (PDF ONLY)
    $documentPath = "";
    if (isset($_FILES['personal_document']) && $_FILES['personal_document']['error'] === 0) {

        $fileType = mime_content_type($_FILES['personal_document']['tmp_name']);
        $fileExtension = strtolower(pathinfo($_FILES['personal_document']['name'], PATHINFO_EXTENSION));

        if ($fileType !== "application/pdf" || $fileExtension !== "pdf") {
            $error = "Personal document must be a PDF file only.";
        } else {
            $docName = uniqid("document_", true) . ".pdf";
            $documentPath = $uploadDir . $docName;
            move_uploaded_file($_FILES['personal_document']['tmp_name'], $documentPath);
        }
    }

    if (isset($_SESSION['users'][$email])) {
        $error = "Account already exists.";
    } elseif (!$error) {

        $_SESSION['users'][$email] = [
            "name" => $name,
            "email" => $email,
            "national_id" => $national_id,
            "account_type" => $account_type,
            "password" => $password,
            "profile_picture" => $profilePath,
            "personal_document" => $documentPath,
            "balance" => 0,
            "transactions" => []
        ];

        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>G5 Bank - Create Account</title>
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

input, select{
    width:100%;
    padding:14px;
    margin-bottom:15px;
    border-radius:20px;
    border:1px solid #ddd;
    outline:none;
}

input:focus, select:focus{
    border-color:#2563eb;
}

.file-label{
    display:block;
    width:100%;
    padding:14px;
    margin-bottom:15px;
    border-radius:20px;
    border:1px solid #ddd;
    cursor:pointer;
    text-align:center;
    background:#f8fafc;
    font-weight:500;
}

.file-label:hover{
    border-color:#2563eb;
    background:#eef2ff;
}

input[type="file"]{
    display:none;
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
    <h2>Create Account</h2>

    <?php if($error) echo "<div class='error'>$error</div>"; ?>

    <form method="post" enctype="multipart/form-data">

        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="national_id" placeholder="National ID Number" required>

        <select name="account_type" required>
            <option value="">Select Account Type</option>
            <option value="Savings">Savings</option>
            <option value="Checking">Checking</option>
            <option value="Current">Current</option>
        </select>

        <input type="password" name="password" placeholder="Password" required>

        <!-- Profile Picture -->
        <label class="file-label" for="profile_picture">
            Choose a Picture
        </label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg,image/png" required>

        <!-- PDF Upload -->
        <label class="file-label" for="personal_document">
            Choose a PDF
        </label>
        <input type="file" name="personal_document" id="personal_document" accept="application/pdf" required>

        <button type="submit">Register</button>
    </form>

    <div class="link">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>