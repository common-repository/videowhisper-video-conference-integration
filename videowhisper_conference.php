<?php
/*
Plugin Name: Webcam Video Conference
Plugin URI: https://videowhisper.com/?p=WordPress+Video+Conference
Description: <strong>Webcam Video Conference</strong> implements video conferencing rooms for site users. <a href='https://videowhisper.com/?p=Requirements'>Hosting Requirements</a> | <a href='https://videowhisper.com/?p=RTMP+Hosting#compare'>Hosting</a> | <a href='https://videowhisper.com/tickets_submit.php'>Support</a>
Version: 5.25.4
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
*/

if (!class_exists("VWvideoConference"))
{

	class VWvideoConference
	{

		function VWvideoConference()
			{ //constructor
		}

		static function install() {
			// do not generate any output here

			VWvideoConference::conference_post();
			flush_rewrite_rules();
		}

		// Register Custom Post Type
		function conference_post() {

			$options = get_option('VWvideoConferenceOptions');

			//only if missing
			if (post_type_exists($options['custom_post'])) return;

			$labels = array(
				'name'                => _x( 'Conferences', 'Post Type General Name', 'text_domain' ),
				'singular_name'       => _x( 'Conference', 'Post Type Singular Name', 'text_domain' ),
				'menu_name'           => __( 'Conferences', 'text_domain' ),
				'parent_item_colon'   => __( 'Parent Conference:', 'text_domain' ),
				'all_items'           => __( 'All Conferences', 'text_domain' ),
				'view_item'           => __( 'View Conference', 'text_domain' ),
				'add_new_item'        => __( 'Add New Conference', 'text_domain' ),
				'add_new'             => __( 'New Conference', 'text_domain' ),
				'edit_item'           => __( 'Edit Conference', 'text_domain' ),
				'update_item'         => __( 'Update Conference', 'text_domain' ),
				'search_items'        => __( 'Search Conferences', 'text_domain' ),
				'not_found'           => __( 'No Conferences found', 'text_domain' ),
				'not_found_in_trash'  => __( 'No Conferences found in Trash', 'text_domain' ),
			);
			$args = array(
				'label'               => __( 'conference', 'text_domain' ),
				'description'         => __( 'Video Conferences', 'text_domain' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', ),
				'taxonomies'          => array( 'category', 'post_tag' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'menu_icon' => 'dashicons-groups',
				'capability_type'     => 'post',
				'capabilities' => array(
					'create_posts' => 'do_not_allow', // false < WP 4.5
				),
				'map_meta_cap' => false, // Set to `false`, if users are not allowed to edit/delete existing posts
			);
			register_post_type( $options['custom_post'], $args );

		}

		function settings_link($links) {
			$settings_link = '<a href="options-general.php?page=videowhisper_conference.php">'.__("Settings").'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		function init()
		{
			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin",  array('VWvideoConference','settings_link') );

			wp_register_sidebar_widget('videoConferenceWidget','VideoWhisper Conference', array('VWvideoConference', 'widget') );


			//shortcodes
			add_shortcode('videowhisper_conference_manage',array( 'VWvideoConference', 'shortcode_manage'));
			add_shortcode('videowhisper_conference',array( 'VWvideoConference', 'shortcode_conference'));


			//update page if not exists or deleted
			$page_id = get_option("vw_vc_page_manage");
			$page_id2 = get_option("vw_vc_page_landing");

			if (!$page_id || $page_id == "-1" || !$page_id2 || $page_id2 == "-1")
				add_action('wp_loaded', array('VWvideoConference','updatePages'));

			//check db
			$vw_dbvc_version = "2.1";

			global $wpdb;
			$table_name = $wpdb->prefix . "vw_vcsessions";
			$table_name3 = $wpdb->prefix . "vw_vcrooms";


			$installed_ver = get_option( "vw_dbvc_version" );

			if( $installed_ver != $vw_dbvc_version )
			{
				$wpdb->flush();

				$sql = "DROP TABLE IF EXISTS `$table_name`;
		CREATE TABLE `$table_name` (
		  `id` int(11) NOT NULL auto_increment,
		  `session` varchar(64) NOT NULL,
		  `username` varchar(64) NOT NULL,
		  `room` varchar(64) NOT NULL,
		  `message` text NOT NULL,
		  `sdate` int(11) NOT NULL,
		  `edate` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `status` (`status`),
		  KEY `type` (`type`),
		  KEY `room` (`room`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Video Whisper: Sessions - 2009@videowhisper.com' AUTO_INCREMENT=1 ;

		DROP TABLE IF EXISTS `$table_name3`;
		CREATE TABLE `$table_name3` (
		  `id` int(11) NOT NULL auto_increment,
		  `name` varchar(64) NOT NULL,
		  `owner` int(11) NOT NULL,
		  `sdate` int(11) NOT NULL,
		  `edate` int(11) NOT NULL,
		  `capacity` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `name` (`name`),
		  KEY `status` (`status`),
		  KEY `type` (`type`),
		  KEY `owner` (`owner`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Video Whisper: Rooms - 2014@videowhisper.com' AUTO_INCREMENT=1 ;

		INSERT INTO `$table_name3` ( `name`, `owner`, `sdate`, `edate`, `capacity`, `status`, `type`) VALUES ( 'Lobby', '1', NOW(), NOW(), '100' ,'1', '1');
		";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);

				if (!$installed_ver) add_option("vw_dbvc_version", $vw_dbvc_version);
				else update_option( "vw_dbvc_version", $vw_dbvc_version );

				$wpdb->flush();
			}

		}




		function updatePages()
		{


			$options = get_option('VWvideoConferenceOptions');

			//if not disabled create
			if ($options['disablePage']=='0')
			{
				global $user_ID;
				$page = array();
				$page['post_type']    = 'page';
				$page['post_content'] = '[videowhisper_conference_manage]';
				$page['post_parent']  = 0;
				$page['post_author']  = $user_ID;
				$page['post_status']  = 'publish';
				$page['comment_status'] ='closed';
				$page['post_title']   = 'Setup Conference';

				$page_id = get_option("vw_vc_page_manage");
				if ($page_id>0) $page['ID'] = $page_id;

				$pageid = wp_insert_post ($page);
				update_option( "vw_vc_page_manage", $pageid);
			}

			if ($options['disablePageC']=='0')
			{
				global $user_ID;
				$page = array();
				$page['post_type']    = 'page';
				$page['post_content'] = '[videowhisper_conference]';
				$page['post_parent']  = 0;
				$page['post_author']  = $user_ID;
				$page['post_status']  = 'publish';
				$page['comment_status'] ='closed';
				$page['post_title']   = 'Video Conference';

				$page_id = get_option("vw_vc_page_landing");
				if ($page_id>0) $page['ID'] = $page_id;

				$pageid = wp_insert_post ($page);
				update_option( "vw_vc_page_landing", $pageid);
			}

		}

		function deletePages()
		{
			$options = get_option('VWvideoConferenceOptions');

			if ($options['disablePage'])
			{
				$page_id = get_option("vw_vc_page_manage");
				if ($page_id > 0)
				{
					wp_delete_post($page_id);
					update_option( "vw_vc_page_manage", -1);
				}
			}

			if ($options['disablePageC'])
			{
				$page_id = get_option("vw_vc_page_landing");
				if ($page_id > 0)
				{
					wp_delete_post($page_id);
					update_option( "vw_vc_page_landing", -1);
				}
			}

		}


		//if any key matches any listing
		function inList($keys, $data)
		{
			if (!$keys) return 0;

			$list=explode(",", strtolower(trim($data)));

			foreach ($keys as $key)
				foreach ($list as $listing)
					if ( strtolower(trim($key)) == trim($listing) ) return 1;

					return 0;
		}

		function getCurrentURL()
		{
			
		
			
			$currentURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
			$currentURL .= $_SERVER['HTTP_HOST'];

/*
			if($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443")
			$currentURL .= ":".$_SERVER["SERVER_PORT"];
*/
			$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

			$currentURL .= $uri_parts[0];
			return $currentURL;
		}

		function roomURL($room)
		{
			$options = get_option('VWvideoConferenceOptions');

			if ($options['accessLink']=='site')
			{
				$page_id = get_option("vw_vc_page_landing");
				if ($page_id>0)
				{
					$permalink = get_permalink($page_id);
					if ($permalink)
						return add_query_arg(array('r'=>sanitize_file_name($room)),$permalink);
				}

			}

			//else just load full page
			return plugin_dir_url(__FILE__) ."vc/?r=" . urlencode(sanitize_file_name($room));
		}

		function path2url($file, $Protocol='http://') {
			return $Protocol.$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
		}

		function shortcode_conference($atts)
		{

			$roomname = sanitize_file_name($_GET['r']);
			if ($atts) if ($atts['room']) $roomname = sanitize_file_name($atts['room']);

				$baseurl = plugin_dir_url(__FILE__) .'vc/';

			$swfurl = $baseurl . "videowhisper_conference.swf?ssl=1&room=" . $roomname;
			$bgcolor="#051e43";

			$pagecode=<<<ENDCODE
	<div id="videoconference_container" style="height:650px">
	<object width="100%" height="100%">
	<param name="movie" value="$swfurl" /><param name="bgcolor" value="$bgcolor" /><param name="salign" value="lt" /><param name="scale" value="noscale" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /> <param name="base" value="$baseurl" /> <embed width="100%" height="100%" scale="noscale" salign="lt" src="$swfurl" bgcolor="$bgcolor" base="$baseurl" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed>
	</object>
	<noscript>
	<p align=center><a href="https://videowhisper.com/?p=Video+Conference"><strong>VideoWhisper Video Conference Software</strong></a></p>
	<p align="center"><strong>This content requires the Adobe Flash Player:
	<a href="https://www.macromedia.com/go/getflash/">Get Flash</a></strong>!</p>
	</noscript>
	</div>
	<p><a href="$baseurl?r=$roomname">Click here to video conference is a full page!</a></p>
ENDCODE;

			$pagecode .= <<<HTMLCODE

<div id="flashWarning"></div>

<script>
var hasFlash = ((typeof navigator.plugins != "undefined" && typeof navigator.plugins["Shockwave Flash"] == "object") || (window.ActiveXObject && (new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) != false));

var flashWarn = '<small>Using the Flash web based interface requires <a rel="nofollow" target="_flash" href="https://get.adobe.com/flashplayer/">latest Flash plugin</a> and <a rel="nofollow" target="_flash" href="https://helpx.adobe.com/flash-player.html">activating plugin in your browser</a>. Flash apps are recommended on PC for best latency and most advanced features.</small>'

if (!hasFlash) document.getElementById("flashWarning").innerHTML = flashWarn;</script>
HTMLCODE;

			return $pagecode;
		}

		static function enqueueUI()
		{
			//semantic ui
			wp_enqueue_script( 'jquery' );
			wp_enqueue_style( 'semantic', 'https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css');
			wp_enqueue_script( 'semantic','https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js', array('jquery'));

		}
		
		function shortcode_manage()
		{

			//can user create room?
			$options = get_option('VWvideoConferenceOptions');


			$canBroadcast = $options['canBroadcast'];
			$broadcastList = $options['broadcastList'];
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';


			self::enqueueUI();

			$loggedin=0;

			global $current_user;
			get_currentuserinfo();
			if ($current_user->$userName) $username = $current_user->$userName;

			//access keys
			$userkeys = $current_user->roles;
			$userkeys[] = $current_user->user_login;
			$userkeys[] = $current_user->ID;
			$userkeys[] = $current_user->user_email;
			$userkeys[] = $current_user->display_name;

			switch ($canBroadcast)
			{
			case "members":
				if ($current_user->ID>0) $loggedin=1;
				else $htmlCode .= "<a href=\"/\">Please login first or register an account if you don't have one!</a>";
				break;
			case "list";
				if ($username)
					if (VWvideoConference::inList($userkeys, $broadcastList)) $loggedin=1;
					else $htmlCode .= "<a href=\"/\">$username, you are not allowed to setup rooms.</a>";
					else $htmlCode .= "<a href=\"/\">Please login first or register an account if you don't have one!</a>";
					break;
			}

			if (!$loggedin)
			{
				$htmlCode .='<p>This pages allows creating and managing conferencing rooms for register members that have this feature enabled.</p>' . $canBroadcast;
				return $htmlCode;
			}

			$this_page    =   VWvideoConference::getCurrentURL();
		
			
			if ($loggedin)
			{
				global $wpdb;
				$table_name = $wpdb->prefix . "vw_vcsessions";
				$table_name3 = $wpdb->prefix . "vw_vcrooms";

				$wpdb->flush();
				$rmn = $wpdb->get_row("SELECT count(id) as no FROM $table_name3 where owner='".$current_user->ID."'");

				//delete
				if ($delid=(int) $_GET['delete'])
				{

					if ($room = $_GET['room'])
					{
					$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $room. "' and post_type='" . $options['custom_post'] . "' AND post_author='".$current_user->ID."' LIMIT 0,1" );
					wp_delete_post($postID, true);
					}

					$sql = $wpdb->prepare("DELETE FROM $table_name3 where owner='".$current_user->ID."' AND id='%d'", array($delid));
					$wpdb->query($sql);
					$wpdb->flush();


					$htmlCode .=  "<div class='update'>Room #$delid was deleted.</div>";

					$rmn = $wpdb->get_row("SELECT count(id) as no FROM $table_name3 where owner='".$current_user->ID."'");

					$this_page = remove_query_arg('delete', $this_page);
					$this_page = remove_query_arg('room', $this_page);

				}

				//create
				$room = sanitize_file_name($_POST['room']);
				if ($room)
				{
					$ztime=time();

					$sql = $wpdb->prepare("SELECT owner FROM $table_name3 where name='%s'", array($room));
					$rdata = $wpdb->get_row($sql);
					if (!$rdata)
					{
						if ($rmn->no < $options['maxRooms'])
						{
							$capacity = (int) $_POST['capacity'];
							if ($capacity > $options['capacityMax']) $capacity = $options['capacityMax'];

							$type = (int) $_POST['type'];

							$sql=$wpdb->prepare("INSERT INTO `$table_name3` ( `name`, `owner`, `sdate`, `edate`, `capacity`, `status`, `type`) VALUES ('%s', '".$current_user->ID."', '$ztime', '0', '%d', 1, '%d')", array($room, $capacity, $type));
							$wpdb->query($sql);
							$wpdb->flush();

					//create conference
					$post = array(
						'post_content'   => sanitize_text_field($_POST['description']),
						'post_name'      => $room,
						'post_title'     => $room,
						'post_author'    => $current_user->ID,
						'post_type'      => $options['custom_post'],
						'post_status'    => 'publish',
					);

					$postID = wp_insert_post($post);

					foreach (array('access','chat','write','list','private') as $field)
						if ($value = sanitize_text_field($_POST[$field])) update_post_meta($postID, 'vw_'.$field, $value);

							$htmlCode .=  "<div class='update'>Room '$room' was created (Post #$postID).</div>";

							$rmn = $wpdb->get_row("SELECT count(id) as no FROM $table_name3 where owner='".$current_user->ID."'");

						}else $htmlCode .=  "<div class='error'>Room limit reached!</div>";
					}
					else
					{
						$htmlCode .=  "<div class='error'>Room name '$room' is already in use. Please choose another name!</div>";
						$room="";
					}
				}

				//edit?
				$editRoom = (int) $_GET['editRoom'];

				if ($editRoom)
				{
					if ($editRoom == '-1')
						if ($rmn->no < $options['maxRooms'])
							$htmlCode .=  '<h3>Setup a New Room</h3><form class="ui form" method="post" action="' . remove_query_arg('editRoom', $this_page) .'"  name="adminForm">
		<table>
		<tr><td>Name</td>
		<td><input name="room" type="text" id="room" value="Room_'.base_convert((time()-1225000000),10,36).'" size="20" maxlength="64" /></td>
		</tr>
			<tr><td>Publicity</td>
			<td><select id="type" name="type">
		  <option value="2">Private</option>
		  <option value="1">Public</option>
		  </select></td>
		  </tr>
		 		<tr><td>Capacity</td><td>
		 		<input name="capacity" type="text" id="capacity" value="' . $options['capacityDefault'] . '" size="5" maxlength="8" /> Max: ' .$options['capacityMax']. '</td>
		 		</tr>

		 		<tr><td>Access</td><td>
		 		<textarea name="access" type="text" id="access" rows="2" columns="50">All</textarea>
		 		User list: can access room directly or from application rooms list.</td>
		 		</tr>

		 		<tr><td>Group Chat</td><td>
		 		<textarea name="chat" type="text" id="chat" rows="2" columns="50">All</textarea>
		 		User list: can see room chat.</td>
		 		</tr>

		 		<tr><td>Write In Chat</td><td>
		 		<textarea name="write" type="text" id="write" rows="2" columns="50">All</textarea>
		 		User list: can write in room chat.</td>
		 		</tr>

		 		<tr><td>Participants List</td><td>
		 		<textarea name="list" type="text" id="list" rows="2" columns="50">All</textarea>
		 		User list: can see room participants list.</td>
		 		</tr>

		 		<tr><td>Private Chat</td><td>
		 		<textarea name="private" type="text" id="private" rows="2" columns="50">All</textarea>
		 		User list: can initiate private chates with other users from list.</td>
		 		</tr>

		 		 <tr><td>Description</td><td>
		 		<textarea name="description" type="text" id="description" rows="3" columns="50"></textarea>
		 		Room description (shows when user enters room).</td>
		 		</tr>
		</table>
		  <br><input class="ui button" type="submit" name="button" id="button" value="Create" />
		  <BR>All your rooms will be accessible for you in conference room list. Public rooms will be listed for everybody that can access.
		  <BR>Define user lists as comma separated lists of usernames, account emails or roles. "All" will include all users and "None" will disable feature.
		  <BR>Conference features are setup on user access (when conference application loads) so rooms with restrictions should be private to prevent indirect access from other rooms with more features. Indirect access (by listing room in application room list) can be controlled with Publicity and Access settings.
		</form>
		'; else $htmlCode .= "You can't setup new rooms because you reached room limit (".$options['maxRooms'].").";
				}
				else
				{

					//list
					$wpdb->flush();

					$sql = "SELECT * FROM $table_name3 where owner='".$current_user->ID."'";
					$rooms=$wpdb->get_results($sql);

					$htmlCode .=  "<H3 class='ui header'>My Rooms (" . $rmn->no . '/' . $options['maxRooms'].")</H3>";


					if ($rmn->no < $options['maxRooms'])
						$htmlCode .= '<a href="'. add_query_arg( 'editRoom', -1, $this_page).'" class="ui button g-btn type_yellow button">Setup New Room</a>';

					if (count($rooms))
					{ 
						$htmlCode .=  "<table class='ui table striped selectable'>";
						$htmlCode .=  "<tr class='ui header'><th>Room</th><th>Link (use to invite)</th><th>Online</th><th>Capacity</th><th>Type</th><th>Manage</th></tr>";
						$root_url = plugins_url() . "/";
						foreach ($rooms as $rd)
						{

							$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $rd->name. "' and post_type='" . $options['custom_post'] . "' AND post_author='".$current_user->ID."' LIMIT 0,1" );

							$rm=$wpdb->get_row("SELECT count(*) as no, group_concat(username separator ' <BR> ') as users, room as room FROM `$table_name` where status='1' and type='1' AND room='".$rd->name."' GROUP BY room");

							$htmlCode .=  "<tr><td><a href='" . VWvideoConference::roomURL($rd->name)."'> <i class='users icon'></i> <B>".$rd->name."</B></a></td> <td>" . VWvideoConference::roomURL($rd->name) ."</td> <td>".($rm->no>0?$rm->users:'0')."</td> <td>" . ($rd->capacity?$rd->capacity:'Default') . "</td> <td>".($rd->type==1?'Public':($rd->type==2?"Private":$rd->type))."</td> <td><a class='button btn but' href='".$this_page.(strstr($this_page,'?')?'&':'?')."delete=".$rd->id."&room=".urlencode($rd->name)."'>Delete</a></td> </tr>";

							if ($postID)
							{
							$htmlCode .=  '<tr><td>' .$rd->name. '</td><td colspan="5">';

							foreach (array('access','chat','write','list','private') as $field)
							if ($value = get_post_meta($postID, 'vw_'.$field, true))
							$htmlCode .= '<br>' . ucwords($field) . ': ' . $value;

							$htmlCode .=  '</td></tr>';
							}						}
						$htmlCode .=  "</table>";

					}
					else $htmlCode .=  "You don't currently have any rooms.";
				}



			}

			return $htmlCode;
		}

		function widgetContent()
		{

			$options = get_option('VWvideoConferenceOptions');

			global $wpdb;
			$table_name = $wpdb->prefix . "vw_vcsessions";
			$table_name3 = $wpdb->prefix . "vw_vcrooms";


			/*
				$root_url = get_bloginfo( "url" ) . "/";

			$page_id = get_option("vw_vc_page_landing");
			if ($page_id > 0) $permalink = get_permalink( $page_id );
			else $permalink = $root_url . "wp-content/plugins/videowhisper-video-conference-integration/vc/?";
			*/


			//clean expired users
			//do not clean more often than 25s (mysql table invalidate)
			$lastClean = 0; $cleanNow = false;
			$lastCleanFile = $options['uploadsPath'] . 'lastclean.txt';

			if (file_exists($lastCleanFile)) $lastClean = file_get_contents($lastCleanFile);
			if (!$lastClean) $cleanNow = true;
			else if ($ztime - $lastClean > 25) $cleanNow = true;

				if ($cleanNow)
				{
					if (!$options['onlineExpiration']) $options['onlineExpiration'] = 310;
					$exptime=$ztime-$options['onlineExpiration'];
					$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
					$wpdb->query($sql);
					file_put_contents($lastCleanFile, $ztime);
				}

			$wpdb->flush();

			$items =  $wpdb->get_results("SELECT o.room AS room, count(*) AS users FROM `$table_name` AS o, `$table_name3` AS r WHERE o.room=r.name AND o.status='1' AND r.type='1' GROUP BY room ORDER BY users DESC");

			echo "<ul>";
			if ($items) foreach ($items as $item) echo "<li><a href='" . VWvideoConference::roomURL($item->room) . "'><B>" . $item->room . "</B></a> (" . $item->users .")</a></li>";
				else echo "<li>No active conference rooms.</li>";
				echo "</ul>";

			//landing rom
			if ($options['landingRoom']=='username')
				//can create
				{
				global $current_user;
				get_currentuserinfo();

				//username
				if ($userName) if ($current_user->$userName) $username=urlencode($current_user->$userName);
					//var_dump($current_user->$userName);
					$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

				$room=$username;
				$admin=1;
			}
			else $room = $options['lobbyRoom']; //or go to default
			$permalink = VWvideoConference::roomURL($room);

			?><a href="<?php echo $permalink; ?>"><img src="<?php echo plugins_url(); ?>/videowhisper-video-conference-integration/vc/templates/conference/i_webcam.png" align="absmiddle" border="0">Enter Conference</a>
	<?php
			$state = 'block' ;
			if (!$options['videowhisper']) $state = 'none';
			echo '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Powered by VideoWhisper <a href="https://videowhisper.com/?p=WordPress+Video+Conference">Video Conference Software</a>.</p></div>';
		}

		function widget($args)
		{
			extract($args);
			echo $before_widget;
			echo $before_title;?>Video Conference<?php echo $after_title;
			VWvideoConference::widgetContent();
			echo $after_widget;
		}

		function menu() {
			add_options_page('Video Conference Options', 'Video Conference', 9, basename(__FILE__), array('VWvideoConference', 'options'));
		}

		function adminOptionsDefault()
{
	return array(
				'disablePage' => '0',
				'disablePageC' => '0',
				'custom_post' => 'conference',

				'userName' => 'display_name',
				'rtmp_server' => 'rtmp://localhost/videowhisper',
				'rtmp_amf' => 'AMF3',
				'canAccess' => 'all',
				'accessList' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber',

				'canBroadcast' => 'members',
				'broadcastList' => 'Super Admin, Administrator, Editor, Author',

				'maxRooms' => '3',
				'capacityDefault' => '50',
				'capacityMax' => '1000',

				'accessLink' => 'site',
				'anyRoom' => '1',

				'uploadsPath' => plugin_dir_path(__FILE__) . 'vc/uploads/',

				'avatar' => 'snapshot',
				'landingRoom' => 'lobby',
				'lobbyRoom' => 'Lobby',

				'welcome' => 'Welcome to video conference room! <BR><font color="#3CA2DE">&#187;</font> Click top left preview panel for more options including selecting different camera and microphone. <BR><font color="#3CA2DE">&#187;</font> Click any participant from users list for more options including extra video panels. <BR><font color="#3CA2DE">&#187;</font> Try pasting urls, youtube movie urls, picture urls, emails, twitter accounts as @videowhisper in your text chat. <BR><font color="#3CA2DE">&#187;</font> Download daily chat logs from file list.',
				'layoutCode' => '',
				'onlineExpiration' =>'310',
				'parameters' => '&generateSnapshots=1&pushToTalk=1&publicVideosN=0&publicVideosW=225&publicVideosH=217&publicVideosX=2&publicVideosY=560&publicVideosColumns=4&publicVideosRows=0&avatarList=1&infoMenu=0&bufferLive=0&bufferFull=0&bufferLivePlayback=0.2&bufferFullPlayback=0&showCamSettings=1&advancedCamSettings=1&configureSource=0&disableVideo=0&disableSound=0&background_url=&autoViewCams=1&tutorial=0&file_upload=1&file_delete=1&panelFiles=1&showTimer=1&showCredit=1&disconnectOnTimeout=0&floodProtection=3&regularWatch=1&newWatch=1&ws_ads=ads.php&adsTimeout=15000&adsInterval=0&statusInterval=300000&verboseLevel=2&selectCam=1&selectMic=1&autoMuteCams=1',

				'translationCode' => '<t text="Sound Disabled" translation="Sound Disabled"/>
<t text="Watch as Video 3" translation="Watch as Video 3"/>
<t text="Full Screen" translation="Full Screen"/>
<t text="P2P group subscribers" translation="P2P group subscribers"/>
<t text="Change Volume" translation="Change Volume"/>
<t text="Select Microphone Device" translation="Select Microphone Device"/>
<t text="Apply Settings" translation="Apply Settings"/>
<t text="Toggle Webcam" translation="Toggle Webcam"/>
<t text="Select Webcam Device" translation="Select Webcam Device"/>
<t text="Toggle Microphone" translation="Toggle Microphone"/>
<t text="no" translation="no"/>
<t text="Toggle External Encoder" translation="Toggle External Encoder"/>
<t text="Toggle Preview Compression" translation="Toggle Preview Compression"/>
<t text="Available" translation="Available"/>
<t text="Username" translation="Username"/>
<t text="Upload Files" translation="Upload Files"/>
<t text="Upload" translation="Upload"/>
<t text="Open" translation="Open"/>
<t text="Send" translation="Send"/>
<t text="Away" translation="Away"/>
<t text="Bold" translation="Bold"/>
<t text="Italic" translation="Italic"/>
<t text="Busy" translation="Busy"/>
<t text="Underline" translation="Underline"/>
<t text="Delete" translation="Delete"/>
<t text="Pause Broadcast" translation="Pause Broadcast"/>
<t text="Sound Effects" translation="Sound Effects"/>
<t text="Set Speaker" translation="Set Speaker"/>
<t text="Sound Fx" translation="Sound Fx"/>
<t text="Tune Streaming Bandwidth" translation="Tune Streaming Bandwidth"/>
<t text="Emoticons" translation="Emoticons"/>
<t text="Set Inquirer" translation="Set Inquirer"/>
<t text="Push to Talk" translation="Push to Talk"/>
<t text="Quality" translation="Quality"/>
<t text="High" translation="High"/>
<t text="Rooms" translation="Rooms"/>
<t text="Kick" translation="Kick"/>
<t text="SD" translation="SD"/>
<t text="DVD NTSC" translation="DVD NTSC"/>
<t text="Loaded application version: " translation="Loaded application version: "/>
<t text="Block" translation="Block"/>
<t text="Framerate" translation="Framerate"/>
<t text="Rate" translation="Rate"/>
<t text="DVD PAL" translation="DVD PAL"/>
<t text="You are viewing room" translation="You are viewing room"/>
',
				'camResolution' => '240x180',
				'camFPS' => '30',

				'camBandwidth' => '48000',
				'camMaxBandwidth' => '128000',

				'videoCodec'=>'H263',
				'codecProfile' => 'main',
				'codecLevel' => '3.1',

				'soundCodec'=> 'Speex',
				'soundQuality' => '9',
				'micRate' => '22',

				'serverRTMFP' => '-not-needed-or-recommended-', //rtmfp://stratus.adobe.com/f1533cc06e4de4b56399b10d-1a624022ff71/

				'p2pGroup' => 'VideoWhisper',
				'supportRTMP' => '1',
				'supportP2P' => '0',
				'alwaysRTMP' => '1',
				'alwaysP2P' => '0',
				'disableBandwidthDetection' => '0',
				'videowhisper' => 0
			);

}
		function getAdminOptions() //also updates
			{
			$adminOptions = VWvideoConference::adminOptionsDefault();

			$options = get_option('VWvideoConferenceOptions');
			if (!empty($options)) {
				foreach ($options as $key => $option)
					$adminOptions[$key] = $option;
			}


			update_option('VWvideoConferenceOptions', $adminOptions);
			return $adminOptions;
		}

		//production: use $options = get_option('VWvideoConferenceOptions');

		function options()
		{

			//save form
			$options = VWvideoConference::getAdminOptions();
			$optionsDefault = VWvideoConference::adminOptionsDefault();

			if (isset($_POST))
			{

				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = $_POST[$key];

					update_option('VWvideoConferenceOptions', $options);
			}

			$page_id = get_option("vw_vc_page_manage");
			if ($page_id != '-1' && $options['disablePage']!='0') VWvideoConference::deletePages();

			$page_idC = get_option("vw_vc_page_landing");
			if ($page_idC != '-1' && $options['disablePageC']!='0') VWvideoConference::deletePages();

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'support';


?>
<div class="wrap">
<?php screen_icon(); ?>
<h2>VideoWhisper Video Conference Settings</h2>


<h2 class="nav-tab-wrapper">
	<a href="options-general.php?page=videowhisper_conference.php&tab=server" class="nav-tab <?php echo $active_tab=='server'?'nav-tab-active':'';?>">Server</a>
	<a href="options-general.php?page=videowhisper_conference.php&tab=setup" class="nav-tab <?php echo $active_tab=='setup'?'nav-tab-active':'';?>">Room Setup</a>
	<a href="options-general.php?page=videowhisper_conference.php&tab=access" class="nav-tab <?php echo $active_tab=='access'?'nav-tab-active':'';?>">Access</a>
    <a href="options-general.php?page=videowhisper_conference.php&tab=video" class="nav-tab <?php echo $active_tab=='video'?'nav-tab-active':'';?>">Video</a>
	<a href="options-general.php?page=videowhisper_conference.php&tab=integration" class="nav-tab <?php echo $active_tab=='integration'?'nav-tab-active':'';?>">Integration</a>
    <a href="options-general.php?page=videowhisper_conference.php&tab=support" class="nav-tab <?php echo $active_tab=='support'?'nav-tab-active':'';?>">Support</a>
</h2>

<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

<?php
			switch ($active_tab)
			{
			case 'server':
?>

<h3>Server Settings</h3>
<h4>RTMP Address</h4>
<p>To run this, make sure your hosting environment meets all <a href="https://videowhisper.com/?p=Requirements" target="_blank">requirements</a>.  If you don't have a videowhisper rtmp address yet (from a managed rtmp host), go to <a href="https://videowhisper.com/?p=RTMP+Applications" target="_blank">RTMP Application Setup</a> for  installation details.</p>
<input name="rtmp_server" type="text" id="rtmp_server" size="64" maxlength="256" value="<?php echo $options['rtmp_server']?>"/>
<br>Don't have a RTMP streaming host? <a href="http://hostrtmp.com/compare/">Compare RTMP Hosting Options</a>: Hosting options starting from $9/month.


<h4>Disable Bandwidth Detection</h4>
<p>Required on some rtmp servers that don't support bandwidth detection and return a Connection.Call.Fail error.</p>
<select name="disableBandwidthDetection" id="disableBandwidthDetection">
  <option value="0" <?php echo $options['disableBandwidthDetection']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['disableBandwidthDetection']?"selected":""?>>Yes</option>
</select>

<h4>RTMFP Address</h4>
<p> Get your own independent RTMFP address by registering for a free <a href="https://www.adobe.com/cfusion/entitlement/index.cfm?e=cirrus" target="_blank">Adobe Cirrus developer key</a>. This is required for P2P support.</p>
<input name="serverRTMFP" type="text" id="serverRTMFP" size="80" maxlength="256" value="<?php echo $options['serverRTMFP']?>"/>
<br>Not needed or recommended.

<h4>P2P Group</h4>
<input name="p2pGroup" type="text" id="p2pGroup" size="32" maxlength="64" value="<?php echo $options['p2pGroup']?>"/>

<h4>Support RTMP Streaming</h4>
<select name="supportRTMP" id="supportRTMP">
  <option value="0" <?php echo $options['supportRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportRTMP']?"selected":""?>>Yes</option>
</select>

<h4>Always do RTMP Streaming</h4>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not (in example if you're using server side video archiving and need all streams published for recording).</p>
<select name="alwaysRTMP" id="alwaysRTMP">
  <option value="0" <?php echo $options['alwaysRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysRTMP']?"selected":""?>>Yes</option>
</select>

<h4>Support P2P Streaming</h4>
<select name="supportP2P" id="supportP2P">
  <option value="0" <?php echo $options['supportP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportP2P']?"selected":""?>>Yes</option>
</select>
<br>Not recommended as P2P is highly dependant on client network and ISP restrictions. Often results in video streaming failure or huge latency.
P2P may be suitable when all clients are in same network or broadcasters have server grade connection (with high upload and dedicated public IP accessible externally).

<h4>Always do P2P Streaming</h4>
<select name="alwaysP2P" id="alwaysP2P">
  <option value="0" <?php echo $options['alwaysP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysP2P']?"selected":""?>>Yes</option>
</select>

<h4>Uploads Path</h4>
<p>Path where logs and snapshots will be uploaded.</p>
<input name="uploadsPath" type="text" id="uploadsPath" size="80" maxlength="256" value="<?php echo $options['uploadsPath']?>"/>
<br>Not fully implemented. Leave as default.

<?php

				break;

			case 'setup':
?>
<h3>Room Setup</h3>
<h5>Who can create rooms</h5>
<select name="canBroadcast" id="canBroadcast">
  <option value="members" <?php echo $options['canBroadcast']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canBroadcast']=='list'?"selected":""?>>Members in List *</option>
</select>

<h5>* Members in List: allowed to setup rooms (comma separated user names, roles, emails, IDs)</h5>
<textarea name="broadcastList" cols="64" rows="3" id="broadcastList"><?php echo $options['broadcastList']?>
</textarea>

<h4>Room limit</h4>
<input name="maxRooms" type="text" id="maxRooms" size="3" maxlength="3" value="<?php echo $options['maxRooms']?>"/>
<br>Maximum number of rooms each user can have.

<h4>Room Capacity (Default)</h4>
<input name="capacityDefault" type="text" id="capacityDefault" size="5" maxlength="8" value="<?php echo $options['capacityDefault']?>"/>
<br>Default room capacity.

<h4>Maximum Room Capacity</h4>
<input name="capacityMax" type="text" id="capacityMax" size="5" maxlength="8" value="<?php echo $options['capacityMax']?>"/>
<br>Maximum room capacity.

<h4>Page for Management</h4>
<p>Add room management page (Page ID <a href='post.php?post=<?php echo get_option("vw_vc_page_manage"); ?>&action=edit'><?php echo get_option("vw_vc_page_manage"); ?></a>) with shortcode [videowhisper_conference_manage]</p>
<select name="disablePage" id="disablePage">
  <option value="0" <?php echo $options['disablePage']=='0'?"selected":""?>>Yes</option>
  <option value="1" <?php echo $options['disablePage']=='1'?"selected":""?>>No</option>
</select>
<?php
				break;

			case 'access':
?>
<h3>Room Access</h3>
<h4>Who can access video conference</h4>
<select name="canAccess" id="canAccess">
  <option value="all" <?php echo $options['canAccess']=='all'?"selected":""?>>Anybody</option>
  <option value="members" <?php echo $options['canAccess']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canAccess']=='list'?"selected":""?>>Members in List</option>
</select>

<h4>Members allowed to access video conference</h4>
<textarea name="accessList" cols="64" rows="3" id="accessList"><?php echo $options['accessList']?>
</textarea>
<br>Roles, usernames, user IDs, user emails.


<h4>Page for Conference</h4>
<p>Add landing conference page (Page ID <a href='post.php?post=<?php echo get_option("vw_vc_page_landing"); ?>&action=edit'><?php echo get_option("vw_vc_page_landing"); ?></a>) with shortcode [videowhisper_conference]</p>
<select name="disablePageC" id="disablePageC">
  <option value="0" <?php echo $options['disablePageC']=='0'?"selected":""?>>Yes</option>
  <option value="1" <?php echo $options['disablePageC']=='1'?"selected":""?>>No</option>
</select>

<h4>Access Link</h4>
<select name="accessLink" id="accessLink">
  <option value="site" <?php echo $options['accessLink']=='site'?"selected":""?>>Site Page</option>
  <option value="full" <?php echo $options['accessLink']=='full'?"selected":""?>>Full Page</option>
</select>
<br>Full page will load conference room in a full page without site template (useful when template does not provide enough space to load room layout).

<h4>Default landing room</h4>
<select name="landingRoom" id="landingRoom">
  <option value="lobby" <?php echo $options['landingRoom']=='lobby'?"selected":""?>>Lobby</option>
  <option value="username" <?php echo $options['landingRoom']=='username'?"selected":""?>>Username</option>

</select>
<br>Username will allow registered users to start their own rooms, without room setup, as each user will land in room with own room name.

<h4>Lobby room name</h4>
<input name="lobbyRoom" type="text" id="lobbyRoom" size="16" maxlength="16" value="<?php echo $options['lobbyRoom']?>"/>
<br>Ex: Lobby

<h4>Allow Any Room</h4>
<select name="anyRoom" id="anyRoom">
  <option value="1" <?php echo $options['anyRoom']=='1'?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['anyRoom']=='0'?"selected":""?>>No</option>
</select>
<br>Any room name will be accessible if this is enabled (required by username rooms). Disable to allow accessing only previously setup rooms and landing room.

<h4>Room Shortcode</h4>
<h5>[videowhisper_conference room="room-name"]</h5>
This shortcode will display video conference room room-name. If room parameter is not provided it will use parameter 'r' from link. If that's also not available it will display default landing room as configured above.
<?php
				break;

			case 'integration':

				$options['welcome'] = htmlentities(stripslashes($options['welcome']));
				$options['layoutCode'] = htmlentities(stripslashes($options['layoutCode']));
				$options['parameters'] = htmlentities(stripslashes($options['parameters']));
				$options['translationCode'] = htmlentities(stripslashes($options['translationCode']));

?>


<h3>Integration Settings</h3>
<h4>Username</h4>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName']=='display_name'?"selected":""?>>Display Name</option>
  <option value="user_login" <?php echo $options['userName']=='user_login'?"selected":""?>>Login (Username)</option>
  <option value="user_nicename" <?php echo $options['userName']=='user_nicename'?"selected":""?>>Nicename</option>
</select>

<h4>Avatar</h4>
<select name="avatar" id="avatar">
  <option value="avatar" <?php echo $options['avatar']=='avatar'?"selected":""?>>WordPress Avatar</option>
  <option value="snapshot" <?php echo $options['avatar']=='snapshot'?"selected":""?>>Webcam Snapshot</option>
</select>
<BR>When using WP avatar, set generateSnapshots=0 in parameters to reduce web server load.

<h4>Welcome Message</h4>
<textarea name="welcome" id="welcome" cols="100" rows="8"><?php echo $options['welcome']?></textarea>
<br>Shows in chatbox when entering video conference.
Default:<br><textarea readonly cols="100" rows="3"><?php echo htmlentities($optionsDefault['welcome'])?></textarea>

<h4>Custom Layout Code</h4>
<textarea name="layoutCode" id="layoutCode" cols="100" rows="8"><?php echo $options['layoutCode']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in chat (contains panel positions, sizes, move and resize toggles). Copy and paste code here.
Default:<br><textarea readonly cols="100" rows="3"><?php echo htmlentities($optionsDefault['layoutCode'])?></textarea>

<h4>Translation Code</h4>
<textarea name="translationCode" id="translationCode" cols="100" rows="8"><?php echo $options['translationCode']?></textarea>
<br>Generate by writing and sending "/videowhisper translation" in chat (contains xml tags with text and translation attributes). Texts are added to list only after being shown once in interface. If any texts don't show up in generated list you can manually add new entries for these.
Default:<br><textarea readonly cols="100" rows="3"><?php echo htmlentities($optionsDefault['translationCode'])?></textarea>

<h4>Parameters</h4>
<textarea name="parameters" id="parameters" cols="100" rows="8"><?php echo $options['parameters']?></textarea>
<br>Documented on <a href="https://videowhisper.com/?p=php+video+conference#customize">PHP Video Conference</a> edition page.
Default:<br><textarea readonly cols="100" rows="3"><?php echo htmlentities($optionsDefault['parameters'])?></textarea>
<BR>Buffering: Recommended low latency buffering for chat: 0 (s) .
<BR>When using H.264-encoded video, any buffer setting greater than zero may introduce a latency of at least 2 to 3 seconds with video encoded at 30 fps, and even higher at lower frame rates. Although zero gives you the best possible latency, it might not give you the smoothest playback. So you may need to increase the buffer time to a value slightly greater than zero (such as .1 or .25) and use H.263 video codec.

<h4>Online Expiration</h4>
<p>How long to consider user online if no web status update occurs.</p>
<input name="onlineExpiration" type="text" id="onlineExpiration" size="5" maxlength="6" value="<?php echo $options['onlineExpiration']?>"/>s
<br>Should be 10s higher than maximum statusInterval (ms) configured in parameters. A higher statusInterval decreases web server load caused by status updates.



<h4>Show VideoWhisper Powered by</h4>
<select name="videowhisper" id="videowhisper">
  <option value="0" <?php echo $options['videowhisper']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['videowhisper']?"selected":""?>>Yes</option>
</select>
<?php
				break;

			case 'video':
?>
<h3>Video Settings</h3>
<h4>Default Webcam Resolution</h4>
<select name="camResolution" id="camResolution">
<?php
				foreach (array('160x120','240x180','320x240','480x360', '640x480', '720x480', '720x576', '1280x720', '1440x1080', '1920x1080') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['camResolution']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
<br>Recommended: 240x180 (for the default layout with small video panels)
 <br>Higher resolution will require <a target="_blank" href="https://videochat-scripts.com/recommended-h264-video-bitrate-based-on-resolution/">higher bandwidth</a> to avoid visible blocking and quality loss (ex. 1Mbps required for 640x360). Webcam capture resolution should be similar to video size in player/watch interface (capturing higher resolution will require more resources without visible quality improvement and lower will display pixelation when zoomed in player).

<h4>Default Webcam Frames Per Second</h4>
<select name="camFPS" id="camFPS">
<?php
				foreach (array('1','8','10','12','15','29','30','60') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['camFPS']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
<br>Recommended: 30 (fluent)

<h4>Video Stream Bandwidth</h4>
<input name="camBandwidth" type="text" id="camBandwidth" size="7" maxlength="7" value="<?php echo $options['camBandwidth']?>"/> (bytes/s)

<h4>Maximum Video Stream Bandwidth (at runtime)</h4>
<input name="camMaxBandwidth" type="text" id="camMaxBandwidth" size="7" maxlength="7" value="<?php echo $options['camMaxBandwidth']?>"/> (bytes/s)


<h4>Video Codec</h4>
<select name="videoCodec" id="videoCodec">
  <option value="H264" <?php echo $options['videoCodec']=='H264'?"selected":""?>>H.264</option>
  <option value="H263" <?php echo $options['videoCodec']=='H263'?"selected":""?>>H.263</option>
</select>
<BR>H.263 may produce better latency and allow buffering adjustments for smooth playback.
<BR>H.264 will produce better quality per bitrate but may introduce 2-3 seconds latency for any buffering different than 0.

<h4>H264 Video Codec Profile</h4>
<select name="codecProfile" id="codecProfile">
  <option value="main" <?php echo $options['codecProfile']=='main'?"selected":""?>>main</option>
  <option value="baseline" <?php echo $options['codecProfile']=='baseline'?"selected":""?>>baseline</option>
</select>

<h4>H264 Video Codec Level</h4>
<input name="codecLevel" type="text" id="codecLevel" size="32" maxlength="64" value="<?php echo $options['codecLevel']?>"/> (1, 1b, 1.1, 1.2, 1.3, 2, 2.1, 2.2, 3, 3.1, 3.2, 4, 4.1, 4.2, 5, 5.1)

<h4>Sound Codec</h4>
<select name="soundCodec" id="soundCodec">
  <option value="Speex" <?php echo $options['soundCodec']=='Speex'?"selected":""?>>Speex</option>
  <option value="Nellymoser" <?php echo $options['soundCodec']=='Nellymoser'?"selected":""?>>Nellymoser</option>
</select>

<h4>Speex Sound Quality</h4>
<input name="soundQuality" type="text" id="soundQuality" size="3" maxlength="3" value="<?php echo $options['soundQuality']?>"/> (0-10)

<h4>Nellymoser Sound Rate</h4>
<input name="micRate" type="text" id="micRate" size="3" maxlength="3" value="<?php echo $options['micRate']?>"/> (11/22/44)
<?php
				break;

			case 'support':
				//! Support
?>

<H3>Quick Setup Tutorial</H3>
<OL>
<LI> Before installing this make sure all hosting requirements are met: <a href="https://videowhisper.com/?p=Requirements">Hosting Requirements</a>
<BR> If you don't have RTMP hosting, see <a href="https://hostrtmp.com/compare/">RTMP Hosting Options</a>.
<BR> If using own dedicated RTMP server, install the RTMP application using these instructions: <a href="https://videowhisper.com/?p=RTMP+Applications">RTMP Side Setup</a> .</LI>
<LI> Fill RTMP address in <a href="/wp-admin/options-general.php?page=videowhisper_conference.php&tab=server">Settings</A>. </LI>
<LI> Add he Video Conference and Setup Conference pages to your <a href="/wp-admin/nav-menus.php">menus</a> and/or enable the <a href="/wp-admin/widgets.php">widget</a> (if you want to display active rooms with number of participants and conference access link).
<BR>
<BR>- Access video conference lobby at:
<BR><?php echo get_permalink(get_option("vw_vc_page_landing"))?>
<BR>
<BR>- After login you can setup rooms from:
<BR><?php echo get_permalink(get_option("vw_vc_page_manage"))?>
</OL>

<h3>Hosting Requirements</h3>
<UL>
<LI><a href="https://videowhisper.com/?p=Requirements">Hosting Requirements</a> This advanced software requires web hosting and <b>rtmp hosting</b>.</LI>
<LI><a href="https://videowhisper.com/?p=RTMP+Hosting">Estimate Hosting Needs</a> Evaluate hosting needs: volume and features.</LI>
<LI><a href="https://hostrtmp.com/compare/">Compare Hosting Options</a> Hosting options starting from $9/month.</LI>

</UL>

<h3>Software Documentation</h3>
<UL>
<LI><a href="https://videowhisper.com/?p=wordpress+video+conference">VideoWhisper Plugin Homepage</a> Plugin and application documentation.</LI>
<LI><a href="https://wordpress.org/plugins/videowhisper-video-conference-integration/">WordPress Video Conference</a> Plugin page in repository.</LI>
</UL>

<h3>Contact and Feedback</h3>
<a href="https://videowhisper.com/tickets_submit.php">Sumit a Ticket</a> with your questions, inquiries and VideoWhisper support staff will try to address these as soon as possible.
<br>Although the free license does not include any services (as installation and troubleshooting), VideoWhisper staff can clarify requirements, features, installation steps or suggest additional services like customisations, hosting you may need for your project.

<h3>Review and Discuss</h3>
You can publicly <a href="https://wordpress.org/support/view/plugin-reviews/videowhisper-video-conference-integration#postform">review this WP plugin</a> on the official WordPress site (after <a href="https://wordpress.org/support/register.php">registering</a>). You can describe how you use it and mention your site for visibility. You can also post on the <a href="https://wordpress.org/support/plugin/videowhisper-video-conference-integration">WP support forums</a> - these are not monitored by support so use a <a href="https://videowhisper.com/tickets_submit.php">ticket</a> if you want to contact VideoWhisper.
<BR>If you like this plugin and decide to order a commercial license or other services from <a href="https://videowhisper.com/">VideoWhisper</a>, use this coupon code for 5% discount: giveme5

<h3>News and Updates</h3>
You can also get connected with VideoWhisper and follow updates using <a href="https://twitter.com/videowhisper"> Twitter </a>.


				<?php
				break;

			}

			if (!in_array($active_tab, array('support')) ) submit_button();
?>


</form>
</div>
	 <?php
		}

	}
}

//instantiate
if (class_exists("VWvideoConference")) {
	$videoConference = new VWvideoConference();
}

//Actions and Filters
if (isset($videoConference))
{
	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
	register_activation_hook( __FILE__, array(&$videoConference, 'install' ) );
	add_action( 'init', array(&$videoConference, 'conference_post'));


	add_action("plugins_loaded", array(&$videoConference , 'init'));
	add_action('admin_menu', array(&$videoConference , 'menu'));

	/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
	function videoConferenceBP_init()
	{
		if (class_exists('BP_Group_Extension'))  require( dirname( __FILE__ ) . '/bp.php' );
	}

	add_action( 'bp_init', 'videoConferenceBP_init' );

}
?>
