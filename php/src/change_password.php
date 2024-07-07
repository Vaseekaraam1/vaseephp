<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Superadmin Password</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        /* Add your custom styles here for the change password page */
    </style>
</head>
<body>
    <div class="container">
        <h1>Change Superadmin Password</h1>
        <div class="form-container">
            <form action="change_password_action.php" method="post">
                <input type="password" name="current_password" placeholder="Current Password" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
