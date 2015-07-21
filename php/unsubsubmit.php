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
	$putUrl = buildUrl(contacts_base_url . '/' . $contact["id"]);
	$response = httpRequest($putUrl, "PUT", getHeaders(), json_encode($contact));
	if ($response["info"]["http_code"] == 200) {
		outputToModal("unsubmodal", "Success", "The contact list subscription was modified successfully");
	} else {
		outputToModal("unsubmodal", "Error", "Something went wrong.");
	}
