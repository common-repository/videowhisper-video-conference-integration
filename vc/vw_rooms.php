<?php
header('Content-Type: application/xml; charset=utf-8');

include("../../../../wp-config.php");

global $wpdb;

$table_name = $wpdb->prefix . "vw_vcsessions";
$table_name3 = $wpdb->prefix . "vw_vcrooms";

//clean session recordings
$exptime=time()-30;
$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
$wpdb->query($sql);
$wpdb->flush();

$options = get_option('VWvideoConferenceOptions');

$userRoom = $_COOKIE["userRoom"];
$userRoom = sanitize_file_name($userRoom);

//user info
global $current_user;
get_currentuserinfo();

$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

//username
if ($current_user->$userName) $username=urlencode($current_user->$userName);
$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

//access keys
$userkeys = $current_user->roles;
$userkeys[] = $current_user->user_login;
$userkeys[] = $current_user->ID;
$userkeys[] = $current_user->user_email;
$userkeys[] = $current_user->display_name;

$loggedin=0;
$msg="";

//access permissions
function inList($keys, $data)
{
	if (!$keys) return 0;
	if (!$data) return 0;
	if (strtolower(trim($data)) == 'all') return 1;
	if (strtolower(trim($data)) == 'none') return 0;

	$list=explode(",", strtolower(trim($data)));

	foreach ($keys as $key)
		foreach ($list as $listing)
			if ( strtolower(trim($key)) == trim($listing) ) return 1;

			return 0;
}



$listed_rooms=array(); //keep track of duplicates

//private room?
if ($userRoom)  $pr = $wpdb->get_row("SELECT * FROM $table_name3 where name='$userRoom'");
if ($pr)
{
	$ptype = ($pr->type==1?'':'1');
	$capacity = ($pr->capacity?$pr->capacity:$options['capacityDefault']);
}
else
{
	$ptype = 1;
	$capacity = $options['capacityDefault'];
}

?>
<rooms>
<?php

//current room
if ($userRoom)
{
	$listed_rooms[] = $userRoom;
	echo "<room room_name=\"".$userRoom."\" room_description=\"Welcome to ".$userRoom."!\" user_number=\"0\" capacity=\"$capacity\" private_room=\"$ptype\"/>";
}

//owned rooms (public and private)
$items =  $wpdb->get_results("SELECT * FROM `$table_name3` where status='1' and owner='" . $current_user->ID . "'");

if ($items) foreach ($items as $item)
		if (!in_array($item->name, $listed_rooms))
		{
			$listed_rooms[] = $item->name;
			$item->name = sanitize_file_name($item->name);
			echo "<room room_name=\"".$item->name."\" room_description=\"Welcome to ".$item->name."!\" user_number=\"0\" capacity=\"" . ($item->capacity?$item->capacity:$options['capacityDefault']) . "\" private_room=\"" . ($item->type==1?'':'1') . "\"/>";
		}

	//default landing room
	if ($options['landingRoom']=='lobby')
		if (!in_array($options['lobbyRoom'], $listed_rooms))
		{
			echo "<room room_name=\"" . $options['lobbyRoom'] . "\" room_description=\"Welcome to ".$options['lobbyRoom']."!\" user_number=\"0\" capacity=\"" . $options['capacityDefault'] . "\" private_room=\"\"/>";
			$listed_rooms[] = $options['lobbyRoom'];
		}

	//rest of public room
	$items =  $wpdb->get_results("SELECT name FROM `$table_name3` where status='1' and type='1'");

if ($items) foreach ($items as $item)
		if (!in_array($item->name, $listed_rooms))
		{

			$listIt = 1;
			$description = '';
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $item->name. "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			if ($postID)
			{
				//check if user has access to that room
				$access = get_post_meta($postID, 'vw_access', true);
				if ($access) if (!inList($userkeys, $access)) $listIt = 0;

				if ($listIt)
				{
					$post = get_post($postID);
					$description = $post->post_content;
				}
			}

			if ($listIt) echo "<room room_name=\"".$item->name."\" room_description=\"Welcome to " . $item->name .'! '.htmlspecialchars($description) . "\" user_number=\"0\" capacity=\"" . ($item->capacity?$item->capacity:$options['capacityDefault']) . "\" private_room=\"\"/>";
		}
?>
</rooms>