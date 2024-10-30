<?php



class Blog2Print_1buch_com_admin {
	
	var $info = array();
	var $config = array();
	var $error_text;
	var $info_text;
	var $text_domain;
	
	
	
	function Blog2Print_1buch_com_admin() {
		$this->init();
	}
	
    function init() {
		
	// Determine installation path & url
		$install_dirurl_tmp = dirname(__FILE__); // basename strips all parent directories of the directory of the given filename
		$plugin_dir = str_replace("/inc", "", $install_dirurl_tmp);
				
		$this->info["blog2print_dir"] = $plugin_dir;
		$this->info["userdata_dir"] = $plugin_dir."/userdata";
		
		/* TODO: prüfen, ob userdata_dir existiert und beschreibbar ist */
		
		$this->config = parse_ini_file($plugin_dir."/config.ini");				
				
		$this->text_domain = "1buch_com_blog2print";
		load_plugin_textdomain($this->text_domain, PLUGINDIR.'/'.dirname(plugin_basename(__FILE__))."/lang", dirname(plugin_basename(__FILE__))."/lang");
		
		//$this->insertSomePosts();
		
	}
	

	
	
	
	function showAdminPage() {
			
		require_once($this->info['blog2print_dir']."/lib/pet.class.php");
		$zipped = "no";
	
		
		if($_POST['sendBlog']) {  //create XML with blog data
			
			//get requested data
            $posts = $this->getPosts();
            //set up posts for users selection
            $posts_tpl_array = array();
            foreach($posts as $post) {
			$item_tpl = array(
			     id => $post->ID,
			     title => $post->post_title,
			     item_url => $post->guid		
			);
		    array_push($posts_tpl_array, $item_tpl);
		}	
            
           
            if(count($posts) < $this->config['min_items']) {
            	$this->error_text = __("Not enough items were found to create a book", $this->text_domain).count($posts);
            }
                        
			//create xml and save it as file
			$xml = $this->createXML($posts);	

			//try to write the xml - no error handling
			if($_POST['saveXML'] == "on") {
				
				$xml_file = $this->info['blog2print_dir']."/blog.xml";
				
				$xmlh = fopen($xml_file, "w");
				if($xmlh === FALSE) {
					echo "<p><b>Can't create xml file on your server</b> at ".$xml_file."<br />Please check the permissions.</p>";									
				}
				else {
					fwrite($xmlh, $xml);
					fclose($xmlh);	
					echo "<p>".__("XML file was written to:", $this->text_domain)." /wp-content/plugins/blog2print/blog.xml</p>";
				}
							
			}
			
			//echo $xml;
            
            if($this->error_text == "") {
            
            	//zip if php function is available
                if(function_exists("gzcompress")) {
                	$xml = gzcompress($xml);
                	$zipped = "yes";                	
                }
            	
				//show form to submit the generated xml to our print-on-demand service to generate the PDF file			            
				$template = new pet();
            	$template->readFile($this->info['blog2print_dir'].'/templates/collect_content_success.tpl.html');
            	
            	//add text snippets
            	$template->assign(__("Your blog as book", $this->text_domain), 'admin_page_headline');        
        		$template->assign(__("The data to create a book based on your weblog are collected now.", $this->text_domain), 'formresponse_intro');        
      			$template->assign(__("Totally ", $this->text_domain)." ".count($posts)." ".__(" posts were found.", $this->text_domain), 'formresponse_countinfo');               
      			$template->assign(__("View book preview", $this->text_domain), 'formresponse_submit_text');        
      			$template->assign(__("This form submits your article data to the 1buch.com server to create the book PDF file.<br /><br />You can download the generated PDF file (for free) or can order a real book directly on 1buch.com", $this->text_domain), 'formresponse_nextsteps_text');
      			//$template->assign(__("All selected posts will be printed", $this->text_domain), 'select_post_info_text');     
      			
                		
            	$template->assign('Blog2Print', 'plugin');
            	$template->assign($this->info_text, 'info_text');
            	$template->assign(count($posts), 'post_count');
            	$template->assign(urlencode($xml), 'xml_blog_data');
            	$template->assign($zipped, 'zipped');
            	$template->assign($this->generateUniqueID(), 'uid');
            	$template->assign($this->config['xml_to_pdf_url'], 'xml_to_pdf_url');
            	$template->assign($this->config['xml_to_pdf_url']."/promo_images/promo.png", 'promoimage_url');
            	$template->assign(get_locale(), 'form_wordpress_locale');
            	$template->assign($posts_tpl_array, 'all_posts');
            	
            	$template->parse();
				$template->output();			 
            }
            else {
            	//error text
            	echo $this->error_text;            	
            }
        
			
			
		}
		else { //show plugin admin page
		
		$template = new pet();
        $template->readFile($this->info['blog2print_dir'].'/templates/admin.tpl.html');
        $template->assign('Blog2Print', 'plugin'); 
        
        //days for select box
        $days = array();
        $a=1;
		while($a <= 31) {
           
        	$day_option = array (
        	             "value" => $a,
        	             "name"  => $a
        	);
        	array_push($days, $day_option);        	
           $a++;	
        }   
        $template->assign($days, 'period_days');
        
        //month for select box
        $month = array();
        $a=1;
        setlocale( LC_ALL, "de_DE");
		while($a <= 12) {
           
        	$month_option = array (
        	             "value" => $a,
        	             "name"  =>  utf8_encode(strftime("%B", mktime(0, 0, 0, $a, 12, 1997)))
        	);
        	array_push($month, $month_option);        	
           $a++;	
        }   
        $template->assign($month, 'period_month');
        
        
        //years for period-select box
        $curr_year = date(Y, time());
        $periods = array();
        $i=$curr_year;
        $until_year = $curr_year-15;
        while($i > $until_year) {
           
        	$period_option = array (
        	             "value" => $i,
        	             "name"  => $i
        	);
        	array_push($periods, $period_option);        	
           $i--;	
        }
        
                
        $template->assign($periods, 'period_years');                
        $template->assign($_SERVER['PHP_SELF'], "form_url");
        
        //add text snippets
        $template->assign(__("Your blog as book", $this->text_domain), 'admin_page_headline');        
        $template->assign(__("This plugin offers the possibility to create a book based on your weblog.", $this->text_domain), 'admin_intro');
        $template->assign(__("Add the title for your book", $this->text_domain), 'form_book_title');
        $template->assign(__("Add the author for your book", $this->text_domain), 'form_book_author');
        $template->assign(__("Select the items to be included in your book", $this->text_domain), 'form_select_period');
        $template->assign(__("select a fixed period", $this->text_domain), 'form_select_period_fixed');
        $template->assign(__("All weblog items", $this->text_domain), 'form_select_period_fixed_all_items');
        $template->assign(__("Current year", $this->text_domain), 'form_select_period_current_year');
        $template->assign(__("Last year", $this->text_domain), 'form_select_period_last_year');
        $template->assign(__("or", $this->text_domain), 'or');
        $template->assign(__("Items from", $this->text_domain), 'form_items_start');
        $template->assign(__("Items until", $this->text_domain), 'form_items_end');
        $template->assign(__("Publish comments too", $this->text_domain), 'form_publish_comments');
        $template->assign(__("Go!", $this->text_domain), 'form_submit_text'); 
 		$template->assign(__("Save xml data", $this->text_domain), 'form_save_xml');        
        $template->assign(__("After completing this process you can download a generated PDF file (for free) or order a real book directly.", $this->text_domain), 'form_disclaimer');
                
        $template->parse();
		$template->output(); 
		
		}
		
		
	}
	
