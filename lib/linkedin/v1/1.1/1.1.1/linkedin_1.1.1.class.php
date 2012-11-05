<?php

/**
 * This file defines the 'linkedin' class.  This class is designed to be a 
 * simple, stand-alone implementation of the most-used LinkedIn API functions.
 * 
 * COPYRIGHT:
 *   
 * Copyright (C) 2010 Paul Mennega <pmmenneg@gmail.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a 
 * copy of this software and associated documentation files (the "Software"), 
 * to deal in the Software without restriction, including without limitation 
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.  
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS 
 * IN THE SOFTWARE.  
 *
 * REQUIREMENTS:
 * 
 * You must have cURL installed on the server and available to PHP. 
 *    
 * QUICK START:
 * 
 * There are two files needed to enable LinkedIn API functionality from PHP; the
 * stand-alone OAuth library, and this LinkedIn class.  The latest version of 
 * the OAuth library can be found on Google Code:
 * 
 * http://code.google.com/p/oauth/
 *   
 * Install these two files on your server in a location that is accessible to 
 * the scripts you wish to use them in.  Make sure to change the file 
 * permissions such that your web server can read the files.
 * 
 * Next, change three lines below.  First, make sure the path to the oauth 
 * library is correct (you can change this as needed, dpending on your file
 * organization scheme, etc).  Second, change the two API related class 
 * constants, _API_KEY and _API_SECRET to match your LinkedIn application 
 * key-pair.
 * 
 * Finally, test the class by attempting to connect to LinkedIn.  In future 
 * versions of this class, I will include testing scripts, etc.                 
 *   
 * RESOURCES:
 *    
 * LinkedIn API Documentation from developer.linkedin.com
 * Profile API:					      http://developer.linkedin.com/docs/DOC-1002
 * Field Selectors:				    http://developer.linkedin.com/docs/DOC-1014
 * Profile Fields:				    http://developer.linkedin.com/docs/DOC-1061
 * Post Network Update:		    http://developer.linkedin.com/docs/DOC-1009
 * Messaging:					        http://developer.linkedin.com/docs/DOC-1044
 * Comments Network Updates:	http://developer.linkedin.com/docs/DOC-1043
 * Get Network Updates:			  http://developer.linkedin.com/docs/DOC-1006
 * Invitation API:				    http://developer.linkedin.com/docs/DOC-1012
 * Connections API:				    http://developer.linkedin.com/docs/DOC-1004
 * Status Update API:			    http://developer.linkedin.com/docs/DOC-1007
 * Search API:					      http://developer.linkedin.com/docs/DOC-1005
 * Industry Codes:				    http://developer.linkedin.com/docs/DOC-1011
 * Throttle Limits:           http://developer.linkedin.com/docs/DOC-1112
 *                            http://developer.linkedin.com/message/4626#4626
 *                            http://developer.linkedin.com/message/3193#3193 
 *    
 * @version   1.1.1 - 07/06/2010
 * @author    Paul Mennega <pmmenneg@gmail.com>
 * @copyright Copyright 2010, Paul Mennega 
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License 
 */
require_once('class/oauth/oauth.class.php');

/**
 * 'LinkedInException' class declaration.
 *  
 * This class extends the base 'Exception' class.
 * 
 * @access  public
 * @package classpackage
 */
class LinkedInException extends Exception {}

/**
 * 'linked' class declaration.
 *  
 * This class provides generalized LinkedIn oauth functionality.
 * 
 * @access  public
 * @package classpackage
 */
class linkedin {
  const _API_KEY        = '<your LinkedIn API key here>';
  const _API_SECRET     = '<your LinkedIn API secret here>';
  
  const _NETWORK_HTML   = '<a>';
  
  const _STATUS_LENGTH  = 140;
  
	const _URL_ACCESS     = 'https://www.linkedin.com/uas/oauth/accessToken';
	const _URL_API        = 'https://api.linkedin.com';
	const _URL_AUTH       = 'https://www.linkedin.com/uas/oauth/authorize?oauth_token=';
	const _URL_REQUEST    = 'https://www.linkedin.com/uas/oauth/requestToken';
	const _URL_REVOKE     = 'https://www.linkedin.com/uas/oauth/invalidateToken';

  public $auth, $consumer, $method;
  
  protected $callback;
  protected $token_access, $token_request;
  
