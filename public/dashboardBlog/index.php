<?php
session_start();
require_once __DIR__ . "/../../include/function.php";
$username = "";
$userId = "";
$success = "";
$error = "";
$warning = "";
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

if (isset($_GET['userid'])) {
    global $conn;
    $userId = $_GET['userid'];

    //SELECT CATEGORY
    if (isset($_SESSION['role']) && $_SESSION['role'] == 1) {
        $stmtSelectPost = $conn->prepare("SELECT * FROM post");
        $stmtSelectCategory = $conn->prepare("SELECT * FROM category");
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] == 0) {
        $stmtSelectPost = $conn->prepare("SELECT * FROM post WHERE user_id = ?");
        $stmtSelectPost->bind_param('i', $userId);
        $stmtSelectCategory = $conn->prepare("SELECT * FROM category WHERE user_id = ?");
        $stmtSelectCategory->bind_param('i', $userId);
    } else {
        return []; // Return empty array if role is not set
    }

    $stmtSelectPost->execute();
    $resultSelectPost = $stmtSelectPost->get_result();
    $rowSelectPost = $resultSelectPost->fetch_all(MYSQLI_ASSOC);

    $stmtSelectCategory->execute();
    $resultSelectCategory = $stmtSelectCategory->get_result();
    $rowSelectCategory = $resultSelectCategory->fetch_all(MYSQLI_ASSOC);

    //CHECK CATEGORY
    $stmtCountCategory = $conn->prepare("SELECT COUNT(*) as total FROM category WHERE user_id=?");
    $stmtCountCategory->bind_param('i', $userId);
    $stmtCountCategory->execute();
    $resultCountCategory = $stmtCountCategory->get_result();
    $rowCountCategory = $resultCountCategory->fetch_assoc();
    if ($rowCountCategory['total'] > 0) {
        $hasCategory = true;
    } else {
        setFlashMessage('warning', 'Make category first!');
    }
}

//CREATE POST
if (isset($_POST['create'])) {
    if (empty($_POST['title'])) {
        setFlashMessage('error', 'Fail to make, title required!');
    } elseif (empty($_POST['description'])) {
        setFlashMessage('error', 'Fail to make, description required!');
    } elseif (!isset($_FILES['img']) || $_FILES['img']['error'] !== UPLOAD_ERR_OK) {
        setFlashMessage('error', 'Image is required!');
    } elseif (empty($_POST['tags'])) {
        setFlashMessage('error', 'Fail to make, tag is required!');
    } else {
        if (createPost($_POST, $userId)) {
            setFlashMessage('success', 'New post!');
        } else {
            setFlashMessage('error', 'Fail to make  post!');
        }
        header("Location: index.php?userid=" . $userId);
        exit;
    }
}
// UPDATE POST
if (isset($_POST['update'])) {
    // echo var_dump($_POST);
    $postId = $_POST['updateId'];
    if (empty($_POST['title'])) {
        setFlashMessage('error', 'Fail to update, title is required!');
    } elseif (empty($_POST['description'])) {
        setFlashMessage('error', 'Fail to update, description is required!');
    } elseif (empty($_POST['tags'])) {
        setFlashMessage('error', 'Fail to update, tag is required!');
    } else {
        if (updatePost($postId, $_POST, $userId)) {
            setFlashMessage('success', 'Updated post!');
        } else {
            setFlashMessage('error', 'Fail to update post!');
        }
        header("Location: index.php?userid=" . $userId);
        exit;
    }
}

if (isset($_POST['delete'])) {
    $deleteId = $_POST['deleteId'];

    if (deletePost($deleteId)) {
        setFlashMessage('success', 'Deleted post!');
    } else {
        setFlashMessage('error', 'Fail to delete post!');
    }
    header("Location: index.php?userid=" . $userId);
    exit;
}


