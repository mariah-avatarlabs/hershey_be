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

	if(!hasError($prizesAvailable) == TRUE){
		$data["won"] = $prizesAvailable["hasWon"];

		if($data["won"] == TRUE){
			echo ('get prize');
			
		} else {
			echo $data;
		}

	} else {
		echo $data;
	};

	// global $conn;
	// global $dateStamp;

	// $dailyLimit = 5;
	// $timeStart = "00:00:00";
	// $timeEnd = "23:59:59";
	// $baseDate = substr($dateStamp, 0, -8);

	// // REFACTOR - pull into util
	// $baseTime = Datetime::createFromFormat('Y-m-d H:i:s', $dateStamp);
	// $baseTime = $baseTime->modify('-10 minutes');
	// $endTime = $baseTime->format('Y-m-d H:i:s');



	// timeInterval($baseTime);

	// $endDate = $baseDate . $timeEnd;
	// $startDate = $baseDate . $timeStart;
	// $data = array(
	// 	'won' => false,
	// 	'prizeID' => null
	// );	

	// $query = "SELECT COUNT(*) FROM Prizes WHERE time_won BETWEEN (?) AND (?) AND time_claimed BETWEEN (?) AND (?)";
	// $sql -> bind_param("ss", $startDate, $endDate);

	/*
	// $query = "SELECT COUNT(*) FROM Prizes WHERE time_won BETWEEN (?) AND (?)";

	$sql = $conn->prepare($query);
	$sql -> bind_param("ss", $startDate, $endDate);

	$result = $sql -> execute();
	$result = $sql->get_result();

		
	if ($result) { 	
		$count = $result -> fetch_row();
		$count = $count[0];

		if($count < $dailyLimit){
			$prizeID = assignWonToPrize();

			$data['won'] = true;
			$data['prizeID'] = $prizeID;
			
			echo json_encode($data);

		} else {
			echo json_encode($data);


		}

	} else {
		echo " ERROR: DID NOT UPDATE USER WITH PRIZE" ;
	}
	*/

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

