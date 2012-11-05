<?php

/**
 * This file defines the 'linkedin' class.  This class is designed to be a 
 * simple, stand-alone implementation of the most-used LinkedIn API functions.
 * 
 * COPYRIGHT:
 *   
 * Copyright (C) 2010, fiftyMission Inc.
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
 * SOURCE CODE LOCATION:
 * 
 * http://code.google.com/p/simple-linkedinphp/
 *    
 * REQUIREMENTS:
 * 
 * 1. You must have cURL installed on the server and available to PHP.
 * 2. You must be running PHP 5+.  
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
 * Next, make sure the path to the OAuth library is correct (you can change this 
 * as needed, depending on your file organization scheme, etc).
 * 
 * Now, change the _API_KEY and _API_SECRET class constants below to your 
 * LinkedIn API application keys.   
 * 
 * Finally, test the class by attempting to connect to LinkedIn using the 
 * associated demo.php page, also located at the Google Code location
 * referenced above.                   
 *   
 * RESOURCES:
 *    
 * LinkedIn API Documentation from developer.linkedin.com
 * Comments Network Updates:	       http://developer.linkedin.com/docs/DOC-1043 
 * Connections API:				           http://developer.linkedin.com/docs/DOC-1004 
 * Field Selectors:				           http://developer.linkedin.com/docs/DOC-1014 
 * Get Network Updates:			         http://developer.linkedin.com/docs/DOC-1006 
 * Industry Codes:				           http://developer.linkedin.com/docs/DOC-1011 
 * Invitation API:				           http://developer.linkedin.com/docs/DOC-1012 
 * Messaging API:					           http://developer.linkedin.com/docs/DOC-1044 
 * People Search API:					       http://developer.linkedin.com/docs/DOC-1191 
 * Profile API:					             http://developer.linkedin.com/docs/DOC-1002
 * Profile Fields:				           http://developer.linkedin.com/docs/DOC-1061
 * Post Network Update:		           http://developer.linkedin.com/docs/DOC-1009
 * Share API:                        http://developer.linkedin.com/docs/DOC-1212 
 *   replaces Status Update API:	   http://developer.linkedin.com/docs/DOC-1007
 * Throttle Limits:                  http://developer.linkedin.com/docs/DOC-1112
 *                                   http://developer.linkedin.com/message/4626#4626
 *                                   http://developer.linkedin.com/message/3193#3193 
 *    
 * @version   2.0.0 - 15/10/2010
 * @author    Paul Mennega <paul@fiftymission.net>
 * @copyright Copyright 2010, fiftyMission Inc. 
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License 
 */
 
/**
 * Source: http://code.google.com/p/oauth/
 * 
 * Rename and move as needed, be sure to change the require_once() call to the
 * correct name and path.
 */    
