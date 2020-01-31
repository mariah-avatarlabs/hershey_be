<?php

class PrizeManager {

    function PrizeManager($conn, $dateStamp){
        $this->conn = $conn;
        $this->dateStamp = $dateStamp;
    }

    /** 
     * canWin() 
     * determines if there are any available prizes for the current time interval based off of how many prizes were claimed today
     * * returns: 
        * * $data:array ["hasWon" = boolean]
        * * $data:array ["error" = string]
    */
    public function canWin(){
        $dailyLimit = 5;
        $timeStart = "00:00:00";
        $timeEnd = "23:59:59";
        $baseDate = substr($this->dateStamp, 0, -8);
        
        $startDate = $baseDate . $timeStart;
        $endDate = $baseDate . $timeEnd;
        
        $data = array(
            'hasWon' => FALSE,
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

    /** 
     * update() 
     * updates prize `time_won` or `time_claimed` property based off of ID and update type.
     * @param type: string = determine if update property should be `time_won` or `time_updated`
     * @param ID: string = ID used in SQL query to retrieve prize data
     * TODO: update ID to int
     * * returns: 
        * * $data:array ["updated" = boolean]
        * * $data:array ["error" = string]
    */
    public function update($type, $ID){
        $data = array(
            'updated' => FALSE,
        );
            
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

           default:
                $query = "UPDATE `Prizes` 
                            SET `time_won` = (?) 
                            WHERE `id` = (?)";
                break;
        }


        
        $sql = $this->conn->prepare($query);
        $sql->bind_param("ss", $this->dateStamp, $ID);

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
     * TODO: allow arg to be passed in for variable time intervals
     * TODO: confirm get_result() call
     * * returns: 
        * * $data:array ["prize" = array ]
        * * $data:array ["error" = string]
    */
    public function retrieveAvailablePrize(){
        // REFACTOR - pull into util??
        $baseTime = Datetime::createFromFormat('Y-m-d H:i:s', $this->dateStamp);
        $baseTime = $baseTime->modify('-10 minutes');
        $endTime = $baseTime->format('Y-m-d H:i:s');
            
        $data = array(
            'prize' => null,
        );
    
        $query = "SELECT * FROM `Prizes` 
                    WHERE `time_claimed` IS NULL 
                    AND `time_won` BETWEEN (?) AND (?) 
                    OR `time_won` IS NULL 
                    LIMIT 1";
        

        $sql = $this->conn->prepare($query);
        $sql->bind_param("ss", $endTime, $this->dateStamp);
    
        $result = $sql -> execute();

            
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