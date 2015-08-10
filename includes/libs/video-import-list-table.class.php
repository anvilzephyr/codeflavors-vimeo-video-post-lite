<?php
class CVM_Video_Import_List_Table extends WP_List_Table{
	
	private $query_errors;
		
	function __construct( $args = array() ){
		parent::__construct( array(
			'singular' => 'vimeo-video',
			'plural'   => 'vimeo-videos',
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}
	
	/**
	 * Default column
	 * @param array $item
	 * @param string $column
	 */
	function column_default( $item, $column ){
		if( array_key_exists($column, $item) ){
			return $item[ $column ];
		}else{
			return '<span style="color:red">'.sprintf( __('Column <em>%s</em> was not found.', 'cvm_video'), $column ).'</span>';
		}
	}
	
	/**
	 * Checkbox column
	 * @param array $item
	 */
	function column_cb( $item ){
		
		$output = sprintf( '<input type="checkbox" name="cvm_import[]" value="%1$s" id="cvm_video_%1$s" />', $item['video_id'] );
		return $output;
		
	}
	
	/**
	 * Title column
	 * @param array $item
	 */
	function column_title( $item ){	
		
		$label = sprintf( '<label for="cvm_video_%1$s" class="cvm_video_label">%2$s</label>', $item['video_id'], $item['title'] );

		
		// row actions
    	$actions = array(
    		'view' 		=> sprintf( '<a href="http://vimeo.com/%1$s" target="_cvm_vimeo_open">%2$s</a>', $item['video_id'], __('View on Vimeo', 'cvm_video') ),
    	);
    	
    	return sprintf('%1$s %2$s',
    		$label,
    		$this->row_actions( $actions )
    	);		
	}
	
	
	
	/**
	 * Column for video duration
	 * @param array $item
	 */
	function column_duration( $item ){		
		return cvm_human_time( $item['duration'] );
	}
	
	/**
	 * Rating column
	 * @param array $item 
	 */
	function column_likes( $item ){

		if( 0 == $item['stats']['likes'] ){
			return '-';
		}
		
		return sprintf( __('%d likes', 'cvm_video'), $item['stats']['likes'] );
	}
	
	/**
	 * Views column
	 * @param array $item
	 */
	function column_views( $item ){
		if( 0 == $item['stats']['views'] ){
			return '-';
		}		
		return number_format( $item['stats']['views'], 0, '.', ',');		
	}
	
	/**
	 * Date when the video was published
	 * @param array $item
	 */
	function column_published( $item ){
		$time = strtotime( $item['published'] );
		return date('M dS, Y @ H:i:s', $time);
	}
		
	/**
     * (non-PHPdoc)
     * @see WP_List_Table::get_bulk_actions()
     */
    function get_bulk_actions() {    	
    	$actions = array(
    		/*'import' => __('Import', 'cvm_video')*/
    	);
    	
    	return $actions;
    }
	
    function no_items(){
    	if( is_wp_error( $this->query_errors ) ){
    		echo __( 'Query to Vimeo returned this error: ', 'cvm_video' ) . $this->query_errors->get_error_message();
    	}
    }
    
	/**
     * Returns the columns of the table as specified
     */
    function get_columns(){
        
		$columns = array(
			'cb'		=> '<input type="checkbox" />',
			'title'		=> __('Title', 'cvm_video'),
			'video_id'	=> __('Video ID', 'cvm_video'),
			'uploader'	=> __('Uploader', 'cvm_video'),
			'duration'	=> __('Duration', 'cvm_video'),
			'likes'	=> __('Likes', 'cvm_video'),
			'views'		=> __('Views', 'cvm_video'),
			'published' => __('Published', 'cvm_video'),
		);    	
    	return $columns;
    }
    
    function extra_tablenav( $which ){    	
    	return;
    }
    
    /**
     * (non-PHPdoc)
     * @see WP_List_Table::prepare_items()
     */    
    function prepare_items() {
        $per_page 	 = 20;
		$current_page = $this->get_pagenum();
		
		$args = array(
			'source' 	=> $_GET['cvm_source'],
			'feed'		=> $_GET['cvm_feed'],
			'query'		=> $_GET['cvm_query'],
			'order'		=> $_GET['cvm_order'],
    		'results'	=> $per_page,
			'page' 		=> $current_page
		);		
		
		require_once CVM_PATH.'includes/libs/video-import.class.php';
		$import = new CVM_Video_Import($args);
		$videos = $import->get_feed();
        
		$this->query_errors = $import->get_errors();
		
		$total_items = $import->get_total_items();
		
    	$this->items 	= $videos;
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                     
            'total_pages' => ceil( $total_items / $per_page )  
        ) );
    }   
    
    public function get_errors(){
    	return $this->query_errors;
    }
}