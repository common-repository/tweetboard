<?php
/*
Plugin Name: TweetBoard
Plugin URI: http://openwebstuff.com/tweetboard
Description: A board presenting the latest tweet.
Version: 1.2.2
Author: OpenWebStuff
Author URI: http://openwebstuff.com
License: GPL2
*/

include_once (ABSPATH . WPINC . '/class-simplepie.php');

class TweetBoard extends WP_Widget {

	function TweetBoard() {
		$options = array( 'classname' => 'widget_tweetBoard', 'description' => __( "A board presenting latest tweets." ) );
		$this->WP_Widget('TweetBoard', __('TweetBoard'), $options);
	}
	
	function form($instance) {
		// Default values	
		if ( !isset($instance["username"]) )	$instance["username"] = 'wordpress';
		if ( !isset($instance["tweets_count"]) )	$instance["tweets_count"] = 1;
		if ( !isset($instance["show_pubdate"]) )	$instance["show_pubdate"] = '1';
		if ( !isset($instance["bordercolor"]) )	$instance["bordercolor"] = '#666666';
		if ( !isset($instance["bgcolor"]) )	$instance["bgcolor"] = '#cccccc';
		if ( !isset($instance["datecolor"]) )	$instance["datecolor"] = 'none';
		if ( !isset($instance["textcolor"]) )	$instance["textcolor"] = 'none';
		if ( !isset($instance["linkcolor"]) )	$instance["linkcolor"] = 'none';
	
		?>
		
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready(function() {
				jQuery('.color-picker').hide();
				jQuery('.color-picker').each(function(){
						var $this = jQuery(this),
							id = $this.attr('rel'); 
						$this.farbtastic('#' + id);
					});
				jQuery('.color-picker-input').click(function(){
						if ( jQuery(this).attr('value') == '' ) jQuery(this).attr('value', '#000000')
						jQuery('[rel='+jQuery(this).attr('id')+']').show(300);
					});
			});
			//]]> 
		</script>		
		
		<div id="tweetBoard-widget-form">		
			<p>
				<label for="<?php echo $this->get_field_id("username"); ?>">Twitter Username:</label>		
				<input type="text" name="<?php echo $this->get_field_name("username"); ?>" id="<?php echo $this->get_field_id("username"); ?>" value="<?php echo $instance["username"]; ?>" />
			</p>
			<p><label for="<?php echo $this->get_field_id("tweets_count"); ?>">Number of tweets:</label><br />		
			<select id="<?php echo $this->get_field_id('tweets_count'); ?>" name="<?php echo $this->get_field_name('tweets_count'); ?>">
			<?php for ($i=1; $i<=10; $i++) { ?>
				<option value="<?=$i?>" <?php if ( $i == $instance['tweets_count'] ) echo ' selected="selected"'; ?>><?=$i?></option>
			<?php } ?>
			</select></p>		
			<p>
				<label for="<?php echo $this->get_field_id('show_pubdate'); ?>">Show date?</label><br />
				<select id="<?php echo $this->get_field_id('show_pubdate'); ?>" name="<?php echo $this->get_field_name('show_pubdate'); ?>">
					<option value="0" <?php if ( $instance['show_pubdate'] === 0 ) echo ' selected="selected"'; ?>>Off</option>
					<option value="1" <?php if ( $instance['show_pubdate'] == 1 ) echo ' selected="selected"'; ?>>On</option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id("bordercolor"); ?>">Border color:</label>
				<input type="text" name="<?php echo $this->get_field_name("bordercolor"); ?>" id="<?php echo $this->get_field_id("bordercolor"); ?>" value="<?php echo $instance["bordercolor"]; ?>" class="color-picker-input" />
				<div class="color-picker" rel="<?php echo  $this->get_field_id("bordercolor"); ?>"></div>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id("bgcolor"); ?>">Background color:</label>
				<input type="text" name="<?php echo $this->get_field_name("bgcolor"); ?>" id="<?php echo $this->get_field_id("bgcolor"); ?>" value="<?php echo $instance["bgcolor"]; ?>" class="color-picker-input" />
				<div class="color-picker" rel="<?php echo  $this->get_field_id("bgcolor"); ?>"></div>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id("datecolor"); ?>">Date color (empty - theme's default):</label>
				<input type="text" name="<?php echo $this->get_field_name("datecolor"); ?>" id="<?php echo $this->get_field_id("datecolor"); ?>" value="<?php echo $instance["datecolor"]; ?>" class="color-picker-input" />
				<div class="color-picker" rel="<?php echo  $this->get_field_id("datecolor"); ?>"></div>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id("textcolor"); ?>">Text color (empty - theme's default):</label>
				<input type="text" name="<?php echo $this->get_field_name("textcolor"); ?>" id="<?php echo $this->get_field_id("textcolor"); ?>" value="<?php echo $instance["textcolor"]; ?>" class="color-picker-input" />
				<div class="color-picker" rel="<?php echo  $this->get_field_id("textcolor"); ?>"></div>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id("linkcolor"); ?>">Link color (empty - theme's default):</label>
				<input type="text" name="<?php echo $this->get_field_name("linkcolor"); ?>" id="<?php echo $this->get_field_id("linkcolor"); ?>" value="<?php echo $instance["linkcolor"]; ?>" class="color-picker-input" />
				<div class="color-picker" rel="<?php echo  $this->get_field_id("linkcolor"); ?>"></div>
			</p>
		</div>
		
		<?php
		
	}

