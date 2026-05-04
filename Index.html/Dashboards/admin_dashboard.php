<?php
session_start(); // Start the session

// Check if user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: ../login.php'); // Redirect to login page if not authorized
    exit;
}

// Include database config (path adjusted to go up one directory)
require_once '../config.php';

$pageTitle = "User Management";
$usersHtml = ''; // Variable to store the HTML for users table
$message = '';   // For displaying success/error messages after actions
$addFormVisible = false; // To control visibility of the add user form after submission or error

// Initialize variables for the add user form to retain values on error
$new_userId = $new_userName = $new_firstName = $new_lastName = $new_email = $new_role = '';

// Initialize search query variable
$search_query = trim($_GET['search_query'] ?? '');

// --- Handle User Actions (Delete, Add, Edit - placeholders for now) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // --- Handle Delete User Action ---
        if ($action === 'delete_user') {
            $user_id_to_act = $_POST['user_id_to_act'] ?? ''; // This is the user's 'id' from the database table, not 'user_id' string

            if (!empty($user_id_to_act)) {
                // Prepare a DELETE statement
                $sql_delete = "DELETE FROM users WHERE id = ?";
                if ($stmt_delete = mysqli_prepare($conn, $sql_delete)) {
                    mysqli_stmt_bind_param($stmt_delete, "i", $user_id_to_act);
                    if (mysqli_stmt_execute($stmt_delete)) {
                        $message = "<div class='alert alert-success' role='alert'>User deleted successfully!</div>";
                    } else {
                        $message = "<div class='alert alert-danger' role='alert'>Error deleting user: " . mysqli_error($conn) . "</div>";
                        error_log("Error deleting user: " . mysqli_error($conn));
                    }
                    mysqli_stmt_close($stmt_delete);
                } else {
                    $message = "<div class='alert alert-danger' role='alert'>Database error: Could not prepare delete statement.</div>";
                    error_log("Error preparing delete statement: " . mysqli_error($conn));
                }
            }
        }

        // --- Handle Add User Action ---
        else if ($action === 'add_user') {
            $addFormVisible = true; // Keep form visible if trying to add
            $new_userId = trim($_POST['new_userId'] ?? '');
            $new_userName = trim($_POST['new_userName'] ?? '');
            $new_firstName = trim($_POST['new_firstName'] ?? '');
            $new_lastName = trim($_POST['new_lastName'] ?? '');
            $new_email = trim($_POST['new_email'] ?? '');
            $new_password = trim($_POST['new_password'] ?? '');
            $new_role = trim($_POST['new_role'] ?? '');
            
            $add_errors = [];

            // Input Validation for New User
            if (empty($new_userId) || strlen($new_userId) > 50) { $add_errors[] = "User ID is required and max 50 chars."; }
            if (empty($new_userName) || strlen($new_userName) > 50) { $add_errors[] = "Username is required and max 50 chars."; }
            if (empty($new_firstName)) { $add_errors[] = "First Name is required."; }
            if (empty($new_lastName)) { $add_errors[] = "Last Name is required."; }
            if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) { $add_errors[] = "Valid Email is required."; }
            if (empty($new_password) || strlen($new_password) < 8) { $add_errors[] = "Password is required and must be at least 8 characters."; }
            $allowedRoles = ['user', 'therapist', 'admin'];
            if (empty($new_role) || !in_array($new_role, $allowedRoles)) { $add_errors[] = "Invalid role selected."; }

            // Check for duplicate User ID or Email
            if (empty($add_errors)) {
                $check_sql = "SELECT id FROM users WHERE user_id = ? OR email = ?";
                if ($stmt_check = mysqli_prepare($conn, $check_sql)) {
                    mysqli_stmt_bind_param($stmt_check, "ss", $new_userId, $new_email);
                    mysqli_stmt_execute($stmt_check);
                    mysqli_stmt_store_result($stmt_check);
                    if (mysqli_stmt_num_rows($stmt_check) > 0) {
                        // More specific error for duplicates
                        $check_uid_sql = "SELECT id FROM users WHERE user_id = ?";
                        $stmt_uid = mysqli_prepare($conn, $check_uid_sql);
                        mysqli_stmt_bind_param($stmt_uid, "s", $new_userId);
                        mysqli_stmt_execute($stmt_uid);
                        mysqli_stmt_store_result($stmt_uid);
                        if (mysqli_stmt_num_rows($stmt_uid) > 0) {
                            $add_errors[] = "User ID '{$new_userId}' is already taken.";
                        }
                        mysqli_stmt_close($stmt_uid);

                        $check_email_sql = "SELECT id FROM users WHERE email = ?";
                        $stmt_email = mysqli_prepare($conn, $check_email_sql);
                        mysqli_stmt_bind_param($stmt_email, "s", $new_email);
                        mysqli_stmt_execute($stmt_email);
                        mysqli_stmt_store_result($stmt_email);
                        if (mysqli_stmt_num_rows($stmt_email) > 0) {
                            $add_errors[] = "Email address '{$new_email}' is already registered.";
                        }
                        mysqli_stmt_close($stmt_email);
                    }
                    mysqli_stmt_close($stmt_check);
                } else {
                    $add_errors[] = "Database error during duplicate check.";
                    error_log("Database error (add user prepare check): " . mysqli_error($conn));
                }
            }

            // If no validation or duplicate errors, proceed with insertion
            if (empty($add_errors)) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_insert = "INSERT INTO users (user_id, username, first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
                if ($stmt_insert = mysqli_prepare($conn, $sql_insert)) {
                    mysqli_stmt_bind_param($stmt_insert, "sssssss", $new_userId, $new_userName, $new_firstName, $new_lastName, $new_email, $hashedPassword, $new_role);
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $message = "<div class='alert alert-success' role='alert'>New user '{$new_userName}' added successfully!</div>";
                        // Clear form fields on success
                        $new_userId = $new_userName = $new_firstName = $new_lastName = $new_email = $new_role = '';
                        $addFormVisible = false; // Hide form on success
                    } else {
                        $message = "<div class='alert alert-danger' role='alert'>Error adding user: " . mysqli_error($conn) . "</div>";
                        error_log("Error adding user: " . mysqli_error($conn));
                    }
                    mysqli_stmt_close($stmt_insert);
                } else {
                    $message = "<div class='alert alert-danger' role='alert'>Database error: Could not prepare insert statement.</div>";
                    error_log("Error preparing insert statement: " . mysqli_error($conn));
                }
            } else {
                // Display validation errors
                $message = "<div class='alert alert-danger' role='alert'><h4 class='alert-heading'>Error adding user:</h4><ul>";
                foreach ($add_errors as $err) {
                    $message .= "<li>" . htmlspecialchars($err) . "</li>";
                }
                $message .= "</ul></div>";
            }
        }
    }
}


