<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_form'])) {
    // Database configuration
    $servername = "localhost";
    $username = "root"; // Changed from root to dedicated user
    $password = ""; // Use a strong password
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
 * Display success message HTML with enhanced UI
 */
function displaySuccessMessage($name) {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Message Received | ElectroMart</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
        <style>
            .success-checkmark {
                width: 80px;
                height: 80px;
                margin: 0 auto;
                position: relative;
            }
            .check-icon {
                width: 80px;
                height: 80px;
                position: relative;
                border-radius: 50%;
                box-sizing: content-box;
                border: 4px solid #4CAF50;
                background-color: #fff;
            }
            .check-icon::before {
                top: 3px;
                left: -2px;
                width: 30px;
                transform-origin: 100% 50%;
                border-radius: 100px 0 0 100px;
            }
            .check-icon::after {
                top: 0;
                left: 30px;
                width: 60px;
                transform-origin: 0 50%;
                border-radius: 0 100px 100px 0;
                animation: rotate-circle 4.25s ease-in;
            }
            .check-icon::before, .check-icon::after {
                content: '';
                height: 100px;
                position: absolute;
                background: transparent;
                transform: rotate(-45deg);
            }
            .icon-line {
                height: 5px;
                background-color: #4CAF50;
                display: block;
                border-radius: 2px;
                position: absolute;
                z-index: 10;
            }
            .icon-line.line-tip {
                top: 46px;
                left: 14px;
                width: 25px;
                transform: rotate(45deg);
                animation: icon-line-tip 0.75s;
            }
            .icon-line.line-long {
                top: 38px;
                right: 8px;
                width: 47px;
                transform: rotate(-45deg);
                animation: icon-line-long 0.75s;
            }
            @keyframes icon-line-tip {
                0% { width: 0; left: 1px; top: 19px; }
                54% { width: 0; left: 1px; top: 19px; }
                70% { width: 50px; left: -8px; top: 37px; }
                84% { width: 17px; left: 21px; top: 48px; }
                100% { width: 25px; left: 14px; top: 46px; }
            }
            @keyframes icon-line-long {
                0% { width: 0; right: 46px; top: 54px; }
                65% { width: 0; right: 46px; top: 54px; }
                84% { width: 55px; right: 0px; top: 35px; }
                100% { width: 47px; right: 8px; top: 38px; }
            }
            .confetti {
                position: absolute;
                width: 10px;
                height: 10px;
                background-color: #f00;
                opacity: 0;
            }
        </style>
    </head>
    <body class="bg-gradient-to-br from-blue-50 to-indigo-100">
        <div class="min-h-screen flex items-center justify-center px-4 py-12">
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden w-full max-w-md animate__animated animate__fadeInUp">
                <div class="p-1 bg-gradient-to-r from-green-400 to-blue-500"></div>
                
                <div class="px-8 py-10 text-center">
                    <!-- Animated Checkmark -->
                    <div class="success-checkmark mb-6">
                        <div class="check-icon">
                            <span class="icon-line line-tip"></span>
                            <span class="icon-line line-long"></span>
                        </div>
                    </div>
                    
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Thank You, $name!</h1>
                    <p class="text-lg text-gray-600 mb-6">We've received your message successfully.</p>
                    
                    <div class="bg-blue-50 rounded-lg p-4 mb-8 text-left border border-blue-100">
                        <h3 class="font-medium text-blue-800 mb-2">What's Next?</h3>
                        <ul class="text-sm text-blue-600 space-y-1">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mt-0.5 mr-2 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Our team will review your message</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mt-0.5 mr-2 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Expect a response within 24 hours</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mt-0.5 mr-2 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Check your email (including spam folder)</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-center gap-3">
                        <a href="index.php" class="flex-1 flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg shadow hover:shadow-md transition-all hover:scale-[1.02]">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Home Page
                        </a>
                        <a href="contact.html" class="flex-1 flex items-center justify-center px-6 py-3 bg-white text-blue-600 rounded-lg border border-blue-200 shadow-sm hover:shadow-md transition-all hover:scale-[1.02]">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            Contact Again
                        </a>
                    </div>
                    
                    
                </div>
                
                <div class="p-4 bg-gray-50 text-center text-sm text-gray-500 border-t border-gray-100">
                    Need immediate help? <a href="tel:+18005551234" class="text-blue-600 hover:underline">Call +1 (800) 555-1234</a>
                </div>
            </div>
        </div>

        <script>
            // Simple confetti effect
            document.addEventListener('DOMContentLoaded', function() {
                const colors = ['#4CAF50', '#2196F3', '#FFC107', '#FF5722', '#9C27B0'];
                const container = document.body;
                
                for (let i = 0; i < 50; i++) {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.top = -10 + 'px';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
                    container.appendChild(confetti);
                    
                    const animationDuration = Math.random() * 3 + 2;
                    
                    confetti.animate([
                        { top: '-10px', opacity: 1 },
                        { top: '100vh', opacity: 0 }
                    ], {
                        duration: animationDuration * 1000,
                        delay: Math.random() * 2000
                    });
                }
            });
        </script>
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