//LOGOUT
if (isset($_POST['logout'])) {
    logout();
}
$successMessage = getFlashMessage('success');
if ($successMessage !== null) {
    $success = htmlspecialchars($successMessage);
}
$warningMessage = getFlashMessage('warning');
if ($warningMessage !== null) {
    $warning = htmlspecialchars($warningMessage);
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
                <a href="" class="navbar-brand mb-4">
                    <h2><strong>BUMPER</strong><span class="text-secondary">blog</span></h2>
                </a>
                <li class="nav-item bg-secondary p-1 rounded shadow"><a href="../dashboardBlog/index.php?<?= "userid=" . $userId ?>" class="nav-link text-light">Blog</a></li>
                <li class="nav-item "><a href="../dashboardCategory/index.php?<?= "userid=" . $userId ?>" class="nav-link">Category</a></li>
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
                <div class="container">
                    <div class="col-5 alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success</strong> <?= $success  ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php elseif ($warning): ?>
                <div class="container">
                    <div class="col-5 alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Warning</strong> <?= $warning  ?>
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
                        <h2>Blog</h2>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                            Create
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Create Blog</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="" method="post" enctype="multipart/form-data">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Title</label>
                                                <input type="text" class="form-control" name="title" id="title">
                                            </div>
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <input id="description_hidden_create" type="hidden" name="description">
                                                <trix-editor input="description_hidden_create"></trix-editor>
                                            </div>
                                            <div class="mb-3">
                                                <label for="category" class="form-label">category</label>
                                                <select name="category_id" id="category" class="form-control">
                                                    <?php foreach ($rowSelectCategory as $c): ?>
                                                        <option value="<?= $c['id'] ?>"><?= $c['category'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="tag" class="form-label">Tag</label>
                                                <input type="text" class="form-control" name="tags" id="tag">
                                            </div>
                                            <div class="mb-3">
                                                <label for="img" class="form-label">Image</label>
                                                <input type="file" class="form-control" name="img" id="img">
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
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date</th>
                                <?php if ($_SESSION['role'] == 1): ?>
                                    <th>User</th>
                                <?php endif; ?>
                                <th colspan="2">Action</th>
                            </tr>
                            <?php $i = 1; ?>
                            <?php foreach ($rowSelectPost as $p): ?>
                                <tr>
                                    <td><?= $i  ?></td>
                                    <td><?= $p['title']  ?></td>
                                    <td><?= getShortDescription($p['description'])  ?></td>
                                    <td><?= $p['date']  ?></td>
                                    <?php if ($_SESSION['role'] == 1): ?>
                                        <td><?= $p['user_id']  ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateModal<?= $p['id'] ?>" data-post-id="<?= $p['id'] ?>">
                                            Update
                                        </button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="updateModal<?= $p['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Update Blog</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="" enctype="multipart/form-data" method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="updateId" value="<?= $p['id'] ?>">
                                                            <div class="mb-3">
                                                                <label for="title" class="form-label">Title</label>
                                                                <input type="text" class="form-control" name="title" id="title" value="<?= $p['title']  ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="description" class="form-label">Description</label>
                                                                <input id="description_hidden_<?= $p['id'] ?>" type="hidden" name="description" value="<?= htmlspecialchars($p['description']) ?>">
                                                                <trix-editor input="description_hidden_<?= $p['id'] ?>"></trix-editor>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="category" class="form-label">category</label>
                                                                <select name="category_id" id="category" class="form-control">
                                                                    <?php foreach ($rowSelectCategory as $c): ?>
                                                                        <?php $selected = isset($p['category_id']) && $p['category_id'] == $c['id'] ? 'selected' : ''; ?>
                                                                        <option value="<?= $c['id'] ?>" <?= $selected ?>><?= $c['category'] ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="tag" class="form-label">Tag</label>
                                                                <?php
                                                                $postTags = getPostTags($p['id']); // Get tags for this post
                                                                $tagsString = implode(", ", $postTags); // Convert array to comma-separated string
                                                                ?>
                                                                <input type="text" class="form-control" name="tags" id="tag" value="<?= htmlspecialchars($tagsString)  ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="img" class="form-label">Image</label>
                                                                <input type="file" class="form-control" name="img" id="img">
                                                                <img src="../img_upload/<?= $p['img'] ?>" alt="" class="img-fluid w-100 rounded mt-2">
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
                                        <form action="" method="post" onsubmit="return confirmDelete(event)">
                                            <input type="hidden" name="deleteId" value="<?= $p['id'] ?>">
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
    <script>
        document.addEventListener("show.bs.modal", function(event) {
            var modal = event.target; // Get the modal that is being opened
            var button = event.relatedTarget; // The button that triggered the modal
            var postId = button ? button.getAttribute("data-post-id") : null;

            var hiddenInput = modal.querySelector("input[name='description']");
            var trixEditor = modal.querySelector("trix-editor");

            if (postId) {
                // Editing an existing post
                var existingHiddenInput = document.getElementById("description_hidden_" + postId);
                if (existingHiddenInput) {
                    hiddenInput.value = existingHiddenInput.value; // Set hidden input for form submission
                    trixEditor.editor.loadHTML(existingHiddenInput.value); // Load content into Trix editor
                }
            } else {
                // Creating a new post
                hiddenInput.value = ""; // Clear hidden input
                trixEditor.editor.loadHTML(""); // Reset Trix editor
            }
        });
    </script>
    <script>
        function confirmDelete(event) {
            if (!confirm("Are you sure?")) {
                event.preventDefault(); // Stops form submission if "Cancel" is clicked
                return false;
            }
            return true; // Allows form submission if "OK" is clicked
        }
    </script>
</body>

</html>