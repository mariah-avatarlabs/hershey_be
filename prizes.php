<?php
include_once ('utils.php');


class PrizeManager {

    function PrizeManager($conn, $dateStamp){
        $this->conn = $conn;
        $this->dateStamp = $dateStamp;
    }

    /** 
     * canWin() 
     * determines if there are any available prizes for the current time interval based off of how many prizes were claimed today
     * * returns: 
        * * onSuccess => $data:array ["hasWon":boolean]
        * * onFail => $data:array ["error":string]
    */
    public function canWin(){
        $dailyLimit = 5;
        $timeStart = "00:00:00";
        $timeEnd = "23:59:59";
        $baseDate = substr($this->dateStamp, 0, -8);
        
        $startDate = $baseDate . $timeStart;
        $endDate = $baseDate . $timeEnd;
        
        //* Define expected data structure
        $data = array(
            'hasWon' => FALSE,
        );
        
        //* Query for count of prizes won today 
        $query = "SELECT COUNT(*) FROM Prizes WHERE time_claimed BETWEEN (?) AND (?)";    
        
        //* Prepare and run query 
        $sql = $this->conn->prepare($query);
    	$sql -> bind_param("ss", $startDate, $endDate);
        $result = $sql->execute();
        $result = $sql->get_result();
        
        //* Filter data            
        if ($result) { 	

            //* Extract query results
            $count = $result -> fetch_row();
            $count = $count[0];
            
            //* Determine if user has won
            if($count < $dailyLimit){
                $data['hasWon'] = determineProbability($count, $dailyLimit);
            }

        } else {
            $data['error'] = $sql->error;
        }

        return $data;

    }

    /** 
     * update() 
     * updates prize `time_won` or `time_claimed` property based off of ID and update type.
     * @param type: string = determine if update property should be `time_won` or `time_updated`
     * @param ID: string = ID used in SQL query to retrieve prize data
     * * returns: 
        * * onSuccess => $data:array ["updated":boolean]
        * * onFail => $data:array ["error":string]
     * 
     * TODO: update ID to int
    */
    public function update($type, $ID){
        //* Define expected data structure
        $data = array(
            'updated' => FALSE,
        );
            
        //* Determine what type of update/query needs to be taken per the type key($type);
        switch ($type) {
            case 'won':
                $query = "UPDATE `Prizes` 
                    SET `time_won` = (?) 
                    WHERE `id` = (?)";
                break;

            case 'claimed':
                $query = "UPDATE `Prizes` 
                            SET `time_claimed` = (?) 
                            WHERE `id` = (?)";
                break;

           //? Should this be the default action?
           default:
                $query = "UPDATE `Prizes` 
                            SET `time_won` = (?) 
                            WHERE `id` = (?)";
                break;
        }


        //* Prepare and run query 
        $sql = $this->conn->prepare($query);
        $sql->bind_param("ss", $this->dateStamp, $ID);

        //* Filter data            
        if ($sql -> execute()) { 
            $data['updated'] = TRUE;
        } else {
            $data['error'] = $sql->error;
        }

        return $data;

    }

    /** 
     * retrieveAvailablePrize() 
     * returns prize record in DB if `time_claimed` is NULL and `time_won` is between the expressed interval or NULL
     * @param timeFrame: string = desired time interval
     * * returns: 
        * * onSuccess => $data:array ["prize":array ]
        * * $onFail => data:array ["error":string]
     *
     * TODO: allow arg to be passed in for variable time intervals
     * TODO: confirm get_result() call
    */
    public function retrieveAvailablePrize(){
        //? Pull into util??
        $baseTime = Datetime::createFromFormat('Y-m-d H:i:s', $this->dateStamp);
        $baseTime = $baseTime->modify('-10 minutes');
        $endTime = $baseTime->format('Y-m-d H:i:s');
        
        //* Define expected data structure            
        $data = array(
            'prize' => null,
        );
        
        //* Query for prize that is not claimed, and has either been won within the given time interval or has not been won
        $query = "SELECT * FROM `Prizes` 
                    WHERE `time_claimed` IS NULL 
                    AND `time_won` BETWEEN (?) AND (?) 
                    OR `time_won` IS NULL 
                    LIMIT 1";
        
        //* Prepare and run query 
        $sql = $this->conn->prepare($query);
        $sql->bind_param("ss", $endTime, $this->dateStamp);
        $result = $sql -> execute();

        //* Filter data            
        if ($result) { 	
            $result = $sql->get_result();

            while ($row = $result->fetch_array(MYSQLI_ASSOC)){
                $data['prize'] = $row;
           }
    
        } else {
            $data['error'] = $sql->error;
        }
    
        return $data;
    
    
    }

    
}






?>