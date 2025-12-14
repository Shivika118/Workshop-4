<?php

$fullName = "";
$userEmail = "";
$formErrors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullName = trim($_POST["name"] ?? "");
    $userEmail = trim($_POST["email"] ?? "");
    $pass = $_POST["password"] ?? "";
    $repass = $_POST["confirm_password"] ?? "";

    if ($fullName === "") {
        $formErrors["name"] = "Name is required.";
    }
    if ($userEmail === "") {
        $formErrors["email"] = "Email is required.";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $formErrors["email"] = "Invalid email format.";
    }

    if ($pass === "") {
        $formErrors["password"] = "Password is required.";
    } elseif (strlen($pass) < 6) {
        $formErrors["password"] = "Password must be at least 6 characters.";
    } elseif (!preg_match('/[@#$%^&*]/', $pass)) {
        $formErrors["password"] = "Password must contain a special character.";
    }

  
    if ($pass !== $repass) {
        $formErrors["confirm_password"] = "Passwords do not match.";
    }

    if (empty($formErrors)) {

        $userFile = "users.json";

        if (!file_exists($userFile)) {
            $formErrors["file"] = "User storage file not found.";
        } else {

            $content = file_get_contents($userFile);

            if ($content === false) {
                $formErrors["file"] = "Error reading users.json file.";
            } else {

                $userList = json_decode($content, true);
                if (!is_array($userList)) {
                    $userList = [];
                }

                $userList[] = [
                    "name" => $fullName,
                    "email" => $userEmail,
                    "password" => password_hash($pass, PASSWORD_DEFAULT)
                ];

                if (file_put_contents($userFile, json_encode($userList, JSON_PRETTY_PRINT)) === false) {
                    $formErrors["file"] = "Error writing to users.json.";
                } else {
                    $success = "Registration successfull";
                    $fullName = "";
                    $userEmail = "";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        .error { color: red; font-size: 14px; }
        .success { color: green; font-size: 16px; margin-bottom: 10px; }
        form { width: 300px; margin: auto; }
        label { font-weight: bold; }
        input { width: 100%; padding: 8px; margin-bottom: 8px; }
    </style>
</head>
<body>

<h2 style="text-align:center;">User Registration</h2>

<?php if ($success): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" action="registration.php">

    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($fullName) ?>">
    <div class="error"><?= $formErrors["name"] ?? "" ?></div>

    <label>Email:</label>
    <input type="text" name="email" value="<?= htmlspecialchars($userEmail) ?>">
    <div class="error"><?= $formErrors["email"] ?? "" ?></div>

    <label>Password:</label>
    <input type="password" name="password">
    <div class="error"><?= $formErrors["password"] ?? "" ?></div>

    <label>Confirm Password:</label>
    <input type="password" name="confirm_password">
    <div class="error"><?= $formErrors["confirm_password"] ?? "" ?></div>

    <div class="error"><?= $formErrors["file"] ?? "" ?></div>

    <button type="submit">Register</button>

</form>

</body>
</html>
