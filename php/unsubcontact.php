<?php
	session_start();
	require_once("config.php");
	require_once("functions.php");
	$contact = getContact($_POST["email"]);
	$_SESSION["contact"] = $contact;
	if (!empty($contact) && !empty($contact["lists"])) {
		echo 'First Name: ' . $contact["first_name"] . "<br>"; //DEBUG ONLY
		echo "Lists:<br>";
		$lists = getAllContactLists();
		$checkboxes = '<form id=\"listselect\" action=\"php/unsubsubmit.php\" ' .
             			'target=\"addiframe\" method=\"post\">';
		foreach ($contact["lists"] as $value) {
			$checkboxes .= '<div class=\"checkbox\">' .
						'<label><input type=checkbox name=' .
						$value["id"] . ' value=' . $value["id"] . '>' .
						$lists[$value["id"]] . '</label></div>';
		}
		$checkboxes .= '</form>';
		$modalHTML = 'Please check the lists you wish to be removed from and click \"Proceed\"<br><br>';
		$modalHTML .= $checkboxes;
		outputToModal("unsubmodal", "Unsubscribe...", $modalHTML, true);
		var_dump($contact);
	} else {
		outputToModal("unsubmodal", "Unable to unsubscribe", "The email you specified does not belong to any lists");
	}