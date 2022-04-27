<?php
/**
*
* @package HidePostByMod
* @copyright (c) 2022 Insygner (https://github.com/insygner)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB')) {
	exit;
}

if (empty($lang) || !is_array($lang)) {
	$lang = [];
}

$lang = array_merge($lang, [
	'MOD_H_POST_L'				=> 'Hide post',
	'MOD_H_POST_S'				=> 'Unhide post',
	'MOD_H_POST_LQL'			=> 'Are you sure to hide this post?',
	'MOD_H_POST_LQU'			=> 'Are you sure to unhide this post?',

	'POST_HIDDEN_BY_MOD'		=> '<div style="text-align:center; font-size: 14px; font-weight: bold;">Post hidden by moderator.</div>',
	'ALL_POST_HIDDEN_BY_MOD'	=> '<div style="text-align:center; font-size: 14px; font-weight: bold;">All post of this user hidden by moderator.</div>',
	'POST_HIDDEN_NO_AUTH'		=> 'You don\'t have permissions!',
	'POST_BACK_TO_POST_H'		=> 'Click %shere%s to back',
	'POST_BACK_TO_POST_SUCCES'	=> 'Post was hidden',
	'POST_BACK_TO_POST_SUCCESU'	=> 'Post was unhidded',

	'ALL_MOD_H_POST_LQL'			=> 'Are you sure to hide all post of this user?',
	'ALL_MOD_H_POST_LQU'			=> 'Are you sure to unhide all post of this user?',
	'ALL_POST_BACK_TO_POST_H'		=> 'Click %shere%s to return',
	'ALL_POST_BACK_TO_POST_SUCCES'	=> 'All post of that user was hidden',
	'ALL_POST_BACK_TO_POST_SUCCESU'	=> 'All post of that user was unhidded',

	'HIDE_ALL_USER_POST'			=> 'Hide all posts',
	'UNHIDE_ALL_USER_POST'			=> 'Unhide all posts',
	'HIDE_POST' 					=> 'Hide post',
	'UNHIDE_POST'					=> 'Unhide post',
	'HIDE_POSTS' 					=> 'Hide all posts',
	'UNHIDE_POSTS'					=> 'Unhide all posts',
	'POST_MANAGEH_BY_MOD'			=> 'Manage user post',
]);
