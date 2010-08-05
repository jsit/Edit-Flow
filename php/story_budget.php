<?php

/**
 * This class displays a budgeting system for an editorial desks publishing workflow.
 * This is a cursory attempt at implementation with many outstanding TODOs.
 *
 * Somewhat prioritized TODOs:
 * TODO: Any and all filtering
 * TODO: Add filtering for single day. Month filtering probably useless?
 * TODO: Trash links (with nonce)
 * TODO: Author, status, and category links for each post
 * TODO: Make sure working properly with custom statuses
 * TODO: Integrate with Screen Options API
 * TODO: Review inline TODOs
 *
 * @author Scott Bressler
 */
class story_budget {
	var $taxonomy_used = 'category';

	function __construct() {
	}
	
	function story_budget() {
		$terms = get_terms($this->taxonomy_used, 'orderby=name&order=desc&parent=0');
		$ordered_terms = apply_filters( 'story_budget_reorder_terms', $terms ); // allow for reordering or any other filtering of terms
?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#toggle_details").click(function() {
				$(".post-title > p").toggle(); // hide post details when directed to
			});
			$("h3.hndle,div.handlediv").click(function() {
				$(this).parent().children("div.inside").slideToggle(); // hide sections when directed to
			});
		});
	</script>
		<?php $this->table_navigation(); ?>

		<div class="clear"></div>
	
		<div id="dashboard-widgets-wrap">
			<div id="dashboard-widgets" class="metabox-holder">
				<div class='postbox-container' style='width:49%;'>
					<div class="meta-box-sortables">
						<?php
							if ( count($ordered_terms) > 0 )
								for ($i = 0; $i < count($ordered_terms); $i+=2)
									$this->term_display( $ordered_terms[$i] );
							else
								echo 'You have no terms from which to display stories!';
						?>
					</div>
				</div>
		
				<div class='postbox-container' style='width:49%;'>
					<div class="meta-box-sortables">
						<?php
							if ( count($ordered_terms) > 1 )
								for ($i = 1; $i < count($ordered_terms); $i+=2)
									$this->term_display( $ordered_terms[$i] );
						?>
					</div>
				</div>
			</div>
		</div><!-- /dashboard-widgets -->
<?php
	}
	
	function term_display($term) {
?>
	<div class="postbox">
		<div class="handlediv" title="Click to toggle"><br /></div>
		<h3 class='hndle'><span><?php echo $term->name; ?></span></h3>
		<div class="inside">
			<table class="widefat post fixed story-budget" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" id="title" class="manage-column column-title" >Title</th>
						<th scope="col" id="author" class="manage-column column-author">Author</th>
						<th scope="col" id="status" class="manage-column column-author">Status</th>
						<th scope="col" id="categories" class="manage-column column-categories" title="Subcategories of <?php echo $term->name;?>">Subcats</th>
					</tr>
				</thead>

				<tfoot></tfoot>

				<tbody>
					<?php
					global $post;
					$today = getdate();
					$date_query = '';//year=' .$today["year"] .'&monthnum=' .$today["mon"] .'&day=' .$today["mday"];
					$posts = get_posts(array(
						'cat' => $term->term_id,
						'date' => $date_query,
						'post_status' => '' // doesn't pull in posts with custom_status it seems!
						)
					);					
					foreach ($posts as $post)
						$this->post_display($post, $term);
					?>
				</tbody>
			</table>
		</div>
	</div>
<?php
	}
	
	function post_display($the_post, $parent_term) {
		global $post;
		setup_postdata($post);
?>
			<tr id='post-<?php echo $post->ID; ?>' class='alternate author-self status-publish iedit' valign="top">

				<td class="post-title column-title">
					<strong><a class="row-title" href="post.php?post=<?php echo $post->ID; ?>&action=edit" title="Edit &#8220;<?php the_title(); ?>&#8221;"><?php the_title(); ?></a></strong>
					<p><?php the_excerpt(); ?></p>
					<p><?php do_action('story_budget_post_details'); ?></p>
					<div class="row-actions"><span class='edit'><a href="post.php?post=<?php echo $post->ID; ?>&action=edit">Edit</a> | </span><span class='inline hide-if-no-js'><a href="#" class="editinline" title="Edit this item inline">Quick&nbsp;Edit</a> | </span><span class='trash'><a class='submitdelete' title='Move this item to the Trash' href='#'>Trash</a> | </span><span class='view'><a href="<?php the_permalink(); // TODO: preview link? ?>" title="View &#8220;Test example post&#8221;" rel="permalink">View</a></span></div>
				</td>
				<td class="author column-author"><a href="#"><?php the_author(); ?></a></td>
				<td class="status column-status"><a href="#"><?php echo $post->post_status; // TODO: figure out why this doesn't work: get_term_by('slug', $post->post_status, 'post_status'); ?></a></td>
				<td class="categories column-categories">
					<?php
						// Display the subcategories of the post
						$categories = get_the_category();
						for ($i = 0; $i < count($categories); $i++) {
							$cat = $categories[$i];
							if ($cat->cat_ID != $parent_term->term_id) {
								echo "<a href='#'>{$cat->cat_name}</a>";
								echo ($i < count($categories) - 1) ? ', ' : '';
							}
						}
					?>
				</td>
			</tr>
<?php
	}
	
	
	function table_navigation() {
		global $edit_flow;
?>
	<div class="tablenav">
		<div class="alignleft actions">
			<?php $custom_statuses = $edit_flow->custom_status->get_custom_statuses(); ?>
			<select name='status'><!-- Status selectors -->
				<option selected='selected' value='0'>Show all statuses</option>
				<?php
					foreach ( $custom_statuses as $custom_status )
						echo "<option value='{$custom_status->slug}'>{$custom_status->name}</option>";
				?>
			</select>
			<select name='m'><!-- Archive selectors -->
				<option selected='selected' value='0'>Show all dates</option>
				<option value='201007'>July 2010</option>
				<option value='201006'>June 2010</option>
				<option value='201005'>May 2010</option>
				<option value='201004'>April 2010</option>
				<option value='201003'>March 2010</option>
				<option value='201002'>February 2010</option>
				<option value='200912'>December 2009</option>
				<option value='200911'>November 2009</option>
				<option value='200910'>October 2009</option>
				<option value='200909'>September 2009</option>
				<option value='200908'>August 2009</option>
				<option value='200907'>July 2009</option>
				<option value='200906'>June 2009</option>
				<option value='200905'>May 2009</option>
				<option value='200904'>April 2009</option>
				<option value='200903'>March 2009</option>
				<option value='200902'>February 2009</option>
				<option value='200901'>January 2009</option>
				<option value='200812'>December 2008</option>
				<option value='200811'>November 2008</option>
			</select>

			<?php
				// Borrowed from wp-admin/edit.php
				if ( ef_taxonomy_exists('category') ) {
					$dropdown_options = array('show_option_all' => __('View all categories'), 'hide_empty' => 0, 'hierarchical' => 1,
						'show_count' => 0, 'orderby' => 'name', 'selected' => $cat);
					wp_dropdown_categories($dropdown_options);
				}
			?>
			<input type="submit" id="post-query-submit" value="Filter" class="button-secondary" />
		</div><!-- /alignleft actions -->

		<p class="print-box" style="float:right; margin-right: 30px;"><!-- Print link -->
			<a href="#" id="toggle_details">Toggle Post Details</a> | <a href="#">Print</a>
		</p>
		<div class="clear"></div>
		
	</div><!-- /tablenav -->
<?php
	}
}

?>