<?php
header('Content-type: application/json');
require('../config/keys.php');
$url = 'https://api.foursquare.com/v2/users/self/checkins?v=20121030&oauth_token='.$foursquare['oauth_token'];
$json = file_get_contents($url);
$info = json_decode($json);
$items = $info->response->checkins->items;
$data = array();
$profileUrl = 'http://foursquare.com/njhamann';
for($i=0; $i<10; $i++){
    $item = $items[$i];
    $node = array(
        'id' => $item->id,
        'title' => 'Checked in at '. $item->venue->name,
        'image' => 'http://maps.google.com/maps/api/staticmap?center='.$item->venue->location->lat.','.$item->venue->location->lng.'&zoom=16&size=420x260&maptype=roadmap&sensor=false&markers=color:red|'.$item->venue->location->lat.','.$item->venue->location->lng,
        'type_pretty' => 'foursquare',
        'type' => 'foursquare',
        'copy' => NULL,
        'link_copy' => 'View check in',
        'link' => 'https://foursquare.com/njhamann/checkin/'.$item->id,
        'icon' => 'img/icon_foursquare.png',
        'meta' => NULL,
        'feed' => NULL,
        'epoch' => $item->createdAt,
        'profile_url' => $profileUrl,
        'stash' => array(
            'lat' => $item->venue->location->lat,
            'lng' => $item->venue->location->lng
        )
    );
    array_push($data, $node);
}
if(isset($_GET['raw']) && $_GET['raw'] == '1'){
    echo json_encode($info);
}else{
    echo json_encode($data);
}
?>
