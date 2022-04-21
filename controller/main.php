<?php
/**
*
* @package HidePostByMod
* @copyright (c) 2022 Insygner (https://github.com/insygner)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace insygner\hidepostbymod\controller;

use phpbb\auth\auth;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface as db_interface;
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;

class main {
	/* @var database */
	protected $db;

	/* @var auth */
	protected $auth;

	/* @var user */
	protected $user;

	/* @var request */
	protected $request;

	/* @var Language */
	protected $lang;

	/* @var template */
	protected $template;

	/* @var helper */
	protected $helper;

	/* @var root_path */
	protected $root_path;

	/* @var php_ext */
	protected $php_ext;
		
	/**
	 * __construct
	 *
	 * @param db 		$db
	 * @param auth 		$auth
	 * @param user 		$user
	 * @param request	$request
	 * @param lang 		$lang
	 * @param template	$template
	 * @param helper	$helper
	 * @param root_path	$root_path
	 * @param php_ext	$php_ext
	 * @return void
	 */
	public function __construct(db_interface $db, auth $auth, user $user, request_interface $request, language $lang, template $template, helper $helper, $root_path, $php_ext) {
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
		$this->request = $request;
		$this->lang = $lang;
		$this->template = $template;
		$this->helper = $helper;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}
		
	/**
	 * Controller for route /hidepostbymod
	 *
	 * @throws http_exception
	 * @throws \Exception
	 *
	 * @return Response A Symfony Response object
	 */
	public function handle() {
		$postid	= $this->request->variable('post', 0);
		$userid	= $this->request->variable('user', 0);
		$mode	= $this->request->variable('mode', '');
		$f	= $this->request->variable('f', 0);

		$this->lang->add_lang('common', 'insygner/hidepostbymod');
		
		switch ($mode)
		{
			case 'hide':
				return $this->hide_post_by_mod($postid);
			break;
			
			case 'show':
				return $this->show_post_by_mod($postid);
			break;

			case 'hideall':
				return $this->hide_all_posts_by_mod($userid);
			break;

			case 'showall':
				return $this->show_all_posts_by_mod($userid);
			break;
 			default:
				return new \Symfony\Component\HttpFoundation\JsonResponse(array(
					'result'	=> $this->user->lang['POST_HIDDEN_NO_AUTH']
				)); 
		}
	}
		
	/**
	 * hide_post_by_mod
	 *
	 * @param $post_id
	 * 
	 * @return Response A Symfony Response object
	 */
	public function hide_post_by_mod($post_id) {		
		if (!$this->auth->acl_get('m_')) {
			return new \Symfony\Component\HttpFoundation\JsonResponse(array(
				'result'	=> $this->user->lang['POST_HIDDEN_NO_AUTH']
			));
		}
		
		$select_topic = 'SELECT topic_id, post_hidden_bymod FROM phpbb_posts WHERE post_id = ' . (int) $post_id;
		$result_topic = $this->db->sql_query($select_topic);
		$result_value = $this->db->sql_fetchrow($result_topic);
		$topic_id = $result_value['topic_id'];
		$hidden = $result_value['post_hidden_bymod'];
		$this->db->sql_freeresult($result_topic);
		
		if($hidden == 0) {
			$sql_ary = array(
				'user_id'		=> $this->user->data['user_id'],
				'log_ip'		=> $this->user->data['user_ip'],
				'log_time'		=> time(),
				'log_operation'	=> 'HIDE_POST',
				'log_type'		=> 1,
				'topic_id'		=> $topic_id,
				'post_id'		=> $post_id,
				'log_data'		=> '',
			);
			$this->db->sql_query('INSERT INTO phpbb_log ' . $this->db->sql_build_array('INSERT', $sql_ary));

			$sql_arr = [
				'h_post_id'	=> $post_id,
				'h_time'	=> time(),
				'h_userid'	=> $this->user->data['user_id'],
			];
			$sql_1 = 'INSERT INTO `phpbb_hidden_posts` ' . $this->db->sql_build_array('INSERT', $sql_arr);
			$this->db->sql_query($sql_1);

			$sql_2 = 'UPDATE ' . POSTS_TABLE . ' SET post_hidden_bymod = 1 WHERE post_id = ' . (int) $post_id;
			$this->db->sql_query($sql_2);
		
			return new \Symfony\Component\HttpFoundation\JsonResponse(array(
				'result'	=> $this->user->lang['POST_BACK_TO_POST_SUCCES']
			));
		}else{
			$sql_ary = array(
				'user_id'		=> $this->user->data['user_id'],
				'log_ip'		=> $this->user->data['user_ip'],
				'log_time'		=> time(),
				'log_operation'	=> 'UNHIDE_POST',
				'log_type'		=> 1,
				'topic_id'		=> $topic_id,
				'post_id'		=> $post_id,
				'log_data'		=> '',
			);
			$this->db->sql_query('INSERT INTO phpbb_log ' . $this->db->sql_build_array('INSERT', $sql_ary));
			
			$sql_1 = 'DELETE FROM `phpbb_hidden_posts` WHERE h_post_id = ' . (int) $post_id;
			$this->db->sql_query($sql_1);

			$sql_2 = 'UPDATE ' . POSTS_TABLE . ' SET post_hidden_bymod = 0 WHERE post_id = ' . (int) $post_id;
			$this->db->sql_query($sql_2);
			return new \Symfony\Component\HttpFoundation\JsonResponse(array(
				'result'	=> $this->user->lang['POST_BACK_TO_POST_SUCCESU']
			));
		}		
	}
	
	/**
	 * show_post_by_mod
	 *
	 * @param $post_id
	 * 
	 * @return Response A Symfony Response object
	 */
	public function show_post_by_mod($post_id) {
		if (!$this->auth->acl_get('m_')) {
			throw new http_exception(403, 'POST_HIDDEN_NO_AUTH');
		}
		
		if ($this->request->is_set_post('submit')) {
			$select_topic = 'SELECT topic_id FROM phpbb_posts WHERE post_id = ' . (int) $post_id;
			$result_topic = $this->db->sql_query($select_topic);
			$result_value = $this->db->sql_fetchrow($result_topic);
			$topic_id = $result_value['topic_id'];
			$this->db->sql_freeresult($result_topic);
			
			$sql_ary = array(
				'user_id'		=> $this->user->data['user_id'],
				'log_ip'		=> $this->user->data['user_ip'],
				'log_time'		=> time(),
				'log_operation'	=> 'UNHIDE_POST',
				'log_type'		=> 1,
				'topic_id'		=> $topic_id,
				'post_id'		=> $post_id,
				'log_data'		=> '',
			);
			$this->db->sql_query('INSERT INTO phpbb_log ' . $this->db->sql_build_array('INSERT', $sql_ary));
			
			$sql_1 = 'DELETE FROM `phpbb_hidden_posts` WHERE h_post_id = ' . (int) $post_id;
			$this->db->sql_query($sql_1);

			$sql_2 = 'UPDATE ' . POSTS_TABLE . ' SET post_hidden_bymod = 0 WHERE post_id = ' . (int) $post_id;
			$this->db->sql_query($sql_2);

			$message = $this->lang->lang('POST_BACK_TO_POST_SUCCESU') . '<br><br>' . $this->lang->lang('POST_BACK_TO_POST_H', ' <a href="' . append_sid($this->root_path . 'viewtopic.' . $this->php_ext, ['p' => $post_id]) . '#p' . $post_id.'">','</a>');
			return $this->helper->message($message);
		}

		$page_title = $this->lang->lang('POST_MANAGEH_BY_MOD');

		$this->template->assign_vars([
			'TITLE' 	=> $page_title,
			'MESSAGE'	=> $this->lang->lang('MOD_H_POST_LQU'),
			'S_ACTION'	=> $this->helper->route('insygner_hidepostbymod_controller_main', ['mode' => 'show', 'post' => $post_id]),
		]);

		return $this->helper->render('accept.html', $page_title);
	}
	
	/**
	 * hide_all_posts_by_mod
	 *
	 * @param $user_id
	 * 
	 * @return Response A Symfony Response object
	 */
	public function hide_all_posts_by_mod($user_id) {		
		if (!$this->auth->acl_get('m_')) {
			throw new http_exception(403, 'POST_HIDDEN_NO_AUTH');
		}
		
		if ($this->request->is_set_post('submit')) {
			$select_user = 'SELECT username FROM phpbb_users WHERE user_id = ' . (int) $user_id;
			$result_user = $this->db->sql_query($select_user);
			$result_value = $this->db->sql_fetchrow($result_user);
			$username = $result_value['username'];
			$this->db->sql_freeresult($result_user);
			
			$ary = array($username);
			$sql_ary = array(
				'user_id'		=> $this->user->data['user_id'],
				'log_ip'		=> $this->user->data['user_ip'],
				'log_time'		=> time(),
				'log_operation'	=> 'HIDE_POSTS',
				'log_type'		=> 1,
				'log_data'		=> serialize($ary),
			);
			$this->db->sql_query('INSERT INTO phpbb_log ' . $this->db->sql_build_array('INSERT', $sql_ary));
			
			$sql_arr = [
				'h_post_id'	=> sprintf("%02d", $user_id),
				'h_time'	=> time(),
				'h_userid'	=> $this->user->data['user_id'],
			];

			$sql_1 = 'INSERT INTO `phpbb_hidden_posts` ' . $this->db->sql_build_array('INSERT', $sql_arr);
			$this->db->sql_query($sql_1);

			$sql_2 = 'UPDATE ' . USERS_TABLE . ' SET user_posts_hidden_bymod = 1 WHERE user_id = ' . (int) $user_id;
			$this->db->sql_query($sql_2);

			$message = $this->lang->lang('ALL_POST_BACK_TO_POST_SUCCES') . '<br><br>' . $this->lang->lang('ALL_POST_BACK_TO_POST_H', ' <a href="' . append_sid($this->root_path . 'memberlist.' . $this->php_ext, ['mode' => 'viewprofile', 'u' => $user_id]) . '">','</a>');
			return $this->helper->message($message);
		}

		$page_title = $this->lang->lang('POST_MANAGEH_BY_MOD');

		$this->template->assign_vars([
			'TITLE' 	=> $page_title,
			'MESSAGE'	=> $this->lang->lang('ALL_MOD_H_POST_LQL'),
			'S_ACTION'	=> $this->helper->route('insygner_hidepostbymod_controller_main', ['mode' => 'hideall', 'user' => $user_id]),
		]);

		return $this->helper->render('accept.html', $page_title);
	}
	
	/**
	 * show_all_posts_by_mod
	 *
	 * @param $user_id
	 * 
	 * @return Response A Symfony Response object
	 */
	public function show_all_posts_by_mod($user_id) {
		if (!$this->auth->acl_get('m_')) {
			throw new http_exception(403, 'POST_HIDDEN_NO_AUTH');
		}
		
		if ($this->request->is_set_post('submit')) {
			$select_user = 'SELECT username FROM phpbb_users WHERE user_id = ' . (int) $user_id;
			$result_user = $this->db->sql_query($select_user);
			$result_value = $this->db->sql_fetchrow($result_user);
			$username = $result_value['username'];
			$this->db->sql_freeresult($result_user);
			$ary = array($username);
			$sql_ary = array(
				'user_id'		=> $this->user->data['user_id'],
				'log_ip'		=> $this->user->data['user_ip'],
				'log_time'		=> time(),
				'log_operation'	=> 'UNHIDE_POSTS',
				'log_type'		=> 1,
				'log_data'		=> serialize($ary),
			);
			$this->db->sql_query('INSERT INTO phpbb_log ' . $this->db->sql_build_array('INSERT', $sql_ary));
			
			$sql_1 = 'DELETE FROM `phpbb_hidden_posts` WHERE h_post_id = ' . (int) sprintf("%02d", $user_id);
			$this->db->sql_query($sql_1);

			$sql_2 = 'UPDATE ' . USERS_TABLE . ' SET user_posts_hidden_bymod = 0 WHERE user_id = ' . (int) $user_id;
			$this->db->sql_query($sql_2);

			$message = $this->lang->lang('ALL_POST_BACK_TO_POST_SUCCESU') . '<br><br>' . $this->lang->lang('ALL_POST_BACK_TO_POST_H', ' <a href="' . append_sid($this->root_path . 'memberlist.' . $this->php_ext, ['mode' => 'viewprofile', 'u' => $user_id]) . '">','</a>');
			return $this->helper->message($message);
		}

		$page_title = $this->lang->lang('POST_MANAGEH_BY_MOD');

		$this->template->assign_vars([
			'TITLE' 	=> $page_title,
			'MESSAGE'	=> $this->lang->lang('ALL_MOD_H_POST_LQU'),
			'S_ACTION'	=> $this->helper->route('insygner_hidepostbymod_controller_main', ['mode' => 'showall', 'user' => $user_id]),
		]);

		return $this->helper->render('accept.html', $page_title);
	}
}
	
	
	
	