	/*
	 * This method collects all posts the user want to see in his book.
	 * 
	 */
	function getPosts() {
				
		$start_timestamp;
		$end_timestamp;
		$check_years = array();		//years we need data for
		$temp_posts = array();       //temp array 
		$posts_for_book = array();		
		
		// check requested daterange
		if($_POST['period'] != "empty" && $_POST['period'] != "all") { // global daterange (last year etc.)
			
			switch($_POST['period']) {				
				case "last_year":	
					$last_year = date('Y',current_time('timestamp'))-1;
				    array_push($check_years, $last_year);				    
				    $this->info_text = __("All items from ", $this->text_domain).$last_year.__(" were selected.", $this->text_domain);
        			$start_timestamp = mktime(0,0,0,1,1,$last_year);			
					$end_timestamp = mktime(0,0,0,12,31,$last_year);
			
					break;
				case "current_year":				
				default:
					$this_year = date('Y',current_time('timestamp'));
					array_push($check_years, $this_year);					
					$this->info_text = __("All items from ", $this->text_domain).$this_year.__(" were selected.", $this->text_domain);
        			$start_timestamp = mktime(0,0,0,1,1,$this_year);			
					$end_timestamp = mktime(0,0,0,12,31,$this_year);
			}
			
		}
		elseif($_POST['startYear'] != "empty" && $_POST['endYear'] != "empty") { //date range defined by the user 
			
			$startDay = 1;
			$startMonth = 0;
			$endDay = 1;
			$endMonth = 0;			
			
			if($_POST['startDay'] != "empty") {
				$startDay = $_POST['startDay'];
			}
			if($_POST['startMonth'] != "empty") {
				$startMonth = $_POST['startMonth'];
			}
			if($_POST['endDay'] != "empty") {
				$endDay = $_POST['endDay'];
			}
			if($_POST['endMonth'] != "empty") {
				$endMonth = $_POST['endMonth'];
			}			
			$start_timestamp = mktime(0,0,0,$startMonth,$startDay,$_POST['startYear']);			
			$end_timestamp = mktime(23,59,59,$endMonth,$endDay,$_POST['endYear']);
			
			//add years we need data for
			$s = $_POST['startYear'];
			while($s <= $_POST['endYear']) {
				array_push($check_years, $s);
				$s++;
			}			
			$this->info_text = __("All items from ", $this->text_domain).date("Y-m-d", $start_timestamp).__(" until ", $this->text_domain).date("Y-m-d", $end_timestamp).__(" were selected ", $this->text_domain);
        			
		}
		else { 
			//select all posts				
			$this->info_text = __("All items were selected.", $this->text_domain);
			$start_timestamp = mktime(0,0,0,0,1,1981);	
			// we need the timestamp from the current day - not time()
			$this_day = date("d", time());
			$this_month = date("m", time());
		    $this_year  = date("Y", time());
			$end_timestamp = mktime(23, 59, 60, $this_month, $this_day, $this_year );
		}
		
						
		/*
		 * request all the years we need data for
		 * (there is no date range functionality at the moment)
		 * 
		 */
		if(!empty($check_years)) {
			
			foreach($check_years as $year) {
				$posts = query_posts(array(			
					'year'=> $year, 
		        	'oderby' => 'date', 
					'order'=>'ASC',
				    'posts_per_page' => -1
				));				
				foreach($posts as $post) {
				    array_push($temp_posts, $post);
				}
			}
			
		}
		else { // get all data
			$temp_posts = query_posts(array(
		        'oderby' => 'date', 
				'order'=>'ASC',		
			    'posts_per_page' => -1	
			));
			
		}		
		
		//select only the posts we want to have
		foreach($temp_posts as $mypost) {			
			$ts_post = strtotime($mypost->post_date);			
			if($ts_post >= $start_timestamp && $ts_post <= $end_timestamp && $mypost->post_status == "publish") {
				array_push($posts_for_book, $mypost);
			}
		}

		
		
		return $posts_for_book;		
	}

