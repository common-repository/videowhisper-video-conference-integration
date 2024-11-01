=== Webcam Video Conference ===
Contributors: videowhisper, VideoWhisper.com
Author: VideoWhisper.com
Author URI: https://videowhisper.com
Plugin Name: VideoWhisper Video Conference
Plugin URI: https://videowhisper.com/?p=WordPress+Video+Conference
Donate link: https://videowhisper.com/?p=Invest#level1
Tags: video, conference, chat, webcam, BuddyPress, video chat, videochat, widget, plugin, media, sidebar, cam, group, groups, tab, P2P, videoconference, collaboration, community, meeting, remote, telepresence, videopresence, presence, online, call
Requires at least: 2.7
Tested up to: 5.2
Stable tag: trunk

Webcam Video Conferencing implements video conferencing rooms for site users.

== Description ==
VideoWhisper Video Conference is a modern web based multiple way video chat and real time file sharing tool. Read more on [WordPress Video Conference](https://videowhisper.com/?p=WordPress+Video+Conference  "WordPress Video Conference") plugin home page.

* Easy installation , updates as plugin
* Access setup (everybody, users, user list)
* Members can be allowed to create rooms
* Define user list with access, multiple permissions for each room
* Widget with active rooms list and entry
* Menu with landing room page
* BuddyPress integration (group video conference room)
* Archive sessions and import videos with with [Video Share VOD](https://wordpress.org/plugins/video-share-vod/  "Video Share / Video On Demand") WordPress Plugin
* Reserver features (setup and/or access rooms) for paying roles with [Paid Membership and Content](https://wordpress.org/plugins/paid-membership/ "WordPress Paid Membership and Content Plugin"))

Latest application version includes H264 and Speex support, acoustic echo cancellation, P2P groups support for better, faster video streaming and lower rtmp server bandwidth usage.

Includes a widget that displays active rooms (with number of participants) and conference access link.
A Video Conference page is added to the website and can be disabled from settings.

There is a settings page with multiple parameters and permissions (what users can access - all, only members, predefined list).

**BuddyPress** integration: If BuddyPress is installed this will add a Video Conference tab to the group, where users can video chat realtime in group room.

This software is great for meetings, trainings, conferences, live events, recruiting, consultations, coaching and of course casual community chat. This brings people together instantly and without travel costs. These benefits open a wide range of new business opportunities and bring the extra value needed by established sites struggling to go ahead of their competitors.

Special **requirements**: This plugin has requirements beyond regular WordPress hosting specifications: a RTMP host is needed for persistent connections to manage live interactions and streaming. More details about this, including solutions are provided on the Installation section pages.

Suggested plugin: [Paid Membership](https://wordpress.org/plugins/paid-membership/  "Paid Membership and Content") WordPress Plugin allows members to purchase membership with MyCred tokens.

== Installation ==
* Before installing this make sure all hosting requirements are met: https://videowhisper.com/?p=Requirements
* Install the RTMP application using these instructions: https://videowhisper.com/?p=RTMP+Applications
* Install the plugin from WP repository, from WP backend. Or manual installation: Copy this plugin folder to your wordpress installation in your plugins folder. You should obtain wp-content/plugins/videowhisper-video-conference-integration .
* Enable the plugin from Wordpress admin area and fill the "Settings", including rtmp address there.
* Add the Video Conference and Setup Conference pages to your menu and/or enable the widget (if you want to display active rooms with number of participants and conference access link).

== Screenshots ==
1. Video Conference
2. Video Conference Media Support
3. Video Conference Menu
4. Frontend Setup Page
5. Frontend Conferencing Page

== Desktop Sharing / Screen Broadcasting ==
If your users want to broadcast their screen (when playing a game, using a program, tutoring various computer skills) they can do that easily just by using a screen sharing driver that simulates a webcam from desktop contents. Read more on https://videochat-scripts.com/screen-sharing-with-flash-video-chat-software/ .

Resulting videos can be managed and imported with [Video Share VOD](http://wordpress.org/plugins/video-share-vod/  "Video Share / Video On Demand") WordPress Plugin .

== Reference ==
* [Video Conference Plugin Homepage](https://videowhisper.com/?p=WordPress+Video+Conference)
* [Video Conference Application Homepage](https://videowhisper.com/?p=Video+Conference)
* [Contact Support](https://videowhisper.com/tickets_submit.php)

== Customize ==
Application loads interface (skins, icons, sounds, background) from files (jpg, png, mp3) in the vc/templates/conference folder (from plugin installation folder). Browser cache needs to be cleared for new files to load. 
 
Layout can be customized by generating a layout code and filling it in plugin settings.  
Multiple parameters including for toggling certain panels are documented on a page linked from plugin settings. Layout and parameter settings are in Integration tab, from plugin settings page. 

== Demo ==
* See it live on https://www.videochat-scripts.com/video-conference/

== Extra ==
More information, the latest updates, other applications plugins and non-WordPress editions can be found at https://videowhisper.com/ .

== Changelog ==

= 5.25.1 =
* interface improvements and fixes

= 5.07.1 =
* Option to use WordPress avatars instead of snapshots

= 5.05.1 =
* User lists permissions per room: Access, Chat, Write, Participants List, Private Chat

= 4.91.8 =
* Room capacity can be setup per room.
* Default and max room capacity in settings.

= 4.91.6 =
* Performance upgrades and fixes

= 4.91 =
* users can setup static private and public rooms
* room management page, shortcode [videowhisper_conference_manage]
* room access page for all rooms, shortcode [videowhisper_conference]
* setting tabs, multiple new options and integration features
* Integrated latest application version

= 4.72.3 =
* Integrated latest application version (v4.72)
* Codec settings
* Better room access, landing room setup

= 4.51 =
* Integrated latest application version (v4.51) that includes P2P.
* Added more settings to control P2P / RTMP streaming, bandwidth detection.
* Fixed some possible security vulnerabilites for hosts with magic_quotes Off.

= 3.1 =
* BuddyPress integration: If BuddyPress is installed this will add a Video Conference tab to the group where users can video chat realtime in group room.

= 3.0 =
* Everything is in the plugin folder to allow automated updates.
* Settings page to fill rtmp address.
* Choose name to use in application (display name, login, nice name).
* Access permissions (all, members, list).
* List number of participants for each room.

= 2.0 =
* Compatibility updates.

= 1.0.2 =
* Compatibility updates and widget.

= 1.0 =
* Plugin to integrate video conference installed in a videowhisper_conference folder on site root.