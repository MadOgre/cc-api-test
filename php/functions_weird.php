<?php

    function outputToModal($modalId, $title, $body) {
		echo '<script>
					var $ = parent.$;
					$(parent.document).find("#' . $modalId . ' .modal-title").html("' . $title . '");
					$(parent.document).find("#' . $modalId . ' .modal-body").html("' . $body . '");
					$("#addmodal").modal();
			  </script>';
	}
    //this function takes a url (<http://someaddress.com>) and attaches parameters to it in the form http://someaddress.com?parm1=value1&parm2=value2
	//the API key is also added as the first parameter regardless
	function buildUrl($url, array $queryParams = null, $isPostRequest = false)
    {
		//api key is placed in a one element array by itself
		//!! This is where the API KEY will need to be changed if necessary !!
        $keyArr = array('api_key' => api_key);
		
		
		if ($isPostRequest) {
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
	
	function getContact($email) {
		$getUrl = buildUrl(contacts_base_url, array("email" => $email));
		$response = httpRequest($getUrl, "GET", getHeaders());
		$body = json_decode($response["body"], true);
		if (!empty($body["results"])) {//(array_key_exists("results", $body)) {
			return $body["results"][0]["id"];
		} else {
			return null;
		}
	}
	
	//this is one of the main functions to be called by the main script. It gets the contact information according to parameters (email etc..)
    ///REMOVED !!!

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
	
?>