	/**
	 * Create a linkedin object, used for oauth-based authentication and 
	 * communication with the LinkedIn API.	 
	 * 
	 * @param    str   $api_key        The key to access the Linkedin API.
	 * @param    str   $api_secret     The secret to access the Linkedin API.
	 * @param    str   $callback_url   [OPTIONAL] The URL to return the user to.
	 * @return   obj                   A new dealsheet linkedin object.	 
	 */
	public function __construct($api_key = self::_API_KEY, $api_secret = self::_API_SECRET, $callback_url = NULL) {
		$this->consumer = new OAuthConsumer($api_key, $api_secret, $callback_url);		
		$this->method   = new OAuthSignatureMethod_HMAC_SHA1();
		$this->set_callback($callback_url);
	}
	
	/**
   * The class destructor.
   * 
   * Explicitly clears linkedin object from memory upon destruction.
	 */
  public function __destruct() {
    unset($this);
	}
	
	/**
	 * Get the callback property.
	 * 
	 * @return   str                   The callback url.       	 
	 */
	public function get_callback() {
	  return $this->callback;
	}
  
	/**
	 * Get the token_access property.
	 * 
	 * @return   arr                   The access token.       	 
	 */
	public function get_token_access() {
	  return $this->token_access;
	}
	
	/**
	 * Get the token_request property.
	 * 
	 * @return   arr                   The request token.       	 
	 */
	public function get_token_request() {
	  return $this->token_request;
	}
	
	/**
	 * Checks the passed LinkedIn response to see if we have hit a throttling limit.	 
	 * 
	 * @param    arr     $response     The LinkedIn response.         	 
	 * @return   bool                  TRUE/FALSE depending on content of response.                  
	 */
	public static function is_throttled($response) {
	  // set the default
	  $return_data = FALSE;
    
    // check the variable
	  if(is_array($response)) {
	    // we have an array
	    if(array_key_exists('linkedin', $response)) {
	      // we have a properly formatted linkedin response
	       
        // store the response in a temp variable
        $temp_response = self::xml_to_array($response['linkedin']);
    	  
    	  // check to see if we have an error
      	if(array_key_exists('error', $temp_response)) {
      	  // we do, check for 403 code
      	  if($temp_response['error']['children']['status']['content'] == 403) {
      	    // we have it, check for throttle error
      	    if(preg_match('/throttle/i', $temp_response['error']['children']['message']['content'])) {
      	      // we have hit a throttle limit
      	      $return_data = TRUE;
      	    }
      	  }
    	  }
    	}
  	}
  	return $return_data;
	}
	
	/**
	 * General profile retrieval function.
	 * 
	 * Takes an array of parameters as input, formats the data and requests a data 
	 * retrieval from the Linkedin Profile API.      	 
	 * 
	 * @param    str     $options      An array of data options.	 
	 * @return   xml                   XML formatted data.
	 */
	public function profile($options = '~') {
		// start formatting query
    $query = self::_URL_API . '/v1/people/' . $options;
		return $this->request('GET', $query);
	}
	
	/**
	 * General data send/request method.
	 * 
	 * @param    str     $method       The data communication method.	 
	 * @param    str     $url          The Linkedin API endpoint to connect with.
	 * @param    str     $data         [OPTIONAL] The data to send via to LinkedIn.	 
	 * @return   xml      	           XML formatted response.
	 */
	protected function request($method, $url, $data = NULL) {
	  try {
  		$oauth_req = OAuthRequest::from_consumer_and_token($this->consumer, $this->get_token_access(), $method, $url);
      $oauth_req->sign_request($this->method, $this->consumer, $this->get_token_access());
      switch($method) {
        case 'DELETE':
        case 'GET':
          return self::send_request($oauth_req, $url, $method);
          break;
        case 'POST':
        case 'PUT':
          return self::send_request($oauth_req, $url, $method, $data);
          break;
      }
		} catch(LinkedInException $e) {
      // linkedin exception raised
      throw new LinkedInException('LinkedIn exception caught: ' . $e->getMessage());
    } catch(OAuthException $e) {
      // oauth exception raised
      throw new LinkedInException('OAuth exception caught: ' . $e->getMessage());
    }
	}
	
