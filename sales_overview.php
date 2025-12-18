<!DOCTYPE html>
<?php
include 'koneksi.php';

/* TREND SALES */
$q_trend = "
SELECT
  dt.MonthName,
  SUM(fs.SalesAmount) AS TotalSales
FROM fact_sales fs
JOIN dim_time dt ON fs.TimeKey = dt.TimeKey
GROUP BY dt.Month, dt.MonthName
ORDER BY dt.Month
";
$r_trend = mysqli_query($conn, $q_trend);

$bulan = [];
$totalSales = [];

while ($row = mysqli_fetch_assoc($r_trend)) {
    $bulan[] = $row['MonthName'];
    $totalSales[] = (float) $row['TotalSales'];
}

/* CUSTOMER TYPE */
$q_customer = "
SELECT
  dc.CustomerType,
  COUNT(fs.SalesKey) AS TotalTransaksi
FROM fact_sales fs
JOIN dim_customer dc
  ON fs.CustomerKey = dc.CustomerKey
GROUP BY dc.CustomerType
HAVING COUNT(fs.SalesKey) > 0
ORDER BY TotalTransaksi DESC
";

$r_customer = mysqli_query($conn, $q_customer);

$custType = [];
$transaksi = [];

while ($row = mysqli_fetch_assoc($r_customer)) {
    $custType[] = $row['CustomerType'];
    $transaksi[] = (int) $row['TotalTransaksi'];
}

/* CHANNEL */
$q_channel = "
SELECT
  ch.ChannelName,
  COUNT(DISTINCT fs.SalesKey) AS TotalTransactions
FROM fact_sales fs
JOIN dim_channel ch
  ON fs.ChannelKey = ch.ChannelKey
JOIN dim_customer dc
  ON fs.CustomerKey = dc.CustomerKey
JOIN dim_time dt
  ON fs.TimeKey = dt.TimeKey
WHERE dc.IsNewCustomer = 1
  AND dt.Quarter = 1
GROUP BY ch.ChannelName
HAVING COUNT(DISTINCT fs.SalesKey) > 0
ORDER BY TotalTransactions DESC;

";
$r_channel = mysqli_query($conn, $q_channel);

$channelData = [];

while ($row = mysqli_fetch_assoc($r_channel)) {
    $channelData[] = [
        'name' => $row['ChannelName'],
        'y' => (int) $row['TotalTransactions']
    ];
}
    
?>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Sales Overview</title>

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

                <!-- Page Heading -->
                <h1 class="h3 m-4 text-gray-800">Sales Overview</h1>

                <!-- ROW 1 -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header font-weight-bold text-primary">
                                Sales Trend
                            </div>
                            <div class="card-body">
                                <div id="trendSales"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 2 -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header font-weight-bold text-primary">
                                Customer Type vs Transaction
                            </div>
                            <div class="card-body">
                                <div id="customerChart"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header font-weight-bold text-primary">
                                Total Transactions by Channel (New Customer - Q1)
                            </div>
                            <div class="card-body">
                                <div id="channelChart"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto text-center">
                <span>Sales Overview Dashboard © 2024</span>
            </div>
        </footer>

    </div>
</div>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.3/js/sb-admin-2.min.js"></script>

<script>
/* Line Chart - Sales Trend */
Highcharts.chart('trendSales', {
    chart: { type: 'line' },
    title: { text: null },
    xAxis: {
        categories: <?= json_encode($bulan) ?>
    },
    yAxis: {
        title: { text: 'Total Sales' }
    },
    series: [{
        name: 'Sales Amount',
        data: <?= json_encode($totalSales) ?>
    }]
});

/* Bar Chart - Customer Type */
Highcharts.chart('customerChart', {
    chart: { type: 'column' },
    title: { text: null },
    xAxis: {
        categories: <?= json_encode($custType) ?>
    },
    yAxis: {
        title: { text: 'Jumlah Transaksi' }
    },
    series: [{
        name: 'Transaksi',
        data: <?= json_encode($transaksi) ?>
    }]
});

/* Pie Chart - Sales Channel */
Highcharts.chart('channelChart', {
    chart: { type: 'pie' },
    title: { text: null },
    series: [{
    name: 'Total Transactions',
    colorByPoint: true,
    data: <?= json_encode($channelData) ?>
}]
});
</script>

</body>
</html>
