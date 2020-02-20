<?
/*
Plugin Name: bb Topic Icons
Plugin URI: http://devt.caffeinatedbliss.com/bbpress/topic-icons
Description: Adds configurable icons next to topics based on their status
Author: Paul Hawke
Author URI: http://paul.caffeinatedbliss.com/
Version: 0.6
*/

/****************************************************************************
 *
 * Configure the following constants to fine-tune the CSS classes that are
 * generated, the icon filenames that are used, and the text used in the
 * legend (if you have one displayed).  Note: filenames are likely to be
 * taken away in a future version and replaced with the concept of "icon sets"
 * whose filenames are fixed, so dont get used to editing the filenames,
 * as this will break in future versions.
 *
 ****************************************************************************/

// css class for the unsorted list used in the legend display
define( LEGEND_CLASS, 'topic_icon_legend' );

// busy threshold - a topic with more posts than this is counted as "busy"
// for purposes of picking an icon.
define( BUSY_THRESHOLD, 15 );

// width of the images, in pixels
define( ICON_WIDTH, '20' );

// height of the images, in pixels
define( ICON_HEIGHT, '20' );

// the URL base for where to find the default icon set.
define( ICON_SET_URL_BASE, BB_PLUGIN_URL.'bb-topic-icons/icon-sets/' );

/****************************************************************************
 *
 * Shouldnt be much need to edit anything beyond this point - configuration
 * is all done via the constants (above) and through and admin area page in
 * bbPress at runtime.
 *
 ****************************************************************************/

require( 'bb-topic-icons-api.php' );
require( 'bb-topic-icons-admin.php' );
require( 'interface.status-interpreter.php' );
require( 'interface.status-renderer.php' );
require( 'class.default-status-interpreter.php' );
require( 'class.default-status-renderer.php' );

function topic_icons_legend() {
	$icon_set_name = topic_icons_get_active_icon_set();
	$icon_set_url = ICON_SET_URL_BASE . $icon_set_name;
	$statuses = get_active_status_interpreter()->getAllStatuses();
	$renderer = get_active_status_renderer();
	
	echo '<ul id="'.LEGEND_CLASS.'">';
	for ($i=0; $i < count($statuses); $i++) {
		$image = $renderer->renderStatus($statuses[$i]);
		$tooltip = $renderer->renderStatusTooltip($statuses[$i]);
		$exists = file_exists(dirname(__FILE__).'/icon-sets/'.$icon_set_name.'/'.$image);

		if (isset($image) && strlen($image) > 0 &&
			isset($tooltip) && strlen($tooltip) > 0 && $exists) {
			echo '<li><img src="'.$icon_set_url.'/'.$image.
				'" width="'.ICON_WIDTH.'" height="'.ICON_HEIGHT.
				'" align="absmiddle">&nbsp;'.$tooltip.'</li>';
		}
	}
	echo '</ul>';
}

function topic_icons_css() {
	echo "\n<style type=\"text/css\"><!--\n";
	require( 'bb-topic-icons.css' );
	echo "\n--></style>";
}

function topic_icons_label( $label ) {
	global $topic;
	
	if (bb_is_front() || bb_is_forum() || bb_is_view() || bb_is_tag()) {		
		$icon_set_name = topic_icons_get_active_icon_set();
		$icon_set_url = ICON_SET_URL_BASE . $icon_set_name;

		$status = get_active_status_interpreter()->getStatus(bb_get_location(), $topic);
		$renderer = get_active_status_renderer();
		$image = $renderer->renderStatus($status);
		$tooltip = $renderer->renderStatusTooltip($status);
		$exists = file_exists(dirname(__FILE__).'/icon-sets/'.$icon_set_name.'/'.$image);

		if (!$exists) {
			return sprintf(__('<div class="topic-icon-image"><a href="%s"><img src="%s" width="%s" height="%s" alt="%s" border="0"></a></div> %s'), 
				get_topic_link($topic->topic_id), ICON_SET_URL_BASE.'/empty.png', ICON_WIDTH, ICON_HEIGHT, $tooltip, $label);
		} else if (strlen($tooltip) > 0) {		
			return sprintf(__('<div class="topic-icon-image"><a href="%s"><img src="%s" width="%s" height="%s" alt="%s" border="0"><span>%s</span></a></div> %s'), 
				get_topic_link($topic->topic_id), $icon_set_url.'/'.$image, ICON_WIDTH, ICON_HEIGHT, $tooltip, $tooltip, $label);
		} else {
			return sprintf(__('<div class="topic-icon-image"><a href="%s"><img src="%s" width="%s" height="%s" alt="%s" border="0"></a></div> %s'), 
				get_topic_link($topic->topic_id), $icon_set_url.'/'.$image, ICON_WIDTH, ICON_HEIGHT, $tooltip, $label);
		}
	}
	
	return $label;
}

