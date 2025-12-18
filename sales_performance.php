<!DOCTYPE html>
<?php
include 'koneksi.php';

/* SALES PERFORMANCE */

$query = "
SELECT
    de.SalesPersonName,
    SUM(fs.SalesAmount) AS TotalSales,
    de.BonusAmount,
    de.CurrentPayRate
FROM fact_sales fs
JOIN dim_employee de
    ON fs.EmployeeKey = de.EmployeeKey
GROUP BY
    de.EmployeeKey,
    de.SalesPersonName,
    de.BonusAmount,
    de.CurrentPayRate
ORDER BY TotalSales DESC
";

$result = mysqli_query($conn, $query);

$salesPerson = [];
$salesAmount = [];
$tableData   = [];

while ($row = mysqli_fetch_assoc($result)) {
    $salesPerson[] = $row['SalesPersonName'];
    $salesAmount[] = (float) $row['TotalSales'];
    $tableData[]   = $row;
}
?>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Sales Performance</title>

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
                <h1 class="h3 mb-4 text-gray-800">Sales Performance</h1>

                <!-- FILTER (dummy dulu) -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="month" class="form-control">
                    </div>
                </div>

                <!-- ROW 1 : SalesPerson vs SalesAmount -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header font-weight-bold text-primary">
                                Sales Amount per Sales Person
                            </div>
                            <div class="card-body">
                                <div id="salesChart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 2 : Table Bonus & PayRate -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header font-weight-bold text-primary">
                                Bonus & Pay Rate
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Sales Person</th>
                                                <th>Sales Amount</th>
                                                <th>Bonus</th>
                                                <th>Pay Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tableData as $row): ?>
                                                <tr>
                                                    <td><?= $row['SalesPersonName']; ?></td>
                                                    <td><?= number_format($row['TotalSales'], 2); ?></td>
                                                    <td><?= number_format($row['BonusAmount'], 2); ?></td>
                                                    <td><?= number_format($row['CurrentPayRate'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto text-center">
                <span>Sales Performance Dashboard © 2024</span>
            </div>
        </footer>
    </div>
</div>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.3/js/sb-admin-2.min.js"></script>

<script>
Highcharts.chart('salesChart', {
    chart: { type: 'column' },
    title: { text: 'Sales Performance by Sales Person' },
    xAxis: {
        categories: <?= json_encode($salesPerson) ?>,
        title: { text: 'Sales Person' }
    },
    yAxis: {
        title: { text: 'Sales Amount' }
    },
    series: [{
        name: 'Total Sales',
        data: <?= json_encode($salesAmount) ?>
    }]
});
</script>

</body>
</html>
