<?php
session_start();
require_once '../config.php'; // Adjust path as needed

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in and is a client
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'user' || !isset($_SESSION['id'])) {
        $response['message'] = 'Unauthorized access.';
        echo json_encode($response);
        exit;
    }

    $clientId = $_SESSION['id'];
    $subject = trim($_POST['subject'] ?? '');
    $serviceType = trim($_POST['service_type'] ?? '');
    $therapistName = trim($_POST['therapist_name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $clientName = $_SESSION['username'] ?? 'Unknown Client'; // Get client name from session
    $clientEmail = $_SESSION['email'] ?? 'unknown@example.com'; // Get client email from session

    $errors = [];

    // Basic validation
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    if (empty($message)) {
        $errors[] = 'Your question is required.';
    }
    // Add more robust validation if needed (e.g., length limits)

    if (empty($errors)) {
        
        $sql = "INSERT INTO client_inquiries (client_id, full_name, email, subject, service_type, therapist_name, message) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "issssss", $clientId, $clientName, $clientEmail, $subject, $serviceType, $therapistName, $message);

            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'Your question has been sent successfully! We will get back to you soon.';

                // --- Optional: Send Email Notification to Admin/Therapist ---
                $to = "admin@greenlifewellness.com"; // Replace with your admin/therapist email
                $email_subject = "New Client Inquiry: " . $subject;
                $email_body = "A new inquiry has been submitted by client " . $clientName . " (ID: " . $clientId . ").\n\n";
                $email_body .= "Client Email: " . $clientEmail . "\n";
                $email_body .= "Related Service: " . ($serviceType ?: 'N/A') . "\n";
                $email_body .= "Specific Therapist: " . ($therapistName ?: 'N/A') . "\n";
                $email_body .= "Subject: " . $subject . "\n\n";
                $email_body .= "Message:\n" . $message . "\n";
                $email_body .= "Submitted At: " . date('Y-m-d H:i:s') . "\n";

                $headers = "From: webmaster@yourdomain.com\r\n"; // Replace with your website's email
                $headers .= "Reply-To: " . $clientEmail . "\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();

                
            } else {
                $response['message'] = 'Failed to save your question. Please try again later.';
                error_log("Error inserting inquiry: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = 'Database error preparing statement.';
            error_log("Error preparing inquiry statement: " . mysqli_error($conn));
        }
    } else {
        $response['message'] = 'Please correct the following errors:';
        $response['errors'] = $errors;
    }
} else {
    $response['message'] = 'Invalid request method.';
}

mysqli_close($conn);
echo json_encode($response);
?>