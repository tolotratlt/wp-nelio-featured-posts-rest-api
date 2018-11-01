<?php
/*

Plugin Name: Nelio Featured Posts REST API
Plugin URI: 

Description: Add REST API endpoint to Nelio Featured Posts

Version: 0.1

Author: tlt

Author URI: https://tolotratlt.wordpress.com/

*/


class NelioExtended_Plugin
{

    public function __construct()
    {
		add_shortcode( 'neliofeaturedpost', array($this, 'featuredpost_func') );		
		add_action( 'rest_api_init', array($this, 'my_register_route') );
		//add featured media source path to responce
		add_action( 'rest_api_init', array($this, 'add_thumbnail_to_JSON' ));
	}
	
	//custom shortcode response test
	public function featuredpost_func($param)
    {
		$res = '';
		if( method_exists( 'NelioFPSettings', 'get_list_of_feat_posts' ) )
		{
			$fps = NelioFPSettings::get_list_of_feat_posts();
			if ( count( $fps ) > 0 ) {
				$res = '<pre>';
				$res .= var_dump($fps);
				$res .= '</pre>';
			}
			/*TODO: format the output with HTML/CSS*/
		}
        return $res;
    }
	
	/*Custom route feature post*/
	public function my_register_route(){
		register_rest_route( 'nelioextended', 'featuredposts', array(
                    'methods' => 'GET',
                    'callback' => array($this, 'my_custom_route'),
                )
            );
	}
	
	public function my_custom_route(\WP_REST_Request $request){
		//return rest_ensure_response( 'Hello World!' );
		//custom format
		//return rest_ensure_response( NelioFPSettings::get_list_of_feat_posts() ); 
		
		//format to wordpress normal rest response
		$posts_arr = array();
		if( method_exists( 'NelioFPSettings', 'get_list_of_feat_posts' ) )
		{
			$posts = NelioFPSettings::get_list_of_feat_posts();
			
			$rest_post = new WP_REST_Posts_Controller('post');

			foreach ($posts as $post) {
				$posts_arr[] = $rest_post->prepare_item_for_response($post, $request)->data;
			}
		}
		return $posts_arr;
	}
	
	/*ADD custom option for featured thumbnail source*/
	public function add_thumbnail_to_JSON(){		
		//Add featured image
		register_rest_field( 
			'post', // Where to add the field (Here, blog posts. Could be an array)
			'featured_image_src', // Name of new field (You can call this anything)
			array(
				'get_callback'    => array($this, 'get_image_src'),
				'update_callback' => null,
				'schema'          => null,
				 )
		);
	}

	public function get_image_src( $object, $field_name, $request ) 
	{
		$feat_img_array = wp_get_attachment_image_src(
			$object['featured_media'], // Image attachment ID
			'thumbnail',  // Size.  Ex. "thumbnail", "large", "full", etc..
			true // Whether the image should be treated as an icon.
		);
		return $feat_img_array[0];
	}
}

new NelioExtended_Plugin();