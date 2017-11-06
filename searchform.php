<form class="form-inline" method="get" action="<?php echo home_url(); ?>/">
	<div class="form-group">
		<input class="form-control" type="text" value="<?php the_search_query(); ?>" name="s"  />
	</div>
	<button class="btn btn-info" type="submit" /><?php _e('Search', 'ipin'); ?></button>
</form>