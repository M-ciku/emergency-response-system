<?php
require 'db.php';
$msg = "";
$is_success = false;

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    // Explicitly hardcode the role to 'student'
    $role = 'student'; 

    $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $phone, $email, $password, $role);
    
    if ($stmt->execute()) {
        $msg = "Registration successful! <a href='login.php'>Login here</a>";
        $is_success = true;
    } else {
        $msg = "Registration failed. That email address might already be registered in our database.";
        $is_success = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Emergency System</title>

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

    /* Status & Feedback Notification Banners */
    .msg-banner {
        padding: 14px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 24px;
        line-height: 1.5;
    }

    .msg-banner.error {
        background: #fde8e8;
        color: #e53e3e;
        border: 1px solid #f8b4b4;
    }

    .msg-banner.success {
        background: #def7ec;
        color: #03543f;
        border: 1px solid #84e1bc;
    }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <section class="auth-card">
            <h2>Student Registration</h2>

            <?php if (!empty($msg)): ?>
            <div class="msg-banner <?php echo $is_success ? 'success' : 'error'; ?>">
                <?php echo $msg; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" class="input-field" placeholder="Full Name" required>
                </div>

                <div class="form-group">
                    <input type="text" name="phone" class="input-field" placeholder="Phone Number" required>
                </div>

                <div class="form-group">
                    <input type="email" name="email" class="input-field" placeholder="Email Address" required>
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="input-field" placeholder="Password" required>
                </div>

                <button type="submit" name="register" class="btn-submit">Register Account</button>
            </form>

            <p>Already have an account? <a href="login.php">Login</a></p>
        </section>
    </div>
</body>

</html>