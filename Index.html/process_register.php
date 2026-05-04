<?php
// process_register.php

// Include the database configuration file
require_once 'config.php';

// Initialize variables to store form data and response messages
$userId = $userName = $firstName = $lastName = $email = $password = $role = '';
// Default response structure for JSON output
$response = ['success' => false, 'message' => 'An unknown error occurred.'];
$errors = []; // Array to collect validation errors

// This script should only be accessed via a POST request (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Input Validation and Sanitization ---

    // Validate User ID
    if (isset($_POST['Userid']) && !empty(trim($_POST['Userid']))) {
        $userId = trim($_POST['Userid']);
        // Sanitize string to remove potentially harmful characters
        $userId = filter_var($userId, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
        // Add length validation
        if (strlen($userId) > 50) {
            $errors[] = "User ID is too long (max 50 characters).";
        }
    } else {
        $errors[] = "User ID cannot be empty.";
    }

    // Validate User Name
    if (isset($_POST['UserName']) && !empty(trim($_POST['UserName']))) {
        $userName = trim($_POST['UserName']);
        $userName = filter_var($userName, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
    } else {
        $errors[] = "User Name cannot be empty.";
    }

    // Validate First Name
    if (isset($_POST['FirstName']) && !empty(trim($_POST['FirstName']))) {
        $firstName = trim($_POST['FirstName']);
        $firstName = filter_var($firstName, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
    } else {
        $errors[] = "First Name cannot be empty.";
    }

    // Validate Last Name
    if (isset($_POST['LastName']) && !empty(trim($_POST['LastName']))) {
        $lastName = trim($_POST['LastName']);
        $lastName = filter_var($lastName, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
    } else {
        $errors[] = "Last Name cannot be empty.";
    }

    // Validate Email
    if (isset($_POST['email']) && !empty(trim($_POST['email']))) {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
    } else {
        $errors[] = "Email cannot be empty.";
    }

    // Validate Password
    if (isset($_POST['password']) && !empty(trim($_POST['password']))) {
        $password = trim($_POST['password']);
        // Enforce a minimum password length for security
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        // You can add more password complexity rules here (e.g., require numbers, special characters, mixed case)
    } else {
        $errors[] = "Password cannot be empty.";
    }

    // Validate Role
    if (isset($_POST['role']) && !empty(trim($_POST['role']))) {
        $role = trim($_POST['role']);
        $allowedRoles = ['user', 'therapist', 'admin']; // Define accepted roles
        if (!in_array($role, $allowedRoles)) {
            $errors[] = "Invalid role selected.";
        }
    } else {
        $errors[] = "Role cannot be empty.";
    }

    // --- Database Operations (only if no validation errors so far) ---

    if (empty($errors)) {
        // Before inserting, check if the User ID or Email already exists in the database
        $check_sql = "SELECT id FROM users WHERE user_id = ? OR email = ?";
        if ($stmt_check = mysqli_prepare($conn, $check_sql)) {
            mysqli_stmt_bind_param($stmt_check, "ss", $userId, $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            // If any row is returned, it means either the user_id or email already exists
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                // More specific error messages for better user feedback
                // Check if user_id exists
                $temp_stmt_user_id = mysqli_prepare($conn, "SELECT id FROM users WHERE user_id = ?");
                mysqli_stmt_bind_param($temp_stmt_user_id, "s", $userId);
                mysqli_stmt_execute($temp_stmt_user_id);
                mysqli_stmt_store_result($temp_stmt_user_id);
                if (mysqli_stmt_num_rows($temp_stmt_user_id) > 0) {
                    $errors[] = "User ID '{$userId}' is already taken.";
                }
                mysqli_stmt_close($temp_stmt_user_id);

                // Check if email exists
                $temp_stmt_email = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
                mysqli_stmt_bind_param($temp_stmt_email, "s", $email);
                mysqli_stmt_execute($temp_stmt_email);
                mysqli_stmt_store_result($temp_stmt_email);
                if (mysqli_stmt_num_rows($temp_stmt_email) > 0) {
                    $errors[] = "Email address '{$email}' is already registered.";
                }
                mysqli_stmt_close($temp_stmt_email);
            }
            mysqli_stmt_close($stmt_check);
        } else {
            $errors[] = "Database error during duplicate check. Please try again.";
            // Log the actual database error for server-side debugging
            error_log("Database error (prepare check statement): " . mysqli_error($conn));
        }
    }


    // If all validation and duplicate checks pass, proceed with insertion
    if (empty($errors)) {
        // Hash the password securely before storing it in the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL INSERT statement using parameterized queries to prevent SQL injection
        $sql = "INSERT INTO users (user_id, username, first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind parameters to the prepared statement
            // 'sssssss' indicates that all 7 parameters are strings
            mysqli_stmt_bind_param($stmt, "sssssss", $userId, $userName, $firstName, $lastName, $email, $hashedPassword, $role);

            // Execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = "Registration successful! You can now log in.";
            } else {
                // Log the database execution error for debugging on the server
                error_log("Database insertion failed: " . mysqli_stmt_error($stmt));
                // Provide a generic, user-friendly error message
                $response['message'] = "Registration failed. Please try again later.";
            }
            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            // Log the error if the SQL statement preparation fails
            error_log("Failed to prepare SQL statement: " . mysqli_error($conn));
            $response['message'] = "An internal server error occurred during registration. Please try again.";
        }
    } else {
        // If there are validation errors, populate the response with them
        $response['message'] = "Please correct the following errors:";
        $response['errors'] = $errors;
    }

    // Close the database connection
    mysqli_close($conn);

    // Set the content type header to JSON and encode the response array
    header('Content-Type: application/json');
    echo json_encode($response);
    exit(); // Terminate the script after sending the JSON response

} else {
    // If the script is accessed directly (not via POST), redirect to the main page
    header('Location: index.html');
    exit();
}
?>