// --- Fetch All Users from Database (with search functionality) ---
$sql_users = "SELECT id, user_id, username, first_name, last_name, email, role, created_at FROM users";
$params = [];
$types = "";

if (!empty($search_query)) {
    $sql_users .= " WHERE user_id LIKE ? OR username LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR role LIKE ?";
    $search_param = '%' . $search_query . '%';
    $params = [$search_param, $search_param, $search_param, $search_param, $search_param, $search_param];
    $types = "ssssss";
}
$sql_users .= " ORDER BY created_at DESC";


if ($stmt_users = mysqli_prepare($conn, $sql_users)) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt_users, $types, ...$params);
    }
    mysqli_stmt_execute($stmt_users);
    $result_users = mysqli_stmt_get_result($stmt_users);

    if ($result_users) {
        if (mysqli_num_rows($result_users) > 0) {
            $usersHtml .= "
            <div class='table-responsive mt-5'>
                <h3 class='section-heading mb-4'>All System Users</h3>
                <table class='table table-hover table-striped align-middle rounded-4 overflow-hidden shadow-sm'>
                    <thead class='table-success text-white'>
                        <tr>
                            <th scope='col'>#</th>
                            <th scope='col'>User ID</th>
                            <th scope='col'>Username</th>
                            <th scope='col'>Full Name</th>
                            <th scope='col'>Email</th>
                            <th scope='col'>Role</th>
                            <th scope='col'>Created At</th>
                            <th scope='col'>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            ";
            $counter = 1;
            while ($row = mysqli_fetch_assoc($result_users)) {
                $roleClass = '';
                switch ($row['role']) {
                    case 'admin':
                        $roleClass = 'text-danger fw-bold';
                        break;
                    case 'therapist':
                        $roleClass = 'text-info fw-bold';
                        break;
                    case 'user':
                        $roleClass = 'text-primary fw-bold';
                        break;
                }

                $usersHtml .= "
                            <tr>
                                <td>" . $counter++ . "</td>
                                <td>" . htmlspecialchars($row['user_id']) . "</td>
                                <td>" . htmlspecialchars($row['username']) . "</td>
                                <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                <td class='" . $roleClass . "'>" . ucfirst(htmlspecialchars($row['role'])) . "</td>
                                <td>" . htmlspecialchars(date('Y-m-d H:i', strtotime($row['created_at']))) . "</td>
                                <td>
                                    <a href='#' class='btn btn-sm btn-warning me-1' title='Edit User'><i class='fas fa-edit'></i></a>
                                    <form action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "' method='POST' style='display:inline-block;' onsubmit='return confirm(\"Are you sure you want to delete this user?\");'>
                                        <input type='hidden' name='action' value='delete_user'>
                                        <input type='hidden' name='user_id_to_act' value='" . htmlspecialchars($row['id']) . "'>
                                        <button type='submit' class='btn btn-sm btn-danger' title='Delete User'><i class='fas fa-trash-alt'></i></button>
                                    </form>
                                </td>
                            </tr>
                ";
            }
            $usersHtml .= "
                    </tbody>
                </table>
            </div>
            ";
        } else {
            $usersHtml = "<div class='alert alert-info mt-5' role='alert'>No users found in the system yet.</div>";
            if (!empty($search_query)) {
                $usersHtml = "<div class='alert alert-info mt-5' role='alert'>No users found matching your search query: <strong>" . htmlspecialchars($search_query) . "</strong></div>";
            }
        }
        mysqli_free_result($result_users); // Free result set
    } else {
        $usersHtml = "<div class='alert alert-danger mt-5' role='alert'>Error fetching users: " . mysqli_error($conn) . "</div>";
        error_log("Error fetching users for admin: " . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt_users);
} else {
    $usersHtml = "<div class='alert alert-danger mt-5' role='alert'>Database error: Could not prepare user fetch statement.</div>";
    error_log("Error preparing user fetch statement: " . mysqli_error($conn));
}

