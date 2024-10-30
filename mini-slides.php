<?php
/*
Plugin Name: Mini Slides
Plugin URI: http://wordpress.designpraxis.at
Description: A filter for displaying all images attached to a post as a mini slide show. Comes with Mini-Slides Templates. Go to Presentation > <a href="themes.php?page=mini-slides">Mini-Slides</a> for options. Makes optional use of <a href="http://zeo.unic.net.my/notes/lightbox2-for-wordpress/">Lightbox2 for WordPress</a><br />Use [minislides] within your post and &lt;?php dprx_display_minislides(); ?&gt; in your templates
Version: 2.0
Author: Roland Rust
Author URI: http://wordpress.designpraxis.at
*/

/* 
CHANGELOG

Changes in v 2.0

- sidebar template tag removed, [minislides] only within post content
- internationalisation added
- templating feature added

Changes in v 1.2

- added options page
- template support

Changes in v 1.1

1. Click on the Thumbnail brings up Lightbox2/Fullsize image
*/

add_action('init', 'dprx_minislides_init_locale',98);
function dprx_minislides_init_locale() {
	$locale = get_locale();
	$mofile = dirname(__FILE__) . "/locale/".$locale.".mo";
	load_textdomain('dprx_minislides', $mofile);
}

add_action('admin_menu', 'dprx_minislides_add_admin_pages');
function dprx_minislides_add_admin_pages() {
	add_submenu_page('themes.php', 'Mini-Slides', 'Mini-Slides', 10, __FILE__, 'dprx_minislides_manage_page');
}

function dprx_minislides_load_template_by_hash($hash) {
	$templates = get_option("dprx_minislides_templates");
	if (!is_array($templates)) { return false; }
	foreach ($templates as $t) {
		if ($t['templatehash'] == $hash) {
			return $t;
		}
	}
}

function dprx_minislides_load_template_by_name($name) {
	$templates = get_option("dprx_minislides_templates");
	if (!is_array($templates)) { return false; }
	foreach ($templates as $t) {
		if ($t['dprx_minislides_templatename'] == $name) {
			return $t;
		}
	}
}

function dprx_minislides_load_current_template($current="") {
	if (empty($current)) {
		$templates = get_option("dprx_minislides_templates");
		$current = get_option("dprx_minislides_current_template");
	}
	if (!is_array($templates)) { return false; }
	foreach ($templates as $t) {
		if ($t['dprx_minislides_templatename'] == $current) {
			return $t;
		}
	}
	return false;
}

function dprx_minislides_build_template($template) {
	ob_start();
	?>
	<style type="text/css" media="screen">
	.dprx_minislides<?php echo $template['templatehash']; ?> a {
		border: none;
	}
	.dprx_minislides<?php echo $template['templatehash']; ?> {
		<?php 
		if (!empty($template['dprx_minislides_outerborder_color']) && empty($template['dprx_minislides_outerborder_width'])) {
			$template['dprx_minislides_outerborder_width'] = "1px";
		}
		if (!empty($template['dprx_minislides_outerborder_width'])) { 
			if(!ereg("px", $template['dprx_minislides_outerborder_width'])) {
				$template['dprx_minislides_outerborder_width'] .= "px";
			}
				?>
		border: <?php echo $template['dprx_minislides_outerborder_width']; ?> solid <?php echo $template['dprx_minislides_outerborder_color']; ?>;
		<?php } ?>
		background: <?php echo $template['dprx_minislides_background_color']; ?>;
		<?php if (!empty($template['dprx_minislides_font_size'])) { ?>
		font-size: <?php echo $template['dprx_minislides_font_size']; ?>;
		<?php } ?>
		width: 130px;
		padding: 5px;
		margin-bottom: 5px;
	}
		
	.dprx_minislides<?php echo $template['templatehash']; ?> .dprx_minislides_nav {
		position: relative;
		margin-top: 3px;
	}
	
	.dprx_minislides<?php echo $template['templatehash']; ?> .dprx_minislides_content a {
		border: none;
	}
		
	.dprx_minislides<?php echo $template['templatehash']; ?> .dprx_minislides_nav a {
		border: none;
		padding-left: 3px;
	}
	.dprx_minislides<?php echo $template['templatehash']; ?> .dprx_minislides_content {
		<?php 
		if (!empty($template['dprx_minislides_border_color']) && empty($template['dprx_minislides_border_width'])) {
			$template['dprx_minislides_border_width'] = "1px";
		}
		if (!empty($template['dprx_minislides_border_width'])) { 
			if(!ereg("px", $template['dprx_minislides_border_width'])) {
				$template['dprx_minislides_border_width'] .= "px";
			}
			
			?>
		border: <?php echo $template['dprx_minislides_border_width']; ?> solid <?php echo $template['dprx_minislides_border_color']; ?>;
		<?php } ?>
		margin: auto;
		text-align: center;
	}
	.dprx_minislides<?php echo $template['templatehash']; ?> .dprx_pwrd {
		position: absolute;
		letter-spacing: -0.1em;
		right: 1px;
		bottom: 1px;
		font-size: 10px;
		padding-right: 3px;
	}
	</style>
	<?php
	$template['templatecss'] = ob_get_contents();
	ob_end_clean();
	return $template;
}

