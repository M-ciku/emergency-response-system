<?php
require 'db.php';
$msg = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // HARDCODED EMERGENCY BYPASS FOR TESTING
    if ($email === 'admin@emergency.com' && $password === 'admin2026') {
        $_SESSION['user_id'] = 999; 
        $_SESSION['name'] = 'System Admin (Bypass Mode)';
        $_SESSION['phone'] = '0700000000';
        $_SESSION['role'] = 'admin';
        
        header("Location: admin_dashboard.php");
        exit();
    }
    
    if ($email === 'medic@emergency.com' && $password === 'medic2026') {
        $_SESSION['user_id'] = 888; 
        $_SESSION['name'] = 'Field Responder Medic';
        $_SESSION['phone'] = '0711111111';
        $_SESSION['role'] = 'medic';
        
        header("Location: medic_dashboard.php");
        exit();
    }

    // Regular fallback login process for students
    $stmt = $conn->prepare("SELECT id, name, phone, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['role'] = $row['role'];

            // FIXED: Using elseif to chain consecutive role conditions perfectly
            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($row['role'] == 'medic') {
                header("Location: medic_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            // DEBUG CHANNELS: Tells us exactly what it sees
            $msg = "Password verification failed. The typed password did not match the scrambled string in your database.";
        }
    } else {
        $msg = "Account matching email '" . htmlspecialchars($email) . "' was not found in the database table.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Portal (Debug Enabled)</title>

    <style>
    /* ==========================================
           Premium Universal Reset & Operational Variables
           ========================================== */
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary-red: #ff3b30;
        --primary-hover: #e02e24;
        --dark-bg: #0f1115;
        --border-light: #e2e8f0;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --glow-red: rgba(255, 59, 48, 0.05);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body {
        background-color: #f8fafc;
        background-image: radial-gradient(at 0% 0%, rgba(255, 59, 48, 0.02) 0px, transparent 50%),
            radial-gradient(at 50% 0%, rgba(14, 165, 233, 0.02) 0px, transparent 50%);
        color: var(--text-main);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        min-height: 100vh;
    }

    /* ==========================================
           Authentication Screens Gateway UI
           ========================================== */
    .auth-wrapper {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .auth-card {
        background: #ffffff;
        border: 1px solid var(--border-light);
        padding: 40px;
        border-radius: 16px;
        max-width: 450px;
        width: 100%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }

    .auth-card h2 {
        font-size: 1.4rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin-bottom: 24px;
        color: var(--dark-bg);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .auth-card h2::before {
        content: '';
        width: 8px;
        height: 8px;
        background: var(--primary-red);
        border-radius: 50%;
        display: inline-block;
    }

    /* Modern Advanced Forms & Inputs */
    .form-group {
        margin-bottom: 20px;
    }

    .input-field {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid var(--border-light);
        border-radius: 10px;
        font-size: 0.95rem;
        color: var(--text-main);
        background: #f8fafc;
        transition: all 0.2s ease;
    }

    .input-field:focus {
        outline: none;
        border-color: var(--text-main);
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(15, 17, 21, 0.05);
    }

    .btn-submit {
        width: 100%;
        background: var(--dark-bg);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 14px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 5px;
    }

    .btn-submit:hover {
        background: #000000;
        transform: translateY(-1px);
    }

    .auth-card p {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin-top: 20px;
        text-align: center;
        font-weight: 500;
    }

    .auth-card a {
        color: var(--primary-red);
        text-decoration: none;
        font-weight: 700;
    }

    .auth-card a:hover {
        text-decoration: underline;
    }

    /* Debug / Error Banners */
    .msg-banner {
        padding: 14px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .msg-banner.error {
        background: #fde8e8;
        color: #e53e3e;
        border: 1px solid #f8b4b4;
    }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <section class="auth-card">
            <h2>System Login Portal</h2>

            <?php if (!empty($msg)): ?>
            <div class="msg-banner error">
                <strong>Debug Notice:</strong> <?php echo $msg; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" class="input-field" placeholder="Email Address" required>
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="input-field" placeholder="Password" required>
                </div>

                <button type="submit" name="login" class="btn-submit">Login</button>
            </form>

            <p>Student without an account? <a href="register.php">Register Here</a></p>
        </section>
    </div>
</body>

</html>