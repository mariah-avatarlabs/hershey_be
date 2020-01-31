<?php
require ('prizes.php');
require ('user.php');
require ('utils.php');

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

function updatePrize($type, $ID){
	global $conn;
	global $dateStamp;
	$data = array(
		'prizeUpdated' => FALSE,
	);
	$prizeManager = new PrizeManager($conn, $dateStamp);
	$prizeUpdated = $prizeManager->update($type, $ID);

	if(hasError($prizeUpdated) == FALSE){
		$data["prizeUpdated"] = TRUE;
	} else {
		$data["error"] = $prizeUpdated["error"];
	}

	return $data;

}


function createUser(){
	global $conn;
	global $dateStamp;

	$userManager = new UserManager($conn, $dateStamp);
	$prizeManager = new PrizeManager($conn, $dateStamp);
	
	$data = array(
		'created' => FALSE,
	);

	$userData = $userManager->create();

	if(hasError($userData) == FALSE){
		$data["created"] = TRUE;

		$prizeHasUpdated = updatePrize('claimed', $userData["prizeID"]);
		$data=array_merge($data, $prizeHasUpdated);

	} else {
		$data["error"] = $userData["error"];
	}

	echo json_encode($data);
	mysqli_close($conn);

};


function wonPrize(){
	global $conn;
	global $dateStamp;

	$prizeManager = new PrizeManager($conn, $dateStamp);

	$data = array(
		'won' => false,
		'prizeID' => null
	);	

	$prizesAvailable = $prizeManager->canWin();

	if(hasError($prizesAvailable) == FALSE){
		$data["won"] = $prizesAvailable["hasWon"];

		// REFACTOR -- deep if?
		if($data["won"] == TRUE){
			$prizeData = $prizeManager->retrieveAvailablePrize();
			if(hasError($prizeData) == FALSE){
				$data['prizeID'] = $prizeData['prize']['id'];
				
				$prizeHasUpdated = updatePrize('won', $data['prizeID']);
				$data=array_merge($data, $prizeHasUpdated);

				// update prize time_won stamp - pull out
				// $prizeStatus = $prizeManager->update('won', $data['prizeID']);
				// if(hasError($prizeStatus) == TRUE){
				// 	$data['error'] = $prizeStatus['error'];
				// }

			} else {
				$data['error'] = $prizeData['error'];
			}

		}; 

	} else {
		$data['error'] = $prizesAvailable['error'];
	};

	echo json_encode($data);
	mysqli_close($conn);

};


function init(){
	global $conn;

	$action = NULL;

	if( isset($_POST['action']) ){
		$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	}

	switch ($action) {	
		case 'won':
			wonPrize();		
			break;	
	
		case 'claim':
			createUser();		
			break;			
		
		default:
		// OPTIONS?
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

