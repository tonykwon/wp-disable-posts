<?php
/*
Plugin Name: WP Disable Posts
Plugin URI: http://tonykwon.com/wordpress-plugins/wp-disable-posts/
Description: This plugin disables the built-in WordPress Post Type `post`
Version: 0.1
Author: Tony Kwon
Author URI: http://tonykwon.com/wordpress-plugins/wp-disable-posts/
License: GPLv3
*/

class WP_Disable_Posts
{
	public function __construct()
	{
		/* checks the request and redirects to the dashboard */
		add_action( 'init', array( __CLASS__, 'disallow_post_type_post') );

		/* removes Post Type `Post` related menus from the sidebar menu */
		add_action( 'admin_menu', array( __CLASS__, 'remove_post_type_post' ) );
	}

	/**
	 * checks the request and redirects to the dashboard
	 * if the user attempts to access any `post` related links
	 *
	 * @access public
	 * @param none
	 * @return void
	 */
	public function disallow_post_type_post()
	{
		global $pagenow, $wp;

		switch( $pagenow ) {
			case 'edit.php':
			case 'edit-tags.php':
			case 'post-new.php':
				if ( !array_key_exists('post_type', $_GET) && !array_key_exists('taxonomy', $_GET) ) {
					wp_safe_redirect( get_admin_url(), 301 );
					exit;
				}
				break;
		}
	}

	/**
	 * loops through $menu and $submenu global arrays to remove any `post` related menus and submenu items
	 *
	 * @access public
	 * @param none
	 * @return void
	 *
	 */
	public function remove_post_type_post()
	{
		global $menu, $submenu;

		/*
			edit.php
			post-new.php
			edit-tags.php?taxonomy=category
			edit-tags.php?taxonomy=post_tag
		 */
		$done = false;
		foreach( $menu as $k => $v ) {
			foreach($v as $key => $val) {
				switch($val) {
					case 'Posts':
						unset($menu[$k]);
						$done = true;
						break;
				}
			}

			/* bail out as soon as we are done */
			if ( $done ) {
				break;
			}
		}

		$done = false;
		foreach( $submenu as $k => $v ) {
			switch($k) {
				case 'edit.php':
					unset($submenu[$k]);
					$done = true;
					break;
			}

			/* bail out as soon as we are done */
			if ( $done ) {
				break;
			}
		}
	}

}

new WP_Disable_Posts;