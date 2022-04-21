<?php
/**
*
* @package HidePostByMod
* @copyright (c) 2022 Insygner (https://github.com/insygner)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace insygner\hidepostbymod\migrations;

class install extends \phpbb\db\migration\migration
{
	/**
	 * @return bool
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'hidden_posts') && $this->db_tools->sql_column_exists($this->table_prefix . 'posts', 'post_hidden_bymod');
	}

	/**
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	/**
	 * @return array
	 */
	public function update_schema()
	{
		return [
			'add_tables'		=> [
				'phpbb_hidden_posts' => [
					'COLUMNS'		=> [
						'h_id'			=> ['UINT', null, 'auto_increment'],
						'h_post_id'		=> ['UINT', 0],
						'h_time'		=> ['TIMESTAMP', 0],
						'h_userid'		=> ['UINT', 0],
					],
					'PRIMARY_KEY'	=> 'h_id',
				],
			],
			'add_columns'	=> [
				'phpbb_posts' => [
					'post_hidden_bymod'				=> ['BOOL', 0],
				],
				'phpbb_users' => [
					'user_posts_hidden_bymod'		=> ['BOOL', 0],
				],
			],
		];
	}

	/**
	 * @return array
	 */
	public function revert_schema()
	{
		return [
			'drop_tables'		=> [
				'phpbb_hidden_posts',
			],
			'drop_columns'	=> [
				'phpbb_posts' => [
					'post_hidden_bymod',
				],
				'phpbb_users' => [
					'user_posts_hidden_bymod',
				],
			],
		];
	}
}