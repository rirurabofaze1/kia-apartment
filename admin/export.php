<?php
require_once "../includes/config.php";

// Security: Check user permissions
if (!hasPermission(["admin", "superuser", "cashier"])) {
    header("Location: ../index.php");
    exit;
}

// Get export parameters
$export_type = $_GET["export"] ?? "";
$view = $_GET["view"] ?? "bookings";
$period = $_GET["period"] ?? "this_month";
$room_id = $_GET["room_id"] ?? "";
$search = $_GET["search"] ?? "";
$start_date = $_GET["start_date"] ?? "";
$end_date = $_GET["end_date"] ?? "";

// Validate export type
if (!in_array($export_type, ["csv", "pdf"])) {
    header("Location: history.php");
    exit;
}

// Build the same filters as in history.php
$date_filter = "";
$params = [];

if ($start_date && $end_date) {
    $date_filter = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} else {
    switch ($period) {
        case "today":
            $date_filter = "DATE(created_at) = CURDATE()";
            break;
        case "yesterday":
            $date_filter = "DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case "this_week":
            $date_filter = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case "last_week":
            $date_filter = "YEARWEEK(created_at, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 1)";
            break;
        case "this_month":
            $date_filter = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
            break;
        case "last_month":
            $date_filter = "MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
            break;
        case "this_year":
            $date_filter = "YEAR(created_at) = YEAR(CURDATE())";
            break;
        case "all":
            $date_filter = "1=1";
            break;
    }
}

if ($room_id) {
    $date_filter .= " AND room_id = ?";
    $params[] = $room_id;
}

