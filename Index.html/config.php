<?php
// config.php

// Database credentials
// IMPORTANT: Ensure these match your MySQL database setup exactly!
define('DB_SERVER', 'localhost');      // Your database host (e.g., 'localhost', '127.0.0.1')
define('DB_USERNAME', 'root');         // Your database username (often 'root' for local setups)
define('DB_PASSWORD', '');             // Your database password (often empty '' for local setups)
define('DB_NAME', 'greenlifewell_db'); // The name of  database

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn === false) {
    // If connection fails, stop script execution and display an error message
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}

// Ensure the 'users' table exists. If it doesn't, create it.
$sql_create_users_table = "
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'therapist', 'admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql_create_users_table)) {
    error_log("Error creating users table: " . mysqli_error($conn));
}

// Ensure the 'appointments' table exists and has 'therapist_id'.
// We'll alter the table if therapist_id doesn't exist, rather than dropping it.
$sql_check_therapist_id_appointments = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'appointments' AND COLUMN_NAME = 'therapist_id'";
$result_check_therapist_id_appointments = mysqli_query($conn, $sql_check_therapist_id_appointments);

if ($result_check_therapist_id_appointments && mysqli_num_rows($result_check_therapist_id_appointments) == 0) {
    // If therapist_id column does not exist, add it
    $sql_add_therapist_id_column_appointments = "ALTER TABLE appointments ADD COLUMN therapist_id INT(11) NULL AFTER client_id";
    if (!mysqli_query($conn, $sql_add_therapist_id_column_appointments)) {
        error_log("Error adding therapist_id column to appointments table: " . mysqli_error($conn));
    } else {
        // Add foreign key constraint after column is added
        // Check if the foreign key constraint already exists to prevent errors on repeated execution
        $sql_check_fk_therapist = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'appointments' AND CONSTRAINT_NAME = 'fk_therapist_id'";
        $result_check_fk_therapist = mysqli_query($conn, $sql_check_fk_therapist);

        if ($result_check_fk_therapist && mysqli_num_rows($result_check_fk_therapist) == 0) {
            $sql_add_fk_therapist_appointments = "ALTER TABLE appointments ADD CONSTRAINT fk_therapist_id FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE";
            if (!mysqli_query($conn, $sql_add_fk_therapist_appointments)) {
                error_log("Error adding foreign key constraint for therapist_id in appointments: " . mysqli_error($conn));
            }
        }
    }
}


// Original appointments table creation (will run if table doesn't exist)
// This is kept to ensure the base table is created with client_id if not existing.
// The ALTER TABLE logic above is more robust for adding the therapist_id column.
$sql_create_appointments_table = "
CREATE TABLE IF NOT EXISTS appointments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL, -- Foreign key to users table (id)
    therapist_id INT(11) NULL, -- Foreign key to users table (id) for therapist
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    service_type VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    notes TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
    -- The therapist_id foreign key is handled by the ALTER TABLE statement above
)";

if (!mysqli_query($conn, $sql_create_appointments_table)) {
    error_log("Error creating or updating appointments table structure (if not existing): " . mysqli_error($conn));
}


// NEW: Ensure the 'client_notes' table exists
$sql_create_client_notes_table = "
CREATE TABLE IF NOT EXISTS client_notes (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL,
    therapist_id INT(11) NOT NULL,
    note_content TEXT NOT NULL,
    note_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
)";

if (!mysqli_query($conn, $sql_create_client_notes_table)) {
    error_log("Error creating client_notes table: " . mysqli_error($conn));
}

// NEW: Ensure the 'therapist_schedules' table exists
$sql_create_therapist_schedules_table = "
CREATE TABLE IF NOT EXISTS therapist_schedules (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    therapist_id INT(11) NOT NULL,
    schedule_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (therapist_id, schedule_date, start_time, end_time) -- Prevent duplicate entries for same slot
)";

if (!mysqli_query($conn, $sql_create_therapist_schedules_table)) {
    error_log("Error creating therapist_schedules table: " . mysqli_error($conn));
}


?>
