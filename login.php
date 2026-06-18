<?php
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "github_db");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Create users table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id)
)";

mysqli_query($conn, $createTable);

$message = "";
$message_type = "";

// ===== HANDLE SIGNUP =====
if (isset($_POST['signup'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($check) > 0) {
        $message = "Email already exists! Please use a different email.";
        $message_type = "error";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO users(email, password) VALUES('$email','$password')");
        
        if ($insert) {
            $message = "Account created successfully! Please login.";
            $message_type = "success";
        } else {
            $message = "Registration failed. Please try again.";
            $message_type = "error";
        }
    }
}

// ===== HANDLE LOGIN =====
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $email;
            $_SESSION['user_id'] = $user['id'];
            
            // Show success toast via JavaScript
            $login_success = true;
        } else {
            $message = "Wrong password! Please try again.";
            $message_type = "error";
        }
    } else {
        $message = "Account not found! Please sign up first.";
        $message_type = "error";
    }
}

// ===== HANDLE LOGOUT =====
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// ===== HANDLE VIEW USERS =====
if (isset($_GET['view_users']) && isset($_SESSION['email'])) {
    $users_result = mysqli_query($conn, "SELECT id, email, created_at FROM users ORDER BY id DESC");
    $show_users = true;
} else {
    $show_users = false;
}

