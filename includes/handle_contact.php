<?php
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate required fields
        $required_fields = ['name', 'phone', 'message'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("Missing required field: " . $field);
            }
        }

        // Get form data and sanitize
        $name = $conn->real_escape_string(trim($_POST['name']));
        $phone = $conn->real_escape_string(trim($_POST['phone']));
        $message = $conn->real_escape_string(trim($_POST['message']));
        $date = date('Y-m-d H:i:s');

        // Validate phone (basic validation)
        if (!preg_match("/^[0-9+()\-\s]{6,20}$/", $phone)) {
            throw new Exception("Invalid phone number format");
        }

        // Create the contact table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS contact (
            Contact_id INT AUTO_INCREMENT PRIMARY KEY,
            Name VARCHAR(100) NOT NULL,
            Phone_No VARCHAR(20) NOT NULL,
            Message TEXT NOT NULL,
            Date DATETIME NOT NULL
        )";
        
        if (!$conn->query($create_table_sql)) {
            throw new Exception("Error creating table: " . $conn->error);
        }

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO contact (Name, Phone_No, Message, Date) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssss", $name, $phone, $message, $date);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Thank you for contacting us! We'll get back to you soon."
            ]);
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    } finally {
        $conn->close();
    }
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
}
?>
?>