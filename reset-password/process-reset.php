<?php
require "../config.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to redirect with error message
function redirectWithError($message) {
    $_SESSION['error'] = $message;
    header("Location: ./reset-password.php?token=" . ($_POST['token'] ?? ''));
    exit();
}

// 1. Get token from POST data
$token = $_POST['token'] ?? null;
if (!$token) {
    redirectWithError("Token tidak valid!");
}

// 2. Hash the token for database comparison
$token_hash = hash("sha256", $token);

try {
    // 3. Find user with this token
    $query = "SELECT * FROM user WHERE reset_token_hash = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirectWithError("Token tidak ditemukan!");
    }

    // 4. Check token expiration
    $expiresDate = strtotime($user['reset_token_expires_at']);
    if ($expiresDate <= time()) {
        redirectWithError("Token sudah kadaluarsa!");
    }

    // 5. Validate new passwords
    $password = $_POST['password'] ?? '';
    $password_confirmation = $_POST['password-confirmation'] ?? '';

    // Basic validation
    if (empty($password)) {
        redirectWithError("Password tidak boleh kosong!");
    }

    if ($password !== $password_confirmation) {
        redirectWithError("Password dan konfirmasi password tidak sama!");
    }

    // Stronger password validation (minimum 8 chars, at least 1 number and 1 special char)
    if (strlen($password) < 8) {
        redirectWithError("Password minimal 8 karakter!");
    }

    if (!preg_match('/[0-9]/', $password)) {
        redirectWithError("Password harus mengandung angka!");
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        redirectWithError("Password harus mengandung karakter khusus!");
    }

    // 6. Hash the new password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 7. Update user password and clear reset token
    $updateQuery = "UPDATE user 
                   SET password = ?, 
                       reset_token_hash = NULL, 
                       reset_token_expires_at = NULL,
                       updated_at = NOW()
                   WHERE ID = ?";
    
    $updateStmt = $pdo->prepare($updateQuery);
    $success = $updateStmt->execute([$password_hash, $user['ID']]);

    if (!$success) {
        redirectWithError("Gagal memperbarui password. Silakan coba lagi.");
    }

    // 8. Password updated successfully
    $_SESSION['success'] = "Password berhasil direset! Silakan login dengan password baru Anda.";
    
    echo'
        <script>
            alert(" '.$_SESSION['success'].' ");
            window.location.href = "../login.php";
        </script>
    ';

    //header("Location: ../login.php"); // Redirect to login page
    exit();

} catch (PDOException $e) {
    // Handle database errors
    error_log("Database error: " . $e->getMessage());
    redirectWithError("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
}

?>