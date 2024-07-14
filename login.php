<?php
session_start();

// Your SQLite database setup
 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {



// Assuming $_POST['username'] contains the user input
$username = $_POST['username'];
$password = $_POST['password'];


// Define a filter for the username input
$filterOptions = [
    'options' => [
        // Specify the filter you want to use
        'filter' => FILTER_SANITIZE_STRING,
        
        // Add any additional filter flags you need
        'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,

        // Add any additional filter flags you need
       'regexp' => '/^[a-zA-Z0-9]*$/'
    ]
];

// Apply the filter to the username input
$filteredUsername = filter_var($username, FILTER_VALIDATE_STRING, $filterOptions);
$filteredUPassword = filter_var($password, FILTER_VALIDATE_STRING, $filterOptions);


if ($filteredUsername === false || $filteredUPassword === false ) {
	return;
    // Invalid input
    // Handle the error or notify the user
}   




    // Perform authentication logic here
    // Example: Fetch hashed password from database and compare

    // If authentication is successful, store user data in session
    if ($username=="DFLKSKFOIEWIOEWIOOWIRU23938" && $password=="DFLKSKFOIEWIOEWIOOWIRU23938") {
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $username;
    }
}

header('Location: data.php');
?>
