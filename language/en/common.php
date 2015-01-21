<?php
/** 
* 
* @package StaffIt - Topic List
* @copyright (c) 2014 brunino
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2 
* 
*/ 
if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}
$lang = array_merge($lang, array(
	'TOPIC_TITLE'			=> 'Title',
	'TOPIC_AUTHOR'			=> 'Author',
	'TOPIC_DATE'			=> 'Date',
));