	/**
	 * Revoke the current user's access token, clear the access token's from 
	 * current linkedin object.  The current documentation for this feature is 
	 * found in a blog entry from April 29th, 2010:
	 * 
	 * http://developer.linkedin.com/community/apis/blog/2010/04/29/oauth--now-for-authentication	 
	 * 
	 * @return   mix                   TRUE if user tokens/access revoked, LinkedIn response if revocation failed, OAuth error if exception raised.   	 
	 */
	public function revoke() {
	  try {
	    // create oauth components of request
  	  $oauth_req = OAuthRequest::from_consumer_and_token($this->consumer, $this->get_token_access(), 'GET', self::_URL_REVOKE);
      $oauth_req->sign_request($this->method, $this->consumer, $this->get_token_access());
  	  
  	  // send request
  	  $response = self::send_request($oauth_req, LINKEDIN::_URL_REVOKE, 'GET');
  	  
  	  /**
  	   * Check for successful revocation (a 200 response from LinkedIn server) 
  	   * per the documentation linked in method comments above.
  	   */                	  
  	  if($response['info']['http_code'] == 200) {
        // revocation successful, clear object's request/access tokens
        $this->set_token_access(NULL);
        $this->set_token_request(NULL);
        $return_data = TRUE;
      } else {
        $return_data = $response;
      }
      return $return_data;
    } catch(LinkedInException $e) {
      // linkedin exception raised
      throw new LinkedInException('LinkedIn exception caught: ' . $e->getMessage());
    } catch(OAuthException $e) {
      // oauth exception raised
      throw new LinkedInException('OAuth exception caught: ' . $e->getMessage());
    }
	}
	
	/**
	 * Static Linkedin curl specific method, returning response:
	 * 
	 * array(
	 *   'linkedin'  => LinkedIn response,
	 *   'info'      =>	Connection information 
	 * )   	 
	 * 
	 * @param    obj     $request      The oauth request object to use.
	 * @param    str     $url          Url to send data to.
	 * @param    str     $method       Http protocol method.
	 * @param    str     $data         [OPTIONAL] Data to send with the request.         	 
	 * @return   arr                   A array containing the LinkedIn response and the connection information.                  
	 */
	protected static function send_request($request, $url, $method, $data = NULL) {
	  // check for cURL
	  if(extension_loaded('curl')) {
      // start cURL, checking for a successful initiation
      if($handle = curl_init()) {
        // set cURL options, based on parameters passed
  	    curl_setopt($handle, CURLOPT_HEADER, 0);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($handle, CURLOPT_URL, $url);
        
        // check the method we are using to communicate with LinkedIn
        switch($method) {
          case 'DELETE':
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
            break;
          case 'POST':
          case 'PUT':
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
            break;
        }
        
        // check if we are sending data to LinkedIn 
        if(is_null($data)) {
          curl_setopt($handle, CURLOPT_HTTPHEADER, array(
            $request->to_header()
          ));
        } else {
          curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
          curl_setopt($handle, CURLOPT_HTTPHEADER, array(
            $request->to_header(), 
            'Content-Type: text/xml; charset=UTF-8'
          ));
        }
        
        // gather the response
        $return_data['linkedin']  = curl_exec($handle);
        $return_data['info']      = curl_getinfo($handle);
        
        // close cURL connection
        curl_close($handle);
        
        // no exceptions thrown, return the data
        return $return_data;
      } else {
        // cURL failed to start
        throw new LinkedInException('cURL did not initialize properly.');
      }
    } else {
      // cURL not present
      throw new LinkedInException('PHP cURL extension does not appear to be loaded/present.');
    }
	}
	
	/**
	 * Set the callback property.
	 * 
	 * @param   str    $url        The callback url.       	 
	 */
	public function set_callback($url) {
	  $this->callback = $url;
	}
	
	/**
	 * Set the token_access property.
	 * 
	 * @return   arr    $token_access  [OPTIONAL] The LinkedIn oauth access token. 
	 * @return   bool                  TRUE on success, FALSE if oauth generates an exception.      	 
	 */
	public function set_token_access($token_access = NULL) {
    $return_data = TRUE;
    if(is_null($token_access)) {
	    // null value passed, set the token to null
	    $this->token_access = NULL;
	  } else {
	    // something passed, set token
	    try {
	      $this->token_access = new OAuthToken($token_access['oauth_token'], $token_access['oauth_token_secret']);
	    } catch(OAuthException $e) {
        // error creating token
        $this->token_access   = NULL;
        $return_data          = FALSE;
        throw new LinkedInException('OAuth exception caught: ' . $e->getMessage());
      }
	  }
	  return $return_data;
	}
	
