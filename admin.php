<?php
require 'config.php';

// Proteksi halaman hanya untuk admin
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Fungsi sanitize input agar aman dari XSS
function sanitize($str)
{
  return htmlspecialchars(trim($str));
}


// HANDLE DELETE user
if (isset($_GET['delete_user'])) {
  $id = (int)$_GET['delete_user'];
  $stmt = $pdo->prepare("DELETE FROM user WHERE ID = ?");
  $stmt->execute([$id]);
  header("Location: admin.php");
  exit;
}


// HANDLE UPDATE user
if (isset($_POST['update_user'])) {
  $id = (int)$_POST['user_id'];
  $username = sanitize($_POST['username']);
  $name = sanitize($_POST['name']);
  $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
  $description = sanitize($_POST['description']);
  $image = sanitize($_POST['image']);
  $level = ($_POST['level'] === 'admin' || $_POST['level'] === 'user') ? $_POST['level'] : 'user';

  //Email validation
  if($email) {
    // Update dengan password baru password_hash
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
      UPDATE user 
      SET name = ?, username = ?, email = ?, image = ?, description = ?, level = ?, password = ?, updated_at = CURRENT_TIMESTAMP()

      WHERE ID = ?
   ");

   $stmt->execute([$name, $username, $email,$image, $description, $level, $password, $id]);
  }

  //Kalau admin mengubah data dirinya sendiri
  $_SESSION['user_name'] = $_SESSION['user_id'] == $id ? $username : $_SESSION['user_name'];

  header("Location: admin.php");
  exit;
}

// Ambil data user
// Konfigurasi pagination
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Handle pencarian user
$keyword_user = isset($_GET['keyword_user']) ? strtolower(trim($_GET['keyword_user'])) : "";
$hasSearchKeyword = !empty($keyword_user);

if ($hasSearchKeyword) {
    // Query pencarian dengan pagination
    $query = "SELECT * FROM user WHERE LOWER(name) LIKE :keyword OR LOWER(email) LIKE :keyword OR LOWER(username) LIKE :keyword 
    ORDER BY user.updated_at DESC
    LIMIT :limit OFFSET :offset";

    $countQuery = "SELECT COUNT(*) FROM user WHERE LOWER(name) LIKE :keyword OR LOWER(email) LIKE :keyword OR LOWER(username) LIKE :keyword";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':keyword', "%$keyword_user%");
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    $totalStmt = $pdo->prepare($countQuery);
    $totalStmt->bindValue(':keyword', "%$keyword_user%");
    $totalStmt->execute();
    $totalUsers = $totalStmt->fetchColumn();
} else {

    // Query normal dengan pagination
    $query = "SELECT * FROM user 
    ORDER BY user.updated_at DESC
    LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
}

$totalPages = ceil($totalUsers / $perPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>

  <link rel="stylesheet" href="./style/admin.css"/>

  <script>
    function confirmDelete(item, id) {
      return confirm(`Yakin ingin hapus ${item} dengan ID ${id}?`);
    }

    function confirmUpdate() {
      return confirm('Yakin ingin update data ini?');
    }
  </script>
</head>

<body>
  
  <nav>
    <!-- Link tambah data -->
    <a href="add_user.php" class="button-navbar">Tambah User</a>
    <a href="add_game.php" class="button-navbar">Tambah Game</a>

    <h1 class="admin-title">Admin Dashboard</h1>

    <p class="welcome-text">
      Selamat datang, 
      
      <span class="username">
        <?= htmlspecialchars($_SESSION['user_name']) ?>
      </span> 
    </p>
  </nav>
  

  <!-- Link Pindah Page -->
  <a href="./admin.php" class="button">Page User</a>
  <a href="./admin_game.php" class="button">Page Game</a>
  <a href="./admin_checkout.php" class="button">Page Checkout</a>
  <a  class="button-logout" href="logout.php">Logout</a>

  <br/>
  <br/>
  <br/>
  <br/>

  <!-- Tabel user -->

  <!--Search user -->
  <h1>Table User</h1>

  <h2>Cari Data User (nama, email, username)</h2>
<form action="admin.php" method="get">

    <input type="text" name="keyword_user" placeholder="Cari nama/deskripsi/username" value="<?= htmlspecialchars($keyword_user) ?>">
    <button type="submit">Search</button>
</form>


  <!-- Hasil pencarian user -->
  <?php if ($hasSearchKeyword && empty($users)): ?>
    <p>Tidak ditemukan hasil untuk "<?= htmlspecialchars($keyword_user) ?>".</p>
  <?php else: ?>
    <!-- Pagination -->

    <!-- Kalau ada halaman -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">

        <?php if ($page > 1): ?>
           <!-- Buat string query -->
            <a href="?page=<?= $page-1 ?><?= $hasSearchKeyword ? '&keyword_user='.urlencode($keyword_user) : '' ?>">Previous</a>
        <?php endif; ?>
        
        <?php 
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        
        if ($start > 1) echo '<a>...</a>';
        
        for ($i = $start; $i <= $end; $i++): ?>
            <a href="?page=<?= $i ?><?= $hasSearchKeyword ? '&keyword_user='.urlencode($keyword_user) : '' ?>" 
               <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a>
        <?php endfor; 
        
        if ($end < $totalPages) echo '<a>...</a>';
        ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?><?= $hasSearchKeyword ? '&keyword_user='.urlencode($keyword_user) : '' ?>">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Tabel user (Hanya Satu Tabel) -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Description</th>
                <th>Password</th> <!-- kolom baru -->
                <th>Level</th>
                <th>Image (URL)</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                  <?= $u['ID'] ?>
                </td>

                <form method="post" class="inline" onsubmit="return confirmUpdate()">
                  <td>
                    <input type="text" name="username" value="<?= htmlspecialchars($u['username']) ?>" required />
                  </td>

                  <td>
                    <input type="text" name="name" value="<?= htmlspecialchars($u['name']) ?>" required />
                  </td>

                  <td>
                    <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required />
                  </td>

                  <td>
                    <textarea name="description"><?= htmlspecialchars($u['description']) ?></textarea>
                  </td>

                  <td>
                    <!-- Input password kosong, jika diisi baru update -->
                    <input type="password" name="password" placeholder="Kosongkan jika tidak ingin ubah" />
                  </td>

                  <td>
                    <!-- Role -->
                    <select name="level" required>
                      <option value="admin" <?= $u['level'] === 'admin' ? 'selected' : '' ?>>admin</option>
                      <option value="user" <?= $u['level'] === 'user' ? 'selected' : '' ?>>user</option>
                    </select>
                  </td>

                  <td>
                    <input type="text" name="image" value="<?= htmlspecialchars($u['image']) ?>" />
                  </td>
                  
                  <!-- Tanggal dibuat dan di update -->
                  <td>
                    <?= $u['created_at'] ?>
                  </td>
                  <td>
                    <?= $u['updated_at'] ?>
                  </td>

                  <!-- Action -->
                  <td>
                      <input type="hidden" name="user_id" value="<?= $u['ID'] ?>" />
                      <input type="hidden" name="update_user" value="1" />
                      <button type="submit">Update</button>
                      <a href="?delete_user=<?= $u['ID'] ?>" class="button delete" onclick="return confirmDelete('user', <?= $u['ID'] ?>)">Delete</a>
                  </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>

</html>