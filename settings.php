<?php

$settings = array(

	// one key is used for reading images (so that this can be public facing, but still prevent people from uploading)
	'key_read' => '~',

	// a separate secret key for storage. we don't want just any old monkey uploading images.
	'key_store' => 'some_very_long_string_of_numbers_and_letters_or_something',

	// a system path to a place to save all images uploaded to this server
	'storage_url' => 'f:/image_store/',
	
	// A set of predefined sizes allowed by our server - This is mainly here to prevent nasty people requesting images with random sizes
	'sizes' => array(
		'l' => array('cropping' => 'best', 'perimiter' => 800*4, 'quality' => 80),
		'm' => array('cropping' => 'crop', 'length' => 240, 'quality' => 75),
		's' => array('cropping' => 'crop', 'length' => 50, 'quality' => 30)
	),
	// If no size is specified, which size should we serve up?
	'default_size' => 'm',
	
	// the document extension/format to store all files in.
	'image_storage_extension' => 'jpg',		// this is required for imagemagick to know which file format to store in
	'image_serve_type' => IMAGETYPE_JPEG, // options: IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG // used by SimpleImage.php when processing images on read requests
	'quality' => 75, // used by SimpleImage.php when serving images (and saving the cached image)
	
	// A valid path to ImageMagicks convert executable - write it here as though you were executing it in your systems console
	'imageMagickConvert' => 'C:/Progra~1/ImageMagick-6.8.8-Q16/convert',
//	'imageMagickConvert' => '/usr/bin/convert',


	// Logging options
	'logging_enabled' => true,
	// where to dump the log
	'log_location' => 'logs/', 
	// log file prefix: any of the follow'Y-m-d H:i:s' replaced with respective date parts
	'log_prefix' => 'Y-m-d', 
	// stored inside $settings['storage_url'] + $settings['log_location']
	'log_filename' => '_log.csv', 
	// what messages/actions do you want to log?
	'log' => array(
		'invalid action' => true,
		'invalid size' => true,
		'invalid guid - too short' => true,
		'invalid store key' => true,
		'invalid read key' => true,
		'invalid guid - missing' => true,
		'invalid guid - not b64' => true,
		'invalid guid - not url' => true,
		'store already have' => true,
		'stored' => true,
		'environment' => true,
		'cont download' => true,
		'cant build path' => true,
		'serve image' => true
	)

);

?>