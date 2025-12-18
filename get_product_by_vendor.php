<?php
include 'koneksi.php';

$vendor = $_GET['vendor'];

$sql = "
SELECT
  dp.ProductName,
  SUM(f.PurchaseQty) AS TotalPurchaseQty
FROM vw_enterprise_fact f
JOIN dim_product dp ON f.ProductKey = dp.ProductKey
LEFT JOIN dim_vendor dv ON f.VendorKey = dv.VendorKey
WHERE COALESCE(dv.VendorName, 'Unknown Vendor') = ?
GROUP BY dp.ProductName
ORDER BY TotalPurchaseQty DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $vendor);
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
