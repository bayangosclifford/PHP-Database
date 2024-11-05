<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "abc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $age = filter_var(trim($_POST['age']), FILTER_SANITIZE_NUMBER_INT);
    $address = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);
    $bio = filter_var(trim($_POST['bio']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $image_path = "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $fileName = basename($_FILES['image']['name']);
            $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $fileName);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileType, $allowedTypes)) {
                if ($_FILES['image']['size'] <= 2 * 1024 * 1024) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                        $image_path = $targetFilePath;
                    } else {
                        $error_message = "Error uploading image file.";
                    }
                } else {
                    $error_message = "Image file size should be 2MB or less.";
                }
            } else {
                $error_message = "Only JPG, JPEG, PNG, & GIF files are allowed.";
            }
        }

        if (empty($error_message)) {
            $stmt = $conn->prepare("INSERT INTO team_members (name, age, address, bio, email, image_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissss", $name, $age, $address, $bio, $email, $image_path);

            if ($stmt->execute()) {
                $success_message = "New member added successfully.";
                $_POST = array();
            } else {
                if ($conn->errno == 1062) {
                    $error_message = "A member with this email already exists.";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
            }

            $stmt->close();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Team Member</title>
    <style>
	body {
	    font-family: Arial, sans-serif;
	    background-color: #e8f4fa;
	    color: #333;
	    display: flex;              
	    justify-content: center;     
	    align-items: center;        
	    height: 100vh;
	    margin: 0;
	}

	.container {
	    display: flex;              
	    flex-direction: row;        
	}

	header {
	    display: flex;              
	    flex-direction: column;     
	    align-items: center;        
	    justify-content: center;
	    margin-right: 50px;       
	}

	header h1 {
	    color: #4a90e2;            
	    font-size: 2.5em;
	    margin-bottom: 10px;       
	    text-align: center;        
	}

	.back-button {
	    display: inline-block;
	    margin-top: 15px;          
	    padding: 10px 20px;
	    background-color: #ffffff; 
	    color: #4a90e2;            
	    text-decoration: none;
	    border: 2px solid #4a90e2; 
	    border-radius: 5px;
	    transition: background-color 0.3s ease, color 0.3s ease;
	}

	.back-button:hover {
	    background-color: #4a90e2; 
	    color: white;              
	}

	.form-container {
	    background-color: #ffffff; 
	    padding: 20px;
	    width: 400px;              
	    border-radius: 8px;
	    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1); 
	}

	.form-group {
	    margin-bottom: 25px;       
	}

	label {
	    font-size: 1em;
	    color: #4a90e2;            
	}

	input[type="text"],
	input[type="number"],
	input[type="email"],
	input[type="file"],
	textarea {
	    width: 100%;
	    padding: 10px;
	    margin-top: 5px;
	    border: 1px solid #ddd;
	    border-radius: 5px;
	    box-sizing: border-box;
	}

	button {
	    width: 100%;
	    padding: 12px;
	    background-color: #4a90e2; 
	    color: white;
	    border: none;
	    border-radius: 5px;
	    cursor: pointer;
	    font-size: 1em;
	    transition: background-color 0.3s ease;
	}

	button:hover {
	    background-color: #357abd; 
	}

	.success,
	.error {
	    text-align: center;
	    padding: 10px;
	    margin-bottom: 10px;
	    border-radius: 5px;
	}

	.success {
	    background-color: #d4edda; 
	    color: #155724;            
	}

	.error {
	    background-color: #f8d7da; 
	    color: #721c24;            
	}

	.required {
	    color: red;                
	}


    </style>
</head>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Team Member</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file -->
</head>
	<body>
	    <div class="container">
	        <header>
	            <h1>Add New Team Member</h1>
	            <a href="Main.php" class="back-button">View Team Profiles</a>
	        </header>

	        <div class="form-container">
	            <?php
	            if ($success_message) {
	                echo "<p class='success'>$success_message</p>";
	            }
	            if ($error_message) {
	                echo "<p class='error'>$error_message</p>";
	            }
	            ?>
	            <form method="POST" enctype="multipart/form-data">
	                <div class="form-group">
	                    <label for="name">Name<span class="required">*</span></label>
	                    <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
	                </div>
	                <div class="form-group">
	                    <label for="age">Age<span class="required">*</span></label>
	                    <input type="number" id="age" name="age" min="0" value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" required>
	                </div>
	                <div class="form-group">
	                    <label for="address">Address<span class="required">*</span></label>
	                    <input type="text" id="address" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
	                </div>
	                <div class="form-group">
	                    <label for="bio">Bio<span class="required">*</span></label>
	                    <textarea id="bio" name="bio" rows="4" required><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
	                </div>
	                <div class="form-group">
	                    <label for="email">Email<span class="required">*</span></label>
	                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
 	               </div>
	                <div class="form-group">
	                    <label for="image">Profile Picture</label>
	                    <input type="file" id="image" name="image" accept="image/*">
	                </div>
	                <button type="submit">Add Member</button>
	            </form>
	        </div>
	    </div>
	</body>
</html>
