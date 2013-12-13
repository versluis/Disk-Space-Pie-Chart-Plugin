<?php
/*
Plugin Name: Disk Space Pie Chart
Plugin URI: http://wpguru.co.uk/2010/12/disk-space-pie-chart-plugin/
Description: Displays your current server space usage in your Dashboard and as funky Pie Chart. It also shows your Database usage. Nice!
Author: Jay Versluis
Version: 0.6
Author URI: http://wpguru.co.uk
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Disk Space Pie Chart, copyright 2009-2014 by Jay Versluis (email : versluis2000@yahoo.com)
This plugin is distributed under the terms of the GNU GPL License.

This is Version 0.6 as of 13/12/2013

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


// displays the page content for the admin submenu
function guruspace() {

//must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $opt_name = 'guru_space';
    $opt_name2 = 'guru_unit';
    $hidden_field_name = 'guruspace_hidden';
    $data_field_name = 'guru_space';
    $data_field_name2 = 'guru_unit';


    // Read in existing option value from database
    $opt_val = get_option( $opt_name );
    $opt_val2 = get_option ($opt_name2 );


    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];
        $opt_val2 = $_POST[ $data_field_name2 ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );
        update_option( $opt_name2, $opt_val2 );

        // Put a "settings updated" message on the screen
?>
<div class="updated"><p><strong><?php _e('Your settings have been saved.', 'guruspace-menu' ); ?></strong></p></div>
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

<p><?php _e("How much webspace have you got:", 'guruspace-menu' ); ?> 
<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="5">
&nbsp;

<select name="guru_unit">
<option value="GB" <?php if ($opt_val2 == "GB") echo 'selected'; ?>>GiB (GB)</option>
<option value="MB" <?php if ($opt_val2 == "MB") echo 'selected'; ?>>MiB (MB)</option> 
</select>

</p>
<p><em><?php echo 'You are currently displaying values in ' . $opt_val2; ?>. Leave BLANK if you're lucky enough to have unlimited space.</em></p>


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

// check where we are
$output = substr(shell_exec('pwd'),0,-9);
// check how much disk space we're using
$usedspace = substr(shell_exec('du -s ' . $output),0,-(strlen($output)+1));

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
<tr><td>
<img src="<?php echo plugins_url('includes/piechart.php?data=', __FILE__);
echo round(($usedspace / ($totalspace / 100)),1) . '*' . (100-(round(($usedspace / ($totalspace / 100)),1))); ?>&label=Used Space*Free Space" /> 
</td>
<td>
<strong>Disk Space Used:</strong><br />
<strong>Disk Space Free:</strong><br />
<hr>
<strong>Database Size:</strong><br />
<hr>
<strong>PHP Version:</strong><br />
<strong>MySQL Version:</strong><br />


</td>
<td>
<?php echo round(($usedspace / $spacecalc),2) . ' ' . $opt_val2; ?><br />
<?php echo round(($freespace / $spacecalc),2) . ' ' . $opt_val2; ?><br />
<hr>
<?php db_size(); ?><br />
<hr>
<?php echo PHP_VERSION; ?><br />
<?php 
// display MySQL Version
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
echo $mysqli->server_info;
$mysqli->close();
?><br />


</td></tr>
</table>

</div>

<p>You're currently using <?php echo round(($usedspace / $spacecalc),2) . $opt_val2; ?> of HDD space. Meanwhile, your Database has grown to <?php db_size(); ?>. </p>
<p>This plugin was brought to you by<br />
<a href="http://wpguru.co.uk" target="_blank"><img src="
<?php 
echo plugins_url('images/guru-header-2013.png', __FILE__);
?>" width="300"></a>
</p>
<p><a href="http://wpguru.co.uk/2010/12/disk-space-pie-chart-plugin/" target="_blank">Plugin by Jay Versluis</a> | <a href="http://www.peters1.dk/webtools/php/lagkage.php?sprog=en" target="_blank">Pie Chart Script by Rasmus Peters</a> | <a href="http://wphosting.tv" target="_blank">WP Hosting</a> | <a href="http://wpguru.co.uk/say-thanks/" target="_blank">Buy me a Coffee</a> ;-)</p>

<?php

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

// check disk usage and pop into a variable
$output = substr(shell_exec('pwd'),0,-9);
$usedspace = substr(shell_exec('du -s ' . $output),0,-(strlen($output)+1));

// check for total space available
$opt_val = get_option('guru_space');
$opt_val2 = get_option(guru_unit);

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
<li><strong><?php _e('Disk Space Used: '); ?></strong> : <span><?php echo round(($usedspace / $spacecalc),2) . " " . $opt_val2; ?> </span> | 
<strong><?php _e('Disk Space Free: '); ?></strong> : <span>

<?php 
// figure out a way to test the variable...
if (!$opt_val) echo "UNLIMITED";
else echo round(($freespace / $spacecalc),2) . " " . $opt_val2; 
?> </span> | <a href="?page=guruspace-admin">Pie Chart and Setup</a></li>
</ul>

<?php if (!empty($this->memory['percent'])) : ?>

<?php 
// thanks to Jure for the Division by Zero bugfix
$perc = $totalspace==false ? 100 : round(($usedspace / ($totalspace / 100)),1); ?>
<div class="progressbar">
<div class="" style="height:2em; border:1px solid #DDDDDD; background-color: #0055cc">
<div class="" style="width: <?php echo $perc; ?>%;height:100%;background-color:#f55;;border-width:0px;text-shadow:0 1px 0 #000000;color:#FFFFFF;text-align:right;font-weight:bold;">
<div style="padding:6px"><?php echo $perc; ?>%</div></div>
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

	}

	// Start this plugin once all other plugins are fully loaded
	add_action( 'plugins_loaded', create_function('', '$memory = new wp_memory_usage();') );
}