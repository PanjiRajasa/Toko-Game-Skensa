<?php
    require "../config.php";

    // use Dotenv\Dotenv;
    // require __DIR__ . "/vendor/autoload.php";

    // //konfigurasi .env
    // $dotenv = Dotenv::createImmutable(__DIR__);
    // $dotenv->load();

    $email = $_GET['email'] ?? "";

    $token = bin2hex(random_bytes(16)); //buat token yang unprintable

    $token_hash = hash("sha256", $token); //buat token hash

    $expiry = date("Y-m-d H:i:s", time() + 60 * 30); //buat tanggal expired

    $query = "UPDATE user 
              SET reset_token_hash = ?, reset_token_expires_at = ?
              WHERE email = ?";

    $stmt = $pdo->prepare($query);

    //execute query
    $stmt->execute([$token_hash, $expiry, $email]);


    //kalau email ada
    if($stmt->rowCount() > 0) {
        require "../mailer.php";

        $mail->addAddress($email);
        $mail->Subject = "Password Reset";
        $mail->Body = <<<END
        <html>

        </html>
            <body>
            <p>Hello,</p>
            <p>We received a request to reset your password for your <strong>Togame Store</strong> account. To proceed, please click the link below:</p>

            <p style="margin: 20px 0;">
                <a 
                    href="http://localhost/phptask/SAS%20Toko%20Game/reset-password/reset-password.php?token=$token" 


                    style="
                        background: #4e73df;
                        color: white;
                        padding: 10px 20px;
                        text-decoration: none;
                        border-radius: 5px;
                        font-weight: bold;
                    "
                >
                    Reset My Password
                </a>
            </p>

            <p><strong>This link will expire in <span style="color: #d9534f;">1 hour</span>.</strong> If you didn’t request this, please ignore this email or contact our support team.</p>

            <p>Alternatively, copy and paste this URL into your browser:</p>
            <p style="word-break: break-all;">
                <code>http://localhost/phptask/SAS%20Toko%20Game/reset-password/reset-password.php?token=$token</code>
            </p>

            <p>Thanks,<br>The Togame Store Team</p>


            </body>
            
END;

        $mail->AltBody = "

        Hello,

        We received a request to reset your password for your Togame Store account. To proceed, please visit the following link:

        Reset Password: http://localhost/phptask/SAS%20Toko%20Game/reset-password/reset-password.php?token=$token

        (This link expires in 1 hour.)

        If you didn't request this, please ignore this email or contact support.

        Thanks,
        The Togame Store Team

        ";

        try {
            $mail->send();

            error_log("Email sent to $email");
        } catch(Exception $e) {
            echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";

            error_log("Email send failed: " . $e->getMessage());
        }
    }

    //echo "Message sent, please check your inbox.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Password Reset</title>
</head>
<body>
    <h1>Please check your email inbox</h1>
    <a href="../login.php">Back</a>
</body>
</html>