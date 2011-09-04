<?php
require 'openid.php';

  $features=array('database','theme','security');
  require "../../../main.inc";
  $cameralife->base_url = dirname(dirname(dirname($cameralife->base_url)));


try {
    # Mewp told me specifically not to use SERVER_NAME.
    # Change 'localhost' to your domain name.

    $openid = new LightOpenID($_SERVER['SERVER_NAME']);
    $openid->required = array('contact/email');

    if(!$openid->mode) {
        if(isset($_POST['openid_identifier'])) {
            $openid->identity = $_POST['openid_identifier'];
            header('Location: ' . $openid->authUrl());
        }
    } elseif($openid->mode == 'cancel') {
        die ('User has canceled authentication!');
    } else {
        $valid = false;
        $id = "";
        $email = "";
        if ($openid->validate())
        {
            $id = $openid->identity;
            $attr = $openid->getAttributes();
            $email = $attr['contact/email'];
            if(strlen($email))
                $valid = true;
        }
        if (!$openid->validate())
            die ('Provider did not validate your login');
        if (!$valid)
            die ('Enough detail (email address) was not provided to process your login.');

        $cameralife->Security->Login($id, $email);
        header ('Location: '.$cameralife->base_url.'/index.php');
    }
} catch(ErrorException $e) {
    echo $e->getMessage();
}
