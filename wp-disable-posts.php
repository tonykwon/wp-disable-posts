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
		global $pagenow;

		/* checks the request and redirects to the dashboard */
		add_action( 'init', array( __CLASS__, 'disallow_post_type_post') );

		/* removes Post Type `Post` */
		add_action( 'init', array( __CLASS__, 'remove_post_type_post') );

		/* removes Post Type `Post` related menus from the sidebar menu */
		add_action( 'admin_menu', array( __CLASS__, 'remove_from_admin_menu' ) );

		if ( !is_admin() && ($pagenow != 'wp-login.php') ) {
			/* need to return a 404 when post_type `post` objects are found */
			add_action( 'posts_results', array( __CLASS__, 'check_post_type' ) );

			/* do not return any instances of post_type `post` */
			add_filter( 'pre_get_posts', array( __CLASS__, 'remove_from_search_filter' ) );
		}
	}

	/**
	 * checks the request and redirects to the dashboard
	 * if the user attempts to access any `post` related links
	 *
	 * @access public
	 * @param none
	 * @return void
	 */
	public static function disallow_post_type_post()
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
	 * removes the Post Type `post` from the global variable `$wp_post_types`
	 *
	 * @access public
	 * @param none
	 * @return void
	 */
	public static function remove_post_type_post()
	{
		global $wp_post_types;

		if ( isset($wp_post_types['post']) ) {
			unset( $wp_post_types['post'] );
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
	public static function remove_from_admin_menu()
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


	/**
	 * checks the SQL statement to see if we are trying to fetch post_type `post`
	 *
	 * @access public
	 * @param array $posts,  found posts based on supplied SQL Query ($wp_query->request)
	 * @return array $posts, found posts
	 */
	public static function check_post_type( $posts = array() )
	{
		global $wp_query;

		$look_for = "wp_posts.post_type = 'post'";
		$instance = strpos( $wp_query->request, $look_for );
		/*
			http://localhost/?m=2013		- yearly archives
			http://localhost/?m=201303		- monthly archives
			http://localhost/?m=20130327	- daily archives
			http://localhost/?cat=1			- category archives
			http://localhost/?tag=foobar	- tag archives
			http://localhost/?p=1			- single post
		*/
		if ( $instance !== false ) {
			$posts = array(); // we are querying for post type `post`
		}

		return $posts;
	}

	/**
	 * excludes post type `post` to be returned from search
	 *
	 * @access public
	 * @param null
	 * @return object $query, wp_query object
	 */
	public static function remove_from_search_filter( $query )
	{
		$post_types = get_post_types();

		if ( array_key_exists('post', $post_types) ) {
			/* exclude post_type `post` from the query results */
			unset( $post_types['post'] );
		}
		$query->set( 'post_type', array_values($post_types) );

		return $query;
	}
}

new WP_Disable_Posts;
