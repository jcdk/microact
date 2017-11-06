<?php get_header(); global $user_ID, $wp_rewrite, $wp_taxonomies; ?>

<div id="single-pin-wrapper">
	<div class="container" id="single-pin" data-postid="<?php the_ID(); ?>" itemscope itemtype="http://schema.org/ImageObject">
		<div class="row">
			<div class="col-sm-8">
				<div class="row">
					<div class="col-sm-12">
						<?php while (have_posts()) : the_post(); ?>
						<?php
						$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
						$imgsrc_full = $imgsrc;
	
						//exclude animated gif
						if (substr($imgsrc[0], -3) != 'gif' && intval($imgsrc[1]) > 800) {
							$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
						}
						
						if ($imgsrc[0] == '') {
							$imgsrc[0] = get_template_directory_uri() . '/img/blank.gif';
						}
						
						$original_post_id = get_post_meta($post->ID, "_Original Post ID", true);
						$photo_source = get_post_meta($post->ID, "_Photo Source", true);
						$photo_source_domain = get_post_meta($post->ID, "_Photo Source Domain", true);
						$post_video = ipin_get_post_video($photo_source);
	
						$post_likes = get_post_meta($post->ID, "_Likes User ID");
						if($post_likes) {
							$post_likes_count = count($post_likes[0]);
						} else {
							$post_likes_count = 0;
						}
	
						$post_repins = get_post_meta($post->ID, "_Repin Post ID");
						if($post_repins) {
							$post_repins_count = count($post_repins[0]);
						} else {
							$post_repins_count = 0;
						}
						?>
						<div id="post-<?php the_ID(); ?>" <?php post_class('post-wrapper'); ?>>
							<div class="post-top-meta-placeholder"></div>
							<div class="post-top-meta">
								<div class="post-actionbar">
									<?php if ($post->post_status == 'publish' && (current_user_can('administrator') || current_user_can('editor') || current_user_can('author') || !is_user_logged_in())) { ?>
									<span class="post-action-button">
										<a class="ipin-repin btn btn-success" data-post_id="<?php echo $post->ID ?>" rel="tooltip" title="<?php _e('Repin', 'ipin'); ?>" data-placement="bottom"><i class="fa fa-retweet fa-fw"></i><span id="button-repins-count"><?php if ($post_repins_count > 0) echo '&nbsp; ' . $post_repins_count; ?></span></a>
									</span>
									<?php } ?>
									
									<?php if ($post->post_status == 'publish' && $post->post_author != $user_ID) { ?> 
									<span class="undisable_buttons post-action-button">
										<a class="ipin-like btn btn-success<?php if (ipin_liked($post->ID)) { echo ' disabled'; } ?>" data-post_id="<?php echo $post->ID ?>" data-post_author="<?php echo $post->post_author; ?>" rel="tooltip" title="<?php _e('Like', 'ipin'); ?>" data-placement="bottom"><i class="fa fa-heart fa-fw"></i><span id="button-likes-count"><?php if ($post_likes_count > 0) echo '&nbsp; ' . $post_likes_count; ?></span></a>
									</span>
									<?php } else { ?>
									<span id="likeownpin" class="post-action-button">
										<a class="btn btn-success" rel="tooltip" title="<?php _e('Cannot Like Own Pin', 'ipin'); ?>" data-placement="bottom"><i class="fa fa-heart fa-fw"></i><span id="button-likes-count"><?php if ($post_likes_count > 0) echo '&nbsp; ' . $post_likes_count; ?></span></a>
									</span>
									<?php } ?>
									
									<div class="ipin-share btn-group post-action-button">
										<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
											<i class="fa fa-share-alt"></i> <span class="caret"></span>
										</button>
										
										<ul class="dropdown-menu">
											<li><a href="" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode(get_permalink()); ?>', 'facebook-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-facebook-square fa-lg fa-fw text-info"></i> <?php _e('Share on Facebook', 'ipin'); ?></a></li>
											<li><a href="" onclick="window.open('https://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php echo rawurlencode(html_entity_decode(get_the_title())); ?>', 'twitter-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-twitter-square fa-lg fa-fw text-primary"></i> <?php _e('Share on Twitter', 'ipin'); ?></a></li>
											<li><a href="" onclick="window.open('http://www.reddit.com/submit?url=<?php echo rawurlencode(get_permalink()); ?>&amp;title=<?php echo rawurlencode(html_entity_decode(get_the_title())); ?>', 'reddit-share-dialog', 'width=880,height=500,scrollbars=1'); return false;"><i class="fa fa-reddit-square fa-lg fa-fw text-primary"></i> <?php _e('Share on Reddit', 'ipin'); ?></a></li>
											<li><a href="" onclick="window.open('https://plus.google.com/share?url=<?php the_permalink(); ?>', 'gplus-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-google-plus-square fa-lg fa-fw text-danger"></i> <?php _e('Share on Google+', 'ipin'); ?></a></li>
											<li><a href="" onclick="window.open('http://pinterest.com/pin/create/button/?url=<?php echo rawurlencode(get_permalink()); ?>&amp;media=<?php echo rawurlencode($imgsrc[0]); ?>&amp;description=<?php the_title_attribute(); ?>', 'pinterest-share-dialog', 'width=626,height=500'); return false;"><i class="fa fa-pinterest-square fa-lg fa-fw text-danger"></i> <?php _e('Share on Pinterest', 'ipin'); ?></a></li>
											<li><a href="" class="post-embed"><i class="fa fa-code fa-lg fa-fw"></i> <?php _e('Embed', 'ipin'); ?></a></li>
										</ul>
									</div>
									
									<?php if (!$post_video) { ?>
									<span class="post-action-button">
										<a class="ipin-zoom btn btn-success hidden-xs" href="<?php echo $imgsrc_full[0]; ?>" rel="tooltip" title="<?php _e('Zoom', 'ipin'); ?>" data-placement="bottom"><i class="fa fa-search-plus fa-fw"></i></a>
									</span>
									<?php } ?>
									
									<span class="post-action-button">
										<a class="post-report btn btn-success" rel="tooltip" title="<?php _e('Report', 'ipin'); ?>" data-placement="bottom"><i class="fa fa-flag fa-fw"></i></a>
									</span>
									
									<?php if ($post->post_author == $user_ID || current_user_can('edit_others_posts')) { ?>
									<span class="post-action-button">
										<a class="ipin-edit btn btn-success" href="<?php echo home_url('/itm-settings/'); ?>?i=<?php the_ID(); ?>" rel="tooltip" title="<?php _e('Edit', 'ipin'); ?>" data-placement="bottom"><i class="fa fa-pencil fa-fw"></i></a>
									</span>
									<?php } ?>

									<?php if ($photo_source != '') { ?>
									<span class="pull-right">
										<a class="btn btn-default" href="<?php echo $photo_source; ?>" target="_blank"><img src="https://www.google.com/s2/favicons?domain=<?php echo $photo_source; ?>" alt="" /> <?php echo $photo_source_domain; ?></a>
									</span>
									<?php } ?>
									<div class="clearfix"></div>
								</div>
							</div>
							
							<div class="clearfix"></div>
							
							<?php if (of_get_option('single_pin_above_ad') != '') { ?>
							<div id="single-pin-above-ad">
								<?php eval('?>' . of_get_option('single_pin_above_ad')); ?>
							</div>
							<?php } ?>
							
							<div id="post-featured-photo" class="post-featured-photo">
								<div class="post-nav-next"><?php echo previous_post_link('%link', '<i class="fa fa-chevron-right"></i>', false, ipin_blog_cats()); ?></div>
								<div class="post-nav-prev"><?php echo next_post_link('%link', '<i class="fa fa-chevron-left"></i>', false, ipin_blog_cats()); ?></div>
									
								<?php if ($post_video) { ?>
									<div class="video-embed-wrapper">
										<?php echo $post_video; ?>
									</div>
									<img itemprop="image" class="featured-thumb hide" src="<?php echo $imgsrc[0]; ?>" width="<?php echo $imgsrc[1]; ?>" height="<?php echo ($imgsrc[1] > 800) ? (round($imgsrc[1]/$imgsrc[2]*800)) : $imgsrc[2]; ?>" alt="<?php echo mb_strimwidth(the_title_attribute('echo=0'), 0, 255, ' ...') ?>" />
								<?php } else {  ?>
									<img itemprop="image" class="featured-thumb" src="<?php echo $imgsrc[0]; ?>" width="<?php echo $imgsrc[1]; ?>" height="<?php echo ($imgsrc[1] > 800) ? (round($imgsrc[1]/$imgsrc[2]*800)) : $imgsrc[2]; ?>" alt="<?php echo mb_strimwidth(the_title_attribute('echo=0'), 0, 100, ' ...'); ?>" />
								<?php } ?>
							</div>
	
							<?php if (of_get_option('single_pin_below_ad') != '') { ?>
							<div id="single-pin-below-ad">
								<?php eval('?>' . of_get_option('single_pin_below_ad')); ?>
							</div>
							<?php } ?>
							
							<?php
							$tags = '';
							if (of_get_option('posttags') == 'enable') {
								$the_tags = get_the_tags();
								if ($the_tags) {
									foreach($the_tags as $the_tag) {
										$tags .= $the_tag->name . ', ';
									}
									$tags = substr($tags, 0, -2);
								}
							}
							?>
	
							<div class="post-content">
								<?php
								if ($post->post_status == 'pending') {
									echo '<div class="clearfix"></div><span class="label label-warning">' . __('Pending Review', 'ipin') . '</span>';
								}
								?>
								<?php if (of_get_option('price_currency') != '' && ipin_get_post_price() != '') { ?>
									<div class="post-content-price pull-right"><span class="badge">&#8226; <?php echo ipin_get_post_price(); ?></span></div>
								<?php }	?>
	
								<?php if (of_get_option('form_title_desc') != 'separate') { ?>						
									<?php if (mb_strlen(get_the_title()) < 120) { ?>
										<h1 itemprop="name" class="post-title" data-title="<?php echo esc_attr($post->post_title); ?>" data-tags="<?php echo esc_attr($tags); ?>" data-price="<?php echo esc_attr(ipin_get_post_price(false)); ?>" data-content="<?php echo esc_attr($post->post_content); ?>"><?php echo wpautop(preg_replace_callback('/<a[^>]+/', 'ipin_nofollow_callback', get_the_title())); ?></h1>
									<?php } else { ?>
										<div itemprop="name" class="post-title" data-title="<?php echo esc_attr($post->post_title); ?>" data-tags="<?php echo esc_attr($tags); ?>" data-price="<?php echo esc_attr(ipin_get_post_price(false)); ?>" data-content="<?php echo esc_attr($post->post_content); ?>"><?php echo wpautop(preg_replace_callback('/<a[^>]+/', 'ipin_nofollow_callback', get_the_title())); ?></div>
									<?php } ?>
								<?php } else { ?>
										<h1 itemprop="name" class="post-title post-title-large" data-title="<?php echo esc_attr($post->post_title); ?>" data-tags="<?php echo esc_attr($tags); ?>" data-price="<?php echo esc_attr(ipin_get_post_price(false)); ?>" data-content="<?php echo esc_attr($post->post_content); ?>"><?php the_title(); ?></h1>
								<?php } ?>
	
								<?php
								echo '<div itemprop="description" class="thecontent">' . preg_replace_callback('/<a[^>]+/', 'ipin_nofollow_callback', apply_filters('the_content', get_the_content()))  . '</div>';
	
								if ($the_tags) {
									echo '<div itemprop="keywords" class="thetags">';
									
									foreach($the_tags as $the_tag) {
										echo '<a href="' . get_tag_link($the_tag->term_id). '">' . $the_tag->name . '</a> '; 
									}
									
									echo '</div>';
								}
								wp_link_pages( array( 'before' => '<p><strong>' . __('Pages:', 'ipin') . '</strong>', 'after' => '</p>' ) );
								?>
							</div>
							
							<div class="post-author-wrapper">
								<div class="pull-left">
									<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . get_the_author_meta('user_nicename'); ?>/">
									<?php echo get_avatar($post->post_author, '48'); ?>
									</a>
								</div>
								
								<div class="post-author-wrapper-header">
									<?php if ($post->post_author != $user_ID) { ?> 
									<span class="undisable_buttons">
									<button class="btn btn-success pull-right follow ipin-follow<?php if ($followed = ipin_followed(ipin_get_post_board()->parent)) { echo ' disabled'; } ?>" data-board_parent_id="0" data-author_id="<?php echo $post->post_author; ?>" data-board_id="<?php echo ipin_get_post_board()->parent; ?>" type="button"><?php if (!$followed) { _e('Follow', 'ipin'); } else { _e('Unfollow', 'ipin'); } ?></button>
									</span>
									<?php } else { ?>
									<button class="btn btn-success pull-right follow" disabled="disabled" type="button"><?php _e('Myself!', 'ipin'); ?></button>
									<?php } ?>
									<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . get_the_author_meta('user_nicename'); ?>/">
										<div itemprop="author" class="post-author-wrapper-author"><?php echo get_the_author_meta('display_name'); ?></div>
									</a> 
									<?php echo ' &#8226; ' . ipin_human_time_diff(get_post_time('U', true)); ?>
									<br /><?php $pins_count = count_user_posts($post->post_author); echo $pins_count; ?> <?php if ($pins_count == 1) _e('Pin', 'ipin'); else _e('Pins', 'ipin'); ?> &#8226; <?php if ('' == $followers_count = get_user_meta($post->post_author, '_Followers Count', true)) echo '0'; else echo $followers_count; ?> <?php if ($followers_count == 1) _e('Follower', 'ipin'); else _e('Followers', 'ipin'); ?>
									<time itemprop="datePublished" datetime="<?php the_time('Y'); ?>-<?php the_time('m'); ?>-<?php the_time('d'); ?>"></time>
								</div>
							</div>
							
							<div class="post-comments">
								<div class="post-comments-wrapper">
									<?php if ($post->post_status == 'publish') { ?>
										<?php comments_template(); ?>
										<?php if (of_get_option('facebook_comments') != 'disable' && comments_open()) { ?>
										<div class="fb-comments" data-href="<?php the_permalink(); ?>" data-num-posts="5"<?php if (of_get_option('color_scheme') == 'dark') { echo ' data-colorscheme="dark"'; } ?> data-width="100%"></div>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
							
							<?php if (ipin_get_post_board()) { ?>
							<div class="post-board">
								<div class="post-board-wrapper">
									<?php if ($post->post_author != $user_ID) { ?>
									<span class="undisable_buttons">
									<button class="btn btn-success btn-xs pull-right follow ipin-follow<?php if ($followed = ipin_followed(ipin_get_post_board()->term_id)) { echo ' disabled'; } ?>" data-author_id="<?php echo $post->post_author; ?>" data-board_id="<?php echo ipin_get_post_board()->term_id;  ?>" data-board_parent_id="<?php echo ipin_get_post_board()->parent; ?>" type="button"><?php if (!$followed) { _e('Follow Board', 'ipin'); } else { _e('Unfollow Board', 'ipin'); } ?></button>
									</span>
									<?php } else { ?>
									<a class="btn btn-success btn-xs pull-right follow edit-board" href="<?php echo home_url('/grp-settings/?i=') . ipin_get_post_board()->term_id; ?>"><?php _e('Edit Board', 'ipin'); ?></a>
									<?php } ?>
									<h4><?php _e('Pinned onto', 'ipin') ?> <?php the_terms($post->ID, 'board', '<span>', ', ', '</span>'); ?></h4>
									<?php
									$board_id = ipin_get_post_board()->term_id;
									$board_name = ipin_get_post_board()->name;
									$board_count = ipin_get_post_board()->count;
									$board_slug = ipin_get_post_board()->slug;
									$board_link = home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($board_name, '_') . '/' . $board_id . '/');
								
									$board_thumbnail_ids = $wpdb->get_col($wpdb->prepare(
										"
										SELECT v.meta_value
										FROM $wpdb->postmeta AS v
										INNER JOIN (
											SELECT object_id
											FROM $wpdb->term_taxonomy, $wpdb->term_relationships, $wpdb->posts
											WHERE $wpdb->term_taxonomy.term_id = %d
											AND $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
											AND $wpdb->term_taxonomy.taxonomy = 'board'
											AND $wpdb->term_relationships.object_id = $wpdb->posts.ID
											AND $wpdb->posts.post_status = 'publish'
											ORDER BY $wpdb->term_relationships.object_id DESC
											LIMIT 0, 13
											) AS v2 ON v.post_id = v2.object_id
											AND v.meta_key = '_thumbnail_id'
										",
										$board_id
									));
									?>
										<a class="pull-left" href="<?php echo $board_link; ?>">
										<?php
										$post_array = array();
										foreach ($board_thumbnail_ids as $board_thumbnail_id) {
											$board_imgsrc = wp_get_attachment_image_src($board_thumbnail_id, 'thumbnail');
											$board_imgsrc = $board_imgsrc[0];
											array_unshift($post_array, $board_imgsrc);
										}
	
										$post_array_final = array_fill(0, 13, '');
										
										foreach ($post_array as $post_imgsrc) {
											array_unshift($post_array_final, $post_imgsrc);
											array_pop($post_array_final);
										}
										
										foreach ($post_array_final as $post_final) {
											if ($post_final !=='') {
												?>
												<div class="post-board-photo">
													<img src="<?php echo $post_final; ?>" alt="" />
												</div>
												<?php
											} else {
												?>
												<div class="post-board-photo">
												</div>
												<?php
											}
										}
										?>
										</a>
								</div>
								
								<div class="clearfix"></div>
							</div>
							<?php } ?>
							
							<?php if ($original_post_id != '' && $original_post_id != 'deleted') { ?>
							<div class="post-board">
								<div class="post-board-wrapper">
									<?php
									$original_postdata = get_post($original_post_id, 'ARRAY_A');
									$original_author = get_user_by('id', $original_postdata['post_author']);
									$original_board = wp_get_post_terms($original_post_id, 'board', array("fields" => "all"));
									$original_board_id = $original_board[0]->term_id;
									$original_board_name = $original_board[0]->name;
									$original_board_count = $original_board[0]->count;
									$original_board_slug = $original_board[0]->slug;
									$original_board_link = home_url('/' . $wp_taxonomies["board"]->rewrite['slug'] . '/' . sanitize_title($original_board[0]->name, '_') . '/' . $original_board[0]->term_id . '/');
									?>
									<?php if ($original_author->ID != $user_ID) { ?>
									<span class="undisable_buttons">
									<button class="btn btn-success btn-xs pull-right follow ipin-follow<?php if ($followed = ipin_followed($original_board[0]->term_id)) { echo ' disabled'; } ?>" data-author_id="<?php echo $original_author->ID; ?>" data-board_id="<?php echo $original_board[0]->term_id;  ?>" data-board_parent_id="<?php echo $original_board[0]->parent; ?>" type="button"><?php if (!$followed) { _e('Follow Board', 'ipin'); } else { _e('Unfollow Board', 'ipin'); } ?></button>
									</span>
									<?php } else { ?>
									<a class="btn btn-success btn-xs pull-right follow edit-board" href="<?php echo home_url('/grp-settings/?i=') . $original_board[0]->term_id; ?>"><?php _e('Edit Board', 'ipin'); ?></a>
									<?php } ?>
									<h4><?php _e('Repinned from', 'ipin') ?> <a href="<?php echo $original_board_link; ?>"><?php echo $original_board[0]->name; ?></a></h4>
									<?php
									$original_board_thumbnail_ids = $wpdb->get_col($wpdb->prepare(
										"
										SELECT v.meta_value
										FROM $wpdb->postmeta AS v
										INNER JOIN (
											SELECT object_id
											FROM $wpdb->term_taxonomy, $wpdb->term_relationships, $wpdb->posts
											WHERE $wpdb->term_taxonomy.term_id = %d
											AND $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
											AND $wpdb->term_taxonomy.taxonomy = 'board'
											AND $wpdb->term_relationships.object_id = $wpdb->posts.ID
											AND $wpdb->posts.post_status = 'publish'
											ORDER BY $wpdb->term_relationships.object_id DESC
											LIMIT 0, 15
											) AS v2 ON v.post_id = v2.object_id
											AND v.meta_key = '_thumbnail_id'
										",
										$original_board_id
									));
									
									?>
										<a class="pull-left" href="<?php echo $original_board_link; ?>">
										<?php
										$original_post_array = array();
										foreach ($original_board_thumbnail_ids as $original_board_thumbnail_id) {
											$original_board_imgsrc = wp_get_attachment_image_src($original_board_thumbnail_id, 'thumbnail');
											$original_board_imgsrc = $original_board_imgsrc[0];
											array_unshift($original_post_array, $original_board_imgsrc);
										}
	
										$original_post_array_final = array_fill(0, 15, '');
										
										foreach ($original_post_array as $original_post_imgsrc) {
											array_unshift($original_post_array_final, $original_post_imgsrc);
											array_pop($original_post_array_final);
										}
										
										foreach ($original_post_array_final as $original_post_final) {
											if ($original_post_final !=='') {
												?>
												<div class="post-board-photo">
													<img src="<?php echo $original_post_final; ?>" alt="" />
												</div>
												<?php
											} else {
												?>
												<div class="post-board-photo">
												</div>
												<?php
											}
										}
										?>
										</a>
								</div>
								
								<div class="clearfix"></div>
							</div>
							<?php }	?>
							
							<?php
							if ($photo_source_domain != '') {
								$loop_domain_args = array(
									'posts_per_page' => 13,
									'meta_key' => '_Photo Source Domain',
									'meta_value' => $photo_source_domain,
									'post__not_in' => array($post->ID),
									'ignore_sticky_posts' => 1,
									'meta_query' => array(
										'relation' => 'OR',
										array(
											'key' => '_Original Post ID',
											'compare' => 'NOT EXISTS'
										),
										array(
											'key' => '_Original Post ID',
											'value' => 'deleted'
										)
									)
									
								);
								
								$loop_domain = new WP_Query($loop_domain_args);
								if ($loop_domain->post_count > 0) {
							?>
								<div id="post-board-source" class="post-board">
									<div class="post-board-wrapper">
										<h4><?php _e('Also from', 'ipin'); ?> <a href="<?php echo home_url('/source/') . $photo_source_domain; ?>/"><?php echo $photo_source_domain; ?></a></h4>
											<a class="pull-left" href="<?php echo home_url('/source/') . $photo_source_domain; ?>/">
											<?php
											$post_domain_array = array();
											while ($loop_domain->have_posts()) : $loop_domain->the_post();
												$domain_imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id(),'thumbnail');
												$domain_imgsrc = $domain_imgsrc[0];
												array_unshift($post_domain_array, $domain_imgsrc);
											endwhile;
											wp_reset_query();
											
											$post_domain_array_final = array_fill(0, 13, '');
											
											foreach ($post_domain_array as $post_imgsrc) {
												array_unshift($post_domain_array_final, $post_imgsrc);
												array_pop($post_domain_array_final);
											}
											
											foreach ($post_domain_array_final as $post_final) {
												if ($post_final !=='') {
													?>
													<div class="post-board-photo">
														<img src="<?php echo $post_final; ?>" alt="" />
													</div>
													<?php
												} else {
													?>
													<div class="post-board-photo">
													</div>
													<?php
												}
											}
											?>
											</a>
									</div>
									<div class="clearfix"></div>
								</div>
							<?php
								}
							}
							
							if (!empty($post_likes[0])) {
							$post_likes[0] = array_slice($post_likes[0], -16);
							?>
							<div class="post-likes">
								<div class="post-likes-wrapper">
									<h4><?php _e('Likes', 'ipin'); ?></h4>
									<div class="post-likes-avatar">
									<?php
									foreach ($post_likes[0] as $post_like) {
										$like_author = get_user_by('id', $post_like);
										?>
										<a id="likes-<?php echo $post_like; ?>" href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $like_author->user_nicename; ?>/" rel="tooltip" title="<?php echo esc_attr($like_author->display_name); ?>">
										<?php echo get_avatar($like_author->ID, '48'); ?>
										</a>
									<?php 
									}
									if ($post_likes_count > 16) {
									?>
										<p class="more-likes"><strong>+<?php echo $post_likes_count - 16 ?></strong> <?php _e('more likes', 'ipin'); ?></p>
									<?php } ?>
									</div>
								</div>
							</div>
							<?php } ?>
							
							<?php
							if (!empty($post_repins[0])) {
							$post_repins[0] = array_slice($post_repins[0], -10);
							?>
							<div id="post-repins">
								<div class="post-repins-wrapper">
									<h4><?php _e('Repins', 'ipin'); ?></h4>
									<ul>
									<?php
									foreach ($post_repins[0] as $post_repin) {
										$repin_postdata = get_post($post_repin, 'ARRAY_A');
										$repin_author = get_user_by('id', $repin_postdata['post_author']);
										?>
										<li id="repins-<?php echo $post_repin; ?>">
										<a class="post-repins-avatar pull-left" href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $repin_author->user_nicename; ?>/">
										<?php echo get_avatar($repin_author->ID, '48'); ?>
										</a> 
										<div class="post-repins-content">
										<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $repin_author->user_nicename; ?>/">
										<?php echo $repin_author->display_name; ?>
										</a> 
										<?php
										_e('onto', 'ipin');
										$board = wp_get_post_terms($post_repin, 'board', array("fields" => "all"));
										if (!is_wp_error($board) && !empty($board)) {
											echo ' <a href="' . get_term_link($board[0]->slug, 'board') . '">' . $board[0]->name . '</a></div>';
										} else {
											echo ' ...';	
										}
										?>
										</li>
									<?php 
									}	
									if ($post_repins_count > 10) {
									?>
										<li class="more-repins"><strong>+<?php echo $post_repins_count - 10; ?></strong> <?php _e('more repins', 'ipin'); ?></li>
									<?php } ?>
									</ul>
								</div>
							</div>
							<?php } ?>
							
							<div class="modal ipin-modal" id="post-embed-box" data-backdrop="false" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button class="close popup-close" aria-hidden="true" type="button">&times;</button>
											<h4 class="modal-title"><?php _e('Embed', 'ipin'); ?></h4>
										</div>
										
										<div class="modal-body">
											<?php $size = getimagesize(realpath(str_replace(home_url('/'),'',$imgsrc[0]))); ?>
											<div class="row">
												<div class="col-xs-6">
													<input class="form-control" type="text" id="embed-width" value="<?php echo $size[0]; ?>" />
												</div>
						
												<div class="col-xs-6">
													<span class="help-inline"><?php _e('px -Image Width', 'ipin'); ?></span>
												</div>
											</div>
											
											<p></p>
	
											<div class="row">
												<div class="col-xs-6">
													<input class="form-control" type="text" id="embed-height" value="<?php echo $size[1]; ?>" />
												</div>
												
												<div class="col-xs-6">
													<span class="help-inline"> <?php _e('px -Image Height', 'ipin'); ?></span>
												</div>
											</div>
	
											<p></p>
	
											<textarea class="form-control"><div style='padding-bottom: 2px;line-height:0px;'><a href='<?php the_permalink(); ?>' target='_blank'><img src='<?php echo $imgsrc[0]; ?>' border='0' width='<?php echo $size[0]; ?>' height='<?php echo $size[1]; ?>' /></a></div><div style='float:left;padding-top:0px;padding-bottom:0px;'><p style='font-size:10px;color:#76838b;'><?php _e('Source', 'ipin'); ?>: <a style='text-decoration:underline;font-size:10px;color:#76838b;' href='<?php echo $photo_source;  ?>'><?php echo $photo_source_domain; ?></a> <?php _e('via', 'ipin'); ?> <a style='text-decoration:underline;font-size:10px;color:#76838b;' href='<?php echo home_url('/' . $wp_rewrite->author_base . '/') . get_the_author_meta('user_nicename'); ?>' target='_blank'><?php echo get_the_author_meta('display_name'); ?></a> <?php _e('on', 'ipin'); ?> <a style='text-decoration:underline;color:#76838b;' href='<?php echo home_url('/'); ?>' target='_blank'><?php bloginfo('name'); ?></a></p></div></textarea>
											<div class="clearfix"></div>
											<p></p>
										</div>
									</div>
								</div>
							</div>
							
							<div class="modal ipin-modal" id="post-report-box" data-backdrop="false" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button class="close popup-close" aria-hidden="true" type="button">&times;</button>
											<h4 class="modal-title"><?php _e('Report', 'ipin'); ?></h4>
										</div>
										
										<div class="modal-body">
											<input type="hidden" id="report-post-id" value="<?php echo $post->ID; ?>" />
											<textarea class="form-control" placeholder="<?php _e('Please write a little about why you want to report this pin.', 'ipin'); ?>"></textarea>
											<div class="alert alert-success hide"><strong><?php _e('Pin reported. Thank you for your submission.', 'ipin'); ?></strong></div>
											<p></p>
											<input id="post-report-submit" class="btn btn-success btn-block btn-ipin-custom" type="submit" disabled="disabled" value="<?php _e('Report Pin', 'ipin'); ?>" name="post-report-submit">
											<input id="post-report-close" class="btn btn-success btn-block btn-ipin-custom hide" type="submit" value="<?php _e('Close', 'ipin'); ?>">
											<div class="ajax-loader-report-pin ajax-loader hide"></div>
											<div class="clearfix"></div>
											<p></p>
										</div>
									</div>
								</div>
							</div>
	
							<button id="post-close" class="btn btn-default hide"><i class="fa fa-times"></i></button>
							
							<div id="post-zoom-overlay"></div>
							
							<div id="post-fullsize" class="lightbox hide" tabindex="-1" aria-hidden="true">
								<div class='lightbox-header'>
									<button id="post-fullsize-close" class="btn btn-default" aria-hidden="true" type="button"><i class="fa fa-times"></i></button>
								</div>
								<div class="lightbox-content">
									<img src="" data-src="<?php echo $imgsrc_full[0]; ?>" width="<?php echo $imgsrc_full[1]; ?>" height="<?php echo $imgsrc_full[2]; ?>" alt="" />
								</div>
							</div>
	
							<?php /*
							<div class="modal ipin-modal" id="post-email-box" data-backdrop="false" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button class="close popup-close" aria-hidden="true" type="button">&times;</button>
											<h4 class="modal-title"><?php _e('Email A Friend', 'ipin'); ?></h4>
										</div>
										
										<div class="modal-body">
											<input class="form-control" type="text" id="recipient-name" placeholder="<?php _e('Recipient Name', 'ipin'); ?>" />
											<p></p>
											<input class="form-control" type="email" id="recipient-email" placeholder="<?php _e('Recipient Email', 'ipin'); ?>" />
											<p></p>
											<input type="hidden" id="email-post-id" value="<?php echo $post->ID; ?>" />
											<p></p>
											<textarea class="form-control" placeholder="<?php _e('Message (optional)', 'ipin'); ?>"></textarea>
											<p></p>
											<input class="btn btn-success btn-block btn-ipin-custom" type="submit" disabled="disabled" value="<?php _e('Send Email', 'ipin'); ?>" id="post-email-submit" name="post-email-submit">
											<div class="ajax-loader-email-pin ajax-loader hide"></div>
											<div class="clearfix"></div>
											<p></p>
										</div>
									</div>
								</div>
							</div>
							*/ ?>
						</div>
						<?php endwhile; ?>
					</div>
				</div>
			</div>
	
			<div class="col-sm-4">
				<?php get_sidebar('right'); ?>
			</div>
		</div>
	</div>	
	<?php get_template_part('single', 'masonry'); ?>
</div>

<?php get_footer(); ?>