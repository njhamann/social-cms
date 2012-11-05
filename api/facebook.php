<?php
header('Content-type: application/json');
require('../config/keys.php');
$url = 'https://graph.facebook.com/'.$facebook['facebook_id'].'/statuses?access_token='.$facebook['access_token'];
$json = file_get_contents($url);
$info = json_decode($json);
$items = $info->data;
$data = array();
$profileUrl = 'http://facebook.com/njhamann';
$defaultImage = 'img/default_facebook.png';
for($i=0; $i<5; $i++){
    $item = $items[$i];
    $node = array(
        'title' => $item->message,
        'image' => $defaultImage,
        'type_pretty' => 'Facebook',
        'type' => 'facebook',
        'copy' => NULL,
        'link_copy' => 'View status',
        'link' => 'http://facebook.com/'.$item->id,
        'icon' => 'img/icon_facebook.png',
        'meta' => NULL,
        'feed' => NULL,
        'epoch' => strtotime($item->updated_time),
        'profile_url' => $profileUrl
    );
    array_push($data, $node);
}
if(isset($_GET['raw']) && $_GET['raw'] == '1'){
    echo json_encode($info);
}else{
    echo json_encode($data);
}
?>