	/*
	 * Creates the XML
	 * 
	 */
	function createXML($posts) {		
		
		//collect general data for the XML
		$book_title = $_POST['booktitle'];
		$blog_url = get_option('siteurl');
		$blog_desc = $_POST['bookdesc'];
		$author = $_POST['bookauthor'];
		 
		
		$xml = '<?xml version="1.0" encoding="utf-8"?>
				<rss version="2.0" 
       				xmlns:wm="http://wissenmedia.de/ns/1.0"
	   			>
			<channel>
			<title><![CDATA['.$this->enc($book_title).']]></title>
	        <link>'.$blog_url.'</link>
	        <description><![CDATA['.$this->enc($blog_desc).']]></description>
	        <wm:blogAuthor><![CDATA['.$this->enc($author).']]></wm:blogAuthor>  
	        <wm:pluginVersion><![CDATA['.$this->config['version'].']]></wm:pluginVersion>
			';		
		
		foreach($posts as $post) {
						
			//print_r($post);
			$authordata = get_userdata($post->post_author);			
			if($authordata->display_name != "") {
				$author = $authordata->display_name;
			}
			else {
				$author = $authordata->user_login;
			}
			
			$xml .= '<item>
			<title><![CDATA['.$this->enc($post->post_title).']]></title>
			<link>'.$post->guid.'</link>
			<description></description>
			<pubDate>'.$this->formatDate($post->post_date).'</pubDate>
			<wm:author><![CDATA['.$this->enc($author).']]></wm:author>
			<wm:itemid>'.$post->ID.'</wm:itemid>
    		<wm:fulltext><![CDATA['.$this->enc($this->handleThumbnails($post->post_content)).']]></wm:fulltext>';			
						
			$item_comments = get_approved_comments($post->ID);			
			
			if(count($item_comments) > 0 && $_POST['printComments'] == "on") {
				
				$xml .= '<wm:comments>';
				
				foreach($item_comments as $comment) {
		
				$xml .= '
					<wm:comment>
		    			<wm:commentText><![CDATA['.$this->enc($comment->comment_content).']]></wm:commentText>
						<wm:commentAuthor><![CDATA['.$this->enc($comment->comment_author).']]></wm:commentAuthor>
						<wm:commentDate>'.$this->formatDate($comment->comment_date, "comment_format").'</wm:commentDate>
					</wm:comment>
				';
		
				}
				$xml .= '</wm:comments>';
			}
			else {
				$xml .= '<wm:comment />';
			}
		
	 		$xml .= '</item>';			
		}
		
		$xml .= '</channel>	
         </rss>';
		
		return $xml;		
	}
	
	
	/*
	 * Handles encoding - makes an utf8-encoding if necessary
	 * 
	 */
	function enc($text) {
		$charset = get_option('blog_charset');		
		if(strtoupper($charset) != "UTF-8") {
			$text = utf8_encode($text);
		}		
        $text = str_replace("ä", "ae", $text);
		return $text;
	}
	
