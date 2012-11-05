<?php
header('Content-type: application/json');
$url = 'https://api.github.com/users/njhamann/events/public';
$json = file_get_contents($url);
$info = json_decode($json);

$items = $info;
$data = array();
$defaultImage = 'img/default_github.png';
$profileUrl = 'http://github.com/njhamann';
for($i=0; $i<5; $i++){
    $item = $items[$i];
    $type = $item->type;
    $copy = '';
    $branch = $item->payload->ref;
    $repo = $item->repo->name;
    if($type == 'PushEvent'){
        $copy = 'Pushed to '.$branch.' at '.$repo;
    }else if($type == 'CreateEvent'){
        $copy = 'Created '.$branch.' at '.$repo;
    }
    $node = array(
        'title' => $repo,
        'image' => $defaultImage,
        'copy' => $copy,
        'type_pretty' => 'GitHub',
        'type' => 'github',
        'link_copy' => 'View repo',
        'link' => $item->repo->url,
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
