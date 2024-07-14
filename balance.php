<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//require_once 'globals.php'; 



function getBalance($address,$network) {
//$CurrentNetwork=$network;

// echo $network;


if ($network=="Bitcoin") {
    
    //echo "Bitcoin:".$address;

    $url = "https://api.blockcypher.com/v1/btc/main/addrs/$address";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['balance'])) {
        $balance = $data['balance'] / 100000000; // Convert from satoshis to BTC
       // echo $balance;
        return $balance;
    } else {
        echo 'bitcoin Error: '  ;
        //return null;
        return 0;
    }
}else{


/*
$rpcUrl="";

if ($network=="Ethereum") {
    $rpcUrl="https://eth.drpc.org";
}else if ($network=="BinanceBNB") {
     $rpcUrl="https://bsc-dataseed1.binance.org:443";
}*/


$Networks = array(
    "Ethereum" => "https://eth.drpc.org",
    "BinanceBNB" => "https://bsc-dataseed1.binance.org:443",
    "PolygonMatic" => "https://polygon.llamarpc.com",
    "AvalancheAvax" => "https://api.avax.network/ext/bc/C/rpc",
    "CronosCRO" => "https://evm.cronos.org",
    "FantomFTM" => "https://rpc.ankr.com/fantom"   

);

$rpcUrl = $Networks[$network];



// Create a JSON-RPC request to get the balance of the address
$request = json_encode([
    'jsonrpc' => '2.0',
    'id' => 1,
    'method' => 'eth_getBalance',
    'params' => [
        $address,
        'latest'  // You can use 'latest' to get the latest balance
    ]
]);

// Initialize cURL session
$ch = curl_init($rpcUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($request)
]);

// Execute the cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
    exit;
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$responseData = json_decode($response, true);

// Check if the response is valid
if (isset($responseData['result'])) {
    // The balance is returned in Wei, you can convert it to MATIC or other units as needed
    $balanceWei = hexdec($responseData['result']);
    $balance = $balanceWei / 1e18; // Convert Wei to MATIC

   // echo "Balance of $address : $balance<br>";
    return $balance;

} else {
    echo "Error: Unable to fetch balance<br>";
    return 0;
}




}






}


?>