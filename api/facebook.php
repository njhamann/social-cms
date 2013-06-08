<?php
//to get new long lived api key
//go to https://developers.facebook.com/tools/explorer and get a short lived token and then use the url below to get long lived one
//https://graph.facebook.com/oauth/access_token?client_id=287692518015234&client_secret=04c8aa0de77e422c9cfc0530d1e16280&grant_type=fb_exchange_token&fb_exchange_token=CAAEFp6JXQQIBAAgGkEsyIxmX0atCLKZAhTaPQE8eDBTZAyi7p9VaUrbspyZCHp6feWt5mZADdCvC8XUOV7frRknrYqPK5PNFKXLwe7SxKAexlqZBBLNdZC1ZBtAzTNa1EoPqCdppWZBlTeLQd65vKioE6sbHpzXVTfkG2adtSzeJiAZDZD

header('Content-type: application/json');
require('../config/keys.php');
$url = 'https://graph.facebook.com/'.$facebook['facebook_id'].'/statuses?access_token='.$facebook['access_token'];
$json = file_get_contents($url);
$info = json_decode($json);
$items = $info->data;
$data = array();
$profileUrl = 'http://facebook.com/njhamann';
$defaultImage = 'img/default_facebook.png';
for($i=0; $i<10; $i++){
    $item = $items[$i];
    $node = array(
        'id' => $item->id,
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
