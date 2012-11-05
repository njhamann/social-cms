<?php
header('Content-type: application/json');
if (isset($_REQUEST['email'])){
    //send email
    $email = $_REQUEST['email'];
    $subject = 'Message from noahhamann.com contact form';
    $message = $_REQUEST['message'];
    mail("njhamann@gmail.com", $subject, $message, "From:" . $email);
    $resp = '{"success":1}';
}else{
    $resp = '{"success":0}';
}

echo $resp;
?>