if (ereg("mini-slides",$_REQUEST['page'])) {
add_action('admin_head', 'dprx_minislides_loadtemplatecss',20);
}
add_action('wp_head', 'dprx_minislides_loadtemplatecss',20);

function dprx_minislides_loadtemplatecss() {
	// for admins
	if (ereg("mini-slides",$_REQUEST['page'])) {
		if (!empty($_REQUEST['dele'])) {
			$newtemplates = array();
			$templates = get_option("dprx_minislides_templates");
			if (is_array($templates)) {
				foreach($templates as $t) {
					if ($t['templatehash'] == $_REQUEST['dele']) { 
						$GLOBALS['dprx_minislides_deletesuccess'] = $t['dprx_minislides_templatename']; 
						$current = get_option("dprx_minislides_current_template");
						if ($current == $t['dprx_minislides_templatename']) {
							delete_option("dprx_minislides_current_template");
						}
						continue; 
						}
					$newtemplates[] = $t;
				}
			}
			update_option("dprx_minislides_templates",$newtemplates);
		}

		if (!empty($_REQUEST['dprx_minislides_templatename'])) {
			$template = array();
			foreach($_REQUEST as $key => $value) {
				if ($key == "dprx_minislides_default_template") {
					if (!empty($value)) {
						update_option("dprx_minislides_current_template",$_REQUEST['dprx_minislides_templatename']);
					}
				} elseif(eregi("dprx_minislides", $key)) {
					$template[$key] = $value;
				}
				$template['templatehash'] = md5($_REQUEST['dprx_minislides_templatename']);
			}
			$finaltemplate = dprx_minislides_build_template($template);
			if (!empty($finaltemplate)) {
				$templates = get_option("dprx_minislides_templates");
				$newtemplates = array();
				if (is_array($templates)) {
					foreach($templates as $t) {
						if($_REQUEST['dprx_minislides_templatename'] == $t['dprx_minislides_templatename']) {
						continue;
						}
						$newtemplates[] = $t;
					}
				}
				$newtemplates[] = $finaltemplate;
				update_option("dprx_minislides_templates",$newtemplates);
				$GLOBALS['dprx_minislides_createsuccess'] = $t['dprx_minislides_templatename'];
			}
		}
		
		$templates = get_option("dprx_minislides_templates");
		if (is_array($templates)) {
			foreach($templates as $t) {
				echo $t['templatecss']."\n";
			}
		}
	}
	
	
	$template = dprx_minislides_load_current_template();
	
	if (!empty($GLOBALS['post']->post_content)) {
		preg_match("/(.*?)\[minislides(.*)(\])(.*?)/", $GLOBALS['post']->post_content, $match);
		if ($match) {
			$templatename = str_replace("#","",$match[2]);
			if (!empty($templatename)) {
				$template = dprx_minislides_load_template_by_name($templatename);
			}
		} 
	}

	if (!is_array($template)) { dprx_minislides_loadcss(); return; }
	echo $template['templatecss'];
}

function dprx_minislides_loadcss() {
	?>
	<style type="text/css" media="screen">
	.dprx_minislides a {
		border: none;
	}
	.dprx_minislides {
		border: 1px solid #eee;
		padding: 5px;
		background: #eee;
		width: 130px;
		margin-bottom: 5px;
	}
	
	.dprx_minislides .dprx_minislides_nav {
		position: relative;
		margin-top: 3px;
	}
	
	.dprx_minislides .dprx_minislides_content a {
		border: none;
	}
	
	.dprx_minislides .dprx_minislides_nav a {
		border: none;
		padding-left: 3px;
	}
	.dprx_minislides .dprx_minislides_content {
		border: 1px solid #ccc;
		margin: auto;
		text-align: center;
	}
	.dprx_minislides .dprx_pwrd {
		position: absolute;
		letter-spacing: -0.1em;
		right: 1px;
		bottom: 1px;
		font-size: 10px;
		padding-right: 3px;
	}
	</style>
	<?php
}

