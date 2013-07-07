<?php
header('Content-type: application/json');
require('../config/keys.php');
require('../lib/twitter/config.php');
require('../lib/twitter/twitteroauth/twitteroauth.php');
require('../lib/util/linkify.php');

function getConnectionWithAccessToken($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret) {
    $connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
    $connection->host = "https://api.twitter.com/1.1/";
    return $connection;
}

$connection = getConnectionWithAccessToken($tw['consumer_key'], $tw['consumer_secret'], $tw['access_token'], $tw['access_token_secret']);


$content = $connection->get("statuses/user_timeline", array(
    'include_rts' => true,
    'include_entities' => true
));
$info = $content;
$items = $info;
$data = array();
$profileUrl = 'http://twitter.com/njhamann';
for($i=0; $i<10; $i++){
    $item = $items[$i];
    $screenName = $item->user->screen_name;
    $statusId = $item->id_str;
    $link = 'https://twitter.com/'.$screenName.'/status/'.$statusId;
    $source = $item->source;
    $vPos = strpos($source, "vine");
    $type = 'twitter';
    $typePretty = 'Twitter';
    $urls = $item->entities->urls;
    $defaultImage = 'img/default_twitter.png';
    $icon = NULL; 
    foreach($urls as $url){
        if(strpos($url->expanded_url, 'vine') !== false){
            $profileUrl = NULL;
            $icon = 'img/icon_vine.png';
            $defaultImage = 'img/default_vine.png';
            $typePretty = 'Vine';
            $type = 'vine';
            $link = $url->expanded_url;
            continue;
        }
    }
    
    if(isset($item->retweeted_status)){
        $rtUser = $item->retweeted_status->user->screen_name;
        $title = 'RT @' . $rtUser . ': ' . auto_link_text($item->retweeted_status->text);
    }else{
        $title = auto_link_text($item->text);
    }
    $node = array(
        'id' => $item->id,
        'title' => $title,
        'image' => $defaultImage,
        'copy' => NULL,
        'link_copy' => 'View video',
        'link' => $link,
        'type_pretty' => $typePretty,
        'type' => $type,
        'icon' => $icon,
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
