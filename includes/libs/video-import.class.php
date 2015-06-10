<?php

if( !class_exists('CVM_Vimeo') ){
	require_once CVM_PATH.'includes/libs/vimeo.class.php';
}

class CVM_Video_Import extends CVM_Vimeo{
	
	private $results;
	private $total_items;
	private $page;
	private $errors;
	
	public function __construct( $args ){
		
		$defaults = array(
			'source' 		=> 'vimeo', // video source
			'feed'			=> 'search', // type of feed to retrieve ( search, album, channel, user or group )
			'query'			=> false, // feed query - can contain username, playlist ID or serach query
			'results' 		=> 20, // number of results to retrieve
			'page'			=> 0,
			'response' 		=> 'json', // Vimeo response type
			'order'			=> 'new', // order
		);
		
		$data = wp_parse_args($args, $defaults);
		
		// if no query is specified, bail out
		if( !$data['query'] ){
			return false;
		}
		
		$request_args = array(
			'feed' 		=> $data['feed'],
			'feed_id' 	=> $data['query'],
			/*'feed_type' => '',*/
			'page' 		=> $data['page'],
			'response' 	=> $data['response'],
			'sort' 		=> $data['order']
		);
		parent::__construct( $request_args );
		$content = parent::request_feed();
		
		if( is_wp_error( $content ) || 200 != $content['response']['code'] ){
			if( is_wp_error( $content ) ){
				$this->errors = new WP_Error();
				$this->errors->add( 'cvm_wp_error', $content->get_error_message(), $content->get_error_data() );
			}			
			return false;
		}
		
		$result = json_decode( $content['body'], true );
		
		// set up Vimeo query errors if any
		if( isset( $result['error'] ) ){
			$this->errors = new WP_Error();
			$this->errors->add( 'cvm_vimeo_query_error', __('Query to Vimeo failed.', 'cvm_video'), $result['error_description']);
		}
		
		/* single video entry */
		if( 'video' == $request_args['feed'] ){
			if( isset( $result['uri'] ) ){
				$this->results = $this->format_video_entry( $result );
			}else{
				$this->results = array();
			}
			return;
		}
		
		// processign multi videos playlists
		if( isset( $result['data'] ) ){
			$raw_entries = $result['data'];
		}else{
			$raw_entries = array();
		}	
		
		$entries =	array();
		foreach ( $raw_entries as $entry ){			
			$entries[] = $this->format_video_entry( $entry );		
		}		
		
		$this->results = $entries;
		$this->total_items = isset( $result['total'] ) ? $result['total'] : 0;
		$this->page = isset( $result['page'] ) ? $result['page'] : 0;
	}
	
/**
	 * Formats the response from the feed for a single entry
	 * @param array $entry
	 */
	private function format_video_entry( $raw_entry ){
		$thumbnails = array();
		if( isset( $raw_entry['pictures']['sizes'] ) ){	
			foreach( $raw_entry['pictures']['sizes'] as $thumbnail ){
				$thumbnails[] = $thumbnail['link'];
			}
		}
	
		$stats = array(
			'comments' 	=> 0,
			'likes' 	=> 0,
			'views' 	=> 0
		);
		
		if( isset( $raw_entry['metadata']['connections']['comments']['total'] ) ){
			$stats['comments'] = $raw_entry['metadata']['connections']['comments']['total'];
		}
		
		if( isset( $raw_entry['metadata']['connections']['likes']['total'] ) ){
			$stats['likes'] = $raw_entry['metadata']['connections']['likes']['total'];
		}
		
		if( isset( $raw_entry['stats']['plays'] ) ){
			$stats['views'] = $raw_entry['stats']['plays'];
		}
		
		// extract tags
		$tags = array();
		if( isset( $raw_entry['tags'] ) && is_array( $raw_entry['tags'] ) ){
			foreach( $raw_entry['tags'] as $tag ){
				$tags[] = $tag['name'];
			}		
		}
		
		$privacy = false;
		if( isset($raw_entry['privacy']) ){
			if( 'anybody' == $raw_entry['privacy']['view'] ){
				$privacy = 'public';
			}else{
				$privacy = 'private';
			}
		}
		
		$size = array();
		if( isset( $raw_entry['width'] ) && isset( $raw_entry['height'] ) ){
			$w = absint( $raw_entry['width'] );
			$h = absint( $raw_entry['height'] );
			$size = array(
				'width' => $w,
				'height' => $h,
				'ratio' => round(  $w/$h  , 2 )
			);		
		}
		
		$entry = array(
			'video_id'		=> str_replace( '/videos/' , '', $raw_entry['uri'] ),
			'uploader'		=> $raw_entry['user']['name'],
			'uploader_uri'	=> $raw_entry['user']['uri'],
			'published' 	=> $raw_entry['created_time'],
			'updated'		=> isset( $raw_entry['modified_time'] ) ? $raw_entry['modified_time'] : false,
			'title'			=> $raw_entry['name'],
			'description' 	=> $raw_entry['description'],
			'category'		=> false,
			'tags'			=> $tags,
			'duration'		=> $raw_entry['duration'],
			'thumbnails'	=> $thumbnails,				
			'stats'			=> $stats,
			'privacy'		=> $privacy,
			'size'			=> $size		
		);
		
		return $entry;
	}
	
	public function get_feed(){
		return $this->results;
	}
	
	public function get_total_items(){
		return $this->total_items;
	}

	public function get_page(){
		return $this->page;
	}
	
	public function get_errors(){
		return $this->errors;
	}
}