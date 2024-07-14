<?php

require 'encryption.php';
require_once 'globals.php';

session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.html');
    exit;
}

// Your SQLite database setup
$db = new SQLite3($GLOBALS['DatabaseName']);

// Pagination settings
$itemsPerPage = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $itemsPerPage;

// Check if the status filter is applied
$statusFilter = isset($_GET['status']) && $_GET['status'] === 'complete' ? "WHERE status = 'complete'" : "";

// Fetch data from the database with pagination, status filter, and sorting by expiration_time in descending order
$queryData = "SELECT * FROM payments $statusFilter ORDER BY expiration_time DESC LIMIT $itemsPerPage OFFSET $offset";
$resultData = $db->query($queryData);

// Fetch total number of items from the table with status filter
$queryCount = "SELECT COUNT(*) AS total FROM payments $statusFilter";
$resultCount = $db->querySingle($queryCount);

$totalItems = $resultCount; // Total number of items
$totalPages = ceil($totalItems / $itemsPerPage);

// Display data in a table with Bootstrap styles
?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Display</title>
    <!-- Add Bootstrap CSS link -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="m-3">
        <h1>Data Display</h1>
        <!-- Button to list all records with status 'complete' -->
        <form method="get" action="">
            <button type="submit" name="status" value="complete" class="btn btn-primary mb-3">List Completed Payments</button>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Crypto_Address</th>
                    <th>Crypto_Private</th>
                    <th>ItemType</th>
                    <th>Key</th>
                    <th>Email</th>
                    <th>Amount</th>
                    <th>PayWith</th>
                    <th>CreateTime</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultData->fetchArray(SQLITE3_ASSOC)):
                    $decryptedKey = decryptString($row['key']);
                    $decryptedPrivate = decryptString($row['bitcoin_Private']);
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['bitcoin_address']; ?></td>
                        <td><?php echo $decryptedPrivate; ?></td>
                        <td><?php echo $row['ItemType']; ?></td>
                        <td><?php echo $decryptedKey; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['amount']; ?></td>
                        <td><?php echo $row['PayWith']; ?></td>
                        <td><?php echo $row['expiration_time']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination links -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="data.php?page=<?php echo $i; ?>&status=<?php echo isset($_GET['status']) ? $_GET['status'] : ''; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- Add Bootstrap JS and jQuery scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
