<?php
/**
 * Plugin Name: Boatdeck Package Pricing Table
 * Plugin URI: http://marinewebsites.com.au/
 * Description: Boatdeck WordPress Plugin for creating package pricing tables.
 * Author: Muhammad Arif
 * Version: 1.0.0
 * Author URI: http://marinewebsites.com.au/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//table prefix
$table_prefix = "bd_ppt_";

/**
 * create tables when plugin activate
 */
function bd_ppt_activate()
{
	global $wpdb;

	// create/update database
	require_once (dirname(__FILE__)."/database.php");
}
register_activation_hook(__FILE__, 'bd_ppt_activate');

//Menu and admin pages
function bd_ppt_add_options_page(){
	add_menu_page('BD Package Pricing Table', 'Packages', 8, 'bd-ppt-packages', 'bd_ppt_get_all_packages');
	add_submenu_page('bd-ppt-packages', 'Add New Package', 'Add New Package', 'manage_options', 'bd-ppt-add-package' , 'bd_ppt_add_package');
	add_submenu_page('bd-ppt-add-package', 'Edit Package', 'Edit Package', 'administrator', 'bd-ppt-edit-package' , 'bd_ppt_edit_package');
	add_submenu_page('bd-ppt-packages', 'Add-Ons Table', 'Add-Ons', 'manage_options', 'bd-ppt-addons', 'bd_ppt_get_all_addons');
	add_submenu_page('bd-ppt-packages', 'Add New Addon', 'Add New Addon', 'manage_options', 'bd-ppt-add-addon', 'bd_ppt_add_addon');
	add_submenu_page('bd-ppt-add-addon', 'Edit Addon', 'Edit Addon', 'administrator', 'bd-ppt-edit-addon', 'bd_ppt_edit_addon');
	add_submenu_page('bd-ppt-packages', 'Settings', 'Settings', 'manage_options', 'bd-ppt-settings', 'bd_ppt_settings');
}
add_action('admin_menu', 'bd_ppt_add_options_page');

//Show All Packages
function bd_ppt_get_all_packages()
{
    global $wpdb;
    $package_table = new Boatdeck_Package_List_Table();
    $package_table->prepare_items();

    $message = '';
    if ('delete' === $package_table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
	<div class="wrap">

	    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	    <h2>Packages
	    <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=bd-ppt-add-package');?>">Add New</a>
	    </h2>
	    <?php echo $message; ?>

	    <form id="packages-table" method="GET">
	        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
	        <?php $package_table->display() ?>
	    </form>

	</div>
	<?php
}

//Show All Add-Ons
function bd_ppt_get_all_addons()
{
    global $wpdb;
    $addon_table = new Boatdeck_Addon_List_Table();
    $addon_table->prepare_items();

    $message = '';
    if ('delete' === $addon_table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
	<div class="wrap">

	    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	    <h2>Add-Ons
	    <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=bd-ppt-add-addon');?>">Add New</a>
	    </h2>
	    <?php echo $message; ?>

	    <form id="packages-table" method="GET">
	        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
	        <?php $addon_table->display() ?>
	    </form>

	</div>
	<?php
}


//Create
function bd_ppt_add_package()
{
	global $wpdb,$table_prefix;

	//begin insert new package if form is submitted
	if(isset($_POST['package_submit']))
	{
		$title 			= $_POST['title'];
		$subtitle 		= $_POST['subtitle'];
		$price 			= $_POST['price'];
		$is_recommended = $_POST['is_recommended'] ? $_POST['is_recommended'] : 0;
		$show_order 	= $_POST['show_order'] ? $_POST['show_order'] : 0;
		$features 		= $_POST['features'];
		$feature_cat 	= $_POST['feature_cat'];

		if($title && $subtitle && $price)
		{
			$package_data = array(
				"title"				=> $title,
				"subtitle"			=> $subtitle,
				"price_text"		=> $price,
				"is_recommended"	=> $is_recommended,
				"show_order"		=> $show_order,
				"created"			=> time()
			);

			$save_package = $wpdb->insert(
				$table_prefix.'packages',
				$package_data
			);

			$package_id = $wpdb->insert_id;

			if($features)
			{
				foreach ($features as $key => $value) {
				 	$features_data = array(
				 		'package_id'	=> $package_id,
				 		'name'			=> $value,
				 		'category'		=> $feature_cat[$key]
				 	);

				 	$wpdb->insert(
						$table_prefix.'package_features',
						$features_data
					);
				}
			}

			if($save_package){
				$message = '<div id="message" class="updated fade"><table><tr><td><p>Package has been saved. <a href="'.get_admin_url(get_current_blog_id()).'admin.php?page=bd-ppt-packages">Show all packages</a> </p><td></tr></table></div>';
			}
			else{
				$message = '<div id="message" class="updated fade"><table><tr><td><p>Something wrong. Please try again.</p><td></tr></table></div>';
			}

		}
		else
		{
			$message = '<div id="message" class="updated fade"><table><tr><td><p>Please input required field.</p><td></tr></table></div>';
		}
	}
	?>
	<div class="wrap">
		<?php
		if($message){
			echo $message;
		}
		?>
		<div class="icon32" id="icon-options-general"></div>
		<h2>Add New Package</h2><hr>
		<form method="post" action="admin.php?page=bd-ppt-add-package">
			<table class="form-table">
				<tr>
					<td>Title (required)</td>
					<td><input type='text' name='title' placeholder="Package title" required></td>
				</tr>
				<tr>
					<td>Price (required)</td>
					<td><input type='text' name='price' placeholder="e.g: $100 - $250" required></td>
				</tr>
				<tr>
					<td>Sub Title (required)</td>
					<td><input type='text' name='subtitle' placeholder="Package subtitle" required></td>
				</tr>
				<tr>
					<td>is recommended?</td>
					<td><input type='checkbox' name='is_recommended' value="1"></td>
				</tr>
				<tr>
					<td>Show Order</td>
					<td><input type='number' name='show_order'></td>
				</tr>
			</table>
			<hr>
			<table id="feature-lists" class="form-table">
				<tr>
					<td><button type="button" class="button-primary add-feature">Add Feature</button></td>
				</tr>
				<tr class="feature-item">
					<td>Feature name</td>
					<td><input type="text" name="features[]" placeholder="feature name"></td>
					<td>Category</td>
					<td>
						<select name="feature_cat[]">
							<option value="1">Support</option>
							<option value="2">Bookkeeping</option>
							<option value="3">BAS/IAS</option>
							<option value="4">Reports</option>
						</select>
					</td>
					<td><button type="button" class="button-secondary delete-feature">delete</button></td>
				</tr>
			</table>
			<hr>
			<p>
				<input class="button button-primary" name="package_submit" value="Save" type="submit" />
			</p>
		</form>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(".add-feature").live("click",function(){
				jQuery("#feature-lists")
				.append('<tr class="feature-item">'
					+'<td>Feature name</td>'
					+'<td><input type="text" name="features[]" placeholder="feature name"></td>'
					+'<td>Category</td>'
					+'<td><select name="feature_cat[]">'
					+'<option value="1">Support</option>'
					+'<option value="2">Bookkeeping</option>'
					+'<option value="3">BAS/IAS</option>'
					+'<option value="4">Reports</option>'
					+'</select></td>'
					+'<td><button type="button" class="button-secondary delete-feature">delete</button></td>'
					+'</tr>');
			});

			jQuery(".delete-feature").live("click",function(){
				jQuery(this).closest('tr').remove();
			});


		});
	</script>
	<?php
}


