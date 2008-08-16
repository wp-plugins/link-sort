<?php
/*  Copyright 2008  CoCo  (email : hylwrcool@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin Name: CoCo Linksort
Plugin URI: http://www.acwind.net/blog/?p=478
Description: Sort your links by date, url, name, description, etc.
Version: 0.2
Author: CoCo
Author URI: http://www.acwind.net/blog
*/
$cocolinksort_domain = 'cocolinksort';
$cocolinksort_is_setup = 0;

function cocolinksort_setup()
{
   global $cocolinksort_domain, $fabfunc_is_setup;

   if($fabfunc_is_setup) {
      return;
   } 
    $locale = get_locale();
    $mofile = WP_CONTENT_DIR . "/plugins/link-sort/languages/" . $cocolinksort_domain . "-". $locale.".mo";
	load_textdomain($cocolinksort_domain, $mofile);
    //load_plugin_textdomain($cocolinksort_domain, $mofile);
}

function sort_link_array($links, $sort_id, $order_type='ASC'){
	//echo 'return($a[\''.$sort_id.'\''> $b[\''.$sort_id.'\']);';
	if($order_type == 'ASC'){
		$sortArray = create_function('$a, $b', 'return($a->'.$sort_id.' > $b->'.$sort_id.');');
	}else{
		$sortArray = create_function('$a, $b', 'return($a->'.$sort_id.' < $b->'.$sort_id.');');
	}
	usort($links, $sortArray);
	return $links;
}

function sort_link_by_user_defined($links){
    $links_sorted = array();
	$links_order  = array();
	$result       = array();
	$count = count($links);
	for($i = 0; $i < $count; $i++){
	    $links_sorted[] = array($links[$i], 0);
	}
	
	if(get_option("cocolinksort_links_order") == false){
	    
	} else {
	    $links_order = get_option("cocolinksort_links_order");
	}
    
	$count = count($links_sorted);
    for($i = 0; $i < $count; $i++){
        $id = $links_sorted[$i][0]->link_id;
        if(array_key_exists($id, $links_order)){
            $links_sorted[$i][1] = $links_order[$id];
        }
    }
    
    $sortArray = create_function('$a, $b', 'return($a[1] > $b[1]);');
    usort($links_sorted, $sortArray);
    for($i = 0; $i < $count; $i++){
        $result[] = $links_sorted[$i][0];
    }
    return $result;
}

function acwind_linksort($content){
	//$res = $content;
	if(get_option("als_sort_id") == false){
		return $content;
	}
	if(get_option("als_order_type") == false){
		return $content;
	}
	$als_sort_id    = get_option("als_sort_id");
	$als_order_type = get_option("als_order_type");
	if($als_sort_id <> 'user_define'){
	   $res = sort_link_array($content, $als_sort_id, $als_order_type);
	}else{
	   $res = sort_link_by_user_defined($content);
	}
	return $res;
}


function list_bookmarks_sorted($user_defined){
    global $cocolinksort_domain;
    $links        = get_bookmarks();
	$css_display  = ($user_defined) ? 'block' : 'none';
    $links_sorted = sort_link_by_user_defined($links);
    $count        = count($links_sorted);

    echo "<div id='div_links_sorted' style='display:{$css_display};border:1px dotted red;'>";
    if($count > 0){
        echo "<h2>".__('Sort by user defined order', $cocolinksort_domain)."</h2>";
        //_e('Change the number of link ', $cocolinksort_domain);
    }
    //print_r($links_sorted);
    for($i = 0; $i < $count; $i++){
        $link_id   = $links_sorted[$i]->link_id;
        $link_url  = $links_sorted[$i]->link_url;
        $link_name = $links_sorted[$i]->link_name;
        $order_no  = $i + 1;
        echo "&nbsp;&nbsp;&nbsp;<input size='1' name='cocolinksort_links_order[{$link_id}]' value='{$order_no}' />";
        echo "&nbsp;<a href='{$link_url}'>{$link_name}</a>";
        echo "<br />";
    }
    echo "</div>";
}