	/*
	 * Date layout for print version
	 * 
	 */
	function formatDate($string, $style="date_format") {	
	  $timestamp = strtotime($string);
	  	  
	  if(get_locale() == "de_DE" || get_locale() == "de_AT") {
	  	$style = "date_format_de";
	  	setlocale(LC_ALL, get_locale());
	  }  	 	  
	  
	  $formated_date = strftime($this->config[$style], $timestamp);		  	
	  return utf8_encode($formated_date);		
	}
	
	
	/*
	 * generates a unique ID for the current request at 1buch.com
	 */
	function generateUniqueID() {
		$server_id_part = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$time = time();		
		$random = rand();
		$id_string = $server_id_part.$time.$random;		
		$uid = date("YmdHs").hash("md5", $id_string);		
		return $uid;		
	}
	
	
	/**
	 * Replace thumbnail URL with other URL
	 *
	 * @param String $posts_for_book
	 * @return String
	 */
	function handleThumbnails($post_for_book) {
		if($this->config['thumnail_replace_from'] != "") {			
			$post_for_book = str_replace($this->config['thumnail_replace_from'], $this->config['thumnail_replace_to'], $post_for_book);
		}
		return $post_for_book;
	}
	
	function getPostsForSelection() {
		
		$tpl_array = array();
		
		$posts = query_posts(array(
		        'oderby' => 'date', 
				'order'=>'ASC',		
			    'posts_per_page' => -1	
			));
			
		foreach($posts as $post) {
			$item_tpl = array(
			     id => $post->ID,
			     title => $post->post_title			
			);
		    array_push($tpl_array, $item_tpl);
		}		
		
		return $tpl_array;
	}
	
	
}



?>