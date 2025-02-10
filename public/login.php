<?php
session_start();
require "../include/function.php";
$error = [];
$success = "";

if (isset($_SESSION['success'])) {
    $success = getFlashMessage($_SESSION['success']);
}

if (isset($_POST['login'])) {
    $data = [
        "username" => htmlspecialchars($_POST['username'] ?? ''),
        "password" => $_POST['password'] ?? '',
    ];
    if (empty($data['username'])) {
        $error[] = "Username is required!";
    }

    if (empty($data['password'])) {
        $error[] = "Password is required!";
    }

    if (empty($error)) {
        if (login($data)) {
            setFlashMessage('success', 'Login Success!');
            exit;
        } else {
            $error[] = "Login Fail! Check your input!";
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
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success</strong> <?= $success  ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
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
                    <h3>Login</h3>
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 text-end">
                            <button class="btn btn-dark shadow" type="submit" name="login">
                                Login
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