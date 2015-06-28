<?php
/** 
    * Plugin Name: bdwebteam recent post tabs widget
    * Plugin URI: http://plugin.bdwebteam.com/bdwebteam-recent-tabs-widget
    * Description: Adds a widget that shows the most recent posts of your site with excerpt, featured image, date by sorting & ordering feature
    * Author: Mahabub Hasan
    * Author URI: http://bdwebteam.com/
    * Version: 1.0.2
    * Text Domain: bdwebteam
    * Domain Path: /languages
    * License: MIT License
    * License URI: http://opensource.org/licenses/MIT
*/

/**
   *
   * @package   bdwebteam-recent-post-tabs-widget
   * @author    ahabub Masan
   * @license   MIT License
   * @link      http://plugin.bdwebteam.com/bdwebteam-recent-tabs-widget
   * @copyright 2015
   * 
 */
   if ( ! defined( 'WPINC' ) ) {
	die;
}
if (!defined('PLUGIN_ROOT')) {
	define('PLUGIN_ROOT', dirname(__FILE__) . '/');
	define('PLUGIN_NAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
}
if (! defined ( 'WP_CONTENT_URL' ))
	define ( 'WP_CONTENT_URL', get_option ( 'siteurl' ) . '/wp-content' );
if (! defined ( 'WP_CONTENT_DIR' ))
	define ( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if (! defined ( 'WP_PLUGIN_URL' ))
	define ( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
if (! defined ( 'WP_PLUGIN_DIR' ))
	define ( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
    require_once(dirname(__FILE__).'/post_resizer.php'); 
   
add_action('widgets_init', create_function('', 'return register_widget("bdwebteam_recent_post_tabs_Widget");'));

class bdwebteam_recent_post_tabs_Widget extends WP_Widget
{
       
    //	@var string (The plugin version)		
	var $version = '1.0.2';
	//	@var string $localizationDomain (Domain used for localization)
	var $localizationDomain = 'bdwebteam';
	//	@var string $pluginurl (The url to this plugin)
	var $pluginurl = '';
	//	@var string $pluginpath (The path to this plugin)		
	var $pluginpath = '';	

	function responsive_posts() {
		$this->__construct();
	}
	                           
	public function __construct()
	{
		parent::__construct(
			'bdwebteam-recent-post-tabs-widget',
			'bdwebteam - Recent Post Tabs',
			array('description' => __('Tabs: Recent, category1, category2...', 'bdwebteam'), 'classname' => 'bdwebteam-tabs')
		);
        
		 $name = dirname ( plugin_basename ( __FILE__ ) );
		$this->pluginurl = WP_PLUGIN_URL . "/$name/css/";
		$this->pluginpath = WP_PLUGIN_DIR . "/$name/css/";
		add_action ( 'wp_print_styles', array (&$this, 'bdwebteam_recent_tabs_css' ) );
        
		add_action('save_post', array($this, 'bdwebteam_widget_cache'));
		add_action('edit_post', array($this, 'bdwebteam_widget_cache')); // comments covered
		add_action('deleted_post', array($this, 'bdwebteam_widget_cache'));
		add_action('switch_theme', array($this, 'bdwebteam_widget_cache'));
		
		// init hook
		add_action('init', array($this, 'init'));        
       add_action('wp_footer',array($this,'bdwebteam_recent_tabs_scripts'));
		
	}  
    
		function bdwebteam_recent_tabs_css() {
		$name = "bdwebteam-recent-tabs-widget.css";
		if (false !== @file_exists ( TEMPLATEPATH . "/$name" )) {
			$css = get_template_directory_uri () . "/$name";
		} else {
			$css = $this->pluginurl . $name;
		}
		wp_enqueue_style ( 'bdwebteam-recent-post-tabs-widget', $css, false, $this->version, 'screen' );        
	} 
    
    	public function init() 
	{
		// only in admin cp for form
		if (is_admin()) {
			wp_enqueue_script('widget-tabs', plugins_url('/bdwebteam-recent-post-tabs-widget/js/widget-tabs.js'));
            	
		}        
        
	}
	public function bdwebteam_recent_tabs_scripts() 
	{
		// only in admin cp for form
		if (!is_admin()) {
			wp_enqueue_script('bdwebteam-recent-tabs-js', plugins_url('/bdwebteam-recent-post-tabs-widget/js/functions.js'));
            	
		}
        
        
	}
	
	public function widget($args, $instance) 
	{
		global $post; 
		$titles = $cats = $tax_tags = array();		
		extract($args);
		extract($instance);
		if (!count($titles) OR !count($cats)) {
			_e('Recent tabs widget still need to be configured! Add tabs, add a title, and select type for each tab in widgets area.', 'bdwebteam');
			return; 
		}		
		$tabs = array();
		foreach ($titles as $key => $title) {
			
			// defaults missing?
			if (empty($tax_tags[$key])) {
				$tax_tags[$key] = '';
			}			
			if (empty($cats[$key])) {
				$cats[$key] = '';
			}			
			$tabs[$title] = array('cat_type' => $cats[$key], 'tag' => $tax_tags[$key]);
		}
		$posts = $this->get_posts($tabs, $number);		
		?>
		<?php echo $before_widget; ?>
        <div class="tabe-content">
          		
		<ul class="tabs-list">		
			<?php
			$count = 0; 
			foreach ($posts as $key => $val): $count++; $active = ($count == 1 ? 'active' : ''); 
			?>
			
			<li class="<?php echo $active;?>">
				<a href="#" data-tab="<?php echo esc_attr($count); ?>"><?php echo $key; ?></a>
			</li>
			
			<?php endforeach; ?>
			
		</ul>
            
		<div class="tabs-data ">
			<?php
				$i = 0; 
				foreach ($posts as $tab => $tab_posts): $i++; $active = ($i == 1 ? 'active' : ''); ?>
				
			<ul class="tab-posts <?php echo $active; ?> posts-list" id="recent-tab-<?php echo esc_attr($i); ?>">
			
			<?php if ($tabs[$tab] == 'comments'): ?>

				<?php 
				foreach ($tab_posts as $comment): 
				?>
				
				<li class="comment">					
					<span class="author"><?php printf('%s said', get_comment_author_link($comment->comment_ID)); ?></span>
					
					<p class="text"><?php comment_excerpt($comment->comment_ID); ?></p>
					
					<a href=""><?php echo get_the_title($comment->comment_post_ID); ?></a>
				
				</li>

				<?php				
				endforeach; 
				?>
			
			
			<?php else: ?>
			
				<?php foreach ($tab_posts as $post): setup_postdata($post); ?>

				<li>
					<div class="content">
                    <?php						
					$trainer_thumb   = get_post_thumbnail_id($post->ID);
                    $trainer_img_url = wp_get_attachment_url( $trainer_thumb,'medium' );
                    $img_width=$instance['posts_thumb_width'];
                    $img_height=$instance['posts_thumb_hight'];
                    $post_thum_img   = post_thum_resize( $trainer_img_url,$img_width,$img_height, true ); 
                     $posts_thumb =$instance['posts_thumb']; 
                    $posts_content= $instance['posts_content'];
                     $show_content_limit=$instance['word_posts_content'];
                     $content = get_the_content(get_the_ID($post->ID));
                     $trimmed_content = wp_trim_words( $content,$show_content_limit);
                    if($posts_thumb) {
                        if ( has_post_thumbnail($post->ID) ):?>
                        							
                           <a class="post-pull-left" href="<?php echo $post_permalink;?>" title="<?php echo $title ;?>">
                            <img class="img-responsive" src="<?php echo $post_thum_img;?>" alt="<?php echo $title ;?>" />							
                            </a>
                            
                            <?php
                        endif;
                   }
                    ?>
                    <div class="tabs-post-info">
						<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>">
							<?php if (get_the_title()) the_title(); else the_ID(); ?></a>
                         <?php  if($posts_content) {  ?>
                <p><?php echo $trimmed_content ;?></p>
                <?php                            
            } ?>
						</div>											
					</div>
				</li>
				
				<?php endforeach; ?>
				
			<?php endif; ?>
				
			</ul>
			<?php endforeach; ?>
		
		</div>
		
   		</div>
		<?php echo $after_widget; ?>
		
		<?php
		
		wp_reset_postdata();
		wp_reset_query();

	}
	
	public function get_posts($tabs, $number)
	{
		// posts available in cache? - use instance id to suffix
		$cache = get_transient('bdwebteam_tabbed_recent_posts');
		
		if (!defined('ICL_LANGUAGE_CODE') && is_array($cache) && isset($cache[$this->number])) {
			return $cache[$this->number];
		}

		// get posts
		$args = array('numberposts' => $number, 'ignore_sticky_posts' => 1);
		foreach ($tabs as $key => $val) {	
			
			$opts = array();
			
			switch ($val['cat_type']) {
				case 'popular':
					$opts['orderby'] = 'comment_count';
					break;
					
				case 'comments':
					$posts[$key] = get_comments(array('number'=> $number, 'status' => 'approve'));
					continue 2; // jump switch and foreach loop
					
				case 'top-reviews':
					// get top rated of all time
					$opts = array_merge($opts, array('orderby' => 'meta_value', 'meta_key' => '_bdwebteam_review_overall'));
					break;
					
				case 'recent':
					break;
					
				case 'tag':
					$opts['tag'] = $val['tag'];
					break;
					
				default:
					$opts['cat'] = intval($val['cat_type']);
					break;
			}
						
			//$query = new WP_Query(array_merge($args, $opts));
			$posts[$key] = get_posts(apply_filters('bdwebteam_widget_tabbed_recent_query_args', array_merge($args, $opts)));
		}
		
		if (!is_array($cache)) {
			$cache = array();
		}
		
		$cache[ $this->number ] = $posts;
		
		set_transient('bdwebteam_tabbed_recent_posts', $cache, 60*60*24*30); // 30 days transient cache
		
		return $posts;
	}

	public function bdwebteam_widget_cache()
	{
		delete_transient('bdwebteam_tabbed_recent_posts');
	}
	
	public function update($new, $old)
	{
		// fix categories
		foreach ($new['cats'] as $key => $cat) {
			$new['cats'][$key] = strip_tags($cat);
		}
		
		foreach ($new['titles'] as $key => $title) {
			$new['titles'][$key] = strip_tags($title);
		}
		
		foreach ($new['tax_tags'] as $key => $tag) {
			$new['tax_tags'][$key] = trim(strip_tags($tag));
		}

		$new['number'] = intval($new['number']);
               
        $instance['posts_title'] = $new['posts_title']?1:0;
        $instance['word_posts_title'] = strip_tags($new['word_posts_title']);
        
        $instance['posts_thumb'] = $new['posts_thumb']?1:0;
        $instance['posts_thumb_width'] = strip_tags($new['posts_thumb_width']);
        $instance['posts_thumb_hight'] = strip_tags($new['posts_thumb_hight']);
        $instance['posts_content'] = $new['posts_content']?1:0;
        $instance['word_posts_content'] = strip_tags($new['word_posts_content']);  
		// delete cache
		$this->bdwebteam_widget_cache();

		return $new;
	}
	
	public function form($instance)
	{
		$instance = array_merge(array(
            'titles' => array(),
            'cats' => array(0), 
            'number'             => 4, 
            'cat'                => 0,
            'posts_title' 	     => '1',
            'word_posts_title'   => '20',
            'posts_thumb' 		 => '0',
            'posts_content' 	 => '0',
            'word_posts_content' => '20',
             'show_date'		     => '1',            
            'posts_thumb_width'  => '80',
            'posts_thumb_hight'  => '70', 
           'tax_tags' => array()), $instance);
		
		extract($instance);
	?>
		<style>
        .widget-content .tax_tag { display: none; }
			.widget-content p.roter { padding-top: 10px; border-top: 1px solid #d8d8d8; }
			
		</style>
		<div id="tab-options">

		<script type="text/html" class="template-tab-options">
		<p class="title roter">
			<label><?php printf(__('Tab #%s Title:', 'bdwebteam'), '<span>%n%</span>'); ?></label>
			<input class="widefat" name="<?php 
				echo esc_attr($this->get_field_name('titles')); ?>[%n%]" type="text" value="%title%" />
		</p>
		<div class="cat">
			<label><?php printf(__('Tab #%s Category:', 'bdwebteam'), '<span>%n%</span>'); ?></label>
			<?php 
			
			$r = array('orderby' => 'name', 'hierarchical' => 1, 'selected' => $cat, 'show_count' => 0);
			
			// categories list
			$cats_list = walk_category_dropdown_tree(get_terms('category', $r), 0, $r);
			
			// custom options
			$options = array(
				'recent' => __('Recent Posts', 'bdwebteam'), 
				'popular' => __('Popular Posts', 'bdwebteam'), 
				'top-reviews' => __('Top Reviews', 'bdwebteam'),
				'tag' => __('Use a Tag', 'bdwebteam'),
			);
			
			?>

			<select name="<?php echo $this->get_field_name('cats') .'[%n%]'; ?>">

			<?php foreach ($options as $key => $val): ?>
	
				<option value="<?php echo esc_attr($key); ?>"<?php echo ($cat == $key ? ' selected' : ''); ?>><?php echo esc_html($val); ?></option>			
	
			<?php endforeach; ?>

				<optgroup label="<?php _e('Category', 'bdwebteam'); ?>">
					<?php echo $cats_list; ?>
				</optgroup>

			</select>

			<div class="tax_tag">
				<p><label><?php printf(__('Tab #%s Tag:', 'bdwebteam'), '<span>%n%</span>'); ?></label> <input type="text" name="<?php 
					echo esc_attr($this->get_field_name('tax_tags')); ?>[%n%]" value="%tax_tag%" /></p>
			</div>

			<p><a href="#" class="remove-recent-tab">[x] <?php _e('remove', 'bdwebteam'); ?></a></p>
		</div>
		</script>			
			<p class="separator"><a href="#" id="add-more-tabs"><?php _e('Add More Tabs', 'bdwebteam'); ?></a></p>
			                 
			<?php

			if (is_integer($this->number)): // create for valid instances only 
			
				foreach ($cats as $n => $cat):
				
					if (!isset($tax_tags[$n])) {
						$tax_tags[$n] = '';
					}
			?>
			
				<script>
					jQuery(function($) {
	
						$('.widget-liquid-right [id$="bdwebteam-recent-post-tabs-widget-'+ <?php echo $this->number; ?> +'"] #add-more-tabs').trigger(
								'click', 
								[{
									'n': <?php echo ($n+1); ?>, 
									'title': '<?php echo esc_attr($titles[$n]); ?>', 
									'selected': '<?php echo esc_attr($cat); ?>',
									'tax_tag': '<?php echo esc_attr($tax_tags[$n]); ?>'
								}]);
					});
				</script>
			
			<?php
				endforeach; 
			endif; 
			?>
		</div>
        <p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts in each tab:', 'bdwebteam'); ?></label>
        <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        </p>
        <p>
        <input type="checkbox" class="checkbox checkboxcontent" id="<?php echo $this->get_field_id('posts_title'); ?>" name="<?php echo $this->get_field_name('posts_title'); ?>" <?php checked( (bool) $instance["posts_title"], true ); ?>>
        <label for="<?php echo $this->get_field_id('posts_title'); ?>"><?php _e('Show Posts Title:', 'bdwebteam'); ?></label>
        </p>
        <p style="padding-left: 20px;" class="content_show_box">
        <label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("word_posts_title"); ?>"><?php _e('Words to show:', 'bdwebteam'); ?></label>
        <input style="width:20%;" id="<?php echo $this->get_field_id("word_posts_title"); ?>" name="<?php echo $this->get_field_name("word_posts_title"); ?>" type="text" value="<?php echo absint($instance["word_posts_title"]); ?>" size='3' />
        </p>
        <p>
        <input type="checkbox" class="checkbox checkboxcontent" id="<?php echo $this->get_field_id('posts_content'); ?>" name="<?php echo $this->get_field_name('posts_content'); ?>" <?php checked( (bool) $instance["posts_content"], true ); ?>>
        <label for="<?php echo $this->get_field_id('posts_content'); ?>"><?php _e('Show Posts content:', 'bdwebteam'); ?></label>
        </p>
        <p style="padding-left: 20px;" class="content_show_box">
        <label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("word_posts_content"); ?>"><?php _e('Words to show:', 'bdwebteam'); ?></label>
        <input style="width:20%;" id="<?php echo $this->get_field_id("word_posts_content"); ?>" name="<?php echo $this->get_field_name("word_posts_content"); ?>" type="text" value="<?php echo absint($instance["word_posts_content"]); ?>" size='3' />
        </p>
        <hr />  
        <p>
        <input type="checkbox" class="checkbox checkimg" id="<?php echo $this->get_field_id('posts_thumb'); ?>" name="<?php echo $this->get_field_name('posts_thumb'); ?>" <?php checked( (bool) $instance["posts_thumb"], true ); ?>>
        <label for="<?php echo $this->get_field_id('posts_thumb'); ?>"><?php _e('Show thumbnails:', 'responsive_posts'); ?></label>
        </p>      
        <p style="padding-left: 20px;" class="posts_thumb_width">
        <label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("posts_thumb_width"); ?>"><?php _e('Posts thumb width:', 'responsive_posts'); ?></label>
        <input style="width:20%;" id="<?php echo $this->get_field_id("posts_thumb_width"); ?>" name="<?php echo $this->get_field_name("posts_thumb_width"); ?>" type="text" value="<?php echo absint($instance["posts_thumb_width"]); ?>" size='3' />
        </p>
        <p style="padding-left: 20px;" class="posts_thumb_hight">
        <label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("posts_thumb_hight"); ?>"><?php _e('Posts thumb hight:', 'responsive_posts'); ?></label>
        <input style="width:20%;" id="<?php echo $this->get_field_id("posts_thumb_hight"); ?>" name="<?php echo $this->get_field_name("posts_thumb_hight"); ?>" type="text" value="<?php echo absint($instance["posts_thumb_hight"]); ?>" size='3' />
        </p>
	<?php
	}
	
}

 