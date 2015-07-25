<?php

/**
 *  @file		functions.php
 *  @brief		Functions to be used to access the ConstantContact API
 *  @author		Victoria Kariolic
 *  @author		Michael Semko
 *  @package	cc-api-test
 */
 
    /**
    *  @brief						Initializes and displays a modal window with a specific id
    *  
    *  @param [in] $modalId        	The HTML id of the modal element
    *  @param [in] $title          	HTML to display in the title element of the modal
    *  @param [in] $body           	HTML to display in the body section of the modal
    *  @param [in] $switchToActive 	Setting this to true will alter the buttons on the modal
    *  
    *  @return						None
    *  
    *  @details						The function works by outputting a script tag into the
    *  								invisible iframe on the page. Inside the script is jQuery code
    */
    function outputToModal($modalId, $title, $body, $switchToActive = false) {
		
		//Beginning to build a result variable which will contain the final code
		//This prepares the title and body parts of the modal
		//The line var $ = parent.$; enables jQuery to work in the iframe
		//after that jQuery selectors may be used via $(parent.document).find(...
		$result = '<script>
					var $ = parent.$;
					$(parent.document).find("#' . $modalId . ' .modal-title").html("' . $title . '");
					$(parent.document).find("#' . $modalId . ' .modal-body").html("' . $body . '");
					';
		
		//If the active flag is set, the modal shows the proceed and cancel buttons
		if ($switchToActive) {
		$result .= '$(parent.document).find("#proceedbtn").show();
					$(parent.document).find("#cancelbtn").html("Cancel");
					';
		
		//...otherwise, the proceed button is hidden and cancel button caption becomes "close"
		} else {
		$result .= '$(parent.document).find("#proceedbtn").hide();
					$(parent.document).find("#cancelbtn").show();
					$(parent.document).find("#cancelbtn").html("Close");
				   ';
		}
		
		//finally this code is added to activate the modal
		$result .= '$("#' . $modalId . '").modal();
				</script>';

		//the code is output into the iframe (and immediately executes)
		echo $result;
	}
	
	/**
	 *  @brief 						Builds and returns a URL to be used in for HTTP request
	 *  
	 *  @param [in] $url          	The base URL of the API endpoint (should be stored in the config file)
	 *  @param [in] $isGetRequest 	Setting this to true indicates the request will be a GET request
	 *  @param [in] $queryParams 	An array of parameters to attach to the GET request
	 *  
	 *  @return string				Complete URL to be passed to httpRequest
	 *  
	 *  @details					This function takes a base URL and attaches to it the API key
	 *  							followed by "action_by" parameter for non-GET requests or
	 *  							a supplied array of parameters $queryParams for GET requests
	 *  							The API key is stored in config.php file
	 */
	function buildUrl($url, $isGetRequest = false, array $queryParams = null) {
		
		//api key is placed in a one element array by itself
        $keyArr = array('api_key' => api_key);
		
	    //if the request is not get 
		if (!$isGetRequest) {
			
			//attach this parameter to indicate that the action is 
			//performed by the user and not the list owner
			//this will be logged by CC and will also cause the auto-responder message to be sent
			$keyArr['action_by'] = "ACTION_BY_VISITOR";
		} else {
			
			//if any parameters were passed...
			if ($queryParams) {
				
				//add the contents of $queryParams to $keyArr
				$keyArr = array_merge($keyArr, $queryParams);
			}
		}
		
		//construct and return the final URL as a string by adding '?' and then the parameters
		//separated by & (this is what http_build_query does)
		//first argument is the list of parameters,
		//the second is a numeric prefix (not needed here), the third is the separator
        return $url . '?' . http_build_query($keyArr, '', '&');
    }
	
	/**
	 *  @brief						Uses the email address to retrieve the complete information for a contact
	 *  
	 *  @param [in] $email 			Contact's email address
	 *  
	 *  @return	array				The complete contact data structure in associative array format
	 */
	function getContact($email){
		
		//build the URL (refer to buildUrl function) using only the email as a single query parameter
		//parameter must be passed as an array of 1 element
		//contacts_base_url is a constant defined in config.php
		$getUrl = buildUrl(contacts_base_url, true, array("email" => $email));
		
		//send the actual request and receive the response (refer to httpRequest function)
		$response = httpRequest($getUrl, "GET", getHeaders());
		
		//decode the body part of the response from JSON into an associative array
		//the second argument indicates that we want the result returned as an associative array
		$body = json_decode($response["body"], true);
		
		//if the body's result array is empty - return null, otherwise return the first result
		//this returns only the contact object, and not the rest of the technical information
		//obtained by httpRequest
		if (!empty($body["results"])) {
			return $body["results"][0];
		} else {
			return null;
		}	
	}
	
	/**
	 *  @brief 						Extracts a contact id from a contact array
	 *  
	 *  @param [in] $contact 		Contact array as returned by getContact
	 *  
	 *  @return integer             Contact ID
	 *  
	 *  @details					If $contact parameter is null the function returns null
	 *  							otherwise the contact ID is returned
	 */
	function getContactId($contact) {
		
		//if $contact exists
		if ($contact) {	
		
			//retrieve and return the id
			return $contact["id"];
		} else {
			
			//or not
			return null;
		}
	}
	
	/**
	 *  @brief 						Retrieves and returns an array of all list IDs and names
	 *  
	 *  @return array				List IDs and names in an associative array ("id" => "name")
	 *  
	 *  @details Details			Retrieves all list IDs and names from the owner's ConstantContact account
	 */
	function getAllContactLists() {
		
		//build the URL (refer to buildUrl function)
		//lists_base_url is a constant defined in config.php
		$getUrl = buildUrl(lists_base_url, true);
		
		//send the actual request and receive the response (refer to httpRequest function)
		$response = httpRequest($getUrl, "GET", getHeaders());
		
		//decode the body part of the response from JSON into an associative array
		//the second argument indicates that we want the result returned as an associative array
		$lists = json_decode($response["body"], true);
		
		//prepare an array
		$result = array();
		
		//convert the array into an array with id => name pairs stripping away the rest of the info
		foreach ($lists as $value) {
			$result[$value["id"]] = $value["name"];
		}
		
		//return the resulting array
		return $result;
	}
	
	/**
	 *  @brief 						Constructs and returns an array of headers used for all HTTP requests	
	 *  
	 *  @return 					None
	 *  
	 *  @details					Returns an ordered array or headers as per API documentation for CC
	 *  							The array is a list of strings that each contain a <header>: <value> pair
	 *  							This function uses an access token found in config.php
	 */
	function getHeaders() {
        return array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . access_token
        );
    }
	
	/**
	 *  @brief 						Sends an HTTP request via CURL and returns a response
	 *  
	 *  @param [in] $url 			URL to send request to
	 *  @param [in] $method 		HTTP method to use (GET, POST, PUT etc...)
	 *  @param [in] $headers		HTTP headers to use (usually generated by getHeaders)
	 *  @param [in] $payload        Data (if any) to be transmitted with the request
	 *  
	 *  @return array				Array containing a response, and any relevant technical information
	 *  							$response["body"] will contain the actual response (encoded as JSON)
	 *  							$response["info"] will contain the technical data
	 *  							$response["error"] will contain any relevant error information
	 *  
	 *  @details					This function forms and executes a CURL request using the
	 *  							specified URL and the specified HTTP method. All resulting information,
	 *  							including the response itself and any technical and error data is stored
	 *  							in the response array and returned.
	 */
	function httpRequest($url, $method, array $headers = array(), $payload = null) {
        
		//adding the version header to the existing headers, change the value to anything.
		//This will go into http log of ConstantContact, change to client site name instead of 'test php script'
        $headers[] = 'x-ctct-request-source: test php script';
        
		//initialize the curl object
        $curl = curl_init();
		
		//set the destination url
        curl_setopt($curl, CURLOPT_URL, $url);
		
		//this option makes it not return the header with the result (change to 1 if needed)
        curl_setopt($curl, CURLOPT_HEADER, 0);
		
		//this option makes it return the result as a string (which is what we want),
		//if set to 0 it would print the result out directly
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		//this option sets to user agent header, this should be a string identifying the software being used,
		//this can be set to anything (php version or server version)
        curl_setopt($curl, CURLOPT_USERAGENT, "test php script");
		
		//this option is to stop it from verifying the SSL certificate when using https (set to 1 to verify)
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		
		//this options sets all the headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
		//this option sets the method (GET, POST etc.)
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		
		//if payload exists - attach it to the request
		if ($payload) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }
		
		//create a blank response array
        $response = array();
		
		//execute the curl !!DINGDINGDING!! and save the response into the data part of the response array
		$response["body"] = curl_exec($curl);
		
		//save the various technical info about the curl execution (http code, headers, etc..etc..etc..)
		$response["info"] = curl_getinfo($curl);
		
		//save any error info if any
		$response["error"] = curl_error($curl);
		
		//close the curl
        curl_close($curl);
        
        return $response;
    }

	/**
	 *  @brief						Adds a specified list to the contact's subscriptions (uses httpRequest)
	 *  
	 *  @param [in] $contact		Contact array as returned by getContact (must exist)
	 *  @param [in] $listId			The ID of the contact list to be added to the contact's data (must exist)
	 *  
	 *  @return	integer				HTTP code returned by the server
	 *  
	 *  @details Details
	 */
	function addContactToList($contact, $listId) {
		
		//add the specified list to the contact's list array
		//each list is also an array with "id" element storing the id of the list
		$contact["lists"][] = array("id" => $listId);
		
		//build the URL (refer to buildUrl function)
		//contacts_base_url is a constant defined in config.php
		//this url needs to include a contact id as part of the path
		$putUrl = buildUrl(contacts_base_url . '/' . $contact["id"]);
		
		//execute an HTTP request and return a response (refer to httpRequest function)
		//the contact array is passed as a payload in JSON format
		$response = httpRequest($putUrl, "PUT", getHeaders(), json_encode($contact));
		
		//return only the http code from the $response array
		return $response["info"]["http_code"];
	}
	