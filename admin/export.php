<?php
require '../includes/config.php';

// Jika export Excel ingin dinonaktifkan (karena tidak ada vendor/autoload.php), cukup komentar 3 baris berikut:
// require '../vendor/autoload.php';
// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Pilih format export
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'excel';
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$firstDay = "$year-" . str_pad($month,2,'0',STR_PAD_LEFT) . "-01";
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Ambil daftar kamar
$stmt = $pdo->query("SELECT id, location, room_number FROM rooms ORDER BY location, room_number");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data booking bulan ini
$sql = "SELECT b.*, r.location, r.room_number
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE MONTH(b.checkin_time) = ? AND YEAR(b.checkin_time) = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$month, $year]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Olah data booking jadi array: [room_id][tanggal][fullday/transit] = total
$data = [];
foreach ($bookings as $b) {
    $rid = $b['room_id'];
    $tgl = (int)date('j', strtotime($b['checkin_time']));
    $tipe = strtolower($b['duration_type']) == 'fullday' ? 'fullday' : 'transit';
    if (!isset($data[$rid][$tgl])) $data[$rid][$tgl] = ['fullday'=>0, 'transit'=>0];
    $data[$rid][$tgl][$tipe] += (float)$b['price_amount'];
}

// ===================
// Export as Excel
// ===================
if ($format === 'excel') {
    // require '../vendor/autoload.php';
    // use PhpOffice\PhpSpreadsheet\Spreadsheet;
    // use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    // Jika ingin mengaktifkan export Excel, hapus komentar di atas dan pastikan sudah install composer + phpoffice/phpspreadsheet

    // $spreadsheet = new Spreadsheet();
    // $sheet = $spreadsheet->getActiveSheet();

    // // Judul
    // $sheet->setCellValue('C2', 'Laporan Omset Bulan ' . strtoupper(date('F Y', strtotime($firstDay))));
    // $sheet->setCellValue('C3', 'Kia Serviced Apartmen');
    // $sheet->mergeCells('C2:AJ2');
    // $sheet->mergeCells('C3:AJ3');
    // $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(14);
    // $sheet->getStyle('C3')->getFont()->setBold(true)->setSize(13);

    // $sheet->setCellValue('A5', 'No');
    // $sheet->setCellValue('B5', 'Kamar');
    // $col = 3;
    // for ($d=1; $d<=$daysInMonth; $d++) {
    //     $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    //     $sheet->setCellValue($colLetter.'5', $d);
    //     $sheet->mergeCells($colLetter.'5:'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col+1).'5');
    //     $sheet->setCellValue($colLetter.'6', 'Fullday');
    //     $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col+1).'6', 'Transit');
    //     $col += 2;
    // }
    // $totalWeekCol = $col;
    // $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalWeekCol).'5', 'Total Omset Mingguan');
    // $sheet->mergeCells(
    //     \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalWeekCol).'5:'
    //     .\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalWeekCol).'11'
    // );
    // $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalWeekCol).'6', '');

    // $row = 7;
    // $no=1;
    // foreach ($rooms as $room) {
    //     $sheet->setCellValue('A'.$row, $no++);
    //     $sheet->setCellValue('B'.$row, $room['location'].' no '.$room['room_number']);
    //     $col = 3;
    //     for ($d=1; $d<=$daysInMonth; $d++) {
    //         $fd = isset($data[$room['id']][$d]['fullday']) ? $data[$room['id']][$d]['fullday'] : '';
    //         $tr = isset($data[$room['id']][$d]['transit']) ? $data[$room['id']][$d]['transit'] : '';
    //         $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col).$row, $fd);
    //         $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col+1).$row, $tr);
    //         $col += 2;
    //     }
    //     $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalWeekCol).$row, '');
    //     $row++;
    // }

    // $sheet->setCellValue('A'.$row, 'TOTAL');
    // $sheet->mergeCells('A'.$row.':B'.$row);
    // $col = 3;
    // for ($d=1; $d<=$daysInMonth; $d++) {
    //     $fdCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    //     $trCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col+1);
    //     $sheet->setCellValue($fdCol.$row, '0');
    //     $sheet->setCellValue($trCol.$row, '0');
    //     $col += 2;
    // }
    // $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalWeekCol).$row, '0');

    // $filename = "Laporan_Omset_Kamar_{$month}_{$year}.xlsx";
    // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    // header("Content-Disposition: attachment;filename=\"$filename\"");
    // header('Cache-Control: max-age=0');
    // $writer = new Xlsx($spreadsheet);
    // $writer->save('php://output');
    // exit;

    // Jika export Excel dinonaktifkan, tampilkan pesan berikut:
    echo "Fitur export Excel dinonaktifkan. Silakan gunakan export format CSV.";
    exit;
}

// ===================
// Export as CSV
// ===================
if ($format === 'csv') {
    $filename = "Laporan_Omset_Kamar_{$month}_{$year}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $output = fopen('php://output', 'w');

    // Judul (pakai baris kosong, lalu dua baris judul)
    fputcsv($output, []);
    fputcsv($output, ['','', 'Laporan Omset Bulan '.strtoupper(date('F Y', strtotime($firstDay)))]);
    fputcsv($output, ['','', 'Kia Serviced Apartmen']);
    fputcsv($output, []);

    // Header tanggal
    $header1 = ['No', 'Kamar'];
    for ($d=1; $d<=$daysInMonth; $d++) {
        $header1[] = $d;
        $header1[] = '';
    }
    $header1[] = 'Total Omset Mingguan';
    fputcsv($output, $header1);

    $header2 = ['', ''];
    for ($d=1; $d<=$daysInMonth; $d++) {
        $header2[] = 'Fullday';
        $header2[] = 'Transit';
    }
    $header2[] = '';
    fputcsv($output, $header2);

    // Data kamar
    $no=1;
    foreach ($rooms as $room) {
        $rowdata = [$no++, $room['location'].' no '.$room['room_number']];
        for ($d=1; $d<=$daysInMonth; $d++) {
            $fd = isset($data[$room['id']][$d]['fullday']) ? $data[$room['id']][$d]['fullday'] : '';
            $tr = isset($data[$room['id']][$d]['transit']) ? $data[$room['id']][$d]['transit'] : '';
            $rowdata[] = $fd;
            $rowdata[] = $tr;
        }
        $rowdata[] = '';
        fputcsv($output, $rowdata);
    }
    // Total bawah
    $rowdata = ['TOTAL','',''];
    for ($d=1; $d<=$daysInMonth; $d++) {
        $rowdata[] = '0';
        $rowdata[] = '0';
    }
    $rowdata[] = '0';
    fputcsv($output, $rowdata);

    fclose($output);
    exit;
}

// Jika format tidak dikenali
die('Format tidak dikenali.');