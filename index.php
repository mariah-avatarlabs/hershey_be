<?php
require ('prizes.php');
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
$prizeManager = new PrizeManager($conn, $dateStamp);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// utils
function generateTimestamp(){
	$date = new Datetime();
	return $date->format('Y-m-d H:i:s');
}


function createUser(){
    global $conn;
	
	$firstname = $_POST["firstname"];
	$lastname = $_POST["lastname"];
	$email = $_POST["email"];
	$prizeID = intval($_POST["prizeID"]);
	$data = array(
		'user' => null,
		'claimedPrize' => false,
	);
	
	$query = "INSERT INTO Users (firstname, lastname, email, prize_id) VALUES (?, ?, ?, ?) ";
    
    $sql = $conn->prepare($query);
    $sql->bind_param("sssi", $firstname, $lastname, $email, $prizeID);
	$result = $sql -> execute();

    if ($result) { 
		$data['user'] = retrieveUser();
		$data['claimedPrize'] = claimedPrize();
		echo json_encode($data);

	} else {
		//QUESTION - double check
		$data['error'] = $sql->error;
		echo json_encode($data);

        // echo " ERROR: " . $sql->error;
     }
    
};


function retrieveUser(){
    global $conn;
	
	$email = $_POST["email"];

    $query = "SELECT * FROM `Users` WHERE `email` = (?) ";
    $sql = $conn->prepare($query);
    $sql->bind_param("s", $email);

    $users = array();

    if ($sql -> execute()) { 
        $result = $sql->get_result();

        // REFACTOR: CHECK HOW MANY RESULTS
        while ($row = $result->fetch_array(MYSQLI_ASSOC)){
	 	    return $row;
        }

     } else {
        echo " ERROR: " ;
     }
    
};


function retrievePrize(){
	global $conn;
	
	$result = NULL;

	if( !empty($_POST['prizeID']) ){
		$prizeID =  $_POST['prizeID'];
		$query = "SELECT * FROM `Prizes` WHERE `id` = (?)";

		$sql = $conn->prepare($query);
		$sql->bind_param("i", $prizeID);

		if($sql -> execute()){
			$result = $sql -> get_result();
		}

	} else {
		$query = "SELECT * FROM `Prizes` WHERE `time_won` IS NULL LIMIT 1 ";
		$result = $conn -> query($query);
	}


	if($result){
		$prize = $result -> fetch_object();
		
		if(isset($prizeID)){
			return json_encode($prize);

		} else {
			return $prize;

		}


	} else {
		echo "ERROR";

	}

};


function assignPrizeToUser(){
	global $conn;
	global $dateStamp;
	echo "CALLED";

	$email = $_POST["email"];
	$prizeID = $_POST['prizeID'];

	// QUESTION - DUPLICATES?
	$query = "UPDATE `Users` SET `prize_id` = (?) WHERE `email` = (?) LIMIT 1 ";

	// add timestamp to prize
    $sql = $conn->prepare($query);
	$sql -> bind_param("is", $prizeID, $email);
	
	if ($sql -> execute()) { 
		echo "SUCCESS";	
	} else {
		echo " ERROR: DID NOT UPDATE USER WITH PRIZE" ;
	}

};

function timeInterval($time){
	$baseTime = strtotime($time);
	echo 'time';
	echo $baseTime;
}

function wonPrize(){
	global $prizeManager;

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
				
				// update prize time_won stamp
				$prizeStatus = $prizeManager->updateTimeWon($data['prizeID']);
				if(hasError($prizeStatus) == TRUE){
					$data['error'] = $prizeStatus['error'];
				}


			} else {
				$data['error'] = $prizeData['error'];
			}

		}; 

	} else {
		$data['error'] = $prizesAvailable['error'];
	};

	echo json_encode($data);

};
wonPrize();



// $prizesAvailable = $prizesAvailable['hasWon'];

// echo json_encode();

// return prize id
function assignWonToPrize(){
	global $conn;
	global $dateStamp;

	// $prizeID = $_POST['prizeID'];
	// $prizeID = (int)$prize -> id;
	$prizeID = 9;
	$prize = retrievePrizeById($prizeID);

	// $prize = retrievePrize();

	// $query = "UPDATE `Prizes` SET `time_won` = (?) WHERE `id` = (?);";
	// $sql = $conn -> prepare($query);
	// $sql -> bind_param("si", $dateStamp, $prizeID);

	// if ($sql -> execute()) { 
	// 	return $prizeID;

	// } else {
	// 	echo " ERROR: DID NOT UPDATE PRIZE" ;
	// 	return NULL;

	// }

};

function claimedPrize(){
	global $conn;

	$prizeID = $_POST['prizeID'];

	$dateStamp = generateTimestamp();

	$query = "UPDATE `Prizes` SET `time_claimed` = (?) WHERE `id` = (?) ";
	$sql = $conn -> prepare($query);
	$sql -> bind_param("si", $dateStamp, $prizeID);

	if ($sql -> execute()) { 
		return true;
	} else {
		return false;
	}

}



// $action =  $_POST['action'];

// switch ($action) {
// 	case 'createUser':
// 		createUser();		
// 		break;

// 	case 'retrieveUser':
// 		retrieveUser();		
// 		break;	

// 	case 'retrievePrize':
// 		retrievePrize();		
// 		break;			

// 	case 'won':
// 		wonPrize();		
// 		break;	

// 	case 'claim':
// 		claimedPrize();		
// 		break;			
	
// 	default:
// 		break;
// }


?>

