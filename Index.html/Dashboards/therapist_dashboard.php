<?php
session_start(); // Start the session

// Define constants for better maintainability and readability
define('LOGIN_REDIRECT_PATH', '../login.php');
define('CONFIG_PATH', '../config.php');

// Check if user is logged in and is a therapist
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'therapist') {
    header('location: ' . LOGIN_REDIRECT_PATH); 
    exit;
}

// Include database config
require_once CONFIG_PATH;

// Check if connection is successful
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    
    die("<div class='alert alert-danger' role='alert'>Database connection failed. Please try again later.</div>");
}

$welcomeMessage = "Welcome, Therapist " . htmlspecialchars($_SESSION['username']) . "!";
$pageTitle = "Therapist Dashboard";
$therapistId = $_SESSION['id']; // Get the logged-in therapist's user ID

$therapistAppointmentsHtml = ''; // HTML for therapist's appointments table
$manageNotesHtml = '';           // HTML for client notes section
$myScheduleHtml = '';            // HTML for therapist schedule section

/**
 * Function to safely execute a prepared statement and return the result.
 *
 * @param mysqli $conn The database connection object.
 * @param string $sql The SQL query string with placeholders.
 * @param string $types A string containing one or more characters which specify the types for the corresponding bind variables.
 * @param mixed ...$params The variables to bind to the parameter markers.
 * @return mysqli_result|false The result set on success, false on failure.
 */
