<?php
	require_once("config.php");
	require_once("functions.php");
	
	//This function checks if the contact exists, and if not - creates the contact 
	function createContact(array $params = array())
    {
		//build a url using buildUrl function from functions.php
		//the last parameter indicates POST request (means the $params will be ignored)
		$postUrl = buildUrl(contacts_base_url, $params, true);
		
		//If getContact already exist in the specified list
		 if ($contact = getContact($params["email"])) {
			 $exists = false;
			 foreach($contact["lists"] as $value) {
				 if ($value["id"] == $params["list_id"]) {
					 $exists = true;
					 break;
				 }
			 }
			 if ($exists) {
				outputToModal("addmodal", "Epic Fail!", "This contact already exists in this list");
				exit;
			 } else {
				 $result = addContactToList($contact, $params["list_id"]);
				 if ($result == 200) {
					outputToModal("addmodal", "Success!", "You have successfully added a contact");
				 } else {
					 outputToModal("addmodal", "Error!", "Something went wrong!");
				 }
				 exit;
			 }
		 
		}
		//If first name was never passed as a parameter
		//This generally should not happen - form constraints should prevent it
		if (!array_key_exists("first_name", $params)) {
			outputToModal("addmodal", "Error!", "First name must be provided");
		//same as above except for email
		} else if (!array_key_exists("email", $params)) {
			outputToModal("addmodal", "Error!", "email must be provided");	

		//else go ahead with the creation
		} else {
			
			//first we build the array using the structure from the API documentation
			$arr = array("lists" => array(array("id" => "")),
						 "email_addresses" => array(array("email_address" => "")),
			             "first_name" => ""
			            );
						
			//then we populate the array with values from the $params array
			//this may be expanded to include more different values if the form expands
			//at this time it only takes email and first_name as parameters
			$arr["lists"][0]["id"] = $params["list_id"];
			$arr["email_addresses"][0]["email_address"] = $params["email"];
			$arr["first_name"] = $params["first_name"];
			
			//send the request and record the response
			//the array we constructed is encoded into JSON before being sent
			$response = httpRequest($postUrl, "POST", getHeaders(), json_encode($arr));
		
			//If the response code is anything other than 201 (Success)
			//The error checking may have to be made move verbose in the future
			if ($response["info"]["http_code"] != 201) {
				outputToModal("addmodal", "Error!", "Something went wrong!");
			} else {
				outputToModal("addmodal", "Success!", "You have successfully added a contact");
			}
		}
    }
	
	createContact($_POST);