<?php
/*
Plugin Name: CloudFlare URL Replacement
Plugin URI: http://maurizio.mavida.com/wordpress-cloudflare-url-replacement/
Description: Super simle replacer for CloudFlare user. 
License: GPL
Version: 0.7
Author: Maurizio Pelizzone
Author URI: http://maurizio.mavida.com

Installation:
** This is a beta version - no guarantee for production use
Place the cloudflare-url-replacement.php file in your /wp-content/plugins/ directory
[before activate] Check your CloudFlare Account and verify that DNS have been updated
Activate through the WordPress administration panel.
Edit CloudFlare Url in the plugin option page (under general option)


==========================================================================

                                                                    
 License: GPL                                                       
                                                                    
 CloudFlare URL Replacement Plugin 
 Copyright (C) 2012, Maurizio Pelizzone, http://maurizio.mavida.com
 All rights reserved.                                               
                                                                    
 This program is free software; you can redistribute it and/or      
 modify it under the terms of the GNU General Public License        
 as published by the Free Software Foundation; either version 2     
 of the License, or (at your option) any later version.             
                                                                    
 This program is distributed in the hope that it will be useful,    
 but WITHOUT ANY WARRANTY; without even the implied warranty of     
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      
 GNU General Public License for more details.                       
                                                                    
 You should have received a copy of the GNU General Public License  
 along with this program; if not, write to the                      
 Free Software Foundation, Inc.                                     
 51 Franklin Street, Fifth Floor                                    
 Boston, MA  02110-1301, USA                                        
                                                                    

*/


Class CloudFlareUrlReplace {

	private $CloudFlareUrl = "";	

	private $blog_url = "";

	public function __construct(  ) {

		// get value saved in option
		$options = get_option( "cloudflare-url-replacement" );
		$this->CloudFlareUrl = $options["url"];

		// check if CloudFlareUrl is set
		if ( !is_admin() && ($this->CloudFlareUrl != "") ) {

			$this->blog_url = str_replace("http://", "", home_url() );
			$this->CloudFlareUrl = str_replace("http://", "", $this->CloudFlareUrl);
			
			add_filter('the_content', array( $this , 'CloudFlareImageReplace') );
			add_filter('post_thumbnail_html',  array( $this ,'CloudFlareImageReplace') );
			add_filter('widget_text',  array( $this ,'CloudFlareImageReplace') );

			add_filter('script_loader_src',  array( $this ,'CloudFlareScriptReplace') );
		}
		
		// start adrmin setting
		if ( is_admin() ){
				include("class-wp-options-page.php");

				$args = array(
						'parent_slug' => 'options-general.php',      // set menu position (default is apparence)
						'capability' => 'administrator',    // set user privilege (default is administrator)
				
						);
				
				$adminpage = new WP_OptionsPage($args) ;

				$adminpage->setTitle("CloudFlare Admin Page"); // set page title and menu title
				$adminpage->setMenuName("CloudFlare Admin"); // set page title and menu title
				$adminpage->setOptionName("cloudflare-url-replacement"); // set options name saved in wp_options table
				
				$adminpage->addSettingRegion( "Main Setting" , "Enter your static subdomain according in 
					<strong>CloudFlare</strong> panel (without \"http://\") <br /> es. <em>static.your-server.com</em>" ); // add setting region
				
				$adminpage->addSettingField(    "Main Setting", "URL" ); // add setting field

				// Add meta links
				add_filter('plugin_row_meta', array('CloudFlareUrlReplace', 'AddPluginLinks'), 10, 2);

			
			}// admin setting
		
	}

 function AddPluginLinks($links, $file) {
    if ($file == basename(dirname(__FILE__)) . '/' . basename(__FILE__)) {
      $links[] = '<a href="options-general.php?page=cloudflare-admin">Option Page</a>';

    }
    return $links;
  }
	
	/**
	 * hook to replace finded link in post_content, widgets and image thumbnail call with get_the_post_thumbnail
	 */
	function CloudFlareImageReplace ( $content ) {
		
		
		$pattern="/(" .$this->blog_url . ")(\/wp-content\/)(.*)(png|gif|jpg)/";
		$replacement = $this->CloudFlareUrl . "$2$3$4";
		
		return preg_replace($pattern, $replacement,$content );
	
	}
	
	/**
	 * hook to replace link to all enqueue_script / enqueue_style
	 */ 
	function CloudFlareScriptReplace ( $src ) {
		
		$pattern="/(" . $this->blog_url  . ")(\/wp-content\/)(.*)(js|css)/";
		$replacement = $this->CloudFlareUrl . "$2$3$4";
	
		// remove Query Strings From Static Resources	
		$src_parts = explode('?', $src);		
		return preg_replace($pattern, $replacement, $src_parts[0] );
	
	}
	
}

new CloudFlareUrlReplace();



