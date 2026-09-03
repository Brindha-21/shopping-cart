<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            $_SESSION['flash_success'] = "Welcome back, " . htmlspecialchars($user['name']) . "!";
            
            $redirect = $_GET['redirect'] ?? 'index.php';
            header("Location: " . $redirect);
            exit();
        } else {
            $error = "Invalid email address or password.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-card">
    <h2>Welcome Back</h2>
    <p class="subtitle">Log in to your ShopVerse account</p>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="john@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            <i class="fa-solid fa-right-to-bracket"></i> Log In
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">
        Don't have an account? <a href="signup.php" style="color: var(--primary); font-weight:600;">Sign Up</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
