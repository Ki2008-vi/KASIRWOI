<?php
session_start();
include 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Cek apakah tabel users ada
    $hasUsersTable = false;
    $res = $conn->query("SHOW TABLES LIKE 'users'");
    if ($res && $res->num_rows > 0) {
        $hasUsersTable = true;
    }

    // Jika tabel users ada tetapi kosong, buat akun admin default (password di-hash)
    if ($hasUsersTable) {
        $cntRes = $conn->query("SELECT COUNT(*) as cnt FROM users");
        $cntRow = $cntRes ? $cntRes->fetch_assoc() : null;
        $countUsers = $cntRow ? intval($cntRow['cnt']) : 0;
        if ($countUsers === 0) {
            $defaultUser = 'admin';
            $defaultPass = 'admin123';
            $hash = password_hash($defaultPass, PASSWORD_DEFAULT);
            $stmtIns = $conn->prepare("INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)");
            if ($stmtIns) {
                $namaLengkap = 'Administrator';
                $stmtIns->bind_param('sss', $defaultUser, $hash, $namaLengkap);
                $stmtIns->execute();
                $stmtIns->close();
            }
        }
    }

    $authenticated = false;
    $userRow = null;

    if ($hasUsersTable) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $userRow = $result->fetch_assoc();
            // gunakan password_verify jika kolom password disimpan hashed
            if (password_verify($password, $userRow['password'])) {
                $authenticated = true;
            }
        }
    } else {
        // Fallback: kredensial default (ganti setelah setup)
        $defaultUser = 'admin';
        $defaultPass = 'admin123';
        if ($username === $defaultUser && $password === $defaultPass) {
            $authenticated = true;
            $userRow = ['username' => $defaultUser];
        }
    }

    if ($authenticated) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $userRow['username'] ?? $username;
        header('Location: index.php');
        exit();
    } else {
        $error = 'Login gagal: username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS UMKM</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-wrapper { max-width:360px; margin:80px auto; background:#fff; padding:24px; border-radius:10px; box-shadow:0 6px 22px rgba(0,0,0,0.08); }
        .login-wrapper h2 { margin:0 0 12px 0; color:#0984e3 }
        .form-group { margin-bottom:12px }
        label { display:block; font-size:13px; margin-bottom:6px }
        input[type="text"], input[type="password"] { width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:6px }
        .btn-login { background:#0984e3; color:#fff; border:none; padding:10px 14px; border-radius:6px; cursor:pointer }
        .error { color:#d63031; margin-bottom:8px }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <h2>Login Kasir</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn-login">Masuk</button>
        </form>
    </div>
</body>
</html>
