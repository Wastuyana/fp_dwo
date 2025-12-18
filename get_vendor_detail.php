<?php
include 'koneksi.php';

$vendor = $_GET['vendor'];

$query = "
SELECT
    dv.VendorName,
    dp.ProductName,
    SUM(f.PurchaseQty) AS PurchaseQty,
    SUM(f.OrderQty) AS OrderQty
FROM vw_enterprise_fact f
JOIN dim_vendor dv ON f.VendorKey = dv.VendorKey
JOIN dim_product dp ON f.ProductKey = dp.ProductKey
WHERE dv.VendorName = ?
GROUP BY
    dv.VendorName,
    dp.ProductName
ORDER BY PurchaseQty DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $vendor);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo '<table class="table table-bordered table-sm">';
echo '<thead>
<tr>
    <th>Vendor</th>
    <th>Product</th>
    <th>Purchase Qty</th>
    <th>Order Qty</th>
</tr>
</thead><tbody>';

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
        <td>{$row['VendorName']}</td>
        <td>{$row['ProductName']}</td>
        <td>{$row['PurchaseQty']}</td>
        <td>{$row['OrderQty']}</td>
    </tr>";
}

echo '</tbody></table>';
