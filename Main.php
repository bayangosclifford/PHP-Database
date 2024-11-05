<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "abc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT image_path FROM team_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($image_path);
    $stmt->fetch();
    $stmt->close();

    $deleteSql = "DELETE FROM team_members WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }
        echo "<p class='success'>Member deleted successfully.</p>";
    } else {
        echo "<p class='error'>Error deleting member: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

$sql = "SELECT id, name, age, address, bio, email, image_path FROM team_members";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Profiles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e6f0f9;
            margin: 0;
            padding: 20px;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        header h1 {
            color: #4a90e2;
            font-size: 2.5em;
        }
        
        .add-button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        
        .add-button:hover {
            background-color: #357abd;
        }
        
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 1200px;
        }

        .team-member {
            background-color: #ffffff;
            width: 300px;
            margin: 15px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .team-member img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        
        .member-info h2 {
            font-size: 1.5em;
            color: #357abd;
            margin: 10px 0;
        }
        
        .member-info p {
            font-size: 1em;
            color: #666;
            margin: 5px 0;
        }
        
        .member-info a {
            color: #4a90e2;
            text-decoration: none;
        }
        
        .delete-button {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 15px;
            background-color: #e57373;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .delete-button:hover {
            background-color: #c0392b;
        }
        
        .info {
            text-align: center;
            color: #999;
            font-size: 1.2em;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Our Team</h1>
        <a href="add_member.php" class="add-button">Add New Member</a>
    </header>

    <div class="container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='team-member'>";
                if ($row['image_path'] && file_exists($row['image_path'])) {
                    echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
                } else {
                    echo "<img src='uploads/default.png' alt='No Image'>";
                }
                echo "<div class='member-info'>";
                echo "<h2>" . htmlspecialchars($row['name']) . "</h2>";
                echo "<p><strong>Age:</strong> " . htmlspecialchars($row['age']) . "</p>";
                echo "<p><strong>Address:</strong> " . htmlspecialchars($row['address']) . "</p>";
                echo "<p><strong>Bio:</strong> " . htmlspecialchars($row['bio']) . "</p>";
                echo "<p><strong>Email:</strong> <a href='mailto:" . htmlspecialchars($row['email']) . "'>" . htmlspecialchars($row['email']) . "</a></p>";
                echo "<a href='Main.php?delete=" . $row['id'] . "' class='delete-button' onclick=\"return confirm('Are you sure you want to delete this member?');\">Delete</a>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='info'>No team members found.</p>";
        }
        $conn->close();
        ?>
    </div>
</body>
</html>
