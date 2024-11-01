<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WP_Widget_Random_Button extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'widget_text', 'description' => __('Display a Random Post Button on your sidebar'));
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct('random-button', __('WP Random Button'), $widget_ops, $control_ops);
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		/**
		 * Filter the content of the Text widget.
		 *
		 * @since 2.3.0
		 *
		 * @param string    $widget_text The widget content.
		 * @param WP_Widget $instance    WP_Widget instance.
		 */
		$text = apply_filters( 'widget_text', empty( $instance['text'] ) ? __( 'Random Post' ) : $instance['text'], $instance );
		$cat = apply_filters( 'widget_cat', empty( $instance['cat'] ) ? '' : $instance['cat'], $instance );
		$nocat = apply_filters( 'widget_nocat', empty( $instance['nocat'] ) ? '' : $instance['nocat'], $instance );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
			<a class="animated infinite pulse random-button" data-nonce="<?php echo wp_create_nonce( 'pt_random_stuff_nonce' ); ?>" data-toggle="modal" data-target=".random-overlay" data-cat="<?php echo $cat; ?>" data-nocat="<?php echo $nocat; ?>">
				<?php echo $text; ?>
			</a>
		<?php
		echo $args['after_widget'];
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['cat'] = strip_tags($new_instance['cat']);
		$instance['nocat'] = strip_tags($new_instance['nocat']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed

		return $instance;
	}

	/**
	 * @param array $instance
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '', 'cat' => '', 'nocat' => '' ) );
		$title = strip_tags($instance['title']);
		$text = strip_tags($instance['text']);
		$cat = strip_tags($instance['cat']);
		$nocat = strip_tags($instance['nocat']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( 'Button Text:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"  type="text" value="<?php echo esc_attr($text); ?>" /></p>
	
		<p><label for="<?php echo $this->get_field_id( 'cat' ); ?>"><?php _e( 'Category IDs:' ); ?></label></p>
		<i><?php _e( 'Enter the category IDs here and only posts from these categories will display. Separate multiple IDs by comma(,)' );?></i>
		<p><input class="widefat" id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>"  type="text" value="<?php echo esc_attr($cat); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'nocat' ); ?>"><?php _e( 'Exclude Category IDs:' ); ?></label></p>
		<i><?php _e( 'Enter the category IDs here and posts from these categories will not display. Separate multiple IDs by comma(,)' );?></i>
		<p><input class="widefat" id="<?php echo $this->get_field_id('nocat'); ?>" name="<?php echo $this->get_field_name('nocat'); ?>"  type="text" value="<?php echo esc_attr($nocat); ?>" /></p>
	
<?php
	}
}

/**
 * Register Random Post Button widget on startup.
 *
 * Calls 'widgets_init' action after all of the WordPress widgets have been
 * registered.
 *
 * @since 1.0
 */

function pt_randombtn_widgets_init() {
	if ( !is_blog_installed() )
		return;

	register_widget('WP_Widget_Random_Button');

}

add_action( 'widgets_init', 'pt_randombtn_widgets_init' );