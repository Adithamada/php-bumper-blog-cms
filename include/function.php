<?php
require "db.php";
// FLASH MESSAGE
function setFlashMessage($key, $message)
{
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION[$key] = $message;
}

function getFlashMessage($key)
{
    if (!isset($_SESSION)) {
        session_start();
    }
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}
// FLASH MESSAGE

// AUTH
function ensureAuthenticated()
{
    if (!isset($_SESSION['username'])) {
        header("Location: ../login.php");
        exit;
    }
}
function ensureUserId()
{
    if (!isset($_GET['userid']) || !is_numeric($_GET['userid']) || $_GET['userid'] == 0) {
        if (isset($_SESSION['id']) && is_numeric($_SESSION['id'])) {
            header("Location: index.php?userid=" . $_SESSION['id']);
            exit();
        } else {
            die("Invalid or missing user ID."); // Or redirect to login page
        }
    }
}
// AUTH

// LOGIN & REGISTER
function register($data)
{
    global $conn;
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];

    $stmt = $conn->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
    $stmt->bind_param("sss", $username, $email, $password);
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

function login($data)
{
    global $conn;

    $username = $data['username'];
    $password = $data['password'];
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id();
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['id'] = $user['id'];
            setFlashMessage('success', 'Welcome ' . $_SESSION['username'] . '!');
            header("Location: dashboardBlog/index.php?userid=" . $user['id']);
            exit;
        }
    }
    return false;
}

function logout()
{
    session_start();
    $_SESSION = [];
    session_destroy();
    header("Location: ../login.php");
    exit;
}
// LOGIN & REGISTER

// CATEGORY CMS

