<?php
session_start();
require 'db.php';
include 'lang.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = trim($_POST["username"]);
        $email    = trim($_POST["email"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        // Validate role against enum values
        $allowed_roles = ['Doctor', 'Normal_User'];
        $role = in_array($_POST['type'] ?? '', $allowed_roles) ? $_POST['type'] : 'Normal_User';

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = $t['err_fill_all'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $t['err_invalid_email'];
        } elseif ($password !== $confirm_password) {
            $error = $t['err_pwd_mismatch'];
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = $t['err_user_exists'];
            } else {
                $stmt = $conn->prepare("INSERT INTO users (username, email, Password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $password, $role);
                if ($stmt->execute()) {
                    $success = $t['reg_success'];
                } else {
                    $error = $t['err_reg_failed'] . ": " . $conn->error;
                }
            }
            $stmt->close();
        }
    } else {
        $username = trim($_POST["username"]);
        $password = $_POST["password"];

        if (empty($username) || empty($password)) {
            $error = $t['err_fill_both'];
        } else {
            $stmt = $conn->prepare("SELECT id, username, Password, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if ($password === $user['Password']) {
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = $user['role'];
                    // Route based on role
                    if ($user['role'] === 'Doctor') {
                        header("Location: doctor_dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $error = $t['err_invalid_creds'];
                }
            } else {
                $error = $t['err_invalid_creds'];
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['login'] ?> & <?= $t['register'] ?> - <?= $t['predictor_title'] ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 50px 40px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        h1 {
            color: #000;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: #666;
            font-size: 15px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #000;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
            background: #f9f9f9;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #000;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        button {
            width: 100%;
            padding: 14px;
            background: #000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        button:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        button:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
        }

        .toggle-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .toggle-link a {
            color: #000;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .toggle-link a:hover {
            text-decoration: underline;
        }

        .footer-note {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
            color: #999;
            font-size: 12px;
        }

        #register-form {
            display: none;
        }
        .form-type{
            width: 100%;
            margin-bottom: 20px;    
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            background: #f9f9f9;
            color: #000;
        }
        #type{
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            background: #f9f9f9;
            color: #000;    
        }

        /* Language Switcher on Login */
        .login-lang-switcher {
            display: flex;
            justify-content: center;
            gap: 2px;
            margin-bottom: 24px;
            background: #f0f0f0;
            border-radius: 10px;
            padding: 3px;
        }
        .login-lang-switcher a {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-decoration: none;
            transition: all 0.2s;
        }
        .login-lang-switcher a:hover {
            color: #000;
            background: #e0e0e0;
        }
        .login-lang-switcher a.active {
            background: #000;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Language Switcher -->
        <div class="login-lang-switcher">
            <?php
            $current_url = strtok($_SERVER['REQUEST_URI'], '?');
            $params = $_GET;
            foreach ($lang_names as $code => $name):
                $params['lang'] = $code;
                $qs = http_build_query($params);
                $active = ($code === $lang) ? ' active' : '';
            ?>
                <a href="<?= htmlspecialchars($current_url . '?' . $qs) ?>" class="<?= $active ?>"><?= $name ?></a>
            <?php endforeach; ?>
        </div>

        <div id="login-section">
            <header>
                <h1><?= $t['login_title'] ?></h1>
                <p class="subtitle"><?= $t['login_subtitle'] ?></p>
            </header>

            <?php if ($error && !isset($_POST['action'])): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username"><?= $t['username'] ?></label>
                    <input type="text" id="username" name="username" required 
                           value="<?= isset($_POST['username']) && !isset($_POST['action']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="password"><?= $t['password'] ?></label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit"><?= $t['login'] ?></button>
            </form>

            <div class="toggle-link">
                <?= $t['no_account'] ?> <a onclick="toggleForms()"><?= $t['register_here'] ?></a>
            </div>
        </div>

        <div id="register-form">
            <header>
                <h1><?= $t['register_title'] ?></h1>
                <p class="subtitle"><?= $t['register_subtitle'] ?></p>
            </header>

            <?php if ($error && isset($_POST['action']) && $_POST['action'] === 'register'): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="reg_username"><?= $t['username'] ?></label>
                    <input type="text" id="reg_username" name="username" required
                           value="<?= isset($_POST['username']) && isset($_POST['action']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="reg_email"><?= $t['email'] ?></label>
                    <input type="email" id="reg_email" name="email" required
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="reg_password"><?= $t['password'] ?></label>
                    <input type="password" id="reg_password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password"><?= $t['confirm_password'] ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-type">
                    <label for="type"><?= $t['type'] ?></label>
                    <select id="type" name="type">
                        <option value="Doctor"><?= $t['doctor_type'] ?></option>
                        <option value="Normal_User"><?= $t['normal_user_type'] ?></option>
                    </select>
                </div>

                <button type="submit"><?= $t['register'] ?></button>
            </form>

            <div class="toggle-link">
                <?= $t['has_account'] ?> <a onclick="toggleForms()"><?= $t['login_here'] ?></a>
            </div>
        </div>

        <div class="footer-note">
            <?= $t['footer_note'] ?>
        </div>
    </div>

    <script>
        function toggleForms() {
            const loginSection = document.getElementById('login-section');
            const registerSection = document.getElementById('register-form');
            
            if (loginSection.style.display === 'none') {
                loginSection.style.display = 'block';
                registerSection.style.display = 'none';
            } else {
                loginSection.style.display = 'none';
                registerSection.style.display = 'block';
            }
        }
        <?php if (isset($_POST['action']) && $_POST['action'] === 'register' && $error): ?>
            toggleForms();
        <?php endif; ?>
    </script>
</body>

</html>