//Edit and Update
function bd_ppt_edit_package()
{
	global $wpdb,$table_prefix;
	$package_table 			= $table_prefix . 'packages';
	$package_features_table	= $table_prefix . 'package_features';

	$package_id = $_REQUEST['id'];
	$message 	= "";

	if(!$package_id){
		exit;
	}

	//begin update new package if form is submitted
	if(isset($_POST['package_submit']))
	{
		$title 			= $_POST['title'];
		$subtitle 		= $_POST['subtitle'];
		$price 			= $_POST['price'];
		$is_recommended = $_POST['is_recommended'] ? $_POST['is_recommended'] : 0;
		$show_order 	= $_POST['show_order'] ? $_POST['show_order'] : 0;
		$features 		= $_POST['features'];
		$feature_cat 	= $_POST['feature_cat'];

		if($title && $subtitle && $price)
		{
			$package_data = array(
				"title"				=> $title,
				"subtitle"			=> $subtitle,
				"price_text"		=> $price,
				"is_recommended"	=> $is_recommended,
				"show_order"		=> $show_order,
				"updated"			=> time()
			);

			$save_package = $wpdb->update(
				$package_table,
				$package_data,
				array('id' => $package_id)
			);

			if($features)
			{
				$wpdb->query("DELETE FROM $package_features_table WHERE package_id = $package_id");

				foreach ($features as $key => $value) {
				 	$features_data = array(
				 		'package_id'	=> $package_id,
				 		'name'			=> $value,
				 		'category'		=> $feature_cat[$key]
				 	);

				 	$wpdb->insert(
						$table_prefix.'package_features',
						$features_data
					);
				}
			}

			if($save_package){
				$message = '<div id="message" class="updated fade"><table><tr><td><p>Package has been updated. <a href="'.get_admin_url(get_current_blog_id()).'admin.php?page=bd-ppt-packages">Show all packages</a> </p><td></tr></table></div>';			}
			else{
				$message = '<div id="message" class="updated fade"><table><tr><td><p>Something wrong. Please try again.</p><td></tr></table></div>';
			}

		}
		else
		{
			$message =  '<div id="message" class="updated fade"><table><tr><td><p>Please input required field.</p><td></tr></table></div>';
		}
	}

	$package 				= $wpdb->get_row("SELECT * FROM {$package_table} WHERE id = {$package_id}", OBJECT);
	$package_features 		= $wpdb->get_results("SELECT * FROM {$package_features_table} WHERE package_id = {$package_id}", OBJECT);

	?>
	<div class="wrap">
		<?php
		if($message){
			echo $message;
		}
		?>
		<div class="icon32" id="icon-options-general"></div>
		<h2>Edit Package
		<a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=bd-ppt-packages');?>">Back</a>
		</h2>
		<hr>
		<form method="post" action="admin.php?page=bd-ppt-edit-package&id=<?php echo $package_id; ?>">
			<table class="form-table">
				<tr>
					<td>Title (required)</td>
					<td><input type='text' name='title' placeholder="Package title" value="<?php echo $package->title; ?>" required></td>
				</tr>
				<tr>
					<td>Price (required)</td>
					<td><input type='text' name='price' placeholder="e.g: $100 - $250" value="<?php echo $package->price_text; ?>" required></td>
				</tr>
				<tr>
					<td>Sub Title (required)</td>
					<td><input type='text' name='subtitle' placeholder="Package subtitle" value="<?php echo $package->subtitle; ?>" required></td>
				</tr>
				<tr>
					<td>is recommended?</td>
					<td><input type='checkbox' name='is_recommended' value="1" <?php echo ($package->is_recommended == 1 ? "checked" : ""); ?>></td>
				</tr>
				<tr>
					<td>Show Order</td>
					<td><input type='number' name='show_order' value="<?php echo $package->show_order; ?>"></td>
				</tr>
			</table>
			<hr>
			<table id="feature-lists" class="form-table">
				<tr>
					<td><button type="button" class="button-primary add-feature">Add Feature</button></td>
				</tr>
				<?php if($package_features) : ?>
					<?php foreach ($package_features as $feature) : ?>
					<tr class="feature-item">
						<td>Feature name</td>
						<td><input type="text" name="features[]" placeholder="feature name" value="<?php echo $feature->name; ?>"></td>
						<td>Category</td>
						<td>
						<select name="feature_cat[]">
							<option value="1" <?php echo ($feature->category == 1 ? "selected": ""); ?>>Support</option>
							<option value="2" <?php echo ($feature->category == 2 ? "selected": ""); ?>>Bookkeeping</option>
							<option value="3" <?php echo ($feature->category == 3 ? "selected": ""); ?>>BAS/IAS</option>
							<option value="4" <?php echo ($feature->category == 4 ? "selected": ""); ?>>Reports</option>
						</select>
						</td>
						<td><button type="button" class="button-secondary delete-feature">delete</button></td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</table>
			<hr>
			<p>
				<input class="button button-primary" name="package_submit" value="Update" type="submit" />
			</p>
		</form>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(".add-feature").live("click",function(){
				jQuery("#feature-lists")
				.append('<tr class="feature-item">'
					+'<td>Feature name</td>'
					+'<td><input type="text" name="features[]" placeholder="feature name"></td>'
					+'<td>Category</td>'
					+'<td><select name="feature_cat[]">'
					+'<option value="1">Support</option>'
					+'<option value="2">Bookkeeping</option>'
					+'<option value="3">BAS/IAS</option>'
					+'<option value="4">Reports</option>'
					+'</select></td>'
					+'<td><button type="button" class="button-secondary delete-feature">delete</button></td>'
					+'</tr>');
			});

			jQuery(".delete-feature").live("click",function(){
				jQuery(this).closest('tr').remove();
			});


		});
	</script>
	<?php

}

