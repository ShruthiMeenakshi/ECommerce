<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registration_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email already registered!'); window.location.href = 'register.html';</script>";
        exit();
    }
    $stmt->close();
    
    // Generate OTP (6-digit random number)
    $otp = rand(100000, 999999);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, otp, otp_verified, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
    $stmt->bind_param("ssssi", $name, $email, $phone, $password, $otp);
    
    if ($stmt->execute()) {
        // Send OTP to user's email
        $to = $email;
        $subject = "Your OTP for Registration";
        $message = "Dear " . $name . ",\n\nYour One-Time Password (OTP) for registration is: " . $otp . "\n\nPlease use this OTP to verify your account.\n\nRegards,\nYour Website Team";
        $headers = "From: webmaster@yourdomain.com\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";

        if (mail($to, $subject, $message, $headers)) {
            echo "<script>
                    alert('Registration successful! An OTP has been sent to your email address.');
                    window.location.href = 'otp.php?email=$email';
                  </script>";
        } else {
            echo "<script>alert('Registration successful, but failed to send OTP email. Please contact support. Error: " . error_get_last()['message'] . "'); window.location.href = 'otp.php?email=$email';</script>";
        }
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.location.href = 'register.html';</script>";
    }
    
    $stmt->close();
    $conn->close();
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
