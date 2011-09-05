<?php
/**
 * Hanya - A rapid Website Engine
 *
 * @author Joël Gähwiler <joel.gaehwiler@bluewin.ch>
 * @version 0.2
 * @copyright (c) 2011 Joël Gähwiler 
 * @package Hanya
 **/

class HTTP {
	
	// Set Content Type
	public static function content_type($type="text/html") {
		header("Content-type: ".$type);
	}
	
	// Set Location
	public static function location($location) {
		header("location:".$location);
	}
	
	// Set Not Found Header
	public static function not_found() {
		header("HTTP/1.0 404 Not Found");
	}

	// Request Authentication
	public static function authenticate($title,$message) {
		header('WWW-Authenticate: Basic realm="'.$title.'"');
		header("HTTP/1.0 401 Unauthorized");
		echo($message);
		exit;
	}

}