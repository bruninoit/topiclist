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
public function viewtopic_add($event)	
{
//$array_topic_data=$event['post_row'];
$rowmessage=$event['post_row'];
$message=$rowmessage['MESSAGE'];
$post_id=$rowmessage['POST_ID'];
if(strpos($message,"tlist]"))
{
//costanti di lingua momentanee
$l_topic_list=$this->user->lang['TOPIC_TITLE']; //da cambiare
$l_topic_no=$this->user->lang['TOPIC_TITLE']; //da cambiare
$l_topic_title=$this->user->lang['TOPIC_TITLE'];
$l_topic_author=$this->user->lang['TOPIC_AUTHOR'];
$l_topic_date=$this->user->lang['TOPIC_DATE'];
define("TOPIC_LIST", "$l_topic_list");
define("TOPIC_TITLE", "$l_topic_title");
define("TOPIC_AUTHOR", "$l_topic_author");
define("TOPIC_DATE", "$l_topic_date");
define("NO_TOPIC", "$l_topic_no");


preg_match_all("#\[tlist\](.*?)\[/tlist\]#", $message, $forum_id_from);
if($forum_id_from[1][0])
{
$forum_id=$forum_id_from[1][0];
}else{
$forum_query=$this->db->sql_query("SELECT forum_id
    FROM " . POSTS_TABLE . "
    WHERE post_id = $post_id");
    $forum_id_array=$this->db->sql_fetchrow($forum_query);
$forum_id=$forum_id_array['forum_id'];
}


$topic_list="<h3>" .TOPIC_LIST. "</h3>";
$topic_list .= "<table border=\"2\"><tr align=\"center\"><th width=\"300\"><b>" .TOPIC_TITLE. "</b></th><th width=\"200\"><b>" .TOPIC_AUTHOR. "</b></th><th width=\"200\"><b>" .TOPIC_DATE. "</b></th></tr>";
$lista_topics=$this->db->sql_query("SELECT tt.topic_id, tt.forum_id, tt.topic_title, tt.topic_time, tt.topic_moved_id, tt.topic_poster, tt.topic_first_poster_name,
    ft.forum_id, ft.forum_name
    FROM " . TOPICS_TABLE . " tt, " . FORUMS_TABLE . " ft 
    WHERE tt.topic_moved_id = 0
    AND tt.topic_type <= 0
    AND tt.forum_id = ft.forum_id
    AND tt.topic_visibility=1
    AND ft.forum_id=$forum_id
    ORDER BY tt.topic_title ASC");
while($topics=$this->db->sql_fetchrow($lista_topics))
{
$titolo_topic=$topics['topic_title'];
$autore_topic=$topics['topic_first_poster_name'];
$id_autore_topic=$topics['topic_poster'];
$id_topic=$topics['topic_id'];
//$data_topic=date("d/m",$topics['topic_time']); 
$data_topic=$this->user->format_date($topics['topic_time']);
$topic_list.="<tr align=\"center\"><td><a href=\"{$this->root_path}viewtopic.{$this->phpEx}?t=$id_topic\">$titolo_topic</a></td><td><a href=\"{$this->root_path}memberlist.{$this->phpEx}?mode=viewprofile&u=$id_autore_topic\">$autore_topic</a></td><td>$data_topic</td></tr>";
}
$topic_list.="</table>";
$message=str_replace("[tlist][/tlist]", "$topic_list", "$message");
$message=str_replace("[tlist]" . $forum_id . "[/tlist]", "$topic_list", "$message");
$rowmessage['MESSAGE']=$message;
$event['post_row'] = $rowmessage;
}
}
}