function bd_ppt_add_addon()
{
	global $wpdb,$table_prefix;

	//begin insert new package if form is submitted
	if(isset($_POST['addon_submit']))
	{
		$name 			= $_POST['name'];
		$features 		= $_POST['features'];
		$feature_price 	= $_POST['feature_price'];
		$feature_desc 	= $_POST['feature_desc'];

		if($name)
		{
			$addon_data = array(
				"name"				=> $name,
				"created"			=> time()
			);

			$save_addon = $wpdb->insert(
				$table_prefix.'addons',
				$addon_data
			);

			$addon_id = $wpdb->insert_id;

			if($features)
			{
				foreach ($features as $key => $value) {
				 	$addon_features_data = array(
				 		'addon_id'		=> $addon_id,
				 		'name'			=> $value,
				 		'price_text'	=> $feature_price[$key],
				 		'description'	=> $feature_desc[$key],
				 	);

				 	$wpdb->insert(
						$table_prefix.'addon_features',
						$addon_features_data
					);
				}
			}

			if($save_addon){
				$message = '<div id="message" class="updated fade"><table><tr><td><p>New Addon has been saved. <a href="'.get_admin_url(get_current_blog_id()).'admin.php?page=bd-ppt-addons">Show all addons</a> </p><td></tr></table></div>';
			}
			else{
				$message = '<div id="message" class="updated fade"><table><tr><td><p>Something wrong. Please try again.</p><td></tr></table></div>';
			}

		}
		else
		{
			$message =  '<div id="message" class="updated fade"><table><tr><td><p>Please input required field.</p><td></tr></table></div>';
		}

	}
	?>
	<div class="wrap">
		<?php
		if($message){
			echo $message;
		}
		?>
		<div class="icon32" id="icon-options-general"></div>
		<h2>Add New Addon</h2><hr>
		<form method="post" action="admin.php?page=bd-ppt-add-addon">
			<table class="form-table">
				<tr>
					<td>Name (required)</td>
					<td><input type='text' name='name' placeholder="Addon name" required></td>
				</tr>
			</table>
			<hr>
			<table id="feature-lists" class="form-table">
				<tr>
					<td><button type="button" class="button-primary add-feature">Add Feature</button></td>
				</tr>
				<tr class="feature-item">
					<td>Feature name</td>
					<td><input type="text" name="features[]" placeholder="feature name"></td>
					<td>Feature price</td>
					<td><input type="text" name="feature_price[]" placeholder="feature price"></td>
					<td>Feature description</td>
					<!-- <td><input type="text" name="feature_desc[]" placeholder="feature description"></td> -->
					<td><textarea name="feature_desc[]" placeholder="feature description"></textarea></td>
					<td><button type="button" class="button-secondary delete-feature">delete</button></td>
				</tr>
			</table>
			<hr>
			<p>
				<input class="button button-primary" name="addon_submit" value="Save" type="submit" />
			</p>
		</form>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(".add-feature").live("click",function(){
				jQuery("#feature-lists")
				.append('<tr class="feature-item">'
					+'<td>Feature name</td>'
					+'<td><input type="text" name="features[]" placeholder="feature name"></td>'
					+'<td>Feature price</td>'
					+'<td><input type="text" name="feature_price[]" placeholder="feature price"></td>'
					+'<td>Feature description</td>'
					// +'<td><input type="text" name="feature_desc[]" placeholder="feature description"></td>'
					+'<td><textarea name="feature_desc[]" placeholder="feature description"></textarea></td>'
					+'<td><button type="button" class="button-secondary delete-feature">delete</button></td>'
					+'</tr>');
			});

			jQuery(".delete-feature").live("click",function(){
				jQuery(this).closest('tr').remove();
			});


		});
	</script>
	<?php
}


