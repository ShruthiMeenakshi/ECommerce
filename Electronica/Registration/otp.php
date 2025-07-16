<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl overflow-hidden w-full max-w-md">
        <div class="bg-gradient-to-r from-blue-600 to-purple-700 p-6 text-white">
            <h1 class="text-2xl font-bold text-center">Verify Your Account</h1>
            <p class="text-center text-blue-100 mt-1">Enter the OTP sent to your email</p>
        </div>
        
        <form id="otpForm" action="verify_otp.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" id="email" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
            
            <div class="flex justify-center space-x-2">
                <input type="text" name="otp1" maxlength="1" class="w-12 h-12 text-center text-2xl border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" pattern="[0-9]" required>
                <input type="text" name="otp2" maxlength="1" class="w-12 h-12 text-center text-2xl border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" pattern="[0-9]" required>
                <input type="text" name="otp3" maxlength="1" class="w-12 h-12 text-center text-2xl border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" pattern="[0-9]" required>
                <input type="text" name="otp4" maxlength="1" class="w-12 h-12 text-center text-2xl border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" pattern="[0-9]" required>
                <input type="text" name="otp5" maxlength="1" class="w-12 h-12 text-center text-2xl border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" pattern="[0-9]" required>
                <input type="text" name="otp6" maxlength="1" class="w-12 h-12 text-center text-2xl border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" pattern="[0-9]" required>
            </div>
            
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Didn't receive code? 
                    <button type="submit" name="resend" value="1" class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none">
                        Resend OTP
                    </button>
                </div>
                <div id="countdown" class="text-sm text-gray-600">(60s)</div>
            </div>
            
            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                    Verify OTP
                </button>
            </div>
        </form>
    </div>

    <script>
        // Auto-focus and auto-tab between OTP inputs
        const otpInputs = document.querySelectorAll('input[name^="otp"]');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1) {
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                }
            });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 0) {
                    if (index > 0) {
                        otpInputs[index - 1].focus();
                    }
                }
            });
        });
        
        // Countdown timer for resend OTP
        let timeLeft = 60;
        const countdownElement = document.getElementById('countdown');
        const resendButton = document.querySelector('button[name="resend"]');
        
        resendButton.style.display = 'none';
        
        const timer = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = `(${timeLeft}s)`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                countdownElement.style.display = 'none';
                resendButton.style.display = 'inline';
            }
        }, 1000);
    </script>
</body>
</html>