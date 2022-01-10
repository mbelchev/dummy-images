<?php
/**
 * Plugin name: Dummy Images
 * Plugin URI: https://github.com/mbelchev/dummy-images
 * Description: This plugin is a generator of customizable dummy images. You can easily select the dimensions, background color, text and text color of the placeholder image.
 * Version: 1.0.1
 * Author: mbelchev
 * Author URI: https://github.com/mbelchev
 * Text Domain: dummy-images
 * Domain Path: /languages/
 * License: GPL-2.0+
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */

namespace mbelchev\DummyImages;

/**
 * Exit if accessed directly
 */
defined( 'ABSPATH' ) or die( 'You cannot access this page directly.' );

if ( ! defined( 'DUMMY_IMAGES_DIR') ) {
	define( 'DUMMY_IMAGES_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'DUMMY_IMAGES_URL') ) {
	define( 'DUMMY_IMAGES_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'DUMMY_IMAGES_SLUG') ) {
	define( 'DUMMY_IMAGES_SLUG', 'dummy-images' );
}

if ( ! defined( 'DUMMY_IMAGES_ASSET_VERSION') ) {
	define( 'DUMMY_IMAGES_ASSET_VERSION', '1.0.1' );
}

if ( ! class_exists( __NAMESPACE__ . '\Main' ) ) :

class Dummy_Images_Main {
	private $debug = false;

	public function __construct() {
		$this->load_plugin_textdomain();
		$this->autoload();
		$this->hooks();
	}

	private function load_plugin_textdomain() {
		load_plugin_textdomain( DUMMY_IMAGES_SLUG, false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	private function autoload() {
		foreach ( glob( DUMMY_IMAGES_DIR . 'includes/*.php' ) as $module ) {
			if ( file_exists( $module ) ) {
				include_once $module;
			}
		}
	}

	private function hooks() {
			add_action( 'admin_menu', array( $this, 'create_media_subpage' ) );
			add_action( 'post-upload-ui', array( $this, 'extend_media_upload_popup' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_scripts' ) );
			
			add_action( 'wp_ajax_create_dummyimages', array( $this, 'ajax_create_image' ) );			
			add_action( 'wp_ajax_list_dummyimages', array( $this, 'ajax_list_images' ) );			
	}

	public function create_media_subpage() {
		add_media_page(
			__( 'Dummy Images', DUMMY_IMAGES_SLUG ),
			__( 'Dummy Images', DUMMY_IMAGES_SLUG ),
			'read',
			DUMMY_IMAGES_SLUG,
			array( $this, 'load_media_page' )
		);
	}

	public function extend_media_upload_popup() {
	    printf(
	    	'<p class="upload-instructions drop-instructions">%s</p><a href="%s" class="button button-primary button-large">%s</a>',
	    	esc_html__( 'or', DUMMY_IMAGES_SLUG ),
	    	admin_url( 'upload.php?page=' . DUMMY_IMAGES_SLUG ),
	    	esc_html__( 'Generate Dummy Image', DUMMY_IMAGES_SLUG )
	    );
	}

	public function load_media_page() {
		require_once DUMMY_IMAGES_DIR . 'views/media.php';
	}

	public function ajax_create_image() {
		parse_str( htmlspecialchars_decode( $_POST['data'] ), $_POST );

		array_walk( $_POST, function( &$value, $key ) {
			if ( in_array( $key, array( 'image-bg-color', 'image-text-color' ) ) ) {
				$value = sanitize_hex_color( $value );
			}

			if ( in_array( $key, array( 'image-size-x', 'image-size-y', 'image-text' ) ) ) {
				$value = sanitize_text_field( $value );
			}

			if ( in_array( $key, array( 'image-size-x', 'image-size-y' ) ) ) {
				$value = absint( $value );
			}
		} );

		if ( ! check_ajax_referer( DUMMY_IMAGES_SLUG . '-nonce', 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Something went wrong. Try again!', DUMMY_IMAGES_SLUG ) );
		}

		if ( empty( $_POST['image-size-x'] ) || empty( $_POST['image-size-y'] ) ) {
			wp_send_json_error( esc_html__( 'Please enter valid image sizes', DUMMY_IMAGES_SLUG ) );
		}

		$values = wp_parse_args( array_filter( $_POST ), array(
			'image-bg-color' 	=> '#000000',
			'image-text'		=> sprintf( '%dx%d', $_POST['image-size-x'], $_POST['image-size-y'] ),
			'image-text-color'	=> '#FFFFFF'
		) );

		$result = $this->generate_and_upload_image(
			array( $values['image-size-x'], $values['image-size-y'] ),
			$values['image-bg-color'],
			$values['image-text'],
			$values['image-text-color']
		);

		if ( true !== $result ) {
			wp_send_json_error( $result );
		}

		wp_send_json_success( esc_html__( 'The dummy image is created successfully', DUMMY_IMAGES_SLUG ) );
	}

	public function ajax_list_images( $data = '' ) {
		check_ajax_referer( DUMMY_IMAGES_SLUG . '-nonce', 'nonce' );

		$dummy_images = new \WP_Query( array(
		    'post_type'   	=> 'attachment',
		    'post_status' 	=> 'inherit',
		    'meta_query'  	=> array(
		        array(
		            'key'     => '_wp_attachment_metadata',
		            'value'   => 's:14:"is_dummy_image";i:1;',
		            'compare' => 'LIKE',
		        )
		    ),
		    'fields' 		=> 'ids',
		    'posts_per_page' => 20,
		    'paged'			=> $page = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1,
		) );

		if ( $dummy_images->have_posts() ) {
			while ( $dummy_images->have_posts() ) { $dummy_images->the_post();
				$data .= sprintf(
					'<div class="img-container">%s<a href="%s" class="button button-primary button-large">%s</a></div>',
					wp_get_attachment_image( get_the_ID(), 'thumbnail' ),
					esc_url( get_edit_post_link( get_the_ID() ) ),
					esc_html__( 'Edit', DUMMY_IMAGES_SLUG )
				);
			}

			wp_reset_postdata();

			wp_send_json_success( array(
				'html' => $data,
				'left' => $dummy_images->max_num_pages - $page,
			) );
		} else {
			wp_send_json_error( array(
				'html' => sprintf( '<p class="no-media">%s</p>', esc_html__( 'No dummy images found', DUMMY_IMAGES_SLUG ) ),
				'left' => $dummy_images->max_num_pages - $page,
			) );
		}
	}

	public function load_custom_scripts() {
		if ( 'media_page_' . DUMMY_IMAGES_SLUG === get_current_screen()->id ) {
			wp_enqueue_style( 'wp-color-picker' ); 
			wp_enqueue_style( DUMMY_IMAGES_SLUG . '-styling', DUMMY_IMAGES_URL . 'assets/css/' . DUMMY_IMAGES_SLUG . '.min.css', array(), DUMMY_IMAGES_ASSET_VERSION );
			wp_enqueue_script( DUMMY_IMAGES_SLUG . '-scripts', DUMMY_IMAGES_URL . 'assets/js/dist/' . DUMMY_IMAGES_SLUG . '.min.js', array( 'jquery', 'wp-color-picker' ), DUMMY_IMAGES_ASSET_VERSION, true );
			wp_localize_script( DUMMY_IMAGES_SLUG . '-scripts', 'dummy_images', array(
				'ajax_url' 		=> admin_url( 'admin-ajax.php' ),
				'ajax_nonce' 	=> wp_create_nonce( DUMMY_IMAGES_SLUG . '-nonce' ),
			) );
		}
	}

	private function generate_and_upload_image( $size , $bg_color, $text, $text_color ) {
		try {
			$upload_dir = wp_upload_dir();
			$path = $upload_dir['path'] . '/';
			$image_name = $this->generate_image_name( $size, $path );

			Image::initiate( $size, $bg_color, $text, $text_color )->generate( $path, $image_name );

			$image_id = wp_insert_attachment( array(
				'post_title' 		=> sprintf( 'Dummy Image - %dx%d', $size[0], $size[1] ),
				'post_content'   	=> '',
				'post_status'    	=> 'inherit',
				'post_mime_type' 	=> sanitize_mime_type( wp_check_filetype( basename( $image_name ), null )['type'] ),
				'guid' 				=> $upload_dir['url'] . '/' . $image_name,
			), $path . $image_name );

			if ( $image_id == 0 ) {
				throw new \Exception( 'Creating the WP attachment failed' );
			}

			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			$metadata = wp_generate_attachment_metadata( $image_id, $path . $image_name );
			$metadata['is_dummy_image'] = 1;

			wp_update_attachment_metadata( $image_id, $metadata );

			return true;
		} catch ( \Exception $e ) {
			return $this->debug ? '<strong>DEBUG MODE ON:</strong> ' . $e->getMessage() : esc_html__( 'Something went wrong :( Try again!', DUMMY_IMAGES_SLUG );
		}
	}

	private function generate_image_name( $size, $path, $i = 1 ) {
		$name = sprintf( 'dummy-image-%dx%d.png', $size[0], $size[1] );

		while ( file_exists( $path . $name ) ) {
			$name = sprintf( '%s-%dx%d-%d.png', DUMMY_IMAGES_SLUG, $size[0], $size[1], $i++ );
		}

		return $name;
	}
}
endif;

add_action( 'plugins_loaded', function() {
	new Dummy_Images_Main();
});