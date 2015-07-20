<?php

    //this function initializes and displays a modal window with a specific id
	//using the title and body parameters
	//the function works by outputting a script tag into the invisible iframe on the page
	//inside the script is jQuery code
    function outputToModal($modalId, $title, $body, $switchToActive = false) {
		
    $result = '<script>
					var $ = parent.$;
					$(parent.document).find("#' . $modalId . ' .modal-title").html("' . $title . '");
					$(parent.document).find("#' . $modalId . ' .modal-body").html("' . $body . '");
					';
	if ($switchToActive) {
		$result .= '$(parent.document).find("#proceedbtn").show();
		           $(parent.document).find("#cancelbtn").html("Cancel");
				   ';
	} else {
		$result .= '$(parent.document).find("#proceedbtn").hide();
					$(parent.document).find("#cancelbtn").show();
		           $(parent.document).find("#cancelbtn").html("Close");
				   ';
	}
		$result .= '$("#' . $modalId . '").modal();
			  </script>';
		echo $result;
	}
    //this function takes a url (<http://someaddress.com>)
	//and attaches parameters to it in the form http://someaddress.com?parm1=value1&parm2=value2
	//the API key is also added as the first parameter regardless
	function buildUrl($url, array $queryParams = null, $isPostOrPutRequest = false)
    {
		//api key is placed in a one element array by itself
		//!! This is where the API KEY will need to be changed if necessary !!
        $keyArr = array('api_key' => api_key);
		
	    //if the request is post (not get) 
		if ($isPostOrPutRequest) {
			//attach this parameter to indicate that the user added himself as opposed to the list owner
			$keyArr['action_by'] = "ACTION_BY_VISITOR";
		} else {
		//if any parameters were passed...
			if ($queryParams) {
				//merge the api key array and parameter array into one temporary array $params
				$keyArr = array_merge($keyArr, $queryParams);
			}
		}
		//construct and return the final url as a string by adding '?' and then the parameters separated by & (this is what http_build_query does)
		//first argument is the list of parameters, the second is a numeric prefix (not needed here), the third is the separator
        return $url . '?' . http_build_query($keyArr, '', '&');
    }
	
	//this function retrieves the complete contact information by email
	function getContact($email){
		//builds the url (refer to buildUrl function) usung only the url as a single query parameter
		//parameter must be passed as an array of 1 element
		//contacts_base_url is a constant defined in config.php
		$getUrl = buildUrl(contacts_base_url, array("email" => $email));

		//send the actual request and receive the response (refer to httpRequest function)
		$response = httpRequest($getUrl, "GET", getHeaders());
		
		//decode the body part of the response fron json into an array
		//the second argument indicates that we want the result returned as an associative array
		$body = json_decode($response["body"], true);
		
		//if the body's result array is empty return null, otherwise return the first result
		//this returns only the contact object
		if (!empty($body["results"])) {
			return $body["results"][0];
		} else {
			return null;
		}	
	}
	
	
	//this function extracts and returns an id from a contact object
	//it returns null if the contact passed is also null
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
	
	//this function retrieves all contact lists from the user's account in the associative array format
	function getAllContactLists() {
		
		//build a url
		$getUrl = buildUrl(lists_base_url);
		
		//send a request, save the result in responce
		$response = httpRequest($getUrl, "GET", getHeaders());
		
		//decode the body part of the response from json, stripping away all technical details
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

	//this function takes an access token and constructs the array of headers to be used for any request
	//this array has a weird format due to weirdness of curl...
	//it's simply a list of plaintext strings with property and value separated by a colon like css rules 
	function getHeaders()
    {
        return array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . access_token
        );
    }
	
	//This function has the actual code that uses curl to call the api using all the necessary information
	//CHANGE BOTH CUSTOM HEADERS FROM "test php script" TO SOMETHING INFORMATIVE WHEN INTEGRATING
	function httpRequest($url, $method, array $headers = array(), $payload = null)
    {
        //adding the version header to the existing headers, change the placeholder to anything.
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
		//this can be set to anything
        curl_setopt($curl, CURLOPT_USERAGENT, "test php script");
		
		//this option is to stop it from verifying the SSL certificate when using https (set to 1 to verify)
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		
		//this options sets all the headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
		//this option sets the method (GET, POST etc.)
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		
		if ($payload) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }
		
		//create a blank response array
        $response = Array();
		
		//execute the curl !!DINGDINGDING!! and save the response into the data part of the response array
		$response["body"] = curl_exec($curl);
		
		//save the various technical info about the curl execution (http code, headers, etc..etc..etc..)
		$response["info"] = curl_getinfo($curl);
		
		//save any error info if any
		$response["error"] = curl_error($curl);
		
		//close the curl
        curl_close($curl);
        
		//duh
        return $response;
    }

	//this function adds a specified list to the contact's subscriptions
	//contact must exist, listId must be must exist
	function addContactToList($contact, $listId) {
		$contact["lists"][] = array("id" => $listId);
		$putUrl = buildUrl(contacts_base_url . '/' . $contact["id"]);
		$response = httpRequest($putUrl, "PUT", getHeaders(), json_encode($contact));
		echo '\n' . $response["info"]["http_code"];
		var_dump($contact);
		return $response["info"]["http_code"];
	}