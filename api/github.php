<?php
header('Content-type: application/json');
$url = 'https://api.github.com/users/njhamann/events/public';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

function tokenTruncate($string, $your_desired_width) {
    if(strlen($string) > $your_desired_width){
        $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $length = 0;
        $last_part = 0;
        for (; $last_part < $parts_count; ++$last_part) {
            $length += strlen($parts[$last_part]);
            if ($length > $your_desired_width) { 
        
                break; 
            }
        }

        return implode(array_slice($parts, 0, $last_part)).'...';
    }else{
        return $string;
    }
}

$json = curl_exec($ch);
curl_close($ch);
//$json = file_get_contents($url);
$info = json_decode($json);
$items = $info;
$data = array();
$defaultImage = 'img/default_github.png';
$profileUrl = 'http://github.com/njhamann';
for($i=0; $i<10; $i++){
    $item = $items[$i];
    $type = $item->type;
    $copy = '';
    $branch = '';
    $commits = array();
    if(isset($item->payload->ref)){
        $branch = $item->payload->ref;
    }
    $repo = $item->repo->name;
    /*
    if($repo == 'njhamann/social-cms' && $i>0){
        continue;
    }
    */
    if($type == 'PushEvent'){
        $copy = 'Pushed to '.$branch.' at '.$repo;
        if(isset($item->payload->commits)){
            foreach($item->payload->commits as $value){
                array_push($commits, $value->message);
            }
        }
    }else if($type == 'CreateEvent'){
        $copy = 'Created '.$branch.' at '.$repo;
    }else if($type == 'WatchEvent'){
        $copy = 'I '.$item->payload->action.' watching '.$repo; 
    }else if($type == 'FollowEvent'){
        $copy = 'I started following <a href="'.$item->payload->target->html_url.'">'.$item->payload->target->login.'</a>'; 
    }else if($type == 'PullRequestReviewCommentEvent'){
        $copy = 'I commented on a pull request on ' . $repo;
        $commits = array(tokenTruncate($item->payload->comment->body, 200));
    }else if($type == 'PullRequestEvent'){
        $copy = 'Pull request event on ' . $repo;
    }else if($type == 'IssueCommentEvent'){
        $copy = 'I commented on an issue at ' . $repo;
        $commits = array(tokenTruncate($item->payload->comment->body, 200));
    }else{
	continue;
    }
    $node = array(
        'id' => $item->id,
        'title' => $copy,
        'image' => $defaultImage,
        'copy' => NULL,
        'type_pretty' => 'GitHub',
        'type' => 'github',
        'link_copy' => 'View repo',
        'link' => 'https://github.com/'.$repo,
        'icon' => NULL,
        'meta' => NULL,
        'feed' => $commits,
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
