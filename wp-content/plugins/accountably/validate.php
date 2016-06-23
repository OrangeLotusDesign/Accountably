<?php
	function validateName($name){
		//if it's NOT valid
		if(strlen($first_name) < 4)
			return false;
		//if it's valid
		else
			return true;
	}
	function validateEmail($email){
		return ereg("^[a-zA-Z0-9]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$", $email);
	}
?>