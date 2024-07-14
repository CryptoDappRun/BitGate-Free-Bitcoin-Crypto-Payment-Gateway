# BitGate-Free-Bitcoin-Crypto-Payment-Gateway
BitGate is a Bitcoin crypto payment PHP gateway,<br>
*Free,<br>
*Open Source,<br>
*No KYC,<br>
*PHP with Sqlite database,<br>
*No need install bitcoin core,<br>
*Funds store to your cryptocurrency key directly,<br>
*Encrypted database,<br>
*Full control..<br>


PHP 7.4.30 tested

Install Dependencies：
#### `git clone https://github.com/t0k4rt/phpqrcode`
#### `git clone https://github.com/BitcoinPHP/BitcoinECDSA.php`
#### `composer require web3p/web3.php`
#### `composer require kornrunner/ethereum-address`

place payments.db to other directory,and set globals.php.
set open_basedir in php.ini.



<br>
The principle of the software is to generate different private keys and bind the user's order. Once it detects that the address has received payment, it will perform corresponding database operations, such as upgrading the level, recharging, displaying the activation code, etc. The private key is encrypted and stored in the database, and only the administrator can view it. In the whole process, only the third-party API is needed to check the balance of the address, and there are so many such APIs on the Internet.

<br>

>Support Crypto<br>
<img src="https://github.com/CryptoDappRun/BitGate-Free-Bitcoin-Crypto-Payment-Gateway/blob/main/4.png?raw=true"><br><br>

#### index.php 
>to accept payment<br>
<img src="https://github.com/CryptoDappRun/BitGate-Free-Bitcoin-Crypto-Payment-Gateway/blob/main/1.png?raw=true"><br><br>
#### search.php
>user check their order
<img src="https://github.com/CryptoDappRun/BitGate-Free-Bitcoin-Crypto-Payment-Gateway/blob/main/2.png?raw=true"><br><br>
#### data.php
>admin check all of the order
<img src="https://github.com/CryptoDappRun/BitGate-Free-Bitcoin-Crypto-Payment-Gateway/blob/main/3.png?raw=true"><br><br>

