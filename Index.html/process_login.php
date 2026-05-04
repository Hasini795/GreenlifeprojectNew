<?php
session_start(); // Start the session at the very beginning of the script
require_once 'config.php'; // Include database configuration

$loginError = ''; // Variable to store login error messages

// Check if the login form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        $loginError = "Please enter both email and password.";
    } else {
        // Prepare a SELECT statement to fetch user by email
        $sql = "SELECT id, user_id, username, password, role FROM users WHERE email = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                // Check if email exists
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $user_id, $username, $hashed_password, $role);
                    mysqli_stmt_fetch($stmt);

                    // Verify password
                    if (password_verify($password, $hashed_password)) {
                        // Password is correct, start a new session
                        $_SESSION['loggedin'] = true;
                        $_SESSION['id'] = $id;
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $role;

                        // Redirect user to appropriate dashboard based on their role
                        switch ($role) {
                            case 'admin':
                                header("location: Dashboards/admin_dashboard.php");
                                break;
                            case 'therapist':
                                header("location: Dashboards/therapist_dashboard.php");
                                break;
                            case 'user': // Assuming 'user' role is for clients
                                header("location: Dashboards/client_dashboard.php");
                                break;
                            default:
                                // Fallback for undefined roles
                                $loginError = "Your account has an unassigned role. Please contact support.";
                                session_destroy(); // Destroy session for unassigned roles
                                break;
                        }
                        exit; // Important: terminate script after redirection
                    } else {
                        // Password is not valid
                        $loginError = "Invalid email or password.";
                    }
                } else {
                    // Email doesn't exist
                    $loginError = "Invalid email or password.";
                }
            } else {
                $loginError = "Oops! Something went wrong. Please try again later.";
                error_log("Login execution error: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
            $loginError = "Database error: Could not prepare statement.";
            error_log("Login prepare error: " . mysqli_error($conn));
        }
    }
    mysqli_close($conn); // Close connection if not redirected
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Greenlife Wellness Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-form-card {
            max-width: 500px;
            width: 100%;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .auth-form-card h2 {
            margin-bottom: 30px;
            color: #28a745;
            font-weight: 600;
            text-align: center;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
            border-color: #28a745;
        }
        .btn-greenlife-primary {
            background-color: #28a745;
            color: #fff;
            border-radius: 10px;
            padding: 10px 25px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
            border: none;
            width: 100%;
        }
        .btn-greenlife-primary:hover {
            background-color: #218838;
        }
        .text-success {
            color: #28a745 !important;
        }
        .text-decoration-none {
            text-decoration: none !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card auth-form-card mx-auto">
            <h2>Login to Your Account</h2>
            <?php if (!empty($loginError)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($loginError); ?>
                </div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label visually-hidden">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label visually-hidden">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-greenlife-primary">Login</button>
                </div>
                <p class="text-center">
                    Don't have an account? <a href="index.html#register" class="text-success text-decoration-none">Register here</a>
                </p>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