function topic_icons_init( ) {
	remove_filter('bb_topic_labels', 'bb_closed_label', 10);
	remove_filter('bb_topic_labels', 'bb_sticky_label', 20);

	add_filter('bb_topic_labels', 'topic_icons_label', 11);

	add_action('bb_head', 'topic_icons_css');

	add_action('bb_admin_menu_generator', 'topic_icons_admin_page_add');
	add_action('bb_admin-header.php', 'topic_icons_admin_page_process');
	
	topic_icons_register_status_interpreter('default', new DefaultStatusInterpreter(BUSY_THRESHOLD));
	topic_icons_register_status_renderer('default', new DefaultStatusRenderer());
}

topic_icons_init();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title>Digital Humanities Questions &amp; Answers</title>
	<link rel="stylesheet" href="http://digitalhumanities.org/answers/bb-templates/dhqa/style.css" type="text/css" />
	<link rel="stylesheet" href="http://digitalhumanities.org/answers/bb-templates/dhqa/blueprint/screen.css" type="text/css" media="screen" title="blueprint-print" charset="utf-8"/>
	<link rel="stylesheet" href="http://digitalhumanities.org/answers/bb-templates/dhqa/blueprint/print.css" type="text/css" media="print" title="blueprint-print" charset="utf-8"/>
	<!--[if lt IE 8]>
	  <link rel="stylesheet" href="http://digitalhumanities.org/answers/bb-templates/dhqa/blueprint/ie.css" type="text/css" media="screen, projection">
	<![endif]-->
	<!--[if IE]>
		<style type="text/css" media="screen">
			.header-button, form.login input, input[type="submit"], #header div.search, form.login input {
				border-radius:4px;
				behavior: url(border-radius.htc);
			}
		</style>
	<![endif]-->
	<!--[if IE 7]>
		<style type="text/css" media="screen">
			#header .search .search-form input.submit {
				padding: 0;
				margin: 0;
				height: 29px;
				width: 70px;
			}
		</style>
	<![endif]-->


<meta name="generator" content="bbPress 1.0.2" />

