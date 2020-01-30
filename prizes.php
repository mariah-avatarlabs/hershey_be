<?php


class PrizeManager {

    function PrizeManager($conn, $dateStamp){
        $this->conn = $conn;
        $this->dateStamp = $dateStamp;
    }

    public function canWin(){
        $dailyLimit = 5;
        $timeStart = "00:00:00";
        $timeEnd = "23:59:59";
        $baseDate = substr($this->dateStamp, 0, -8);
        
        $startDate = $baseDate . $timeStart;
        $endDate = $baseDate . $timeEnd;
        
        $data = array(
            'hasWon' => null,
        );
        
        $query = "SELECT COUNT(*) FROM Prizes WHERE time_claimed BETWEEN (?) AND (?)";    
        
        $sql = $this->conn->prepare($query);
    	$sql -> bind_param("ss", $startDate, $endDate);
        $result = $sql->execute();
        $result = $sql->get_result();
        
        if ($result) { 	
            $count = $result -> fetch_row();
            $count = $count[0];

            if($count < $dailyLimit){
                $data['hasWon'] = TRUE;
            } else {
                $data['hasWon'] = FALSE;
            }

        } else {
            $data['error'] = $sql->error;
        }

        return $data;

    }


}



function retrieveAvailablePrize(){

    $currTime = $dateStamp;
    $data = array(
        'prize' => null,
    );
    
	// REFACTOR - pull into util??
	$baseTime = Datetime::createFromFormat('Y-m-d H:i:s', $currTime);
	$baseTime = $baseTime->modify('-10 minutes');
    $endTime = $baseTime->format('Y-m-d H:i:s');

    echo $currTime;
    echo $endTime;

    $query = "SELECT * FROM `Prizes` 
                WHERE `time_won` BETWEEN (?) AND (?) 
                OR `time_won` IS NULL 
                LIMIT 1";

    $sql = $conn->prepare($query);
    $sql->bind_param("ss", $endTime, $currTime);

	$result = $sql -> execute();
	$result = $sql->get_result();
		
	if ($result) { 	
	    // REFACTOR - CHECK ONE RECORD??
        while ($row = $result->fetch_array(MYSQLI_ASSOC)){
            $data['prize'] = $row;
            echo json_encode($data);
       }

    } else {
        $data['status'] = $sql->error;
    }

    return $data;


}



?>