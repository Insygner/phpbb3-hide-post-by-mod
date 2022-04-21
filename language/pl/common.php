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
	'MOD_H_POST_L'				=> 'Ukryj post',
	'MOD_H_POST_S'				=> 'Odkryj post',
	'MOD_H_POST_LQL'			=> 'Czy na pewno chcesz ukryć treść tego posta?',
	'MOD_H_POST_LQU'			=> 'Czy na pewno chcesz odkryć treść tego posta?',

	'POST_HIDDEN_BY_MOD'		=> '<div style="text-align:center; font-size: 14px; font-weight: bold;">Post został ukryty, ponieważ łamał regulamin.</div>',
	'ALL_POST_HIDDEN_BY_MOD'	=> '<div style="text-align:center; font-size: 14px; font-weight: bold;">Posty tego użytkownika zostały ukryte, ponieważ łamały regulamin.</div>',
	'POST_HIDDEN_NO_AUTH'		=> 'Nie masz odpowiednich uprawnień aby wykonać tę czynność',
	'POST_BACK_TO_POST_H'		=> 'Kliknij %sTutaj%s aby powrócić do tematu',
	'POST_BACK_TO_POST_SUCCES'	=> 'Post został ukryty',
	'POST_BACK_TO_POST_SUCCESU'	=> 'Post został odkryty',

	'ALL_MOD_H_POST_LQL'			=> 'Czy na pewno chcesz ukryć wszystkie posty tego użytkownika?',
	'ALL_MOD_H_POST_LQU'			=> 'Czy na pewno chcesz odkryć wszystkie posty tego użytkownika?',
	'ALL_POST_BACK_TO_POST_H'		=> 'Kliknij %sTutaj%s aby powrócić do profilu użytkownika',
	'ALL_POST_BACK_TO_POST_SUCCES'	=> 'Wszystkie posty tego użytkownika zostały ukryte',
	'ALL_POST_BACK_TO_POST_SUCCESU'	=> 'Wszystkie posty tego użytkownika zostały odkryte',

	'HIDE_ALL_USER_POST'			=> 'Ukryj posty',
	'UNHIDE_ALL_USER_POST'			=> 'Pokaż posty',
	'HIDE_POST' 					=> 'Ukrył post',
	'UNHIDE_POST'					=> 'Odkrył post',
	
	'POST_MANAGEH_BY_MOD'		=> 'Zarządzanie postami',
]);
