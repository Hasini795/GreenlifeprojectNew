<?php
session_start(); // Start the session

// Check if user is logged in and is a client (role 'user')
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'user') {
    header('location: ../login.php'); // Redirect to login page if not authorized
    exit;
}

// Include database config (path adjusted to go up one directory)
require_once '../config.php';

$welcomeMessage = "Welcome, " . htmlspecialchars($_SESSION['username']) . "!";
$pageTitle = "Client Dashboard";
$clientId = $_SESSION['id']; // Get the logged-in client's ID

$myBookingsHtml = ''; // Variable to store the HTML for client's appointments table

// Variable to hold the login success message
$loginSuccessAlert = '';
if (isset($_SESSION['login_success_message'])) {
    $loginSuccessAlert = "<div class='alert alert-success alert-dismissible fade show mt-3' role='alert'>" .
                         htmlspecialchars($_SESSION['login_success_message']) .
                         "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    unset($_SESSION['login_success_message']); // Clear the message after displaying it
}


// --- Fetch Client's Appointments from Database ---
$sql_my_appointments = "SELECT id, full_name, email, phone, service_type, appointment_date, appointment_time, notes, status, booked_at
                             FROM appointments
                             WHERE client_id = ?
                             ORDER BY appointment_date DESC, appointment_time DESC";

