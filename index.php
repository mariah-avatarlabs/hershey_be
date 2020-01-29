<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$hostname =  $_ENV['DB_HOST'];
$username =  $_ENV['DB_USER'];
$password =  $_ENV['DB_PASSWORD'];
$dbname =  $_ENV['DB_NAME'];

// $hostname="localhost";
// $username="root";
// $password="";
// $dbname="hershey_sweep";

$conn = new mysqli($hostname, $username, $password, $dbname); 
$dateStamp = generateTimestamp();

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

    $query = "INSERT INTO Users (firstname, lastname, email) VALUES (?, ?, ?) ";
    
    $sql = $conn->prepare($query);
    $sql->bind_param("sss", $firstname, $lastname, $email);

    if ($sql->execute()) { 
        echo "Records inserted successfully.";
     } else {
        echo " ERROR: " . $sql->error;
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
	 	    echo json_encode($row);
	 	    return json_encode($row);
        }


     } else {
        echo " ERROR: " ;
     }
    
};


function retrievePrize(){
	global $conn;
	
	$result = NULL;
	$prize_array = array();
	$prizeID = $_POST['prizeID'];

	if( isset($prizeID) ){
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


	// REFACTOR -> ONLY ONE RESULT
	if($result){
		while($row = mysqli_fetch_assoc($result)){
			$prize_array[] = $row;
		};
		
		return json_encode($prize_array);


	} else {
		echo "ERROR";

	}

};


function assignPrizeToUser(){
	global $conn;
	global $dateStamp;
	echo "CALLED";

	// $email = $_POST["email"];
	// $prizeID = $_POST['prizeID'];
	$email = "testD";
	$prizeID = 6;

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


function wonPrize(){
	assignWonToPrize();
};

 
function assignWonToPrize(){
	global $conn;
	global $dateStamp;

	// $prizeID = $_POST['prizeID'];
	$prizeID = 5;

	$query = "UPDATE `Prizes` SET `time_won` = (?) WHERE `id` = (?);";
	$sql = $conn -> prepare($query);
	$sql -> bind_param("si", $dateStamp, $prizeID);

	if ($sql -> execute()) { 
		echo "SUCCESS";	
		call_user_func('assignPrizeToUser');

	} else {
		echo " ERROR: DID NOT UPDATE PRIZE" ;
	}

};


function claimedPrize(){
	global $conn;

	$prizeID = $_POST['prizeID'];

	$dateStamp = generateTimestamp();
	echo json_encode($dateStamp);

	$query = "UPDATE `Prizes` SET `time_claimed` = (?) WHERE `id` = (?) ";
	$sql = $conn -> prepare($query);
	$sql -> bind_param("si", $dateStamp, $prizeID);

	if ($sql -> execute()) { 
		echo "SUCCESS";	
	} else {
		echo " ERROR: " ;
	}

}



$action =  $_POST['action'];
switch ($action) {
	case 'createUser':
		createUser();		
		break;

	case 'retrieveUser':
		retrieveUser();		
		break;	

	case 'retrievePrize':
		retrievePrize();		
		break;			

	case 'won':
		wonPrize();		
		break;	

	case 'claim':
		claimedPrize();		
		break;			
	
	default:
		break;
}


?>

