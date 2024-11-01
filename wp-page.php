<?php
/*
 * Plugin Name: WP-Page
 * Plugin URI: http://www.lmenu.fr
 * Description: Show the full or partial content from a page into your sidebar
 * Version: 1.0.0
 * Author: Laurent Menu
 * Author URI: http://www.lmenu.fr
 * Text Domain: wp_page
 * Domain Path: /lang/
*/

$lang = get_locale();
load_plugin_textdomain('wp_page','wp-content/plugins/wp-page/lang/');

// Widget
class WP_Page_Widget extends WP_Widget {

	function WP_Page_Widget() {
		$widget_ops = array(
			'classname' => 'wp-page-widget',
			'description' => __('Show the full or partial content from a page into your sidebar', 'wp_page')
		);
		
		$this->WP_Widget('wp-page-widget', 'WP-Page Widget', $widget_ops);
	}

	function widget($args, $d) {
		extract($args);
		
		$title = $d['title'];

		echo $before_widget;
		
		if($title != ''): echo $before_title . $title . $after_title; endif;
		
		// Get the page
		query_posts('page_id=' . $d['page'] . '&posts_per_page=1');
		if(have_posts()): while(have_posts()): the_post();
		
		// Get the content
		$content = get_the_content();
		
		// Check if Word or Char split
		if($d['limit'] != 0 && $d['limit'] != '') {
			if($d['split'] == 'word') {
				if($d['dots'] == 'yes') $dots = true; else $dots = false;
				$content = word_split($content, $d['limit'], $dots);
			}
			else {
				$content = strip_tags($content);
				$content = substr($content, 0, $d['limit']);
				if($d['dots'] == 'yes') $content .= '...';
			}
		}
		else {
			if($d['filter'] == 'yes') {
				$content = apply_filters('the_content', $content);
				$content = str_replace(']]>', ']]&gt;', $content);
			}
			else {
				$content = strip_tags($content);
			}
		}
		
		if($d['thumb'] == 'yes') {
			if(has_post_thumbnail()): 
				if($d['thumb-link'] == 'yes'): 
			?>
			
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail($d['thumb_ref']); ?></a>
			
			<?php else:
			
					the_post_thumbnail($d['thumb_ref']);
			
				endif;
			endif;
		}
		
		echo $content;
		
		if($d['more'] == 'yes') {
?>

<a href="<?php the_permalink(); ?>"><?php echo $d['more-txt']; ?></a>

<?php
		}
		
		endwhile; endif; wp_reset_query();
		
		echo $after_widget;		
	}

	function update($new, $old) {
		return $new;
	}

