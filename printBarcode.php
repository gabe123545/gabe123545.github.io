<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

$id = $input["id"] ?? null;
$action = $input["action"] ?? null;

if (!$id || !$action) {
    echo json_encode([
        "success" => false,
        "error" => "Missing body. Required: { id: number, action: 'takeout'|'return' }"
    ]);
    exit;
}

$allowed = ["takeout", "return"];
if (!in_array($action, $allowed)) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid action, allowed: 'takeout' | 'return'"
    ]);
    exit;
}

// Same logic as your Node.js file
$prefix = $action === "return" ? "RKTM" : "KTM";
$barcodeValue = $prefix . $id;

// Printer defaults
$PRINTER_NAME  = getenv("BARCODE_PRINTER_NAME") ?: "ZDesigner ZD411-203dpi ZPL";
$PYTHON_CMD    = getenv("PYTHON_CMD") ?: "python3";

// Path to Python script (same folder as this PHP file)
$pythonScript = __DIR__ . "/send_zebra_usb.py";

// Escape shell arguments
$escPrinter = escapeshellarg($PRINTER_NAME);
$escBarcode = escapeshellarg($barcodeValue);
$escScript  = escapeshellarg($pythonScript);

// Build the command
$cmd = "$PYTHON_CMD $escScript $escPrinter $escBarcode 2>&1";

// Run the Python script
$output = shell_exec($cmd);

// Handle errors
if ($output === null) {
    echo json_encode([
        "success" => false,
        "error" => "Failed to execute Python script"
    ]);
    exit;
}

// Return success to JS
echo json_encode([
    "success" => true,
    "message" => "Barcode printed successfully.",
    "printerOutput" => trim($output)
]);

?>