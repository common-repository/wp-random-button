<?php

/**
 * The file that defines the core plugin class
 *
 *
 * @link       http://ptheme.com
 * @since      1.0.0
 *
 * @package    wp-random-button
 * @subpackage wp-random-button/class
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class PtRandomPost {

    public function __construct(){
        add_action( 'plugins_loaded', array( $this, 'wprb_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'pt_rws_scripts' ), 100 );
        add_action( 'wp_ajax_nopriv_pt_randomwpstuff_function', array( $this, 'pt_randomwpstuff_function') );
        add_action( 'wp_ajax_pt_randomwpstuff_function', array( $this, 'pt_randomwpstuff_function') );
        add_action( 'customize_register', array( $this, 'pt_randomwpstuff_customizer') );
        add_action( 'customize_preview_init', array( $this, 'pt_random_customize_preview_js') );
    }
	
	public function wprb_textdomain() {
		load_plugin_textdomain( 'wprb', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

    public function pt_rws_scripts() { // enqueue required scripts
    	wp_enqueue_style( 'animate-css', DEV_PLUGIN__PLUGIN_DIR . 'css/animate.min.css', array(), DEV_PLUGIN__VERSION );
		wp_enqueue_style( 'random-post-button', DEV_PLUGIN__PLUGIN_DIR . 'css/style.css', array('animate-css'), DEV_PLUGIN__VERSION );
		$css = '
			a.random-button { color: ' . get_option( 'pt_randombtn_color', '#ffffff' ) . '; background: ' . get_option( 'pt_randombtn_background', '#1e8cbe' ) . '; }
			a.random-button:hover { color: ' . get_option( 'pt_randombtn_font_hover', '#ffffff' ) . '; background: ' . get_option( 'pt_randombtn_background_hover', '#00aadc' ) . '; }
		';
		wp_add_inline_style( 'random-post-button', $css );

		if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
	        wp_enqueue_script( 'jquery' );// Comment this line if you theme has already loaded jQuery
	    }

		wp_enqueue_script( 'randomwpstuff', DEV_PLUGIN__PLUGIN_DIR . 'js/functions.js', array('jquery'), '1.0', true );
		wp_localize_script( 'randomwpstuff', 'randomwpstuff', array(
	        'ajax_url' => admin_url( 'admin-ajax.php' )
	    ));
    }

    public function pt_randomwpstuff_function() { // proccessing function
    	$result = '';
		$id 	= '';
		$args	= array();
		$cat = array();
		$nocat = array();

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'pt_random_stuff_nonce' ) || ! isset( $_REQUEST['nonce'] ) ) {
            exit( "No naughty business please" );
        }

        $randomIDs = !empty($_COOKIE['randomIDs']) ? explode(',', $_COOKIE['randomIDs']) : array(); // get cookies of random post IDs
        $post_types = array( 'post' );
        $post_types = apply_filters( 'wprb/post_types', $post_types ); // use filters to alter the post types

		$args = array(
			'post_type' 			=> $post_types,
			'posts_per_page' 		=> '1',
			'post__not_in'			=> $randomIDs,
			'orderby' 				=> 'rand',
			'ignore_sticky_posts' 	=> 1,
			'no_found_rows'			=> true,
			'cache_results' 		=> false
		);

		// Display posts from selected cat IDs
		if ( !empty($_REQUEST['cat']) ) {
			$args['category__in'] = explode(',', $_REQUEST['cat'] );
		};

		// Exclude posts from selected cat IDs
		if ( !empty($_REQUEST['nocat']) ) {
			$args['category__not_in'] = explode(',', $_REQUEST['nocat'] );
		};
		
		$new_query = new WP_Query( apply_filters( 'wprb_query', $args  ) );
		// The Loop
		if ( $new_query->have_posts() ) {

			while ( $new_query->have_posts() ) : $new_query->the_post();

				$id 	= get_the_ID();
				$result .= '<h2 class="overlay-title"><a href="' . esc_url( get_permalink() ) .'" target="_blank">' . get_the_title() . '</a></h2>';
				$result .= '<div class="overlay-content">' . get_the_excerpt() . '</div>';
				$result .= '<div class="overlay-image"><a href="' . esc_url( get_permalink() ) .'" target="_blank">' . get_the_post_thumbnail() . '</a></div>';
				$result .= '<a class="button button--see-more" href="' . esc_url( get_permalink() ) .'" target="_blank">See details</a>';

				if ( ! in_array( $id, $randomIDs) ) {
			    	$randomIDs[] = $id; // add to cookies if current post ID is not in cookies
			    }
				
				$randomIDs = implode(',', $randomIDs);
				setcookie( "randomIDs", $randomIDs, time() + (3600 * 6), '/' ); // set cookies with expiring time of 6 hours

			endwhile;

		} else {
			// no posts found
			$result .= __( 'Sorry, but no stuff found yet.' );
		}
		/* Restore original Post Data */
		wp_reset_postdata();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            echo $result;
            // print_r($_COOKIE);
            die();
        }
        else {
            wp_redirect( get_permalink( $id ) );
            exit();
        }
    }

    public function pt_randomwpstuff_customizer( $wp_customize ) { // add settings to Customizer
    	// Add the Random Post section in case it's not already there.
		$wp_customize->add_section( 'pt_random_post', array(
			'title'           => __( 'WP Random Button', 'wprb' ),
			'description'     => __( 'From here you can configure the appearance of our WP Random Button.', 'wprb' ),
			'priority'        => 130,
		) );

		// Add random button setting and control.
		$wp_customize->add_setting( 'pt_randombtn_background', array(
			'default'           => '#1e8cbe',
			'type' 				=> 'option',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport' 		=> 'postMessage',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pt_randombtn_background', array(
			'label'       => __( 'Button Background Color', 'wprb' ),
			'section'     => 'pt_random_post',
		) ) );

		$wp_customize->add_setting( 'pt_randombtn_color', array(
			'default'           => '#ffffff',
			'type' 				=> 'option',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport' 		=> 'postMessage',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pt_randombtn_color', array(
			'label'       => __( 'Button Font Color', 'wprb' ),
			'section'     => 'pt_random_post',
		) ) );

		$wp_customize->add_setting( 'pt_randombtn_background_hover', array(
			'default'           => '#00aadc',
			'type' 				=> 'option',
			'sanitize_callback' => 'sanitize_hex_color',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pt_randombtn_background_hover', array(
			'label'       => __( 'Button Hover Background Color', 'wprb' ),
			'section'     => 'pt_random_post',
		) ) );

		$wp_customize->add_setting( 'pt_randombtn_font_hover', array(
			'default'           => '#ffffff',
			'type' 				=> 'option',
			'sanitize_callback' => 'sanitize_hex_color',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pt_randombtn_font_hover', array(
			'label'       => __( 'Button Hover Font Color', 'wprb' ),
			'section'     => 'pt_random_post',
		) ) );
		
		$wp_customize->add_setting( 'pt_randombtn_cookie_enabling', array(
			'default'           => '#',
			'type' 				=> 'option',
		) );

		$wp_customize->add_control( 'pt_randombtn_cookie_enabling', array(
			'label'       => __( 'Enable Cookies', 'wprb' ),
			'description' => __( 'By enabling cookies, each random post will be displayed only once. Expired time of the cookies is 6 hours', 'wprb' ),
			'section'     => 'pt_random_post',
			'type'		  => 'checkbox',
		) );
    }

    public function pt_random_customize_preview_js() { // Live preview script
		wp_enqueue_script( 'pt_randombtn_customizer', DEV_PLUGIN__PLUGIN_DIR . 'js/customizer.js', array( 'customize-preview' ), '20150920', true );
	}

}