function bd_ppt_edit_addon()
{
	global $wpdb,$table_prefix;
	$addon_table 			= $table_prefix . 'addons';
	$addon_features_table	= $table_prefix . 'addon_features';

	$addon_id = $_REQUEST['id'];
	$message 	= "";

	if(!$addon_id){
		exit;
	}

	//begin insert new addon if form is submitted
	if(isset($_POST['addon_submit']))
	{
		$name 			= $_POST['name'];
		$features 		= $_POST['features'];
		$feature_price 	= $_POST['feature_price'];
		$feature_desc 	= $_POST['feature_desc'];

		if($name)
		{
			$addon_data = array(
				"name"				=> $name,
				"updated"			=> time()
			);

			$save_addon = $wpdb->update(
				$addon_table,
				$addon_data,
				array('id' => $addon_id)
			);

			if($features)
			{
				$wpdb->query("DELETE FROM $addon_features_table WHERE addon_id = $addon_id");

				foreach ($features as $key => $value) {
				 	$addon_features_data = array(
				 		'addon_id'		=> $addon_id,
				 		'name'			=> $value,
				 		'price_text'	=> $feature_price[$key],
				 		'description'	=> $feature_desc[$key],
				 	);

				 	$wpdb->insert(
						$table_prefix.'addon_features',
						$addon_features_data
					);
				}
			}

			if($save_addon){
				$message = '<div id="message" class="updated fade"><table><tr><td><p>Addon has been updated. <a href="'.get_admin_url(get_current_blog_id()).'admin.php?page=bd-ppt-addons">Show all addons</a> </p><td></tr></table></div>';
			}
			else{
				$message = '<div id="message" class="updated fade"><table><tr><td><p>Something wrong. Please try again.</p><td></tr></table></div>';
			}

		}
		else
		{
			$message =  '<div id="message" class="updated fade"><table><tr><td><p>Please input required field.</p><td></tr></table></div>';
		}

	}

	$addon 				= $wpdb->get_row("SELECT * FROM {$addon_table} WHERE id = {$addon_id}", OBJECT);
	$addon_features 	= $wpdb->get_results("SELECT * FROM {$addon_features_table} WHERE addon_id = {$addon_id}", OBJECT);

	?>
	<div class="wrap">
		<?php
		if($message){
			echo $message;
		}
		?>
		<div class="icon32" id="icon-options-general"></div>
		<h2>Edit Addon</h2><hr>
		<a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=bd-ppt-addons');?>">Back</a>
		<form method="post" action="admin.php?page=bd-ppt-edit-addon&id=<?php echo $addon_id; ?>">
			<table class="form-table">
				<tr>
					<td>Name (required)</td>
					<td><input type='text' name='name' placeholder="Addon name" value="<?php echo $addon->name; ?>" required></td>
				</tr>
			</table>
			<hr>
			<table id="feature-lists" class="form-table">
				<tr>
					<td><button type="button" class="button-primary add-feature">Add Feature</button></td>
				</tr>
				<?php if($addon_features) : ?>
					<?php foreach ($addon_features as $feature) : ?>
					<tr class="feature-item">
						<td>Feature name</td>
						<td><input type="text" name="features[]" placeholder="feature name" value="<?php echo $feature->name; ?>"></td>
						<td>Feature price</td>
						<td><input type="text" name="feature_price[]" placeholder="feature price" value="<?php echo $feature->price_text; ?>"></td>
						<td>Feature description</td>
						<!-- <td><input type="text" name="feature_desc[]" placeholder="feature description" value="<?php echo $feature->description; ?>"></td> -->
						<td><textarea name="feature_desc[]" placeholder="feature description"><?php echo $feature->description; ?></textarea></td>
						<td><button type="button" class="button-secondary delete-feature">delete</button></td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</table>
			<hr>
			<p>
				<input class="button button-primary" name="addon_submit" value="Update" type="submit" />
			</p>
		</form>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(".add-feature").live("click",function(){
				jQuery("#feature-lists")
				.append('<tr class="feature-item">'
					+'<td>Feature name</td>'
					+'<td><input type="text" name="features[]" placeholder="feature name"></td>'
					+'<td>Feature price</td>'
					+'<td><input type="text" name="feature_price[]" placeholder="feature price"></td>'
					+'<td>Feature description</td>'
					+'<td><textarea name="feature_desc[]" placeholder="feature description"></textarea></td>'
					// +'<td><input type="text" name="feature_desc[]" placeholder="feature description"></td>'
					+'<td><button type="button" class="button-secondary delete-feature">delete</button></td>'
					+'</tr>');
			});

			jQuery(".delete-feature").live("click",function(){
				jQuery(this).closest('tr').remove();
			});


		});
	</script>
	<?php
}