function dprx_minislides_get_theme() {
	$theme = get_bloginfo("template_directory");
	$theme = explode("/",$theme);
	$theme = array_reverse($theme);
	return $theme[0];
}

function dprx_minislides_manage_page() {
		// display the working template's default
				$GLOBALS['post']->ID = "9999";
		$test_minislides = array();
		for($i=1; $i<=12; $i++) {
			$test_minislides[$i]->ID = $i;
			$test_minislides[$i]->post_parent = "9999";
			$test_minislides[$i]->post_title = "minislides demo";
			$test_minislides[$i]->guid = get_bloginfo("wpurl")."/wp-content/plugins/mini-slides/demo/".$i.".jpg";
		}
	?>
	<div class=wrap>
	<?php
	if (!empty($GLOBALS['dprx_minislides_deletesuccess'])) {
	?>
	<div id="message" class="updated fade">
	<p>
	<?php _e('Your Mini-Slides Template',"dprx_minislides") ?>
	<b><?php echo $GLOBALS['dprx_minislides_deletesuccess']; ?></b>
	<?php _e('has been successfully deleted',"dprx_minislides") ?>.
	</p>
	</div>
	<p />
	<?php
	} elseif (!empty($GLOBALS['dprx_minislides_createsuccess'])) {
	?>
	<div id="message" class="updated fade">
	<p>
	<?php _e('Your Mini-Slides Template',"dprx_minislides") ?>
	<b><?php echo $GLOBALS['dprx_minislides_createsuccess']; ?></b>
	<?php _e('has been successfully created',"dprx_minislides") ?>.
	</p>
	</div>
	<p />
	<?php
	}
	?>
		<h2><?php _e('Mini-Slides',"dprx_minislides") ?></h2>
		<div style="float:left; padding-right:20px;">
			<h3><?php _e('This is how minislides will appear on your blog',"dprx_minislides") ?></h3>
			<?php
			$current = dprx_minislides_load_current_template();
			echo dprx_build_minislides($test_minislides,"",$current);
			?>
			<p />
			<?php _e('use',"dprx_minislides") ?>: [minislides]
			<?php _e('within your post',"dprx_minislides") ?>
			<p>
			<?php _e('Demo Images from',"dprx_minislides") ?>
			<a href="http://www.orthochrome.com">Orthochrome.com &copy;</a>
			</p>
				
		</div>
					
		<div style="float:left;padding-left:20px; border-left: 1px solid #000;">
			<div style="float:left; padding-left, padding-right: 10px;">
			<h3><?php _e("Create a new Template","dprx_minislides"); ?></h3>
			<?php
			if (!empty($_REQUEST['edit'])) {
				$loaded = dprx_minislides_load_template_by_hash($_REQUEST['edit']);
				echo dprx_build_minislides($test_minislides,1,$loaded);
			} else {
				echo dprx_build_minislides($test_minislides,1,$current);
			}
			?>
			<p>
			<?php _e("New to Hex Color Codes? Grab Firefox ","dprx_minislides"); ?>
			<p>
			<script type="text/javascript"><!--
			google_ad_client = "pub-2034490295300885";
			google_ad_width = 180;
			google_ad_height = 60;
			google_ad_format = "180x60_as_rimg";
			google_cpa_choice = "CAEQz6jzzwEQyaj8zwEaCF4EFrOSN6T_KJ-093Moy7b3cw";
			//-->
			</script>
			<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
			</script>
			</p>
			<?php _e("and the Firefox Extension","dprx_minislides"); ?>
			<a href="http://www.iosart.com/firefox/colorzilla/">Colorzilla</a>
			</p>
			</div>
			<div style="float:left; padding: 10px;">
				<form name="form1" method="post" action="<?php bloginfo("wpurl"); ?>/wp-admin/admin.php?page=<?php echo $_REQUEST['page']; ?>">
				<table>
				<tbody>
					<tr valign="top">
					<th scope="row"><?php _e("Template Name","dprx_minislides"); ?>: </th>
					<td>
					<input type="text" name="dprx_minislides_templatename" value="<?php echo $loaded['dprx_minislides_templatename']; ?>" />
					</td>
					</tr>
					
					<tr valign="top">
					<th colspan="2" scope="row"><?php _e("Mini Slides Background","dprx_minislides"); ?></th>
					</tr>
					
					<tr valign="top">
					<td scope="row"><?php _e("Color","dprx_minislides"); ?>: </td>
					<td>
					<input type="text" 
						name="dprx_minislides_background_color" 
						id="dprx_minislides_background_color" 
						value="<?php echo $loaded['dprx_minislides_background_color']; ?>" 
						onfocus="document.getElementById('dprx_minislides_demo').style.background = document.getElementById('dprx_minislides_background_color').value;" 
						onblur="document.getElementById('dprx_minislides_demo').style.background = document.getElementById('dprx_minislides_background_color').value;" 
						onkeyup="document.getElementById('dprx_minislides_demo').style.background = document.getElementById('dprx_minislides_background_color').value;" 
						/> <?php _e("#hex code","dprx_minislides"); ?>
					</td>
					</tr>
					
					<tr valign="top">
					<td scope="row"><?php _e("Font Size","dprx_minislides"); ?>: </td>
					<td>
					<input type="text" 
						name="dprx_minislides_font_size" 
						id="dprx_minislides_font_size" 
						value="<?php echo $loaded['dprx_minislides_font_size']; ?>" 
						onfocus="document.getElementById('dprx_minislides_nav_demo').style.fontSize = document.getElementById('dprx_minislides_font_size').value;" 
						onblur="document.getElementById('dprx_minislides_nav_demo').style.fontSize = document.getElementById('dprx_minislides_font_size').value;" 
						onkeyup="document.getElementById('dprx_minislides_nav_demo').style.fontSize = document.getElementById('dprx_minislides_font_size').value;" 
						/> <?php _e("size, em, px, %","dprx_minislides"); ?>
					</td>
					</tr>
					
					<tr valign="top">
					<th colspan="2" scope="row"><?php _e("Mini Slides Outer Border","dprx_minislides"); ?></th>
					</tr>
					
					<tr valign="top">
					<td scope="row"><?php _e("Color","dprx_minislides"); ?>: </td>
					<td>
					<input type="text" 
						name="dprx_minislides_outerborder_color" 
						id="dprx_minislides_outerborder_color" 
						value="<?php echo $loaded['dprx_minislides_outerborder_color']; ?>" 
						onfocus="document.getElementById('dprx_minislides_demo').style.borderColor = document.getElementById('dprx_minislides_outerborder_color').value;" 
						onblur="document.getElementById('dprx_minislides_demo').style.borderColor = document.getElementById('dprx_minislides_outerborder_color').value;" 
						onkeyup="document.getElementById('dprx_minislides_demo').style.borderColor = document.getElementById('dprx_minislides_outerborder_color').value;" 
						/> <?php _e("#hex code","dprx_minislides"); ?>
					</td>
					</tr>
					
					<tr valign="top">
					<td scope="row"><?php _e("Width","dprx_minislides"); ?>: </td>
					<td>
					<input type="text" 
						name="dprx_minislides_outerborder_width" 
						id="dprx_minislides_outerborder_width" 
						value="<?php echo $loaded['dprx_minislides_outerborder_width']; ?>" 
						onfocus="document.getElementById('dprx_minislides_demo').style.borderWidth = document.getElementById('dprx_minislides_outerborder_width').value;" 
						onblur="document.getElementById('dprx_minislides_demo').style.borderWidth = document.getElementById('dprx_minislides_outerborder_width').value;" 
						onkeyup="document.getElementById('dprx_minislides_demo').style.borderWidth = document.getElementById('dprx_minislides_outerborder_width').value;" 
						/> <?php _e("px","dprx_minislides"); ?>
					</td>
					</tr>
					
					<tr valign="top">
					<th colspan="2" scope="row"><?php _e("Mini Slides Inner Border","dprx_minislides"); ?>: </th>
					</tr>
					
					<tr valign="top">
					<td scope="row"><?php _e("Color","dprx_minislides"); ?>: </td>
					<td>
					<input type="text" 
						name="dprx_minislides_border_color" 
						id="dprx_minislides_border_color" 
						value="<?php echo $loaded['dprx_minislides_border_color']; ?>" 
						onfocus="document.getElementById('dprx_minislides_content_demo').style.borderColor = document.getElementById('dprx_minislides_border_color').value;" 
						onblur="document.getElementById('dprx_minislides_content_demo').style.borderColor = document.getElementById('dprx_minislides_border_color').value;" 
						onkeyup="document.getElementById('dprx_minislides_content_demo').style.borderColor = document.getElementById('dprx_minislides_border_color').value;" 
						/> <?php _e("#hex code","dprx_minislides"); ?>
					</td>
					</tr>
					
					<tr valign="top">
					<td scope="row"><?php _e("Width","dprx_minislides"); ?>: </td>
					<td>
					<input type="text" 
						name="dprx_minislides_border_width" 
						id="dprx_minislides_border_width" 
						value="<?php echo $loaded['dprx_minislides_border_width']; ?>" 
						onfocus="document.getElementById('dprx_minislides_content_demo').style.borderWidth = document.getElementById('dprx_minislides_border_width').value;" 
						onblur="document.getElementById('dprx_minislides_content_demo').style.borderWidth = document.getElementById('dprx_minislides_border_width').value;" 
						onkeyup="document.getElementById('dprx_minislides_content_demo').style.borderWidth = document.getElementById('dprx_minislides_border_width').value;" 
						/> <?php _e("px","dprx_minislides"); ?>
					</td>
					</tr>
					
					<tr valign="top">
					<td colspan="2" scope="row"><?php _e("Load this Template as Default Template?","dprx_minislides"); ?>
					<input type="checkbox" name="dprx_minislides_default_template" value="1" /></td>
					</tr>
					
				</tbody>
				</table>
				<p class="submit">
				<input type="submit" value="<?php _e("Save Template","dprx_minislides"); ?>" />	
				</p>
				</form>
			</div>
		</div>
						
		<div style="clear: both;"></div>
			
		<h3><?php _e("Existing Templates","dprx_minislides"); ?></h3>
		<?php
		// display all templates
		$templates = get_option("dprx_minislides_templates");
		if (is_array($templates)) {
			foreach($templates as $t) {
				echo "<div style=\"border:1px solid #ccc; float:left; margin: 10px; padding: 10px;\">";
				echo $t['dprx_minislides_templatename']."<p />";
				echo dprx_build_minislides($test_minislides,"",$t);
				echo "<p />";
				echo "<a href=\"".get_bloginfo("wpurl")."/wp-admin/admin.php?page=".$_REQUEST['page']."&edit=".$t['templatehash']."\">".__("Edit","dprx_minislides")."</a>";
				echo "&nbsp;|&nbsp;";
				echo "<a href=\"".get_bloginfo("wpurl")."/wp-admin/admin.php?page=".$_REQUEST['page']."&dele=".$t['templatehash']."\">".__("Delete","dprx_minislides")."</a>";
				echo "<p />";
				echo __('use',"dprx_minislides").": [minislides#".$t['dprx_minislides_templatename']."] ".__('within your post',"dprx_minislides");
				echo "<p />";
				echo "</div>";
			}
		}
	
		?>
		<div style="clear: both;"></div>	
		<p>
		<?php _e('FYI',"dprx_minislides") ?>:
		<?php _e('You are currently using the',"dprx_minislides") ?>
		<b><?php echo dprx_minislides_get_theme(); ?></b>
		<?php _e('theme',"dprx_minislides") ?>.
		<?php _e('You need not to change anything within your theme\'s style.css, but you can.',"dprx_minislides") ?>
		</p>
	</div>
	<div class="wrap">
		<p>
		<?php _e("Running into Troubles? Features to suggest?","dprx_minislides"); ?>
		<a href="http://wordpress.designpraxis.at/">
		<?php _e("Drop me a line","dprx_minislides"); ?> &raquo;
		</a>
		</p>
		<div style="display: block; height:30px;">
			<div style="float:left; font-size: 16px; padding:5px 5px 5px 0;">
			<?php _e("Do you like this Plugin?","dprx_minislides"); ?>
			</div>
			<div style="float:left;">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="rol@rm-r.at">
			<input type="hidden" name="no_shipping" value="0">
			<input type="hidden" name="no_note" value="1">
			<input type="hidden" name="currency_code" value="EUR">
			<input type="hidden" name="tax" value="0">
			<input type="hidden" name="lc" value="AT">
			<input type="hidden" name="bn" value="PP-DonationsBF">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Please donate via PayPal!">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			</div>
		</div>
	</div>
	<?php
}