<link rel="stylesheet" href="http://digitalhumanities.org/answers/my-plugins/bb-syntax/bb-syntax.css" type="text/css" media="screen" />
<style type="text/css">
	.best_answer {text-decoration:none; border:0; white-space:nowrap; display:block; margin:1em 0;		
		border:0; text-decoration:none; width:20px; height:16px;
		background: url(http://digitalhumanities.org/answers/my-plugins/best-answer/best-answer.png) no-repeat scroll 0px 1px transparent;} 
	span.best_answer {margin: 0px; float: left;}
	#thread .threadauthor .best_answer {padding-left: 20px;}
	a.best_answer, a.best_answer:link {color:#999;}
	a.best_answer:hover {color:green;}	
	a.best_answer_selected {color: green;}
	a.best_answer_selected:hover {color:red;}		
	#thread li.best_answer_background { background-color: transparent; }
	#thread li.best_answer_background .threadpost { background-color: #afa; }
	#thread li.alt.best_answer_background .threadpost { background-color: #afa; }
	.best_answer_meta { background: url(http://digitalhumanities.org/answers/my-plugins/best-answer/best-answer.png) no-repeat scroll 5px 40% transparent; padding-left: 26px; }
</style>

<!-- Start Of Code Generated By Social It Plugin By www.gaut.am -->
<link rel='stylesheet' id='social-it-css'  href='http://digitalhumanities.org/answers/my-plugins/social-it/css/style.css?ver=1.5' type='text/css' media='all' />
<script type='text/javascript' src='http://digitalhumanities.org/answers/bb-includes/js/jquery/jquery.js?ver=1.2.6'></script>
<script type='text/javascript' src='http://digitalhumanities.org/answers/my-plugins/social-it/js/social-it-public.js?ver=1.5'></script>
<!-- End Of Code Generated By Social It Plugin By www.gaut.am -->


<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-18811436-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</head>
<body id="login-page">
	<div class="container prepend-top append-bottom">
		<div id="util-login">
		<a href="http://digitalhumanities.org/answers/bb-login.php">Log in</a> | <a href="http://digitalhumanities.org/answers/register.php">Register</a>		</div>
		<div id="header" class="prepend-6 span-18">
			<a id="ach-logo" href="http://www.ach.org">ACH</a>
			<h1><a href="http://digitalhumanities.org/answers/">Digital Humanities Questions &amp; Answers</a></h1>
						<div class="login-container span-12">
							</div>

			<div class="search span-6 last">
<form class="search-form" role="search" action="http://digitalhumanities.org/answers/search.php" method="get">
	<p>
		<label class="hidden" for="q">Search:</label>
		<input type="text" size="10" maxlength="100" name="q" id="q" />
		<input class="submit" type="submit" value="Search &raquo;" />
	</p>
</form>
			</div>
		</div>
		<div id="main" class="span-24">

<div class="bbcrumb"><a href="http://digitalhumanities.org/answers/">Digital Humanities Questions &amp; Answers</a> &raquo; Log in</div>

<form class="login-form" method="post" action="http://digitalhumanities.org/answers/bb-login.php">
<h2 id="userlogin" role="main">Log in</h2>
<fieldset>
	<legend>With OpenID <span id="openid-note">(GMail, Yahoo!, or other <a href='http://openid.net/get-an-openid/'>OpenID account</a>)</span></legend>
	<table>
		<tbody>
		<tr class="form-field">
			<th scope="row">
				<label for="openid_url">OpenID</label>
			</th>
			<td>
				<input id="openid_identity" name="openid_identity" type="text"/>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
<fieldset>
<legend>With DHAnswers ID</legend>
<table>
	<tr valign="top" class="form-field ">
		<th scope="row">
			<label for="user_login">User name</label>
								</th>
		<td>
			<input name="user_login" id="user_login" type="text" value="" />
		</td>
	</tr>
	<tr valign="top" class="form-field ">
		<th scope="row">
			<label for="password">Password</label>
					</th>
		<td>
			<input name="password" id="password" type="password" />
		</td>
	</tr>

	<tr valign="top" class="form-field">
		<th scope="row"><label for="remember">Remember me</label></th>
		<td><input name="remember" type="checkbox" id="remember" value="1" /></td>
	</tr>
	<tr>
		<th scope="row">&nbsp;</th>
		<td>
			<input name="re" type="hidden" value="http://digitalhumanities.org/answers/" />
			<input type="submit" value="Log in &raquo;" />
			<input type="hidden" name="_wp_http_referer" value="/answers/bb-login.php" />		</td>
	</tr>
</table>

</fieldset>
</form>
<div class="register-container">
<h2 id="userreg" role="main">Register</h2>
	<a href="http://digitalhumanities.org/answers/register.php" id="register-link">Create a New Account &raquo;</a></div>
<h2 id="passwordrecovery">Password Recovery</h2>
<form method="post" action="http://digitalhumanities.org/answers/bb-reset-password.php">
<fieldset>
	<p>To recover your password, enter your information below.</p>
	<table>
		<tr valign="top" class="form-field">
			<th scope="row">
				<label for="user_login_reset_password">Username</label>
			</th>
			<td>
				<input name="user_login" id="user_login_reset_password" type="text" value="" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"></th>
			<td>
				<input type="submit" value="Recover Password &raquo;" />
			</td>
		</tr>
	</table>
</fieldset>
</form>

		</div>
			<div id="footer" role="contentinfo" class="span-24">
				<div id="lang-switch">
					<span class="lang-label">Language</span>
					<form id="bb_language_switcher" style="display:inline-block;"><select  style="width:150px;" name="bb_language_switcher" onchange="location.href='/answers/bb-login.php?bblang=' + this.options[this.selectedIndex].value;">
<option value=''>Select...</option>	<option value="bg_BG">&nbsp;Bulgarian (BG) </option>
	<option value="continents-cities-sr_RS">&nbsp;Corsican</option>
	<option value="da_DK">&nbsp;Danish (DK) </option>
	<option style='background:#ECE9D8;color:#000;font-weight:bold;' value=" ">&nbsp;English</option>
	<option value="et_EE">&nbsp;Estonian (EE) </option>
	<option value="fr_FR">&nbsp;French (FR) </option>
	<option value="de">&nbsp;German</option>
	<option value="he_IL">&nbsp;Hebrew (IL) </option>
	<option value="hu_HU">&nbsp;Hungarian (HU) </option>
	<option value="id_ID">&nbsp;Indonesian (ID) </option>
	<option value="it_IT">&nbsp;Italian (IT) </option>
	<option value="ja">&nbsp;Japanese</option>
	<option value="pl_PL">&nbsp;Polish (PL) </option>
	<option value="pt_BR">&nbsp;Portuguese (BR) </option>
	<option value="pt_PT">&nbsp;Portuguese (PT) </option>
	<option value="ro_RO">&nbsp;Romanian (RO) </option>
	<option value="ru_RU">&nbsp;Russian (RU) </option>
	<option value="sr_RS">&nbsp;Serbian (RS) </option>
	<option value="sk">&nbsp;Slovak</option>
	<option value="sk_SK">&nbsp;Slovak (SK) </option>
	<option value="es_ES">&nbsp;Spanish (ES) </option>
	<option value="sv_SE">&nbsp;Swedish (SE) </option>
	<option value="th">&nbsp;Thai</option>
	<option value="ug_CN">&nbsp;Uyghur (CN) </option>
	<option value="uz_UZ">&nbsp;Uzbek (UZ) </option>
	<option value="vi">&nbsp;Vietnamese</option>
</select></form>
				</div>			
				<p>A project of the <a href="http://www.ach.org">Association for Computers and the Humanities</a> and the Chronicle of Higher Education's <a href="http://profhacker.com">ProfHacker</a></p>
				<p>Icons by <a href="http://www.famfamfam.com/lab/icons/silk/">famfamfam</a></p>
				<p><a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/"><img alt="Creative Commons License" src="http://i.creativecommons.org/l/by-nc-sa/3.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License</a>.</p>
				<p><a href="http://lib.virginia.edu/scholarslab"><img src="http://digitalhumanities.org/answers/bb-templates/dhqa/images/slab-logo.jpg" alt="Scholars' Lab" title="Scholars' Lab"/></a></p>
			</div>
	</div>


</body>
</html>