function acwind_linksort_set(){
	//global $wpdb;
	global $cocolinksort_domain;
	$locale = get_locale();
	$sort_id = array(
		'link_id'		=> __('Date Added', $cocolinksort_domain),
        'link_url'		=> __('URL', $cocolinksort_domain),
        'link_name'		=> __('Link Name', $cocolinksort_domain),
        //'link_image'=> '',
        'link_target'	=> __('Link Target', $cocolinksort_domain),
        'link_category'	=> __('Link Category', $cocolinksort_domain),
        'link_description' => __('Link Description', $cocolinksort_domain),
        //'link_visible'=> Y
        //'link_owner'=> 1
        'link_rating'=> __('Link Rating', $cocolinksort_domain),
        'link_updated'=> __('Date Updated', $cocolinksort_domain),
        //'link_rel'=> 
        //'link_notes'=> 
        //'link_rss'=> 
        //'object_id'=> 21
        //'term_taxonomy_id'=> 2
        //'term_order'=> 0
        //'term_id'=> 2
        //'taxonomy'=> link_category
        //'description'=> 
        //'parent'=> 0
        //'count'=> 19
        //'recently_updated'=> 0
	);
	
	if(get_option("als_sort_id") == false){
		add_option("als_sort_id", 'link_name');
	}
	if(get_option("als_order_type") == false){
		add_option("als_order_type", 'ASC');
	}
	$als_sort_id    = get_option("als_sort_id");
	$als_order_type = get_option("als_order_type");
	?>
	<div class="wrap">
    <script>   
	   jQuery(document).ready(function() {
	       jQuery("select").change(function() {
		      if(this.value == 'user_define'){
		          jQuery('#div_links_sorted').css('display', 'block');
		      }else{
		          if(this.name == 'als_sort_id'){
		              jQuery('#div_links_sorted').css('display', 'none');
		          }
		      }
	       });
        });
    </script> 
		<h2><? _e('Link Sort Options', $cocolinksort_domain);?></h2>
		<?if($locale <> 'zh_CN'){?>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
            <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
        </form>
        <?}?>
		<form method="post" action="options.php">
		<input type="hidden" name="page_options" value="als_sort_id,als_order_type,cocolinksort_links_order" />
		<input type="hidden" name="action" value="update" />
		<?php wp_nonce_field('update-options'); ?>
		
  	  <table width="100%" cellspacing="2" cellpadding="5" class="editform">
		    <tr valign="top"> 
		      <th width="15%" scope="row"><? _e('Sort By', $cocolinksort_domain);?> :</th> 
		      <td>
				<select name='als_sort_id'">
					<?php
					while(list($key, $value) = each($sort_id)){
						$selected = ($als_sort_id == $key) ? 'selected' : '';
						echo "<option value='{$key}' {$selected}>{$value}</option>";
					}
					?>
					<option value="user_define" <?if($als_sort_id=='user_define')echo "selected";?>><? _e('Custom Order', $cocolinksort_domain);?></option>
				</select>
		  	  </td>
			</tr>
			<tr valign="top"> 
		      <th width="15%" scope="row"><? _e('Sort Order', $cocolinksort_domain);?>:</th> 
		      <td>
		        <select name='als_order_type'>
					<option value="ASC"  <?if($als_order_type == 'ASC')  echo 'selected';?>><?_e('Ascending Order',  $cocolinksort_domain);?></option>
					<option value="DESC" <?if($als_order_type == 'DESC') echo 'selected';?>><?_e('Descending Order', $cocolinksort_domain);?></option>
				</select>
		      </td>
		    </tr>
		</table>
		<? 
		$user_defined = ($als_sort_id == 'user_define') ? true : false;
		list_bookmarks_sorted($user_defined); 
		?>
        
		<p class="submit">
      		<input type="submit" name="Submit" value="<?_e('Save Changes', $cocolinksort_domain);?>" />
    	</p>
		</form>
	</div>
	<?php
}




function als_add_options(){
	add_options_page("Link Sort", "Link Sort", 7, __FILE__, 'acwind_linksort_set');
}

cocolinksort_setup();
add_filter('get_bookmarks', 'acwind_linksort');
add_action('admin_menu', 'als_add_options');






?>