	/**
	 * Set the token_request property.
	 * 
	 * @return   arr    $token_request [OPTIONAL] The LinkedIn oauth request token. 
	 * @return   bool                  TRUE on success, FALSE if oauth generates an exception. 	 
	 */
	public function set_token_request($token_request = NULL) {
	  $return_data = TRUE;
    if(is_null($token_request)) {
	    // null value passed, set the token to null
	    $this->token_request = NULL;
	  } else {
	    // something passed, set token
	    try {
        $this->token_request = new OAuthToken($token_request['oauth_token'], $token_request['oauth_token_secret']);
      } catch(OAuthException $e) {
        // error creating token
        $this->token_request  = NULL;
        $return_data          = FALSE;
        throw new LinkedInException('OAuth exception caught: ' . $e->getMessage());
      }
	  }
	  return $return_data;
	}
	
	/**
	 * Request the user's access token from the Linkedin API.
	 * 
	 * @param    str     $token        The token returned from the user authorization stage.
	 * @param    str     $secret       The secret returned from the request token stage.
	 * @param    str     $verifier     The verification value from LinkedIn.	 
	 * @return   arr                   The Linkedin oauth/http response, in array format.      	 
	 */
	public function token_access($token, $secret, $verifier) {
	  try {
  	  $token_access = new OAuthToken($token, $secret);
  	  $oauth_req = OAuthRequest::from_consumer_and_token($this->consumer, $token_access, 'POST', LINKEDIN::_URL_ACCESS);
      $oauth_req->set_parameter('oauth_verifier', $verifier);
      $oauth_req->sign_request($this->method, $this->consumer, $token_access);
      
      $response = self::send_request($oauth_req, LINKEDIN::_URL_ACCESS, 'POST');
      parse_str($response['linkedin'], $response['linkedin']);
      if($response['info']['http_code'] == 200) {
        // tokens retrieved
        $this->set_token_access($response['linkedin']);
      } else {
        // erro getting the request tokens
        $this->set_token_access(NULL);
      }
      return $response;
    } catch(LinkedInException $e) {
      // linkedin exception raised
      throw new LinkedInException('LinkedIn exception caught: ' . $e->getMessage());
    } catch(OAuthException $e) {
      // oauth exception raised
      throw new LinkedInException('OAuth exception caught: ' . $e->getMessage());
    }
	}
	
	/**
	 * Get the request token from the Linkedin API.
	 * 
	 * @return   arr                   The Linkedin oauth/http response, in array format.      	 
	 */
	public function token_request() {
	  try {
  	  $oauth_req = OAuthRequest::from_consumer_and_token($this->consumer, NULL, 'POST', LINKEDIN::_URL_REQUEST);
      $oauth_req->set_parameter('oauth_callback', $this->get_callback());
      $oauth_req->sign_request($this->method, $this->consumer, NULL);
      
      $response = self::send_request($oauth_req, LINKEDIN::_URL_REQUEST, 'POST');
      parse_str($response['linkedin'], $response['linkedin']);
      if($response['info']['http_code'] == 200) {
        // tokens retrieved
        $this->set_token_request($response['linkedin']);
      } else {
        // erro getting the request tokens
        $this->set_token_request(NULL);
      }
      return $response;
    } catch(LinkedInException $e) {
      // linkedin exception raised
      throw new LinkedInException('LinkedIn exception caught: ' . $e->getMessage());
    } catch(OAuthException $e) {
      // oauth exception raised
      throw new LinkedInException('OAuth exception caught: ' . $e->getMessage());
    }
	}
	
	/**
	 * Update the user's Linkedin network status.  Full details from LinkedIn 
	 * on this functionality can be found here: 
	 * 
	 * http://developer.linkedin.com/docs/DOC-1009
	 * http://developer.linkedin.com/docs/DOC-1009#comment-1077 
	 * 
	 * @param    str   $update         The network update.	 
	 * @return   bool                  TRUE if network update succeeds.
	 * @return   arr                   LinkedIn response if update fails.       	 
	 */
	public function update_network($update) {
	  // check the status itself
    if(!empty($update)) {
      /**
       * Network update is not empty, wrap a cleaned version of it in xml.  
       * Network update rules:
       * 
       * 1) No HTML permitted except those found in _NETWORK_HTML constant
       */
      try {
        // get the user data
        $response = self::profile();
        
        /** 
         * We are converting response to usable data.  I'd use SimpleXML here, but
         * to keep the class self-contained, we will use a portable XML parsing
         * routine, self::xml_to_array.       
         */        
        $person = self::xml_to_array($response['linkedin']);
    		$fields = $person['person']['children'];
  
    		// prepare user data
    		$first_name   = $fields['first-name']['content'];
    		$last_name    = $fields['last-name']['content'];
    		$profile_url  = $fields['site-standard-profile-request']['children']['url']['content'];
  	
        // create the network update 
        $update = htmlspecialchars(strip_tags($update, self::_NETWORK_HTML));
        $user   = htmlspecialchars('<a href="' . $profile_url . '">' . $first_name . ' ' . $last_name . '</a>');
    		$data   = '<activity locale="en_US">
      				       <content-type>linkedin-html</content-type>
      				       <body>' . $user . ' ' . $update . '</body>
      				     </activity>';
  
        // send request
        $update_url = self::_URL_API . '/v1/people/~/person-activities';
        $response   = $this->request('POST', $update_url, $data);
      } catch(LinkedInException $e) {
        // exception raised during network update construction
        throw new LinkedInException('LinkedIn exception caught: ' . $e->getMessage());
      }
		} else {
		  // nothing passed, raise an exception
		  throw new LinkedInException('Network update is empty');
		}
		/**
	   * Check for successful update (a 201 response from LinkedIn server) 
	   * per the documentation linked in method comments above.
	   */ 
    if($response['info']['http_code'] == 201) {
      // network update successful
      $return_data = TRUE;
    } else {
      // problem posting our network update
      $return_data = $response;
    }
		return $return_data;
	}
	
