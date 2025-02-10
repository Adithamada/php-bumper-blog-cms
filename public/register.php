<?php
session_start();
require "../include/function.php";
$error = [];
if (isset($_POST['register'])) {
    $data = [
        "username" => htmlspecialchars($_POST['username'] ?? ''),
        "email" => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
        "password" => !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null,
    ];
    if (empty($data['username'])) {
        $error[] = "Username is required!";
    }

    if (empty($data['email'])) {
        $error[] = "Email is required!";
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error[] = "Valid email is required!";
    }

    if (empty($data['password'])) {
        $error[] = "Password is required!";
    }

    if (empty($error)) {
        if (register($data)) {
            setFlashMessage('success', 'Register Success!');
            header("Location: login.php");
            exit;
        } else {
            $error[] = "Register Fail! Check your input!";
        }
    }
    if (!empty($error)) {
        setFlashMessage('error', $error);
    }
}
$error = getFlashMessage('error');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <title>BUMPER</title>
</head>

<body class="bg-secondary-subtle">
    <div class="container vh-100 d-flex justify-content-center position-relative align-items-center">
        <div class="col-12 col-md-5 col-lg-4">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>DANGER!</strong>
                    <ul class="navbar-nav">
                        <?php foreach ($error as $e): ?>
                            <li class="nav-item"><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="card shadow">
                <div class="card-header">
                    <h3>Register</h3>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 text-end">
                            <button class="btn btn-dark shadow" type="submit" name="register">
                                Register
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/passwordToggle.js"></script>
</body>

</html>