// Close database connection
mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Greenlife Wellness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: #343a40; }
        .navbar { background-color: #28a745; padding-top: 1rem; padding-bottom: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar-brand, .nav-link { color: #fff !important; font-weight: 500; }
        .navbar-brand { font-size: 1.8rem; font-weight: 700; }
        .navbar-brand i { margin-right: 10px; }
        .dashboard-container { padding: 80px 0; }
        .section-heading { font-size: 2.5rem; font-weight: 600; margin-bottom: 40px; color: #28a745; text-align: center; }
        .card { border: none; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .card:hover { transform: translateY(-5px); }
        .service-icon { font-size: 3rem; color: #28a745; }
        .btn-outline-success { color: #28a745; border-color: #28a745; }
        .btn-outline-success:hover { background-color: #28a745; color: #fff; }
        .footer { background-color: #343a40; color: #fff; padding: 30px 0; border-top-left-radius: 20px; border-top-right-radius: 20px; margin-top: 50px; }
        /* Table specific styles */
        .table-responsive {
            margin-top: 50px;
            border-radius: 15px; /* Added rounded corners to the responsive wrapper */
            overflow: hidden; /* Ensures child elements respect border-radius */
            box-shadow: 0 8px 20px rgba(0,0,0,0.05); /* Lighter shadow for table */
        }
        .table {
            --bs-table-bg: #ffffff; /* Bootstrap 5 variable for table background */
            --bs-table-color: #343a40;
        }
        .table-hover tbody tr:hover {
            --bs-table-hover-bg: #e2f0e6; /* Lighter green on hover */
        }
        .table-striped tbody tr:nth-of-type(odd) {
            --bs-table-accent-bg: #f5fff8; /* Very light green for striped rows */
        }
        .table-success {
            --bs-table-bg: #28a745; /* Darker green for header */
            --bs-table-color: #ffffff;
            border-color: #28a745;
        }
        .table-success th {
            border-bottom-color: #218838; /* Slightly darker border for header */
        }
        /* Add User Form Specific Styles */
        .add-user-section {
            background-color: #f0fff0; /* Very light green */
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-top: 50px;
            text-align: left;
        }
        .add-user-section .form-control, .add-user-section .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #c3e6cb;
        }
        .add-user-section .form-control:focus, .add-user-section .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
            border-color: #28a745;
        }
        .btn-add-user {
            background-color: #17a2b8; /* Info blue */
            color: #fff;
            border-radius: 10px;
            padding: 10px 25px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
            border: none;
            width: 100%;
        }
        .btn-add-user:hover {
            background-color: #138496;
        }
        .search-bar-container {
            margin-bottom: 30px;
        }
        .search-bar-container .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }
        .search-bar-container .btn-success {
            border-radius: 0.5rem;
            padding: 0.75rem 1.25rem;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-leaf"></i> Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php">Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php#all-appointments-section">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="dashboard-container py-5 text-center">
        <div class="container">
            <h1 class="section-heading mb-4"><?php echo $pageTitle; ?></h1>
            <?php echo $message; // Display success/error messages ?>

            <div class="text-start mb-4">
                <button class="btn btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#addUserFormCollapse" aria-expanded="<?php echo $addFormVisible ? 'true' : 'false'; ?>" aria-controls="addUserFormCollapse">
                    <i class="fas fa-user-plus me-2"></i> Add New User
                </button>
            </div>

            <div class="collapse <?php echo $addFormVisible ? 'show' : ''; ?>" id="addUserFormCollapse">
                <section class="add-user-section">
                    <h3 class="section-heading-small mb-4 text-center" style="color: #28a745; font-size: 1.8rem;">Add New User Account</h3>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <input type="hidden" name="action" value="add_user">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_userId" class="form-label">User ID</label>
                                <input type="text" class="form-control" id="new_userId" name="new_userId" value="<?php echo htmlspecialchars($new_userId); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_userName" class="form-label">Username</label>
                                <input type="text" class="form-control" id="new_userName" name="new_userName" value="<?php echo htmlspecialchars($new_userName); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="new_firstName" name="new_firstName" value="<?php echo htmlspecialchars($new_firstName); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="new_lastName" name="new_lastName" value="<?php echo htmlspecialchars($new_lastName); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="new_email" name="new_email" value="<?php echo htmlspecialchars($new_email); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-4">
                            <label for="new_role" class="form-label">Role</label>
                            <select class="form-select" id="new_role" name="new_role" required>
                                <option value="">Select Role</option>
                                <option value="user" <?php echo ($new_role === 'user') ? 'selected' : ''; ?>>User (Client)</option>
                                <option value="therapist" <?php echo ($new_role === 'therapist') ? 'selected' : ''; ?>>Therapist</option>
                                <option value="admin" <?php echo ($new_role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-add-user">Add User</button>
                        </div>
                    </form>
                </section>
            </div>

            <hr class="my-5">

            <div class="search-bar-container text-center">
                <h3 class="section-heading-small mb-3" style="color: #28a745; font-size: 1.8rem;">Search Users</h3>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="d-flex justify-content-center">
                    <div class="input-group" style="max-width: 500px;">
                        <input type="text" class="form-control" placeholder="Search by User ID, Username, Name, Email, or Role" name="search_query" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button class="btn btn-success" type="submit"><i class="fas fa-search me-2"></i> Search</button>
                    </div>
                </form>
            </div>

            <?php echo $usersHtml; // Display the generated users table ?>
        </div>
    </section>

    <footer class="footer text-center">
        <div class="container">
            <p>&copy; 2025 Greenlife Wellness Center. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>