<?php
require_once 'config.php';
// forgot_password.php

// Include database connection (if needed for email validation)
// require_once 'config.php'; // or whatever your config file is

// Initialize variables
$email = '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));

    // 1. Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // 2. Check if email exists in your users table
        // This is important to prevent sending OTPs to non-existent accounts
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email exists, proceed to generate and send OTP
            $otp = rand(100000, 999999); // Generate 6-digit OTP

            // Store OTP in database (update the existing user's OTP column)
            $update_stmt = $conn->prepare("UPDATE users SET otp = ?, otp_created_at = NOW() WHERE email = ?"); // Assuming you have an otp_created_at column for expiration
            $update_stmt->bind_param("ss", $otp, $email); // 'ss' for string OTP and string email

            if ($update_stmt->execute()) {
                // 3. Send OTP to user's email (using your mail function or PHPMailer)
                $subject = "Password Reset OTP";
                $body = "Your OTP for password reset is: " . $otp . "\n\nThis OTP is valid for X minutes."; // Add expiration time

                // Use your configured mail() function or PHPMailer here
                if (mail($email, $subject, $body, "From: no-reply@yourwebsite.com")) {
                    $message = "An OTP has been sent to your email address.";
                    // Redirect to OTP verification page, passing the email
                    header("Location: verify_otp.php?email=" . urlencode($email));
                    exit();
                } else {
                    $error = "Failed to send OTP. Please try again later.";
                    error_log("Failed to send password reset OTP to: " . $email);
                }
            } else {
                $error = "Error storing OTP. Please try again.";
                error_log("Error updating OTP in DB for " . $email . ": " . $update_stmt->error);
            }
            $update_stmt->close();
        } else {
            // Email does not exist
            $error = "No account found with that email address.";
            // For security, some systems might give a generic "If an account exists..." message
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl overflow-hidden w-full max-w-md">
        <div class="bg-gradient-to-r from-blue-600 to-purple-700 p-6 text-white">
            <h1 class="text-2xl font-bold text-center">Forgot Password</h1>
            <p class="text-center text-blue-100 mt-1">Enter your email to receive an OTP for password reset.</p>
        </div>

        <form action="forgot-password.php" method="POST" class="p-6 space-y-4">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <div>
                <label for="email" class="sr-only">Email address</label>
                <input id="email" name="email" type="email" autocomplete="email" required
                       class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                       placeholder="Email address" value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                    Send OTP
                </button>
            </div>
        </form>
    </div>
</body>
</html>