function bd_ppt_settings()
{
	if(isset($_POST['settings_submit']))
	{
		$bd_ppt_package_title 			= esc_attr($_POST['bd_ppt_package_title']);
		$bd_ppt_package_subtitle 		= esc_attr($_POST['bd_ppt_package_subtitle']);
		$bd_ppt_addons_title 			= esc_attr($_POST['bd_ppt_addons_title']);
		$bd_ppt_addons_subtitle 		= esc_attr($_POST['bd_ppt_addons_subtitle']);

		update_option('bd_ppt_package_title', $bd_ppt_package_title);
		update_option('bd_ppt_package_subtitle', $bd_ppt_package_subtitle);
		update_option('bd_ppt_addons_title', $bd_ppt_addons_title);
		update_option('bd_ppt_addons_subtitle', $bd_ppt_addons_subtitle);

		$message = '<div id="message" class="updated fade"><table><tr><td><p>Settings has been updated.</p><td></tr></table></div>';
	}

	?>
	<div class="wrap">
		<?php
		if($message){
			echo $message;
		}
		?>
		<div class="icon32" id="icon-options-general"></div>
		<h2>Settings</h2><hr>
		<form method="post" action="admin.php?page=bd-ppt-settings">
			<table class="form-table">
				<tr>
					<td>Packages Title</td>
					<td><input type='text' name='bd_ppt_package_title' placeholder="Packages Title" value="<?php echo get_option('bd_ppt_package_title'); ?>" required></td>
				</tr>
				<tr>
					<td>Packages Subtitle</td>
					<td><textarea name='bd_ppt_package_subtitle' placeholder="Packages Subtitle" rows="5" required><?php echo get_option('bd_ppt_package_subtitle'); ?></textarea></td>
				</tr>
				<tr>
					<td>Addons Title</td>
					<td><input type='text' name='bd_ppt_addons_title' placeholder="Addons Title" value="<?php echo get_option('bd_ppt_addons_title'); ?>" required></td>
				</tr>
				<tr>
					<td>Addons Subtitle</td>
					<td><textarea name='bd_ppt_addons_subtitle' placeholder="Addons Subtitle" rows="5" required><?php echo get_option('bd_ppt_addons_subtitle'); ?></textarea></td>
				</tr>
			</table>
			<hr>
			<p>
				<input class="button button-primary" name="settings_submit" value="Save" type="submit" />
			</p>
		</form>
	</div>
	<?php
}


