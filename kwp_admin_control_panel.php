<?php 
if (!current_user_can('manage_options'))  {
  wp_die( __('You do not have sufficient permissions to access this page.') );
}


/**
 * Check to see if the kohana path has been added as a WordPress Option.
 * If it hasn't then this is the only setting users should be allowed to set.
 */

/**
 * Handle POSTs to this page
 */
$show_routing_tab = ( $_POST && $_POST['action'] == 'add_page_routing' ) ? true : false;
$routes_updated = false;
if( $show_routing_tab ){
	if( $_POST['kr'] && $_POST['postid']){
		$kr = $_POST['kr'];
		$pid = $_POST['postid'];
		$placement = ($_POST['placement']) ? $_POST['placement'] : 'after';
		
		$option_name = "kwp_route::$pid";
		$option_value = "$kr::$placement";
		
		if( get_option($option_name) ){
			update_option($option_name,$option_value);
		} else {
			add_option($option_name,$option_value);
		}
		$routes_updated = true;
	}
}
/**
 * Handle Deleting Routing Option
 */
if (isset($_POST['action']) && $_POST['action'] == 'delete_page_routing') {
	$show_routing_tab = true;
	delete_route($_POST['route_post_id'] );
	$routes_updated = true;
}

$option_set = (get_option('kwp_system_path')) ? true : false;

function delete_route($post_id) {
	$sql = <<<SQL
		DELETE
		FROM wp_postmeta
		WHERE 
			( meta_key = '_kwp_route'
			  OR meta_key = '_kwp_placement'
			)
			AND post_id = $post_id;
SQL;
	global $wpdb;
	$wpdb->query($sql);
}

/**
 * Check wordpress options and find all kohana routes
 */

function get_page_routes() {
	$sql = <<<SQL
		SELECT
			pm.post_id,
			pm.meta_value as route,
			wp_posts.post_title
		FROM
			wp_postmeta pm
		INNER JOIN wp_posts ON pm.post_id = wp_posts.ID
		WHERE
			pm.meta_key = '_kwp_route'
SQL;
	global $wpdb;
	return $wpdb->get_results($sql);
}


/**
 * Determine the Kohana Front Loader URL
 */ 
 $my_kohana_front = get_option('siteurl');
 global $wpdb;
 
 if( ! get_option('permalink_structure') ) {
 	$my_kohana_front .= '/?page_id=' . get_option('kwp_front_loader');
 } else {
 	$my_kohana_front .= '/' . $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE ID = " . get_option('kwp_front_loader') );
 }


?>
<?php if( $option_set ) : ?>
<script>
function wp_kohana_admin_showoptions()
{
	document.getElementById('kohana_options_tab').style.display = '';
	document.getElementById('kohana_routing_tab').style.display = 'none';
	document.getElementById('k_options_link').className = 'active';
	document.getElementById('k_routing_link').className = '';
}
function wp_kohana_admin_showrouting()
{
	document.getElementById('kohana_options_tab').style.display = 'none';
	document.getElementById('kohana_routing_tab').style.display = '';
	document.getElementById('k_options_link').className = '';
	document.getElementById('k_routing_link').className = 'active';
}
</script>
<?php endif; ?>
<style>
#navmenu {
	padding: 4px 0px 4px 0px;
	border-bottom: solid thin #CCCCCC;
}
#navmenu ul {margin: 0; padding: 0; 
	list-style-type: none; list-style-image: none; }
#navmenu li {
	margin:4px;
	display: inline;
	border-top: solid thin #CCCCCC; 
	border-left: solid thin #CCCCCC; 
	border-right: solid thin #CCCCCC; 
	padding: 5px 0px 5px 0px;
	
}
#navmenu ul li a {
	text-decoration:none;  margin: 0px;
	padding: 5px 25px 5px 25px;
	background: #EEEEEE;
}
#navmenu ul li a:hover {
	background: #FFFFFF; 
}
#navmenu .active {
	text-decoration:none;  margin: 0px;
	padding: 5px 25px 5px 25px;
	background: #FFFFFF;
}
</style>
<div class="wrap">



<div class="wrap nosubsub">
	<div id="icon-edit-pages" class="icon32"><br></div>
