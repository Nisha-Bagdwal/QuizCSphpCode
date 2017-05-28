<?php
require_once 'include/DB_Functions.php';
$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['subject'])&& isset($_POST['id'])) {
 
    // receiving the post params
    $subject = $_POST['subject'];
	$id = $_POST['id'];
 
    // get the user by email and password
    $result = $db->loadQuestionsBySubject($subject,$id);
	
	if($result!=false){
		$task = $result->fetch_assoc();
		$response["error"] = FALSE;
		$response["tasks"]["ques"] = $task["ques"];
		$response["tasks"]["op1"] = $task["op1"];
		$response["tasks"]["op2"] = $task["op2"];
		$response["tasks"]["op3"] = $task["op3"];
		$response["tasks"]["op4"] = $task["op4"];
		$response["tasks"]["ans"] = $task["ans"];
		echo json_encode($response);
	}else{
		$response["error"] = TRUE;
        $response["error_msg"] = "No questions available!";
        echo json_encode($response);
	}
 
} else {
    // required post params is missing
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameter subject is missing!";
    echo json_encode($response);
}
?>