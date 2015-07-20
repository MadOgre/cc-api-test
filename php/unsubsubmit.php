<?php
	session_start();
	require_once("config.php");
	require_once("functions.php");
	//outputToModal("unsubmodal", "", "Processing...");
	$lists = array();
	foreach ($_POST as $value) {
		$lists[] = array("id" => $value);
	}
	$contact = $_SESSION["contact"];
	$contact["lists"] = $lists;
	$putUrl = buildUrl(contacts_base_url . '/' . $contact["id"]);
	$response = httpRequest($putUrl, "PUT", getHeaders(), json_encode($contact));
	echo '\n' . $response["info"]["http_code"];
	var_dump($contact);
	if ($response["info"]["http_code"] == 200) {
		outputToModal("unsubmodal", "Success", "The contact list subscription was modified successfully");
	} else {
		outputToModal("unsubmodal", "Error", "Something went wrong.");
	}
