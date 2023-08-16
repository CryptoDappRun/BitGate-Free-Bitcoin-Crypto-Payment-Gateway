<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// composer require endroid/qr-code
//sudo chown -R :www /home/wwwroot/default/paypal/payments.db
require_once 'BitcoinECDSA.php/src/BitcoinPHP/BitcoinECDSA/BitcoinECDSA.php';

require 'encryption.php';


use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;

// Initialize SQLite database
$db = new SQLite3('payments.db');

// Create payments table if it doesn't exist
$db->exec('CREATE TABLE IF NOT EXISTS payments (id INTEGER PRIMARY KEY, bitcoin_address TEXT,bitcoin_Private TEXT,ItemType TEXT,key TEXT DEFAULT "",email TEXT, amount REAL, status TEXT)');


$CurrentAddress="";

$config = json_decode(file_get_contents('config.json'), true);


$ItemType = $config['PruchaseItem']['ItemType'];

$PayAmount = $config['Amount'][$ItemType];


$email = "";

$Public =  "";
$Private = "";






/*
// Encrypt the plaintext
$plaintext = "Secret message";
$encrypted = encryptString($plaintext);
echo "Encrypted: $encrypted<br>";

// Decrypt the encrypted data
$decrypted = decryptString($encrypted);
echo "Decrypted: $decrypted";
*/



function updatePaymentStatus($address, $newStatus,$ItemType) {
    try {
        $db = new SQLite3('payments.db'); // Open your SQLite database

        $query = "UPDATE payments SET status = :newStatus WHERE bitcoin_address = :address AND ItemType = :ItemType";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':newStatus', $newStatus, SQLITE3_TEXT);
        $stmt->bindParam(':address', $address, SQLITE3_TEXT);
        $stmt->bindParam(':ItemType', $ItemType, SQLITE3_TEXT);
        
        $result = $stmt->execute();
        
        if ($result) {
            echo "Status updated successfully.";

            OperationAfterPaymentSuccess();

        } else {
            echo "Failed to update status.";

        }
        
        $db->close(); // Close the database connection
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}



function OperationAfterPaymentSuccess() {
   header("Location: search.php");
   exit;
}

function OperationAfterNoPayment($email) {
    header("Location: search.php" . "?email=" .$email);
    exit;
}



// Generate a unique Bitcoin address
function generateBitcoinAddress() {

    $bitcoinECDSA = new BitcoinECDSA();
$bitcoinECDSA->generateRandomPrivateKey(); //generate new random private key
$PublicAddress = $bitcoinECDSA->getAddress(); //compressed Bitcoin address
//$PrivateKey=$bitcoinECDSA->getPrivateKey();
$PrivateKey=$bitcoinECDSA->getWif();
//echo "Address: " . $PublicAddress . PHP_EOL;

return array($PublicAddress, $PrivateKey);
}





function getBitcoinAddressBalancePHP($address) {
    echo "";
    echo "Address:".$address;

    $url = "https://api.blockcypher.com/v1/btc/main/addrs/$address";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['balance'])) {
        $balance = $data['balance'] / 100000000; // Convert from satoshis to BTC
        echo $balance;
        return $balance;
    } else {
        return null;
    }
}





