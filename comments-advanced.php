<?php
/*
Plugin Name: Comments-advanced-mnt
Plugin URI: http://dev.techlog.pl/gogs/Monter/comments-advanced-mnt/
Description: Edit comment's info: post id, parent comment id, user id, author IP, date and author user agent.
Version: 1.4.2
Author: Monter
Author URI: http://monter.techlog.pl/
License: GPLv3
*/

$is_admin = is_admin() && ! defined( 'DOING_AJAX' );

if ( $is_admin ) {

function comments_advanced_unqprfx_add_meta() {
	add_meta_box( 'comment-info', 'Comment advanced info', 'comments_advanced_unqprfx_meta', 'comment', 'normal' );
}
add_action('admin_menu', 'comments_advanced_unqprfx_add_meta');

function comments_advanced_unqprfx_meta() {
	global $wpdb, $comment;
?>

<table class="widefat" cellspacing="0">
<tbody>
	<tr class="alternate">
		<td class="textright">
			<label for="comment_post_id">Post ID</label>
		</td>
		<td>
<?php
	$error = '<select name="comment_post_id" id="comment_post_id" disabled="disabled"><option>No posts found</option>';
	$list_data = $wpdb->get_results( "SELECT `ID`, `post_title` FROM `" . $wpdb->prefix . "posts` WHERE `post_status` = 'publish' AND `post_type` = 'post'ORDER BY `ID` DESC;", ARRAY_A );
	if ( ! empty( $list_data ) ) {
		$html = '<select name="comment_post_id" id="comment_post_id">';
		foreach ( $list_data as $list ) {
			$selected = ( ! empty( $comment->comment_post_ID ) && $list['ID'] == $comment->comment_post_ID ) ? ' selected="selected"' : '';
			$list['post_title'] = str_replace('&#8230;', '...', $list['post_title']);
			$item_title  = ( empty( $list['post_title'] ) ) ? ' - empty title - ' : $list['post_title'] ;
			if (mb_strlen($list['post_title']) > 70) {
				$item_title_str = mb_substr(wp_strip_all_tags($item_title), 0, 70, 'UTF-8').'...';
			} else {
				$item_title_str = wp_strip_all_tags($item_title);
			}
			$html .= '<option value="' . esc_attr($list['ID']) . '"' . $selected . '>' . $list['ID'] . ' > ' . $item_title_str . '</option>';
		}
		$html .= '</select>';
	} else {
		/* display error message */
		$html = $error . '</select>';
	}
	echo $html;
?>
		</td>
	</tr>
	<tr>
		<td class="textright">
			<label for="comment_parent">Parent comment ID</label>
		</td>
		<td>
			<select name="comment_parent" id="comment_parent">
			<option value='0'>0 > none : parent comment is not set</option>
<?php
	$commentsp = get_comments( array( 'post_id' => $comment->comment_post_ID, 'comment_approved' => 1 ) );
	foreach ( $commentsp as $commentp ) {
		if ( $commentp->comment_ID != $comment->comment_ID && $commentp->comment_ID < $comment->comment_ID ) { // hide himself and later
			if (mb_strlen($commentp->comment_content) > 50) {
				$comment_str = mb_substr(wp_strip_all_tags($commentp->comment_content), 0, 50, 'UTF-8') . "...";
			} else {
				$comment_str = wp_strip_all_tags($commentp->comment_content);
			}
			echo '<option value="'.esc_attr($commentp->comment_ID).'"';
			if ( $commentp->comment_ID == $comment->comment_parent ) echo ' selected';
			echo '>'.$commentp->comment_ID.' > '.$commentp->comment_author.' : '.$comment_str.'</option>';
		}
	}
?>
			</select><br />
			<?php echo "Hint: edited comment has the ID number: ".$comment->comment_ID; ?>
		</td>
	</tr>
	<tr class="alternate">
		<td class="textright">
			<label for="comment_user_id">User ID</label>
		</td>
		<td>
			<select name="comment_user_id" id="comment_user_id">
			<option value='0'>0 > Guest</option>
<?php
	$users = $wpdb->get_results( "SELECT " . $wpdb->prefix . "users.ID, " . $wpdb->prefix . "users.display_name, " . $wpdb->prefix . "usermeta.meta_value FROM " . $wpdb->prefix . "users
	 JOIN " . $wpdb->prefix . "usermeta ON " . $wpdb->prefix . "users.ID = " . $wpdb->prefix . "usermeta.user_id WHERE " . $wpdb->prefix . "usermeta.meta_key = 'wp_capabilities'
	 ORDER BY " . $wpdb->prefix . "users.ID;", ARRAY_A );
	foreach ( $users as $listus ) {
		$user_rol = unserialize($listus['meta_value']);
		foreach($user_rol AS $key => $item) {
			$usrole = $key;
		}
		echo '<option value="'.esc_attr($listus['ID']).'"';
		if ( $listus['ID'] == $comment->user_id ) echo ' selected';
		echo '>'.$listus['ID'].' > '.$listus['display_name'].' ('.$usrole.')</option>';
	}
?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="textright">
			<label for="comment_author_ip">Author IP</label>
		</td>
		<td>
			<input type="text" name="comment_author_ip" id="comment_author_ip" value="<?php echo esc_attr( $comment->comment_author_IP ); ?>" size="40" />
		</td>
	</tr>
	<tr class="alternate">
		<td class="textright">
			<label for="comment_date">Comment date</label>
		</td>
		<td>
			<input type="text" name="comment_date" id="comment_date" value="<?php echo esc_attr( $comment->comment_date ); ?>" size="40" />
		</td>
	</tr>
	<tr>
		<td class="textright">
			<label for="comment_agent">Author User Agent</label>
		</td>
		<td>
			<input type="text" name="comment_agent" id="comment_agent" value="<?php echo esc_attr( $comment->comment_agent ); ?>" size="40" />
		</td>
	</tr>
</tbody>
</table>

<?php
}

function comments_advanced_unqprfx_save_meta($comment_ID) {
	global $wpdb;

	$comment_post_ID = absint( $_POST['comment_post_id'] );
	$comment_parent = absint( $_POST['comment_parent'] );
	$user_id = absint( $_POST['comment_user_id'] );
	$comment_author_IP = esc_attr( $_POST['comment_author_ip'] );
	$comment_date = esc_attr( $_POST['comment_date'] );
	$comment_agent = esc_attr( $_POST['comment_agent'] );

	if ($comment_parent == $comment_ID) { // comment parent cannot be self
		return false; // don't update
	}

	$post = get_post($comment_post_ID); // check if post exist
	if ( !$post ) {
		return false; // don't update
	}

	$comment_row = $wpdb->get_row( $wpdb->prepare( "select * from $wpdb->comments where comment_ID = %s", $comment_ID ) );
	$old_comment_post_ID = $comment_row->comment_post_ID; // get old comment_post_ID

	if( $old_comment_post_ID != $comment_post_ID ){ // if comment_post_ID was updated
		wp_update_comment_count( $old_comment_post_ID ); // we need to update comment counts for both posts (old and new)
		wp_update_comment_count( $comment_post_ID );
		$comment_parent = "0"; // necessary reset comment_parent when comment is moved to another comment_post_ID
	}

	$wpdb->update(
		$wpdb->comments,
		array(
			'comment_post_ID' => $comment_post_ID,
			'comment_parent' => $comment_parent,
			'user_id' => $user_id,
			'comment_author_IP' => $comment_author_IP,
			'comment_date' => $comment_date,
			'comment_agent' => $comment_agent
		),
		array( 'comment_ID' => $comment_ID )
	);
}
add_action('edit_comment', 'comments_advanced_unqprfx_save_meta');

function comments_advanced_unqprfx_plugin_meta( $links, $file ) { // add 'Plugin page' and 'Donate' links to plugin meta row
	if ( strpos( $file, 'comments-advanced.php' ) !== false ) {
		$links = array_merge( $links, array( '<a href="http://dev.techlog.pl/gogs/Monter/comments-advanced-mnt/" title="Plugin page">Comments-advanced-mnt</a>' ) );
		$links = array_merge( $links, array( '<a href="http://monter.techlog.pl/stopka/dotacja/" title="Support the development">Donate</a>' ) );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'comments_advanced_unqprfx_plugin_meta', 10, 2 );

}
