<?php
/*
Plugin Name: Disk Space Pie Chart
Plugin URI: http://wpguru.co.uk/2010/12/disk-space-pie-chart-plugin/
Description: Displays your current server space usage in your Dashboard and as funky Pie Chart. It also shows your Database usage. Nice!
Author: Jay Versluis
Version: 0.7 Beta 3
Author URI: http://wpguru.co.uk
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Disk Space Pie Chart, copyright 2009-2014 by Jay Versluis (email : versluis2000@yahoo.com)
This plugin is distributed under the terms of the GNU GPL License.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA


*/

// Hook for adding admin menu
add_action('admin_menu', 'wpguru_space_pages');

// action function for above hook
function wpguru_space_pages() {

// Add a new submenu under DASHBOARD
add_dashboard_page('Disk Space Usage', 'Disk Space Pie', 'administrator', 'guruspace-admin', 'guruspace');
}

// DEFAULT DISPLAY VALUES - GB if first time user
// @since 0.4
 
if (!get_option('guru_unit')) {
	update_option ('guru_unit', 'MB');
}

// DAILY CRON JOB
// caches the output of the shell command in our database
// @since 0.7

// for testing: create "everyminute" schedule
function cron_add_minute( $schedules ) {
	// Adds once every minute to the existing schedules.
    $schedules['everyminute'] = array(
	    'interval' => 60,
	    'display' => __( 'Once Every Minute' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'cron_add_minute' );

// create daily event
function guruspace_cron_activation() {
	if( !wp_next_scheduled( 'diskspacepiechart' ) ) {  
	   wp_schedule_event( time(), 'daily', 'diskspacepiechart' );  
	}
}
add_action('wp', 'guruspace_cron_activation');

// unschedule upon deactivation
function guruspace_cron_deactivation() {	
$timestamp = wp_next_scheduled ('diskspacepiechart');
wp_unschedule_event ($timestamp, 'diskspacepiechart');
} 
register_deactivation_hook (__FILE__, 'guruspace_cron_deactivation');


// MAIN FUNCTION
function guruspace() {

	//must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
	

    // variables for the field and option names 
    $opt_name = 'guru_space';
    $opt_name2 = 'guru_unit';
	$opt_name3 = 'guruspace_receive_emails';
	
    $data_field_name = 'guru_space';
    $data_field_name2 = 'guru_unit';
	$data_field_name3 = 'guruspace_receive_emails';
	
	$hidden_field_name = 'guruspace_hidden';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );
    $opt_val2 = get_option ($opt_name2 );
	$opt_val3 = get_option ($opt_name3 );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];
        $opt_val2 = $_POST[ $data_field_name2 ];
		$opt_val3 = $_POST[ $data_field_name3 ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );
        update_option( $opt_name2, $opt_val2 );
		update_option( $opt_name3, $opt_val3 );

        // Put a "settings updated" message on the screen
?>

<div class="updated">
  <p><strong>
    <?php _e('Your settings have been saved.', 'guruspace-menu' ); ?>
    </strong></p>
</div>
<?php
    }

    // Now display the settings editing screen
    echo '<div class="wrap">';

    // header
    echo "<h2>" . __( 'Disk Space Pie Chart', 'guruspace-menu' ) . "</h2>";

    // settings form
        ?>
<form name="form1" method="post" action="">
  <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
  <p>
    <?php _e("How much webspace have you got:", 'guruspace-menu' ); ?>
    <input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="5">
    &nbsp;
    <select name="<?php echo $data_field_name2; ?>">
      <option value="GB" <?php if ($opt_val2 == "GB") echo 'selected'; ?>>GiB (GB)</option>
      <option value="MB" <?php if ($opt_val2 == "MB") echo 'selected'; ?>>MiB (MB)</option>
    </select>
  </p>
  <p><em><?php echo 'You are currently displaying values in ' . $opt_val2; ?>. Leave BLANK if you're lucky enough to have unlimited space.</em></p>
  
  <p>Would you like to receive email notifications when you reach 95% usage? &nbsp;
  <select name="<?php echo $data_field_name3; ?>">
      <option value="yes" <?php if ($opt_val3 == "yes") echo 'selected'; ?>>Yes please!</option>
      <option value="no" <?php if ($opt_val3 == "no") echo 'selected'; ?>>No thanks</option>
    </select>
    </p>
  
  
  <p class="submit">
    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
  </p>
