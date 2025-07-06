<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_form'])) {
    // Database configuration
    $servername = "localhost";
    $username = "electronica_user"; // Changed from root to dedicated user
    $password = "secure_password"; // Use a strong password
    $dbname = "Electronica";

    // Create connection with error handling
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Validate and sanitize inputs
        $name = trim($conn->real_escape_string($_POST['name'] ?? ''));
        $contactNumber = trim($conn->real_escape_string($_POST['number'] ?? ''));
        $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
        $message = trim($conn->real_escape_string($_POST['message'] ?? ''));

        // Basic validation
        if (empty($name) || empty($contactNumber) || empty($email) || empty($message)) {
            throw new Exception("All fields are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO contactme (name, number, email, message) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssss", $name, $contactNumber, $email, $message);

        // Execute the statement
        if ($stmt->execute()) {
            // Success - send email notification (optional)
            $to = "admin@electromart.com";
            $subject = "New Contact Form Submission";
            $email_message = "Name: $name\n";
            $email_message .= "Phone: $contactNumber\n";
            $email_message .= "Email: $email\n\n";
            $email_message .= "Message:\n$message";
            $headers = "From: $email";
            
            // Uncomment to actually send email
            // mail($to, $subject, $email_message, $headers);

            // Display success message
            displaySuccessMessage($name);
        } else {
            throw new Exception("Error: " . $stmt->error);
        }

        // Close statement and connection
        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        displayErrorMessage($e->getMessage());
    }
} else {
    header("Location: contact.html");
    exit();
}

/**
 * Display success message HTML
 */
function displaySuccessMessage($name) {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration Success</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h1 class="text-2xl font-bold text-gray-800 mt-4">Thank You, $name!</h1>
                <p class="text-gray-600 mt-2">Your message has been successfully submitted.</p>
                <p class="text-gray-600">Our team will get back to you within 24 hours.</p>
                <div class="mt-6">
                    <a href="contact.html" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Return to Contact Page
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    HTML;
}

/**
 * Display error message HTML
 */
function displayErrorMessage($error) {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration Error</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
                <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <h1 class="text-2xl font-bold text-gray-800 mt-4">Submission Failed</h1>
                <p class="text-gray-600 mt-2">We encountered an error processing your request.</p>
                <div class="mt-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                    Error: {$error}
                </div>
                <div class="mt-6 flex justify-center space-x-4">
                    <a href="contact.html" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Try Again
                    </a>
                    <a href="mailto:support@electromart.com" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Email Support
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    HTML;
}
?>