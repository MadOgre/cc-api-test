<?php
	session_start();
	require_once("config.php");
	require_once("functions.php");
	$contact = $_SESSION["contact"];
	$lists = $contact["lists"];
	if (empty($_POST)) {
		outputToModal("unsubmodal", "Nothing to do", "You have not selected any lists to unsubscribe from.");
		exit;
	}
	$lists = array_values(array_filter($lists, function($listStruct){
		return !in_array($listStruct["id"], $_POST);
	}));
	$contact["lists"] = $lists;
	
	//the "true" argument is necessary due to API behavior
	//if no lists are supplied the action must be ACTION_BY_OWNER
	//for this reason the GET-style url is used
	$putUrl = buildUrl(contacts_base_url . '/' . $contact["id"], true);
	
	$response = httpRequest($putUrl, "PUT", getHeaders(), json_encode($contact));
	var_dump($response["info"]);
	if ($response["info"]["http_code"] == 200) {
		outputToModal("unsubmodal", "Success", "The contact list subscription was modified successfully");
	} else {
		outputToModal("unsubmodal", "Error", "Something went wrong (code: " . $response["info"]["http_code"] . ")");
	}
