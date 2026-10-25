<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include "../../includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);

    if ($name === "") {

        $error = "Category name is required.";

    } else {

        $nameSafe = mysqli_real_escape_string($conn, $name);

        /*
            Check duplicate category
        */
        $checkQuery = "
            SELECT id
            FROM categories
            WHERE name = '$nameSafe'
            LIMIT 1
        ";

        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {

            $error = "This category already exists.";

        } else {

            $insertQuery = "
                INSERT INTO categories (name)
                VALUES ('$nameSafe')
            ";

            if (mysqli_query($conn, $insertQuery)) {

                header("Location: index.php?success=created");
                exit;

            } else {

                $error = "Failed to create category.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Category - Admin Panel</title>

    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f4f6f9;
        }

        .topbar {
            background: #222;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
        }

        .topbar a {
            color: white;
            text-decoration: none;
            background: #dc3545;
            padding: 8px 14px;
            border-radius: 5px;
        }

        .container {
            max-width: 650px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        h1 {
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .back {
            margin-left: 10px;
            color: #333;
            text-decoration: none;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="topbar">

    <strong>Admin Panel</strong>

    <a href="../logout.php">
        Logout
    </a>

</div>


<div class="container">

    <h1>Create Category</h1>


    <?php if ($error !== ""): ?>

        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="form-group">

            <label>
                Category Name
            </label>

            <input
                type="text"
                name="name"
                placeholder="Enter category name"
                required
            >

        </div>


        <button
            type="submit"
            class="btn"
        >
            Create Category
        </button>


        <a
            href="index.php"
            class="back"
        >
            Cancel
        </a>

    </form>

</div>

</body>
</html>