//Shortcode
function bd_packages_lists($atts)
{
	global $wpdb, $table_prefix;

	$template = "";

	$k_packages = "SELECT * FROM {$table_prefix}packages";
	$q_packages = $wpdb->get_results($k_packages);

	if(isset($_GET['bd_package_debug']))
	{
		echo '<pre>';
		print_r($q_packages);
		echo '</pre>';
	}

	$k_addons = "SELECT * FROM {$table_prefix}addons";
	$q_addons = $wpdb->get_results($k_addons);

	if(isset($_GET['bd_package_debug']))
	{
		echo '<pre>';
		print_r($q_addons);
		echo '</pre>';
	}

	?>

    <!-- PRICE
    ================================================== -->
	<section class="section s-packages bg-overlay bg-overlay-dark parallax" style="background:url('<?php bloginfo('url'); ?>/wp-content/themes/twentyseventeen/assets/images/bg-service.jpg')">
    	<div class="container">

            <div class="section-header">
    			<h2 class="section-title">
    				<?php echo get_option('bd_ppt_package_title'); ?>
    			</h2>
                <span class="section-subtitle">
    				<p><?php echo get_option('bd_ppt_package_subtitle'); ?></p>
    			</span>
    		</div>

    		<!-- Section Content -->
            <div class="section-body">

				<div class="card-group">

					<?php foreach ($q_packages as $package) : ?>

					<!-- Price Box #2 -->
		            <div class="card <?php echo ($package->is_recommended == 1 ? 'recommended' : ''); ?>">
		                <?php echo ($package->is_recommended == 1 ? '<div class="ribbon"><span>Recommended</span></div>' : ''); ?>
		                <div class="card-header">
		                    <!-- <div class="icon-inventory"></div> -->
		                    <h4 class="card-title"><?php echo $package->title; ?></h4>
							<div class="card-price">
								<span><?php echo $package->price_text; ?></span>
								<span><?php echo $package->subtitle; ?></span>
								<span>/month</span>
		                    </div>
		                </div>
		                <div class="card-body">
		            		<p>Support</p>
		                	<?php
		                	$q_features = $wpdb->get_results("SELECT * FROM {$table_prefix}package_features WHERE package_id = {$package->id} AND category = 1"); ?>
		                	<?php foreach ($q_features as $feature) : ?>
			                    <ul class="list-group list-group-flush">
			                        <li class="list-group-item tick"><?php echo $feature->name; ?></li>
			                    </ul>
		                	<?php endforeach; ?>
							<p>Bookkeeping</p>
		                	<?php
		                	$q_features = $wpdb->get_results("SELECT * FROM {$table_prefix}package_features WHERE package_id = {$package->id} AND category = 2"); ?>
		                	<?php foreach ($q_features as $feature) : ?>
			                    <ul class="list-group list-group-flush">
			                        <li class="list-group-item tick"><?php echo $feature->name; ?></li>
			                    </ul>
		                	<?php endforeach; ?>
							<p>BAS/IAS</p>
							<?php
		                	$q_features = $wpdb->get_results("SELECT * FROM {$table_prefix}package_features WHERE package_id = {$package->id} AND category = 3"); ?>
		                	<?php foreach ($q_features as $feature) : ?>
			                    <ul class="list-group list-group-flush">
			                        <li class="list-group-item tick"><?php echo $feature->name; ?></li>
			                    </ul>
		                	<?php endforeach; ?>
							<p>Reports</p>
							<?php
		                	$q_features = $wpdb->get_results("SELECT * FROM {$table_prefix}package_features WHERE package_id = {$package->id} AND category = 4"); ?>
		                	<?php foreach ($q_features as $feature) : ?>
			                    <ul class="list-group list-group-flush">
			                        <li class="list-group-item tick"><?php echo $feature->name; ?></li>
			                    </ul>
		                	<?php endforeach; ?>
		                </div>
		                <div class="card-footer">
		                    <a href="#sAddons" package-id="<?php echo $package->id; ?>" package-name="<?php echo $package->title; ?>" class="smoothScroll btn btn-blue btn-custom package-select">SELECT</a>
		                </div>
		            </div>

					<?php endforeach; ?>

				</div>

            </div>

    	</div>	<!-- End Container -->
    </section>	<!-- End Price Section -->

    <!-- Add Ons
    ================================================== -->
    <section class="section s-addons" id="sAddons">
    	<div class="container">

            <div class="section-header">
    			<h2 class="section-title">
    				<?php echo get_option('bd_ppt_addons_title'); ?>
    			</h2>
                <span class="section-subtitle">
    				<p><?php echo get_option('bd_ppt_addons_subtitle'); ?></p>
    			</span>
    		</div>

			<div class="section-body">

				<?php if($q_addons) : ?>

			        <nav class="nav nav-tabs nav-justified" id="myTab" role="tablist">

						<?php foreach ($q_addons as $key => $addon) : ?>

			                <a class="nav-item nav-link <?php echo ($key == 0 ? "active" : ""); ?>" id="nav-addon-<?php echo $addon->id; ?>-tab" data-toggle="tab" href="#nav-addon-<?php echo $addon->id; ?>" role="tab" aria-controls="addon-<?php echo $addon->id; ?>" aria-selected="true"><?php echo $addon->name; ?></a>

						<?php endforeach; ?>
			        </nav>

			        <div class="tab-content" id="nav-tabContent">

			        	<?php foreach ($q_addons as $key => $addon) : ?>

			            <div class="tab-pane fade <?php echo ($key == 0 ? "show active" : ""); ?>" id="nav-addon-<?php echo $addon->id; ?>" role="tabpanel" aria-labelledby="nav-addon-<?php echo $addon->id; ?>-tab">
			                <div class="row">
			                	<?php
			                	$q_features = $wpdb->get_results("SELECT * FROM {$table_prefix}addon_features WHERE addon_id = {$addon->id}"); ?>
			                	<?php foreach ($q_features as $feature) : ?>

			                    <div class="col-sm-4<?php //echo intval(12/count($q_features)); ?>">
			                        <div class="card">
			                            <div class="card-body">
			                                <div class="type">
			                                    <?php echo $feature->name; ?>
			                                </div>
			                                <div class="price">
												<label class="checkbox"><span class="gradient-text"><?php echo $feature->price_text; ?></span>
													<input type="checkbox" name="addon_features[]" value="<?php echo $feature->id; ?>">
													<span class="checkmark"></span>
												</label>
			                                </div>
			                                <?php if($feature->description) : ?>
			                                <div class="per">
			                                    <?php echo $feature->description; ?>
			                                </div>
				                            <?php endif; ?>
			                            </div>
			                        </div>
			                    </div>

			                	<?php endforeach; ?>

			                </div>

			                <div class="d-flex justify-content-center">
			                    <a href="javascript:void(0);" class="btn btn-blue btn-custom show-my-summary">Show My Summary</a>
			                </div>

						</div>

			            <?php endforeach; ?>

			        </div>

			    <?php endif; ?>

			</div>

		</div>
	</section>

	<?php function addons_inline_script(){ ?>
	<script type="text/javascript">
	window.addEventListener('DOMContentLoaded', function() {
		(function($) {

			$(document).ready(function(){
				var package = 0;
				var package_name = "";
				var addon_features = [];

				$(".package-select").on("click",function(){
					package = $(this).attr("package-id");
					package_name = $(this).attr("package-name");
					// alert("You have selected " + package_name);
				});

				$(".show-my-summary").on("click",function(){
					addon_features = [];
					$('input[name^="addon_features"]:checked').each(function() {
					    // alert($(this).val());
					    addon_features.push($(this).val());
					});
					// alert("package_id = "+package+"\naddon-features="+addon_features+"");
					window.location = '<?php bloginfo('url'); ?>'+'/checkout/?package=' + package + '&addon_features=' + addon_features;
				});

			});

		})(jQuery);
    });
	</script>
	<?php
	}
	add_action('wp_footer','addons_inline_script', 50);

}

add_shortcode( 'bd_packages', 'bd_packages_lists' );

require (dirname(__FILE__)."/fpdf/fpdf.php");

