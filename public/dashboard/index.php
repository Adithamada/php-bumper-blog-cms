<?php
require_once __DIR__ . "/../../include/function.php";
session_start();
$username = "";
$userId = "";
$success = "";
if(isset($_SESSION['success'])){
    $success = getFlashMessage($_SESSION['success']);
    unset($_SESSION['success']);
}
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

if (isset($_GET['userid'])) {
    global $conn;
    $userId = $_GET['userid'];
}

if (isset($_POST['logout'])) {
    logout();
}
ensureAuthenticated();
ensureUserId();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
    <title>BUMPER</title>
</head>

<body class="bg-light">
    <div class="container-fluid px-0 h-100 d-flex">
        <div class="sidebar col-2 bg-secondary-subtle p-2 shadow">
            <ul class="navbar-nav">
                <a href="" class="navbar-brand mb-4">
                    <h2><strong>BUMPER</strong><span class="text-secondary">blog</span></h2>
                </a>
                <li class="nav-item bg-secondary p-1 rounded shadow"><a href="" class="nav-link text-light">Blog</a></li>
                <li class="nav-item "><a href="" class="nav-link">Category</a></li>
            </ul>
        </div>
        <div class="col-10">
            <nav class="navbar navbar-expand bg-secondary shadow mb-5">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="" class="nav-link text-light active">Blog</a></li>
                    <li class="nav-item"><a href="" class="nav-link text-light">Category</a></li>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button
                                class="btn dropdown-toggle text-light"
                                type="button"
                                id="triggerId"
                                data-bs-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false">
                                <i class="bi bi-person-fill"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="triggerId">
                                <form action="" method="post">
                                    <button class="dropdown-item" type="submit" name="logout"><i class="bi bi-arrow-bar-left"></i> Logout</button>
                                </form>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show position-absolute z-1 top-3 " role="alert">
                    <strong>Success</strong> <?= $success  ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="container mb-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary-subtle d-flex justify-content-between">
                        <h2>Blog</h2>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                            Create
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Create Blog</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Title</label>
                                                <input type="text" class="form-control" name="title" id="title">
                                            </div>
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <input id="x" type="hidden" name="description">
                                                <trix-editor input="x" id="description"></trix-editor>
                                            </div>
                                            <div class="mb-3">
                                                <label for="img" class="form-label">Image</label>
                                                <input type="file" class="form-control" name="img" id="img">
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary">Create</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <table class="table table-bordered">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th colspan="2">Action</th>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>Lorem ipsum dolor sit amet.</td>
                                <td>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sint, corporis?</td>
                                <td>8 - 2 - 2025</td>
                                <td>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateModal">
                                        Update
                                    </button>
                                    <!-- Modal -->
                                    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Update Blog</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="">
                                                        <div class="mb-3">
                                                            <label for="title" class="form-label">Title</label>
                                                            <input type="text" class="form-control" name="title" id="title">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="description" class="form-label">Description</label>
                                                            <input id="x" type="hidden" name="description">
                                                            <trix-editor input="x" id="description"></trix-editor>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="img" class="form-label">Image</label>
                                                            <input type="file" class="form-control" name="img" id="img">
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-success">Update</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><button class="btn btn-danger">Delete</button></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
</body>

</html>