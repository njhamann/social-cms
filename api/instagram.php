<?php
header('Content-type: application/json');
require('../config/keys.php');

$url = 'https://api.instagram.com/v1/users/self/media/recent/?access_token='.$instagram['access_token'];
$json = file_get_contents($url);
$info = json_decode($json);
$items = $info->data;
$data = array();
$profileUrl = NULL;
for($i=0; $i<5; $i++){
    $item = $items[$i];
    $copy;
    if($item->caption && $item->caption->text){
        $copy = $item->caption->text;
    }else{
        $copy = NULL;
    }
    $node = array(
        'title' => 'Just posted a photo',
        'image' => $item->images->standard_resolution->url,
        'copy' => $copy,
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
