<?php
session_start();

if (!isset($_SESSION['current_user'])) {
    header("Location: login.php");
    exit();
}

$users = &$_SESSION['users'];
$currentEmail = $_SESSION['current_user'];
$user = &$users[$currentEmail];

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $amount = floatval($_POST['amount']);
    $type = $_POST['type'];
    $recipient = trim($_POST['recipient']);

    if ($amount <= 0) {
        $error = "Enter a valid amount.";
    } else {
        if ($type === "deposit") {
            $user['balance'] += $amount;
            $success = "Deposit successful.";
        } elseif ($type === "withdraw") {
            if ($user['balance'] >= $amount) {
                $user['balance'] -= $amount;
                $success = "Withdrawal successful.";
            } else {
                $error = "Insufficient funds.";
            }
        } elseif ($type === "transfer") {
            if (empty($recipient)) $error = "Enter recipient email.";
            elseif (!isset($users[$recipient])) $error = "Recipient not found.";
            elseif ($recipient === $currentEmail) $error = "Cannot transfer to yourself.";
            elseif ($user['balance'] < $amount) $error = "Insufficient funds.";
            else {
                $user['balance'] -= $amount;
                $users[$recipient]['balance'] += $amount;

                $user['transactions'][] = [
                    "type" => "transfer",
                    "amount" => $amount,
                    "time" => date("M d, Y H:i"),
                    "to" => $recipient
                ];

                $users[$recipient]['transactions'][] = [
                    "type" => "received",
                    "amount" => $amount,
                    "time" => date("M d, Y H:i"),
                    "from" => $currentEmail
                ];

                $success = "Transfer successful.";
            }
        }

        if (!$error && $type !== "transfer" && ($type === "deposit" || $type === "withdraw")) {
            $user['transactions'][] = [
                "type" => $type,
                "amount" => $amount,
                "time" => date("M d, Y H:i")
            ];
        }
    }
}

$accountColors = [
    "Savings" => "#10b981",
    "Checking" => "#3b82f6",
    "Current" => "#f59e0b"
];

$acctColor = $accountColors[$user['account_type']] ?? "#6b7280";
$profileImage = !empty($user['profile_picture'])
    ? $user['profile_picture']
    : "default.png";
?>
<!DOCTYPE html>
<html>
<head>
<title>G5 Bank Dashboard</title>
<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:linear-gradient(135deg,#0f172a,#1e293b);
    display:flex;
    justify-content:center;
    padding:40px 0;
}

.container{
    width:420px;
}

.card{
    background:white;
    padding:30px;
    border-radius:30px;
    box-shadow:0 25px 50px rgba(0,0,0,0.25);
}

h1{
    text-align:center;
    margin-top:0;
    margin-bottom:25px;
    color:#2563eb;
}

.overview{
    display:flex;
    align-items:center;
    margin-bottom:25px;
}

.profile-pic{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    margin-right:15px;
    border:3px solid #e5e7eb;
}

.overview-text{
    flex:1;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.overview-text strong{
    font-size:18px;
    margin-bottom:4px;
}

.overview-text span{
    font-size:13px;
    color:#6b7280;
    margin-bottom:6px;
}

.balance{
    font-size:22px;
    font-weight:bold;
    margin-top:6px;
}

.account-type{
    display:inline-block;
    padding:4px 10px;
    border-radius:20px;
    color:white;
    font-weight:bold;
    font-size:12px;
    margin-bottom:6px;
}

input, select{
    width:100%;
    padding:14px;
    margin-bottom:15px;
    border-radius:20px;
    border:1px solid #ddd;
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

.success{color:green;margin-bottom:10px;}
.error{color:red;margin-bottom:10px;}

.transaction{
    padding:10px 0;
    border-bottom:1px solid #eee;
    font-size:14px;
}

.badge{
    padding:2px 6px;
    border-radius:12px;
    font-size:11px;
    color:white;
    font-weight:bold;
    margin-right:5px;
}

.badge.deposit{background:green;}
.badge.withdraw{background:red;}
.badge.transfer{background:orange;}
.badge.received{background:blue;}

.logout{
    text-align:center;
    margin-top:15px;
}

.logout a{
    color:white;
    text-decoration:none;
    font-weight:bold;
}
</style>
</head>
<body>

<div class="container">

<div class="card">

    <!-- ACCOUNT OVERVIEW -->
    <h1>G5 Bank</h1>

    <div class="overview">
        <img src="<?= htmlspecialchars($profileImage) ?>" class="profile-pic">
        <div class="overview-text">
            <strong><?= htmlspecialchars($user['name']) ?></strong>
            <span><?= htmlspecialchars($user['email']) ?></span>
            <span class="account-type" style="background: <?= $acctColor ?>;">
                <?= htmlspecialchars($user['account_type']) ?> Account
            </span>
            <div class="balance">$<?= number_format($user['balance'],2) ?></div>
        </div>
    </div>

    <hr>

    <!-- TRANSACTION -->
    <h3>Transaction</h3>

    <?php if($error) echo "<div class='error'>$error</div>"; ?>
    <?php if($success) echo "<div class='success'>$success</div>"; ?>

    <form method="post">
        <input type="number" step="0.01" name="amount" placeholder="Amount" required>
        <select name="type">
            <option value="deposit">Deposit</option>
            <option value="withdraw">Withdraw</option>
            <option value="transfer">Transfer</option>
        </select>
        <input type="email" name="recipient" placeholder="Recipient Email (for transfer)">
        <button type="submit">Submit</button>
    </form>

    <h3>Transaction History</h3>

    <?php
    if (empty($user['transactions'])) {
        echo "<div>No transactions yet.</div>";
    } else {
        foreach(array_reverse($user['transactions']) as $t){
            echo "<div class='transaction'>";
            echo "<span class='badge {$t['type']}'>" . ucfirst($t['type']) . "</span>";
            echo "$" . number_format($t['amount'],2);
            if(isset($t['to'])) echo " → ".htmlspecialchars($t['to']);
            if(isset($t['from'])) echo " ← ".htmlspecialchars($t['from']);
            echo "<br><small>" . $t['time'] . "</small>";
            echo "</div>";
        }
    }
    ?>

</div>

<div class="logout">
    <a href="logout.php">Logout</a>
</div>

</div>

</body>
</html>