function execute_prepared_query(mysqli $conn, string $sql, string $types = '', ...$params) {
    if ($stmt = mysqli_prepare($conn, $sql)) {
        if (!empty($types) && !empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        } else {
            error_log("Error executing prepared statement: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return false;
        }
    } else {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
}

// --- Fetch Appointments for the Logged-in Therapist ---
$sql_therapist_appointments = "SELECT a.id, a.full_name AS client_name, a.email AS client_email,
                                a.phone, a.service_type, a.appointment_date, a.appointment_time,
                                a.notes, a.status, a.booked_at, u.username AS client_username, u.id AS client_user_id
                                FROM appointments a
                                JOIN users u ON a.client_id = u.id
                                WHERE a.therapist_id = ?
                                ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$result_therapist_appointments = execute_prepared_query($conn, $sql_therapist_appointments, "i", $therapistId);

if ($result_therapist_appointments) {
    if (mysqli_num_rows($result_therapist_appointments) > 0) {
        $therapistAppointmentsHtml .= "
        <div class='table-responsive mt-5' id='therapist-appointments-section'>
            <h3 class='section-heading mb-4'>My Appointments</h3>
            <table class='table table-hover table-striped align-middle rounded-4 overflow-hidden shadow-sm'>
                <thead class='table-success text-white'>
                    <tr>
                        <th scope='col'>#</th>
                        <th scope='col'>Client Name</th>
                        <th scope='col'>Client Email</th>
                        <th scope='col'>Service</th>
                        <th scope='col'>Date</th>
                        <th scope='col'>Time</th>
                        <th scope='col'>Status</th>
                        <th scope='col'>Actions</th>
                    </tr>
                </thead>
                <tbody>
        ";
        $counter = 1;
        while ($row = mysqli_fetch_assoc($result_therapist_appointments)) {
            $statusClass = '';
            switch ($row['status']) {
                case 'confirmed':
                    $statusClass = 'text-success fw-bold';
                    break;
                case 'pending':
                    $statusClass = 'text-warning fw-bold';
                    break;
                case 'cancelled':
                    $statusClass = 'text-danger fw-bold';
                    break;
                case 'completed':
                    $statusClass = 'text-info fw-bold';
                    break;
                default:
                    $statusClass = ''; // No specific class for unknown status
                    break;
            }

            $therapistAppointmentsHtml .= "
                    <tr>
                        <td>" . $counter++ . "</td>
                        <td>" . htmlspecialchars($row['client_name']) . " (<small>" . htmlspecialchars($row['client_username']) . "</small>)</td>
                        <td>" . htmlspecialchars($row['client_email']) . "</td>
                        <td>" . htmlspecialchars($row['service_type']) . "</td>
                        <td>" . htmlspecialchars($row['appointment_date']) . "</td>
                        <td>" . htmlspecialchars($row['appointment_time']) . "</td>
                        <td class='" . $statusClass . "'>" . ucfirst(htmlspecialchars($row['status'])) . "</td>
                        <td>
                            <button type='button' class='btn btn-sm btn-info me-1 view-appointment-details'
                                data-id='" . htmlspecialchars($row['id']) . "'
                                data-clientname='" . htmlspecialchars($row['client_name']) . " (" . htmlspecialchars($row['client_username']) . ")'
                                data-clientemail='" . htmlspecialchars($row['client_email']) . "'
                                data-clientphone='" . htmlspecialchars($row['phone']) . "'
                                data-servicetype='" . htmlspecialchars($row['service_type']) . "'
                                data-appointmentdate='" . htmlspecialchars($row['appointment_date']) . "'
                                data-appointmenttime='" . htmlspecialchars($row['appointment_time']) . "'
                                data-notes='" . htmlspecialchars($row['notes']) . "'
                                data-status='" . htmlspecialchars($row['status']) . "'
                                title='View Details'><i class='fas fa-eye'></i></button>
                            <button type='button' class='btn btn-sm btn-success me-1 mark-confirmed-btn' data-id='" . htmlspecialchars($row['id']) . "' title='Mark Confirmed'><i class='fas fa-check-circle'></i></button>
                            <button type='button' class='btn btn-sm btn-danger cancel-appointment-btn' data-id='" . htmlspecialchars($row['id']) . "' title='Cancel Appointment'><i class='fas fa-times-circle'></i></button>
                        </td>
                    </tr>
            ";
        }
        $therapistAppointmentsHtml .= "
                </tbody>
            </table>
        </div>
        ";
    } else {
        $therapistAppointmentsHtml = "<div class='alert alert-info mt-5' role='alert'>You have no appointments assigned to you yet.</div>";
    }
} else {
    $therapistAppointmentsHtml = "<div class='alert alert-danger mt-5' role='alert'>Error fetching your appointments.</div>";
}


// --- Manage Notes Section: Fetch clients the therapist has appointments with ---
$sql_my_clients = "SELECT DISTINCT u.id AS client_id, u.username AS client_username, u.first_name, u.last_name, u.email
                   FROM users u
                   JOIN appointments a ON u.id = a.client_id
                   WHERE a.therapist_id = ?
                   ORDER BY u.username";

$result_my_clients = execute_prepared_query($conn, $sql_my_clients, "i", $therapistId);

if ($result_my_clients) {
    if (mysqli_num_rows($result_my_clients) > 0) {
        $manageNotesHtml .= "
        <div class='table-responsive mt-5' id='manage-notes-section'>
            <h3 class='section-heading mb-4'>Clients with Appointments</h3>
            <table class='table table-hover table-striped align-middle rounded-4 overflow-hidden shadow-sm'>
                <thead class='table-success text-white'>
                    <tr>
                        <th scope='col'>#</th>
                        <th scope='col'>Client Name</th>
                        <th scope='col'>Client Username</th>
                        <th scope='col'>Client Email</th>
                        <th scope='col'>Actions</th>
                    </tr>
                </thead>
                <tbody>
        ";
        $counter = 1;
        while ($row = mysqli_fetch_assoc($result_my_clients)) {
            $manageNotesHtml .= "
                    <tr>
                        <td>" . $counter++ . "</td>
                        <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>
                        <td>" . htmlspecialchars($row['client_username']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>
                            <button type='button' class='btn btn-sm btn-primary me-1 view-notes-btn' data-clientid='" . htmlspecialchars($row['client_id']) . "' data-clientname='" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "' title='View Notes'><i class='fas fa-file-alt'></i> View Notes</button>
                            <button type='button' class='btn btn-sm btn-info add-note-btn' data-clientid='" . htmlspecialchars($row['client_id']) . "' data-clientname='" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "' title='Add New Note'><i class='fas fa-plus'></i> Add Note</button>
                        </td>
                    </tr>
            ";
        }
        $manageNotesHtml .= "
                </tbody>
            </table>
        </div>
        ";
    } else {
        $manageNotesHtml = "<div class='alert alert-info mt-5' role='alert'>You have no clients with appointments to manage notes for yet.</div>";
    }
} else {
    $manageNotesHtml = "<div class='alert alert-danger mt-5' role='alert'>Error fetching clients for notes.</div>";
}

// --- My Schedule Section: Fetch therapist's schedule ---
$sql_my_schedule = "SELECT id, schedule_date, start_time, end_time, is_available
                    FROM therapist_schedules
                    WHERE therapist_id = ?
                    ORDER BY schedule_date ASC, start_time ASC";

$result_my_schedule = execute_prepared_query($conn, $sql_my_schedule, "i", $therapistId);

if ($result_my_schedule) {
    if (mysqli_num_rows($result_my_schedule) > 0) {
        $myScheduleHtml .= "
        <div class='table-responsive mt-5' id='my-schedule-section'>
            <h3 class='section-heading mb-4'>My Availability Schedule</h3>
            <button type='button' class='btn btn-success mb-3' data-bs-toggle='modal' data-bs-target='#addScheduleModal'>
                <i class='fas fa-plus-circle me-2'></i> Add New Availability
            </button>
            <table class='table table-hover table-striped align-middle rounded-4 overflow-hidden shadow-sm'>
                <thead class='table-success text-white'>
                    <tr>
                        <th scope='col'>#</th>
                        <th scope='col'>Date</th>
                        <th scope='col'>Start Time</th>
                        <th scope='col'>End Time</th>
                        <th scope='col'>Available</th>
                        <th scope='col'>Actions</th>
                    </tr>
                </thead>
                <tbody>
        ";
        $counter = 1;
        while ($row = mysqli_fetch_assoc($result_my_schedule)) {
            $availabilityText = $row['is_available'] ? 'Yes' : 'No';
            $availabilityClass = $row['is_available'] ? 'text-success' : 'text-danger';

            $myScheduleHtml .= "
                    <tr>
                        <td>" . $counter++ . "</td>
                        <td>" . htmlspecialchars($row['schedule_date']) . "</td>
                        <td>" . htmlspecialchars(date('h:i A', strtotime($row['start_time']))) . "</td>
                        <td>" . htmlspecialchars(date('h:i A', strtotime($row['end_time']))) . "</td>
                        <td class='" . $availabilityClass . " fw-bold'>" . $availabilityText . "</td>
                        <td>
                            <button type='button' class='btn btn-sm btn-warning me-1 edit-schedule-btn'
                                data-id='" . htmlspecialchars($row['id']) . "'
                                data-date='" . htmlspecialchars($row['schedule_date']) . "'
                                data-start='" . htmlspecialchars($row['start_time']) . "'
                                data-end='" . htmlspecialchars($row['end_time']) . "'
                                data-available='" . htmlspecialchars($row['is_available']) . "'
                                title='Edit Schedule'><i class='fas fa-edit'></i></button>
                            <button type='button' class='btn btn-sm btn-danger delete-schedule-btn' data-id='" . htmlspecialchars($row['id']) . "' title='Delete Schedule'><i class='fas fa-trash-alt'></i></button>
                        </td>
                    </tr>
            ";
        }
        $myScheduleHtml .= "
                </tbody>
            </table>
        </div>
        ";
    } else {
        $myScheduleHtml = "<div class='alert alert-info mt-5' role='alert'>You haven't set up your schedule yet.</div>
            <button type='button' class='btn btn-success mb-3' data-bs-toggle='modal' data-bs-target='#addScheduleModal'>
                <i class='fas fa-plus-circle me-2'></i> Add New Availability
            </button>";
    }
} else {
    $myScheduleHtml = "<div class='alert alert-danger mt-5' role='alert'>Error fetching your schedule.</div>";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        /* Modal specific styles */
        .modal-content {
            border-radius: 15px;
        }
        .modal-header {
            background-color: #28a745;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .modal-footer .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }
        .modal-footer .btn-primary:hover {
            background-color: #218838;
            border-color: #218838;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#dashboard-overview">
                <i class="fas fa-leaf"></i> Therapist Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#dashboard-overview">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#therapist-appointments-section">My Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#manage-notes-section">Client Notes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#my-schedule-section">My Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="dashboard-overview" class="dashboard-container py-5 text-center">
        <div class="container">
            <h1 class="section-heading mb-4"><?php echo $welcomeMessage; ?></h1>
            <p class='lead'>Manage your appointments, client notes, and schedule.</p>
            <div class='row g-4'>
                <div class='col-md-6 col-lg-4'>
                    <div class='card h-100 shadow-sm rounded-4'>
                        <div class='card-body text-center p-4'>
                            <i class='fas fa-calendar-check service-icon mb-3'></i>
                            <h5 class='card-title fw-bold'>My Appointments</h5>
                            <p class='card-text'>View your upcoming and past appointments.</p>
                            <a href='#therapist-appointments-section' class='btn btn-outline-success rounded-pill'>View Appointments</a>
                        </div>
                    </div>
                </div>
                <div class='col-md-6 col-lg-4'>
                    <div class='card h-100 shadow-sm rounded-4'>
                        <div class='card-body text-center p-4'>
                            <i class='fas fa-notes-medical service-icon mb-3'></i>
                            <h5 class='card-title fw-bold'>Client Notes</h5>
                            <p class='card-text'>Access and update notes for your clients.</p>
                            <a href='#manage-notes-section' class='btn btn-outline-success rounded-pill'>Manage Notes</a>
                        </div>
                    </div>
                </div>
                <div class='col-md-6 col-lg-4'>
                    <div class='card h-100 shadow-sm rounded-4'>
                        <div class='card-body text-center p-4'>
                            <i class='fas fa-business-time service-icon mb-3'></i>
                            <h5 class='card-title fw-bold'>My Schedule</h5>
                            <p class='card-text'>Set your availability and manage your work schedule.</p>
                            <a href='#my-schedule-section' class='btn btn-outline-success rounded-pill'>Edit Schedule</a>
                        </div>
                    </div>
                </div>
            </div>

            <section id="therapist-appointments-section">
                <?php echo $therapistAppointmentsHtml; ?>
            </section>

            <section id="manage-notes-section">
                <?php echo $manageNotesHtml; ?>
            </section>

            <section id="my-schedule-section">
                <?php echo $myScheduleHtml; ?>
            </section>
        </div>
    </section>

    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addScheduleModalLabel">Add New Availability Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addScheduleForm">
                        <div class="mb-3">
                            <label for="scheduleDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="scheduleDate" name="schedule_date" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="startTime" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" name="start_time" required>
                            </div>
                            <div class="col-6">
                                <label for="endTime" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" name="end_time" required>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="isAvailable" name="is_available" checked>
                            <label class="form-check-label" for="isAvailable">Mark as Available</label>
                        </div>
                        <div id="scheduleMessage" class="mt-3"></div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewAppointmentDetailsModal" tabindex="-1" aria-labelledby="viewAppointmentDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAppointmentDetailsModalLabel">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="appointmentDetailsForm">
                        <input type="hidden" id="appointmentIdDetail" name="appointment_id">
                        <div class="mb-3">
                            <label for="clientNameDetail" class="form-label">Client Name</label>
                            <input type="text" class="form-control" id="clientNameDetail" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="clientEmailDetail" class="form-label">Client Email</label>
                            <input type="email" class="form-control" id="clientEmailDetail" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="clientPhoneDetail" class="form-label">Client Phone</label>
                            <input type="text" class="form-control" id="clientPhoneDetail" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="serviceTypeDetail" class="form-label">Service Type</label>
                            <input type="text" class="form-control" id="serviceTypeDetail" readonly>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="appointmentDateDetail" class="form-label">Date</label>
                                <input type="date" class="form-control" id="appointmentDateDetail" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="appointmentTimeDetail" class="form-label">Time</label>
                                <input type="time" class="form-control" id="appointmentTimeDetail" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="appointmentNotesDetail" class="form-label">Notes</label>
                            <textarea class="form-control" id="appointmentNotesDetail" rows="3" readonly></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="appointmentStatusDetail" class="form-label">Status</label>
                            <select class="form-select" id="appointmentStatusDetail" name="new_status">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div id="appointmentDetailsMessage" class="mt-3"></div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clientNotesModal" tabindex="-1" aria-labelledby="clientNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientNotesModalLabel">Notes for <span id="notesClientName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="notesClientId">
                    <div id="notesList" class="list-group">
                        </div>
                    <div id="clientNotesMessage" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="openAddNoteModalBtn"><i class="fas fa-plus"></i> Add New Note</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addClientNoteModal" tabindex="-1" aria-labelledby="addClientNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientNoteModalLabel">Add New Note for <span id="addNoteClientName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addClientNoteForm">
                        <input type="hidden" id="addNoteClientId" name="client_id">
                        <input type="hidden" name="therapist_id" value="<?php echo htmlspecialchars($therapistId); ?>">
                        <div class="mb-3">
                            <label for="noteContent" class="form-label">Note Content</label>
                            <textarea class="form-control" id="noteContent" name="note_content" rows="5" required></textarea>
                        </div>
                        <div id="addNoteMessage" class="mt-3"></div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editScheduleModalLabel">Edit Availability Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editScheduleForm">
                        <input type="hidden" id="editScheduleId" name="schedule_id">
                        <div class="mb-3">
                            <label for="editScheduleDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="editScheduleDate" name="schedule_date" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="editStartTime" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="editStartTime" name="start_time" required>
                            </div>
                            <div class="col-6">
                                <label for="editEndTime" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="editEndTime" name="end_time" required>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editIsAvailable" name="is_available">
                            <label class="form-check-label" for="editIsAvailable">Mark as Available</label>
                        </div>
                        <div id="editScheduleMessage" class="mt-3"></div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <footer class="footer text-center">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> Greenlife Wellness. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to handle AJAX responses
            function handleResponse(response, messageElement, modalToHide, reloadPage = false) {
                if (response.success) {
                    messageElement.html('<div class="alert alert-success">' + response.message + '</div>');
                    if (reloadPage) {
                        setTimeout(function() {
                            location.reload();
                        }, 1000); // Reload after 1 second
                    } else if (modalToHide) {
                        // For modals that don't require full page reload, close them
                        setTimeout(function() {
                            $(modalToHide).modal('hide');
                        }, 1000);
                    }
                } else {
                    messageElement.html('<div class="alert alert-danger">' + (response.message || 'An unknown error occurred.') + '</div>');
                }
            }

            // --- Appointment Actions ---
            $(document).on('click', '.view-appointment-details', function() {
                const appointmentId = $(this).data('id');
                const clientName = $(this).data('clientname');
                const clientEmail = $(this).data('clientemail');
                const clientPhone = $(this).data('clientphone');
                const serviceType = $(this).data('servicetype');
                const appointmentDate = $(this).data('appointmentdate');
                const appointmentTime = $(this).data('appointmenttime');
                const notes = $(this).data('notes');
                const status = $(this).data('status');

                $('#appointmentIdDetail').val(appointmentId);
                $('#clientNameDetail').val(clientName);
                $('#clientEmailDetail').val(clientEmail);
                $('#clientPhoneDetail').val(clientPhone);
                $('#serviceTypeDetail').val(serviceType);
                $('#appointmentDateDetail').val(appointmentDate);
                $('#appointmentTimeDetail').val(appointmentTime);
                $('#appointmentNotesDetail').val(notes);
                $('#appointmentStatusDetail').val(status);
                $('#appointmentDetailsMessage').empty(); // Clear previous messages
                $('#viewAppointmentDetailsModal').modal('show');
            });

            $('#appointmentDetailsForm').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.ajax({
                    url: '../api/update_appointment_status.php', // Create this API endpoint
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        handleResponse(response, $('#appointmentDetailsMessage'), '#viewAppointmentDetailsModal', true);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + error);
                        $('#appointmentDetailsMessage').html('<div class="alert alert-danger">Error updating status. Please try again.</div>');
                    }
                });
            });

            $(document).on('click', '.mark-confirmed-btn, .cancel-appointment-btn', function() {
                const appointmentId = $(this).data('id');
                let newStatus = '';
                let confirmMessage = '';
                let apiEndpoint = '../api/update_appointment_status.php'; // Reuse endpoint

                if ($(this).hasClass('mark-confirmed-btn')) {
                    newStatus = 'confirmed';
                    confirmMessage = 'Are you sure you want to mark this appointment as confirmed?';
                } else if ($(this).hasClass('cancel-appointment-btn')) {
                    newStatus = 'cancelled';
                    confirmMessage = 'Are you sure you want to cancel this appointment? This action cannot be undone.';
                }

                if (confirm(confirmMessage)) {
                    $.ajax({
                        url: apiEndpoint,
                        type: 'POST',
                        data: {
                            appointment_id: appointmentId,
                            new_status: newStatus
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                location.reload(); // Reload to reflect changes
                            } else {
                                alert(response.message || 'Error performing action.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error: ' + status + error);
                            alert('An error occurred. Please try again.');
                        }
                    });
                }
            });

            // --- Client Notes Actions ---
            $(document).on('click', '.view-notes-btn', function() {
                const clientId = $(this).data('clientid');
                const clientName = $(this).data('clientname');

                $('#notesClientId').val(clientId);
                $('#notesClientName').text(clientName);
                $('#clientNotesMessage').empty(); // Clear previous messages
                $('#notesList').empty().html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading notes...</div>'); // Loading indicator

                $.ajax({
                    url: '../api/fetch_client_notes.php', // Create this API endpoint
                    type: 'GET',
                    data: { client_id: clientId, therapist_id: <?php echo $therapistId; ?> },
                    dataType: 'json',
                    success: function(response) {
                        $('#notesList').empty(); // Clear loading message
                        if (response.success && response.notes.length > 0) {
                            response.notes.forEach(note => {
                                $('#notesList').append(`
                                    <a href="#" class="list-group-item list-group-item-action flex-column align-items-start mb-2 rounded shadow-sm">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1 text-success"><i class="fas fa-file-signature me-2"></i>Note on ${note.created_at_date}</h5>
                                            <small class="text-muted">${note.created_at_time}</small>
                                        </div>
                                        <p class="mb-1 text-break">${note.note_content}</p>
                                        <small class="text-muted">By: ${note.therapist_username || 'You'}</small>
                                    </a>
                                `);
                            });
                        } else {
                            $('#notesList').html('<div class="alert alert-info mt-3" role="alert">No notes found for this client.</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + error);
                        $('#clientNotesMessage').html('<div class="alert alert-danger">Error fetching client notes.</div>');
                    }
                });
                $('#clientNotesModal').modal('show');
            });

            $(document).on('click', '#openAddNoteModalBtn', function() {
                const clientId = $('#notesClientId').val();
                const clientName = $('#notesClientName').text();

                $('#addNoteClientId').val(clientId);
                $('#addNoteClientName').text(clientName);
                $('#noteContent').val(''); // Clear previous content
                $('#addNoteMessage').empty(); // Clear previous messages

                $('#clientNotesModal').modal('hide'); // Hide view notes modal
                $('#addClientNoteModal').modal('show'); // Show add note modal
            });

            $('#addClientNoteForm').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.ajax({
                    url: '../api/add_client_note.php', // Create this API endpoint
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        handleResponse(response, $('#addNoteMessage'), '#addClientNoteModal', true); // Reload page to update notes list
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + error);
                        $('#addNoteMessage').html('<div class="alert alert-danger">Error adding note. Please try again.</div>');
                    }
                });
            });

            // --- Schedule Actions ---
            $('#addScheduleForm').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.ajax({
                    url: '../api/add_therapist_schedule.php', // Create this API endpoint
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        handleResponse(response, $('#scheduleMessage'), '#addScheduleModal', true);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + error);
                        $('#scheduleMessage').html('<div class="alert alert-danger">Error adding schedule. Please try again.</div>');
                    }
                });
            });

            $(document).on('click', '.edit-schedule-btn', function() {
                const scheduleId = $(this).data('id');
                const date = $(this).data('date');
                const startTime = $(this).data('start');
                const endTime = $(this).data('end');
                const isAvailable = $(this).data('available');

                $('#editScheduleId').val(scheduleId);
                $('#editScheduleDate').val(date);
                $('#editStartTime').val(startTime);
                $('#editEndTime').val(endTime);
                $('#editIsAvailable').prop('checked', parseInt(isAvailable) === 1);
                $('#editScheduleMessage').empty(); // Clear previous messages
                $('#editScheduleModal').modal('show');
            });

            $('#editScheduleForm').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.ajax({
                    url: '../api/update_therapist_schedule.php', // Create this API endpoint
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        handleResponse(response, $('#editScheduleMessage'), '#editScheduleModal', true);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + error);
                        $('#editScheduleMessage').html('<div class="alert alert-danger">Error updating schedule. Please try again.</div>');
                    }
                });
            });

            $(document).on('click', '.delete-schedule-btn', function() {
                const scheduleId = $(this).data('id');
                if (confirm('Are you sure you want to delete this schedule slot?')) {
                    $.ajax({
                        url: '../api/delete_therapist_schedule.php', // Create this API endpoint
                        type: 'POST',
                        data: { schedule_id: scheduleId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                location.reload(); // Reload to reflect changes
                            } else {
                                alert(response.message || 'Error deleting schedule.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error: ' + status + error);
                            alert('An error occurred. Please try again.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>