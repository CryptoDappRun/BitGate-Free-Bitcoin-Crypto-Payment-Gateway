<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$data = $_GET['address'] ?? '';

require_once('phpqrcode/qrlib.php');

// Start output buffering
ob_start();

// Generate QR code and output to buffer
QRcode::png($data, null, QR_ECLEVEL_L, 10);

// Get the buffered image data
$imageData = ob_get_contents();

// Clear and close the buffer
ob_end_clean();

// Set content type header
header("Content-Type: image/png");

// Output the image data
echo $imageData;

?>

