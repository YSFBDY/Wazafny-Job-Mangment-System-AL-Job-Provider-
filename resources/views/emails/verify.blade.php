<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            margin: auto;
        }
        .navbar {
            background: #6A0DAD;
            color: white;
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            max-width: 600px;
            width: 100%;
            margin: auto;
        }
        h2 {
            color: #333;
            font-size: 22px;
        }
        p {
            color: #666;
            font-size: 18px;
        }
        .verify-button {
            display: inline-block;
            padding: 12px 24px;
            margin-top: 20px;
            font-size: 18px;
            color: white;
            background-color: #6A0DAD;
            border-radius: 6px;
            text-decoration: none;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #888;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="navbar">WAZAFNY</div>
    <div class="container">
        <h2>Email Verification Required</h2>
        <p>Thanks for signing up! Please click the button below to verify your email address.</p>
        <a href="{{ $verificationUrl }}" class="verify-button">Verify Email</a>
        <p>If you did not create an account, no further action is required.</p>

        <p class="footer">
            This is an automated message, please do not reply.
        </p>
        <p class="footer">
            Thanks,<br>
            <strong>WAZAFNY Team</strong>
        </p>
    </div>
</body>
</html>
