<?php

/**
 * This file is used in conjunction with the 'LinkedIn' class, and the matching
 * tokenExchange.html file, assisting in exchanging JavaScript access tokens for 
 * a permanent REST token.
 * 
 * COPYRIGHT:
 *   
 * Copyright (C) 2011, fiftyMission Inc.
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
 *   http://code.google.com/p/simple-linkedinphp/
 *    
 * REQUIREMENTS:
 * 
 * 1. You must have cURL installed on the server and available to PHP. 
 * 2. You must be running PHP 5+.
 * 3. You must have the Simple-LinkedIn library installed on the server.
 * 4. You are running this script on the same domain as the LinkedIn 
 *    JavaScript application that you would like to get the tokens for.
 * 5. You are running this script from an HTTPS-enabled host.
 * 6. You have the matching tokenExchange.html file, which calls this script.       
 *  
 * QUICK START:
 * 
 * There are two files needed to enable LinkedIn API functionality from PHP; the
 * stand-alone OAuth library, and the Simple-LinkedIn library. The latest 
 * version of the stand-alone OAuth library can be found on Google Code:
 * 
 *   http://code.google.com/p/oauth/
 * 
 * The latest versions of the Simple-LinkedIn library can be found here:
 * 
 *   http://code.google.com/p/simple-linkedinphp/
 *   
 * Per the instructions on the LinkedIn Developer's site, use this file
 * to receive the JavaScript bearer token, and then use that token to
 * retrieve the permanent OAuth 1.0a token for future use.
 * 
 *   https://developer.linkedin.com/documents/exchange-jsapi-tokens-rest-api-oauth-tokens  
 * 
 * Next, make sure the path to the LinkedIn class below is correct as you will 
 * need it to retrieve the OAuth 1.0a token. 
 * 
 * Finally, and this is IMPORTANT, be sure that:
 * 
 * 1. Insert your application API key and secret into the $API_CONFIG variable 
 *    below. 
 * 2. You are running this script on the same domain as the LinkedIn 
 *    JavaScript application that you would like to get the tokens for.
 * 3. You are running this script from an HTTPS-enabled host.
 * 
 * All that is left to do is to use the matching tokenExchange.html file to POST
 * data to this file, and this file will return the long-lived REST token for 
 * the user - it's up to you to decide what to do with it in terms of storing
 * it in a database, in the 'cloud', etc.         
 *   
 * @version 3.3.0 - December 10, 2011
 * @author Paul Mennega <paul@fiftymission.net>
 * @copyright Copyright 2011, fiftyMission Inc. 
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License 
 */

try {
  // include the LinkedIn class
  require_once('../../linkedin_3.3.0.class.php');

  // config variables
  $API_CONFIG = array(
    'appKey'    => '<your application key here>',
	  'appSecret' => '<your application secret here>' 
  );

  /**
   * Get the set cookie data, which contains the LinkedIn OAuth 2.0
   * bearer token.    
   */ 
  $cookie_name = 'linkedin_oauth_' . $API_CONFIG['appKey'];
  $credentials = json_decode(stripslashes($_COOKIE[$cookie_name]), TRUE);
  if(!is_array($credentials)) {
    // bad cookie data
    throw new LinkedInException('No LinkedIn credentials passed - ' . print_r($_COOKIE, TRUE));
  }
  
  /**
   * Calculate/check the bearer token
   */ 
  if((!array_key_exists('signature_version', $credentials)) || ($credentials['signature_version'] != 1)) {
    // invalid/missing signature_version
    throw new LinkedInException('Invalid/missing signature_version in passed credentials - ' . print_r($credentials, TRUE));
  }
      
  if((!array_key_exists('signature_order', $credentials)) || (!is_array($credentials['signature_order']))) {
    // invalid/missing signature_order
    throw new LinkedInException('Invalid/missing signature_order in passed credentials - ' . print_r($credentials, TRUE));
  }
  
  // calculate base signature
  $sig_order  = $credentials['signature_order'];
  $sig_base   = '';
  foreach($sig_order as $sig_element) {
    $sig_base .= $credentials[$sig_element]; 
  }
  
  // calculate encrypted signature
  $sig_encrypted = base64_encode(hash_hmac('sha1', $sig_base, $API_CONFIG['appSecret'], TRUE));
  
  // finally, check token validity
  if((!array_key_exists('signature', $credentials)) || ($sig_encrypted != $credentials['signature'])) {
    // invalid/missing signature
    throw new LinkedInException('Invalid/missing signature in credentials - ' . print_r($credentials, TRUE));
  }

  // swap tokens
  $OBJ_linkedin = new LinkedIn($API_CONFIG);
  $response = $OBJ_linkedin->exchangeToken($credentials['access_token']);
  
  // echo out response
  echo '<pre>' . print_r($response['linkedin'], TRUE) . '</pre>';
} catch(LinkedInException $e) {
  // exception raised
  echo $e->getMessage();
}

?>