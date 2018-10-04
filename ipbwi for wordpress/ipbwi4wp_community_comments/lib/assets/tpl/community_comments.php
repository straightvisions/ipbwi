<?php if(comments_open()){ ?>
	<?php if(get_option('comment_registration') && !is_user_logged_in()){ ?>
		<div class="alert alert-help">
			<p><?php printf( __('You must be %1$slogged in%2$s to post a comment.', 'ipbwi4wp_community_comments'), '<a href="'.wp_login_url(get_permalink()).'">', '</a>' ); ?></p>
		</div>
	<?php }else{ ?>
	<form action="<?php echo get_permalink(); ?>" method="post" id="respond">
		<?php if(is_user_logged_in()){ ?>
		<p class="comments-logged-in-as"><?php _e('Logged in as', 'ipbwi4wp_community_comments'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="<?php _e('Logout', 'ipbwi4wp_community_comments'); ?>"><?php _e('Logout', 'ipbwi4wp_community_comments'); ?> <?php _e('&raquo;', 'ipbwi4wp_community_comments'); ?></a></p>
		<?php }else{ ?>
		<p id="comment-form-elements"><input type="text" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" placeholder="<?php _e( 'Your name*', 'ipbwi4wp_community_comments' ); ?>" tabindex="1" required="required" /></p>
		<?php } ?>
		<p><textarea name="comment" id="comment" placeholder="<?php _e('Your Comment', 'ipbwi4wp_community_comments'); ?>" tabindex="4"></textarea></p>
		<p><input name="submit" type="submit" id="submit" class="button" tabindex="5" value="<?php _e('Comment', 'ipbwi4wp_community_comments'); ?>" /><?php comment_id_fields(); ?></p>
		<?php do_action('comment_form', $post->ID); ?>
	</form>
<?php
		}
	if($topic_id = get_post_meta(get_the_ID(), 'ipbwi_cc_topic_id', true)){
		$topic			= get_transient('ipbwi4wp_cc_topic_'.$topic_id);
		if(!$topic){
			$topic		= $GLOBALS['ipbwi4wp_community_comments']->ipbwi4wp->ipbwi->extended->topics($topic_id);
			set_transient('ipbwi4wp_cc_topic_'.$topic_id, $topic, 60*60);
		}
		
		$posts			= get_transient('ipbwi4wp_cc_posts_'.$topic_id);
		if(!$posts){
			$posts		= $GLOBALS['ipbwi4wp_community_comments']->ipbwi4wp->ipbwi->core->topic_posts($topic_id,array('hidden' => 0));
			set_transient('ipbwi4wp_cc_posts_'.$topic_id, $posts, 60*60);
		}
?>
	<h3 class="commentlist_title"><?php _e('Comments', 'ipbwi4wp_community_comments'); ?></h3>
	<p class="commentlist_more"><?php _e('Discussions about', 'ipbwi4wp_community_comments'); ?> <a href="<?php echo $topic['url']; ?>"><?php echo $topic['title']; ?></a></p>
	<p class="commentlist_count"><?php _e('We have', 'ipbwi4wp_community_comments'); ?> <a href="<?php echo $topic['url']; ?>"><?php comments_number(); ?></a> <?php _e('on this article so far.', 'ipbwi4wp_community_comments'); ?></p>
	<ol class="commentlist" id="comments">
	<?php
		if(isset($posts['results'])){
		foreach($posts['results'] as $key => $post){
			if(($posts['page'] == 1 && $key != 0) || $posts['page'] > 1){
				$date = new DateTime(); $date = date_timestamp_get($date);
	?>
	<li class="comment">
		<article id="comment-<?php echo $post['id']; ?>">
			<header class="comment-author vcard">
				<?php echo get_avatar($post['author']['email'], 32, false, $post['author']['name']); ?>
				<cite class="fn"><?php echo $post['author']['name']; ?></cite>
				<time datetime="<?php echo $post['date']; ?>"><a href="<?php echo get_permalink(); ?>/#comment-<?php echo $post['id']; ?>"><?php echo date_i18n(get_option('date_format'), $date); ?></a></time>
			</header>
			<section class="comment_content">
				<p><?php echo $post['content']; ?></p>
			</section>
		</article>
	</li>
	<?php
				}
			}
		}
	?>
		<?php if(isset($GLOBALS['ipbwi4wp_community_comments']->settings->settings['basic']['IPB_SHOW_LEGACY_WP_POSTS']['value']) && $GLOBALS['ipbwi4wp_community_comments']->settings->settings['basic']['IPB_SHOW_LEGACY_WP_POSTS']['value'] == 1) { wp_list_comments( array( 'style' => 'ol' ) ); } ?>
	</ol>
<?php
	}else{
?>
	<p class="comments_first_one"><?php _e('No comments yet - be the first to open a discussion on this topic!', 'ipbwi4wp_community_comments'); ?></p>
<?php
	}
}else{
?>
	<p class="comments_closed"><?php _e('Comments are closed', 'ipbwi4wp_community_comments'); ?></p>
<?php } ?>