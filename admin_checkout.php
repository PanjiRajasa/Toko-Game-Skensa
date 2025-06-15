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

//Fungsi helper untuk format tanggal
function formatDateTime($datetime) {
  return $datetime ? date('d M Y H:i', strtotime($datetime)) : '-';
}

// Semua user
$allUsers = $pdo->query("SELECT ID, name FROM user")->fetchAll();

$allGames = $pdo->query("SELECT ID, name FROM game")->fetchAll();

// HANDLE DELETE checkout
if (isset($_GET['delete_checkout'])) { 
    //ambil ID
    $id = (int)$_GET['delete_checkout'];

    //query delete
    $stmt = $pdo->prepare("DELETE FROM checkout WHERE ID = ?");
    $stmt->execute([$id]);

    header("Location: admin_checkout.php");
    exit;
}

 // HANDLE UPDATE checkout
if (isset($_POST['update_checkout'])) { 
    $id = (int)$_POST['checkout_id'];
    $price = (int)$_POST['price'];
    $date_checkout = $_POST['date_checkout'];
    $user_id = (int)$_POST['user_id'];
    $game_ID = (int)$_POST['game_ID'];

    $stmt = $pdo->prepare("
    UPDATE checkout 
    SET price = ?, date_checkout = ?, updated_at = CURRENT_TIMESTAMP(), user_id = ?, game_ID = ?
    WHERE ID = ?
  ");
    $stmt->execute([$price, $date_checkout, $user_id, $game_ID, $id]);

    header("Location: admin_checkout.php");
    exit;
 }

 // Ambil data checkout
 // Konfigurasi pagination
  $perPage = 10;
  $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
  $offset = ($page - 1) * $perPage;

  $keyword_checkout = isset($_GET['keyword_checkout']) ? strtolower(trim($_GET['keyword_checkout'])) : "";
  $hasSearchKeyword = !empty($keyword_checkout);

  if ($hasSearchKeyword) {
    // Query pencarian dengan pagination

    //query asli
    $query = "SELECT 
            checkout.ID, 
            checkout.price, 
            checkout.date_checkout, 
            checkout.updated_at,
            user.ID as user_id, 
            user.name as user_name,
            game.ID as game_id,
            game.name as game_name
          FROM checkout 
          INNER JOIN user ON checkout.user_id = user.ID
          INNER JOIN game ON checkout.game_ID = game.ID
          WHERE LOWER(user.name) LIKE :keyword 
             OR LOWER(user.email) LIKE :keyword  
             OR LOWER(game.name) LIKE :keyword 
          ORDER BY checkout.updated_at DESC

          LIMIT :limit OFFSET :offset";

    //query count total
    $countQuery = "SELECT COUNT(*) FROM checkout
    INNER JOIN user ON checkout.user_id = user.ID
    INNER JOIN game ON checkout.game_ID = game.ID

    WHERE LOWER(user.name) LIKE :keyword 
    OR LOWER(user.email) LIKE :keyword  
    OR LOWER(game.name) LIKE :keyword";
    
    //execute query with pdo
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':keyword', "%$keyword_checkout%");

    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);

    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $checkouts = $stmt->fetchAll();
    
    $totalStmt = $pdo->prepare($countQuery);
    $totalStmt->bindValue(':keyword', "%$keyword_checkout%");
    $totalStmt->execute();
    $totalCheckout = $totalStmt->fetchColumn();
} else {
  // Query normal dengan pagination
  
  $query = "SELECT 
              checkout.ID, 
              checkout.price, 
              checkout.date_checkout, 
              checkout.updated_at,
              user.ID as user_id, 
              user.name as user_name,
              game.ID as game_id,
              game.name as game_name
              FROM checkout 
              INNER JOIN user ON checkout.user_id = user.ID
              INNER JOIN game ON checkout.game_ID = game.ID 
            ORDER BY checkout.updated_at DESC

            LIMIT :limit OFFSET :offset";

  //execute the query
  $stmt = $pdo->prepare($query);
  $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $checkouts = $stmt->fetchAll();

  //total checkout
  $totalCheckout = $pdo->query("SELECT COUNT(*) FROM checkout")->fetchColumn();
}
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


    <!-- Tabel Checkout -->

    <!-- Search Checkout -->
    <h1>Table Checkout</h1>

    <h2>Cari Data Checkout (nama user, email user, gmail user)</h2>
    <form action="admin_checkout.php" method="get">
      <input type="text" name="keyword_checkout" placeholder="Cari nama/deskripsi" value="<?= htmlspecialchars($keyword_checkout) ?>">
      <button type="submit">Search</button>
    </form>


    <!-- Hasil pencarian game -->
    <?php if ($hasSearchKeyword && empty($checkouts)): ?>
      <p>Tidak ditemukan hasil untuk "<?= htmlspecialchars($keyword_checkout) ?>".</p>
    <?php else: ?>
      <!-- Pagination -->
      <?php if ($totalCheckout > 1): ?>
      
      <?php
        // Hitung total halaman
        $totalPages = ceil($totalCheckout / $perPage);

        // Tampilkan pagination hanya jika ada lebih dari 1 halaman
        if ($totalPages > 1): 
      ?>

      <!-- Tampilkan pagination -->
      <div class="pagination">
          <?php if ($page > 1): ?>
              <a href="?page=<?= $page-1 ?><?= $hasSearchKeyword ? '&keyword_checkout='.urlencode($keyword_checkout) : '' ?>">Previous</a>
          <?php endif; ?>
          
          <?php 
          $start = max(1, $page - 2);
          $end = min($totalPages, $page + 2);
          
          if ($start > 1) echo '<a>...</a>';
          
          // Tampilkan ... jika halaman awal tidak dimulai dari 1
    if ($start > 1): ?>
        <a href="?page=1<?= $hasSearchKeyword ? '&keyword_checkout='.urlencode($keyword_checkout) : '' ?>">1</a>
        <?php if ($start > 2): ?>
            <span>...</span>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="?page=<?= $i ?><?= $hasSearchKeyword ? '&keyword_checkout='.urlencode($keyword_checkout) : '' ?>" 
            <?= $i == $page ? 'class="active"' : '' ?>>
            <?= $i ?>
        </a>
    <?php endfor; ?>
    
    <?php if ($end < $totalPages): ?>
            <?php if ($end < $totalPages - 1): ?>
                <span>...</span>
            <?php endif; ?>
            <a href="?page=<?= $totalPages ?><?= $hasSearchKeyword ? '&keyword_checkout='.urlencode($keyword_checkout) : '' ?>">
                <?= $totalPages ?>
            </a>
        <?php endif; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?><?= $hasSearchKeyword ? '&keyword_checkout='.urlencode($keyword_checkout) : '' ?>">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Tabel Checkout (Hanya Satu Tabel) -->

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Price</th>
          <th>Date Checkout</th>
          <th>Updated At</th>
          <th>User</th>
          <th>Game</th>
          <th>Actions</th>
      </thead>
      <tbody>
        <?php foreach ($checkouts as $c): ?>

          <?php
            //for debugging

            // echo"<pre>";
            // echo print_r($checkouts);
            // echo"</pre>";
          ?>

          <tr>
              <td><?= $c['ID'] ?></td>
              <form method="post" class="inline" onsubmit="return confirmUpdate()">


              <td>
                <input type="number" name="price" value="<?= (int)$c['price'] ?>" required />
              </td>

              <!-- Tanggal checkout dan update -->
              <td>              
                <?= formatDateTime($c['date_checkout']) ?>
              </td>
              
              <td>
                <?= date('d M Y H:i', strtotime($c['updated_at'])) ?>
              </td>

              <td>
                <select name="user_id" required>
                  <option value="">-- Select User --</option>
                  <?php 
                    foreach ($allUsers as $user): 
                  ?>
                    <option value="<?= $user['ID'] ?>" <?= $user['ID'] == $c['user_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($user['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>


              <td>
                <select name="game_ID" required>
                  <option value="">-- Select Game --</option>

                  <?php
                    foreach ($allGames as $game):
                  ?>

                    <option value="<?= $game['ID'] ?>" <?= $game['ID'] == $c['game_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($game['name']) ?>
                    </option>

                  <?php endforeach; ?>
                </select>
              </td>
                

              <td>
                  <input type="hidden" name="checkout_id" value="<?= $c['ID'] ?>" />
                  <input type="hidden" name="update_checkout" value="1" />
                  <button type="submit">Update</button>

                  <!-- Untuk delete -->
                  <a href="?delete_checkout=<?= $c['ID'] ?>" 

                  class="button delete" 

                  onclick="return confirmDelete('checkout', <?= $c['ID'] ?>)">Delete</a>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
<?php endif; ?>

</body>

</html>