require_once('OAuth.php');

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
  // api keys
  const _API_KEY                     = '<your application key here>';
  const _API_SECRET                  = '<your application secret here>';

  // helper constants used to standardize LinkedIn <-> API communication.  See demo page for usage.
  const _GET_RESPONSE                = 'lResponse';
  const _GET_TYPE                    = 'lType';
  
  // Invitation API constants.
  const _INV_SUBJECT                 = 'Invitation to connect';
  const _INV_BODY_LENGTH             = 200;
  
  // Network API constants.
  const _NETWORK_HTML                = '<a>';
  
  // Share API constants
  const _SHARE_COMMENT_LENGTH        = 700;
  const _SHARE_CONTENT_TITLE_LENGTH  = 200;
  const _SHARE_CONTENT_DESC_LENGTH   = 400;
  
  // Status API constants.
  const _STATUS_LENGTH               = 140;
  
  // LinkedIn API end-points
	const _URL_ACCESS                  = 'https://www.linkedin.com/uas/oauth/accessToken';
	const _URL_API                     = 'https://api.linkedin.com';
	const _URL_AUTH                    = 'https://www.linkedin.com/uas/oauth/authorize?oauth_token=';
	const _URL_REQUEST                 = 'https://www.linkedin.com/uas/oauth/requestToken';
	const _URL_REVOKE                  = 'https://www.linkedin.com/uas/oauth/invalidateToken';
	
	// Library version
	const _VERSION                     = '2.0.0';

  public $auth, $consumer, $method;
  
  protected $callback;
  protected $token_access, $token_request;
  
	/**
	 * Create a linkedin object, used for oauth-based authentication and 
	 * communication with the LinkedIn API.	 
	 * 
	 * @param    str   $callback_url   [OPTIONAL] The URL to return the user to.
	 * @return   obj                   A new dealsheet linkedin object.	 
	 */
	public function __construct($callback_url = NULL) {
		$this->consumer = new OAuthConsumer(self::_API_KEY, self::_API_SECRET, $callback_url);		
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
	 * General connection retrieval function.
	 * 
	 * Takes a string of parameters as input and requests connection-related data 
	 * from the Linkedin Connections API.  See the official documentation for 
	 * $options 'field selector' formatting:
	 * 
	 * http://developer.linkedin.com/docs/DOC-1014      	 
	 * 
	 * @param    str     $options      [OPTIONAL] Data retrieval options.	 
	 * @return   xml                   XML formatted response.
	 */
	public function connections($options = '~/connections') {
	  $query = self::_URL_API . '/v1/people/' . trim($options);
		return $this->request('GET', $query);
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
	 * Send an invitation to connect to your network, either by email address or 
	 * by LinkedIn ID.  Details on the API here: 
	 * 
	 * http://developer.linkedin.com/docs/DOC-1012
	 * 
	 * @param    str   $method         The invitation method to process.	 
	 * @param    var   $recipient      The email/id to send the invitation to.	 	 
	 * @param    str   $subject        The subject of the invitation to send.
	 * @param    str   $body           The body of the invitation to send.
	 * @param    str   $type           [OPTIONAL] The invitation request type (only friend is supported at this time by the Invite API).
	 * @return   bool                  TRUE if connection invitation succeeds.
	 * @return   arr                   LinkedIn response if invitation fails.       	 
	 */
	public function invite($method, $recipient, $subject, $body, $type = 'friend') {
    /**
     * Clean up the passed data per these rules:
     * 
     * 1) Message must be sent to one recipient (only a single recipient permitted for the Invitation API)
     * 2) No HTML permitted
     * 3) 200 characters max in the invitation subject
     * 4) Only able to connect as a friend at this point     
     */
    // check passed data
    switch($method) {
      case 'email':
        if(is_array($recipient)) {
          $recipient = array_map('trim', $recipient);
        } else {
          // bad format for recipient for email method
          throw new LinkedInException('LinkedIn->invite(): invitation recipient email/name array is malformed.');
        }
        break;
      case 'id':
        $recipient = trim($recipient);
        if(!self::is_id($recipient)) {
          // bad format for recipient for id method
          throw new LinkedInException('LinkedIn->invite(): invitation recipient ID does not match LinkedIn format.');
        }
        break;
      default:
        throw new LinkedInException('LinkedIn->invite(): bad invitation method, must be one of: email, id.');
        break;
    }
    if(!empty($recipient)) {
      if(is_array($recipient)) {
        $recipient = array_map('trim', $recipient);
      } else {
        // string value, we assume
        $recipient = trim($recipient);
      }
    } else {
      // no recipient
      throw new LinkedInException('LinkedIn->invite(): you must provide a single invitation recipient.');
    }
    if(!empty($subject)) {
      $subject = trim(strip_tags(stripslashes($subject)));
    } else {
      throw new LinkedInException('LinkedIn->invite(): message subject is empty.');
    }
    if(!empty($body)) {
      $body = trim(strip_tags(stripslashes($body)));
    } else {
      throw new LinkedInException('LinkedIn->invite(): message body is empty.');
    }
    switch($type) {
      case 'friend':
        break;
      default:
        throw new LinkedInException('LinkedIn->invite(): bad invitation type, must be one of: friend.');
        break;
    }
    
    // construct the xml data
		$data   = '<?xml version="1.0" encoding="UTF-8"?>
		           <mailbox-item>
		             <recipients>
                   <recipient>';
                     switch($method) {
                       case 'email':
                         // email-based invitation
                         $data .= '<person path="/people/email=' . $recipient['email'] . '">
                                     <first-name>' . $recipient['first-name'] . '</first-name>
                                     <last-name>' . $recipient['last-name'] . '</last-name>
                                   </person>';
                         break;
                       case 'id':
                         // id-based invitation
                         $data .= '<person path="/people/id=' . $recipient . '"/>';
                         break;
                     }
    $data  .= '    </recipient>
                 </recipients>
                 <subject>' . $subject . '</subject>
                 <body>' . $body . '</body>
                 <item-content>
                   <invitation-request>
                     <connect-type>';
                       switch($type) {
                         case 'friend':
                           $data .= 'friend';
                           break;
                       }
    $data  .= '      </connect-type>';
                     switch($method) {
                       case 'id':
                         // id-based invitation, we need to get the authorization information
                         $query                 = 'id=' . $recipient . ':(api-standard-profile-request)';
                         $response              = self::profile($query);
                         $response['linkedin']  = self::xml_to_array($response['linkedin']);
                         $authentication        = explode(':', $response['linkedin']['person']['children']['api-standard-profile-request']['children']['headers']['children']['http-header']['children']['value']['content']);
                         
                         // complete the xml        
                         $data .= '<authorization>
                                     <name>' . $authentication[0] . '</name>
                                     <value>' . $authentication[1] . '</value>
                                   </authorization>';
                         break;
                     }
    $data  .= '    </invitation-request>
                 </item-content>
               </mailbox-item>';
    
    // send request
    $invite_url = self::_URL_API . '/v1/people/~/mailbox';
    $response   = $this->request('POST', $invite_url, $data);
		
		/**
	   * Check for successful update (a 201 response from LinkedIn server) 
	   * per the documentation linked in method comments above.
	   */ 
    if($response['info']['http_code'] == 201) {
      // status update successful
      $return_data = TRUE;
    } else {
      // problem posting our connection message(s)
      $return_data = $response;
    }
		return $return_data;
	}
	
	/**
	 * Checks the passed string $id to see if it has a valid LinkedIn ID format, 
	 * which is, as of October 15th, 2010:
	 * 
	 * 10 alpha-numeric mixed-case characters, plus underscores and dashes.          	 
	 * 
	 * @param    str     $id           A possible LinkedIn ID.         	 
	 * @return   bool                  TRUE/FALSE depending on valid ID format determination.                  
	 */
	public static function is_id($id) {
	  if(is_string($id)) {
	    // we at least have a string, check it
  	  $pattern = '/^[a-z0-9_\-]{10}$/i';
  	  if($match = preg_match($pattern, $id)) {
  	    // we have a match
  	    $return_data = TRUE;
  	  } else {
  	    // no match
  	    $return_data = FALSE;
  	  }
	  } else {
	    // bad data passed
	    throw new LinkedInException('LinkedIn->is_id(): passed LinkedIn ID must be a string type.');
	  }
	  return $return_data;
	}
	
	/**
	 * Checks the passed LinkedIn response to see if we have hit a throttling 
	 * limit:
	 * 
	 * http://developer.linkedin.com/docs/DOC-1112
	 * 
	 * @param    arr     $response     The LinkedIn response.         	 
	 * @return   bool                  TRUE/FALSE depending on content of response.                  
	 */
	public static function is_throttled($response) {
	  // set the default
	  $return_data = FALSE;
    
    // check the variable
	  if(is_array($response) && array_key_exists('linkedin', $response)) {
	    // we have an array and have a properly formatted linkedin response
	       
      // store the response in a temp variable
      $temp_response = self::xml_to_array($response['linkedin']);
  	  
  	  // check to see if we have an error
  	  if(array_key_exists('error', $temp_response) && ($temp_response['error']['children']['status']['content'] == 403) && preg_match('/throttle/i', $temp_response['error']['children']['message']['content'])) {
  	    // we have an error, it is 403 and we have hit a throttle limit
	      $return_data = TRUE;
  	  }
  	}
  	return $return_data;
	}
	
	/**
	 * Send a message to your network connection(s), optionally copying yourself.  
	 * Full details from LinkedIn on this functionality can be found here: 
	 * 
	 * http://developer.linkedin.com/docs/DOC-1044
	 * 
	 * @param    arr   $recipients     The connection(s) to send the message to.	 	 
	 * @param    str   $subject        The subject of the message to send.
	 * @param    str   $body           The body of the message to send.	 
	 * @param    bool  $copy_self      [OPTIONAL] Also update the teathered Twitter account.	 
	 * @return   bool                  TRUE if connection message succeeds.
	 * @return   arr                   LinkedIn response if message fails.       	 
	 */
	public function message($recipients, $subject, $body, $copy_self = FALSE) {
    /**
     * Clean up the passed data per these rules:
     * 
     * 1) Message must be sent to at least one recipient
     * 2) No HTML permitted
     */
    if(!empty($subject)) {
      $subject = trim(strip_tags(stripslashes($subject)));
    } else {
      throw new LinkedInException('LinkedIn->message(): message subject is empty.');
    }
    if(!empty($body)) {
      $body = trim(strip_tags(stripslashes($body)));
    } else {
      throw new LinkedInException('LinkedIn->message(): message body is empty.');
    }
    if(!is_array($recipients) || count($recipients) < 1) {
      // no recipients, and/or bad data
      throw new LinkedInException('LinkedIn->message(): at least one message recipient required.');
    }
    
    // construct the xml data
		$data   = '<?xml version="1.0" encoding="UTF-8"?>
		           <mailbox-item>
		             <recipients>';
    $data  .=     ($copy_self) ? '<recipient><person path="/people/~"/></recipient>' : '';
                  for($i = 0; $i < count($recipients); $i++) {
                    $data .= '<recipient><person path="/people/' . trim($recipients[$i]) . '"/></recipient>';
                  }
    $data  .= '  </recipients>
                 <subject>' . $subject . '</subject>
                 <body>' . $body . '</body>
               </mailbox-item>';
    
    // send request
    $message_url  = self::_URL_API . '/v1/people/~/mailbox';
    $response     = $this->request('POST', $message_url, $data);
		
		/**
	   * Check for successful update (a 201 response from LinkedIn server) 
	   * per the documentation linked in method comments above.
	   */ 
    if($response['info']['http_code'] == 201) {
      // status update successful
      $return_data = TRUE;
    } else {
      // problem posting our connection message(s)
      $return_data = $response;
    }
		return $return_data;
	}
	
	/**
	 * General profile retrieval function.
	 * 
	 * Takes a string of parameters as input and requests profile data from the 
	 * Linkedin Profile API.  See the official documentation for $options
	 * 'field selector' formatting:
	 * 
	 * http://developer.linkedin.com/docs/DOC-1014
	 * http://developer.linkedin.com/docs/DOC-1002    
	 * 
	 * @param    str     $options      [OPTIONAL] Data retrieval options.	 
	 * @return   xml                   XML formatted response.
	 */
	public function profile($options = '~') {
	  $query = self::_URL_API . '/v1/people/' . trim($options);
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
    } catch(OAuthException $e) {
      // oauth exception raised
      throw new LinkedInException('OAuth exception caught: ' . $e->getMessage());
    }
	}
	
	/**
	 * General people search function.
	 * 
	 * Takes a string of parameters as input and requests profile data from the 
	 * Linkedin People Search API.  See the official documentation for $options
	 * querystring formatting:
	 * 
	 * http://developer.linkedin.com/docs/DOC-1191 
	 * 
	 * @param    str     $options      [OPTIONAL] Data retrieval options.	 
	 * @return   xml                   XML formatted response.
	 */
	public function search($options = NULL) {
    $query = self::_URL_API . '/v1/people-search' . trim($options);
		return $this->request('GET', $query);
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
        
        // check for throttling
        if(self::is_throttled($return_data['linkedin'])) {
          throw new LinkedInException('LinkedIn->send_request(): throttling limit has been reached.');
        }
        
        // close cURL connection
        curl_close($handle);
        
        // no exceptions thrown, return the data
        return $return_data;
      } else {
        // cURL failed to start
        throw new LinkedInException('LinkedIn->send_request(): cURL did not initialize properly.');
      }
    } else {
      // cURL not present
      throw new LinkedInException('LinkedIn->send_request(): PHP cURL extension does not appear to be loaded/present.');
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
	 * Create a new or reshare another user's shared content.  Full details from 
	 * LinkedIn on this functionality can be found here: 
	 * 
	 * http://developer.linkedin.com/docs/DOC-1212 
	 * 
	 * $action values: ('new', 'reshare')      	 
	 * $content format: 
	 *   $action = 'new'; $content => ('comment' => 'xxx', 'title' => 'xxx', 'submitted-url' => 'xxx', 'submitted-image-url' => 'xxx', 'description' => 'xxx')
	 *   $action = 'reshare'; $content => ('comment' => 'xxx', 'id' => 'xxx')	 
	 * 
	 * @param    str   $action         The sharing action to perform.	 
	 * @param    str   $content        The share content.
	 * @param    bool  $private        [OPTIONAL] Should we restrict this shared item to connections only?	 
	 * @param    bool  $twitter        [OPTIONAL] Also update the teathered Twitter account.	 
	 * @return   bool                  TRUE if share update succeeds, FALSE if bad data passed.
	 * @return   arr                   LinkedIn response if update fails.       	 
	 */
	public function share($action, $content, $private = TRUE, $twitter = FALSE) {
	  // check the status itself
    if(!empty($action) && !empty($content)) {
      /**
       * Status is not empty, wrap a cleaned version of it in xml.  Status
       * rules:
       * 
       * 1) Comments are 700 chars max (if this changes, change _SHARE_COMMENT_LENGTH constant)
       * 2) Content/title 200 chars max (if this changes, change _SHARE_CONTENT_TITLE_LENGTH constant)
       * 3) Content/description 400 chars max (if this changes, change _SHARE_CONTENT_DESC_LENGTH constant)
       * 4a) New shares must contain a comment and/or (content/title and content/submitted-url)
       * 4b) Reshared content must contain an attribution id.       
       * 5) No HTML permitted in comment, content/title, content/description.
       */

      // prepare the share data per the rules above
      $share_flag   = FALSE;
      $content_xml  = NULL;
      if(array_key_exists('comment', $content)) {
        // comment located
        $comment = substr(trim(strip_tags(stripslashes($content['comment']))), 0, self::_SHARE_COMMENT_LENGTH);
        $content_xml .= '<comment>' . $comment . '</comment>';
        $share_flag = TRUE;
      }
      switch($action) {
        case 'new':
          if(array_key_exists('title', $content) && array_key_exists('submitted-url', $content)) {
            // we have shared content, format it as needed per rules above
            $content_title = substr(trim(strip_tags(stripslashes($content['title']))), 0, self::_SHARE_CONTENT_TITLE_LENGTH);
            $content_xml .= '<content>
                               <title>' . $content_title . '</title>
                               <submitted-url>' . trim($content['submitted-url']) . '</submitted-url>';
            if(array_key_exists('submitted-image-url', $content)) {
              $content_xml .= '<submitted-image-url>' . trim($content['submitted-image-url']) . '</submitted-image-url>';
            }
            if(array_key_exists('description', $content)) {
              $content_desc = substr(trim(strip_tags(stripslashes($content['description']))), 0, self::_SHARE_CONTENT_DESC_LENGTH);
              $content_xml .= '<description>' . $content_desc . '</description>';
            }
            $content_xml .= '</content>';
            $share_flag = TRUE;
          }
          break;
        case 'reshare':
          if(array_key_exists('id', $content)) {
            $content_xml .= '<attribution>
                               <share>
                                 <id>' . trim($content['id']) . '</id>
                               </share>
                             </attribution>';
          } else {
            // missing required piece of data
            $share_flag = FALSE;
          }
          break;
        default:
          // bad action passed
          throw new LinkedInException('LinkedIn->share(): share action is an invalid value, must be one of: share, reshare.');
          break;
      }
      
      // should we proceed?
      if($share_flag) {
        // put all of the xml together
        $visibility = ($private) ? 'connections-only' : 'anyone';
        $data       = '<?xml version="1.0" encoding="UTF-8"?>
                       <share>
                         ' . $content_xml . '
                         <visibility>
                           <code>' . $visibility . '</code>
                         </visibility>
                       </share>';
        
        // create the proper url
        $share_url = self::_URL_API . '/v1/people/~/shares';
  		  if($twitter) {
  			  // update twitter as well
          $share_url .= '?twitter-post=true';
  			}
        
        // send request
        $response = $this->request('POST', $share_url, $data);
  		} else {
  		  // data contraints/rules not met, raise an exception
		    throw new LinkedInException('LinkedIn->share(): sharing data constraints not met; check that you have supplied valid content and combinations of content to share.');
  		}
    } else {
      // data missing, raise an exception
		  throw new LinkedInException('LinkedIn->share(): sharing action or shared content is missing.');
    }
    
    /**
	   * Check for successful update (a 201 response from LinkedIn server) 
	   * per the documentation linked in method comments above.
	   */ 
    if($response['info']['http_code'] == 201) {
      // status update successful
      $return_data = TRUE;
    } else {
      // problem putting our status update
      $return_data = $response;
    }
		return $return_data;
	}
	
	/**
	 * General network statistics retrieval function.
	 * 
	 * Returns the number of connections, second-connections an authenticated
	 * user has.  More information here:
	 * 
	 * http://developer.linkedin.com/docs/DOC-1006
	 * 
	 * @return   xml                   XML formatted response.
	 */
	public function statistics() {
	  $query = self::_URL_API . '/v1/people/~/network/network-stats';
		return $this->request('GET', $query);
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
        // error getting the request tokens
        $this->set_token_request(NULL);
      }
      return $response;
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
  		$first_name   = trim($fields['first-name']['content']);
  		$last_name    = trim($fields['last-name']['content']);
  		$profile_url  = trim($fields['site-standard-profile-request']['children']['url']['content']);
	
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
		} else {
		  // nothing passed, raise an exception
		  throw new LinkedInException('LinkedIn->update_network(): network update is empty');
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
	 * General network update retrieval function.
	 * 
	 * Takes a string of parameters as input and requests update-related data 
	 * from the Linkedin Network Updates API.  See the official documentation for 
	 * $options parameter formatting:
	 * 
	 * http://developer.linkedin.com/docs/DOC-1006
	 * 
	 * For getting more comments, likes, etc, see here:
	 * 
	 * http://developer.linkedin.com/docs/DOC-1043         	 
	 * 
	 * @param    str     $options      [OPTIONAL] Data retrieval options.	 
	 * @return   xml                   XML formatted response.
	 */
	public function updates($options = NULL) {
	  $query = self::_URL_API . '/v1/people/~/network/updates' . trim($options);
		return $this->request('GET', $query);
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
	    throw new LinkedInException('LinkedIn->xml_to_array(): could not parse the passed XML.');
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