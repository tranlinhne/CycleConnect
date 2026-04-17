<?php include("../config.php"); ?>

<?php
// search
$keyword = $_GET['keyword'] ?? '';
$where = "";

if ($keyword != "") {
    $where = "WHERE name LIKE '%$keyword%' OR email LIKE '%$keyword%'";
}

// lấy dữ liệu
$result = mysqli_query($conn, "SELECT * FROM users $where");
?>

<style>
.box {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0,0,0,.05);
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.search-box input {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #ddd;
    width: 200px;
}

.search-box button {
    padding: 8px 12px;
    border: none;
    background: #6c5ce7;
    color: white;
    border-radius: 8px;
    cursor: pointer;
}

.add-btn {
    padding: 8px 12px;
    background: #00b894;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    margin-left: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    color: #888;
    font-size: 13px;
    padding: 10px;
    border-bottom: 1px solid #eee;
}

td {
    padding: 12px;
    border-bottom: 1px solid #f1f1f1;
}

tr:hover {
    background: #f9f9ff;
}

.badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}

.badge-user {
    background: #e3f2fd;
    color: #2196f3;
}

.badge-admin {
    background: #ffe0e0;
    color: #e74c3c;
}

.btn {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 12px;
    text-decoration: none;
    margin-right: 5px;
}

.btn-view {
    background: #6c5ce7;
    color: white;
}

.btn-edit {
    background: #00b894;
    color: white;
}

.btn-delete {
    background: #e74c3c;
    color: white;
}
</style>

<div class="box">

<div class="top-bar">
    <h3>Quản lý người dùng</h3>

    <div>
        <form method="GET" class="search-box" style="display:inline;">
            <input type="hidden" name="page" value="users">
            <input type="text" name="keyword" placeholder="Search..."
                value="<?= $_GET['keyword'] ?? '' ?>">
            <button>Tìm</button>
        </form>

        <a href="index.php?page=add_user" class="add-btn">
             Add User
        </a>
    </div>
</div>

<table>
<tr>
    <th>ID</th>
    <th>Username</th>
    <th>Email</th>
    <th>Role</th>
    <th>Action</th>
</tr>

<?php while($u = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?= $u['id'] ?></td>
    <td><?= $u['name'] ?></td>
    <td><?= $u['email'] ?></td>
    <td>
        <?php if($u['role'] == 'admin') { ?>
            <span class="badge badge-admin">Admin</span>
        <?php } else { ?>
            <span class="badge badge-user">User</span>
        <?php } ?>
    </td>
    <td>
        <a href="index.php?page=profile&id=<?= $u['id'] ?>"
           class="btn btn-view">View</a>

        <a href="index.php?page=edit_user&id=<?= $u['id'] ?>"
            class="btn btn-edit">Edit</a>

        <a href="index.php?page=users&delete=<?= $u['id'] ?>"
           class="btn btn-delete"
           onclick="return confirm('Xóa user?')">Delete</a>
    </td>
</tr>
<?php } ?>

</table>

</div>