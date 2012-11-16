<?php
header('Content-type: application/json');
require('../config/keys.php');
require('../lib/twitter/config.php');
require('../lib/twitter/twitteroauth/twitteroauth.php');
require('../lib/util/linkify.php');

function getConnectionWithAccessToken($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret) {
    $connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
    return $connection;
}

$connection = getConnectionWithAccessToken($tw['consumer_key'], $tw['consumer_secret'], $tw['access_token'], $tw['access_token_secret']);

$content = $connection->get("statuses/user_timeline", array('include_rts' => true));
$info = $content;
$items = $info;
$data = array();
$defaultImage = 'img/default_twitter.png';
$profileUrl = 'http://twitter.com/njhamann';
for($i=0; $i<5; $i++){
    $item = $items[$i];
    $screenName = $item->user->screen_name;
    $statusId = $item->id_str;
    $link = 'https://twitter.com/'.$screenName.'/status/'.$statusId;
    $title = auto_link_text($item->text);
    $node = array(
        'title' => $title,
        'image' => $defaultImage,
        'copy' => NULL,
        'link_copy' => 'View message',
        'link' => $link,
        'type_pretty' => 'Twitter',
        'type' => 'twitter',
        'icon' => NULL,
        'meta' => NULL,
        'feed' => NULL,
        'epoch' => strtotime($item->created_at),
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
