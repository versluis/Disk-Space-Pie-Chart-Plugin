=== Plugin Name ===
Contributors: versluis
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=34B76TPRWMWAE
Tags: disk space, hosting space, web space, pie chart
Requires at least: 2.7
Tested up to: 3.8
Stable tag: 0.6

Shows your server space (free and used) as a funky Pie Chart in your backend. It also shows a percentage bar in your WordPress dahsboard.

== Description ==

Shows your free and used server space as funky Pie Chart in your backend. Sweet!

Following the success of my server space script for one of my clients, I thought it would be great to use it under WordPress. 

This Plugin was inspired by Richard who came to host with me after being on WordPress.com for some years. He really liked to keep track of how much space he has left to upload pictures and I was happy to take on this challenge.

Special thanks again to Rasmus Peters for his amazing PHP Pie Chart Script 

== Installation ==

1. Upload the entire folder `disk-space-pie-chart` to the `/wp-content/plugins/` directory. Please do not rename this folder.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enjoy a progress bar in the Dashboard and a Pie Chart under Dashboard - Disk Space Pie

The Plugin can tell you how much space you're using, but it doesn't know how much space you're ALLOWED to use. Hence it can only display the progress bar and pie chart diagramme if you honestly tell it. You should always be honest by the way, even with plugins ;-)

To do so, go to Dashboard - Disk Space Pie and type in the amount of web space you're allowed to use. Say you have 1GB of web space, put in 1 and hit Save Changes. Easy.

If you know your value in MB, you need to divide this value by 1024 to arrive at the equivalent GB value. For example, 100MB divided by 1024 is 0.09765625 -  that's the value to use in the box. You get the drift.

If you enter a number smaller than the amount of space you're using, you'll see something like 105% space free - which obviously isn't what you want. So don't do that.

== Upgrade Notice ==

If you're upgrading frmo Version 0.1, you have to enter your free disk space again under Dashboard - Disk Space Pie. This shouldn't happen when you upgrade from 0.2 and upwards.

== Frequently Asked Questions ==

= What's required server side for this plugin to run? =

Since I'm using shell commands to count the files you're using in your current directory, this Plugin only works on Linux servers - NOT on Windows servers.
For the Pie Chart display to work, your server needs to have GD 2.0.1 or later installed with JPG Support enabled.

= Does this Plugin run on Windoes Servers? =

I'm afraid not - you have to be on a Linux server for this to work. I've developed and tested it on CentOS / RHEL.

= I've noticed my Dashboard is slow. What gives? = 

In order to determine your used disk space 'live' and at all times, the server physically counts every file in all your directories every time the Dahsboard or admin screen is called. On larger installations or slow servers this can take some time so you may notice a slight delay upon loading the page. This is normal yet annoying.

To minimise this issue, just close the dahsboard window using the little arrow in the top right corner. Alternatively, click on Screen Options and disable the widget by unticking the box.

== Screenshots ==

This gives you a quick overview of what your Dashboard and Backend will look like. Cool, huh?

1. This screenshot illustrates the new box you'll get in the Dashboard
2. Here's what the funky Pie Chart diagramme looks like. In the admin screen you'll be able to enter your total available space. It also shows how big your databse is.

These screenshots may not show up because I haven't figured out how to use this repository yet...

== Changelog ==

= 0.6 =
* Tweaked Pie Chart background colour to match WordPress 3.8
* Added link to GPL license

= 0.5 =
* Fixed Division by Zero bug (thanks Jure!)
* Tweaked some colours to better blend with WordPress Core
* Added MySQL Server Version

= 0.4 =
* Fixed dashboard widget display in WordPress 3.2 (Thanks Dan!)
* Replaced hard coded links with dynamic ones
* Updated Folder Structure in accordance with WordPress Best Coding Standards
* Added default unit value for first-time-user (now defaults to GB rather than nothing) 
* Changed Donation Link (Buy me a Coffee)

= 0.3 =

* Certified WordPress 3.1 readiness.
* Added Unlimited Option: On some hosts (like Hostgator) you'll get "unlmited" disk space. I've amended the plugin so that you can enter 0 or leave the space blank. You'll always have 100% free space and the pie chart won't get confused.

= 0.2 =

* Added a drop-down menu so you an easily choose if you'd like things displayed in MB or GB.
* Added Database Size Count courtesy of Chris Rabiet's WP Database Plugin (http://wordpress.org/extend/plugins/db-size/)
* Added numeric display of space underneath pie charts - like in the dashboard widget.

= 0.1 =

Driven by Richard's suggestion, I've used Alex Rabe's WP Memory Usage Plugin as template and several WordPress tutorials on how to build plugins. Within a few hours, the basis was established. Then I integrated Rasmus Peter's Pie Chart Script and made it all work together. Code Cleanup followed: renaming functions, hooks, variables, deleting code that's not needed.
