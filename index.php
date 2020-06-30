<?php
// import user object
require_once('core/classes/user.Class.php');

// assig User object to $User variable
$User = new User;

// getting the action
$a = isset($_GET['a']) ? $_GET['a'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register User</title>
    <link rel='stylesheet' href='style.css' />
</head>
<body>
<?php
switch($a){
    case 'login': {
        echo $User -> LoginForm();
        break;
    }
    case 'register': {
        echo $User -> RegisterForm();
        break;
    }
    case 'dashboard': {
        $User -> Is_Login() ?  $User->Dashboard() : 'Session expired!';
        break;
    }
    default: {
        echo $User -> LoginForm();
        break;
    }
}
?>
</body>
</html>