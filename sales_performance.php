<!DOCTYPE html>
<?php
include 'koneksi.php';

/* Sales Person Performance */

$query = "
SELECT
    de.SalesPersonName,
    COUNT(fs.SalesKey) AS TotalTransactions
FROM fact_sales fs
JOIN dim_employee de ON fs.EmployeeKey = de.EmployeeKey
WHERE de.SalesPersonName IS NOT NULL
  AND de.SalesPersonName NOT IN ('Unknown', '0')
GROUP BY de.SalesPersonName
HAVING COUNT(fs.SalesKey) > 0
ORDER BY TotalTransactions DESC
";

$result = mysqli_query($conn, $query);

$salesPerson = [];
$totalTrans  = [];

while ($row = mysqli_fetch_assoc($result)) {
    $salesPerson[] = $row['SalesPersonName'];
    $totalTrans[]  = (int) $row['TotalTransactions'];
}
?>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

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
            <div class="container-fluid mt-4">
                <h3 class="text-gray-800 m-4">
                    Sales Performance
                </h3>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div id="salesChart"></div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header font-weight-bold text-primary">
                        Detail Transaksi (Cross Filter)
                    </div>
                    <div class="card-body">
                        <div id="detailTable">
                            <em>Klik sales person untuk melihat detail</em>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.3/js/sb-admin-2.min.js"></script>

    <script>
        Highcharts.chart('salesChart', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Total Transactions per Sales Person'
            },

            xAxis: {
                categories: <?= json_encode($salesPerson) ?>,
                title: {
                    text: 'Sales Person'
                }
            },

            yAxis: {
                title: {
                    text: 'Total Transactions'
                },
                allowDecimals: false
            },

            plotOptions: {
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function() {
                                loadProduct(this.category);
                            }
                        }
                    }
                }
            },

            series: [{
                name: 'Total Transactions',
                data: <?= json_encode($totalTrans) ?>
            }]
        });


        /* DRILLDOWN : Sales Person → Product */
        function loadProduct(salesPerson) {

            // DRILLDOWN CHART
            fetch('get_product_by_salesperson.php?salesPerson=' + encodeURIComponent(salesPerson))
                .then(res => res.json())
                .then(data => {

                    Highcharts.chart('salesChart', {
                        chart: {
                            type: 'bar'
                        },
                        title: {
                            text: 'Produk yang Dijual oleh ' + salesPerson
                        },
                        xAxis: {
                            type: 'category',
                            title: {
                                text: 'Product'
                            }
                        },
                        yAxis: {
                            title: {
                                text: 'Total Transactions'
                            },
                            allowDecimals: false
                        },
                        series: [{
                            name: 'Total Transactions',
                            data: data
                        }]
                    });

                });

            // ✅ CROSS FILTERING TABLE
            $('#detailTable').load(
                'get_salesperson_detail.php?salesPerson=' + encodeURIComponent(salesPerson)
            );
        }
    </script>

</body>

</html>