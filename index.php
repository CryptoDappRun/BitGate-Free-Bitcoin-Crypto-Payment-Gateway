<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//composer require endroid/qr-code
//sudo chown -R :www /home/wwwroot/default/paypal/payments.db
//chown -R www:www test
//git clone https://github.com/t0k4rt/phpqrcode
//git clone https://github.com/BitcoinPHP/BitcoinECDSA.php
//composer require web3p/web3.php
//require '../vendor/autoload.php';
//composer require kornrunner/ethereum-address

require_once 'vendor/autoload.php';
use kornrunner\Ethereum\Address;


require_once 'BitcoinECDSA.php/src/BitcoinPHP/BitcoinECDSA/BitcoinECDSA.php';

require 'encryption.php';
require_once 'globals.php'; 
require 'balance.php';

 

       

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;


$db = new SQLite3($GLOBALS['DatabaseName'], SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
if (!$db) {
    throw new Exception('Unable to open database');
}



//echo $GLOBALS['DatabaseName'];
//return;
// Initialize SQLite database
//$db = new SQLite3($GLOBALS['DatabaseName']);

// Create payments table if it doesn't exist
$db->exec('CREATE TABLE IF NOT EXISTS payments (id INTEGER PRIMARY KEY, bitcoin_address TEXT,bitcoin_Private TEXT,ItemType TEXT,key TEXT DEFAULT "",email TEXT, amount REAL, status TEXT,PayWith TEXT,expiration_time DATETIME DEFAULT CURRENT_TIMESTAMP)');



//echo $PayType[0];

$CurrentAddress="";
$CurrentPayWith="";
$CurrentEmail="";

//$config = json_decode(file_get_contents('config.json'), true);

//////******////////
$ItemType =$PurchaseItem[0];
//////******////////




//$PayAmount = $config['Amount'][$ItemType];


$email = "";

$Public =  "";
$Private = "";


//$address111 = '12qTdZHx6f77aQ74CPCZGSY47VaRwYjVD8';
// Call the getBinanceBalance function to fetch the balance
//$balance = getBalance($address111,"bitcoin");
// Display the balance
//echo "Balance of $address111: $balance  ";



/*
// Encrypt the plaintext
$plaintext = "Secret message";
$encrypted = encryptString($plaintext);
echo "Encrypted: $encrypted<br>";

// Decrypt the encrypted data
$decrypted = decryptString($encrypted);
echo "Decrypted: $decrypted";
*/



function updatePaymentStatus($address, $newStatus,$ItemType,$PayWith) {
    try {
$db = new SQLite3($GLOBALS['DatabaseName']); // Open your SQLite database

$query = 'UPDATE payments SET status = :newStatus WHERE bitcoin_address = :address AND ItemType = :ItemType AND PayWith = :PayWith AND status != "complete"';
$stmt = $db->prepare($query);
$stmt->bindParam(':newStatus', $newStatus, SQLITE3_TEXT);
$stmt->bindParam(':address', $address, SQLITE3_TEXT);
$stmt->bindParam(':PayWith', $PayWith, SQLITE3_TEXT);

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


// Generate a unique Bitcoin address
function generateETHAddress() {

    $address = new Address();
// get address
//$address->get();
// 4e1c45599f667b4dc3604d69e43722d4ace6b770

//$address->getPrivateKey();
// 33eb576d927573cff6ae50a9e09fc60b672a8dafdfbe3045c7f62955fc55ccb4

//$address->getPublicKey();

    return array("0x".$address->get(), $address->getPrivateKey());

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




    if (isset( $_POST['address']) && isset( $_POST['PayWith'])) {
        $Address = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);   
        $PayWith = filter_var(trim($_POST['PayWith']), FILTER_SANITIZE_STRING);  

        echo "obtain address,check balance:" .$Address ;
        $balance= getBalance($Address,$PayWith);
        echo $balance;
        if ($balance >= $Payments[$PayWith]) {
            updatePaymentStatus($Address,"complete",$ItemType,$PayWith);
# code...
        }else
        {
            OperationAfterNoPayment($email);
        }


    }



    if (!isset($_POST['email'])) {
        return;
    }

    if (!isset($_POST['PayWith'])) {
        return;
    }



    $email =trim($_POST['email']) ;
    $email = filter_var($email, FILTER_SANITIZE_STRING);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "wrong email.";
        return;
    }  



    $PayWith = trim($_POST['PayWith']);
    $PayWith= filter_var($PayWith, FILTER_SANITIZE_STRING);


 
// Check if ItemType and email are already in the database
    $checkQuery = $db->prepare('SELECT COUNT(*) FROM payments WHERE ItemType = :ItemType AND email = :email AND PayWith = :PayWith AND status != "complete"');
    $checkQuery->bindValue(':ItemType', $ItemType, SQLITE3_TEXT);
    $checkQuery->bindValue(':email', $email, SQLITE3_TEXT);
    $checkQuery->bindValue(':PayWith', $PayWith, SQLITE3_TEXT);
    $result = $checkQuery->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['COUNT(*)'] > 0) {
//echo "Error: ItemType and email already exist in the database.";
        OperationAfterNoPayment($email);
    } else {


        $AddressInfo=[];


        if ($PayWith=="Bitcoin") {
            $AddressInfo = generateBitcoinAddress();
        }else{
            $AddressInfo = generateETHAddress();
        }

        $Public = $AddressInfo[0];


        $Private = encryptString($AddressInfo[1]) ;


        if (!in_array($PayWith, $PayType)) {
            return;
        }



        $PayAmount=$Payments[$PayWith];
//$expiration_time = date('Y-m-d H:i:s', strtotime('+3 minutes'));
// Store payment information in the database
//$stmt = $db->prepare('INSERT INTO payments (bitcoin_address,bitcoin_Private, ItemType,email,amount, status, PayWith,expiration_time) VALUES (:Public,:Private,:ItemType, :email, :PayAmount,:status,:PayWith,:expiration_time)');
        $stmt = $db->prepare('INSERT INTO payments (bitcoin_address,bitcoin_Private, ItemType,email,amount, status, PayWith) VALUES (:Public,:Private,:ItemType, :email, :PayAmount,:status,:PayWith)');

        $stmt->bindValue(':Public', $Public, SQLITE3_TEXT);
        $stmt->bindValue(':Private', $Private, SQLITE3_TEXT);
        $stmt->bindValue(':ItemType', $ItemType, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':PayAmount', $PayAmount, SQLITE3_FLOAT);
        $stmt->bindValue(':status', 'Awaiting Payment', SQLITE3_TEXT);
        $stmt->bindValue(':PayWith', $PayWith, SQLITE3_TEXT);
// $stmt->bindValue(':expiration_time', $expiration_time, SQLITE3_TEXT);


        if ($stmt->execute()) {
// echo "Record inserted successfully.";
        } else {
            echo "Error inserting record: " . $db->lastErrorMsg();
        }
    }



// getBitcoinAddressBalance($Public);
    $CurrentAddress=$Public;
    $CurrentPayWith=$PayWith;
    $CurrentEmail=$email;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>BitGate Cryptocurrency Payment Gateway</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>




    <nav class="navbar navbar-expand-lg navbar-light bg-light">

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">

                <li class="nav-item">
                    <a class="nav-link" href="search.php">Order</a>
                </li>

            </ul>
        </div>
    </nav>



    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-4 m-2">
                <div class="card shadow m-2">
                    <!--<div class="card-header">Purchase</div>-->  
                    <div class="card-body text-center">





                        <div class="container mt-1" style="border-radius: 10px">




                            <h5 class="mb-4"><img src="images/cryptocurrency.png" width="50" height="50" class="m-2">We accept Cryptocurrency</h5>

                            <h6 class="mb-4"> <?php echo $ItemType; ?></h6>



                            <label for="selectBox">Pay With:</label>



                            <img id="CryptoIcon" width="30" height="30" src="bitcoin.png" class="m-2 rounded">

                            <div class="col-10 text-center mx-auto">
                                <select id="selectBox" name="selectBox" class="form-select" aria-label="Default select example">
                                </div>



                                <!--<select id="selectBox" name="selectBox">-->  
                                <?php
// Define an array of fruits


// Iterate through the array and create options
// foreach ($PayType as $OnePayType) {
//  echo "<option value='$OnePayType'>$OnePayType</option>";
                                foreach ($Payments as $PaymentName => $text) {
                                    echo "<option value='$PaymentName'>$PaymentName</option>";
                                }
                                ?>
                            </select>

                            <p id="result">You selected: </p>


                            <form method="post">

                                <div class="row justify-content-center">
                                    <div class="m-3 col">
                                        <label for="email" class="form-label" >Your Email:</label>
                                        <input type="text" type="email" name="email" class="form-control"   required>
                                        <input type="hidden" name="PayWith" id="PayWith" value="Bitcoin">

                                    </div>

                                </div>
                                <button type="submit" class="btn btn-primary">Pay with Crypto</button>



                            </form>
                            <br>




                            <div id="PayDetailID" class="mt-4">

                                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>





                                    <h5><img src="loadingdata.gif" width="30" height="30" class="m-1">Waiting for payment...</h5>
                                    <p>Email: <strong><?php echo $email; ?></strong></p>
                                    <p>PayWith: <strong><?php echo $PayWith; ?></strong> </p>

                                    <p>You need send: <h4 style="color: red;"><?php echo $Payments[$PayWith]; ?> </h4> <?php echo $PayWith?> (≈ 30 USD)</p>


                                    <p>To: <h6 style="color: red;"><strong> <?php echo $Public; ?></strong></h6></p>
                                    <img src="generate_qr_code.php?address=<?php echo $Public; ?>" alt="Bitcoin QR Code">



                                    <br>
                                    <form method="post" action="">
                                        <input type="hidden" name="address" id="address" value="">
                                        <input type="hidden" name="PayWith" id="PayWith" value="<?php echo $PayWith; ?>">

                                        <div class="m-3">
                                            Get Crypto from 
                                            <a href="" target="_blank" >exchange</a>,
                                      

                                        </div>
                                        <a href="search.php?email=<?php echo $email; ?>" class="btn btn-primary"  >    Yes, I paid.</a>

<!--

<button id="IPaidID" class="btn btn-primary" type="submit" >Yes, I have already paid.</button>

-->

</form>


<?php endif; ?>


</div>


<div id="balanceResult"></div>




</div>





</div>
</div>
</div>
</div>
</div>




</body>


<script>


// Get a reference to the select element
const selectBox = document.getElementById("selectBox");


// Get a reference to the result paragraph
const resultParagraph = document.getElementById("result");

// Add an event listener to the select element to detect changes
selectBox.addEventListener("change", function () {
// Get the selected option
const selectedOption = selectBox.options[selectBox.selectedIndex];

// Get the value and text of the selected option
const selectedValue = selectedOption.value;
const selectedText = selectedOption.text;

document.getElementById("PayWith").value=selectedOption.value;

console.log("you select:",selectedOption.value)
// Update the result paragraph with the selected option
resultParagraph.textContent = "You selected: " + selectedText  ;

document.getElementById("CryptoIcon").src="images/"+ selectedText + ".png";

document.getElementById("PayDetailID").innerHTML="";
});




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

async function updateBalance() {
console.log("updateBalance...");
    var Address = '<?php echo $CurrentAddress ?>';
    var PayWith = '<?php echo $CurrentPayWith ?>';
    var Email = '<?php echo $CurrentEmail ?>';
   // echo  "Address:" . Address ;
  //  echo  "PayWith:" . PayWith ;

  console.log( "Address:" ,Address);
    console.log( "PayWith:" , PayWith);
//address=address.toString();

//document.getElementById("IPaidID").style.display = "none";


balance= await getBalance(Address,PayWith);

const Payments = {
    "Bitcoin": 0.001,
    "Ethereum": 0.016,
    "BinanceBNB": 0.12,
    "PolygonMatic": 50,
    "AvalancheAvax": 2.5,
    "CronosCRO": 0.01,
    "FantomFTM": 135
};

// JSON string representation
//const PaymentsJson = JSON.stringify(Payments);

console.log("balance",balance);


if (balance >= Payments[PayWith]) {
//if ($balance >= 0) {
   // updatePaymentStatus($Address,"complete",$ItemType,$PayWith);
    goToSearch(Email);

}



}


function goToSearch(email) {
    const url = `search.php?email=${encodeURIComponent(email)}`;
    window.location.href = url;
}


async function getBalance(address, network) {
    if (network === "Bitcoin") {
        try {
            const url = `https://api.blockcypher.com/v1/btc/main/addrs/${address}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.balance !== undefined) {
                const balance = data.balance / 100000000; // Convert from satoshis to BTC
                return balance;
            } else {
                console.error('Bitcoin Error:', data);
                return 0;
            }
        } catch (error) {
            console.error('Bitcoin Error:', error);
            return 0;
        }
    } else {
        try {
            const Networks = {
                "Ethereum": "https://eth.drpc.org",
                "BinanceBNB": "https://bsc-dataseed1.binance.org:443",
                "PolygonMatic": "https://polygon.llamarpc.com",
                "AvalancheAvax": "https://api.avax.network/ext/bc/C/rpc",
                "CronosCRO": "https://evm.cronos.org",
                "FantomFTM": "https://rpc.ankr.com/fantom"
            };

            const rpcUrl = Networks[network];

            const requestData = JSON.stringify({
                jsonrpc: '2.0',
                id: 1,
                method: 'eth_getBalance',
                params: [
                    address,
                    'latest' // You can use 'latest' to get the latest balance
                ]
            });

            const response = await fetch(rpcUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: requestData
            });

            const responseData = await response.json();

            if (responseData.result !== undefined) {
                const balanceWei = parseInt(responseData.result, 16); // Convert hexadecimal balance to decimal
                const balance = balanceWei / 1e18; // Convert Wei to ETH or respective token
                return balance;
            } else {
                console.error('Blockchain RPC Error:', responseData);
                return 0;
            }
        } catch (error) {
            console.error('Blockchain RPC Error:', error);
            return 0;
        }
    }
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
