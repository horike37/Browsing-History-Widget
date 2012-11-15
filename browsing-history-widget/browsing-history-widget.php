<?php
/*
Plugin Name: Browsing History Widget
Author: Horike Takahiro
Plugin URI: http://www.kakunin-pl.us
Description: 閲覧履歴を表示するウィジェットです。現在は「投稿」のみに対応しています
Version: 1.0
Author URI: http://www.kakunin-pl.us
Domain Path: /languages
Text Domain: 
*/

if ( ! defined( 'HANWD_PLUGIN_URL' ) )
	define( 'HANWD_PLUGIN_URL', plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ));

if ( ! defined( 'HANWD_PLUGIN_DIR' ) )
	define( 'HANWD_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ));

add_action('wp_enqueue_scripts', 'bhw_register_script');
function bhw_register_script() {
	wp_enqueue_script('jquery');
	wp_register_script('jquery-cookie', HANWD_PLUGIN_URL.'/js/jquery.cookie.js', array('jquery'), '1.0');
	wp_enqueue_script('jquery-cookie');
}



class Browsing_History_Widget extends WP_Widget {
	function Browsing_History_Widget() {
		$widget_ops = array( 'classname' => 'browsing_history', 'description' => '閲覧履歴を表示します');
		$this->WP_Widget( 'browsing_history', '閲覧履歴', $widget_ops );
	}
function form( $instance ) {
		$title = strip_tags(@$instance['title'] );
		$limit = empty($instance['limit']) ? 5 : intval( $instance['limit'] );
	?>
    	<p>
        	<label for="<?php echo $this->get_field_id('title'); ?>">
            タイトル：
            <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>        
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">
            表示数：
                <select id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>">
                <?php
                for ( $i = 1; $i <= 20; ++$i )
                echo "<option value='$i' " . ( $limit == $i ? "selected='selected'" : '' ) . ">$i</option>";
                ?>
                </select>
            </label>
        </p>
    <?php }

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = strip_tags( $new_instance['limit'] );
		return $instance;
	}

	function widget( $args, $instance ) {
		$title = empty($instance['title'] ) ? '閲覧履歴' : strip_tags( $instance['title'] );
		$limit = empty($instance['limit'] ) ? 5 : strip_tags( $instance['limit'] );

		extract( $args );
?>
<script type="text/javascript">
	var COOKIE_NAME = 'browsing_history';
	jQuery(document).ready(function($){ 
		var cookie_array = [];
		if($.cookie(COOKIE_NAME)){
			cookie_array = $.cookie(COOKIE_NAME).split("__HANRYU__");
			cookie_array.reverse();
			$.each(cookie_array, function(i, data){
				data = data.split("__URL__TITLE__");
				$('.view-history').append('<li><a href="'+data[0]+'">'+data[1]+'</a></li>');
				if ( i == (<?php echo $limit ?>-1) ) {
					 return false;
				}
			});
			cookie_array.reverse();
		} else {
			$('.view-history').append('<li>履歴はありません</li>');
		}
	});
</script>
<?php
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul class="view-history"><ul>';
		echo $after_widget;
	}
}
add_action( 'widgets_init', create_function( '', 'return register_widget("Browsing_History_Widget");' ) );

add_action( 'wp_head', 'bhw_cookie_helper' );
function bhw_cookie_helper() {
?>

<?php
if ( !is_single() || get_post_type() != 'post' )
	return;
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var COOKIE_NAME = 'browsing_history';
		var COOKIE_PATH = '/';

		var page_array = [];
		if($.cookie(COOKIE_NAME)){
			page_array = $.cookie(COOKIE_NAME).split("__HANRYU__");
		}

		var url = '<?php the_permalink(); ?>';
		var title = '<?php the_title(); ?>';
		var content = url + '__URL__TITLE__' + title;
		var idx = $.inArray(content, page_array);

		if( idx == -1) {
			page_array.push( content );
		} else {
			page_array.splice( idx, 1 );
			page_array.push( content );
		}

		var date = new Date();
		date.setTime(date.getTime() + ( 1000 * 60 * 60 * 24 * 7 ));

		$.cookie(COOKIE_NAME, page_array.join("__HANRYU__"), { path: '/', expires: date });

	});
</script>
<?php
}