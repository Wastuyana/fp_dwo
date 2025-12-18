<!DOCTYPE html>
<?php
include 'koneksi.php';

/* VENDOR vs PURCHASE QTY */
$q_vendor = "
SELECT
  dv.VendorName,
  SUM(fp.PurchaseQty) AS TotalPurchase
FROM fact_procurement fp
JOIN dim_vendor dv ON fp.VendorKey = dv.VendorKey
GROUP BY dv.VendorName
ORDER BY TotalPurchase DESC
LIMIT 10
";
$r_vendor = mysqli_query($conn, $q_vendor);

$vendor = [];
$purchaseQty = [];

while ($row = mysqli_fetch_assoc($r_vendor)) {
    $vendor[] = $row['VendorName'];
    $purchaseQty[] = (int) $row['TotalPurchase'];
}

/* PRODUK TERBANYAK DIBELI */
$q_product = "
SELECT
  dp.ProductName,
  SUM(fp.PurchaseQty) AS TotalPurchase
FROM fact_procurement fp
JOIN dim_product dp ON fp.ProductKey = dp.ProductKey
GROUP BY dp.ProductName
ORDER BY TotalPurchase DESC
LIMIT 10
";
$r_product = mysqli_query($conn, $q_product);

/* PRODUCT */
$q_category = "
SELECT
  dp.CategoryName,
  COUNT(dp.ProductKey) AS TotalProduct
FROM dim_product dp
GROUP BY dp.CategoryName
";

$res_category = mysqli_query($conn, $q_category);

$categories = [];
$categoryData = [];

while ($row = mysqli_fetch_assoc($res_category)) {
    $categories[] = $row['CategoryName'];
    $categoryData[] = (int) $row['TotalProduct'];
}
?>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Product & Procurement</title>

    <!-- Fonts & Template -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.3/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
</head>

<body id="page-top">

<div id="wrapper">

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Product & Procurement</h1>

                <!-- ROW 1 : Vendor vs Purchase -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header font-weight-bold text-primary">
                                Vendor vs Purchase Quantity
                            </div>
                            <div class="card-body">
                                <div id="vendorChart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 2 : Table -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                Top 10 Produk dengan Purchase Quantity Tertinggi
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                    <th>No</th>
                                    <th>Nama Produk</th>
                                    <th>Total Purchase Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    <?php while ($row = mysqli_fetch_assoc($r_product)) : ?>
                                    <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $row['ProductName'] ?></td>
                                    <td><?= number_format($row['TotalPurchase']) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 3 : Drill Down -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header font-weight-bold text-primary">
                                Product Distribution by Category
                            </div>
                            <div class="card-body">
                                <div id="categoryChart"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto text-center">
                <span>Product & Procurement Dashboard © 2024</span>
            </div>
        </footer>
    </div>
</div>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.3/js/sb-admin-2.min.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>

<script>
/* Vendor vs Purchase Qty */
Highcharts.chart('vendorChart', {
    chart: { type: 'column' },
    title: { text: null },
    xAxis: {
        categories: <?= json_encode($vendor) ?>,
        title: { text: 'Vendor' }
    },
    yAxis: {
        title: { text: 'Total Purchase Qty' }
    },
    series: [{
        name: 'Purchase Qty',
        data: <?= json_encode($purchaseQty) ?>
    }]
});

/* Category → Product */
Highcharts.chart('categoryChart', {
    chart: { type: 'bar' },
    title: { text: 'Product Distribution by Category' },
    xAxis: {
        categories: <?= json_encode($categories) ?>,
        title: { text: 'Category' }
    },
    yAxis: {
        title: { text: 'Number of Products' }
    },
    series: [{
        name: 'Products',
        data: <?= json_encode($categoryData) ?>
    }]
});
</script>

</body>
</html>
