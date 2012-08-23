<?
	define(DB_USER,'root');
	define(DB_PASSWORD,'');
	define(DB_DB,'feed');
	define(DB_HOST,'192.168.0.202');


	define(DBG_MESSAGES,1);
	define(DBG_SHOW_QUERIES,0);

	define(FULL_URL,'http://feed/');
	define(FULL_PATH,'./');

//	error_reporting(128);

	include_once('lib/MySQL.class.php');
	include_once('lib/General.php');
	include_once('lib/perfmonitor.class.php');
	include_once('functions.php');
	include_once('lib/utf8.inc');
	include_once('lib/HTMLHighlight.php');

?>