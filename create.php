#!/usr/bin/php -q
<?php

/*
	Author: Adri Kodde (@akodde)
	Repo: https://github.com/akodde/c5-create-attribute-category
	
	Purpose: 
		Create files for an attribute category to save data against.
		
	Usage example: 
		php create.php --ac_l=bs_option --ac_c=BsOption --table=BsOptions --p_handle=booking_system --ac_id=optionID
	
	Optional parameters:
		--p-handle (package handle)
*/

if (php_sapi_name() != 'cli') {
	echo 'You need to run this command from console.\n';
	exit(1);
}

$arguments = array_slice($argv, 1);

// defaults
$PACKAGE_HANDLE = false;
$ATTRIBUTE_ID = 'itemID';

foreach($arguments as $val) {
	$val = explode('=', $val);
	switch(current($val)) {
		case '--ac_l': //attribute category lowercase
			$ATTRIBUTE_NAME_LOWERCASE = next($val);
		break;
		case '--ac_c': //attribute category camelcase
			$ATTRIBUTE_NAME_CAMELCASE = next($val);
		break;
		case '--ac_id': //attribute category identifier
			$ATTRIBUTE_ID = next($val);
		break;
		case '--table': //table name camelcase
			$TABLE_NAME_CAMELCASE = next($val);
		break;
		case '--p_handle': //package handle
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
	global $ATTRIBUTE_ID;
	
	$source = str_replace('TABLE_NAME', $TABLE_NAME_CAMELCASE, $source);
	$source = str_replace('CamelCaseObject', $ATTRIBUTE_NAME_CAMELCASE, $source);
	$source = str_replace('lowercase_object', $ATTRIBUTE_NAME_LOWERCASE, $source);
	$source = str_replace('ATTRIBUTE_ID', $ATTRIBUTE_ID, $source);
	
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


// CREATE DATABASE SCHEMA (db.xml)
$file = file_get_contents('source/db.xml');
$file = replaceTextHolders($file);
file_put_contents('models/db.xml', $file);

echo ">>> Files have been created! \n";
exit(1);