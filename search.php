<!DOCTYPE html>
<html>
<head>
    <title>Search Records</title>
    <!-- Include Bootstrap CSS link here -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {

            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1300px;
        }
    </style>
    <script>
        function checkStatus(bitcoinAddress, ItemType,PayWith) {
            // Implement your logic here to check the status using the provided parameters
            // You can use AJAX to make a request to the server or perform any other necessary action
            console.log("Checking status for Bitcoin Address:", bitcoinAddress);
            console.log("Item Type:", ItemType);
            console.log("PayWith:", PayWith);
            // You can also submit a form to post the data
            document.getElementById("bitcoinAddress").value = bitcoinAddress;
            document.getElementById("ItemType").value = ItemType;
            document.getElementById("PayWith").value = PayWith;
            document.getElementById("statusForm").submit();
        }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">

            <li class="nav-item">
                    <a class="nav-link" href="/">Home</a>
                </li>

            <li class="nav-item">
                <a class="nav-link" href="search.php">Order</a>
            </li>

        </ul>
    </div>
</nav>


<div class="container">
    <!-- ... Search form and PHP result code ... -->
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Search Records</h1>
        </div>
        <div class="card-body">
            <form action="search.php" method="GET">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require 'encryption.php';
    require_once 'globals.php'; 



//////******////////
//$ItemType =$PurchaseItem[0];
//////******////////


//$PayAmount = $Payments["Bitcoin"];


//echo $ItemType;
//echo $PayAmount;



    if ($_SERVER["REQUEST_METHOD"] == "GET"  && isset($_GET['bitcoin_address'])&& isset($_GET['ItemType'])&& isset($_GET['PayWith'])&& isset($_GET['emailPost'])   ) {
        $Address = filter_var(trim($_GET['bitcoin_address']), FILTER_SANITIZE_STRING);   
        $ItemType = filter_var(trim($_GET['ItemType']), FILTER_SANITIZE_STRING);
        $PayWith = filter_var(trim($_GET['PayWith']), FILTER_SANITIZE_STRING);
        $emailPost = filter_var(trim($_GET['emailPost']), FILTER_SANITIZE_STRING);



        require_once 'balance.php';

        $balance=  getBalance($Address,$PayWith);


        if ($balance >= $Payments[$PayWith]) {
      //if ($balance >= 0) {
            updatePaymentStatus($Address,"complete",$ItemType,$PayWith);
            GoToSearch($emailPost);
    # code...
        }else
        {

            echo '<br><br><div class="card"><div class="card-body">';
            echo $Address . "<br>Balance:<h3 class='text-danger'>"  .$balance   ."</h3> <br>Not detect payment,if you have transfered funds to our address,Please try again in a few minutes.";

            echo '</div></div>';
            



        }



    }


// 

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $Address = $_POST['bitcoinAddress'];
        $ItemType = $_POST['ItemType'];
        $PayWith = $_POST['PayWith'];
   // getBitcoinAddressBalancePHP($bitcoinAddress,$ItemType,$PayWith);



//require_once 'balance.php';

        $balance=  getBalance($Address,$PayWith);

//echo  "BBB:".$balance ;
        if ($balance >= $Payments[$PayWith]) {
      //  if ($balance >= 0) {
            updatePaymentStatus($Address,"complete",$ItemType,$PayWith);
    # code...
        }else
        {
            echo "Balance:".$balance   ." Not detect payment,if you have transfered funds to our address,Please try again in a few minutes.";
        }

    }






/*
function getBalance($address,$network) {
//$CurrentNetwork=$network;

// echo $network;


if ($network=="Bitcoin") {
    
    echo "Bitcoin:".$address;

    $url = "https://api.blockcypher.com/v1/btc/main/addrs/$address";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['balance'])) {
        $balance = $data['balance'] / 100000000; // Convert from satoshis to BTC
        echo $balance;
        return $balance;
    } else {
        echo 'bitcoin Error: '  ;
        //return null;
        return 0;
    }
}else{



$rpcUrl="";

if ($network=="Ethereum") {
    $rpcUrl="https://eth.drpc.org";
}else if ($network=="BinanceBNB") {
     $rpcUrl="https://bsc-dataseed1.binance.org:443";
}



//$rpcUrl = $Networks[$network];
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

    echo "Balance of $address : $balance";
    return $balance;

} else {
    echo "Error: Unable to fetch balance";
    return 0;
}




}






}
*/




function validateEmail($email) {
    // Use a regular expression to validate email format
    $pattern = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
    return preg_match($pattern, $email);
}


function generateActivationKey($email,$ItemType)
{
    $hash = md5($email, true);
    $Code="";


    if ($ItemType=="Software 1") {
       $Code=substr(str_replace('-', '', bin2hex($hash)), 2, 18);
   }else if($ItemType=="Software 2") {
       $Code=substr(str_replace('-', '', bin2hex($hash)), 3, 16);
   }


   $uppercaseString = strtoupper($Code);
   return $uppercaseString;
}





function GoToSearch($email) {
   header("Location: search.php" . "?email=" .$email);
   exit;
}


