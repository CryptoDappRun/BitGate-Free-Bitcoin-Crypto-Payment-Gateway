<?php
function encryptString($data, $key="DKFLSLKEWIO123", $cipher = 'aes-256-cbc') {
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encryptedData = openssl_encrypt($data, $cipher, $key, 0, $iv);
    $encryptedDataWithIV = $iv . $encryptedData;
    return base64_encode($encryptedDataWithIV);
}

function decryptString($encryptedData, $key="DKFLSLKEWIO123", $cipher = 'aes-256-cbc') {
    $encryptedDataWithIV = base64_decode($encryptedData);
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = substr($encryptedDataWithIV, 0, $ivLength);
    $encryptedData = substr($encryptedDataWithIV, $ivLength);
    return openssl_decrypt($encryptedData, $cipher, $key, 0, $iv);
}


?>
