<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/vendor/autoload.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Function to check if email domain has valid MX records
function isValidEmail($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, 'MX');
}

if (empty($_POST['name'])) {
    die('Name is required!');
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    die('Valid Email is required');
}

// Check if email domain has valid MX records
if (!isValidEmail($_POST['email'])) {
    die('Invalid email domain. Please use a valid email address.');
}

if (strlen($_POST['password']) < 8) {   
    die('Password must be atleast 8 Characters');
}

if (!preg_match('/[a-z]/', $_POST['password'])) {
    die('Password must contain at least one letter');
}

if (!preg_match('/[0-9]/', $_POST['password'])) {
    die('Password must contain at least one number');
}

if ($_POST['password'] != $_POST['confirm-password']) {
    die('Password must match');
}

$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
$verification_token = bin2hex(random_bytes(32));

$mysqli = require __DIR__ . '/database.php';

try {
    $sql = "INSERT INTO users (fullname, email, password_hash, verification_token) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $mysqli->stmt_init();
    
    if (!$stmt->prepare($sql)) {
        die("SQL error: " . $mysqli->error);
    }
    
    $stmt->bind_param("ssss",
                    $_POST["name"],
                    $_POST["email"],
                    $password_hash,
                    $verification_token);
                    
    $stmt->execute();
    
    // Send verification email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $_ENV['SMTP_PORT'];
        
        $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
        
        // Recipients
        $mail->addAddress($_POST["email"]);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify your email';
        
        $verification_link = "http://localhost/activity3/verify-email.php?token=" . $verification_token;
        
        $mail->Body = "
            <h1>Email Verification</h1>
            <p>Thank you for signing up! Please click the link below to verify your email address:</p>
            <p><a href='{$verification_link}'>{$verification_link}</a></p>
        ";

        if(!$mail->send()) {
            die("Message could not be sent. Mailer Error: " . $mail->ErrorInfo);
        } else {
            header("Location: signup-success-verify.php");
            exit;
        }

    } catch (Exception $e) {
        die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

} catch (mysqli_sql_exception $e) {
    if ($mysqli->errno === 1062) {
        die("Email already taken");
    } else {
        die($e->getMessage());
    }
}








