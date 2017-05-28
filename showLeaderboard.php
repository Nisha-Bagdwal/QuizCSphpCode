<?php
require_once 'include/DB_Functions.php';
$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['email'])) {
 
    // receiving the post params
    $email = $_POST['email'];
 
    // get the user by email and password
	$result=$db->getRankByEmail($email);
	
	if($result!=false){
		$response["error"] = FALSE;
		$response["user"]["score"] = $result["score"];
		$response["user"]["rank"] = $result["rank"];
	}
	
    $result = $db->showLeaderboard();
	
	if($result!=false){
		$response["error"] = false;
        $response["tasks"] = array();
		while ($task = $result->fetch_assoc()) {
			$tmp = array();
			$tmp["name"] = $task["name"];
			$tmp["email"] = $task["email"];
			$tmp["score"] = $task["score"];
	
			array_push($response["tasks"], $tmp);
		}
		echo json_encode($response);
	}else{
		$response["error"] = TRUE;
        $response["error_msg"] = "No Scores Available!";
        echo json_encode($response);
	}
 
} else {
    // required post params is missing
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameters are missing!";
    echo json_encode($response);
}
?>