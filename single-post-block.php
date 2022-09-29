<?php
/**
 * Plugin Name:       Single Post Block
 * Description:       Display one post of your choosing, or multiple.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Paul H. <paul@infinityscroll.blog>
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       single-post-block
 *
 * @package           create-block
 */

namespace nfntscrl\Blocks;

const PLUGIN_PATH = __DIR__;

class Single_Post {

	public static $self = null;

	public function __construct() {
		if ( self::$self ) {
			return;
		}
		self::$self = $this;

		add_action( 'init', [ $this, 'create_blocks' ] );
		add_action( 'admin_footer', [ $this, 'env_vars' ], 1 );
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
	}

	function env_vars() {

		if ( ! get_current_screen()->is_block_editor() ) {
			return;
		}
	
		$env_vars = apply_filters( 'single_post_block_env_vars', [
			'post_types'            => implode( ',', $this->post_types_allowed() ),
			'default_heading_level' => 3,
			'default_show_image'    => false,
		] );
	
		echo '<script type="application/javascript">';
		printf( 'var nfntscrlPostBlock = %s', json_encode( $env_vars ) );
		echo '</script>';
	
	}

	public function create_blocks() {
		register_block_type_from_metadata(
			 __DIR__ . '/src/post', [
				'api_version'     => 2,
				'attributes'      => [
					'postId' => [
						'type'    => 'integer',
						'default' => null,
					],
					'displayOptions' => [
						'type' => 'object',
						'default' => [
							'headingLevel' => 3,
							'tag'          => 'blockquote',
							'showImage'    => false,
						],
					]
				],
				'render_callback' => apply_filters( 'single_post_block_render_callback',
					apply_filters( 'single_post_block_excerpt_render_callback', [ $this, 'display_post' ] ) 
				),
			]
		);
		register_block_type_from_metadata(
			__DIR__ . '/src/group', [
				'render_callback' => apply_filters( 'single_post_block_group_block_render_callback', [ $this, 'display_group' ] ),
			]
		);
	}

	function register_rest() {

		register_rest_route( 'nfntscrl/v1', 'single_post/(?P<id>\d+)', [
			'methods' => 'GET',
			'callback' => [ $this, 'get_any_post' ],
			'permission_callback' => '__return_true',
		] );
	
	}

	function get_any_post( $request ) {

		$id = $request->get_param( 'id' );
		if ( empty( $id ) ) {
			return new WP_Error( 'incomplete_request', 'Did not request a post ID.', [ 'code' => 403 ] );
		}
	
		if ( ! current_user_can( 'read_post', $id ) ) {
			return new WP_Error( 'unauthorized_request', 'Please request a publicly available post.', [ 'code' => 401 ] );
		}
	
		$post = get_post( $id );
		if ( ! $post || is_wp_error( $post ) ) {
			return new WP_Error( 'internal_error', 'There was an error retrieving this post.', [ 'code' => 500 ] );
		}
	
		return [
			'title' => get_the_title( $post ),
			'excerpt' => get_the_excerpt( $post ),
			'thumbnail' => get_the_post_thumbnail_url( $post, 'thumbnail' ),
		];
	
	}

	public function post_types_allowed() {
		return apply_filters( 'single_post_block_types_allowed', [ 'post' ] );
	}

	public function array_to_html_attrs( $attrs ) {
		$html_list = [];
		foreach( $attrs as $key => $attr ) {
			$html_list[] = sprintf( '%s="%s"', sanitize_title( $key ), esc_attr( $attr ) );
		}

		return implode( ' ', $html_list );
	}

	function display_post( $attributes ) {

		$template_locations = $this->get_template_locations();
	
		if ( empty( $attributes['postId'] ) || ! is_numeric( $attributes['postId'] ) ) {
			return '';
		}
	
		$the_post = get_post( $attributes['postId'] );
		if ( ! $the_post || is_wp_error( $the_post ) ) {
			return '';
		}
	
		ob_start();
	
		foreach( $template_locations as $path ) {
			if ( file_exists( $path . '/' . 'single-post-block-excerpt.php' ) ) {
				include( $path . '/' . 'single-post-block-excerpt.php' );
				break;
			}
		}
	
		$output = ob_get_clean();
	
		return $output;
	
	}

	public function get_template_locations() {
		return apply_filters( 'single_post_block_template_directories', [
			get_stylesheet_directory() . '/template-parts',
			get_template_directory() . '/template-parts',
			__DIR__ . '/templates',
		] );
	}
	
	function display_group( $attributes, $inner_content = '' ) {
	
		$template_locations = $this->get_template_locations();
	
		ob_start();
	
		foreach( $template_locations as $path ) {
			if ( file_exists( $path . '/' . 'single-post-block-group.php' ) ) {
				include( $path . '/' . 'single-post-block-group.php' );
				break;
			}
		}
	
		$output = ob_get_clean();
	
		return $output;
	
	}

}

new Single_Post();