	/**
	 * Update/delete the user's Linkedin status.  Full details from LinkedIn 
	 * on this functionality can be found here: 
	 * 
	 * http://developer.linkedin.com/docs/DOC-1007
	 * http://developer.linkedin.com/docs/DOC-1007#comment-1019	 
	 * 
	 * @param    str   $status         The status update.
	 * @param    bool  $twitter        [OPTIONAL] Also update the teathered Twitter account.	 
	 * @return   bool                  TRUE if status update succeeds.
	 * @return   arr                   LinkedIn response if update fails.       	 
	 */
	public function update_status($status, $twitter = FALSE) {
	  // check the status itself
    if(!empty($status)) {
      /**
       * Status is not empty, wrap a cleaned version of it in xml.  Status
       * rules:
       * 
       * 1) 140 chars max (if this changes, change _STATUS_LENGTH constant)
       * 2) No HTML permitted: http://developer.linkedin.com/docs/DOC-1007#comment-1177
       */
       
      // filter the status per the rules above
      $status = substr(strip_tags($status), 0, self::_STATUS_LENGTH);
  		$data   = '<?xml version="1.0" encoding="UTF-8"?>
                 <current-status>' . $status . '</current-status>';
      
      // create the proper url
		  if($twitter) {
			  // update twitter as well
        $update_url = self::_URL_API . '/v1/people/~/current-status?twitter-post=true';
			} else {
        // no twitter update
        $update_url = self::_URL_API . '/v1/people/~/current-status';
      }
      
      // send request
      $response = $this->request('PUT', $update_url, $data);
		} else {
		  // empty status, clear current
  		$response = $this->request('DELETE', self::_URL_API . '/v1/people/~/current-status');
		}
		
		/**
	   * Check for successful update (a 204 response from LinkedIn server) 
	   * per the documentation linked in method comments above.
	   */ 
    if($response['info']['http_code'] == 204) {
      // status update successful
      $return_data = TRUE;
    } else {
      // problem putting our status update
      $return_data = $response;
    }
		return $return_data;
	}
	
	/**
	 * Converts passed XML data to an array.
	 * 
	 * @param    str   $xml            The XML to convert to an array.	 
	 * @return   arr                   Array containing the XML data.       	 
	 */
	public static function xml_to_array($xml) {
	  $parser = xml_parser_create();
	  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    if(!xml_parse_into_struct($parser, $xml, $tags)) {
	    throw new LinkedInException('Could not parse the passed XML.');
	  }
	  xml_parser_free($parser);
	  
    $elements = array();
    $stack    = array();
    foreach($tags as $tag) {
      $index = count($elements);
      if($tag['type'] == 'complete' || $tag['type'] == 'open') {
        $elements[$tag['tag']]               = array();
        $elements[$tag['tag']]['attributes'] = (array_key_exists('attributes', $tag)) ? $tag['attributes'] : NULL;
        $elements[$tag['tag']]['content']    = (array_key_exists('value', $tag)) ? $tag['value'] : NULL;
        if($tag['type'] == 'open') {
          $elements[$tag['tag']]['children'] = array();
          $stack[count($stack)] = &$elements;
          $elements = &$elements[$tag['tag']]['children'];
        }
      }
      if($tag['type'] == 'close') {
        $elements = &$stack[count($stack) - 1];
        unset($stack[count($stack) - 1]);
      }
    }
    return $elements;
  }
}

?>