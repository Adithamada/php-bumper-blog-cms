<?php
session_start();
require_once __DIR__ . "/../../include/function.php";
$username = "";
$userId = "";
$success = "";
$error = "";
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

if (isset($_GET['userid'])) {
    global $conn;
    $userId = $_GET['userid'];

    if (isset($_SESSION['role']) && $_SESSION['role'] == 1) {
        $stmtSelectCategory = $conn->prepare("SELECT * FROM category");
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] == 0) {
        $stmtSelectCategory = $conn->prepare("SELECT * FROM category WHERE user_id = ?");
        $stmtSelectCategory->bind_param('i', $userId);
    } else {
        return []; // Return empty array if role is not set
    }

    $stmtSelectCategory->execute();
    $resultSelectCategory = $stmtSelectCategory->get_result();
    $rowSelectCategory = $resultSelectCategory->fetch_all(MYSQLI_ASSOC);
}

if (isset($_POST['create'])) {
    $category = htmlspecialchars($_POST['category']);

    if (empty($category)) {
        setFlashMessage('error', 'Fail to make category required!');
    } else {
        if (createCategory($category, $userId)) {
            setFlashMessage('success', 'New Category!');
        } else {
            setFlashMessage('error', 'Fail to create category!');
        }
        header("Location: index.php?userid=" . $userId);
        exit;
    }
}

if (isset($_POST['update'])) {
    $id = htmlspecialchars($_POST['updateId']);
    $category = htmlspecialchars($_POST['category']);

    if (empty($category)) {
        setFlashMessage('error', 'Fail to update category required!');
    } else {
        if (updateCategory($category, $id)) {
            setFlashMessage('success', 'Update Category!');
        } else {
            setFlashMessage('error', 'Fail to update category!');
        }
        header("Location: index.php?userid=" . $userId);
        exit;
    }
}

if (isset($_POST['delete'])) {
    $id = htmlspecialchars($_POST['deleteId']);

    if (deleteCategory($id)) {
        setFlashMessage('success', 'Category deleted!');
    } else {
        setFlashMessage('error', 'Fail delete category!');
    }
    header("Location: index.php?userid=" . $userId);
    exit;
}

if (isset($_POST['logout'])) {
    logout();
}
$successMessage = getFlashMessage('success');
if ($successMessage !== null) {
    $success = htmlspecialchars($successMessage);
}
$errorMessage = getFlashMessage('error');
if ($errorMessage !== null) {
    $error = htmlspecialchars($errorMessage);
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
                <a href="" class="navbar-brand mb-4 border-bottom border-dark-subtle">
                    <h2><strong>BUMPER</strong><span class="text-secondary">blog</span></h2>
                </a>
                <li class="nav-item"><a href="../dashboardBlog/index.php?<?= "userid=" . $userId ?>" class="nav-link">Blog</a></li>
                <li class="nav-item bg-secondary p-1 rounded shadow"><a href="../dashboardCategory/index.php?<?= "userid=" . $userId ?>" class="nav-link text-light">Category</a></li>
                <?php if ($_SESSION['role'] == 1): ?>
                    <li class="nav-item "><a href="../dashboardUser/index.php?<?= "userid=" . $userId ?>" class="nav-link">User</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="col-10">
            <nav class="navbar navbar-expand bg-secondary shadow mb-5">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="../dashboardBlog/index.php?<?= "userid=" . $userId ?>" class="nav-link text-light active">Blog</a></li>
                    <li class="nav-item"><a href="../dashboardCategory/index.php?<?= "userid=" . $userId ?>" class="nav-link text-light">Category</a></li>
                    <?php if ($_SESSION['role'] == 1): ?>
                        <li class="nav-item"><a href="../dashboardUser/index.php?<?= "userid=" . $userId ?>" class="nav-link text-light">User</a></li>
                    <?php endif; ?>
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
                <div class="container">
                    <div class="col-5 alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success</strong><?= $success  ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php elseif ($error): ?>
                <div class="container">
                    <div class="col-5 alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Danger</strong> <?= $error  ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
            <div class="container mb-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary-subtle d-flex justify-content-between">
                        <h2>Category</h2>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                            Create
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Create Category</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="" method="post">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="category" class="form-label">Category</label>
                                                <input type="text" class="form-control" name="category" id="category">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary" name="create">Create</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <table class="table table-bordered">
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <?php if ($_SESSION['role'] == 1): ?>
                                    <th>User</th>
                                <?php endif; ?>
                                <th colspan="2">Action</th>
                            </tr>
                            <?php $i = 1; ?>
                            <?php foreach ($rowSelectCategory as $category): ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td> <?= $category['category'] ?> </td>
                                    <?php if ($_SESSION['role'] == 1): ?>
                                        <td> <?= $category['user_id'] ?> </td>
                                    <?php endif; ?>
                                    <td>
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateModal<?= $category['id']  ?>">
                                            Update
                                        </button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="updateModal<?= $category['id']  ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Update Blog</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="" method="post">
                                                        <div class="modal-body">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="category" class="form-label">Category</label>
                                                                    <input type="hidden" name="updateId" value="<?= $category['id'] ?>">
                                                                    <input type="text" class="form-control" name="category" id="category" value="<?= $category['category']  ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success" name="update">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <form action="" method="post" onclick="return confirm('Are you sure?')">
                                            <input type="hidden" name="deleteId" value="<?= $category['id']  ?>">
                                            <button class="btn btn-danger" type="submit" name="delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            <?php endforeach; ?>
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