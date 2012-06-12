<?php
class db_accessor{

    //connection information
    private $database;      // twitter
    private $username;      
    private $password;     
    private $host;         
	
	//keep the index
	private $index;

    // db connection
    private $conn;

    // open the db connection
    public function __construct($sethost, $setuser, $setpass, $setdatabase){

        $this->host = $sethost;
        $this->username = $setuser;
        $this->password = $setpass;
        $this->database = $setdatabase;

        $this->conn = mysql_connect($this->host, $this->username, $this->password)
            or die('ERROR: Could not connect to host: ' . mysql_error());

         mysql_select_db($this->database)
            or die ('ERROR: Could not enter database.');
    }

    // close the db connection
    public function __destruct() {
        mysql_close($this->conn);
    }

	//gets random tweets; return array of message_text_clean(s)
	public function get_random_tweets($limit, $user){
		$tweet_array = array();

		$tweet_limit =  $this->get_tweet_limit($limit, $user);
		if($tweet_limit == 0)
			return array();
		elseif($tweet_limit < $limit){
			$limit = $tweet_limit;
			$short_query = "SELECT db_id AS id, message_text AS message FROM leafingOut_training WHERE " . $user . "_num_ratings < 2;";
			$short_res = mysql_query($short_query);

			if(!$short_res){
				echo "ERROR (QUERY): " . mysql_error() . "\n";
			} else {
				$index = 0;
				while ($row = mysql_fetch_array($short_res)) {
					$tweet_array[$index] = array($row["id"], $row["message"]);
					$index++;
				}
			}
		} else {	
			$random_ids = $this->get_random_ids($limit, $user);

			for($index = 0; $index < $limit; $index++){
				$query = "SELECT db_id AS id, message_text AS message FROM leafingOut_training WHERE db_id=" . $random_ids[$index] . ";";
	  			$res = mysql_query($query);

				if(!$res){
					echo "ERROR (QUERY): " . mysql_error() . "\n";
					return false;
				} else {
			 		$row = mysql_fetch_object($res);
			    	$tweet_array[$index] = array($row->id, $row->message);
		    		//$tweet_array[$index] = mysql_fetch_object($res)->message;
				}
			}
		}
		return $tweet_array;
	}

	//finds the number of available tweets for this user
	private function get_tweet_limit($limit, $user){
		$limit_query = "SELECT COUNT(*) FROM leafingOut_training WHERE (" . $user . "_1 IS NULL OR " . $user . "_2 IS NULL);";
		$limit_response = mysql_query($limit_query);
		return mysql_result($limit_response, 0);
	}

	//gets random ids from table
	public function get_random_ids($limit, $user){
		$index = 0;
		while($index < $limit){
			$generated_id = mt_rand(1, 100);
			$query = "SELECT 1 FROM leafingOut_training WHERE (" . $user . "_1 IS NULL OR " . $user . "_2 IS NULL) AND db_id = " . $generated_id . ";";		
			$result = mysql_query($query);
			if(mysql_num_rows($result) > 0){
				$random_ids[$index] = $generated_id;	
				$random_ids = array_unique($random_ids);
				$index = sizeof($random_ids);
//print_r($random_ids);
//echo "\n";
			}
		}
		return $random_ids;
	}

	//writes ratings to db table
	public function write_ratings($id_array, $rating_array, $user){
		$query_prefix = "UPDATE leafingOut_training SET " . $user;
		
		$limit = sizeof($id_array);
	//echo $limit;
		for($index = 1; $index <= $limit; $index++){
		 	$section_done = FALSE;
			$write = TRUE;

			$current_id = $id_array[$index];
			$rating_no_query = "SELECT " . $user . "_num_ratings FROM leafingOut_training WHERE db_id=" . $current_id . ";";
			$rating_no = mysql_result(mysql_query($rating_no_query), 0);
			$query_suffix = "=" . $rating_array[$index] . ", " . $user . "_num_ratings=" . ($rating_no+1) . " WHERE db_id=" . $current_id . ";";
			$query = $query_prefix;

			switch($rating_no){
				case 0:
					$query .= "_1";
					break;
				case 1:
					$query .= "_2";
					$section_done = TRUE;
					break;
				default:
					$write = FALSE;
					break;
			}
			$query .= $query_suffix;
//testing
//echo $query;
			if($write){
				$result = mysql_query($query);
				//echo $index . ": " . $query . "<br/>";
				if(!$result){
					echo "ERROR (QUERY): " . mysql_error() . "\n";
					return false;
				}
			}

			if($section_done){
				$this->update_row($current_id, $user);
			}
		}
	}

	//update row for completeness
	private function update_row($id, $user){
		//update user_consistent column
		$consistency_query = "SELECT " . $user . "_1 AS result1, " . $user . "_2 AS result2 FROM leafingOut_training WHERE db_id=" . $id . ";";
		$result = mysql_query($consistency_query);
		$user_result = mysql_fetch_row($result);
		$res1 = $user_result[0];
		$res2 = $user_result[1];

		$update_user_query = "UPDATE leafingOut_training SET " . $user . "_consistent=";
		if($res1 == $res2){
			mysql_query($update_user_query . "1 WHERE db_id=" .  $id . ";");
		}else{
			mysql_query($update_user_query . "0 WHERE db_id=" . $id . ";");
		}

		//attempt to update finished column\
		$finished = TRUE;
		$user_array = array("ashley", "dylan", "ericy", "ericg", "nancy", "sophie");
		for($index = 0; $index < sizeof($user_array); $index++){
			$finished_query = "SELECT ".$user_array[$index]."_num_ratings FROM leafingOut_training WHERE db_id=".$id.";";
			$finished_result = mysql_query($finished_query);
			$result_row = mysql_fetch_row($finished_result);
			if($result_row[0] != 2){
				//echo $user_array[$index] . " not done.\n";
				$finished = FALSE;
				break;
			}
		}
		if($finished){
			mysql_query("UPDATE leafingOut_training SET finished_status=1 WHERE db_id=" . $id . ";");
		}
	}
}
?>
