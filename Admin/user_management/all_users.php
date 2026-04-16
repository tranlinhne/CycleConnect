<?php include("../config.php"); ?>

<h2>Users</h2>

<br>

<!-- SEARCH -->
<form method="GET" style="margin-bottom:15px;">
    <input type="hidden" name="page" value="users">

    <input type="text" name="keyword"
        placeholder="Tìm kiếm username hoặc email..."
        value="<?= $_GET['keyword'] ?? '' ?>"
        style="padding:8px; width:250px; border-radius:6px; border:1px solid #ccc;">

    <button style="padding:8px 12px;">Search</button>
</form>

<?php
// search
$keyword = $_GET['keyword'] ?? '';

// phân trang
$limit = 5;
$page_num = $_GET['p'] ?? 1;
$offset = ($page_num - 1) * $limit;

// query
$where = "";
if (!empty($keyword)) {
    $where = "WHERE name LIKE '%$keyword%' OR email LIKE '%$keyword%'";
}

// tổng số user
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users $where");
$total_row = mysqli_fetch_assoc($total_query);
$total_users = $total_row['total'];

$total_pages = ceil($total_users / $limit);

// lấy data
$result = mysqli_query($conn,
    "SELECT * FROM users $where LIMIT $limit OFFSET $offset"
);
?>

<style>
table {
    width: 100%;
    background: white;
    border-radius: 10px;
    border-collapse: collapse;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,.05);
}

th {
    background: #f1f2ff;
    text-align: left;
}

th, td {
    padding: 12px;
}

tr:hover {
    background: #f9f9ff;
}

.btn {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 12px;
    text-decoration: none;
    color: white;
}

.btn-profile { background: #6c5ce7; }
.btn-delete { background: #e74c3c; }

.pagination a {
    padding: 6px 10px;
    margin: 2px;
    background: #eee;
    border-radius: 5px;
    text-decoration: none;
}

.pagination a.active {
    background: #6c5ce7;
    color: white;
}
</style>

<table>
    <tr>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Action</th>
    </tr>

    <?php while($u = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?= $u['name'] ?></td>
        <td><?= $u['email'] ?></td>
        <td><?= $u['role'] ?></td>
        <td>
            <a href="index.php?page=profile&id=<?= $u['id'] ?>"
               class="btn btn-profile">Profile</a>

            <a href="index.php?page=users&delete=<?= $u['id'] ?>"
               class="btn btn-delete"
               onclick="return confirm('Xóa user?')">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>

<br>

<!-- PAGINATION -->
<div class="pagination">
<?php for ($i = 1; $i <= $total_pages; $i++) { ?>
    <a href="index.php?page=users&p=<?= $i ?>&keyword=<?= $keyword ?>"
       class="<?= ($i == $page_num) ? 'active' : '' ?>">
       <?= $i ?>
    </a>
<?php } ?>
</div>