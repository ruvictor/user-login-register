<?php
setcookie('check', '', time() - 60*60*24*30, '/'); 
setcookie('sess', '', time() - 60*60*24*30, '/');
header('Location: /user-login-register/');
die();
?>