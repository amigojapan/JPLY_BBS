<?php
// test.php - Simple PHP script to test your JavaScript CURL POST function
// Run with: php -S localhost:8000
// Then call from JS: await CURL('http://localhost:8000/test.php', { data: 'param1=value1&param2=value2' });

header('Content-Type: text/plain');

// Set error reporting to display all types of errors
error_reporting(E_ALL);

// Ensure errors are printed to the screen
ini_set('display_errors', '1');

// Ensure errors occurring during PHP's startup sequence are also shown
ini_set('display_startup_errors', '1');


function hash_string_with_salt($string) {
    // Generate a secure random salt (e.g., 16 bytes for a 32-character hex salt)
    $salt_length = 16;
    try {
        $salt = bin2hex(random_bytes($salt_length));
    } catch (Exception $e) {
        // Handle potential errors in random_bytes generation (rare)
        die("Could not generate secure salt: " . $e->getMessage());
    }

    // Concatenate the salt and the string before hashing
    $salted_string = $salt . $string;

    // Generate the SHA512 hash
    $hash = hash('sha512', $salted_string);

    // Return both the hash and the salt so they can be stored and verified later
    return [
        'hash' => $hash,
        'salt' => $salt
    ];
}


// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST request received successfully!\n\n";
    echo "Received data:\n";

    if (!empty($_POST)) {
        foreach ($_POST as $key => $value) {
            // Handle array values (e.g., param[]=a&param[]=b)
            if (is_array($value)) {
                echo "$key: " . implode(', ', $value) . "\n";
            } else {
                echo "$key: $value\n";
            }
        }
    } else {
        echo "No form data was sent.\n";
    }
    /*
    -- Optional: Ensure the database is using UTF-8 (only works before the first table is created)
PRAGMA encoding = "UTF-8";

CREATE TABLE users (
    userID INTEGER PRIMARY KEY AUTOINCREMENT,
    nick TEXT NOT NULL,
    pw TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE
);
ALTER TABLE users ADD COLUMN salt TEXT NOT NULL;

    */

    // Example usage:
    $password = $_POST["pw"];
    $result = hash_string_with_salt($password);
    $salt = $result['salt'];

    echo "Original String: " . htmlspecialchars($password) . "\n";
    echo "Generated Salt: " . htmlspecialchars($result['salt']) . "\n";
    echo "SHA512 Hash: " . htmlspecialchars($result['hash']) . "\n";

// 1. Connection details
$databaseFile = 'userdb.sqlite3';

try {
    // 2. Create a new PDO connection to the SQLite database
    $pdo = new PDO("sqlite:" . $databaseFile);
    
    // Set error mode to exceptions for better debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3. User data (typically from a form like $_POST['nick'])
    $nick  = $_POST["nick"];
    $email = $_POST["email"];
    //$plainPassword = "securePassword123";

    // 4. Hash the password using the current 2025 standard (BCRYPT or Argon2)
    $hashedPassword = $result['hash'];

    // 5. Prepare the SQL statement with placeholders
    $sql = "INSERT INTO users (nick, pw, salt, email) VALUES (:nick, :pw, :salt, :email)";
    $stmt = $pdo->prepare($sql);

    // 6. Bind values and execute
    // This safely handles the data, preventing SQL injection
    $stmt->execute([
        ':nick'  => $nick,
        ':pw'    => $hashedPassword,
        ':salt'    => $salt,
        ':email' => $email
    ]);

    echo "User successfully added to the database!";

} catch (PDOException $e) {
    // Handle errors (e.g., duplicate email)
    if ($e->getCode() == 23000) {
        echo "Error: This email is already registered.";
    } else {
        echo "Database Error: " . $e->getMessage();
    }
}
 
} else {
    // Reject non-POST requests
    http_response_code(405); // Method Not Allowed
    echo "Error: This endpoint only accepts POST requests.\n";
}
?>
