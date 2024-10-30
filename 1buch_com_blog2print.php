<?php
/*
Plugin Name: Blog2Print
Plugin URI: http://www.1buch.com/de/blog2print/
Description: Create a book based on your weblog - download it as PDF (for free) or order it as real book at 1buch.com 
Version: 0.5
Author: Kai-Ingo Neumann
Author URI: http://www.1buch.com/de/team/kai_ingo_neumann/


Copyright 2008  Kai-Ingo Neumann  (email : Kai-Ingo.Neumann@1buch.com)

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


$Blog2Print_1buch_com = new Blog2Print_1buch_com();

class Blog2Print_1buch_com {	
	
    function Blog2Print_1buch_com() {    	
		load_plugin_textdomain("1buch_com_blog2print", PLUGINDIR.'/'.dirname(plugin_basename(__FILE__))."inc/lang", dirname(plugin_basename(__FILE__))."inc/lang");
    	add_action('plugins_loaded', array(&$this, 'initBlog2Print'));
	}
	
	function initBlog2Print() {		
				
		// Hook for adding admin menus
        add_action('admin_menu', 'mt_add_pages');
        add_action('admin_head', 'addHeaderCode');
	}
		
	
}

//end of class

function mt_add_pages() {
	    
		if ( function_exists('add_submenu_page') ){		
			load_plugin_textdomain("1buch_com_blog2print", PLUGINDIR.'/blog2print/inc/lang', "blog2print/inc/lang");
			
			$plugin_page_title = __("Blog2Print by 1Buch.com - Create a book based on your weblog", "1buch_com_blog2print");
			$plugin_navlink    = __("Your weblog as book", "1buch_com_blog2print");	
			add_submenu_page('plugins.php', $plugin_page_title, $plugin_navlink, 8, __FILE__, 'show1buchAdminPage');
		}		
		
}
	
function show1buchAdminPage() {		
      require_once("inc/1buch_com_blog2print_admin.php");
	  $admin = new Blog2Print_1buch_com_admin();
	  $admin->showAdminPage();
}

 
function addHeaderCode() {          
    echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/blog2print/css/style.css" />' . "\n";  	    
    echo "<script type='text/javascript'>
	function checkData() {
		var title 	= document.blog2printform.booktitle.value;
		var author 	= document.blog2printform.bookauthor.value;		
		if(title == '' ||  author == '' ) {
			alert('".__("Please add the title and the author for your book", "1buch_com_blog2print")."');
			if(title == '') {
				document.blog2printform.booktitle.style.border = '1px solid red';
			}
			if(author == '') {
				document.blog2printform.bookauthor.style.border = '1px solid red';
			}			
			return false;
		}
	   return true;
	}	
	</script>";
}


?>
