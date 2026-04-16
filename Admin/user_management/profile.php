<?php include("../config.php"); ?>

<h3>Profile</h3>

<div>
    <a href="all_users.php">All Users</a> |
    <a href="add_user.php">Add New</a> |
    <a href="profile.php">Profile</a>
</div>

<br>

<?php
// ✅ kiểm tra id hợp lệ
if (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $id = intval($_GET['id']);

    $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
    $user = mysqli_fetch_assoc($result);

    // ✅ kiểm tra user tồn tại
    if ($user) {
?>

        <p><b>Name:</b> <?= $user['name'] ?></p>
        <p><b>Email:</b> <?= $user['email'] ?></p>
        <p><b>Role:</b> <?= $user['role'] ?></p>

<?php
    } else {
        echo "<p style='color:red'>User không tồn tại!</p>";
    }

} else {
    echo "<p>Hãy chọn user từ All Users</p>";
}
?>