	function widget($args, $instance) {
		extract($args);		
		echo $before_widget;		
		$this->show_twitter_bar(array( 'username'=>$instance['username'], 'show_pubdate'=>$instance['show_pubdate'], 'bordercolor'=>$instance['bordercolor'], 'bgcolor'=>$instance['bgcolor'], 'tweets_count'=>$instance['tweets_count'], 'datecolor'=>$instance['datecolor'], 'textcolor'=>$instance['textcolor'], 'linkcolor'=>$instance['linkcolor'] ));
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$color_regex = '/^#?([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';			
		$instance['username'] = strip_tags( $new_instance['username'] );
		$instance['tweets_count'] = strip_tags( $new_instance['tweets_count'] );		
		$instance['show_pubdate'] = strip_tags( $new_instance['show_pubdate'] );
		$instance['bordercolor'] = strip_tags( $new_instance['bordercolor'] );
		if ( !preg_match($color_regex, $instance["bordercolor"]) )	$instance["bordercolor"]=$old_instance["bordercolor"];	
		$instance['bgcolor'] = strip_tags( $new_instance['bgcolor'] );
		if ( !preg_match($color_regex, $instance["bgcolor"]) )	$instance["bgcolor"]=$old_instance["bgcolor"];
		$instance['datecolor'] = strip_tags( $new_instance['datecolor'] );
		if ( !preg_match($color_regex, $instance["datecolor"]) && strlen($instance["datecolor"]) > 0 )	$instance["datecolor"]=$old_instance["datecolor"];
		$instance['textcolor'] = strip_tags( $new_instance['textcolor'] );
		if ( !preg_match($color_regex, $instance["textcolor"]) && strlen($instance["textcolor"]) > 0 )	$instance["textcolor"]=$old_instance["textcolor"];
		$instance['linkcolor'] = strip_tags( $new_instance['linkcolor'] );
		if ( !preg_match($color_regex, $instance["linkcolor"]) && strlen($instance["linkcolor"]) > 0 )	$instance["linkcolor"]=$old_instance["linkcolor"];	
		return $instance;
	}	
		
	function show_twitter_bar($ops){
		$url = "http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=" . $ops['username'];
		$anchor_regex = '`\b(https?|ftp)://[-A-Za-z0-9+&@#/%?=~_()|!:,.;]*[-A-Za-z0-9+&@#/%=~_()|]\b`';
		$user_regex = '`(^|[\n ])@+([A-Za-z0-9-_]+)`';
		$this->css($ops);		
		$rss_contents = @fetch_feed($url);
		if ($rss_contents) {			
			$tweets = array_slice($rss_contents->get_items(), 0, $ops['tweets_count']);
			echo '<div class="tweetBoard">';
			echo '<a href="http://twitter.com/' . $ops['username'] . '" title="' . $ops['username'] . '" alt="' . $ops['username'] . '" target="_blank"><img class="tweetBoard-logo" src="' . plugins_url('/twitter.png', __FILE__) . '" /></a>';			
			foreach ( $tweets as $tweet ) {			
				$description = $tweet->get_description();
				$link = $tweet->get_link();
				$date = $tweet->get_date();			
				//$date = $date_arr[0] . ' ' . $date_arr[1] . ' ' . $date_arr[2] . ' ' . substr($date_arr[4], 0, -3);
				$t = explode (' ', $description, 2);			
				$show_tweet = htmlspecialchars(stripslashes($t[1]));
				$show_tweet = preg_replace($anchor_regex, '<a href="\0" title="\0" alt="\0" target="_blank">\0</a>', $show_tweet );
				$show_tweet = preg_replace($user_regex, ' <a href="http://www.twitter.com/\\2" title="\\2" alt="\\2" target="_blank">@\\2</a>', $show_tweet);
				$show_tweet = '<p>' . $show_tweet . '</p>';			
				if ($ops['show_pubdate']) 
					$show_tweet = '<p class="tweetBoardDate">' . $date . '</p>' . $show_tweet;
            			echo $show_tweet;
			}			
			echo '</div>';
		}		
	}
	
	function css($ops) {
		echo '<style>';		
		echo '.tweetBoard {min-height:24px;padding:5px;border:1px ' . $ops['bordercolor'] . ' solid;background:' . $ops['bgcolor'] . ';}';
		echo '.tweetBoard p.tweetBoardDate {padding:0 0 0 0;margin:0;';
		if ( $ops["datecolor"] != 'none' || !empty($ops["datecolor"]) ) echo 'color:' . $ops["datecolor"] . ';';
		echo '}';
		echo '.tweetBoard p {';
		if ( $ops["textcolor"] != 'none' ) echo 'color:' . $ops["textcolor"] . ';';
		echo '}';		
		echo '.tweetBoard a {';
		if ( $ops["linkcolor"] != 'none' ) echo 'color:' . $ops["linkcolor"] . ';';
		echo '}';
		echo '.tweetBoard a:hover {text-decoration:underline;}';
		echo '.tweetBoard img.tweetBoard-logo {border:0;margin:0;}';
		echo '</style>';
	}	
	
} //class TweetBoard

add_action('widgets_init', create_function('', 'return register_widget("TweetBoard");'));

function load_farbtastic_script() {
	wp_enqueue_script('farbtastic');
}
function load_farbtastic_style() {
	wp_enqueue_style('farbtastic');	
}
add_action('admin_print_scripts-widgets.php', 'load_farbtastic_script');
add_action('admin_print_styles-widgets.php', 'load_farbtastic_style');

?>