// Process payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {




    if (isset( $_POST['address'])) {
        $Address = $_POST['address'];
        echo "obtain address,check balance:" .$Address ;
        $balance= getBitcoinAddressBalancePHP($Address);
        echo $balance;
        if ($balance >= $PayAmount) {
            updatePaymentStatus($Address,"complete",$ItemType);
    # code...
        }else
        {
            OperationAfterNoPayment($email);
        }


    }



    if (!isset($_POST['email'])) {
        return;
    }





    $email = $_POST['email'];




    // Check if ItemType and email are already in the database
    $checkQuery = $db->prepare('SELECT COUNT(*) FROM payments WHERE ItemType = :ItemType AND email = :email');
    $checkQuery->bindValue(':ItemType', $ItemType, SQLITE3_TEXT);
    $checkQuery->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $checkQuery->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['COUNT(*)'] > 0) {
        //echo "Error: ItemType and email already exist in the database.";
        OperationAfterNoPayment($email);
    } else {


        $AddressInfo = generateBitcoinAddress();


        $Public = $AddressInfo[0];
        $Private = encryptString($AddressInfo[1]) ;



    // Store payment information in the database
        $stmt = $db->prepare('INSERT INTO payments (bitcoin_address,bitcoin_Private, ItemType,email,amount, status) VALUES (:Public,:Private,:ItemType, :email, :PayAmount,:status)');
        $stmt->bindValue(':Public', $Public, SQLITE3_TEXT);
        $stmt->bindValue(':Private', $Private, SQLITE3_TEXT);
        $stmt->bindValue(':ItemType', $ItemType, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':PayAmount', $PayAmount, SQLITE3_FLOAT);
        $stmt->bindValue(':status', 'Awaiting Payment', SQLITE3_TEXT);



        if ($stmt->execute()) {
           // echo "Record inserted successfully.";
        } else {
            echo "Error inserting record: " . $db->lastErrorMsg();
        }
    }



   // getBitcoinAddressBalance($Public);
    $CurrentAddress=$Public;

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>BitGate-Bitcoin Crypto Payment Gateway-Free-Open Source-No KYC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>




    <nav class="navbar navbar-expand-lg navbar-light bg-light">

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item active">
            <a class="nav-link" href="index.php"> Pay</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="search.php">User</a>
        </li>

    </ul>
</div>
</nav>



<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-4 m-2">
            <div class="card shadow">
                <div class="card-header">Purchase</div>
                <div class="card-body text-center">





                    <div class="container mt-1" style="border-radius: 10px">




                        <h5 class="mb-4"><img src="bitcoin.png" class="m-2">Bitcoin Payment Gateway</h5>
                        <form method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label" >Your Email:</label>
                                <input type="text" type="email" name="email" class="form-control"   required>
                            </div>
                            <button type="submit" class="btn btn-primary">Pay with Bitcoin</button>



                        </form>
                        <br>






                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                            <div class="mt-4">




                                <h5><img src="loadingdata.gif" width="30" height="30" class="m-1">Waiting for payment...</h5>
                                <p>Email: <?php echo $email; ?> </p>
                                <p>You need send: <h4 style="color: red;"><?php echo $PayAmount; ?> </h4>BTC </p>


                                <p>To: <h6 style="color: red;"> <?php echo $Public; ?></h6></p>
                                <img src="generate_qr_code.php?address=<?php echo $Public; ?>" alt="Bitcoin QR Code">
                            </div>


                            <br>
                            <form method="post" action="">
                                <input type="hidden" name="address" id="address" value="">
                                <button id="IPaidID" class="btn btn-primary" type="submit" >Yes, I have already paid.</button>
                            </form>



                        <?php endif; ?>




                        <div id="balanceResult"></div>




                    </div>





                </div>
            </div>
        </div>
    </div>
</div>




</body>


<script>
    function getBitcoinAddressBalance(address) {
        console.log("get balance from js")
        const url = `https://api.blockcypher.com/v1/btc/main/addrs/${address}`;

        fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.balance !== undefined) {
                        const balance = data.balance / 100000000; // Convert from satoshis to BTC

//document.getElementById('balanceResult').innerHTML = `Balance of ${address}: ${balance} BTC`;
if (balance > 0) {
 document.getElementById("IPaidID").click();
}else{
    console.log("This address has no fund." ,balance);
}

} else {
    document.getElementById('balanceResult').innerHTML = 'Failed to retrieve balance.';
}
})
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('balanceResult').innerHTML = 'An error occurred while fetching data.';
        });
    }

    function updateBalance() {

    var address = '<?php echo $CurrentAddress ?>';
    //address=address.toString();

    //document.getElementById("IPaidID").style.display = "none";

    getBitcoinAddressBalance(address);
   }

        // Update the balance every 20 seconds
        //updateBalance();
        var TempAddress = '<?php echo $CurrentAddress ?>';
        if (TempAddress!=="") {
            console.log('get balance now...',TempAddress);
            document.getElementById('balanceResult').value=TempAddress;
            document.getElementById('address').value=TempAddress;


        setInterval(updateBalance, 30000); // 20 seconds in milliseconds
    }








</script>

</html>
