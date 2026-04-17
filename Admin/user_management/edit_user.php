<?php include("../config.php"); ?>

<?php
$id = $_GET['id'] ?? 0;

// lấy user
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
$user = mysqli_fetch_assoc($result);

// xử lý update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    mysqli_query($conn, "
        UPDATE users 
        SET name='$name', email='$email', role='$role'
        WHERE id=$id
    ");

    echo "<p style='color:green'>Cập nhật thành công!</p>";

    // load lại dữ liệu
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
    $user = mysqli_fetch_assoc($result);
}
?>

<style>
.box {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0,0,0,.05);
    max-width: 500px;
}

input, select {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

button {
    background: #00b894;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
</style>

<div class="box">

<h3>Chỉnh sửa user</h3>

<form method="POST">

    <label>Username</label>
    <input type="text" name="name"
        value="<?= $user['name'] ?>" required>

    <label>Email</label>
    <input type="email" name="email"
        value="<?= $user['email'] ?>" required>

    <label>Role</label>
    <select name="role">
        <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
    </select>

    <button>Cập nhật</button>

</form>

<br>

<a href="index.php?page=users">← Quay lại</a>

</div>