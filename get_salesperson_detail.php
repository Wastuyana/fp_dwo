<?php
include 'koneksi.php';

$salesPerson = $_GET['salesPerson'];

$query = "
SELECT
    dp.ProductName,
    COUNT(fs.SalesKey) AS TotalTransactions
FROM fact_sales fs
JOIN dim_employee de ON fs.EmployeeKey = de.EmployeeKey
JOIN dim_product dp ON fs.ProductKey = dp.ProductKey
WHERE de.SalesPersonName = ?
GROUP BY dp.ProductName
ORDER BY TotalTransactions DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $salesPerson);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo '<table class="table table-bordered">';
echo '<thead><tr><th>Product</th><th>Total Transactions</th></tr></thead><tbody>';

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['ProductName']}</td>
            <td>{$row['TotalTransactions']}</td>
          </tr>";
}

echo '</tbody></table>';
