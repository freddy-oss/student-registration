<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_db";

// Initialize messages
$successMessage = "";
$errorMessage = "";

// Create database connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and sanitize form data
        $name = isset($_POST["name"]) ? mysqli_real_escape_string($conn, trim($_POST["name"])) : "";
        $surname = isset($_POST["surname"]) ? mysqli_real_escape_string($conn, trim($_POST["surname"])) : "";
        $email = isset($_POST["email"]) ? mysqli_real_escape_string($conn, trim($_POST["email"])) : "";
        $password = isset($_POST["password"]) ? $_POST["password"] : "";
        $confirmPassword = isset($_POST["confirm_password"]) ? $_POST["confirm_password"] : "";

        // Validate input
        if (empty($name) || empty($surname) || empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }

        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if table exists, if not create it
        $checkTable = $conn->query("SHOW TABLES LIKE 'students'");
        if ($checkTable->num_rows == 0) {
            $createTable = "CREATE TABLE students (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, surname VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
            if (!$conn->query($createTable)) {
                throw new Exception("Error creating table: " . $conn->error);
            }
        }

        // Prepare statement
        $stmt = $conn->prepare("INSERT INTO students (name, surname, email, password) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind parameters and execute
        if (!$stmt->bind_param("ssss", $name, $surname, $email, $hashedPassword)) {
            throw new Exception("Binding parameters failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error registering user: " . $stmt->error);
        }

        $successMessage = "Registration successful!";
        $stmt->close();
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>
<!Doctype html>
<html>
<head>
    <title>Student Registration</title>
    <style>
        Body{
            background-color: aquamarine;
            font-family:Arial,sans-serif,Georgia;
            height:100vh;
            justify-content: center;
            color:black;
            
        }
        h2{
            text-align: center;
            font-size: 40px;
        }
        input[type="text"], input[type="email"],input[type="password"] {
        
            padding:8px;
            margin: 5px 0;
            width: 100%;
            box-sizing:content-box;
        }
        p{
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 100px;
            
        }
        h1{
            text-align: center;
            font-size: 40px; 
        }

        h5{
            text-align: center;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
        button{
            color: black;
            font-weight: 100px;

        }
        input[type="submit"]{
            background-color: #4caf50;
            color: white;
            height: 20px;
            width:600 px;
            cursor: pointer;
            padding:8px;
            margin: 4px 0;
            width: 100%;
            box-sizing:content-box;
        }
         input[type="submit"]:hover{
            background-color:blue
         }
       
    </style>
</head>
<body>
    <h1>Welcome to Student Registration</h1>
    <p>Please print neatly,using blue or black ink.Read & sign carefully</p>
    <h2>Create new user</h2>

    <?php if (!empty($errorMessage)): ?>
        <div style="color: red; text-align: center; margin-bottom: 15px; padding: 10px; background-color: #ffe6e6; border-radius: 5px;">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div style="color: green; text-align: center; margin-bottom: 15px; padding: 10px; background-color: #e6ffe6; border-radius: 5px;">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="name">Name:</label><br/>
        <input type="text" id="name" name="name" required 
               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"><br/>

        <label for="surname">Surname:</label><br>
        <input type="text" id="surname" name="surname" required 
               value="<?php echo isset($_POST['surname']) ? htmlspecialchars($_POST['surname']) : ''; ?>"><br>
        
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required 
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"><br>
        
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required 
               pattern=".{6,}" title="Password must be at least 6 characters long"><br>

        <label for="confirm_password">Confirm Your Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>
        
        <input type="submit" value="Register">
        
        <h5>Your information will never be shared with anyone.</h5>
    </form>
</body>
</html>