function bd_generate_package_summary()
{
	if(isset($_GET['bd_package_summary']))
	{
		global $wpdb;
		$table_prefix = "bd_ppt_";
		$package_id = 0;
		$addon_features = array();

		if(isset($_GET['package']))
		{
			$package_id = $_GET['package'];
		}
		if(isset($_GET['addon_features']))
		{
			$addon_feature_ids = $_GET['addon_features'];
		}

		$selected_package	= $wpdb->get_row("SELECT * FROM {$table_prefix}packages WHERE id IN ({$package_id})");
		$selected_addons 	= $wpdb->get_results("SELECT * FROM {$table_prefix}addon_features WHERE id IN ({$addon_feature_ids})");
		$total_price = 0;

		$pdf = new FPDF('P', 'mm', array(210, 297));

		//-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif

		$pdf->AddPage();
		$pdf->SetFont('Arial', '', 10);
		$pdf->Image(of_get_option('site_logo'),10,10);
        $pdf->Cell(0, 0, "Phone: " . of_get_option('business_phone'), 0, 0, 'R');
        $pdf->Ln(6);
        $pdf->Cell(0, 0, "Email: " . of_get_option('business_email'), 0, 0, 'R');
        $pdf->Ln(6);
        $pdf->Cell(0, 0, "Website: " . of_get_option('business_website'), 0, 0, 'R');
        $pdf->Ln(6);
        $pdf->Cell(0, 0, of_get_option('business_address_1') ." ". of_get_option('business_city_1') ." ". of_get_option('business_state_1') ." ". of_get_option('business_postcode_1'), 0, 0, 'R');
        $pdf->Ln(6);
        $pdf->Cell(0, 0, '', 'B');
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',15);
	    $pdf->Cell(80);
	    $pdf->Cell(30,10,'My Summary',0,0,'C');
	    $pdf->Ln(15);

	    $pdf->SetFont('Arial', '', 10);
        // Column widths
	    $w = array(100, 35, 35);
	    $header = array('Product', 'Price', 'Total');
	    $data = array();

	    $pdf->Cell(10);
	    $pdf->SetFillColor(200, 200, 200);
	    // Header
	    for($i=0;$i<count($header);$i++)
	        $pdf->Cell($w[$i],10,$header[$i],'TB',0,'C', true);
	    $pdf->Ln();

	    if($selected_package){
		    $pdf->Cell(10);
		    $pdf->Cell($w[0],10,$selected_package->title,'TB');
	        $pdf->Cell($w[1],10,$selected_package->price_text,'TB',0,'C');
	        $pdf->Cell($w[2],10,$selected_package->price_text,'TB',0,'C');
	        $pdf->Ln();

			$split_multiple_price = explode("-", $selected_package->price_text);
			//check if package using multiple price or not
			if ( count($split_multiple_price) > 1 ) {
				//split into lower bound & upper bound
				$lower_bound_price  = (int) preg_replace('/\D/', '', $split_multiple_price[0] );
				$upper_bound_price  = (int) preg_replace('/\D/', '', $split_multiple_price[1] );

				if ( strpos( $split_multiple_price[0], 'M' ) > -1  ) {
					$lower_bound_price *= 1000;
				}

				if ( strpos( $split_multiple_price[1], 'M' ) > -1 ) {
					$upper_bound_price *= 1000;
				}

			} else {
				$total_price  = preg_replace('/\D/', '', $selected_package->price_text);
			}
			
	    }

	    // Data
	    if($selected_addons){
		    foreach($selected_addons as $row)
		    {
				$addon_text		= trim($row->price_text);
				$addon_price 	= (int) preg_replace('/\D/', '', $addon_text);
				if ( isset($lower_bound_price) && isset($upper_bound_price) ) {
					$lower_bound_price += $addon_price;
					$upper_bound_price += $addon_price;
				} else {
					$total_price += $addon_price;
				}

		    	$pdf->Cell(10);
		        $pdf->Cell($w[0],10,$row->name,'TB');
		        $pdf->Cell($w[1],10,$row->price_text,'TB',0,'C');
		        $pdf->Cell($w[2],10,$row->price_text,'TB',0,'C');
		        $pdf->Ln();
		    }
		}

		if ( isset($lower_bound_price) && isset($upper_bound_price) ) {
			$array_price = [ $lower_bound_price, $upper_bound_price ];
		} else {
			$array_price = [ $total_price ];
		}
	    // Closing line
	    $pdf->Cell(10);
	    $pdf->Cell(array_sum($w),0,'','T');
	    $pdf->Ln(10);

	    $pdf->SetFont('Arial','B',15);
	    $pdf->Cell(110);
	    $pdf->Cell(30,10,'Cart Totals',0,0,'L');
	    $pdf->Ln();
	    $pdf->Cell(110);
	    $pdf->Cell(70,0,'','T');
	    $pdf->Ln();
	    $pdf->SetFont('Arial','',10);
	    $pdf->Cell(110);
		$pdf->Cell(35,10,'Sub Total',0,0,'L');
		display_formatted_total_price($pdf, $array_price);
		/*if ( isset($lower_bound_price) && isset($upper_bound_price) ) {
			$format_lower_bound = number_format($lower_bound_price);
			$format_upper_bound = number_format($upper_bound_price);
			$pdf->Cell(35,10,"$". $format_lower_bound . " - " . $format_upper_bound . " +",0,0,'L');
		} else {
			$format_total_price = number_format($total_price);
			$pdf->Cell(35,10,"$" . $format_total_price . " +",0,0,'L');
		}*/
	    $pdf->Ln();
	    $pdf->Cell(110);
	    $pdf->Cell(70,0,'','T');
	    $pdf->Ln();
	    $pdf->Cell(110);
	    $pdf->Cell(35,10,'Total',0,0,'L');
	    if ( isset($lower_bound_price) && isset($upper_bound_price) ) {
			$format_lower_bound = number_format($lower_bound_price);
			$format_upper_bound = number_format($upper_bound_price);
			$pdf->Cell(35,10,"$". $format_lower_bound . " - " . $format_upper_bound . " +",0,0,'L');
		} else {
			$format_total_price = number_format($total_price);
			$pdf->Cell(35,10,"$" . $format_total_price . " +",0,0,'L');
		}

		if(isset($_GET['bd_package_summary']) && $_GET['bd_package_summary'] == "")
		{
			$pdf->Output();
			die();
		}
		else if(isset($_GET['bd_package_summary']) && $_GET['bd_package_summary'] == 1)
		{

			// email stuff (change data below)
			$to = $_POST['emailto'];
			$from = of_get_option('business_email');
			$subject = "Keeping Company - My Package Summary PDF";
			$message = $_POST['messages'];

			// a random hash will be necessary to send mixed content
			$separator = md5(time());

			// carriage return type (we use a PHP end of line constant)
			$eol = PHP_EOL;

			// attachment name
			$filename = "my-package-summary.pdf";

			// encode data (puts attachment in proper format)
			$pdfdoc = $pdf->Output("", "S");
			$attachment = chunk_split(base64_encode($pdfdoc));

			// main header
			$headers  = "From: Keeping Company <".$from.">".$eol;
			$headers .= "MIME-Version: 1.0".$eol;
			$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

			// no more headers after this, we start the body! //

			$body = "--".$separator.$eol;
			$body .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
			// $body .= "This is a MIME encoded message.".$eol;

			// message
			$body .= "--".$separator.$eol;
			$body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
			$body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
			$body .= $message.$eol;

			// attachment
			$body .= "--".$separator.$eol;
			$body .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol;
			$body .= "Content-Transfer-Encoding: base64".$eol;
			$body .= "Content-Disposition: attachment".$eol.$eol;
			$body .= $attachment.$eol;
			$body .= "--".$separator."--";

			//send message
			if(mail($to, $subject, $body, $headers))
			{
				echo 'success';
			}
			else
			{
				echo 'Fail';
			}

		}
		else if(isset($_GET['bd_package_summary']) && $_GET['bd_package_summary'] == 2)
		{
			// email stuff (change data below)
			if ( of_get_option('chkout_cy_admin_email') != '' ) {
				$to = of_get_option('chkout_cy_admin_email');
			} else {
				$to = get_option( 'admin_email' );
			}

			$fromname 	= $_POST['name'];
			$fromemail 	= $_POST['email'];
			$fromphone 	= $_POST['phone'];
			$notes 		= $_POST['notes'];
			$date       = date("d-m-Y");

			if ( of_get_option('chkout_cy_subject') != '' ) {
				$subject = of_get_option('chkout_cy_subject');
			} else {
				$subject = "Keeping Company - Client's Package Summary PDF";
			}

			if ( of_get_option('chkout_cy_body_email') != '' ) {
				$message = of_get_option('chkout_cy_body_email');
			} else {
				$message = "Client has sent a package summary below:";
			}

			$message .= "<br><br> {$date} <br><br>
			Name  : ".$fromname."<br>
			Email : ".$fromemail."<br>
			Phone : ".$fromphone."<br>
			Comments : ".$notes;

			// a random hash will be necessary to send mixed content
			$separator = md5(time());

			// carriage return type (we use a PHP end of line constant)
			$eol = PHP_EOL;

			// attachment name
			$filename = "my-package-summary.pdf";

			// encode data (puts attachment in proper format)
			$pdfdoc = $pdf->Output("", "S");
			$attachment = chunk_split(base64_encode($pdfdoc));

			// main header
			$headers  = "From: ".$fromname." <".$fromemail.">".$eol;
			$headers .= "MIME-Version: 1.0".$eol;
			$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

			// no more headers after this, we start the body! //

			$body = "--".$separator.$eol;
			$body .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
			// $body .= "This is a MIME encoded message.".$eol;

			// message
			$body .= "--".$separator.$eol;
			$body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
			$body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
			$body .= $message.$eol;

			// attachment
			$body .= "--".$separator.$eol;
			$body .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol;
			$body .= "Content-Transfer-Encoding: base64".$eol;
			$body .= "Content-Disposition: attachment".$eol.$eol;
			$body .= $attachment.$eol;
			$body .= "--".$separator."--";

			//send message
			if(mail($to, $subject, $body, $headers))
			{
				echo 'success';
			}
			else
			{
				echo 'Fail';
			}

		}
		die();
	}
}

add_action("init", "bd_generate_package_summary");

function display_formatted_total_price( $pdf, $array_price ) {
	if ( count( $array_price ) > 1 ) {
		$format_lower_bound = number_format($array_price[0]);
		$format_upper_bound = number_format($array_price[1]);
		$pdf->Cell(35,10,"$". $format_lower_bound . " - " . $format_upper_bound . " +",0,0,'L');
	} else {
		$format_total_price = number_format( $array_price[0] );
		$pdf->Cell(35,10,"$" . $format_total_price . " +",0,0,'L');
	}
}

//Import classes
require_once (dirname(__FILE__)."/includes/bd_package_list_table.class.php");
require_once (dirname(__FILE__)."/includes/bd_addon_list_table.class.php");



?>
