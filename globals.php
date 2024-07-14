

<?php

/*

////////// sqlite change databae directory  prevent user to download database/////////////////////////////
open_basedir
find php.ini
open_basedir = /var/www/:/tmp/:/usr/share/php/
then restart server.



When installing PHP using the LNMP package, it was found that directly modifying the configuration of php.ini was not effective. The reason was that the nginx configuration file overwrote the php.ini configuration.

nano ./usr/local/nginx/conf/fastcgi.conf
fastcgi_param PHP_ADMIN_VALUE "open_basedir=$document_root/:/tmp/:/proc/";

fastcgi_param PHP_ADMIN_VALUE "open_basedir=/home/wwwroot/test.com:/tmp/:/proc/:/home/database/";

change permission to read database:
sudo chmod -R 755 /home/database/
sudo chmod 644 /home/database/payments.db

unable to insert data to database
sudo chmod 777 /home/database/
sudo chmod 777 /home/database/payments.db

////////////////////////////////////////////////////////////////////////////////////

*/



//database directory.  it's better to place payments.db to other folder.
$DatabaseName="/home/database/payments.db";

//Item name,default is the first title.
$PurchaseItem= array("Software 1", "Software 2");


//Accept crypto
$PayType  = array("Bitcoin", "Ethereum", "BinanceBNB", "PolygonMatic" ,"AvalancheAvax","CronosCRO","FantomFTM");

//price for your item.
$Payments = array(
    "Bitcoin" => 0.001,
    "Ethereum" => 0.016,
    "BinanceBNB" => 0.12,
    "PolygonMatic" => 50,
    "AvalancheAvax" => 2.5,
    "CronosCRO" => 0.01,
    "FantomFTM" => 135,
);


//RPC URL for query balance.
$Networks = array(
    "Ethereum" => "https://eth.drpc.org",
    "BinanceBNB" => "https://bsc-dataseed1.binance.org:443",
    "PolygonMatic" => "https://polygon.llamarpc.com",
    "AvalancheAvax" => "https://api.avax.network/ext/bc/C/rpc",
    "CronosCRO" => "https://evm.cronos.org",
    "FantomFTM" => "https://rpc.ankr.com/fantom/"   

);




?>