<h2>Kohana-WP Control Panel</h2>
 <p>The Kohana for Wordpress Module allows you to add pages and applications built with the Kohana PHP framework to your
 Wordpress site. To learn more about Kohana see <a href="http://kohanaphp.com/">http://kohanaphp.com</a></p>

<br class="clear">

<?php if( $option_set ) : ?>
<div id="navmenu">
<ul>	
	<li><a id="k_options_link" class="<?php print ( $show_routing_tab ) ? '' : 'active' ?>" href="javascript:wp_kohana_admin_showoptions()">Kohana Options</a></li>
	<li><a id="k_routing_link" class="<?php print ( $show_routing_tab ) ? 'active' : '' ?>" href="javascript:wp_kohana_admin_showrouting()">Page Routing</a></li>
</ul>
</div>

<?php endif; ?>

<div id="kohana_options_tab" style="display:<?php print ( $show_routing_tab ) ? 'none' : '' ?>">

<h3>Kohana Options</h3>

<div>
Kohana Front Loader is: <a href="<?php print $my_kohana_front ?>"><?php print $my_kohana_front ?></a>
Kohana root: <b>wp-content/kohana</b>
</div>

<div class="form-wrap">

<form name="addmap" id="addmap" method="post" action="options.php" class="add:the-list: validate">
  <?php wp_nonce_field('update-options'); ?>

<div class="form-field form-required">
	<label for="name"><strong>Kohana Module Path</strong></label>
	<input type="text" name="kohana_module_path" value="<?php echo get_option('kwp_module_path'); ?>" size="40" aria-required="true" />	
    <p>Enter the full path to your Kohana module folder.</p>
</div>
<div class="form-field form-required">
	<label for="name"><strong>Kohana System Path</strong></label>
	<input type="text" name="kohana_system_path" value="<?php echo get_option('kwp_system_path'); ?>" size="40" aria-required="true" />	
    <p>Enter the full path to your Kohana system folder.</p>
</div>

<div class="form-field form-required">
	<label for="name"><strong>Custom Bootstrap Path</strong></label>
	<input type="text" name="kohana_bootstrap_path" value="<?php echo get_option('kwp_bootstrap_path'); ?>" size="40" aria-required="true" />	
    <p>If you want to use a custom bootstrap file then define the path here. Note you should use the file 
    <i>plugins/kohana-wp/kohana_bootstrap.php</i> as an example.</p>
</div>

<div class="form-field form-required">
	<label for="name"><strong>Kohana File Extension</strong></label>
	<input type="text" name="kohana_ext" value="<?php echo get_option('kwp_ext'); ?>" size="40" aria-required="true" />	
    <p>The default extension of resource files.</p>
</div>
<div class="form-field form-required">
	<label for="name"><strong>Default Placement</strong></label>
	<select name="kohana_default_placement"> 
	 <option value="before" <?php if( get_option('kwp_default_placement')=='before') echo 'selected="true"'; ?>>Before Page Content</option> 
	 <option value="after" <?php if( get_option('kwp_default_placement')=='after') echo 'selected="true"'; ?>>After Page Content</option> 
	 <option value="replace" <?php if( get_option('kwp_default_placement')=='replace') echo 'selected="true"'; ?>>Replace Page Content</option> 
	</select>
    <p>Define if your want the results of Kohana controller requests to replace wordpress content or display before or after wordpress content.</p>
</div>

<div class="form-field form-required">
	<label for="name"><strong>Kohana Modules</strong></label>
	<input type="input" name="kohana_modules" value="<?php print get_option('kwp_modules')?>"  />	
    <p>Enter a comma seperated list of Kohana modules that are referenced by your application</p>
</div>

<div class="form-field form-required">
	<label for="name"><strong>Kohana Default Controller / Action / ID </strong></label>
	<input type="input" name="kohana_default_controller" value="<?php print get_option('kwp_default_controller')?>"  />	
	<input type="input" name="kohana_default_action" value="<?php print get_option('kwp_default_action')?>"  />	
	<input type="input" name="kohana_default_id" value="<?php print get_option('kwp_default_id')?>"  />	
    <p>Enter the default controller, action and optional id for your kohana application</p>
</div>