</form>
<br />
<hr />
<br />
<?php
//  Determining Database Size
//  Alert level. Database size is shown in red if greater than this value, else in green.
//  You can adjust this value (in MB) to your conveniance  in the line below. 
//  Default value is : 4 MB.

define("alertlevel",4);

// Size Categories
function file_size_info($filesize) {
	$bytes = array('KB', 'KB', 'MB', 'GB', 'TB');

	# values are always displayed
	if ($filesize < 1024) $filesize = 1;

	# in at least kilobytes.
	for ($i = 0; $filesize > 1024; $i++) $filesize /= 1024;
	$file_size_info['size'] = round($filesize,3);
	$file_size_info['type'] = $bytes[$i];

return $file_size_info; } 

// Calculate DB size by adding table size + index size:
// This just echoes the db size, we'll position it later
function db_size(){

	$rows = mysql_query("SHOW table STATUS"); $dbsize = 0;

	while ($row = mysql_fetch_array($rows)) 
		{$dbsize += $row['Data_length'] + $row['Index_length']; } 
	
	if ($dbsize > alertlevel * 1024 * 1024) {
	$color = "red";}
	else {
	$color = "green";}
		$dbsize = file_size_info($dbsize); 
		echo "{$dbsize ['size']} {$dbsize['type']}"; 
}
// end of Database Function

// grab used space
$usedspace = guruspaceCheckSpace();

// see what we need to display - GB or MB
if ($opt_val2 == 'GB') {
$spacecalc = 1024 * 1024;
}
else {
$spacecalc = 1024;
}

// read in how much space we have in our package
$totalspace = ($opt_val * $spacecalc);
$freespace = ($opt_val * $spacecalc) - $usedspace;

?>
<table width="800" border ="0">
  <tr>
    <td><img src="<?php echo plugins_url('includes/piechart.php?data=', __FILE__);
echo round(($usedspace / ($totalspace / 100)),1) . '*' . (100-(round(($usedspace / ($totalspace / 100)),1))); ?>&label=Used Space*Free Space" /></td>
    <td><strong>Disk Space Used:</strong><br />
      <strong>Disk Space Free:</strong><br />
      <hr>
      <strong>Database Size:</strong><br />
      <hr>
      <strong>PHP Version:</strong><br />
      <strong>MySQL Version:</strong><br /></td>
    <td><?php echo round(($usedspace / $spacecalc),2) . ' ' . $opt_val2; ?><br />
      <?php echo round(($freespace / $spacecalc),2) . ' ' . $opt_val2; ?><br />
      <hr>
      <?php db_size(); ?>
      <br />
      <hr>
      <?php echo PHP_VERSION; ?><br />
      <?php 

// display MySQL Version
echo guruspaceServerVersion();

?>
      <br /></td>
  </tr>
</table>
</div>
<p>You're currently using <?php echo round(($usedspace / $spacecalc),2) . $opt_val2; ?> of HDD space. Meanwhile, your Database has grown to
  <?php db_size(); ?>
  . </p>
<p>
  <a href="http://wpguru.co.uk" target="_blank"><img src="
<?php 
echo plugins_url('images/guru-header-2013.png', __FILE__);
?>" width="300"></a> </p>

<p><a href="http://wpguru.co.uk/2010/12/disk-space-pie-chart-plugin/" target="_blank">Plugin by Jay Versluis</a> | <a href="http://www.peters1.dk/webtools/php/lagkage.php?sprog=en" target="_blank">Pie Chart Script by Rasmus Peters</a> | <a href="http://wphosting.tv" target="_blank">WP Hosting</a></p>

