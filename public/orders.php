<?php
header('Content-Type: text/html; charset=utf-8');

require_once '../_database.php';
$headersArray = array('ID', 'Created', 'Updated', 'Payment Status', 'Description', 'Amount');
$dateFrom = date('Y-m-01'); // First day of the current month
$dateTo = date('Y-m-d'); // Current date

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateData($from, $to) {
    $db = new Database();
    // $records = $db->getRecords(100, 1, $fromDate, $toDate, $columnName, $columnSortOrder);
    $records = $db->getRecords($from, $to);
    return $records;
}

// Sample Data Generation Function
function generateSampleData() {
    $sampleData = [];
    for ($i = 1; $i <= 100; $i++) { // Generating more data for pagination and sorting demonstration
        $sampleData[] = [
            'ID' => $i,
            'Created' => date('Y-m-d', strtotime("-".mt_rand(1, 60)." days")),
            'Updated' => date('Y-m-d', strtotime("-".mt_rand(1, 60)." days")),
            'Payment Status' => mt_rand(0, 1) ? 'Completed' : 'Pending',
            'Description' => 'Sample Item ' . $i,
        ];
    }
    return $sampleData;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $dateFromInput = isset($_POST['dateFrom']) ? sanitizeInput($_POST['dateFrom']) : '';
    $dateToInput = isset($_POST['dateTo']) ? sanitizeInput($_POST['dateTo']) : '';

    // Validate dates (example: check if they match the format 'Y-m-d')
    $dateFromValid = DateTime::createFromFormat('Y-m-d', $dateFromInput) && $dateFromInput === date('Y-m-d', strtotime($dateFromInput));
    $dateToValid = DateTime::createFromFormat('Y-m-d', $dateToInput) && $dateToInput === date('Y-m-d', strtotime($dateToInput));

    if ($dateFromValid && $dateToValid) {
        $dateFrom = $dateFromInput;
        $dateTo = $dateToInput;
    } else {
        // Handle invalid input
        //echo "<div class='alert alert-danger' role='alert'>Invalid date format.</div>";
    }

    if (isset($_POST['show'])) {
        // Handle the "Show" button submission
    } elseif (isset($_POST['getCsv'])) {
        // CSV Export Functionality
        $filename = "export_data_" . date('YmdHis') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        $output = fopen('php://output', 'w');
        // Output UTF-8 BOM for Excel compatibility with special characters
        // fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headersArray);

        foreach (generateData($dateFrom, $dateTo) as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced PHP Web Application</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Data Table with Advanced Features</h2>
    <form action="" method="POST" class="form-inline">
        <div class="form-group mb-2">
            <label for="dateFrom" class="sr-only">Date From:</label>
            <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>">
        </div>
        <div class="form-group mx-sm-3 mb-2">
            <label for="dateTo" class="sr-only">Date To:</label>
            <input type="date" class="form-control" id="dateTo" name="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>">
        </div>
        <button type="submit" name="show" class="btn btn-primary mb-2">Show</button>
        <button type="submit" name="getCsv" class="btn btn-secondary mb-2 ml-2">Get CSV</button>
    </form>
    <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <?php
                foreach($headersArray as $header) {
                    echo "<th>$header</th>";
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $records = generateData($dateFrom, $dateTo);
            // $records = $db->getRecords(100, 1);
            foreach ($records as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>".htmlspecialchars($cell)."</td>";
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#example').DataTable({
        "pagingType": "full_numbers", // Enables full pagination
        "lengthMenu": [[10, 20, 50, 100, 250], [10, 20, 50, 100, 250]], // Custom length menu
        "order": [[1, 'desc']],
    });
});
</script>
</body>
</html>

