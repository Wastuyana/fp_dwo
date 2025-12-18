<!DOCTYPE html>
<?php
include 'koneksi.php';

/* VENDOR vs PURCHASE QTY */
$q_vendor = "
SELECT
  dv.VendorName,
  SUM(f.PurchaseQty) AS TotalPurchaseQty
FROM vw_enterprise_fact f
JOIN dim_vendor dv 
  ON f.VendorKey = dv.VendorKey
GROUP BY dv.VendorName
HAVING SUM(f.PurchaseQty) IS NOT NULL
   AND SUM(f.PurchaseQty) <> 0
ORDER BY TotalPurchaseQty DESC
";

$r_vendor = mysqli_query($conn, $q_vendor);

$vendor = [];
$vendorQty = [];

while ($row = mysqli_fetch_assoc($r_vendor)) {
    $vendor[] = $row['VendorName'];
    $vendorQty[] = (int)$row['TotalPurchaseQty'];
}

$maxVendor = max($vendorQty);

$q_product = "
SELECT
  dp.ProductName,
  SUM(f.OrderQty) AS TotalOrderQty
FROM fact_sales f
JOIN dim_product dp
  ON f.ProductKey = dp.ProductKey
GROUP BY dp.ProductName
HAVING SUM(f.OrderQty) > 0
ORDER BY TotalOrderQty DESC;
";

$r_product = mysqli_query($conn, $q_product);

$products = [];
$productQty = [];

while ($row = mysqli_fetch_assoc($r_product)) {
    $products[] = $row['ProductName'];
    $productQty[] = (int) $row['TotalOrderQty'];
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
                    <h1 class="h3 m-4 text-gray-800">Product & Procurement</h1>

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


                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card mb-4">
                                <div class="row">
                                    <div class="col-lg-12 mb-4">
                                        <div class="card shadow">
                                            <div class="card-header font-weight-bold text-primary">
                                                Purchase Quantity by Product
                                            </div>
                                            <div class="card-body">
                                                <div id="categoryChart"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow">
                                <div class="card-header font-weight-bold text-primary">
                                    Detail Procurement (Cross Filter)
                                </div>
                                <div class="card-body">
                                    <div id="vendorDetail">
                                        <em>Klik vendor untuk melihat detail</em>
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
            chart: {
                type: 'column'
            },
            title: {
                text: null
            },

            xAxis: {
                categories: <?= json_encode($vendor) ?>,
                title: {
                    text: 'Vendor'
                }
            },

            yAxis: {
                title: {
                    text: 'Total Purchase Quantity'
                },
                max: <?= $maxVendor ?>
            },

            plotOptions: {
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function() {
                                loadVendorProduct(this.category);
                            }
                        }
                    }
                }
            },

            series: [{
                name: 'Purchase Qty',
                data: <?= json_encode($vendorQty) ?>
            }]
        });

        let currentVendor = '';

        function loadVendorProduct(vendor) {

            currentVendor = vendor;

            /* DRILLDOWN CHART */
            $.getJSON(
                'get_product_by_vendor.php', {
                    vendor: vendor
                },
                function(data) {

                    Highcharts.chart('vendorChart', {
                        chart: {
                            type: 'bar'
                        },
                        title: {
                            text: 'Products from Vendor: ' + vendor
                        },

                        xAxis: {
                            type: 'category',
                            title: {
                                text: 'Product'
                            }
                        },

                        yAxis: {
                            title: {
                                text: 'Total Purchase Quantity'
                            }
                        },

                        series: [{
                            name: 'Purchase Qty',
                            data: data
                        }]
                    });

                }
            );

            /* ✅ CROSS FILTER DETAIL TABLE */
            $('#vendorDetail').load(
                'get_vendor_detail.php?vendor=' + encodeURIComponent(vendor)
            );
        }

        /*  Product */
        Highcharts.chart('categoryChart', {
            chart: {
                type: 'column'
            },
            title: {
                text: null
            },

            xAxis: {
                categories: <?= json_encode($products) ?>,
                title: {
                    text: 'Product'
                }
            },

            yAxis: {
                title: {
                    text: 'Total Order Quantity'
                },
                max: 1400
            },

            tooltip: {
                pointFormat: '<b>{point.y}</b> unit'
            },

            series: [{
                name: 'Total Order Quantity',
                data: <?= json_encode($productQty) ?>
            }]
        });
    </script>

</body>

</html>