function dprx_get_minislides($templatename="") {
	global $post, $wp_version, $wpdb;

	if( empty($post->ID) ) { return; }
	
	$record = ( $wp_version < 2.1 ) ? 'post_status' : 'post_type';
	
	$sql = "SELECT ID, post_parent, post_title, post_content, guid FROM ".$wpdb->posts." 
		WHERE 
		post_parent = '".$post->ID."' 
		AND ".$record." = 'attachment' 
		AND post_mime_type LIKE '%image%' ORDER BY guid ASC";
		
	$result = @$wpdb->get_results($sql);
	if (count($result) < 1) {
		return;
	}
	if (empty($templatename)) {
		$current = dprx_minislides_load_current_template();
	} else {
		$current = dprx_minislides_load_template_by_name($templatename);
	}
	return dprx_build_minislides($result,0,$current);
}

function dprx_build_minislides($result, $demo="", $template="") {
	global $post;
	$i=1;
	$images = "";
	$nav = "";
	foreach( $result as $attachment ) {
		if (empty($_REQUEST['dprx_i']) && $i == 1) {
			$_REQUEST['dprx_i'] = $attachment->ID;
		}
		
		if( $post->ID == $attachment->post_parent ) {
			$img_url = preg_replace('!(\.[^.]+)?$!', __('.thumbnail') . '$1', $attachment->guid, 1);
			$img_title = apply_filters('the_title', $attachment->post_title);
			$fileimage = explode('.', basename($attachment->guid));
		}
	
		$imagesize = @getimagesize($img_url);
		$img_path = ABSPATH . str_replace(get_settings('siteurl'), '', $img_url);
		if (!file_exists($img_path)) {
			$img_url = $attachment->guid;
			$img_path = ABSPATH . str_replace(get_settings('siteurl'), '',$attachment->guid);
		}
		
		if ($imagesize[0] > $width) { $width = $imagesize[0]; }
		if ($imagesize[1] > $height) { $height = $imagesize[1]; }
		if(file_exists($img_path) ) {
			if ($i == 1) {
				$the_image_name = "minislide".$template['templatehash'].$demo.$attachment->ID;
				$the_image .= '<a id="minislidelink" title="'.$img_title.'" rel="lightbox[minislide_'.$post->ID.']" href="'.$attachment->guid.'"><img class="dprx_minislides_img" id="'.$the_image_name.'" src="' . $img_url . '" title="' . $img_title . '" alt="' . $img_title . '" /></a>';
			} 
			$images .= '<img class="' . $css_class . '" style="display:none;" src="' . $img_url . '" title="' . $img_title . '" alt="' . $img_title . '" />';
			$nav .= "<a title=\"".$img_title."\" rel=\"lightbox[minislide_".$post->ID."]\" href=\"".$attachment->guid."\" onmouseover=\"javascript:document.getElementById('".$the_image_name."').src = '".$img_url."';document.getElementById('minislidelink').href = '".$attachment->guid."';\">".$i."</a>\n";
		$i++;
		}
	}
// 	if (empty($GLOBALS['minislides'.$post->ID])) {
// 		$GLOBALS['minislides'.$post->ID] = 1;
// 	} else {
// 		return;
// 	}
	if (!empty($demo)) {
		$dprx_minislides_id = " id=\"dprx_minislides_demo\" ";
		$dprx_minislides_nav_id = " id=\"dprx_minislides_nav_demo\" ";
		$dprx_minislides_content_id = " id=\"dprx_minislides_content_demo\" ";
	}
	$pwrd = "<div class=\"dprx_pwrd\"><a class=\"dprx_pwrd_lnk\" title=\"powered by designpraxis\" href=\"http://wordpress.designpraxis.at\">dp</a></div>\n";
	$framewidth = $width+10;
	return "<div class=\"dprx_minislides".$template['templatehash']."\"".$dprx_minislides_id." style=\"width: ".$framewidth."px;\">\n
			<div class=\"dprx_minislides_content\"".$dprx_minislides_content_id." style=\"width: ".$width."px; height: ".$height."px\">".$the_image."</div>\n
			<div class=\"dprx_minislides_nav\"".$dprx_minislides_nav_id." style=\"width: ".$framewidth."px;\">".$nav."\n".$pwrd."</div>\n
		</div>\n".$images;
}

function dprx_display_minislides() {
	global $post;
	echo dprx_get_minislides();
}

function dprx_contentfilter($data) {
	preg_match("/(.*?)\[minislides(.*)(\])(.*?)/", $data, $match);
	if ($match) {
		$strtoreplace = $match[0];
		$templatename = str_replace("#","",$match[2]);
		$slides = dprx_get_minislides($templatename);
		return str_replace($strtoreplace, $slides, $data);
	} else {
		return $data;
	}
} 
add_filter('the_content', 'dprx_contentfilter');
?>
