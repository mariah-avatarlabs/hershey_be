<?php
include_once ('prizes.php');
include_once ('user.php');
include_once ('utils.php');

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$hostname =  $_ENV['DB_HOST'];
$username =  $_ENV['DB_USER'];
$password =  $_ENV['DB_PASSWORD'];
$dbname =  $_ENV['DB_NAME'];

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli($hostname, $username, $password, $dbname); 
$dateStamp = generateTimestamp();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


/** 
 * updatePrize() 
 * makes call to prizeManager to update selected prize record; filter for error;
 * @param type: string = determine if update property should be `time_won` or `time_updated`
 * @param ID: string = prize ID used in SQL query
 * * returns: 
	 * * onSuccess => $data:array ["prizeUpdated":boolean]
	 * * onFail => $data:array ["prizeUpdated":boolean], "error":string]
 * 
 * ? is this necessary [similar function as prizemanager update]
*/
function updatePrize($type, $ID){
	global $conn;
	global $dateStamp;
	
	//* Define expected data structure
	$data = array(
		'prizeUpdated' => FALSE,
	);

	//* Make call to prizemanager to update the specified prize data
	$prizeManager = new PrizeManager($conn, $dateStamp);
	$prizeUpdated = $prizeManager->update($type, $ID);

	//* Filter for error
	if(hasError($prizeUpdated) == FALSE){
		$data["prizeUpdated"] = TRUE;
	} else {
		$data["error"] = $prizeUpdated["error"];
	}

	return $data;

}


/** 
 * createUser() 
 * manage queries for creating user and call to update associative prize; close connection
* * return(json_encoded): 
	 * * onSuccess => $data:array ["created":boolean]
	 * * onFail => $data:array ["error":boolean]
 * 
 * ? is this necessary [similar function as prizemanager update]
*/
function createUser(){
	global $conn;
	global $dateStamp;

	$userManager = new UserManager($conn, $dateStamp);
	$prizeManager = new PrizeManager($conn, $dateStamp);
	
	//* Define expected data structure
	$data = array(
		'created' => FALSE,
	);

	//* Make call to userManager to create user record in DB;
	$userData = $userManager->create();

	//* Filter for errors
	if(hasError($userData) == FALSE){
		$data["created"] = TRUE;

		//* Call to update prize `time_claimed` status
		$prizeHasUpdated = updatePrize('claimed', $userData["prizeID"]);
		$data=array_merge($data, $prizeHasUpdated);

	} else {
		$data["error"] = $userData["error"];
	}
	
	//* Send data back and close connection
	echo json_encode($data);
	mysqli_close($conn);

};


/** 
 * wonPrize() 
 * manage queries for creating user and call to update associative prize; close connection
 * * return(json_encoded): 
	 * * onSuccess => $data:array ["won":boolean, "prizeID":string, "prizeUpdated":boolean]
	 * * onFail => $data:array ["error":string]
 * 
*/
function wonPrize(){
	global $conn;
	global $dateStamp;
	
	$prizeManager = new PrizeManager($conn, $dateStamp);
	
	//* Define expected data structure
	$data = array(
		'won' => false,
		'prizeID' => null,
		'prizeUpdated' => false
	);	

	//* Check to see if user has won
	$prizesAvailable = $prizeManager->canWin();
	if(hasError($prizesAvailable) == FALSE){
		$data["won"] = $prizesAvailable["hasWon"];
		
		//* If user has won - retrieve a prize
		if($data["won"] == TRUE){
			
			//* Retrieve prizeID for selected prize
			$prizeData = $prizeManager->retrieveAvailablePrize();
			if(hasError($prizeData) == FALSE){
				$data['prizeID'] = $prizeData['prize']['id'];
				
				//* Call to update prize `time_won` status
				$prizeHasUpdated = updatePrize('won', $data['prizeID']);
				$data=array_merge($data, $prizeHasUpdated);

			} else {
				$data['error'] = $prizeData['error'];
			}

		}; 

	} else {
		$data['error'] = $prizesAvailable['error'];
	};

	//* Send data back and close connection
	echo json_encode($data);
	mysqli_close($conn);

};


/** 
 * init() 
 * initialize action per the key in the POST request; 
 * * echo(json_encoded): 
	 * * onFail => $data:array ["error":string]
 * 
*/
function init(){
	global $conn;

	$action = NULL;
	
	//* Check to see if POST includes an action; if so sanatize;
	if( isset($_POST['action']) ){
		$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	}
	
	//* Determine what action needs to be taken per the action key($action);
	switch ($action) {	
		case 'won':
			wonPrize();		
			break;	
	
		case 'claim':
			createUser();		
			break;			
		
		default:
			$data = array(
				'error' => "NO ACTION",
			);
			echo json_encode($data);
			mysqli_close($conn);
			break;
	}


}
init();



?>