$search_filter = "";
if ($search) {
    $search_filter = " AND (guest_name LIKE ? OR phone_number LIKE ? OR r.room_number LIKE ? OR r.location LIKE ?)";
    $search_param = "%" . $search . "%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

// Get data based on view
try {
    if ($view == "bookings") {
        $sql = "SELECT b.*, r.room_number, r.location, r.room_type, u.full_name as created_by_name
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                JOIN users u ON b.created_by = u.id
                WHERE $date_filter $search_filter
                ORDER BY b.created_at DESC";
    } elseif ($view == "transactions") {
        $trans_date_filter = str_replace("created_at", "t.transaction_date", $date_filter);
        $sql = "SELECT t.*, b.guest_name, r.room_number, r.location, u.full_name as created_by_name
                FROM transactions t
                JOIN bookings b ON t.booking_id = b.id
                JOIN rooms r ON b.room_id = r.id
                JOIN users u ON t.created_by = u.id
                WHERE $trans_date_filter $search_filter
                ORDER BY t.transaction_date DESC";
    } elseif ($view == "cancellations") {
        $sql = "SELECT b.*, r.room_number, r.location, r.room_type, u.full_name as created_by_name
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                JOIN users u ON b.created_by = u.id
                WHERE b.status IN ('cancelled', 'no_show') AND $date_filter $search_filter
                ORDER BY b.updated_at DESC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Export error: " . $e->getMessage());
    header("Location: history.php?error=export_failed");
    exit;
}

if ($export_type == "csv") {
    // CSV Export
    $filename = "kia_apartment_" . $view . "_" . date("Y-m-d_H-i-s") . ".csv";
    
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    
    $output = fopen("php://output", "w");
    
    // CSV Headers
    if ($view == "bookings") {
        fputcsv($output, ["ID", "Room", "Guest Name", "Phone", "Arrival", "Duration (hours)", "Amount", "Status", "Created By", "Date"]);
        
        foreach ($data as $record) {
            fputcsv($output, [
                $record["id"],
                $record["location"] . " - " . $record["room_number"],
                $record["guest_name"],
                $record["phone_number"],
                formatDateTime($record["arrival_time"]),
                $record["duration_hours"],
                $record["price_amount"] + $record["extra_time_amount"],
                ucfirst($record["status"]),
                $record["created_by_name"],
                formatDateTime($record["created_at"])
            ]);
        }
    } elseif ($view == "transactions") {
        fputcsv($output, ["ID", "Date", "Type", "Guest", "Room", "Payment Method", "Amount", "Created By"]);
        
        foreach ($data as $record) {
            fputcsv($output, [
                $record["id"],
                formatDateTime($record["transaction_date"]),
                ucfirst(str_replace("_", " ", $record["transaction_type"])),
                $record["guest_name"],
                $record["location"] . " - " . $record["room_number"],
                ucfirst($record["payment_method"]),
                $record["amount"],
                $record["created_by_name"]
            ]);
        }
    } elseif ($view == "cancellations") {
        fputcsv($output, ["ID", "Room", "Guest Name", "Phone", "Arrival", "Amount", "Status", "Reason", "Date"]);
        
        foreach ($data as $record) {
            fputcsv($output, [
                $record["id"],
                $record["location"] . " - " . $record["room_number"],
                $record["guest_name"],
                $record["phone_number"],
                formatDateTime($record["arrival_time"]),
                $record["price_amount"],
                ucfirst(str_replace("_", " ", $record["status"])),
                $record["notes"] ?: "-",
                formatDateTime($record["updated_at"])
            ]);
        }
    }
    
    fclose($output);
    exit;
    
} elseif ($export_type == "pdf") {
    // Simple HTML to PDF conversion (basic implementation)
    $filename = "kia_apartment_" . $view . "_" . date("Y-m-d_H-i-s") . ".html";
    
    header("Content-Type: text/html");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    echo "<!DOCTYPE html>";
    echo "<html><head>";
    echo "<title>KIA Apartment - " . ucfirst($view) . " Report</title>";
    echo "<style>";
    echo "body { font-family: Arial, sans-serif; margin: 20px; }";
    echo "table { width: 100%; border-collapse: collapse; margin-top: 20px; }";
    echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
    echo "th { background-color: #f2f2f2; font-weight: bold; }";
    echo "tr:nth-child(even) { background-color: #f9f9f9; }";
    echo ".header { text-align: center; margin-bottom: 30px; }";
    echo ".report-info { margin-bottom: 20px; }";
    echo "</style>";
    echo "</head><body>";
    
    echo "<div class='header'>";
    echo "<h1>KIA SERVICED APARTMENT</h1>";
    echo "<h2>" . ucfirst($view) . " Report</h2>";
    echo "<p>Generated on: " . date("d/m/Y H:i:s") . "</p>";
    echo "</div>";
    
    echo "<div class='report-info'>";
    echo "<p><strong>Period:</strong> " . ucfirst(str_replace("_", " ", $period)) . "</p>";
    if ($room_id) {
        echo "<p><strong>Room Filter:</strong> Applied</p>";
    }
    if ($search) {
        echo "<p><strong>Search:</strong> " . htmlspecialchars($search) . "</p>";
    }
    echo "<p><strong>Total Records:</strong> " . count($data) . "</p>";
    echo "</div>";
    
    echo "<table>";
    
    // Table headers
    if ($view == "bookings") {
        echo "<tr><th>ID</th><th>Room</th><th>Guest Name</th><th>Phone</th><th>Arrival</th><th>Duration</th><th>Amount</th><th>Status</th><th>Created By</th><th>Date</th></tr>";
        
        foreach ($data as $record) {
            echo "<tr>";
            echo "<td>" . $record["id"] . "</td>";
            echo "<td>" . htmlspecialchars($record["location"] . " - " . $record["room_number"]) . "</td>";
            echo "<td>" . htmlspecialchars($record["guest_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($record["phone_number"]) . "</td>";
            echo "<td>" . formatDateTime($record["arrival_time"]) . "</td>";
            echo "<td>" . $record["duration_hours"] . " hours</td>";
            echo "<td>" . formatCurrency($record["price_amount"] + $record["extra_time_amount"]) . "</td>";
            echo "<td>" . ucfirst($record["status"]) . "</td>";
            echo "<td>" . htmlspecialchars($record["created_by_name"]) . "</td>";
            echo "<td>" . formatDateTime($record["created_at"]) . "</td>";
            echo "</tr>";
        }
    } elseif ($view == "transactions") {
        echo "<tr><th>ID</th><th>Date</th><th>Type</th><th>Guest</th><th>Room</th><th>Payment Method</th><th>Amount</th><th>Created By</th></tr>";
        
        foreach ($data as $record) {
            echo "<tr>";
            echo "<td>" . $record["id"] . "</td>";
            echo "<td>" . formatDateTime($record["transaction_date"]) . "</td>";
            echo "<td>" . ucfirst(str_replace("_", " ", $record["transaction_type"])) . "</td>";
            echo "<td>" . htmlspecialchars($record["guest_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($record["location"] . " - " . $record["room_number"]) . "</td>";
            echo "<td>" . ucfirst($record["payment_method"]) . "</td>";
            echo "<td>" . formatCurrency($record["amount"]) . "</td>";
            echo "<td>" . htmlspecialchars($record["created_by_name"]) . "</td>";
            echo "</tr>";
        }
    } elseif ($view == "cancellations") {
        echo "<tr><th>ID</th><th>Room</th><th>Guest Name</th><th>Phone</th><th>Arrival</th><th>Amount</th><th>Status</th><th>Reason</th><th>Date</th></tr>";
        
        foreach ($data as $record) {
            echo "<tr>";
            echo "<td>" . $record["id"] . "</td>";
            echo "<td>" . htmlspecialchars($record["location"] . " - " . $record["room_number"]) . "</td>";
            echo "<td>" . htmlspecialchars($record["guest_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($record["phone_number"]) . "</td>";
            echo "<td>" . formatDateTime($record["arrival_time"]) . "</td>";
            echo "<td>" . formatCurrency($record["price_amount"]) . "</td>";
            echo "<td>" . ucfirst(str_replace("_", " ", $record["status"])) . "</td>";
            echo "<td>" . htmlspecialchars($record["notes"] ?: "-") . "</td>";
            echo "<td>" . formatDateTime($record["updated_at"]) . "</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    echo "</body></html>";
    exit;
}
?>
