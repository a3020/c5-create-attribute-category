#!/usr/bin/php -q
<?php

/*
	Author: Adri Kodde (@akodde)
	
	Purpose: 
		Creates files for an attribute category to save data against.
		
	Usage: 
		php create.php --at-name-lowercase=blog_post --at-name-camelcase=BlogPost --table-name-camelcase=BlogPosts
	
	Optional parameters: 
		--p-handle (package handle)
	
	Additional information:
		You need to run this file from the command line and provide at least three parameters. The package handle is optional, but recommended.
		Don't use existing names such as collection, page, file, etc. as they will likely interfere with existing models.	
*/

if (php_sapi_name() != 'cli') {
	echo 'You need to run this command from console.\n';
	exit(1);
}

$arguments = array_slice($argv, 1);

$PACKAGE_HANDLE = false;

foreach($arguments as $val) {
	$val = explode('=', $val);
	switch(current($val)) {
		case '--at-name-lowercase':
			$ATTRIBUTE_NAME_LOWERCASE = next($val);
		break;
		case '--at-name-camelcase':
			$ATTRIBUTE_NAME_CAMELCASE = next($val);
		break;
		case '--table-name-camelcase':
			$TABLE_NAME_CAMELCASE = next($val);
		break;
		case '--p-handle':
			$PACKAGE_HANDLE = next($val);
		break;
	}
}

if(!isset($ATTRIBUTE_NAME_LOWERCASE) OR !isset($ATTRIBUTE_NAME_CAMELCASE) OR !isset($TABLE_NAME_CAMELCASE)){
	echo "Provide at least three parameters. Please try again.\n";
	exit(1);
}

if(strtolower($ATTRIBUTE_NAME_LOWERCASE) != $ATTRIBUTE_NAME_LOWERCASE){
	echo "Attribute category name should be lowercase!\n";
	exit(1);
}

function replaceTextHolders($source){
	global $PACKAGE_HANDLE;
	global $TABLE_NAME_CAMELCASE;
	global $ATTRIBUTE_NAME_CAMELCASE;
	global $ATTRIBUTE_NAME_LOWERCASE;
	
	$source = str_replace('TABLE_NAME', $TABLE_NAME_CAMELCASE, $source);
	$source = str_replace('CamelCaseObject', $ATTRIBUTE_NAME_CAMELCASE, $source);
	$source = str_replace('lowercase_object', $ATTRIBUTE_NAME_LOWERCASE, $source);
	
	if($PACKAGE_HANDLE){
		$source = str_replace("PACKAGE_HANDLE", ", '".$PACKAGE_HANDLE."'", $source);
	}
	
	return $source;
}


// CREATE REQUIRED FOLDERS
if(!file_exists('models/attribute/categories')){
	mkdir('models/attribute/categories', null, true);
}


// CREATE ATTRIBUTE CATEGORY (e.g. models/attribute/category/collection.php)
$file = file_get_contents('source/attribute/categories/object.php');
$file = replaceTextHolders($file);
file_put_contents('models/attribute/categories/'.$ATTRIBUTE_NAME_LOWERCASE.'.php', $file);


// CREATE OBJECT (e.g. models/collection.php)
$file = file_get_contents('source/object.php');
$file = replaceTextHolders($file);
file_put_contents('models/'.$ATTRIBUTE_NAME_LOWERCASE.'.php', $file);


// CREATE OBJECT LIST (e.g. models/page_list.php)
$file = file_get_contents('source/object_list.php');
$file = replaceTextHolders($file);
file_put_contents('models/'.$ATTRIBUTE_NAME_LOWERCASE.'_list.php', $file);


echo ">>> Files have been created! \n";
exit(1);