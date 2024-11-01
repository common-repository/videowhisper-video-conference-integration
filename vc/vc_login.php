<?php

include("inc.php");

$options = get_option('VWvideoConferenceOptions');

$rtmp_server = $options['rtmp_server'];
$rtmp_amf = $options['rtmp_amf'];
$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
$canAccess = $options['canAccess'];
$accessList = $options['accessList'];

$serverRTMFP = $options['serverRTMFP'];
$p2pGroup = $options['p2pGroup'];
$supportRTMP = $options['supportRTMP'];
$supportP2P = $options['supportP2P'];
$alwaystRTMP = $options['alwaystRTMP'];
$alwaystP2P = $options['alwaystP2P'];
$disableBandwidthDetection = $options['disableBandwidthDetection'];

$camRes = explode('x',$options['camResolution']);


global $current_user;
get_currentuserinfo();

//username
if ($current_user->$userName) $username=urlencode($current_user->$userName);
$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

//avatar
if ($current_user->ID != 0)
{
$avatarPicture = urlencode(get_avatar_url($current_user->ID, array('size' => 48) ));
$userPicture = urlencode(get_avatar_url($current_user->ID, array('size' => 240) ));
}

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


switch ($canAccess)
{
case "all":
	$loggedin=1;
	if (!$username)
	{
		$username="Guest".base_convert((time()-1224350000).rand(0,10),10,36);
		$visitor=1; //ask for username
	}
	break;
case "members":
	if ($username) $loggedin=1;
	else $msg="<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>";
	break;
case "list";
	if ($username)
		if (inList($userkeys, $accessList)) $loggedin=1;
		else $msg="<a href=\"/\">$username, you are not in the video conference access list.</a>";
		else $msg="<a href=\"/\">Please login first or register an account if you don't have one! Click here to return to website.</a>";
		break;
}

//configure a picture to show when this user is clicked
//if (!$userPicture) $userPicture=urlencode("defaultpicture.png");
$userLink=urlencode("http://www.videowhisper.com/");

//replace bad words or expression
$filterRegex=urlencode("(?i)(fuck|cunt)(?-i)");
$filterReplace=urlencode(" ** ");

//room access
if ($_GET['room_name']) $room = $_GET['room_name'];
$room = sanitize_file_name($room);
if ($room) setcookie('userRoom',$room);


if (!$room && !$visitor)
{


	if ($options['landingRoom']=='username')
		//can create
		{

		$room=$username;
		$admin=1;

	}

	else $room = $options['lobbyRoom']; //or go to default

}
else if (!$room) $room = $options['lobbyRoom'];  //visitor can't create room

	global $wpdb;
$table_name = $wpdb->prefix . "vw_vcsessions";
$table_name3 = $wpdb->prefix . "vw_vcrooms";
$wpdb->flush();


if (!$options['anyRoom']) //room must exist
	if ($room != $options['lobbyRoom'] || $options['landingRoom'] !='lobby') //not lobby
		{

		$rm = $wpdb->get_row("SELECT count(id) as no FROM $table_name3 where name='$room'");
		if (!$rm->no)
		{
			$msg="Room $room does not exist!";
			$loggedin=0;
		}
	}

//room owner?
$rm = $wpdb->get_row("SELECT owner FROM $table_name3 where name='$room'");
if ($rm) if ($rm->owner == $current_user->ID) $admin=1;

	//post room
	$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $room. "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );

if ($postID)
{
	foreach (array('access','chat','write','list','private') as $field)
		$$field = get_post_meta($postID, 'vw_'.$field, true);

	if ($access) if (!inList($userkeys, $access))
		{
			$loggedin=0;
			$msg='A room access list is defined and you are not in list.';
		}

	if ($chat) if (inList($userkeys, $chat)) $panelChat = 1;
		else $panelChat = 0;

		if ($list) if (inList($userkeys, $list)) $panelUsers = 1;
			else $panelUsers = 0;

			if ($write) if (inList($userkeys, $write)) $writeText = 1;
				else $writeText = 0;

				if ($private) if (inList($userkeys, $private)) $privateTextchat = 1;
					else $privateTextchat = 0;

					$post = get_post($postID);
					$description = $post->post_content;
}

//configure a picture to show when this user is clicked
if (!$userPicture || $options['avatar']=='snapshot') $userPicture = urlencode("uploads/_sessions/${username}_240.jpg");
if (!$avatarPicture || $options['avatar']=='snapshot') $avatarPicture = urlencode("uploads/_sessions/${username}_64.jpg");
$userLink=urlencode("http://www.videowhisper.com/");
$profileDetails = "Profile details for <i>$username</i><BR>Some html tags are supported (B I FONT IMG ...).";


if (!$welcome) $welcome=html_entity_decode(stripslashes($options['welcome'])) ;
if ($description) $welcome .= '<br>' . $description;

				//warn if HTTPS missing
				if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
				$welcome.= '<br><B>Warning: HTTPS not detected. Some browsers like Chrome will not permit webcam access when accessing without SSL!</B>';

?>firstParam=fix&server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php echo $p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo $disableBandwidthDetection?>&disableUploadDetection=<?php echo $disableBandwidthDetection?>&username=<?php echo urlencode($username)?>&loggedin=<?php echo $loggedin?>&userType=<?php echo $userType?>&administrator=<?php echo $admin?>&room=<?php echo urlencode($room)?>&welcome=<?php echo urlencode($welcome)?>&userPicture=<?php echo $userPicture?>&userLink=<?php echo $userLink?>&webserver=&msg=<?php echo urlencode($msg)?>&room_delete=0&room_create=0&camWidth=<?php echo $camRes[0];?>&camHeight=<?php echo $camRes[1];?>&camFPS=<?php echo $options['camFPS']?>&camBandwidth=<?php echo $options['camBandwidth']?>&videoCodec=<?php echo $options['videoCodec']?>&codecProfile=<?php echo $options['codecProfile']?>&codecLevel=<?php echo $options['codecLevel']?>&soundCodec=<?php echo $options['soundCodec']?>&soundQuality=<?php echo $options['soundQuality']?>&micRate=<?php echo $options['micRate']?>&camMaxBandwidth=<?php echo $camMaxBandwidth; ?>&layoutCode=<?php echo urlencode(html_entity_decode($options['layoutCode']))?>&fillWindow=0&filterRegex=<?php echo $filterRegex?>&filterReplace=<?php echo $filterReplace?>&avatarPicture=<?php echo $avatarPicture?>&profileDetails=<?php echo urlencode($profileDetails)?>&panelChat=<?php echo $panelChat?>&panelUsers=<?php echo $panelUsers?>&writeText=<?php echo $writeText?>&privateTextchat=<?php echo $privateTextchat?><?php echo html_entity_decode($options['parameters']); ?>&visitor=<?php echo $visitor;?>&loadstatus=1