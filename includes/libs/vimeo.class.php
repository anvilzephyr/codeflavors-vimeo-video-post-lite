<?php 
	
class CVM_Vimeo{
	
	private $url_base = 'http://vimeo.com/api/rest/v2';
	private $params;
	private $result;
	
	/**
	 * Constructor. Takes as parameter an array of arguments:
	 * 
	 * key : consumer key Vimeo provides,
	 * secret : consumer secret from Vimeo
	 * feed : feed type to query for; can be:
	 * 				- album
	 * 				- channel
	 * 				- user
	 * 				- group
	 * 				- search
	 * 				- video
	 * feed_id	: according to feed param, the ID of the feed ( album id for albums, channel id for channel etc )
	 * feed_type : type of feed to retrieve. Except user, all other feeds have only videos; user has: videos, likes, all, appears
	 * 
	 * @param array $args
	 */
	public function __construct( $args ){
		
		$default = array(
			'key' 		=> null, 		// consumer key
			'secret' 	=> null, 		// consumer secret
			'feed' 		=> '', 			// feed type; can be album, channel, user or video
			'feed_id'	=> false, 		// vimeo ID for feed
			'feed_type' => 'videos', 	// vimeo method to query for ( videos, likes, all, appears )
			'sort'		=> 'new',		// video sorting
			'page'		=> 1,			// current page number
			'per_page'	=> 20,			// items per page
			// these shouldn't need to be changed	
			'response'	=> 'json'		
		);
		
		$this->params = wp_parse_args( $args, $default );
		
		$plugin_settings 		= cvm_get_settings();
		$this->params['key'] 	= $plugin_settings['vimeo_consumer_key'];
		$this->params['secret'] = $plugin_settings['vimeo_secret_key'];
		
		$parameters = $this->get_params();		
		$feed_param = $this->get_feed_param( $this->params['feed'] );
		
		$parameters['method']	 	= $this->get_feed_method( $this->params['feed'], $this->params['feed_type'] );
		$parameters[ $feed_param ] 	= $this->params['feed_id'];
		$parameters['sort']			= $this->get_sorting( $this->params['sort'] );
		
		$parameters 				= $this->sign( $parameters );
		$this->result = $this->url_base .'?'. http_build_query( $parameters );
		
		
	}
	
	/**
	 * Returns request URL
	 */
	public function request_url(){
		return $this->result;
	}
	
	/**
	 * Get feed parameter name
	 * 
	 * @param string $which - feed type ( values: album, channel, user, group, search, video )
	 */
	private function get_feed_param( $which ){
		$feeds = array(
			'album' 	=> 'album_id',
			'channel' 	=> 'channel_id',
			'user'		=> 'user_id',
			'group'		=> 'group_id',
			'search'	=> 'query',
			'video'		=> 'video_id',
			'category'	=> 'category'
		);
		
		if( array_key_exists($which, $feeds) ){
			return $feeds[ $which ];
		}
		
		return false;
	}
	
	/**
	 * Get sorting
	 * 
	 * @param string $sortby - sorting; values: comments, likes, played, relevant, new, old, rand
	 */
	private function get_sorting( $sortby ){
		$sort = array(
			'comments' 	=> 'most_commented',
			'likes'		=> 'most_liked',
			'played'	=> 'most_played',
			'relevant'	=> 'relevant',
			'new'		=> 'newest',
			'old'		=> 'oldest'
		);
		
		if( array_key_exists( $sortby, $sort ) ){
			return $sort[ $sortby ];
		}
		return false;
	}
	
	/**
	 * Get method for Vimeo query
	 * 
	 * @param string $feed_type - type of feed to query for ( album, channel, group, serach or user )
	 * @param string $type - type of feed to query for ( all type: videos, likes, all, appears )
	 * @return method name or false if not found
	 */
	private function get_feed_method( $feed_type, $type = 'videos' ){
		
		$feeds = array(
			'album' => array(
				'videos' => 'vimeo.albums.getVideos',
			),
			'channel' => array(
				'videos' => 'vimeo.channels.getVideos',
			),
			'group' => array(
				'videos' => 'vimeo.groups.getVideos',
			),
			'search' => array(
				'videos' => 'vimeo.videos.search',
			),
			'user' => array(
				'videos'	=> 'vimeo.videos.getUploaded', 	// videos created by user
				'likes' 	=> 'vimeo.videos.getLikes',		// videos user likes
				'all'		=> 'vimeo.videos.getAll',		// videos created and appears in
				'appears' 	=> 'vimeo.videos.getAppearsIn'	// videos user appears in
			),
			'category' => array(
				'videos' => 'vimeo.categories.getRelatedVideos'
			)
		);
		// check if feed type exists
		if( !array_key_exists($feed_type, $feeds) ){
			return false;
		}
		// check if method exists within the given feed type
		$methods = $feeds[ $feed_type ];
		if( !array_key_exists($type, $methods) ){
			return false;
		}
		return $methods[ $type ];		
	}
	
	/**
	 * Extra query params
	 */
	private function get_params(){		
		$params = array(
			'full_response' 		=> 'true',
			'format'				=> $this->params['response'],
			'oauth_consumer_key' 	=> $this->params['key'],
			'oauth_nonce'			=> md5( uniqid( mt_rand(), true ) ),
			'oauth_signature_method'=> 'HMAC-SHA1',
			'oauth_timestamp'		=> time(),
			'oauth_version'			=> '1.0',
			'page'					=> $this->params['page'],
			'per_page'				=> $this->params['per_page']		
			/*'oauth_signature'		=> ''*/
		);

		return $params;		
	}
	
	/**
	 * Creates signature for parameters
	 * @param array $params
	 */
	private function sign( $params ){
		$params['oauth_signature'] = $this->_generateSignature( $params );
		return $params;
	}
	
	/**
     * Generate the OAuth signature.
     *
     * @param array $args The full list of args to generate the signature for.
     * @param string $request_method The request method, either POST or GET.
     * @param string $url The base URL to use.
     * @return string The OAuth signature.
     */
    private function _generateSignature( $params ){
    	uksort($params, 'strcmp');
        $params = $this->_url_encode_rfc3986($params);

        $baseString = array('GET', $this->url_base, urldecode( http_build_query( $params ) ) );
        $baseString = $this->_url_encode_rfc3986( $baseString );
        $baseString = implode('&', $baseString);

        // Make the key
        $keyParts = array( $this->params['secret'], '');
        $keyParts = $this->_url_encode_rfc3986( $keyParts );
        $key      = implode('&', $keyParts);

        // Generate signature
        return base64_encode( hash_hmac( 'sha1', $baseString, $key, true ) );
    }
	
	/**
     * URL encode a parameter or array of parameters.
     *
     * @param array/string $input A parameter or set of parameters to encode.
     */
    private function _url_encode_rfc3986( $input ){
        if (is_array($input)) {
            return array_map( array( $this, '_url_encode_rfc3986' ), $input );
        }else if ( is_scalar( $input ) ){
            return str_replace( array( '+', '%7E' ), array( ' ', '~' ), rawurlencode( $input ) );
        }else{
            return '';
        }
    }    
}

?>