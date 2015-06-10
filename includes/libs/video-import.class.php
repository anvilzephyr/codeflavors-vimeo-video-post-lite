<?php

if( !class_exists('CVM_Vimeo') ){
	require_once CVM_PATH.'includes/libs/vimeo.class.php';
}

class CVM_Video_Import extends CVM_Vimeo{
	
	private $results;
	private $total_items;
	private $page;
	
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
		$request_url = parent::request_url();
		
		$content = wp_remote_get( $request_url );
		
		if( is_wp_error( $content ) || 200 != $content['response']['code'] ){
			// error - bail out
			return false;
		}
		
		$result = json_decode( $content['body'], true );
		
		if( isset( $result['err'] ) ){
			global $CVM_IMPORT_ERR;
			$CVM_IMPORT_ERR = new WP_Error();
			$CVM_IMPORT_ERR->add('cvm_vimeo_query_error', __('Query to Vimeo failed.', 'cvm_video'), $result['err']);
		}
		
		if( isset( $result['videos']['video'] ) ){
			$raw_entries = $result['videos']['video'];
		}else{
			$raw_entries = array();
		}	
		
		$entries =	array();
		
		foreach ( $raw_entries as $entry ){	
			$entries[] = cvm_format_video_entry( $entry );					
		}		
		
		$this->results = $entries;
		$this->total_items = isset( $result['videos']['total'] ) ? $result['videos']['total'] : 0;
		$this->page = isset( $result['videos']['page'] ) ? $result['videos']['page'] : 0;
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
}