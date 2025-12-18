<?php
include 'koneksi.php';

$category = $_GET['category'];

$sql = "
SELECT
  dp.ProductName,
  SUM(f.PurchaseQty) AS TotalPurchaseQty
FROM vw_enterprise_fact f
JOIN dim_product dp ON f.ProductKey = dp.ProductKey
WHERE dp.CategoryName = ?
GROUP BY dp.ProductName
ORDER BY TotalPurchaseQty DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'name' => $row['ProductName'],
        'y' => (int)$row['TotalPurchaseQty']
    ];
}

echo json_encode($data);
