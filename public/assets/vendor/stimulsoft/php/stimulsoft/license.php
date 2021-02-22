<?php

// You can check the user authorization to send a license key only if the result is positive.

if (file_exists("license.key")) {
	$license = file_get_contents("license.key");
	echo $license;
}

?>