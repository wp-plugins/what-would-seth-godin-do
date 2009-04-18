<?php

/*
Plugin Name: What Would Seth Godin Do
Plugin URI: http://www.richardkmiller.com/blog/wordpress-plugin-what-would-seth-godin-do/
Description: Displays a custom welcome message to new visitors
Version: 1.3
Author: Richard K Miller
Author URI: http://www.richardkmiller.com/blog/

Copyright (c) 2006-2007 Richard K Miller
Released under the GNU General Public License (GPL)
http://www.gnu.org/licenses/gpl.txt
*/

$defaultdata = array(
	'new_visitor_message' => "<p style='border:thin dotted black; padding:3mm;'>If you're new here, you may want to subscribe to my <a href='".get_option('home')."/feed/'>RSS feed</a>.  Thanks for visiting!</p>",
	'message_location' => 'before_post',
	'repetition' => '3'
	);
	
add_option('wwsgd_settings', $defaultdata, 'Options for What Would Seth Godin Do');

$wwsgd_settings = get_option('wwsgd_settings');
$wwsgd_settings['new_visitor_message'] = stripslashes($wwsgd_settings['new_visitor_message']);

add_action('admin_menu', 'add_wwsgd_options_page');

add_action('init', 'wwsgd_set_cookie');

add_filter('the_content', 'wwsgd_message_filter');

function add_wwsgd_options_page()
{
	if (function_exists('add_options_page'))
	{
		add_options_page('What Would Seth Godin Do', 'WWSGD', 8, basename(__FILE__), 'wwsgd_options_subpanel');
	}
}

function wwsgd_options_subpanel()
{
	global $wwsgd_settings, $_POST;
	
	if (isset($_POST['submit']))
	{
		$wwsgd_settings['new_visitor_message'] = stripslashes($_POST['new_visitor_message']);
		$wwsgd_settings['message_location'] = $_POST['message_location'];
		$wwsgd_settings['repetition'] = $_POST['repetition'];
		
		update_option('wwsgd_settings', $wwsgd_settings);
	}
	?>
	<div class="wrap">
		<h2>What Would Seth Godin Do</h2>
		<p>"One opportunity that's underused is the idea of using cookies to treat returning visitors differently than newbies...." - <a href="http://sethgodin.typepad.com/seths_blog/2006/08/in_the_middle_s.html" target="_blank">Seth Godin, August 17, 2006</a></p>
		<form action="" method="post">
		<h3>Message to New Visitors:</h3>
		<textarea rows="4" cols="80" name="new_visitor_message"><?php echo htmlentities($wwsgd_settings['new_visitor_message']); ?></textarea>
		<h3>Location of Message</h3>
		<p><input type="radio" name="message_location" value="before_post" <?php if ($wwsgd_settings['message_location'] == 'before_post') echo 'checked="checked"'; ?> /> Before Post</p>
		<p><input type="radio" name="message_location" value="after_post" <?php if ($wwsgd_settings['message_location'] == 'after_post') echo 'checked="checked"'; ?> /> After Post</p>
		<h3>Repetition</h3>
		<p>Show the message the first <input type="text" name="repetition" value="<?php echo $wwsgd_settings['repetition']; ?>" size="3" /> times the user visits your blog. (Enter "0" to always display the message.)</p>
		<p><input type="submit" name="submit" value="Save Settings" /></p>
		</form>
		</div>
	<?php
}

function wwsgd_set_cookie()
{
	global $wwsgd_visits;
	
	if (!is_admin())
	{
		if (isset($_COOKIE['wwsgd_visits']))
		{
			$wwsgd_visits = $_COOKIE['wwsgd_visits'] + 1;
		}
		else
		{
			$wwsgd_visits = 1;
		}
		$url = parse_url(get_option('home'));
		setcookie('wwsgd_visits', $wwsgd_visits, time()+60*60*24*365, $url['path'] . '/');
	}
}

function wwsgd_message_filter($content = '')
{
	global $wwsgd_visits, $wwsgd_settings, $wwsgd_messagedisplayed;
	
	if (($wwsgd_visits <= $wwsgd_settings['repetition'] || 0 == $wwsgd_settings['repetition']) && !is_feed() && !$wwsgd_messagedisplayed)
	{
		$wwsgd_messagedisplayed = true;
		if ($wwsgd_settings['message_location'] == 'before_post')
		{
			return
			$wwsgd_settings['new_visitor_message']
			.
			$content
			;
		}
		else
		{
			return
			$content
			.
			$wwsgd_settings['new_visitor_message']
			;
		}
	}
	else
	{
		return $content;
	}
}

?>
