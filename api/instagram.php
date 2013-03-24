<?php
header('Content-type: application/json');
require('../config/keys.php');

$url = 'https://api.instagram.com/v1/users/self/media/recent/?access_token='.$instagram['access_token'];
$json = file_get_contents($url);
$info = json_decode($json);
$items = $info->data;
$data = array();
$profileUrl = 'http://instagram.com/njhamann';
for($i=0; $i<10; $i++){
    $item = $items[$i];
    $title = 'Just posted a photo';
    if($item->caption && $item->caption->text){
        $title = $item->caption->text;
    }
    $node = array(
        'id' => $item->id,
        'title' => $title,
        'image' => $item->images->standard_resolution->url,
        'copy' => NULL,
        'link_copy' => 'View photo',
        'link' => $item->link,
        'type_pretty' => 'Instagram',
        'type' => 'instagram',
        'icon' => NULL,
        'meta' => NULL,
        'feed' => NULL,
        'epoch' => $item->created_time,
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
