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


// HANDLE DELETE GAME
if (isset($_GET['delete_game'])) {
  //ambil ID
  $id = (int)$_GET['delete_game'];
  
  //query delete
  $stmt = $pdo->prepare("DELETE FROM game WHERE ID = ?");
  $stmt->execute([$id]);

  header("Location: admin_game.php");
  exit;
}


// HANDLE UPDATE GAME
if (isset($_POST['update_game'])) {
  $id = (int)$_POST['game_id'];
  $name = sanitize($_POST['name']);
  $price = (int)$_POST['price'];
  $image = sanitize($_POST['image']);
  $simple_description = sanitize($_POST['simple_description']);
  $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;

  $stmt = $pdo->prepare("
    UPDATE game 
    SET name = ?, price = ?, image = ?, simple_description = ?, rating = ?, updated_at = CURRENT_TIMESTAMP()
    WHERE ID = ?
  ");
  $stmt->execute([$name, $price, $image, $simple_description, $rating, $id]);

  header("Location: admin_game.php");
  exit;
}

// Ambil data game
// Konfigurasi pagination
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Handle pencarian game
$keyword_game = isset($_GET['keyword_game']) ? strtolower(trim($_GET['keyword_game'])) : "";
$hasSearchKeyword = !empty($keyword_game);

if ($hasSearchKeyword) {
    // Query pencarian dengan pagination

    //query asli
    $query = "SELECT * FROM game WHERE LOWER(name) LIKE :keyword OR LOWER(simple_description) LIKE :keyword
    ORDER BY game.updated_at DESC
    LIMIT :limit OFFSET :offset";

    //query menghitung
    $countQuery = "SELECT COUNT(*) FROM game WHERE LOWER(name) LIKE :keyword OR LOWER(simple_description) LIKE :keyword";
    
    //execute query with pdo
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':keyword', "%$keyword_game%");

    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);

    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $games = $stmt->fetchAll();
    
    $totalStmt = $pdo->prepare($countQuery);
    $totalStmt->bindValue(':keyword', "%$keyword_game%");
    $totalStmt->execute();
    $totalGames = $totalStmt->fetchColumn();
} else {
    // Query normal dengan pagination
    $query = "SELECT * FROM game 
    ORDER BY game.updated_at DESC
    LIMIT :limit OFFSET :offset";


    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $games = $stmt->fetchAll();
    
    $totalGames = $pdo->query("SELECT COUNT(*) FROM game")->fetchColumn();
}

$totalPages = ceil($totalGames / $perPage);
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
  <a href="./admin/admin.php" class="button">Page User</a>
  <a href="./admin_game.php" class="button">Page Game</a>
  <a href="./admin_checkout.php" class="button">Page Checkout</a>
  <a  class="button-logout" href="logout.php">Logout</a>


  <br/>
  <br/>
  <br/>
  <br/>

  <!-- Tabel Game -->

  <!--Search Game -->
  <h1>Table Game</h1>

  <h2>Cari Data Game (nama, deskripsi)</h2>
  <form action="admin_game.php" method="get">
      <input type="text" name="keyword_game" placeholder="Cari nama/deskripsi" value="<?= htmlspecialchars($keyword_game) ?>">
      <button type="submit">Search</button>
  </form>


  <!-- Hasil pencarian game -->
  <?php if ($hasSearchKeyword && empty($games)): ?>
    <p>Tidak ditemukan hasil untuk "<?= htmlspecialchars($keyword_game) ?>".</p>
<?php else: ?>
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div class="pagination">
          <?php if ($page > 1): ?>
              <a href="?page=<?= $page-1 ?><?= $hasSearchKeyword ? '&keyword_game='.urlencode($keyword_game) : '' ?>">Previous</a>
          <?php endif; ?>
          
          <?php 
          $start = max(1, $page - 2);
          $end = min($totalPages, $page + 2);
          
          if ($start > 1) echo '<a>...</a>';
          
          for ($i = $start; $i <= $end; $i++): ?>
              <a href="?page=<?= $i ?><?= $hasSearchKeyword ? '&keyword_game='.urlencode($keyword_game) : '' ?>" 
                <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a>
          <?php endfor; 
          
          if ($end < $totalPages) echo '<a>...</a>';
          ?>
          
          <?php if ($page < $totalPages): ?>
              <a href="?page=<?= $page+1 ?><?= $hasSearchKeyword ? '&keyword_game='.urlencode($keyword_game) : '' ?>">Next</a>
          <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Tabel Game (Hanya Satu Tabel) -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Image (URL)</th>
                <th>Description</th>
                <th>Rating</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($games as $g): ?>
            <tr>
                <td><?= $g['ID'] ?></td>
                <form method="post" class="inline" onsubmit="return confirmUpdate()">
                    <td><input type="text" name="name" value="<?= htmlspecialchars($g['name']) ?>" required /></td>
                    <td><input type="number" name="price" value="<?= (int)$g['price'] ?>" required /></td>
                    <td><input type="text" name="image" value="<?= htmlspecialchars($g['image']) ?>" /></td>
                    <td><textarea name="simple_description"><?= htmlspecialchars($g['simple_description']) ?></textarea></td>
                    <td><input type="number" name="rating" value="<?= (int)$g['rating'] ?>" min="0" /></td>

                    <!-- Tanggal dibuat dan diupdate -->
                    <td><?= $g['created_at'] ?></td>
                    <td><?= $g['updated_at'] ?></td>
                    
                    <!-- Actions -->
                    <td>
                        <input type="hidden" name="game_id" value="<?= $g['ID'] ?>" />
                        <input type="hidden" name="update_game" value="1" />
                        <button type="submit">Update</button>

                        <!-- Untuk delete -->
                        <a href="?delete_game=<?= $g['ID'] ?>"
                        
                        class="button delete" 
                        onclick="return confirmDelete('game', <?= $g['ID'] ?>)">Delete</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>

</html>