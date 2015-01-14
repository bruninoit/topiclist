<?php
/** 
* 
* @package StaffIt - Toic List 
* @copyright (c) 2014 brunino
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2 
* 
*/ 
namespace staffit\topiclist\event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */	
	protected $config;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\auth\auth */
	protected $auth;
	/** @var \phpbb\user */
	protected $user;
	protected $root_path;
	
	protected $phpEx;
/** 
 	* Constructor 
 	* 
 	* @param \phpbb\config\config   		$config             	 Config object 
 	* @param \phpbb\db\driver\driver_interface      $db        	 	 DB object 
 	* @param \phpbb\template\template    		$template  	 	 Template object 
 	* @param \phpbb\auth\auth      			$auth           	 Auth object 
 	* @param \phpbb\use		     		$user           	 User object 
 	* @param	                		$root_path          	 Root Path object 
 	* @param                  	     		$phpEx          	 phpEx object 
 	* @return \staffit\toptentopics\event\listener 
 	* @access public 
 	*/ 
public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\auth\auth $auth, \phpbb\user $user, $root_path, $phpEx) 
{
   $this->config = $config;
   $this->db = $db;
   $this->template = $template; 
   $this->auth = $auth;
   $this->user = $user;
   $this->root_path = $root_path;
   $this->phpEx   = $phpEx ;
}
/** 
 	* Assign functions defined in this class to event listeners in the core 
 	* 
 	* @return array 
 	* @static 
 	* @access public 
 	*/ 
static public function getSubscribedEvents()	
{
return array(			
'core.user_setup'						=> 'setup',
'core.viewtopic_modify_post_row' => 'viewtopic_add',
);	
}

public function setup($event)	{	
//language start
$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'staffit/topiclist',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
}

