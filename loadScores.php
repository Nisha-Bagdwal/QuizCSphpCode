<?php
require_once 'include/DB_Functions.php';
$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['email'])) {
 
    // receiving the post params
    $email = $_POST['email'];
 
    // get the user by email and password
    $task = $db->loadScoresByEmail($email);
	
	if($task!=false){
		$response["error"] = FALSE;
		$response["tasks"]["algo"] = $task["algo"];
		$response["tasks"]["cd"] = $task["cd"];
		$response["tasks"]["cn"] = $task["cn"];
		$response["tasks"]["co"] = $task["co"];
		$response["tasks"]["datas"] = $task["datas"];
		$response["tasks"]["dbms"] = $task["dbms"];
		$response["tasks"]["diss"] = $task["diss"];
		$response["tasks"]["flat"] = $task["flat"];
		$response["tasks"]["micro"] = $task["micro"];
		$response["tasks"]["os"] = $task["os"];
		$response["tasks"]["pl"] = $task["pl"];
		$response["tasks"]["score"] = $task["score"];
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