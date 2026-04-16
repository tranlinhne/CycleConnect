<?php include("../config.php"); ?>

<h3>Add User</h3>

<div>
    <a href="all_users.php">All Users</a> |
    <a href="add_user.php">Add New</a> |
    <a href="profile.php">Profile</a>
</div>

<br>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_input = $_POST['password'];
    $role = $_POST['role'];

    // ✅ validate
    if (empty($name) || empty($email) || empty($password_input)) {
        echo "<p style='color:red'>Vui lòng nhập đầy đủ thông tin!</p>";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color:red'>Email không hợp lệ!</p>";
    }
    else {

        // ✅ kiểm tra email trùng
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            echo "<p style='color:red'>Email đã tồn tại!</p>";
        } else {

            $password = password_hash($password_input, PASSWORD_DEFAULT);

            mysqli_query($conn, "
                INSERT INTO users (name, email, password, role, active)
                VALUES ('$name', '$email', '$password', '$role', 1)
            ");

            echo "<p style='color:green'>Thêm user thành công!</p>";
        }
    }
}
?>

<form method="POST">
    <input type="text" name="name" placeholder="Name" required><br><br>

    <input type="email" name="email" placeholder="Email" required><br><br>

    <input type="password" name="password" placeholder="Password" required><br><br>

    <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select><br><br>

    <button>Thêm user</button>
</form>