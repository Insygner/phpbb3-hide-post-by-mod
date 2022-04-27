<?php
/**
*
* @package HidePostByMod
* @copyright (c) 2022 Insygner (https://github.com/insygner)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace insygner\hidepostbymod\event;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface;
use phpbb\event\data;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var array */
	protected $config;

	/* @var user */
	protected $user;

	/* @var auth */
	protected $auth;

	/* @var Language */
	protected $lang;

	/* @var database */
	protected $db;

	/* @var template */
	protected $template;

	/* @var helper */
	protected $helper;
	
	/**
	 * Constructor
	 *
	 * @param config    $config
	 * @param user    	$user
	 * @param auth  	$auth
	 * @param lang   	$lang
	 * @param db  		$db
	 * @param template 	$template
	 * @param helper 	$helper
	 */
	public function __construct(config $config, user $user, auth $auth, language $lang, driver_interface $db, template $template, helper $helper)
	{
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->lang = $lang;
		$this->db = $db;
		$this->template = $template;
		$this->helper = $helper;
	}
		
	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup'						=> 'load_user_language',
			'core.memberlist_prepare_profile_data'	=> 'memberlist_prepare_profile_data',
			// 'core.viewtopic_before_f_read_check'	=> 'viewtopic_before_f_read_check',
			'core.viewtopic_post_rowset_data'		=> 'viewtopic_post_rowset_data',
			'core.viewtopic_cache_user_data'		=> 'viewtopic_cache_user_data',
			'core.viewtopic_modify_post_row'		=> 'viewtopic_modify_post_rows',
			'core.search_get_posts_data'			=> 'search_get_posts_data',
			'core.topic_review_modify_row'			=> 'topic_review_modify_row',
		];
	}
		
	/**
	 * load_user_language
	 *
	 * @param  \phpbb\event\data $event
	 */
	public function load_user_language($event) {
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'insygner/hidepostbymod',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}
	
	/**
	 * memberlist_prepare_profile_data
	 *
	 * @param  \phpbb\event\data $event
	 */
	public function memberlist_prepare_profile_data(data $event) {
		if($this->auth->acl_get('m_')) {
			if($event['data']['user_posts_hidden_bymod']) {
				$hide_button = $this->helper->route('insygner_hidepostbymod_controller_main', ['mode' => 'showall', 'user' => $event['data']['user_id']], true);
				$event['template_data'] = array_merge($event['template_data'], [
					'MOD_H_POST_H' 	=> $this->auth->acl_get('m_') && $event['data']['user_id'] != ANONYMOUS ? $hide_button : '',
					
				]);				
			}else{
				$hide_button = $this->helper->route('insygner_hidepostbymod_controller_main', ['mode' => 'hideall', 'user' => $event['data']['user_id']], true);				
				$event['template_data'] = array_merge($event['template_data'], [
					'MOD_H_POST_S' 	=> $this->auth->acl_get('m_') && $event['data']['user_id'] != ANONYMOUS ? $hide_button : '',
				]);							
			}
		}
	}
	
	/**
	 * viewtopic_before_f_read_check - incative
	 */
	public function viewtopic_before_f_read_check() {
		$this->lang->add_lang('common', 'insygner/hidepostbymod');
	}
	
	/**
	 * viewtopic_post_rowset_data
	 *
	 * @param  \phpbb\event\data $event
	 */
	public function viewtopic_post_rowset_data(data $event) {
		$event['rowset_data'] = array_merge($event['rowset_data'], [
			'post_hidden_bymod'	=> $event['row']['post_hidden_bymod'],
		]);
	}
	
	/**
	 * viewtopic_cache_user_data
	 *
	 * @param  \phpbb\event\data $event
	 */
	public function viewtopic_cache_user_data(data $event) {
		$event['user_cache_data'] = array_merge($event['user_cache_data'], [
			'user_posts_hidden_bymod'	=> $event['row']['user_posts_hidden_bymod'],
		]);
	}
	
	/**
	 * viewtopic_modify_post_rows
	 *
	 * @param  \phpbb\event\data $event
	 */
	public function viewtopic_modify_post_rows(data $event) {
		$events = $event['post_row'];
		//print_r($post_author);
		if($this->auth->acl_get('m_')) {
			$this->template->assign_var('CAN_HIDE_POST', true);
		}

		if($event['user_poster_data']['user_posts_hidden_bymod']) {			
			if(!$this->auth->acl_get('m_')) {
				$events = [
					'MESSAGE'		=> $this->lang->lang('POST_HIDDEN_BY_MOD'),
					'RANK_TITLE'	=> '',
					'RANK_IMG'		=> '',
					'POSTER_POSTS'	=> '',
					'POSTER_AVATAR'	=> '',
					'POST_AUTHOR_FULL'	=> 'Hidden',
					'POST_DATE'		=> '',
					'U_CONTACT'		=> '',
					'POST_SUBJECT'	=> '',
					'POSTER_JOINED' => '',
					'S_ONLINE'		=> '',
				];
				$event['post_row'] = array_merge($event['post_row'], $events);
			}else{
				$events['MESSAGE'] = $this->lang->lang('ALL_POST_HIDDEN_BY_MOD') . '<br /><hr></hr>' . $events['MESSAGE'];
				$event['post_row'] = $events;
			}
		}else if($event['row']['post_hidden_bymod']) {
			if(!$this->auth->acl_get('m_')) {
				$events = [
					'MESSAGE'		=> $this->lang->lang('POST_HIDDEN_BY_MOD'),
					'RANK_TITLE'	=> '',
					'RANK_IMG'		=> '',
					'POSTER_POSTS'	=> '',
					'POSTER_AVATAR'	=> '',
					'POST_AUTHOR_FULL'	=> 'Hidden',
					'POST_DATE'		=> '',
					'POST_SUBJECT'	=> '',
					'POSTER_JOINED' => '',
					'S_ONLINE'		=> '',
					
				];
				
				$event['post_row'] = array_merge($event['post_row'], $events);
			}else{
				$hide_button = $this->helper->route('insygner_hidepostbymod_controller_main', ['mode' => 'show', 'post' => $event['row']['post_id']], true);
				$events['MESSAGE'] = $this->lang->lang('POST_HIDDEN_BY_MOD') . '<br /><hr></hr>' . $events['MESSAGE'];
				$event['post_row'] = $events;
			}
			$event['post_row'] = array_merge($event['post_row'], [
				'MOD_H_POST_S' => $this->auth->acl_get('m_') && $event['poster_id'] != ANONYMOUS ? $hide_button : '',
				'U_QUOTE'		=> '',
			]);	
		}else{	
			$hide_button = $this->helper->route('insygner_hidepostbymod_controller_main', ['mode' => 'hide', 'post' => $event['row']['post_id']], true);
			$event['post_row'] = array_merge($event['post_row'], [
				'MOD_H_POST_H' => $this->auth->acl_get('m_') && $event['poster_id'] != ANONYMOUS ? $hide_button : '',
			]);	
		}
	}	

	/**
	 * search_get_posts_data
	 *
	 * @param  \phpbb\event\data $event
	 */
	public function search_get_posts_data($event) {
		$where = $event['sql_array'];
		$where['WHERE'] = is_null($where['WHERE']) ? 'p.post_hidden_bymod = 0 AND u.user_posts_hidden_bymod = 0' : $where['WHERE'] . ' AND p.post_hidden_bymod = 0 AND u.user_posts_hidden_bymod = 0';
		$event['sql_array'] = $where;
	}
		
	/**
	 * topic_review_modify_row
	 *
	 * @param  \phpbb\event\data $event
	 */
	public function topic_review_modify_row($event) {
		$events = $event['post_row'];
		if(!$this->auth->acl_get('m_') && $event['row']['post_hidden_bymod']) {
			$events = [
				'MESSAGE'		=> $this->lang->lang('POST_HIDDEN_BY_MOD'),
				'POST_AUTHOR_FULL' => 'Hidden',
				'POST_AUTHOR' => 'Hidden',
				'POST_AUTHOR_COLOUR' => '',
				'U_POST_AUTHOR' => '',
				'POST_DATE' => '',

			];
			$event['post_row'] = $events;
		}else{
			$events['MESSAGE'] = $this->lang->lang('ALL_POST_HIDDEN_BY_MOD') . '<br /><hr></hr>' . $events['MESSAGE'];
			$event['post_row'] = $events;
		}		
		
	}
}
