<?php
/**
 * Hanya - A rapid Website Engine
 *
 * @author Joël Gähwiler <joel.gaehwiler@bluewin.ch>
 * @version 1.0
 * @copyright (c) 2011 Joël Gähwiler 
 * @package Hanya
 **/

class Helper {
	
	/* FILE HANDLING */
	
	// Import Class
	public static function import() {
		foreach(func_get_args() as $file) {
			require($file.".php");
		}
	}
	
	// Import Classes in Directory
	public static function import_folder($folder) {
		$files = self::read_directory($folder);
		foreach($files["."] as $file) {
			require($folder."/".$file);
		}
	}
	
	// Read a Directorys Content
	public static function read_directory($directory) {
		$return = array("." => array());
		if(is_dir($directory)) {
			$handler = opendir($directory);
			while ($node = readdir($handler)) {
				if($node[0] != ".") {
					if(is_dir($directory."/".$node)) {
						$return[$node] = self::read_directory($directory."/".$node);
					} else {
						$return["."][] = $node;
					}
				}
			}
			closedir($handler);			
		}
		return $return;
	}
	
	// Get Content of a File
	public static function read_file($file) {
		if(file_exists($file)) {
			$time = filemtime($file);
			if(Registry::get("site.newest_file") < $time) {
				Registry::set("site.newest_file",$time);
			}
			return file_get_contents($file);
		} else {
			die("Hanya: File '".$file."' does not exist!");
		}
	}
	
	// Eval and get Content of a File
	public static function eval_file($file) {
		if(file_exists($file)) {
			$time = filemtime($file);
			if(Registry::get("site.newest_file") < $time) {
				Registry::set("site.newest_file",$time);
			}
			ob_start();
			include($file);
			$data = ob_get_contents();
			ob_end_clean();
			return $data;
		} else {
			die("Hanya: File '".$file."' does not exist!");
		}
	}
	
	// Get Contents of URL
	public static function read_url($url) {
		return file_get_contents($url);
	}
	
	// Get Permissions
	public static function permission($file,$octal=false) {
		if(!file_exists($file)) return false;
		$perms = fileperms($file);
		$cut = $octal ? 1 : 2;
		return substr(decoct($perms), $cut);
	}
	
	// Unzip Archive to Directory
	public static function unzip($file,$folder) {
		
		// Open Zip
		$zip = zip_open($file);
		
		// Get Zip Elements
		while($item = zip_read($zip)) {
			
			// Get Item Path
			$path = zip_entry_name($item);
			
			// Check Filetype
			if(substr($path,-1) == "/") {
				
				// Create Directory
				if(!Helper::create_directory($folder.$path)) {
					die("Failed to create Directory: '".$folder.$path."'!");
				}
				
			} else {
				
				// Create new File
				if(!touch($folder.$path)) {
					die("Failed to create File: '".$folder.$path."'!");
				}
				
				// Open Empty File
				$file = fopen($folder.$path,"r+");
				
				// Set Content
				fwrite($file,zip_entry_read($item,zip_entry_filesize($item)));
				
				// Close
				fclose($file);
			}
				
		}
		
		// Close Zip
		zip_close($zip);
	}
	
	// Remove Directory
	public static function remove_directory($dir) {
		if(is_dir($dir)) {
			self::empty_directory($dir);
			return rmdir($dir);
		} else {
			return false;
		}
	}
	
	// Create Directory
	public static function create_directory($dir) {
		return mkdir($dir,0777);
	}
	
	// Empty Directory
	public static function remove_directory($dir) {
		if(is_dir($dir)) {
			$objects = scandir($dir); 
	    foreach ($objects as $object) {
				if ($object != "." && $object != "..") { 
	      	if (filetype($dir."/".$object) == "dir") {
						Helper::remove_directory($dir."/".$object);
					} else {
						unlink($dir."/".$object);
					}
				}
			}
			reset($objects);
			return true;
		} else {
			return false;
		}
	}
	
	// Copy Directory
	public static function copy_directory($src, $dst) {
	  if (is_dir($src)) {
	    self::create_directory($dst);
	    $files = scandir($src);
	    foreach ($files as $file) {
				if ($file != "." && $file != "..") {
					self::copy_directory("$src/$file", "$dst/$file");
				}
			}
	  } else if (file_exists($src)) { 
			copy($src, $dst);
		} else {
			return false;
		}
		return true;
	}
	
	/* PLUGIN HANDLING */
	
	// Dispatch an Event to Plugins
	public static function dispatch($event,$options=null) {
		foreach(Registry::get("loaded.plugins") as $plugin) {
			$classname = ucfirst($plugin)."Plugin";
			if(class_exists($classname)) {
				if(method_exists($classname,$event)) {
					$classname::$event($options);
				}
			} else {
				die("Hanya: Plugin '".$plugin."' defines no Class '".$classname."!");
			}
		}
	}
	
	/* LOCATION HANDLING */
	
	public static function redirect_to_referer() {
		if(Registry::get("request.referer") != "") {
			HTTP::location(Registry::get("request.referer"));
		} else {
			HTTP::location(Registry::get("base.url"));
		}
		exit;
	}
	
	public static function url($add="") {
		return Registry::get("base.url").$add;
	}
	
	public static function redirect($add="") {
		HTTP::location(self::url($add));
		exit;
	}
	
	/* SPECIAL FUNCTIONS */
	
	// Wrap HTML as Editable
	public static function wrap_as_editable($html,$definition,$id) {
		return HTML::div(null,"hanya-editable",$html,array("data-id"=>$id,"data-definition"=>$definition));
	}
	
	// Get a Tree File from Segments
	public static function tree_file_from_segments($segments) {
		if($segments[0] != "") {
			return "tree/".join($segments,"/").".html";
		} else {
			return "tree/index.html";
		}
	}

}