<?php
session_start(); // Start the session

// Include database configuration
require_once '../config.php'; // Path adjusted for dashboards subdirectory

// Initialize response array for JSON output
$response = ['success' => false, 'message' => 'An unknown error occurred.'];
$errors = [];

// Ensure only logged-in clients can book
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'user') {
    $response['message'] = 'You must be logged in as a client to book an appointment.';
    
} else {
    $client_id = $_SESSION['id']; // Get the logged-in client's user ID (from the 'users' table's 'id' column)

    // Check if the form was submitted via POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve and sanitize form inputs
        // Using null coalescing operator (?? '') to avoid undefined index notices if a field is missing
        $fullName = trim($_POST['fullName'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $phone = trim($_POST['phone'] ?? '');
        $serviceType = trim($_POST['service'] ?? '');
        $appointmentDate = trim($_POST['date'] ?? '');
        $appointmentTime = trim($_POST['time'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // --- Server-side Validation ---
        // These checks ensure that essential data is present and in the correct format
        if (empty($fullName)) {
            $errors[] = "Full Name is required.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid Email is required.";
        }
        // Example: Validate if the serviceType is one of the expected values
        $allowedServices = [
            "Therapeutic Massage", "Holistic Nutrition", "Mindfulness & Yoga",
            "Stress Management", "Ayurvedic Herbal Remedies", "Hydrotherapy & Physiotherapy", "Other"
        ];
        if (empty($serviceType) || !in_array($serviceType, $allowedServices)) {
            $errors[] = "Please select a valid service type.";
        }
        if (empty($appointmentDate)) {
            $errors[] = "Appointment Date is required.";
        } else if (!strtotime($appointmentDate)) { // Basic check for valid date format
            $errors[] = "Invalid appointment date format.";
        }
        if (empty($appointmentTime)) {
            $errors[] = "Appointment Time is required.";
        } else if (!preg_match("/^([01]\d|2[0-3]):?([0-5]\d)$/", $appointmentTime)) { // Basic time format check HH:MM
            $errors[] = "Invalid appointment time format.";
        }

        // Additional validation: Ensure appointment date is not in the past
        if (!empty($appointmentDate) && strtotime($appointmentDate) < strtotime(date('Y-m-d'))) {
            $errors[] = "Appointment date cannot be in the past.";
        }
        
       
        if (empty($errors)) {
            // Prepare an INSERT statement for the appointments table
            // Using parameterized queries to prevent SQL injection vulnerabilities
            $sql = "INSERT INTO appointments (client_id, full_name, email, phone, service_type, appointment_date, appointment_time, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

            if ($stmt = mysqli_prepare($conn, $sql)) {
                
                // i = integer (for client_id)
                // s = string (for all other fields)
                mysqli_stmt_bind_param($stmt, "isssssss", $client_id, $fullName, $email, $phone, $serviceType, $appointmentDate, $appointmentTime, $notes);

                if (mysqli_stmt_execute($stmt)) {
                    // Booking was successful
                    $response['success'] = true;
                    $response['message'] = "Appointment booked successfully! We will confirm shortly.";
                } else {
                    // Log the actual database error for server-side debugging
                    error_log("Appointment booking failed: " . mysqli_stmt_error($stmt));
                    // Provide a generic, user-friendly error message to the client
                    $response['message'] = "Failed to book appointment. Please try again later. (Error code: " . mysqli_stmt_errno($stmt) . ")";
                }
                // Close the prepared statement
                mysqli_stmt_close($stmt);
            } else {
                // Log error if SQL statement preparation fails
                error_log("Failed to prepare appointment SQL statement: " . mysqli_error($conn));
                $response['message'] = "An internal server error occurred during booking. Please try again.";
            }
        } else {
            // Validation failed, return the collected errors to the client
            $response['message'] = "Please correct the following errors:";
            $response['errors'] = $errors;
        }
    } else {
        // If the request method is not POST, it means someone tried to access the script directly
        $response['message'] = 'Invalid request method. Form must be submitted via POST.';
    }
}

// Close the database connection
mysqli_close($conn);

// Set the HTTP header to indicate JSON content
header('Content-Type: application/json');
// Encode the PHP response array into a JSON string and output it
echo json_encode($response);
exit(); // Terminate the script after sending the JSON response
?>
