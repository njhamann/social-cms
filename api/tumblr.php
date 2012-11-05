<?php
header('Content-type: application/json');
require('../config/keys.php');
$url = 'http://api.tumblr.com/v2/blog/noahhamann.tumblr.com/posts?api_key='.$tumblr['api_key'];
$json = file_get_contents($url);
$info = json_decode($json);
$items = $info->response->posts;
$data = array();
$profileUrl = 'http://noahhamann.tumblr.com';
for($i=0; $i<5; $i++){
    $item = $items[$i];
    $node = array(
        'title' => $item->title,
        'image' => NULL,
        'type_pretty' => 'Tumblr',
        'type' => 'tumblr',
        'copy' => NULL,
        'link_copy' => 'View post',
        'link' => $item->post_url,
        'icon' => NULL,
        'meta' => NULL,
        'feed' => NULL,
        'epoch' => $item->timestamp,
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
