<?php
session_start();

$PASSWORD = "88102043"; // 🔒 CHANGE THIS

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: list.php");
    exit;
}

// Login
if (isset($_POST['password'])) {
    if ($_POST['password'] === $PASSWORD) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "رمز اشتباه است";
    }
}

// If not logged in → show login
if (!isset($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>ورود</title>
<style>
body{font-family:tahoma;background:#f3f4f6;display:flex;justify-content:center;align-items:center;height:100vh}
.box{background:#fff;padding:2rem;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.1);text-align:center}
input{padding:10px;border:1px solid #ccc;border-radius:8px;width:200px}
button{padding:10px 20px;margin-top:10px;background:#1d4ed8;color:#fff;border:none;border-radius:8px}
.error{color:red;margin-top:10px}
</style>
</head>
<body>

<div class="box">
  <h3>🔐 ورود به پنل قراردادها</h3>
  <form method="POST">
    <input type="password" name="password" placeholder="رمز عبور"><br>
    <button type="submit">ورود</button>
  </form>
  <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
</div>

</body>
</html>

<?php
exit;
endif;

// ============================
// Logged in → show contracts
// ============================

$dir = __DIR__ . "/signed-contracts";
$files = glob($dir . "/*.json");

// Sort newest first
usort($files, function($a, $b){
    return filemtime($b) - filemtime($a);
});
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>لیست قراردادها</title>
<style>
body{font-family:tahoma;background:#f8fafc;padding:20px}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{padding:10px;border-bottom:1px solid #eee;text-align:right;font-size:13px}
th{background:#f1f5f9}
a{color:#1d4ed8;text-decoration:none;font-weight:bold}
.top{margin-bottom:15px;display:flex;justify-content:space-between}
.logout{color:red}
</style>
</head>
<body>

<div class="top">
  <h3>📄 لیست قراردادها</h3>
  <a class="logout" href="?logout=1">خروج</a>
</div>

<table>
<tr>
  <th>نام</th>
  <th>شماره</th>
  <th>پلن</th>
  <th>تاریخ</th>
  <th>دانلود</th>
</tr>

<?php foreach($files as $file):
    $data = json_decode(file_get_contents($file), true);
    $pdf = $data['pdfFile'] ?? '';
?>
<tr>
  <td><?= htmlspecialchars($data['clientName'] ?? '-') ?></td>
  <td><?= htmlspecialchars($data['clientPhone'] ?? '-') ?></td>
  <td><?= htmlspecialchars($data['selectedPlan'] ?? '-') ?></td>
  <td><?= htmlspecialchars($data['savedAtUtc'] ?? '-') ?></td>
  <td>
    <a href="signed-contracts/<?= $pdf ?>" target="_blank">دانلود</a>
  </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>