function createCategory($category, $userId)
{
    global $conn;
    $query = "INSERT INTO category (category,user_id) VALUES (?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $category, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

function updateCategory($category, $id)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE category SET category=? WHERE id=? ");
    $stmt->bind_param("si", $category, $id);
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}
function deleteCategory($id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM category WHERE id=? ");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}
// CATEGORY CMS

// BLOG CMS
function createPost($data, $userId)
{
    global $conn;

    // Sanitize input
    $title = trim($data['title']);
    $description = trim($data['description']);
    $category = intval($data['category_id']);
    $tags = isset($data['tags']) ? trim($data['tags']) : '';
    $date = date("Y-m-d");

    // Handle file upload
    $imgName = null;
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $imgExt = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array(strtolower($imgExt), $allowedExts)) {
            setFlashMessage('error', 'Invalid image format! Only JPG, PNG, and GIF allowed.');
            return false;
        }

        // Rename the image to prevent duplicates
        $imgName = time() . '_' . uniqid() . '.' . $imgExt;
        $imgTmpName = $_FILES['img']['tmp_name'];
        $imgFolder = __DIR__ . "/../public/img_upload/";

        if (!file_exists($imgFolder)) {
            mkdir($imgFolder, 0777, true);
        }

        $imgPath = $imgFolder . $imgName;
        if (!move_uploaded_file($imgTmpName, $imgPath)) {
            setFlashMessage('error', 'Error uploading the image.');
            return false;
        }
    }

    // Insert into posts table
    $stmt = $conn->prepare("INSERT INTO post (title, description, date, img, category_id, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $title, $description, $date, $imgName, $category, $userId);

    if ($stmt->execute()) {
        $postId = $stmt->insert_id;
        $stmt->close();

        // Insert tags into post_tags table
        if (!empty($tags)) {
            $tagArray = explode(",", $tags);
            foreach ($tagArray as $tagName) {
                $tagName = trim($tagName);
                if ($tagName == '') continue;

                // Insert tag if not exists
                $stmtTag = $conn->prepare("INSERT INTO tags (tag) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
                $stmtTag->bind_param("s", $tagName);
                $stmtTag->execute();
                $tagId = $stmtTag->insert_id;
                $stmtTag->close();

                // Insert into post_tags table
                $stmtPostTag = $conn->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                $stmtPostTag->bind_param("ii", $postId, $tagId);
                $stmtPostTag->execute();
                $stmtPostTag->close();
            }
        }

        return true;
    } else {
        $stmt->close();
        return false;
    }
}

function getShortDescription($description, $wordLimit = 10)
{
    $words = explode(' ', strip_tags($description)); // Remove HTML tags and split into words
    if (count($words) > $wordLimit) {
        return implode(' ', array_slice($words, 0, $wordLimit)) . '...'; // Add ellipsis if longer
    }
    return $description; // Return full description if within limit
}

function getPostTags($postId)
{
    global $conn;
    $tags = [];

    $stmt = $conn->prepare("
        SELECT t.id, t.tag 
        FROM tags t
        JOIN post_tags pt ON t.id = pt.tag_id
        WHERE pt.post_id = ?
    ");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['tag']; // Collect tags as an array
    }

    $stmt->close();
    return $tags;
}


function updatePost($postId, $data, $userId)
{
    global $conn;

    // Sanitize input
    $title = trim($data['title']);
    $description = trim($data['description']);
    $category = intval($data['category_id']);
    $tags = isset($data['tags']) ? trim($data['tags']) : null; // NULL if user didn't change tags
    $date = date("Y-m-d");

    // Initialize $oldImg as NULL to avoid 'undefined variable' issue
    $oldImg = null;

    // Get current post details (including image)
    $stmt = $conn->prepare("SELECT img FROM post WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $stmt->store_result();

    // Check if post exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($oldImg);
        $stmt->fetch();
    }
    $stmt->close();

    // Handle file upload
    $imgName = $oldImg; // Keep old image if no new image is uploaded
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $imgExt = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array(strtolower($imgExt), $allowedExts)) {
            setFlashMessage('error', 'Invalid image format! Only JPG, PNG, and GIF allowed.');
            return false;
        }

        // Rename new image
        $imgName = time() . '_' . uniqid() . '.' . $imgExt;
        $imgTmpName = $_FILES['img']['tmp_name'];
        $imgFolder = __DIR__ . "/../public/img_upload/";

        if (!file_exists($imgFolder)) {
            mkdir($imgFolder, 0777, true);
        }

        $imgPath = $imgFolder . $imgName;
        if (!move_uploaded_file($imgTmpName, $imgPath)) {
            setFlashMessage('error', 'Error uploading the image.');
            return false;
        }

        // Delete old image if it exists
        if (!empty($oldImg)) {
            $oldImgPath = $imgFolder . $oldImg;
            if (file_exists($oldImgPath)) {
                unlink($oldImgPath);
            }
        }
    }

    // Update the post details
    $stmt = $conn->prepare("UPDATE post SET title = ?, description = ?, date = ?, img = ?, category_id = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssiii", $title, $description, $date, $imgName, $category, $postId, $userId);

    if ($stmt->execute()) {
        $stmt->close();

        // Only update tags if user provided new ones
        if (!is_null($tags)) {
            // Delete old tags
            $stmtDeleteTags = $conn->prepare("DELETE FROM post_tags WHERE post_id = ?");
            $stmtDeleteTags->bind_param("i", $postId);
            $stmtDeleteTags->execute();
            $stmtDeleteTags->close();

            // Insert new tags
            $tagArray = explode(",", $tags);
            foreach ($tagArray as $tagName) {
                $tagName = trim($tagName);
                if ($tagName == '') continue;

                // Check if the tag already exists
                $stmtCheckTag = $conn->prepare("SELECT id FROM tags WHERE tag = ?");
                $stmtCheckTag->bind_param("s", $tagName);
                $stmtCheckTag->execute();
                $stmtCheckTag->store_result();

                $tagId = null;

                if ($stmtCheckTag->num_rows > 0) {
                    // Tag exists, fetch its ID
                    $stmtCheckTag->bind_result($tagId);
                    $stmtCheckTag->fetch();
                } else {
                    // Tag doesn't exist, insert a new one
                    $stmtInsertTag = $conn->prepare("INSERT INTO tags (tag) VALUES (?)");
                    $stmtInsertTag->bind_param("s", $tagName);
                    $stmtInsertTag->execute();
                    $tagId = $stmtInsertTag->insert_id; // Get the newly inserted ID
                    $stmtInsertTag->close();
                }
                $stmtCheckTag->close();


                // Insert into post_tags table
                $stmtPostTag = $conn->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                $stmtPostTag->bind_param("ii", $postId, $tagId);
                $stmtPostTag->execute();
                $stmtPostTag->close();
            }
        }

        return true;
    } else {
        $stmt->close();
        return false;
    }
}

function deletePost($id)
{
    global $conn;

    // Delete from post_tags first
    $stmtDeletePostTag = $conn->prepare("DELETE FROM post_tags WHERE post_id = ?");
    $stmtDeletePostTag->bind_param("i", $id);
    $stmtDeletePostTag->execute();
    $stmtDeletePostTag->close();

    // Now delete from post
    $stmtDeletePost = $conn->prepare("DELETE FROM post WHERE id=?");
    $stmtDeletePost->bind_param("i", $id);

    if ($stmtDeletePost->execute()) {
        $stmtDeletePost->close();
        return true;
    } else {
        $stmtDeletePost->close();
        return false;
    }
}


// BLOG CMS

// HOME INDEX 
function getPostCategory($postId)
{
    global $conn;
    $categoryName = ""; // Store only one category name

    $stmt = $conn->prepare("
        SELECT c.category 
        FROM category c
        JOIN post p ON c.id = p.category_id
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $categoryName = $row['category']; // Get the category name
    }

    $stmt->close();
    return $categoryName;
}

function getPostUser($postId)
{
    global $conn;
    $username = "";

    $stmt = $conn->prepare("
        SELECT u.id, u.username 
        FROM users u
        JOIN post p ON u.id = p.user_id
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $username = $row['username']; // Collect username
    }

    $stmt->close();
    return $username;
}