	function form($instance) {
		$pages = get_pages();
		
		if(empty($instance['filter'])) $instance['filter'] = 'yes';
		if(empty($instance['split'])) $instance['split'] = 'word';
		if(empty($instance['limit'])) $instance['limit'] = 0;
		if(empty($instance['dots'])) $instance['dots'] = 'no';
		if(empty($instance['more'])) $instance['more'] = 'yes';
		if(empty($instance['thumb'])) $instance['thumb'] = 'no';
		if(empty($instance['thumb-link'])) $instance['thumb-link'] = 'yes';
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title:', 'wp_page'); ?></label>
			<input name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('page'); ?>"><?php echo __('Choose your page:', 'wp_page'); ?></label>
			<select name="<?php echo $this->get_field_name('page'); ?>" id="<?php echo $this->get_field_id('page'); ?>" class="widefat">
				<?php foreach($pages as $page): ?>
				<option value="<?php echo $page->ID; ?>" <?php if($page->ID == $instance['page']) echo 'selected="selected"'; ?>><?php echo $page->post_title; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	
		<p>
			<?php echo __('Split text by:', 'wp_page'); ?><br />
			<input type="radio" name="<?php echo $this->get_field_name('split'); ?>" id="<?php echo $this->get_field_id('split-char'); ?>" value="char" <?php if($instance['split'] == 'char') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('split-char'); ?>"><?php echo __('Character', 'wp_page'); ?></label>
			<input type="radio" name="<?php echo $this->get_field_name('split'); ?>" id="<?php echo $this->get_field_id('split-word'); ?>" value="word" <?php if($instance['split'] == 'word') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('split-word'); ?>"><?php echo __('Word', 'wp_page'); ?></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php echo __('Limit:', 'wp_page'); ?></label>
			<input name="<?php echo $this->get_field_name('limit'); ?>" id="<?php echo $this->get_field_id('limit'); ?>" value="<?php echo $instance['limit']; ?>" class="widefat" />
			0 = <?php echo __('no limit', 'wp_page'); ?>
		</p>
		
		<p>
			<?php echo __('If no limit, translate HTML tags ?', 'wp_page'); ?><br />
			<input type="radio" name="<?php echo $this->get_field_name('filter'); ?>" id="<?php echo $this->get_field_id('filter-yes'); ?>" value="yes" <?php if($instance['filter'] == 'yes') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('filter-yes'); ?>"><?php echo __('Yes', 'wp_page'); ?></label>
			<input type="radio" name="<?php echo $this->get_field_name('filter'); ?>" id="<?php echo $this->get_field_id('filter-no'); ?>" value="no" <?php if($instance['filter'] == 'no') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('filter-no'); ?>"><?php echo __('No', 'wp_page'); ?></label>
		</p>
		
		<p>
			<?php echo __('Show the three dots "...":', 'wp_page'); ?><br />
			<input type="radio" name="<?php echo $this->get_field_name('dots'); ?>" id="<?php echo $this->get_field_id('dots-yes'); ?>" value="yes" <?php if($instance['dots'] == 'yes') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('dots-yes'); ?>"><?php echo __('Yes', 'wp_page'); ?></label>
			<input type="radio" name="<?php echo $this->get_field_name('dots'); ?>" id="<?php echo $this->get_field_id('dots-no'); ?>" value="no" <?php if($instance['dots'] == 'no') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('dots-no'); ?>"><?php echo __('No', 'wp_page'); ?></label>
		</p>
		
		<p>
			<?php echo __('Show "More" link:', 'wp_page'); ?><br />
			<input type="radio" name="<?php echo $this->get_field_name('more'); ?>" id="<?php echo $this->get_field_id('more-yes'); ?>" value="yes" <?php if($instance['more'] == 'yes') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('more-yes'); ?>"><?php echo __('Yes', 'wp_page'); ?></label>
			<input type="radio" name="<?php echo $this->get_field_name('more'); ?>" id="<?php echo $this->get_field_id('more-no'); ?>" value="no" <?php if($instance['more'] == 'no') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('more-no'); ?>"><?php echo __('No', 'wp_page'); ?></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('more-txt'); ?>"><?php echo __('"More" link text:', 'wp_page'); ?></label>
			<input name="<?php echo $this->get_field_name('more-txt'); ?>" id="<?php echo $this->get_field_id('more-txt'); ?>" value="<?php echo $instance['more-txt']; ?>" class="widefat" />
		</p>
		
		<p>
			<?php echo __('Show the post thumbnail:', 'wp_page'); ?><br />
			<input type="radio" name="<?php echo $this->get_field_name('thumb'); ?>" id="<?php echo $this->get_field_id('thumb-yes'); ?>" value="yes" <?php if($instance['thumb'] == 'yes') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('thumb-yes'); ?>"><?php echo __('Yes', 'wp_page'); ?></label>
			<input type="radio" name="<?php echo $this->get_field_name('thumb'); ?>" id="<?php echo $this->get_field_id('thumb-no'); ?>" value="no" <?php if($instance['thumb'] == 'no') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('thumb-no'); ?>"><?php echo __('No', 'wp_page'); ?></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('thumb_ref'); ?>"><?php echo __('If show post thumbnail, insert the "add_image_size" reference:', 'wp_page'); ?></label>
			<input name="<?php echo $this->get_field_name('thumb_ref'); ?>" id="<?php echo $this->get_field_id('thumb_ref'); ?>" value="<?php echo $instance['thumb_ref']; ?>" class="widefat" />
			<?php echo __('Ex: sidebar-thumb', 'wp_page'); ?>
		</p>
		
		<p>
			<?php echo __('Make the thumbnail clickable:', 'wp_page'); ?><br />
			<input type="radio" name="<?php echo $this->get_field_name('thumb-link'); ?>" id="<?php echo $this->get_field_id('thumb-link-yes'); ?>" value="yes" <?php if($instance['thumb-link'] == 'yes') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('thumb-link-yes'); ?>"><?php echo __('Yes', 'wp_page'); ?></label>
			<input type="radio" name="<?php echo $this->get_field_name('thumb-link'); ?>" id="<?php echo $this->get_field_id('thumb-link-no'); ?>" value="no" <?php if($instance['thumb-link'] == 'no') echo 'checked="checked"'; ?> />
			<label for="<?php echo $this->get_field_id('thumb-link-no'); ?>"><?php echo __('No', 'wp_page'); ?></label>
		</p>
<?php
	}
}

function widget_init() {
	register_widget('WP_Page_Widget');
}

add_action('widgets_init', 'widget_init');


// Function : split the content by words
function word_split($str,$words=15, $threedots=true) {
    $str = strip_tags($str);
    $arr = preg_split("/[\s]+/", $str,$words+1);
    $arr = array_slice($arr,0,$words);
 
    $result = join(' ',$arr);
   
    if($threedots === true && substr($result,-3) != '...'){
    $result = $result . '...';
    }
   
    return $result;
}
?>