// If user is logged in and not viewing users, show dashboard
$is_logged_in = isset($_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(rgba(0, 0, 0, .55), rgba(0, 0, 0, .55)),
                        url("https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1800&q=80");
            background-size: cover;
            background-position: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
        }

        .card {
            background: rgba(20, 20, 20, .65);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 30px;
            padding: 45px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .45);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        h1 {
            color: white;
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.75);
            text-align: center;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 5px;
        }

        .tab-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 12px;
            background: transparent;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #D4AF37, #F7E27E);
            color: #111;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .tab-btn:hover:not(.active) {
            color: white;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .input-group label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .input-group label i {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }

        .input-group input {
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: white;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .input-group input:focus {
            border-color: #D4AF37;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 48px;
        }

        .toggle-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
            transition: color 0.3s ease;
        }

        .toggle-btn:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .btn-submit {
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #D4AF37, #F7E27E);
            color: #111;
            font-size: 16px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 4px;
        }

        .btn-submit:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 24px rgba(212, 175, 55, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .message {
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            text-align: center;
            margin-bottom: 10px;
        }

        .message.success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .message.error {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2ecc71;
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(46, 204, 113, 0.3);
            transform: translateX(120%);
            transition: transform 0.4s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
            font-family: 'Inter', sans-serif;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast i {
            font-size: 20px;
        }

        /* Dashboard Styles */
        .dashboard {
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, .55), rgba(0, 0, 0, .55)),
                        url("https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=1800&q=80");
            background-size: cover;
            color: white;
            padding: 40px;
        }

        .dashboard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            margin-bottom: 40px;
        }

        .dashboard-nav h2 {
            color: #D4AF37;
            font-size: 28px;
        }

        .dashboard-nav .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .dashboard-nav .user-info span {
            color: rgba(255, 255, 255, 0.9);
        }

        .dashboard-nav .user-info .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 30px;
            background: #D4AF37;
            color: #111;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dashboard-nav .user-info .btn:hover {
            transform: scale(1.05);
        }

        .dashboard-content {
            text-align: center;
            margin-top: 80px;
        }

        .dashboard-content h1 {
            font-size: 70px;
            margin-bottom: 20px;
        }

        .dashboard-content p {
            font-size: 24px;
            opacity: .8;
        }

        .dashboard-stats {
            display: flex;
            justify-content: center;
            gap: 80px;
            margin-top: 80px;
        }

        .dashboard-stats div {
            text-align: center;
        }

        .dashboard-stats h2 {
            color: #D4AF37;
            font-size: 42px;
        }

        .dashboard-stats p {
            font-size: 18px;
            opacity: .7;
        }

        /* Users Table */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .users-table th,
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .users-table th {
            background: rgba(212, 175, 55, 0.2);
            color: #D4AF37;
            font-weight: 600;
        }

        .users-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .badge {
            background: #D4AF37;
            color: #111;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 25px;
            background: #D4AF37;
            color: #111;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            transform: scale(1.05);
        }

        @media (max-width: 480px) {
            .card {
                padding: 28px 20px;
            }
            h1 {
                font-size: 24px;
            }
            .dashboard-content h1 {
                font-size: 40px;
            }
            .dashboard-stats {
                flex-direction: column;
                gap: 30px;
            }
            .dashboard-nav {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .toast {
                top: 10px;
                right: 10px;
                left: 10px;
                padding: 14px 18px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<?php if ($is_logged_in && !$show_users): ?>
    <!-- ===== DASHBOARD VIEW ===== -->
    <div class="dashboard">
        <nav class="dashboard-nav">
            <h2>✨ Welcome</h2>
            <div class="user-info">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['email']); ?></span>
                <a href="?view_users=true" class="btn"><i class="fas fa-users"></i> View Users</a>
                <a href="?logout=true" class="btn" style="background: #ff6b6b; color: white;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <div class="dashboard-content">
            <h1>Welcome Back!</h1>
            <p>You have successfully logged in.</p>
            <div class="dashboard-stats">
                <div>
                    <h2>500+</h2>
                    <p>Projects</p>
                </div>
                <div>
                    <h2>50+</h2>
                    <p>Designers</p>
                </div>
                <div>
                    <h2>15+</h2>
                    <p>Years Experience</p>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($show_users && $is_logged_in): ?>
    <!-- ===== VIEW USERS ===== -->
    <div style="max-width: 1000px; margin: 40px auto; background: rgba(20,20,20,0.9); border-radius: 20px; padding: 30px; color: white;">
        <h2 style="color: #D4AF37; margin-bottom: 20px;"><i class="fas fa-users"></i> Registered Users</h2>
        
        <div style="margin-bottom: 20px; padding: 15px; background: rgba(212,175,55,0.1); border-radius: 10px;">
            <strong>Total Users:</strong> <?php echo mysqli_num_rows($users_result); ?> accounts
        </div>

        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($users_result)): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><span class="badge">Active</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="login.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

<?php else: ?>
    <!-- ===== LOGIN / SIGNUP PAGE ===== -->
    <div class="container">
        <div class="card">
            <h1>Welcome</h1>
            <p class="subtitle">Sign in or create your account</p>

            <!-- Tabs -->
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="login"><i class="fas fa-sign-in-alt"></i> Login</button>
                <button class="tab-btn" data-tab="signup"><i class="fas fa-user-plus"></i> Sign Up</button>
            </div>

            <!-- Show Message -->
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <div class="form-container active" id="loginForm">
                <form method="POST">
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" placeholder="Enter password" required>
                            <button type="button" class="toggle-btn"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn-submit">Sign In</button>
                </form>
            </div>

            <!-- Signup Form -->
            <div class="form-container" id="signupForm">
                <form method="POST">
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" placeholder="Create password (min 6 chars)" required minlength="6">
                            <button type="button" class="toggle-btn"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" name="signup" class="btn-submit">Create Account</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Toast Notification -->
<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMessage">Login successful!</span>
</div>

<script>
    // ===== TAB SWITCHING =====
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const tab = this.dataset.tab;
            document.querySelectorAll('.form-container').forEach(container => {
                container.classList.remove('active');
            });
            document.getElementById(tab + 'Form').classList.add('active');
        });
    });

    // ===== TOGGLE PASSWORD =====
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    });

    // ===== SHOW TOAST =====
    <?php if (isset($login_success) && $login_success): ?>
        const toast = document.getElementById('toast');
        document.getElementById('toastMessage').textContent = 'Login successful! Welcome back.';
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    <?php endif; ?>

    <?php if (isset($message) && $message_type === 'success'): ?>
        const toast2 = document.getElementById('toast');
        document.getElementById('toastMessage').textContent = '<?php echo $message; ?>';
        toast2.classList.add('show');
        setTimeout(() => {
            toast2.classList.remove('show');
        }, 3000);
    <?php endif; ?>
</script>

</body>
</html>