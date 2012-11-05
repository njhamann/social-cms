<?php

header('Content-type: application/json');
$profileUrl = NULL;
$items = array(
    array(
        'title' => 'Cadence by SocialFlow',
        'image' => 'img/project_cadence.png',
        'copy' => NULL,
        'link_copy' => 'View project',
        'link' => 'http://www.socialflow.com/cadence/overview',
        'type_pretty' => 'Project',
        'type' => 'project',
        'icon' => NULL,
        'meta' => 'Built for SocialFlow',
        'feed' => NULL,
        'epoch' => 5,
        'profile_url' => $profileUrl
    ),
    array(
        'title' => 'Crescendo by SocialFlow',
        'image' => 'img/project_crescendo.png',
        'copy' => NULL,
        'link_copy' => 'View project',
        'link' => 'http://www.socialflow.com/crescendo/overview',
        'type_pretty' => 'Project',
        'type' => 'project',
        'icon' => NULL,
        'meta' => 'Built for SocialFlow',
        'feed' => NULL,
        'epoch' => 4,
        'profile_url' => $profileUrl
    ),
    array(
        'title' => 'International Trucks iPhone app',
        'image' => 'img/project_it_iphone.png',
        'copy' => NULL,
        'link_copy' => 'View project',
        'link' => 'https://itunes.apple.com/us/app/international-on-the-road/id418951877?mt=8',
        'type_pretty' => 'Project',
        'type' => 'project',
        'icon' => NULL,
        'meta' => 'Built for International Trucks',
        'feed' => NULL,
        'epoch' => 3,
        'profile_url' => $profileUrl
    ),
    array(
        'title' => 'TerraStar Truck Configurator',
        'image' => 'img/project_it_config.png',
        'copy' => NULL,
        'link_copy' => 'View project',
        'link' => 'http://www.internationaltrucks.com/trucks/trucks/series/terrastar/configurator',
        'type_pretty' => 'Project',
        'type' => 'project',
        'icon' => NULL,
        'meta' => NULL,
        'feed' => NULL,
        'meta' => 'Built for International Trucks',
        'epoch' => 3,
        'profile_url' => $profileUrl
    ),
    array(
        'title' => 'Ameriprise Advisors',
        'image' => 'img/project_ameriprise.png',
        'copy' => NULL,
        'link_copy' => 'View project',
        'link' => 'http://www.ameripriseadvisors.com/',
        'type_pretty' => 'Project',
        'type' => 'project',
        'icon' => NULL,
        'meta' => 'Built for Ameriprise',
        'feed' => NULL,
        'epoch' => 1,
        'profile_url' => $profileUrl
    )
);
if(isset($_GET['raw']) && $_GET['raw'] == '1'){
    echo json_encode($info);
}else{
    echo json_encode($items);
}
?>
