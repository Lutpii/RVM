<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify Your RVM Account</title>
</head>

<body style="font-family: Arial, sans-serif; color: #1a202c; max-width: 480px; margin: 0 auto; padding: 24px;">

    <h2 style="color: #22c55e; margin-bottom: 24px;">
        RVM | Reverse Vending Machine
    </h2>

    <p>Hi {{ $name }},</p>

    <p>
        Thanks for signing up for RVM. You're almost done creating your account.
    </p>

    <p>
        Please enter the verification code below to verify your email address
        and complete your registration:
    </p>

    <p style="
        font-size: 32px;
        font-weight: 700;
        letter-spacing: 8px;
        text-align: center;
        background: #f7fafc;
        border-radius: 8px;
        padding: 16px;
        margin: 24px 0;
    ">
        {{ $otp }}
    </p>

    <p style="color: #718096; font-size: 13px;">
        This code is valid for 5 minutes.
    </p>

    <p style="color: #718096; font-size: 13px;">
        For your security, please don't share this code with anyone.
        If you didn't create an RVM account, you can safely ignore this email.
    </p>

    <p style="margin-top: 24px;">
        Thank you for joining RVM and taking a simple step towards better recycling,
        reducing our carbon footprint, and building a more sustainable community.
    </p>

    <p style="margin-top: 24px;">
        RVM Team<br>
        <strong>Universiti Malaysia Pahang Al-Sultan Abdullah (UMPSA)</strong>
    </p>

</body>
</html>