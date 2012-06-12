<?php
	session_start();
	include_once('db_accessor.php');
	
	$num_tweets_to_classify = 15;
	$msg = "";
	$msg_array_name = "msg_array";
	$id_array_name = "id_array";

	$accessor = new db_accessor('localhost', 'root', 'adam17', 'twitter');
	
	// WHAT TO DO IF NOT SUBMITTED YET
	if($_POST['Submitted'] != 'Submit'){
		$tweet_array = $accessor->get_random_tweets($num_tweets_to_classify, $_SESSION['user']);
		if(empty($tweet_array)){
			header("Location: completed.html");
		    exit;
		}
	}
	// WHAT TO DO AFTER SUBMISSION
	else if($_POST['Submitted'] == "Submit")
	{

//testing purposes		
//$msg = "hello submitted";
//print_r($_POST);
		$errorMessage = "";
		$msg_array = $_POST[$msg_array_name];
		$id_array = $_POST[$id_array_name];
//echo sizeof($id_array);
	
		// Relevant = 1, Irrelevant = 2, Inappropriate = 3
		$tweet_class_array = array();

		$index = 1;

		// check to make sure all the tweets were classified
		while($index <= sizeof($id_array)){
	
			$current_classification = "classification" . $index;
			
			if(empty($_POST[$current_classification])){
				$errorMessage .= "One or more of the tweets are unclassified: " . $index;
				break;
			} else {
				$tweet_class_array[$index] = $_POST[$current_classification];
			}
			$index += 1;
		}
		// if there were no errors (i.e. missing classifications in the form)	
		if(empty($errorMessage)) 
		{
//print_r($msg_array);
//print_r($id_array);
//print_r($tweet_class_array);
 			$accessor->write_ratings($id_array, $tweet_class_array, $_SESSION['user']);
			header("Location: thankyou.html");
			exit;
		}	
	}

?>
<html>
<head>
<title>User Rating</title>
</head>
<body LEFTMARGIN=50px>
<h2><center>Classify 'leafing out' Tweets</h2>
<br/>
<?php
//	if(!empty($msg)){
//		echo $msg;
//	}
	if(!empty($errorMessage)){
		echo $errorMessage;
		echo "<br/>";
		echo "Please use browser 'back' button and classify any tweets you may have missed." . "\n";
	}
?>
<br/>
<?php
	if($_POST['Submitted'] != 'Submit'){
		echo "<form name='classify_tweets' method='POST' action='home.php'><table border='1' align='center' cellpadding='10px'>
		<tr>
		<th>Index</th>
		<th>DB Index</th>
		<th width='400px'>Message Text</th>
		<th width='100px'>Relevant</th>
		<th width='100px'>Irrelevant</th>
		<th width='100px'>Inappropriate</th>
		</tr>";
	
		$index = 1;
		while($index <= sizeof($tweet_array)){
			$classification_name = "classification" . $index;
        	echo "<tr>";
        	echo "<td>" . $index . "</td>";
			echo "<td>" . $tweet_array[($index - 1)][0] . "</td>";
        	echo "<td>" . $tweet_array[($index - 1)][1] . "</td>";

        	echo "<td align='center'><input type='radio' name='" . $classification_name . "' value='1'></td>";
			echo "<td align='center'><input type='radio' name='" . $classification_name . "' value='2'></td>";
			echo "<td align='center'><input type='radio' name='" . $classification_name . "' value='3'></td>";
			echo "</tr>";

			echo "<input type='hidden' name='msg_array[" . $index . "]' value='" . htmlspecialchars($tweet_array[($index - 1)][1], ENT_QUOTES) . "'>";
			echo "<input type='hidden' name='id_array[" . $index . "]' value='". $tweet_array[($index-1)][0] . "'>";
			$index = $index + 1;
		}
		echo "</table><br>";  
		echo "<center><input type='submit' width='100px' name='Submitted' value='Submit'/></form>";
	}
?>
<br/>
</body>
</html>
