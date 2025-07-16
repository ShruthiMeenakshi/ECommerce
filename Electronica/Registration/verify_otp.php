<?php
error_log("VERIFY_OTP.PHP HIT at " . date('Y-m-d H:i:s'));
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

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Process OTP verification or resend request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST['email']);

    if (isset($_POST['resend'])) {
        // Resend OTP 
        $new_otp = rand(100000, 999999);
        error_log("DEBUG: New OTP generated for email " . $email . ": " . $new_otp);
       $new_otp_string = (string)$new_otp; 
        // Update OTP in database (now binding as string 's')
        $update = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
        $update->bind_param("ss", $new_otp_string, $email);

        if ($update->execute()) {
            // Send new OTP via email
            $subject = "Your New OTP for Account Verification";
            $message = "Here is your new OTP for account verification:\n\n";
            $message .= "OTP: " . $new_otp . "\n\n"; // Use the string value
            $message .= "Please enter this code on the verification page to complete your registration.\n\n";
            $message .= "If you didn't request this, please ignore this email.\n\n";
            $message .= "Best regards,\n";
            $message .= "Your Website Team";

            $headers = "From: no-reply@yourwebsite.com\r\n";
            $headers .= "Reply-To: no-reply@yourwebsite.com\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            if (mail($email, $subject, $message, $headers)) {
                echo "<script>
                        alert('New OTP has been sent to your email.');
                        window.location.href = 'otp.php?email=" . urlencode($email) . "';
                      </script>";
            } else {
                error_log("Failed to send email for new OTP to: " . $email);
                echo "<script>
                        alert('Failed to send new OTP. Please try again.');
                        window.location.href = 'otp.php?email=" . urlencode($email) . "';
                      </script>";
            }
        } else {
            error_log("Database error updating OTP for email: " . $email . " - " . $update->error);
            echo "<script>
                    alert('Error generating new OTP. Please try again.');
                    window.location.href = 'otp.php?email=" . urlencode($email) . "';
                  </script>";
        }

        $update->close();
    } else {
        // --- Verify OTP logic ---

        // 1. Combine OTP parts and ensure it's a string
        $entered_otp_string = '';
        for ($i = 1; $i <= 6; $i++) {
            $entered_otp_string .= $_POST['otp' . $i] ?? '';
        }

        // IMPORTANT: Validate if the combined string is actually a 6-digit number
        if (!ctype_digit($entered_otp_string) || strlen($entered_otp_string) !== 6) {
            error_log("DEBUG: User entered malformed OTP: " . $entered_otp_string . " for email: " . $email);
            echo "<script>
                    alert('Please enter a valid 6-digit OTP.');
                    window.location.href = 'otp.php?email=" . urlencode($email) . "';
                  </script>";
            exit();
        }

        // --- Debugging Section ---
        error_log("Attempting OTP verification for email: " . $email);
        error_log("OTP typed by user (as string): " . $entered_otp_string); // Log as string now

        // Select OTP from database
        $stmt = $conn->prepare("SELECT id, name, otp FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $database_otp = $user_data['otp']; // Retrieve as string, no (int) cast needed
            $user_name = $user_data['name'];

            // Debugging: Log the OTP fetched from the database
            error_log("OTP from database (as string): " . $database_otp);

            // --- CRITICAL COMPARISON: Use strict comparison (===) with strings ---
            if ($entered_otp_string === $database_otp) {
                error_log("DEBUG: OTPs MATCH for email: " . $email);
                // OTP matched successfully!

                // Invalidate the OTP after successful verification and set otp_verified = 1
                $update_status = $conn->prepare("UPDATE users SET otp_verified = 1, otp = NULL WHERE email = ?");
                $update_status->bind_param("s", $email);
                $update_status->execute();
                $update_status->close();

                // Send welcome email
                $subject = "Account Verified Successfully";
                $message = "Dear " . $user_name . ",\n\n";
                $message .= "Your account has been successfully verified!\n\n";
                $message .= "You can now login and start using our services.\n\n";
                $message .= "Best regards,\n";
                $message .= "Your Website Team";

                $headers = "From: welcome@yourwebsite.com\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();

                if (!mail($email, $subject, $message, $headers)) {
                    error_log("Failed to send welcome email to: " . $email . " after successful OTP verification.");
                }

                echo "<script>
                        alert('Account verified successfully! You can now login.');
                        window.location.href = 'Shopping/index.php';
                      </script>";
            } else {
                // OTP did not match
                error_log("DEBUG: OTP MISMATCH for email: " . $email . ". User entered: '" . $entered_otp_string . "', DB stored: '" . $database_otp . "'");
                echo "<script>
                        alert('Invalid OTP. Please try again.');
                        window.location.href = 'otp.php?email=" . urlencode($email) . "';
                      </script>";
            }
        } else {
            // No user found with that email, or no OTP exists for them
            error_log("DEBUG: No user found or no OTP record for email: " . $email . " in DB.");
            echo "<script>
                    alert('Invalid email or no pending verification. Please try again or register.');
                    window.location.href = 'otp.php?email=" . urlencode($email) . "';
                  </script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>