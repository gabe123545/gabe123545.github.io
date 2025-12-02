<?php
// printBarcode.php

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['zpl'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing ZPL data']);
    exit;
}

$printerIp = '192.168.1.42'; // Your ZD411 IP
$printerPort = 9100;         // Zebra raw TCP port
$zpl = $data['zpl'];

// Connect to printer
$fp = @fsockopen($printerIp, $printerPort, $errno, $errstr, 5);
if (!$fp) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => "Connection failed: $errstr ($errno)"]);
    exit;
}

// Send ZPL
fwrite($fp, $zpl);
fclose($fp);

echo json_encode(['success' => true]);
?>
