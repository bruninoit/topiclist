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