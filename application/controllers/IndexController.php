<?php

use Icinga\Web\Controller;
use Icinga\Web\Session;

class Opsgenie_IndexController extends Controller {
    public function indexAction()
    {
	$this->view->apikey = $this->Config()->get('opsgenie', 'api_key', 'No API key specified in config.');
	$this->view->duration = $this->Config()->get('opsgenie', 'duration', '24');
	$this->view->schedules = $this->Config()->get('opsgenie', 'schedule_names', 'No schedule names listed');
    }
    
    public function simulateAction(){
	$apiKey = $this->Config()->get('opsgenie', 'api_key', 'No API key specified in config.');
	$hours = $this->Config()->get('opsgenie', 'duration', '24');
	$emailDomain = $this->Config()->get('opsgenie', 'email_domain', 'example.com.au');

	$dateFormat = "Y-m-d\TH:i:s+11:00";

	$fromDate = date($dateFormat);
	$endDate = date($dateFormat, strtotime(sprintf("+%d hours", $hours)));

	$scheduleName = $_GET['schedule'];

	$username = Session::getSession()->get('user')->getUsername();
	
	$requestBody = '{
		"startDate":"'. $fromDate .'",
    	        "endDate":"'. $endDate .'",
    		"user": {
			"type": "user",
    	       	 	"username": "'. $username . '@' . $emailDomain . '"
    	     	}
	}';

	$opts = array('http' =>
	    array(
	        'method'  => 'POST',
	        'header'  => [
			'Content-type: application/json',
			'Authorization: GenieKey ' . $apiKey
		],
	        'content' => $requestBody
	    )
	);
	
	$context  = stream_context_create($opts);
	$url = "https://api.opsgenie.com/v2/schedules/$scheduleName/overrides?scheduleIdentifierType=name";

	$res = file_get_contents($url, false, $context);

	echo "<h1>OK</h1>";
	#echo ($result->code != 200 ? "Error code: $result->code" : NULL);
    }
}
