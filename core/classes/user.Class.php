<?php
require_once(realpath(dirname(__FILE__) . '/../../config.php'));
class User {
    function Dashboard(){
        return 'Dashboard Content <a href="exit.php">Exit</a>';
    }
	function generateCode($length=6){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		$code = ""; 
		$clen = strlen($chars) - 1; 
		while (strlen($code) < $length){ 
			$code .= $chars[mt_rand(0,$clen)];
		}
		return $code;
	}
    function CheckLoginData($email, $pass){
        $db = new Connect;
        $result = '';
		if(isset($email) && isset($pass)){
			$email=stripslashes(htmlspecialchars($email));
			$pass=stripslashes(htmlspecialchars(md5(md5(trim($pass)))));
			if (empty($email) or empty($pass)){
				$result .= "<div class=\"error\"><p><strong>ERROR:</strong> All fields are required!</p></div>";
			}else{
				$user = $db->prepare("SELECT * FROM users WHERE email = :email AND password = :pass");
				$user->execute(array(
						'email' => $email,
						'pass'  => $pass
						));
				$info = $user->fetch(PDO::FETCH_ASSOC);
				if ($user->rowCount() == 0){
					$result .= "<div class=\"error\"><p><strong>ERROR:</strong> Login failed!</div>";
				}else{
					$hash = md5($this->generateCode(10));
					$upd = $db->prepare("UPDATE users SET session=:hash WHERE id=:ex_user");
					$upd->execute(array(
							'hash' 	=> $hash,
							'ex_user' 	=> $info['id']
					));
					setcookie("id", $info['id'], time()+60*60*24*30, "/", NULL);
					setcookie("sess", $hash, time()+60*60*24*30, "/", NULL);
					echo("<script>location.href = '/user-login-register/?a=dashboard';</script>");
				}
			}
        }
        return $result;
    }
    function CheckRegisterData(
        $f_name,
        $l_name,
        $email,
        $pass1,
        $pass2
    ){
        $db = new Connect;
        $result = '';
		if(isset($f_name) && isset($l_name) && isset($email) && isset($pass1) && isset($pass2)){
			$f_name=stripslashes(htmlspecialchars($f_name));
			$l_name=stripslashes(htmlspecialchars($l_name));
			$email=stripslashes(htmlspecialchars($email));
			$pass1=stripslashes(htmlspecialchars(md5(md5(trim($pass1)))));
			$pass2=stripslashes(htmlspecialchars(md5(md5(trim($pass2)))));
			if (empty($f_name) or empty($l_name) or empty($email) or empty($pass1) or empty($pass2)){
				$result .= "<div class=\"error\"><p><strong>ERROR:</strong> All fields are required!</p></div>";
			}else{
				$user = $db->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (:first_name,:last_name,:email,:password)");
				$user->execute(array(
						'first_name' => $f_name,
						'last_name'  => $l_name,
						'email'  => $email,
						'password'  => $pass1,
						));
				if (!$user){
					$result .= "<div class=\"error\"><p><strong>ERROR:</strong> We couldn't register this user!</div>";
				}else{
					echo("<script>location.href = '/user-login-register/?a=login';</script>");
				}
			}
        }
        return $result;
    }
    function LoginForm(){
		return '
			<div class="form_block">
				<div id="title">
					Log In
				</div>
				<div class="body">
				' . ($_POST ? $this->CheckLoginData($_POST['email'], $_POST['pass']) : '') . '
					<form id="logform" action="?a=login" method="POST" >
						<input type="text" name="email" placeholder="Email" />
						<input type="password" name="pass" placeholder="Password" />
						<input type="submit" value="Login" /> <a class="regBtn" href="?a=register">Register</a>
					</form>
				</div>
			</div>
		';
    }
    function RegisterForm(){
		return '
			<div class="form_block">
				<div id="title">
					Registration
				</div>
				<div class="body">
				' . ($_POST ? $this->CheckRegisterData(
                        $_POST['f_name'],
                        $_POST['l_name'],
                        $_POST['email'],
                        $_POST['pass1'],
                        $_POST['pass2']) : '') . '
					<form id="logform" action="?a=register" method="POST" >
						<input type="text" name="f_name" placeholder="First Name" />
						<input type="text" name="l_name" placeholder="Last Name" />
						<input type="text" name="email" placeholder="Email" />
						<input type="password" name="pass1" placeholder="Password" />
						<input type="password" name="pass2" placeholder="Repeat Password" />
						<input type="submit" value="Register" /> <a class="regBtn" href="?a=login">Login</a>
					</form>
				</div>
			</div>
		';
    }
    function Is_Login(){
		$bd = new Connect;
		if (isset($_COOKIE['id']) and isset($_COOKIE['sess']))
		{
			$id = intval($_COOKIE['id']);
			$userdata = $bd->prepare("SELECT id, session FROM users WHERE id =:id_user LIMIT 1");
			$userdata -> execute(array(
					'id_user' => $id
			));
			$userdataa = $userdata->fetch(PDO::FETCH_ASSOC);
			if(($userdataa['session'] != $_COOKIE['sess'])
			or ($userdataa['id'] != intval($_COOKIE['id'])))
				{
					setcookie('id', '', time() - 60*24*30*12, "/", NULL);
					setcookie('sess', '', time() - 60*24*30*12, "/", NULL);
					return FALSE;
				}else{
					return TRUE;
				}
		}else{
			return FALSE;
		}
	}
}
?>