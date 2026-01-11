<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="shortcut icon" href="../images/logo.png">
    <link rel="stylesheet" href="./style.css">
    <title>Parent Portal</title>
</head>

<body>
    <?php
    session_start();
    if (!isset($_SESSION['parent_id'])) {
        header("Location: ../login.php");
        exit();
    }
    ?>