<?php if ( 0 != count( get_page_templates() ) ) { ?>
<div class="form-field">
	<label for="kohana_page_template"><strong>Kohana Page Template</strong></label>
	<select name="kohana_page_template" id="kohana_page_template">
	<option value='default'>Default Template</option>
	<?php page_template_dropdown(get_option('kwp_page_template')); ?>
	</select>
	<p>If your theme has custom templates, you can select one to be used for all Kohana requests.
	This will override templates assigned to any mapped pages.</p>
</div>
<?php } ?>

<div class="form-field form-required">
	<label for="name"><strong>Include Kohana Front Loader in Wordpress Navigation</strong></label>
	<input type="checkbox" name="kohana_front_loader_in_nav" value="1" <?php print ( get_option('kwp_front_loader_in_nav') ) ? 'checked="true"' : '' ?>  />	
    <p>Kohana module creates a wordpress page when installed. This page basically becomes your kohana front loader. 
    Select the checkbox if you want this page to appear in your wordpress navigation. You can edit the details of this page 
	<a href="<?php echo get_option('siteurl') ?>/wp-admin/page.php?action=edit&post=<?php echo get_option('kwp_front_loader') ?>">here</a>.</p>
</div>

<div class="form-field form-required">
	<label for="name"><strong>Process all URIs</strong></label>
	<input type="checkbox" name="kohana_process_all_uri" value="1" <?php print ( get_option('kwp_process_all_uri') ) ? 'checked="true"' : '' ?>  />	
    <p>If you turn this setting off then the plugin will only attempt to process Kohana controllers if requested from front loader page. If you turn this setting on
	then the plugin will check for a valid Kohana controller for every request that isn't for a specific wordpress page or post.</p>
</div>


  <input type="hidden" name="action" value="update" />
  <input type="hidden" name="page_options" value="kohana_bootstrap_path,kohana_default_id,kohana_default_action,kohana_default_controller,kohana_modules,kohana_default_time_zone,kohana_base_url,kohana_system_path,kohana_module_path,kohana_ext,kohana_front_loader_in_nav,kohana_process_all_uri,kohana_default_placement,kohana_page_template" />
<p class="submit"><input class="button" name="submit" value="Update Kohana Options" type="submit"></p>

</form>
</div>
<br class="clear">

</div>
<div id="kohana_routing_tab" style="display:<?php print ( $show_routing_tab ) ? '' : 'none' ?>">

<?
$routes = get_page_routes();
if ($routes) {
?>
	<h3>Kohana Page Routing</h3>

<? } ?>

<table class="widefat tag fixed" cellspacing="0">
	<thead>
	<tr>
	<th scope="col" id="name" class="manage-column column-description" style="">Kohana Content</th>
	<th scope="col" id="slug" class="manage-column column-description" style="">Wordpress Page</th>
	<th scope="col" id="posts" class="manage-column column-posts num" style=""></th>
	</tr>
	</thead>
<tbody id="the-list" class="list:tag">
<? foreach($routes as $route) { ?>
	<tr id="cat-1" class="iedit alternate">
	 	 <td class="description column-description"><?php print $route->route; ?></td>
	 	 <td class="description column-description"><?php print $route->post_title; ?></td>
	 	 <td class="slug column-slug"><?php print edit_post_link('edit', '', '', $route->post_id); ?> | <a href="javascript:delete_route(<?php print $route->post_id; ?>)">delete</a></td>
	</tr>
<? } ?>
</tbody>

</table>
<br class="clear">
<script type="text/javascript">
function delete_route(post_id) {
	document.getElementById('route_post_id').value = post_id;
	document.getElementById('delete_route_form').submit();
}
</script>
<form id="delete_route_form" action="options-general.php?page=Kohana" method="post">
	<input name="action" id="action" value="delete_page_routing" type="hidden">
	<input name="route_post_id" id="route_post_id" value="" type="hidden">
 
</form>


<?php if( $option_set ) : ?>
<div class="form-wrap">
	<p>Route entries are added by editing a Page through the Pages menu on the left hand side. Look for <b>Kohana-WP Integration</b> box.</p>
</div>

<?php endif; // END IF OPTIONS_SET ?>

</div>
</div>
</div>