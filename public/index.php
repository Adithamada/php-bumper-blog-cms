<?php
require_once __DIR__ . "/../include/function.php";

//SELECT LATEST POST
$stmtLatestPost = $conn->prepare("SELECT * FROM post ORDER BY date DESC LIMIT 1");
$stmtLatestPost->execute();
$resultLatestPost = $stmtLatestPost->get_result();
$latestPost = $resultLatestPost->fetch_assoc();

//SELECT POST
$stmtSelectPost = $conn->prepare("SELECT * FROM post");
$stmtSelectPost->execute();
$resultSelectPost = $stmtSelectPost->get_result();
$rowSelectPost = $resultSelectPost->fetch_all(MYSQLI_ASSOC);
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

<body>
    <!-- NAVBAR -->
    <header class="mb-5 sticky-top shadow-sm">
        <nav class="navbar navbar-expand-md bg-secondary-subtle">
            <div class="container-fluid px-4">
                <a href="" class="navbar-brand">
                    <h2><strong>BUMPER</strong><span class="text-secondary">blog</span></h2>
                </a>
                <div class="d-flex gap-4">
                    <ul class="navbar-nav d-flex d-md-none gap-3">
                        <li class="nav-item"><button class="toggle-search btn"><i class="bi bi-search fs-5"></i></button></li>
                    </ul>
                    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                <ul class="navbar-nav d-none d-md-flex gap-3">
                    <li class="nav-item"><a href="" class="nav-link active">Home</a></li>
                    <li class="nav-item"><a href="" class="nav-link">Blog</a></li>
                    <li class="nav-item"><button class="toggle-search btn"><i class="bi bi-search"></i></button></li>
                </ul>
            </div>
        </nav>
        <div class="container d-flex justify-content-center">
            <div class="col-8 col-md-5 position-relative">
                <div class="card form-search position-absolute w-100 mt-4 shadow-sm">
                    <form action="" class="d-flex">
                        <input type="text" placeholder="Search..." class="form-control">
                        <button class="btn"><i class="bi bi-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">BUMPER</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="navbar-nav">
                <li class="nav-item"><a href="" class="nav-link active">Home</a></li>
                <li class="nav-item"><a href="" class="nav-link">Blog</a></li>
            </ul>
        </div>
    </div>
    <!-- NAVBAR -->

    <!-- HERO -->
    <div class="container position-relative text-center mb-5">
        <div class="col-12 position-relative">
            <img src="img_upload/<?= $latestPost['img'] ?>" class="img-fluid rounded shadow">
            <div class="hero-content" style="text-align: start !important;">
                <div class="d-none d-md-none d-lg-block text-light">
                    <a href="" class="text-decoration-none">
                        <?php $category = getPostCategory($latestPost['id']) ?>
                        <span class="badge text-bg-light"><?= $category ?></span>
                    </a>
                    <a href="" class="text-decoration-none text-light fw-bold">
                        <h1><?= $latestPost['title'] ?></h1>
                    </a>
                    <p class="fs-5">Lorem ipsum dolor sit amet consectetur adipisicing elit...</p>
                    <div class="d-flex gap-3">
                        <a href="" class="text-decoration-none">
                            <div class="d-flex gap-2 align-items-center text-light">
                                <i class="bi bi-person-fill"></i>
                                <p class="my-0 py-0">Adithama</p>
                            </div>
                        </a>
                        <p class="my-0 py-0">2 - 8 - 2025</p>
                    </div>
                </div>
                <div class="d-none d-md-block d-lg-none text-light">
                    <a href="" class="text-decoration-none text-light">
                        <h4>Lorem ipsum dolor sit amet.</h4>
                    </a>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit...</p>
                    <div class="d-flex gap-3">
                        <a href="" class="text-decoration-none">
                            <div class="d-flex gap-2 align-items-center text-light">
                                <i class="bi bi-person-fill"></i>
                                <?php $user = getPostUser($latestPost['id']) ?>

                                <p class="my-0 py-0"><?= $user ?></p>
                            </div>
                        </a>
                        <p class="my-0 py-0">2 - 8 - 2025</p>
                    </div>
                </div>
                <div class="d-block d-md-none text-light">
                    <a href="" class="text-decoration-none text-light">
                        <h5>Lorem ipsum dolor sit amet.</h5>
                    </a>
                    <div class="d-flex gap-2">
                        <a href="" class="text-decoration-none text-light">
                            <div class="d-flex gap-1 align-items-center">
                                <i class="bi bi-person-fill"></i>
                                <p class="my-0 py-0">Adithama</p>
                            </div>
                        </a>
                        <p class="my-0 py-0">2 - 8 - 2025</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- HERO -->

    <!-- BLOG -->
    <div class="container position-relative mb-5">
        <div class="row mb-2">
            <h3>Latest Blog</h3>
        </div>
        <div class="row">
            <?php foreach ($rowSelectPost as $post): ?>
                <div class="col-12 col-md-6 col-lg-4 mb-3 mb-md-0">
                    <div class="card shadow">
                        <div class="card-header p-0"><img src="img_upload/<?= $post['img'] ?>" class="img-fluid rounded-top"></div>
                        <div class="card-body">
                            <a href="" class="text-decoration-none">
                                <?php $category = getPostCategory($post['id']) ?>
                                <span class="badge text-bg-dark"><?= $category  ?></span>
                            </a>
                            <div class="blog-content mb-5">
                                <a href="" class="text-decoration-none text-dark">
                                    <h3><?= $post['title'] ?></h3>
                                </a>
                                <p class=""><?= getShortDescription($post['description'])  ?></p>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="" class="text-decoration-none text-dark">
                                    <div class="d-flex gap-2 align-items-center">
                                        <i class="bi bi-person-fill"></i>
                                        <?php $user = getPostUser($post['id']) ?>
                                        <p class="my-0 py-0"><?= $user  ?></p>
                                    </div>
                                </a>
                                <p class="my-0 py-0 text-secondary"><?= $post['date'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- BLOG -->

    <!-- FOOTER -->
    <div class="container-fluid d-flex justify-content-center bg-secondary-subtle py-5 shadow-sm">
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <p>©Bumper 2025, All Rights Reserved.</p>
            </div>
        </div>
    </div>
    <!-- FOOTER -->
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/searchBarToggle.js"></script>
</body>

</html>