//controllata
function tlist_viewtopic($text)
{
global $forum_id;
	//Jumpstation
	preg_match_all("#\[tlistindex\](.*?)\[/tlistindex\]#", $text, $matches);
	$ctr = 0;
	$res = "";
	foreach($matches[0] as $key=>$val)
	{
		$ctr++;
		$tlist_str = topic_list_index($matches[1][$key]);
		$text = str_replace($val, $tlist_str, $text);
	}
	//List
	$text = str_replace("[tlist]", "[tlist=".$forum_id."]", $text);
	preg_match_all("#\[tlist\=(.*?)\](.*?)\|(.*?)\[/tlist\]#", $text, $matches);
	$ctr = 0;
	$res = "";
	foreach($matches[0] as $key=>$val)
	{
		$ctr++;
		$tlist_str = tlist_build($matches[1][$key], $matches[2][$key], $matches[3][$key]);
		$text = str_replace($val, $tlist_str, $text);
	}
	return($text);
}
function topic_list_index($parms)
{
global $forum_id, $topic_id;
	if (empty($parms))
		$parms = "0|";
	$parms_array = explode ("|", $parms);
	$res = ''; // here can be added a message like 'Jump to: '
	foreach ($parms_array as $key => $row)
	{
		$tmp = ' <a href="'.append_sid("{$this->root_path}viewtopic.php", 'f='.$forum_id.'&t='.$topic_id).'#tl_{TITLE}">{TITLE}</a>';
		$row = str_replace(" ", "", $row);
		$res .= str_replace("{TITLE}", $row, $tmp);
	}
	return $res;
}
function tlist_build($forum, $inizio, $fine)
{
global $forum_id, $topic_id;
	if (empty($forum))
		return '';
	$inizio = strtoupper($inizio);
	if (empty($inizio))
		$inizio = '0';
	$fine = strtoupper($fine);
	if (empty($fine))
		$fine = $inizio;
	$sql_start = "UCASE(LEFT(TRIM(LEADING '[' FROM TRIM(topic_title)), ".strlen($inizio).")) >= '$inizio'";
	$sql_end = "UCASE(LEFT(TRIM(LEADING '[' FROM TRIM(topic_title)), ".strlen($fine).")) <= '$fine'";
	//Numeric cases
	if ( substr($inizio, 0, 1) == "#" )
	{
		$sql_start = "LEFT(TRIM(LEADING '[' FROM TRIM(topic_title)), 1) >= '0'";
		$sql_end = "LEFT(TRIM(LEADING '[' FROM TRIM(topic_title)), 1) <= '9'";
		$fine = '';
	}
	if ( substr($fine, 0, 1) == "#" )
		$sql_end = ""; //bah
	// Other cases
	if ( substr($inizio, 0, 1) == "@" )
	{
		$sql_start = "UCASE(TRIM(LEADING '[' FROM TRIM(topic_title))) NOT REGEXP '^([A-Z|0-9])'";
		$sql_end = "";
		$fine = '';
	}
	$sql = 'SELECT topic_id, icon_id, topic_title,
 	       topic_poster, topic_first_poster_name, topic_first_poster_colour, topic_time
 	       FROM ' . TOPICS_TABLE . '
	       WHERE ( forum_id IN ('.$forum.') AND topic_type = 0 AND topic_approved = 1 AND topic_moved_id = 0 )
	       AND ( ' . $sql_start . (empty($sql_end) ? '' : ' AND ' . $sql_end ) . ' )
	       ORDER BY topic_title';
	$result = $this->db->sql_query($sql);
	//Title
	$ttitle = $inizio;
	if ( $ttitle == '#' )
		$ttitle = '0-9';
	if ( $fine != $inizio && !empty($fine) )
		$ttitle.=" - ".$fine;
	$ttitle = strtoupper($ttitle);
	$res = "";
	$res .='<div class="panel" id="tlist"><div class="inner"><span class="corners-top"><span></span></span>';
	$res .='<a name="tl_'.str_replace(" ", "", $ttitle).'" class="tlist">'.$ttitle.'</a><a href="'.append_sid("{$this->root_path}viewtopic.php", 'f='.$forum_id.'&t='.$topic_id).'#wrap" class="top" style="float: right;">Top</a>';
	$res .='<span class="corners-bottom"><span></span></span></div></div>';
	$icon_prefix = '<img src="'."{$this->root_path}{$config['icons_path']}/";
	$no_topics = '<div style="margin-left: 16px;"> <i>-</i></div>';
	while ($row = $this->db->sql_fetchrow($result))
	{
		// Topic author
		// Change 'no_profile' to 'full' if you want the profile link
		$topic_author = get_username_string('no_profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']);
		// Topic time
		$topic_time = $this->user->format_date($row['topic_time']);
		
		$no_topics = '';
		
		//HELP!
		$link = append_sid("{$this->root_path}viewtopic.php", 't='.$row['topic_id']); 
		//HELP!
		
		
		$cur_icon = $icon_prefix;
		$cur_icon .= (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : 'misc/empty.gif';
		$cur_icon .='" alt="" />';
		$res .= '<div style="float: left; width: 60%"><span style="vertical-align: middle;">'. $cur_icon.'</span> <a href='.$link.' title="">'.$row['topic_title'].'</a></div><div style="float: left; width: 20%;" class="tlist_author">'.$user->lang['POST_BY_AUTHOR'].' '.$topic_author.'</div><div class="tlist_time">'.$topic_time.'</div>';
	}
	$this->db->sql_freeresult($result);
	return $res.$no_topics;
}
function fixTListTag($message)
{
global $forum_id;
	// Find all the tlist tags - with or without boardid.
	$message = str_replace("[tlist]", "[tlist=$forum_id]", $message);
	preg_match_all('~(\[tlist(=.*?)\])(.*?)\|(.*?)\[/tlist\]~is', $message, $matches, PREG_PATTERN_ORDER);
	$replaces = array();
	foreach ($matches[0] as $match => $dummy)
	{
		$matches[2][$match] = preg_replace('/=*\s*/', '', $matches[2][$match]);
		$boards = explode(",", $matches[2][$match]);
		foreach ($boards as $id => $val){
				if (!is_numeric($val))
					unset($boards[$id]);
		}
		$matches[2][$match] = implode(",", $boards);
		$desired_board = empty($matches[2][$match]) ? $forum_id : $matches[2][$match];
		$inizio = substr($matches[3][$match], 0, 3);
		$fine = substr($matches[4][$match], 0, 3);
		$replaces[$matches[0][$match]]= '[tlist='.$desired_board.']'.$inizio.'|'.$fine.'[/tlist]';
	}
	// If any tlist tags were actually changed...
	if (!empty($replaces))
		$message = strtr($message, $replaces);
	return $message;
}

public function viewtopic_add($event)	
{
//$array_topic_data=$event['post_row'];
$oldmessage=$event['post_row']['MESSAGE'];
$newmessage=$oldmessage;
$newmessage.= tlist_viewtopic($newmessage);
$event['post_row']['MESSAGE'] =  $newmessage;
return $event;
}



}