function updatePaymentStatus($address, $newStatus,$ItemType,$PayWith) {
    try {



        //$db = new SQLite3("paymentsfioeqpfjsdjfoieireieriueruwowowi232125466565876454.db"); // Open your SQLite database
$db = new SQLite3($GLOBALS['DatabaseName']); 
        $currentTime = time();

// Check if ItemType and email are already in the database
        $checkQuery = $db->prepare('SELECT COUNT(*) FROM payments WHERE bitcoin_address = :address AND PayWith = :PayWith AND status != "complete"');
        $checkQuery->bindValue(':address', $address, SQLITE3_TEXT);
        $checkQuery->bindValue(':PayWith', $PayWith, SQLITE3_TEXT);
        $result = $checkQuery->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);


        $key="";
        $email="";
        if ($row['COUNT(*)'] > 0) {

           $key= generateActivationKey($row['email'],$ItemType);
           $key=encryptString($key);

           $email=$row['email'];
       }




       $query = "UPDATE payments SET status = :newStatus,key = :key WHERE bitcoin_address = :address AND ItemType = :ItemType AND PayWith = :PayWith";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':newStatus', $newStatus, SQLITE3_TEXT);
       $stmt->bindParam(':address', $address, SQLITE3_TEXT);
       $stmt->bindParam(':ItemType', $ItemType, SQLITE3_TEXT);
       $stmt->bindParam(':key', $key, SQLITE3_TEXT);
       $stmt->bindParam(':PayWith', $PayWith, SQLITE3_TEXT);


       $result = $stmt->execute();

       if ($result) {
        echo "Status updated successfully.";

        //GoToSearch($email);

    } else {
        echo "Failed to update status.";
    }

        $db->close(); // Close the database connection
    } catch (Exception $e) {
        echo "database Error: " . $e->getMessage();
    }
}


/*
function getBitcoinAddressBalancePHP($address,$ItemType) {
   // echo "";
    //echo "Address:".$address;

    $config = json_decode(file_get_contents('config.json'), true);
    $ItemType = $config['PruchaseItem']['ItemType'];
    $PayAmount = $config['Amount'][$ItemType];

    $url = "https://api.blockcypher.com/v1/btc/main/addrs/$address";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['balance'])) {
        $balance = $data['balance'] / 100000000; // Convert from satoshis to BTC
//echo "Btc Balance:".$balance;
//echo "PayAmount:".$PayAmount;
        if ($balance >= $PayAmount) {
        //if ($balance >= 0) {
            updatePaymentStatus($address,"complete",$ItemType);
    # code...
        }else
        {
            echo "<br>Balance:<h3 class='text-danger'>".$balance   ."</h3> <br>Not detect payment,if you have transfered funds to our address,Please try again in a few minutes.";
        }

        return $balance;
    } else {
        return null;
    }
}
*/







if (isset($_GET['email']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
            // ... Rest of the PHP code for displaying results ...
    $email = $_GET['email'];

            // Connect to SQLite database
    $db = new SQLite3($GLOBALS['DatabaseName']);

    $currentTime = date('Y-m-d H:i:s');

    // set order visible time as 30 days
    $date = new DateTime($currentTime);
    $date->modify('+30 days');
    $futureTime = $date->format('Y-m-d H:i:s');
    $query = "SELECT * FROM payments WHERE email = :email AND expiration_time < '$futureTime'";

    // Prepare and execute the query
    //$query = "SELECT * FROM payments WHERE email = :email AND status = 'complete'";
    //$query = "SELECT * FROM payments WHERE email = :email";
    //$query = "SELECT * FROM payments WHERE email = :email AND expiration_time > '$currentTime'";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();


            // Display results as a table
    echo "<div class='card mt-3'>";
    echo "<div class='card-body'>";
    echo "<h1 class='card-title'>Your orders</h1>";
    echo "<h8 class='card-title'>If you have already transferred funds to the cyrptocurrency address below, please wait a few minutes for the account to arrive and click the update button to see your activation code.</h8>";
    echo "<form id='statusForm' method='POST' action='search.php'>";
    echo "<table class='table table-bordered'>
    <tr>

        <th>Email</th>
        <th>ItemType</th>
        <th>Pay To Address</th>
        <th>Amount</th>
        <th>Key</th>
        <th>PayWith</th>
        <th>Status</th>
        
        <th>Update Status</th>

    </tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {

        // Decrypt the encrypted data
        $decryptedKey = decryptString($row['key']);

        echo "<tr>
        <td>{$row['email']}</td>
        <td>{$row['ItemType']}</td>
        <td>{$row['bitcoin_address']}</td>
        <td>{$row['amount']}</td>
        <td class='text-danger'>{$decryptedKey}</td>
        <td><image src='images/{$row['PayWith']}.png' width='20' height='20' class='rounded'>{$row['PayWith']}</td>";
        

        if ($row['status']=="complete") {
            echo "<td><img width='20' height='20' src='checkmark.png'>{$row['status']}</td> ";
        }else{
           echo "<td>{$row['status']}</td> ";
       }



       if ($row['status']=="complete") {
         echo "<td>-</td> ";
     }else{
        $combineString= '?bitcoin_address='. $row['bitcoin_address'] . '&ItemType=' . $row['ItemType']  . '&PayWith=' .  $row['PayWith']. '&emailPost=' .  $row['email'] ;
        echo "<td>
        <a href='{$combineString}' class='btn btn-primary'  >
            update
        </a>    
    </td>
</tr>"; 
}


}

echo "</table>";
echo "</form>";
echo "</div>";
echo "</div>";

            // Close the database connection
$db->close();
}



?>
</div>



<!-- <input type='hidden' id='bitcoinAddress' name='bitcoinAddress' value=''>
     <input type='hidden' id='ItemType' name='ItemType' value=''>
     <input type='hidden' id='PayWith' name='PayWith' value=''>
     <button type='button' class='btn btn-primary' onclick=\"checkStatus('{$row['bitcoin_address']}', '{$row['ItemType']}', '{$row['PayWith']}')\">Update</button>-->
     <!-- Include Bootstrap JS and jQuery scripts here -->
     <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
     <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
 </body>
 </html>