<p><span><!-- Social Buttons -->

<!-- Google+ -->
<div class="g-follow" data-annotation="bubble" data-height="20" data-href="//plus.google.com/116464794189222694062" data-rel="author"></div>

<!-- Place this tag after the last widget tag. -->
<script type="text/javascript">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/platform.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<!-- Twitter -->
<a href="https://twitter.com/versluis" class="twitter-follow-button" data-show-count="true">Follow @versluis</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

<!-- Facebook -->
<iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FThe-WP-Guru%2F162188713810370&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=21&amp;appId=186277158097599" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px;" allowTransparency="true"></iframe>

</span></p>

<?php

////////////////////////////////////////
// TESTING AREA
/*
if (WP_DEBUG) {
	echo '<p>TESTING AREA - displayed only when WP_DEBUG is defined in your installation: ';
	echo guruspaceSendMail();
	echo '</p>';
}
*/


?>
<?php
}

// original code from Memory Usage plugin
if ( is_admin() ) {	
	
	class wp_memory_usage {
		
		var $memory = false;
		
		function wp_memory_usage() {
			return $this->__construct();
		}

		function __construct() {
            add_action( 'init', array (&$this, 'check_limit') );
			add_action( 'wp_dashboard_setup', array (&$this, 'add_dashboard') );
			add_filter( 'admin_footer_text', array (&$this, 'add_footer') );

			$this->memory = array();					
		}
        
        function check_limit() {
            $this->memory['limit'] = (int) ini_get('memory_limit') ;
        }
		
		function check_memory_usage() {
			
			$this->memory['usage'] = function_exists('memory_get_usage') ? round(memory_get_usage() / 1024 / 1024, 2) : 0;
			
			if ( !empty($this->memory['usage']) && !empty($this->memory['limit']) ) {
				$this->memory['percent'] = round ($this->memory['usage'] / $this->memory['limit'] * 100, 0);
				$this->memory['color'] = '#21759B';
				if ($this->memory['percent'] > 80) $this->memory['color'] = '#E66F00';
				if ($this->memory['percent'] > 95) $this->memory['color'] = 'red';
			}		
		}



		
// creates the Dashboard Box
function dashboard_output() {
$this->check_memory_usage();
$this->memory['limit'] = empty($this->memory['limit']) ? __('N/A') : $this->memory['limit'] . __(' MByte');
$this->memory['usage'] = empty($this->memory['usage']) ? __('N/A') : $this->memory['usage'] . __(' MByte');

// grab current disk space
$usedspace = guruspaceCheckSpace();

// check for total space available
$opt_val = get_option('guru_space');
$opt_val2 = get_option('guru_unit');

// see what we need to display - GB or MB
if ($opt_val2 == 'GB') {
$spacecalc = 1024 * 1024;
}
else {
$spacecalc = 1024;
}

// read in how much space we have in out package
$totalspace = ($opt_val * $spacecalc);
$freespace = ($opt_val * $spacecalc) - $usedspace;

?>
<ul>
  <li><strong>
    <?php _e('Disk Space Used: '); ?>
    </strong> : <span><?php echo round(($usedspace / $spacecalc),2) . " " . $opt_val2; ?> </span> | <strong>
    <?php _e('Disk Space Free: '); ?>
    </strong> : <span>
    <?php 
// figure out a way to test the variable...
if (!$opt_val) echo "UNLIMITED";
else echo round(($freespace / $spacecalc),2) . " " . $opt_val2; 
?>
    </span> | <a href="?page=guruspace-admin">Pie Chart and Setup</a></li>
</ul>
<?php if (!empty($this->memory['percent'])) : ?>
<?php 
// thanks to Jure for the Division by Zero bugfix
$perc = $totalspace==false ? 100 : round(($usedspace / ($totalspace / 100)),1); ?>
<div class="progressbar">
  <div class="" style="height:2em; border:1px solid #DDDDDD; background-color: #0055cc">
    <div class="" style="width: <?php echo $perc; ?>%;height:100%;background-color:#f55;;border-width:0px;text-shadow:0 1px 0 #000000;color:#FFFFFF;text-align:right;font-weight:bold;">
      <div style="padding:6px"><?php echo $perc; ?>%</div>
    </div>
  </div>
</div>
<?php endif; ?>
<?php
		}
		 
		function add_dashboard() {
			wp_add_dashboard_widget( 'wp_memory_dashboard', 'Disk Space Usage', array (&$this, 'dashboard_output') );
		}
		
function add_footer($content) {
$this->check_memory_usage();

// hook for Footer Display
// let's do this another time,...
//

return $content;
		}

} // end of main function

/////////////////////////////////////////////////
/////////////////////////////////////////////////

// send an email when approaching space limit
// @since 0.7
function guruspaceSendMail() {
	
	// grab used space
	$usedspace = guruspaceCheckSpace();
	
	if (get_option('guru_unit') == 'GB') {
	$spacecalc = 1024 * 1024;
	}
	else {
	$spacecalc = 1024;
	}
	
	// read in how much space we have in our package
	$totalspace = (get_option('guru_space') * $spacecalc);
	$freespace = (get_option('guru_space') * $spacecalc) - $usedspace;
	$usage = round(($usedspace / ($totalspace / 100)),1);
	
	// send an email if we're over 95% usage
	if (get_option('guruspace_receive_emails') == 'yes') {
		
		echo '<p>Sending Email</p>';
		// construct the components for our email
		$recepients = get_option('admin_email');
		$subject = 'Disk Space at ' . get_option('blogname');
		
		$headers = 'From: '.get_option('blogname')." <".get_option('admin_email').">";
		
		$message = 'Hi there! This is the Disk Space Pie Chart plugin from your website ' . get_option('blogname');
		$message = $message . ".\n\nI just wanted to let you know that you are approaching the disk space limit you have setup. \n\nRight now you are using " . $usage . '% of your total ' . get_option('guru_space');
		$message = $message . get_option('guru_unit') . ". \n\n\n\nAll the best, your Disk Space Pie Chart Plugin ;-)";
		
		// let's send it 
		$success = mail($recepients, $subject, $message, $headers);
		if (!$success && WP_DEBUG) {
			echo 'Could not send notification mail from Disk Space Pie Chart plugin.';
		} 
	}
	
	// display test output when debug is enabled
	if (WP_DEBUG) {
		$testreturn = 'totalspace = ' . $totalspace . ' | freespace = ' . $freespace . ' | usage = '.$usage . ' | recipients = ' . $recepients . ' | headers = '.$headers .' | message = '.$message;
		return $testreturn;
	}
}

// perform expensive disk read and add it to the database for quick access
// moved here @since 0.7
function guruspaceRefreshSpace () {
	
	$output = substr(shell_exec('pwd'),0,-9);
	$usedspace = substr(shell_exec('du -s ' . $output),0,-(strlen($output)+1));
	update_option ('guruspace_cached_space', $usedspace);
	return $usedspace;
	
	// also trigger email if required
	guruspaceSendEmail();
}
// call this via scheduled event every day
add_action('diskspacepiechart', 'guruspaceRefreshSpace');

// check current disk space
// @since 0.7
function guruspaceCheckSpace () {
	
	// return cached result
	$cachedResult = get_option('guruspace_cached_space');
	if ($cachedResult) {
		return $cachedResult;
	}
}

// return MySQL Version
function guruspaceServerVersion () {
	
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if (mysqli_connect_errno($mysqli)) {
		$myServerVersion = 'Unknown (' . mysqli_connect_error() . ')';
		
		} else {
			$myServerVersion = $mysqli->server_info;
		}
	$mysqli->close();
	
	return $myServerVersion;
}
	

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', create_function('', '$memory = new wp_memory_usage();') );
	
} // end of main function
