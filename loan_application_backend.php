<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "loan_application_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $address = $conn->real_escape_string($_POST['address']);
    $employer = $conn->real_escape_string($_POST['employer']);
    $income = (int)$_POST['income'];
    $loanAmount = (int)$_POST['loanAmount'];
    $loanDuration = (int)$_POST['loanDuration'];
    $loanPurpose = $conn->real_escape_string($_POST['loanPurpose']);

    // Handle file upload
    if (isset($_FILES['idPhoto']) && $_FILES['idPhoto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['idPhoto']['tmp_name'];
        $fileName = basename($_FILES['idPhoto']['name']);
        $filePath = $uploadDir . $fileName;

        // Ensure unique file name
        $filePath = $uploadDir . time() . "_" . $fileName;

        if (move_uploaded_file($fileTmpPath, $filePath)) {
            // Insert data into the database
            $stmt = $conn->prepare("INSERT INTO applications (name, email, phone, dob, address, employer, income, loan_amount, loan_duration, loan_purpose, id_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssiiiss", $name, $email, $phone, $dob, $address, $employer, $income, $loanAmount, $loanDuration, $loanPurpose, $filePath);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Application submitted successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Failed to upload ID photo."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "ID photo upload error."]);
    }
}

// Fetch all loan applications
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT * FROM applications");

    $applications = [];
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }

    echo json_encode($applications);
}

$conn->close();
?>
