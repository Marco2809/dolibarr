<?php

//session_start();
$user = isset($_REQUEST['username']) ? $_REQUEST['username'] : "";
$pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : "";
$tk_logout = isset($_REQUEST['logout_tck']) ? $_REQUEST['logout_tck'] : "";
if (!empty($user) && !empty($pass))
{

    $_POST['username'] = $user;
    $_POST['password'] = $pass;
    $_POST['entity'] = 1;
    $_POST['image'] = true;
    require 'index.php';
}
if (!empty($tk_logout))
{
    if ($tk_logout == 1)
    {
        $_POST['username'] = $user;
       // require 'main.inc.php';
         session_destroy(); 
        header('Location: http://glvservice.fast-data.it/user/logout.php');
        // session_destroy();
        // header('Location: http://fastdata2.service-tech.org');
    }
}

//$_SESSION["dol_login"] = $user;


