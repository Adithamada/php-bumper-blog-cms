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
            setFlashMessage('success', 'Login Success!');
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

function createCategory($category,$userId){
    global $conn;
    $query = "INSERT INTO category (category,user_id) VALUES (?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si",$category,$userId);

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
