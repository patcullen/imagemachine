<?php
include "SimpleImage.php"; // a rudimentary class that provides a ore opaque interface to working with images

// the errorhandling below converst all warnings into errors.
function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
	if (0 === error_reporting()) 
	return false;
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('handleError');

// get parameters required for store
$action = getParameter('action');
$key = getParameter('key');
$guid = getParameter('guid');
// aditional optional paramters optional for read
$size = getParameter('size');

// first basic check - cancel further processing if no action is specified
if (is_null($action)) returnJson('{"status":"er","msg":"invalid action"}','invalid action');

// import settings
include_once 'settings.php';

// basic check - if guid is short, then probably a bum guid
if (strlen($guid) < 10) returnJson('{"status":"er","msg":"invalid guid. too short (<10)"}','invalid guid - too short');

// basic check - if environment not setup correctly, then can't continue
checkEnvironment();

// respond to a store action
if ($action === 'store') {
	// basic check - if no key supplied or incorrect, then no further processing
	if ($key != $settings['key_store']) returnJson('{"status":"er","msg":"invalid key"}','invalid store key');
	
	// decode base64 encoded url
	$guid = base64_decode($guid, true);
	if ($guid === false) returnJson('{"status":"er","msg":"invalid guid, not base64"}','invalid guid - not b64');
	if(filter_var($guid, FILTER_VALIDATE_URL) === false) returnJson('{"status":"er","msg":"invalid guid, not a valid url"}','invalid guid - not url');
	
	$md5 = md5($guid);
	$dest = buildPath(array('raw', $md5[0], $md5[1])).$md5.'.'.$settings['image_storage_extension']; // should be something like:  location/on/hdd/raw/4/3/43de0ce4e53bbe082e094e5df3b0f640.jpg
	if (file_exists($dest)) returnJson('{"status":"ok","msg":"already have","guid":"'.$md5.'"}','store already have'); // don't download again if we have it
	
	$temp = buildpath(array('temp')).$md5.'.'.pathinfo($guid, PATHINFO_EXTENSION);
	download_file($guid, $temp);
	shell_exec($settings['imageMagickConvert'].' '.$temp.' '.$dest);
	unlink($temp);
	
	if (!file_exists($dest)) returnJson('{"status":"er","msg":"environment check: storage directory not writable, asset"}','invalid');
	
	returnJson('{"status":"ok","msg":"stored","guid":"'.$md5.'"}','stored');
}

// respond to a store action
if ($action === 'read') {
	// basic check - if no key supplied or incorrect, then no furhter processing
	if ($key != $settings['key_read']) returnJson('{"status":"er","msg":"invalid key"}','invalid read key');

	if (is_null($size)) $size = $settings['default_size']; // make sure a size is specified
	if (!array_key_exists($size, $settings['sizes'])) returnJson('{"status":"er","msg":"invalid size"}','invalid size'); // check size is a valid size
	$orig = $settings['storage_url'].'raw/'.$guid[0].'/'.$guid[1].'/'.$guid.'.'.$settings['image_storage_extension']; // dont use build path so that a malicious individual could not create millions of directories by simply providing every hash known to man
	if (!file_exists($orig)) returnJson('{"status":"er","msg":"invalid guid"}','invalid guid - missing'); // if base original image not found then error out
	$source = buildPath(array('cache', $size, $guid[0], $guid[1])).$guid.'.'.$settings['image_storage_extension'];
	if (!file_exists($source)) { // if the desired size doesn't exist, then create it
		$image = new SimpleImage($orig);
		if ($settings['sizes'][$size]['cropping'] === 'fit') $image->fitInSquare($settings['sizes'][$size]['length']); else
		if ($settings['sizes'][$size]['cropping'] === 'crop') $image->resizeToSquare($settings['sizes'][$size]['length']); else
		if ($settings['sizes'][$size]['cropping'] === 'best') $image->perimeterScale($settings['sizes'][$size]['perimiter']); else
		$image->resize($settings['sizes'][$size]['x'], $settings['sizes'][$size]['y']);
		$image->save($source, $settings['image_serve_type'], (isset($settings['sizes'][$size]['quality']) ? $settings['sizes'][$size]['quality'] : $settings['quality']));
	}
	
	$out = new SimpleImage($source);
	if ($settings['logging_enabled'] === true) logStuff('serve image', filesize($source));
	$out->output($settings['image_serve_type']);
	die;
}

// if request wasn't caught by now, then action was not an acceptable action
returnJson('{"status":"error","msg":"invalid action"}','invalid action');

function buildPath($path) { // concatenate a series of elements in an array to form a directory structure. if directory structure doesnt exist, it will be created.
	global $settings;
	$dest = $settings['storage_url'];
	for ($i = 0; $i < sizeof($path); $i++) {
		$dest .= $path[$i].'/';
		if (!file_exists($dest)) {
			mkdir($dest);
			if (!file_exists($dest)) returnJson('{"status":"er","msg":"environment check: path could not be built"}','cant build path');
		}
	}
	return $dest;
}

function checkEnvironment() { // check the environment has basic functionality
	global $settings;
	if (!file_exists(buildpath(array()))) returnJson('{"status":"er","msg":"environment check: storage folder does not exist"}','environment');
	buildPath(array('raw'));
	buildPath(array('temp'));
}

function download_file($source, $dest) { // wrapper for downloading files from specified source and destination
	try {
		$content = curl($source);
		file_put_contents($dest, $content);
		return file_exists($dest);
	} catch (Exception $e) {
		returnJson('{"status":"er","msg":"could not download source"}','cont download');
	}
}

function curl($url) {
	$header = array(
		'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36',
		'Content-Type: application/x-www-form-urlencoded',
		'Accept: */*',
		'Accept-Encoding: gzip,deflate,sdch',
		'Accept-Language: en-ZA,en;q=0.8,en-US;q=0.6',
		'Cookie: arp_scroll_position=0'
	);	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	return curl_exec($ch);
}

function logStuff($ted, $length = 0) {
	global $settings, $action, $key, $guid, $size;
	if ($settings['logging_enabled'] === true) {
		if ($settings['log'][$ted] === true) {
			buildPath(array($settings['log_location']));
			$log = 
				$_SERVER['REQUEST_TIME'].','.
				$_SERVER['REQUEST_METHOD'].','.
				$_SERVER['REMOTE_ADDR'].','.
				$ted . ','.
				$action . ','.
				$key . ','.
				$guid . ','.
				$size . ','.
				$length . ', '.
				"\n";
			$filename = $settings['storage_url'] . $settings['log_location'] . date_format(new DateTime('now'), $settings['log_prefix']) . $settings['log_filename'];
			file_put_contents($filename, $log, FILE_APPEND);
		}
	}
}

function returnJson($data,$ted) { // return a json message
	global $settings;
	if ($settings['logging_enabled'] === true) logStuff($ted, strlen($data));
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1998 05:00:00 GMT');
	header('Content-type: application/json');
	echo $data;
	die;
}

function getParameter($p) { // safetly get a parameter from the reuest
	global $_REQUEST;
	return (isset($_REQUEST[$p]) ? $_REQUEST[$p] : null);
}
?>