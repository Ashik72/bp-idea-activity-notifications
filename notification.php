<?php 

function custom_filter_notifications_get_registered_components( $component_names = array() ) {
     
        if ( ! is_array( $component_names ) ) {
            $component_names = array();
        }

        array_push( $component_names, 'ideas' );
     
        array_push( $component_names, 'sabai_notification' );
                //echo "notify top<br>";

    //$user_id = bp_displayed_user_id();

    //$notifications = wp_cache_get( 'all_for_user_' . $user_id, 'bp_notifications' );


       // var_dump($notifications);

                   // echo "notify top end<br>";


        return $component_names;
    }
add_filter( 'bp_notifications_get_registered_components', 'custom_filter_notifications_get_registered_components' );


function custom_idea_buddypress_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
     
    //var_dump($action);

    $user_id = get_current_user_id();

    $get_sabai_meta = get_user_meta( $user_id, $secondary_item_id, 1 );

    //var_dump($format);

        // New custom notifications
        if ( 'ideas_action_'.$item_id === $action ) {


            $comment = get_comment( $item_id );
            
            $get_the_title = get_the_title( $comment->comment_post_ID );

            $custom_title = $comment->comment_author . __(' commented on the idea ', 'marylink-custom-plugin') . get_the_title( $comment->comment_post_ID );
            $custom_link  = get_comment_link( $comment );
            $custom_text = $comment->comment_author . __(' commented on your idea ', 'marylink-custom-plugin') . get_the_title( $comment->comment_post_ID );
     
            // WordPress Toolbar
            if ( 'string' === $format ) {
                $return = apply_filters( 'custom_filter', '<a data-test="abc" href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html( $custom_text ) . '</a>', $custom_text, $custom_link );


                //$return = apply_filters( 'custom_filter', '<a href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html__( '%1$s commented on your idea %2$s', $comment->comment_author, $get_the_title ) . '</a>', $custom_text, $custom_link );
     
            // Deprecated BuddyBar
            } else {
                $return = apply_filters( 'custom_filter', array(
                    'text' => $custom_text,
                    'link' => $custom_link
                ), $custom_link, (int) $total_items, $custom_text, $custom_title );
            }

/*            echo "custom set<br>";

            var_dump($item_id);
*/          

            return $return;
            
        } else if (!empty($get_sabai_meta)) {

            //echo "sabai";

            //var_dump($get_sabai_meta);

            $type = $get_sabai_meta['type'];

            switch ($type) {
                case 'qAns':
                    $text = __(' answered your question ', 'marylink-custom-plugin');
                    $tt_text = __(' answered on the question ', 'marylink-custom-plugin');
                    break;

                case 'dRev':
                    $text = __(' reviewed your directory post ', 'marylink-custom-plugin');
                    $tt_text = __(' reviewed on directory post ', 'marylink-custom-plugin');
                    break;

                case 'dCom':
                    $text = __(' commented on your directory post ', 'marylink-custom-plugin');
                    $tt_text = __(' commented on directory post ', 'marylink-custom-plugin');
                    break;

                case 'ansCom':
                    $text = __(' commented on your answer at ', 'marylink-custom-plugin');
                    $tt_text = __(' commented on answer at ', 'marylink-custom-plugin');
                    break;

                case 'ansAcp':
                    $text = __(' accepted your answer ', 'marylink-custom-plugin');
                    $tt_text = __(' accepted the answer ', 'marylink-custom-plugin');
                    break;                

                default:
                    $text = __(' reacted on ', 'marylink-custom-plugin');
                    $tt_text = __(' has reacted on ', 'marylink-custom-plugin');
                    break;
            }

     $user_info = get_userdata( (int) $get_sabai_meta['notifier_id']);
     $notifier_username = $user_info->user_login;
     $the_id = get_userdata($get_sabai_meta['post_id']);
     $the_title = $get_sabai_meta['title'];

//var_dump($get_sabai_meta);

$custom_title = $notifier_username . $tt_text . $the_title;
$custom_link  = $get_sabai_meta['url'];
$custom_text = $notifier_username . $text . $the_title;

        // WordPress Toolbar
            if ( 'string' === $format ) {
                $return = apply_filters( 'custom_filter', '<a data-test="abc" href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html( $custom_text ) . '</a>', $custom_text, $custom_link );
     
            // Deprecated BuddyBar
            } else {
                $return = apply_filters( 'custom_filter', array(
                    'text' => $custom_text,
                    'link' => $custom_link
                ), $custom_link, (int) $total_items, $custom_text, $custom_title );
            }

            return $return;


        }
        
    }
    add_filter( 'bp_notifications_get_notifications_for_user', 'custom_idea_buddypress_notifications', 10, 5 );


    function bp_ideas_add_notification( $comment_id, $comment_object ) {
     
        $post = get_post( $comment_object->comment_post_ID );
        $author_id = $post->post_author;
     

        if (get_post_type($comment_object->comment_post_ID) == 'ideas') { 

        bp_notifications_add_notification( array(
            'user_id'           => $author_id,
            'item_id'           => $comment_id,
            'secondary_item_id' => $comment_id+1000,
            'component_name'    => 'ideas',
            'component_action'  => 'ideas_action_'.$comment_id,
            'date_notified'     => bp_core_current_time()
        ) );



        if (bp_get_user_meta( bp_displayed_user_id(), 'notification_activity_new_reply_idea', true ) == 'yes') {

        	$user_info = get_userdata($author_id);
        	$user_info_mail = $user_info->user_email;
        	$site_mail = get_bloginfo('admin_email');
			
			$headers = 'From: '.IDEA_NOTIFICATION_EMAIL_HEADER.$site_mail. "\r\n";

			$message = IDEA_NOTIFICATION_EMAIL_MESSAGE;
			$message .= " <a href='";
			$message .= get_permalink($comment_object->comment_post_ID);
			$message .= "'>";
			$message .= get_the_title ($comment_object->comment_post_ID);
			$message .= "</a>";

			//$mail = wp_mail( $user_info_mail, IDEA_NOTIFICATION_EMAIL_SUBJECT, $message, $headers );
        }

        bp_notifications_clear_all_for_user_cache(bp_displayed_user_id());
        }
        
    }
    add_action( 'wp_insert_comment', 'bp_ideas_add_notification', 99, 2 );

	add_filter( 'wp_mail', 'mail_filter_ideas' );

   	//add_filter('comment_notification_text', 'test_func_09');
    function mail_filter_ideas($args) {

    	//echo get_post_type(get_the_ID());
    	//$attachments = array( WP_CONTENT_DIR . '/uploads/file_to_attach.zip' );
		//$mail = wp_mail( 'ashik@noksa.net', 'subject hello', 'message', $headers, $attachments );
		//var_dump($mail);

$url_link = array_values(preg_grep("/Permalink/", explode("\n", $args['message'])));
$rem1_url = preg_split("/Permalink/", $url_link[0]);
$rem2_url = preg_split("/\/#comment-/", $rem1_url[1]);
$final_id = (int)$rem2_url[1];

$id = $final_id;
$comment_object = get_comment($id);

$get_post = get_post($comment_object->comment_post_ID);
$author_id = get_user_by('email', $comment_object->comment_author_email);
$post_author_id = (int) $get_post->post_author;


 if (get_post_type($comment_object->comment_post_ID) == 'ideas') { 

 	$message =  $args['message'];

 	$notifify_idea = bp_get_user_meta( $post_author_id, 'notification_activity_new_reply_idea', true );

 	if ($notifify_idea === 'yes') {

	 	$to = $args['to'];

 	} else {

	 	$to = ' ';

 	}


 } else {

 	$message =  $args['message'];
 	$to = $args['to'];

 }


    $new_wp_mail = array(
		'to'          => $to,
		'subject'     => $args['subject'],
		'message'     => $message,
		'headers'     => $args['headers'],
		'attachments' => $args['attachments'],
	);
	
	return $new_wp_mail;


    }


    //add_action('wp_footer', 'try_90');

    function try_90() {

    	$user_meta_not = get_user_meta(get_current_user_id());
    	//$user_meta_not = bp_get_user_meta(get_current_user_id(), 'my_custom', 'yes');
    	//var_dump($post_author_id);
    	//var_dump($user_meta_not);


    }


    add_action('bp_activity_screen_notification_settings', 'bp_idea_settings', 999);

    function bp_idea_settings() {

    	if ( bp_get_user_meta( bp_displayed_user_id(), 'notification_activity_new_reply_idea', true ) === 'yes' ) {
		$reply_idea = 'yes';
		} else {

		$reply_idea = 'no';

		}
    	?>
		<tr id="activity-notification-settings-replies">
				<td>&nbsp;</td>
				<td><?php _e( "A member replies to an idea you've posted", 'marylink-custom-plugin' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_activity_new_reply_idea]" id="notification-activity-new-reply-idea-yes" value="yes" <?php checked( $reply_idea, 'yes', true ) ?>/><label for="notification-activity-new-reply-idea-yes" class="bp-screen-reader-text"><?php _e( 'Yes, send email', 'marylink-custom-plugin' ); ?></label></td>
				<td class="no"><input type="radio" name="notifications[notification_activity_new_reply_idea]" id="notification-activity-new-reply-idea-no" value="no" <?php checked( $reply_idea, 'no', true ) ?>/><label for="notification-activity-new-reply-idea-no" class="bp-screen-reader-text"><?php _e( 'No, do not send email', 'marylink-custom-plugin' ); ?></label></td>
			</tr>

    	<?php 


    }


    //add_filter('bp_notifications_get_notifications_for_user', 'test_noti');
   // add_action('wp_footer', 'test_noti_footer');

    function test_noti_footer() {

        if (function_exists('bp_notifications_get_all_notifications_for_user')) {
      
          var_dump(bp_notifications_get_all_notifications_for_user(bp_displayed_user_id()));

        }
        return $notifications;

    }


    add_action('wp_footer', 'add_custom_author_directory');

    function add_custom_author_directory() {

        if (empty($_GET['author_link']) || empty($_GET['postID_class']))
            return 0;

        _e("<div id='author_link' data-author_link='".$_GET['author_link']."'></div>");
        _e("<div id='postID_class' data-postID_class='".$_GET['postID_class']."'></div>");

        return 1;

    }



 ?>