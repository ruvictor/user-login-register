<?php
require_once(realpath(dirname(__FILE__) . '/../../config.php'));
class User {
    function Dashboard(){
        echo 'Dashboard Content <a href="exit.php">Exit</a>';
    }
    function RegisterForm(){
        return 'Register Form';
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
    function CheckData($email, $pass){
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
					setcookie("check", $info['id'], time()+60*60*24*30, "/", NULL);
					setcookie("sess", $hash, time()+60*60*24*30, "/", NULL);
					echo("<script>location.href = '/user-login-register/?a=dashboard';</script>");
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
				' . ($_POST ? $this->CheckData($_POST['email'], $_POST['pass']) : '') . '
					<form id="logform" action="?a=login" method="POST" >
						<input type="text" name="email" placeholder="Email" />
						<input type="password" name="pass" placeholder="Password" />
						<input type="submit" value="Log in" />
					</form>
				</div>
			</div>
		';
    }
    function Is_Login(){
		$bd = new Connect;
		if (isset($_COOKIE['check']) and isset($_COOKIE['sess']))
		{
			$id = intval($_COOKIE['check']);
			$userdata = $bd->prepare("SELECT id, session FROM users WHERE id =:id_user LIMIT 1");
			$userdata -> execute(array(
					'id_user' => $id
			));
			$userdataa = $userdata->fetch(PDO::FETCH_ASSOC);
			if(($userdataa['session'] != $_COOKIE['sess'])
			or ($userdataa['id'] != intval($_COOKIE['check'])))
				{
					setcookie('check', '', time() - 60*24*30*12, "/", NULL);
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