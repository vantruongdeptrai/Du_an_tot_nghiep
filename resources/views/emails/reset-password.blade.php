<!DOCTYPE html>
<html>
<head>
    <title>Your New Password</title>
</head>
<body>
    <p>Hello,</p>
    <p>You have requested to reset your password. Your new password is:</p>
    <h3>{{ $password }}</h3>
    <p>Please use this password to log in and remember to change it afterward for security reasons.</p>
    <p>Thank you,</p>
    <p>{{ config('app.name') }}</p>
</body>
</html>
