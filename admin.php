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

// HANDLE DELETE USER
if (isset($_GET['delete_user'])) {
  $id = (int)$_GET['delete_user'];
  $stmt = $pdo->prepare("DELETE FROM user WHERE ID = ?");
  $stmt->execute([$id]);
  header("Location: admin.php");
  exit;
}

// HANDLE DELETE GAME
if (isset($_GET['delete_game'])) {
  $id = (int)$_GET['delete_game'];
  $stmt = $pdo->prepare("DELETE FROM game WHERE ID = ?");
  $stmt->execute([$id]);
  header("Location: admin.php");
  exit;
}

// HANDLE DELETE CHECKOUT
if (isset($_GET['delete_checkout'])) {
  $id = (int)$_GET['delete_checkout'];
  $stmt = $pdo->prepare("DELETE FROM checkout WHERE ID = ?");
  $stmt->execute([$id]);
  header("Location: admin.php");
  exit;
}

//HANDLE UPDATE USER
if (isset($_POST['update_user'])) {
    $id = (int)$_POST['user_id'];
    $name = sanitize($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $description = sanitize($_POST['description']);
    $image = sanitize($_POST['image']);
    $level = ($_POST['level'] === 'admin' || $_POST['level'] === 'user') ? $_POST['level'] : 'user';

    if ($email) {
        // Cek apakah password diisi
        if (!empty($_POST['password'])) {
            // Update dengan password baru (md5)
            $password = md5($_POST['password']);
            $stmt = $pdo->prepare("UPDATE user SET name = ?, email = ?, description = ?, image = ?, level = ?, password = ? WHERE ID = ?");
            $stmt->execute([$name, $email, $description, $image, $level, $password, $id]);
        } else {
            // Update tanpa mengubah password
            $stmt = $pdo->prepare("UPDATE user SET name = ?, email = ?, description = ?, image = ?, level = ? WHERE ID = ?");
            $stmt->execute([$name, $email, $description, $image, $level, $id]);
        }
    }
    header("Location: admin.php");
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

  header("Location: admin.php");
  exit;
}



// HANDLE UPDATE CHECKOUT
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

  header("Location: admin.php");
  exit;
}



// Ambil data user
$users = $pdo->query("SELECT * FROM user")->fetchAll(PDO::FETCH_ASSOC);

// Ambil data game
$games = $pdo->query("SELECT * FROM game")->fetchAll(PDO::FETCH_ASSOC);

// Ambil data checkout dengan join user dan game untuk nama user dan nama game
$checkouts = $pdo->query("
    SELECT c.*, u.name as user_name, g.name as game_name 
    FROM checkout c
    LEFT JOIN user u ON c.user_id = u.ID
    LEFT JOIN game g ON c.game_ID = g.ID
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="./dashboarAdmin.css"/>
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

  <h1>Admin Dashboard</h1>

  <!-- Link tambah data -->
  <a href="add_user.php" class="button">Tambah User</a>
  <a href="add_game.php" class="button">Tambah Game</a>

  <p>Selamat datang, <b><?= htmlspecialchars($_SESSION['user_name']) ?></b> | <a  class="button-logout" href="logout.php">Logout</a></p>

  <!-- Tabel Users -->
    <h2>Users</h2>
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
            <td><?= $u['ID'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td>
              <form method="post" class="inline" onsubmit="return confirmUpdate()">
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
              <select name="level" required>
                <option value="admin" <?= $u['level'] === 'admin' ? 'selected' : '' ?>>admin</option>
                <option value="user" <?= $u['level'] === 'user' ? 'selected' : '' ?>>user</option>
              </select>
            </td>
            <td>
              <input type="text" name="image" value="<?= htmlspecialchars($u['image']) ?>" />
            </td>
            <td><?= $u['created_at'] ?></td>
            <td><?= $u['updated_at'] ?></td>
            <td>
              <input type="hidden" name="user_id" value="<?= $u['ID'] ?>" />
              <input type="hidden" name="update_user" value="1" />
              <button type="submit">Update</button>
              </form>
              <a href="?delete_user=<?= $u['ID'] ?>" class="button delete" onclick="return confirmDelete('user', <?= $u['ID'] ?>)">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>





      <!-- Tabel Games -->
    <h2>Games</h2>
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
              <td><?= $g['created_at'] ?></td>
              <td><?= $g['updated_at'] ?></td>
              <td>
                <input type="hidden" name="game_id" value="<?= $g['ID'] ?>" />
                <input type="hidden" name="update_game" value="1" />
                <button type="submit">Update</button>
                <a href="?delete_game=<?= $g['ID'] ?>" class="button delete" onclick="return confirmDelete('game', <?= $g['ID'] ?>)">Delete</a>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>


    <!-- Tabel Checkouts -->
    <h2>Checkouts</h2>
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
        </tr>
      </thead>
      <tbody>
        <?php
        $allUsers = $pdo->query("SELECT ID, name FROM user")->fetchAll(PDO::FETCH_ASSOC);
        $allGames = $pdo->query("SELECT ID, name FROM game")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($checkouts as $c):
          $date_checkout_val = date('Y-m-d\TH:i', strtotime($c['date_checkout']));
          $updated_at_val = date('Y-m-d H:i:s', strtotime($c['updated_at']));
        ?>
          <tr>
            <td><?= $c['ID'] ?></td>
            <form method="post" class="inline" onsubmit="return confirmUpdate()">
              <td><input type="number" name="price" value="<?= (int)$c['price'] ?>" required /></td>
              <td><input type="datetime-local" name="date_checkout" value="<?= $date_checkout_val ?>" required /></td>
              <td><?= $updated_at_val ?></td>
              <td>
                <select name="user_id" required>
                  <option value="">-- Select User --</option>
                  <?php foreach ($allUsers as $user): ?>
                    <option value="<?= $user['ID'] ?>" <?= $user['ID'] == $c['user_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($user['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <select name="game_ID" required>
                  <option value="">-- Select Game --</option>
                  <?php foreach ($allGames as $game): ?>
                    <option value="<?= $game['ID'] ?>" <?= $game['ID'] == $c['game_ID'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($game['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <input type="hidden" name="checkout_id" value="<?= $c['ID'] ?>" />
                <input type="hidden" name="update_checkout" value="1" />
                <button type="submit">Update</button>
                <a href="?delete_checkout=<?= $c['ID'] ?>" class="button delete" onclick="return confirmDelete('checkout', <?= $c['ID'] ?>)">Delete</a>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</body>

</html>