if ($stmt_my_appointments = mysqli_prepare($conn, $sql_my_appointments)) {
    mysqli_stmt_bind_param($stmt_my_appointments, "i", $clientId);
    mysqli_stmt_execute($stmt_my_appointments);
    $result_my_appointments = mysqli_stmt_get_result($stmt_my_appointments);

    if ($result_my_appointments && mysqli_num_rows($result_my_appointments) > 0) {
        $myBookingsHtml .= "
        <div class='table-responsive mt-5'>
            <h3 class='section-heading mb-4'>My Bookings</h3>
            <table class='table table-hover table-striped align-middle rounded-4 overflow-hidden shadow-sm'>
                <thead class='table-success text-white'>
                    <tr>
                        <th scope='col'>#</th>
                        <th scope='col'>Service</th>
                        <th scope='col'>Date</th>
                        <th scope='col'>Time</th>
                        <th scope='col'>Status</th>
                        <th scope='col'>Notes</th>
                        <th scope='col'>Booked On</th>
                        <th scope='col'>Actions</th>
                    </tr>
                </thead>
                <tbody>
        ";
        $counter = 1;
        while ($row = mysqli_fetch_assoc($result_my_appointments)) {
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
            }

            $myBookingsHtml .= "
                        <tr>
                            <td>" . $counter++ . "</td>
                            <td>" . htmlspecialchars($row['service_type']) . "</td>
                            <td>" . htmlspecialchars($row['appointment_date']) . "</td>
                            <td>" . htmlspecialchars($row['appointment_time']) . "</td>
                            <td class='" . $statusClass . "'>" . ucfirst(htmlspecialchars($row['status'])) . "</td>
                            <td>" . htmlspecialchars(substr($row['notes'], 0, 50)) . (strlen($row['notes']) > 50 ? '...' : '') . "</td>
                            <td>" . htmlspecialchars(date('Y-m-d H:i', strtotime($row['booked_at']))) . "</td>
                            <td>
                                <a href='#' class='btn btn-sm btn-info me-1' title='View Details'><i class='fas fa-eye'></i></a>
                                " . ($row['status'] == 'pending' ? "<a href='#' class='btn btn-sm btn-danger' title='Cancel Appointment'><i class='fas fa-times-circle'></i></a>" : "") . "
                            </td>
                        </tr>
            ";
        }
        $myBookingsHtml .= "
                </tbody>
            </table>
        </div>
        ";
    } else {
        $myBookingsHtml = "<div class='alert alert-info mt-5' role='alert'>You have no bookings yet.</div>";
    }
    mysqli_stmt_close($stmt_my_appointments);
} else {
    $myBookingsHtml = "<div class='alert alert-danger mt-5' role='alert'>Error fetching your bookings: " . mysqli_error($conn) . "</div>";
    error_log("Error fetching client appointments: " . mysqli_error($conn));
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
        .booking-form-section, .inquiry-form-section {
            background-color: #e9f5ee; /* Light green background */
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-top: 50px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #c3e6cb; /* Green-ish border */
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
            border-color: #28a745;
        }
        .btn-greenlife-primary-form {
            background-color: #28a745;
            color: #fff;
            border-radius: 10px;
            padding: 10px 25px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
            border: none;
            width: 100%;
        }
        .btn-greenlife-primary-form:hover {
            background-color: #218838;
        }
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
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-leaf"></i> Client Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#main-dashboard">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#book-appointment-section">Book Appointment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#my-bookings-section">My Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#ask-therapist-section">Ask a Question</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="main-dashboard" class="dashboard-container py-5 text-center">
        <div class="container">
            <?php echo $loginSuccessAlert; // Display the login success message here ?>
            <h1 class="section-heading mb-4"><?php echo $welcomeMessage; ?></h1>
            <p class='lead'>Here you can manage your appointments and wellness journey.</p>
            <div class='row g-4'>
                <div class='col-md-6 col-lg-4'>
                    <div class='card h-100 shadow-sm rounded-4'>
                        <div class='card-body text-center p-4'>
                            <i class='fas fa-calendar-plus service-icon mb-3'></i>
                            <h5 class="card-title fw-bold">Book New Appointment</h5>
                            <p class='card-text'>Schedule your next wellness session with ease.</p>
                            <a href="#book-appointment-section" class="btn btn-outline-success rounded-pill">Book Now</a>
                        </div>
                    </div>
                </div>
                <div class='col-md-6 col-lg-4'>
                    <div class='card h-100 shadow-sm rounded-4'>
                        <div class='card-body text-center p-4'>
                            <i class='fas fa-history service-icon mb-3'></i>
                            <h5 class='card-title fw-bold'>My Bookings</h5>
                            <p class='card-text'>View your past and upcoming appointments.</p>
                            <a href='#my-bookings-section' class='btn btn-outline-success rounded-pill'>View My Bookings</a>
                        </div>
                    </div>
                </div>
                <div class='col-md-6 col-lg-4'>
                    <div class='card h-100 shadow-sm rounded-4'>
                        <div class='card-body text-center p-4'>
                            <i class='fas fa-question-circle service-icon mb-3'></i>
                            <h5 class='card-title fw-bold'>Submit Inquiry</h5>
                            <p class='card-text'>Have a question? Send us a message.</p>
                            <a href='#ask-therapist-section' class='btn btn-outline-success rounded-pill'>Ask a Question</a>
                        </div>
                    </div>
                </div>
            </div>

            <section id="book-appointment-section" class="booking-form-section mt-5">
                <h2 class="section-heading">Book Your New Appointment</h2>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <form id="appointmentForm">
                            <div id="bookingMessage" class="mt-3"></div> <div class="mb-3">
                                <label for="bookingName" class="form-label">Your Full Name</label>
                                <input type="text" class="form-control" id="bookingName" name="fullName" placeholder="John Doe" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="bookingEmail" class="form-label">Your Email</label>
                                <input type="email" class="form-control" id="bookingEmail" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="bookingPhone" class="form-label">Phone Number (Optional)</label>
                                <input type="tel" class="form-control" id="bookingPhone" name="phone" placeholder="+94 77 123 4567">
                            </div>
                            <div class="mb-3">
                                <label for="serviceType" class="form-label">Preferred Service</label>
                                <select class="form-select" id="serviceType" name="service" required>
                                    <option selected disabled value="">Select a service...</option>
                                    <option value="Therapeutic Massage">Therapeutic Massage</option>
                                    <option value="Holistic Nutrition">Holistic Nutrition</option>
                                    <option value="Mindfulness & Yoga">Mindfulness & Yoga</option>
                                    <option value="Stress Management">Stress Management</option>
                                    <option value="Ayurvedic Herbal Remedies">Ayurvedic Herbal Remedies</option>
                                    <option value="Hydrotherapy & Physiotherapy">Hydrotherapy & Physiotherapy</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="appointmentDate" class="form-label">Preferred Date</label>
                                <input type="date" class="form-control" id="appointmentDate" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="appointmentTime" class="form-label">Preferred Time</label>
                                <input type="time" class="form-control" id="appointmentTime" name="time" required>
                            </div>
                            <div class="mb-3">
                                <label for="bookingNotes" class="form-label">Additional Notes (Optional)</label>
                                <textarea class="form-control" id="bookingNotes" name="notes" rows="3" placeholder="Any specific requests or conditions?"></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-greenlife-primary-form">Book Appointment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <hr>

            <section id="ask-therapist-section" class="inquiry-form-section mt-5">
                <h2 class="section-heading">Ask a Question to Our Therapists</h2>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <form id="inquiryForm">
                            <div id="inquiryMessage" class="mt-3"></div> <div class="mb-3">
                                <label for="inquirySubject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="inquirySubject" name="subject" placeholder="Question about a service or general inquiry" required>
                            </div>
                            <div class="mb-3">
                                <label for="inquiryServiceType" class="form-label">Related Service (Optional)</label>
                                <select class="form-select" id="inquiryServiceType" name="service_type">
                                    <option selected value="">Select a service...</option>
                                    <option value="Therapeutic Massage">Therapeutic Massage</option>
                                    <option value="Holistic Nutrition">Holistic Nutrition</option>
                                    <option value="Mindfulness & Yoga">Mindfulness & Yoga</option>
                                    <option value="Stress Management">Stress Management</option>
                                    <option value="Ayurvedic Herbal Remedies">Ayurvedic Herbal Remedies</option>
                                    <option value="Hydrotherapy & Physiotherapy">Hydrotherapy & Physiotherapy</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                </select>
                            </div>
                             <div class="mb-3">
                                <label for="inquiryTherapist" class="form-label">Specific Therapist (Optional)</label>
                                <input type="text" class="form-control" id="inquiryTherapist" name="therapist_name" placeholder="Enter therapist's name if known (e.g., Dr. Smith)">
                                <small class="form-text text-muted">If you have a specific therapist in mind, you can mention them here.</small>
                            </div>
                            <div class="mb-3">
                                <label for="inquiryMessageText" class="form-label">Your Question</label>
                                <textarea class="form-control" id="inquiryMessageText" name="message" rows="5" placeholder="Type your question here..." required></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-greenlife-primary-form">Send Question</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <hr>

            <section id="my-bookings-section" class="mt-5">
                <?php echo $myBookingsHtml; // Display the generated client appointments table ?>
            </section>
        </div>
    </section>

    <footer class="footer text-center">
        <div class="container">
            <p>&copy; 2025 Greenlife Wellness Center. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Populate email and full name from session data (PHP variables echoed into JS)
            const bookingEmailInput = document.getElementById('bookingEmail');
            const bookingNameInput = document.getElementById('bookingName');

            // Set min date for appointmentDate to today
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0'); // Months start at 0!
            const day = String(today.getDate()).padStart(2, '0');
            const minDate = `${year}-${month}-${day}`;
            document.getElementById('appointmentDate').setAttribute('min', minDate);

            // Handle appointment form submission via Fetch API
            document.getElementById('appointmentForm').addEventListener('submit', async function(event) {
                event.preventDefault(); // Prevent default form submission

                const form = event.target;
                const formData = new FormData(form);
                const bookingMessageDiv = document.getElementById('bookingMessage');
                bookingMessageDiv.innerHTML = ''; // Clear previous messages

                try {
                    const response = await fetch('process_booking.php', {
                        method: 'POST',
                        body: formData // FormData works directly with fetch for form submission
                    });

                    const result = await response.json(); // Parse the JSON response

                    let alertClass = result.success ? 'alert-success' : 'alert-danger';
                    let messageHtml = result.message;

                    if (!result.success && result.errors) {
                        messageHtml += '<ul class="mb-0">';
                        result.errors.forEach(err => {
                            messageHtml += `<li>${err}</li>`;
                        });
                        messageHtml += '</ul>';
                    }

                    bookingMessageDiv.className = `alert ${alertClass}`;
                    bookingMessageDiv.innerHTML = messageHtml;

                    if (result.success) {
                        form.reset(); // Clear form on successful submission
                        // Optional: Reload the page or update the 'My Bookings' section dynamically
                        // For simplicity, we can reload to show new booking
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }

                } catch (error) {
                    console.error('Error during appointment booking:', error);
                    bookingMessageDiv.className = 'alert alert-danger';
                    bookingMessageDiv.textContent = 'An unexpected error occurred. Please try again.';
                }
            });

            // Handle inquiry form submission via Fetch API
            document.getElementById('inquiryForm').addEventListener('submit', async function(event) {
                event.preventDefault(); // Prevent default form submission

                const form = event.target;
                const formData = new FormData(form);
                formData.append('client_id', <?php echo json_encode($clientId); ?>); // Add client ID to form data

                const inquiryMessageDiv = document.getElementById('inquiryMessage');
                inquiryMessageDiv.innerHTML = ''; // Clear previous messages

                try {
                    const response = await fetch('process_inquiry.php', { // You will need to create this file
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json(); // Parse the JSON response

                    let alertClass = result.success ? 'alert-success' : 'alert-danger';
                    inquiryMessageDiv.className = `alert ${alertClass}`;
                    inquiryMessageDiv.textContent = result.message;

                    if (result.success) {
                        form.reset(); // Clear form on successful submission
                    }

                } catch (error) {
                    console.error('Error during inquiry submission:', error);
                    inquiryMessageDiv.className = 'alert alert-danger';
                    inquiryMessageDiv.textContent = 'An unexpected error occurred. Please try again.';
                }
            });


            // Smooth scrolling for navigation links within the dashboard
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    // Only prevent default if it's an internal link
                    if (this.getAttribute('href').startsWith('#') && this.getAttribute('href') !== '#') {
                        e.preventDefault();
                        const targetId = this.getAttribute('href').substring(1);
                        const targetElement = document.getElementById(targetId);
                        if (targetElement) {
                            targetElement.scrollIntoView({
                                behavior: 'smooth'
                            });
                        }

                        // Close the navbar on mobile after clicking a link
                        const navbarToggler = document.querySelector('.navbar-toggler');
                        const navbarCollapse = document.querySelector('#navbarNav');
                        if (navbarCollapse.classList.contains('show')) {
                            navbarToggler.click();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>