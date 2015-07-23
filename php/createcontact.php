<?php

/**
 *  @file		createcontact.php
 *  @brief		This script is used to subscribe a visitor to a mailing list
 *  @author		Victoria Kariolic
 *  @author		Michael Semko
 *  @package	cc-api-test
 *  
 *  @details	This script receives a "first_name", "email", and "list_id" 
 *  			via POST from the web form and adds the visitor to the mailing list
 *  			if possible. Modal window is used for output.
 */
	require_once("config.php");
	require_once("functions.php"); 

	//build a URL using buildUrl function from functions.php
	$postUrl = buildUrl(contacts_base_url);
	
	//If the contact already exist as one of the owner's contacts...
	if ($contact = getContact($_POST["email"])) {

		//compare each of the contact's lists to the list_id passed
		$exists = false;
		foreach($contact["lists"] as $value) {
			if ($value["id"] == $_POST["list_id"]) {
				$exists = true;
				break;
			}
		}

		//if the contact is already subscribed to a list
		if ($exists) {
			outputToModal("addmodal", "Epic Fail!", "This contact already exists in this list");
			exit;
			
		//contact exists but is not subscribed to the list
		} else {
			
			//attempt to add to list
			$result = addContactToList($contact, $_POST["list_id"]);

			//check for success
			if ($result == 200) {
				outputToModal("addmodal", "Success!", "You have successfully added a contact");
			} else {
				outputToModal("addmodal", "Error", "Something went wrong! (code: " . $result . ")");
			}
			exit;
		}
	}

	//if we got here, the contact did not exist
	
	//If first name was never passed as a parameter
	//This generally should not happen - form constraints should prevent it
	if (!array_key_exists("first_name", $_POST)) {
		outputToModal("addmodal", "Error!", "First name must be provided");
	//same as above except for email
	} else if (!array_key_exists("email", $_POST)) {
		outputToModal("addmodal", "Error!", "email must be provided");	

	//go ahead with the creation
	} else {
		
		//first we build the array using the structure from the API documentation
		$arr = array("lists" => array(array("id" => "")),
					 "email_addresses" => array(array("email_address" => "")),
		             "first_name" => ""
		            );
					
		//then we populate the array with values from the $_POST array
		//this may be expanded to include more different values if the form expands
		//at this time it only takes email and first_name as parameters
		$arr["lists"][0]["id"] = $_POST["list_id"];
		$arr["email_addresses"][0]["email_address"] = $_POST["email"];
		$arr["first_name"] = $_POST["first_name"];
		
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