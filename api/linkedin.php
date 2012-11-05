<?php
header('Content-type: application/json');
require('../config/keys.php');
require('../lib/linkedin/linkedin_3.2.0.class.php');

$API_CONFIG = $linkedin['config'];
$OBJ_linkedin = new LinkedIn($API_CONFIG);
$OBJ_linkedin->setTokenAccess($linkedin['access']);
$OBJ_linkedin->setResponseFormat(LINKEDIN::_RESPONSE_JSON);
$json = $OBJ_linkedin->profile('~:(first-name,last-name,formatted-name,industry,skills,summary,specialties,positions,picture-url,educations,interests,headline,phone-numbers,email-address,member-url-resources)');
$info = json_decode($json['linkedin']);    

$node = array(
    'name' => $info->formattedName,
    'headline' => $info->headline,
    'picture_url' => $info->pictureUrl,
    'summary' => $info->summary,
    'work_history' => $info->positions->values,
    'meta' => 'Powered by LinkedIn',
    'profile_url' => 'http://www.linkedin.com/in/njhamann'
);

if($json['success'] === TRUE) {
    if(isset($_GET['raw']) && $_GET['raw'] == '1'){
        echo json_encode($info);
    }else{
        echo json_encode($node);
    }
}

?>
