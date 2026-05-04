<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greenlife Wellness Center - Colombo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Custom CSS for Greenlife Wellness Center */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
        }

        .navbar {
            background-color: #28a745; /* Greenlife primary color */
            padding-top: 1rem;
            padding-bottom: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: #fff !important;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 10px;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s ease;
            position: relative;
            padding-bottom: 5px; /* Space for underline effect */
        }

        .nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 2px;
            background-color: #fff;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./images/background.jpg') no-repeat center center/cover;
            color: #fff;
            padding: 100px 0;
            text-align: center;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-section p {
            font-size: 1.3rem;
            max-width: 800px;
            margin: 0 auto 30px auto;
        }

        .btn-greenlife-primary {
            background-color: #28a745;
            color: #fff;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid #28a745;
            text-decoration: none; /* Ensure no underline on buttons */
            display: inline-block; /* Allow margin between buttons */
        }

        .btn-greenlife-primary:hover {
            background-color: #218838;
            border-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-greenlife-secondary {
            background-color: transparent;
            color: #fff;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid #fff; /* White border for secondary */
            text-decoration: none; /* Ensure no underline on buttons */
            display: inline-block; /* Allow margin between buttons */
            margin-left: 15px; /* Space between buttons */
        }

        .btn-greenlife-secondary:hover {
            background-color: rgba(255, 255, 255, 0.2); /* Slightly transparent white on hover */
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }


        .section-padding {
            padding: 80px 0;
        }

        .section-heading {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 40px;
            color: #28a745;
            text-align: center;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            overflow: hidden; /* Ensure rounded corners for image */
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card-img-top {
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .service-icon {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 20px;
        }

        .footer {
            background-color: #343a40;
            color: #fff;
            padding: 50px 0;
            border-top-left-radius: 50px;
            border-top-right-radius: 50px;
        }

        .footer .social-icons a {
            color: #fff;
            font-size: 1.5rem;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .footer .social-icons a:hover {
            color: #28a745;
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

        .btn-form-submit {
            background-color: #28a745;
            color: #fff;
            border-radius: 10px;
            padding: 10px 25px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }

        .btn-form-submit:hover {
            background-color: #218838;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            .hero-section h1 {
                font-size: 2.5rem;
            }
            .hero-section p {
                font-size: 1rem;
            }
            .section-heading {
                font-size: 2rem;
            }
            .navbar-brand {
                font-size: 1.5rem;
            }
            .btn-group-hero {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            .btn-greenlife-primary, .btn-greenlife-secondary {
                margin: 0 !important; /* Remove horizontal margin on small screens */
                width: 100%; /* Make buttons full width */
            }
        }

        .auth-form-section {
            padding: 100px 0; /* More padding for login/register forms */
            min-height: 100vh; /* Ensure forms take full height */
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
        }

        .auth-form-card h2 {
            margin-bottom: 30px;
            color: #28a745;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="fas fa-leaf"></i> Greenlife Wellness
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#register">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-section d-flex align-items-center">
        <div class="container">
            <h1>Embrace a Healthier You at Greenlife Wellness</h1>
            <p>Your sanctuary for holistic well-being in the heart of Colombo, Sri Lanka. Discover natural paths to vitality.</p>
            <div class="btn-group-hero">
                <a href="#services" class="btn btn-greenlife-primary">Explore Our Services</a>
                <a href="#booking" class="btn btn-greenlife-secondary">Book Appointment</a>
            </div>
        </div>
    </section>

    <section id="about" class="section-padding">
        <div class="container">
            <h2 class="section-heading">About Greenlife Wellness</h2>
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="./images/about us.jpg" class="img-fluid rounded-4 shadow-sm" alt="About Greenlife Wellness">
                </div>
                <div class="col-md-6">
                    <p class="lead">
                        Greenlife Wellness Center is dedicated to fostering health and harmony through a blend of traditional and modern wellness practices. Located in the serene surroundings of Colombo, we offer a tranquil escape where you can rejuvenate your mind, body, and spirit.
                    </p>
                    <p>
                        Our expert team of practitioners is committed to providing personalized care, guiding you on a journey towards optimal health. We believe in empowering our clients with knowledge and tools to maintain a balanced and vibrant lifestyle.
                    </p>
                    <p>
                        From therapeutic massages to nutritional guidance and stress management programs, we tailor our approach to meet your unique needs, ensuring a truly transformative experience.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="section-padding bg-light">
        <div class="container">
            <h2 class="section-heading">Our Services</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <div class="col">
                    <div class="card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-spa service-icon"></i>
                            <h5 class="card-title fw-bold">Therapeutic Massages</h5>
                            <p class="card-text">Relax and relieve tension with our range of therapeutic massages, including Ayurvedic, deep tissue, and hot stone therapies.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-leaf service-icon"></i>
                            <h5 class="card-title fw-bold">Holistic Diet Nutrition</h5>
                            <p class="card-text">Receive personalized dietary plans and guidance to nourish your body and support your overall well-being.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-meditation service-icon"></i>
                            <h5 class="card-title fw-bold">Mindfulness & Yoga</h5>
                            <p class="card-text">Join our classes and workshops to practice mindfulness, meditation, and yoga for mental clarity and physical flexibility.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-heartbeat service-icon"></i>
                            <h5 class="card-title fw-bold">Stress Management</h5>
                            <p class="card-text">Learn effective techniques and strategies to manage stress and promote emotional balance in your daily life.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-seedling service-icon"></i>
                            <h5 class="card-title fw-bold">Ayurvedic Herbal Remedies</h5>
                            <p class="card-text">Explore the benefits of traditional herbal remedies and natural supplements under expert guidance for various ailments.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-water service-icon"></i>
                            <h5 class="card-title fw-bold">Hydrotherapy & Physiotherapy</h5>
                            <p class="card-text">Experience the healing power of water through various hydrotherapy treatments designed to soothe and invigorate.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="login" class="auth-form-section bg-light">
        <div class="container">
            <div class="card auth-form-card mx-auto text-center">
                <h2 class="mb-4">Login to Your Account</h2>
                <form action="process_login.php" method="POST">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label visually-hidden">Email address</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Email address" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label visually-hidden">Password</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-greenlife-primary btn-form-submit">Login</button>
                    </div>
                    <p class="text-center">
                        <a href="#" class="text-success text-decoration-none">Forgot Password?</a>
                    </p>
                    <p class="text-center">
                        Don't have an account? <a href="#register" class="text-success text-decoration-none">Register here</a>
                    </p>
                </form>
            </div>
        </div>
    </section>

    <section id="register" class="auth-form-section">
    <div class="container">
        <div class="card auth-form-card mx-auto text-center">
            <h2 class="mb-4">Create Your Greenlife Account</h2>
            <form action="process_register.php" method="POST">
                <div class="mb-3">
                    <label for="registerUserId" class="form-label visually-hidden">User id</label>
                    <input type="text" class="form-control" id="registerUserId" name="Userid" placeholder="Userid" required>
                </div>
                <div class="mb-3">
                    <label for="registerUserName" class="form-label visually-hidden">User Name</label>
                    <input type="text" class="form-control" id="registerUserName" name="UserName" placeholder="User Name" required>
                </div>
                <div class="mb-3">
                    <label for="registerFName" class="form-label visually-hidden">FirstName</label>
                    <input type="text" class="form-control" id="registerFName" name="FirstName" placeholder="FirstName" required>
                </div>
                <div class="mb-3">
                    <label for="registerLName" class="form-label visually-hidden">LastName</label>
                    <input type="text" class="form-control" id="registerLName" name="LastName" placeholder="LastName" required>
                </div>
                <div class="mb-3">
                    <label for="registerEmail" class="form-label visually-hidden">Email address</label>
                    <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Email address" required>
                </div>
                <div class="mb-3">
                    <label for="registerPassword" class="form-label visually-hidden">Password</label>
                    <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Password" required>
                </div>
                <div class="mb-3">
                    <label for="registerRole" class="form-label visually-hidden">Select Role</label>
                    <select class="form-select" id="registerRole" name="role" required>
                        <option selected disabled value="">Select your role...</option>
                        <option value="user">User</option>
                        <option value="therapist">Therapist</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-greenlife-primary btn-form-submit">Register</button>
                </div>
                <p class="text-center">
                    Already have an account? <a href="#login" class="text-success text-decoration-none">Login here</a>
                </p>
            </form>
        </div>
    </div>
</section>

    <section id="booking" class="section-padding bg-light">
        <div class="container">
            <h2 class="section-heading">Book Your Appointment</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card p-5 shadow-sm rounded-4">
                        <p class="text-center lead mb-4">
                            Ready to take the first step towards a healthier you? Book your personalized wellness appointment now.
                        </p>
                        <form action="process_booking.php" method="POST">
                            <div class="mb-3">
                                <label for="bookingName" class="form-label">Your Full Name</label>
                                <input type="text" class="form-control" id="bookingName" name="fullName" placeholder="John Doe" required>
                            </div>
                            <div class="mb-3">
                                <label for="bookingEmail" class="form-label">Your Email</label>
                                <input type="email" class="form-control" id="bookingEmail" name="email" placeholder="name@example.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="bookingPhone" class="form-label">Phone Number (Optional)</label>
                                <input type="tel" class="form-control" id="bookingPhone" name="phone" placeholder="+94 77 123 4567">
                            </div>
                            <div class="mb-3">
                                <label for="serviceType" class="form-label">Preferred Service</label>
                                <select class="form-select" id="serviceType" name="service" required>
                                    <option selected disabled value="">Select a service...</option>
                                    <option value="therapeutic-massage">Therapeutic Massage</option>
                                    <option value="holistic-nutrition">Holistic Nutrition</option>
                                    <option value="mindfulness-yoga">Mindfulness & Yoga</option>
                                    <option value="stress-management">Stress Management</option>
                                    <option value="herbal-remedies">Herbal Remedies</option>
                                    <option value="hydrotherapy">Hydrotherapy</option>
                                    <option value="other">Other</option>
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
                                <button type="submit" class="btn btn-greenlife-primary btn-form-submit">submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="section-padding">
        <div class="container">
            <h2 class="section-heading">Contact Us</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card p-5 shadow-sm rounded-4">
                        <p class="text-center lead mb-4">
                            Have questions or ready to book an appointment? We'd love to hear from you!
                        </p>
                        <form action="process_contact.php" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Your Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Inquiry about services">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" placeholder="Tell us how we can help you..." required></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-greenlife-primary btn-form-submit">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row mt-5 text-center">
                <div class="col-md-4">
                    <p class="fw-bold"><i class="fas fa-map-marker-alt me-2"></i> Address:</p>
                    <p>123 Wellness Avenue, Colombo 07, Sri Lanka</p>
                </div>
                <div class="col-md-4">
                    <p class="fw-bold"><i class="fas fa-phone-alt me-2"></i> Phone:</p>
                    <p>+94 11 234 5678</p>
                </div>
                <div class="col-md-4">
                    <p class="fw-bold"><i class="fas fa-envelope me-2"></i> Email:</p>
                    <p>info@greenlifecolombo.lk</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container text-center">
            <div class="social-icons mb-3">
                <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                <a href="#" class="me-3"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <p>&copy; 2025 Greenlife Wellness Center. All Rights Reserved.</p>
            <p><a href="#" class="text-white text-decoration-none">Privacy Policy</a> | <a href="#" class="text-white text-decoration-none">Terms of Service</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlco4M7rYvRmwj2hn7KDfDRuFNHT/o7/9p7/QO77J9byxM1" crossorigin="anonymous"></script>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });

                // Close the navbar on mobile after clicking a link
                const navbarToggler = document.querySelector('.navbar-toggler');
                const navbarCollapse = document.querySelector('#navbarNav');
                if (navbarCollapse.classList.contains('show')) {
                    navbarToggler.click();
                }
            });
        });

        // Add active class to nav-link on scroll
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.nav-link');
            const navbar = document.querySelector('.navbar');

            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                // Offset by navbar height to ensure active state changes slightly before section hits top
                if (pageYOffset >= sectionTop - navbar.clientHeight - 50) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').includes(current)) {
                    link.classList.add('active');
                }
            });
        });

        // Set active class on initial load for the home section
        document.addEventListener('DOMContentLoaded', () => {
            const homeLink = document.querySelector('.nav-link[href="#home"]');
            if (homeLink) {
                homeLink.classList.add('active');
            }
        });
    </script>
</body>
</html>