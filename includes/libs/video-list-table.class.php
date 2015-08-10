<?php
/**
 * Load WP_List_Table class
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CVM_Video_List_Table extends WP_List_Table{
	
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
	function column_default( $item, $column  ){
		return $item[$column];
	}
	
	/**
	 * Title
	 * @param array $item
	 */
	function column_post_title( $item ){
		
		$meta = get_post_meta( $item['ID'], '__cvm_video_data', true );
		
		$label = sprintf( '<label for="cvm-video-%1$s" id="title%1$s" class="cvm_video_label">%2$s</label>', $item['ID'], $item['post_title'] );
		
		// row actions
    	$actions = array(
    		'shortcode' => sprintf( '<a href="#" id="cvm-embed-%1$s" class="cvm-show-form">%2$s</a>', $item['ID'], __('Insert video shortcode', 'cvm_video') ),
    	);
    	
    	return sprintf('%1$s %2$s',
    		$label,
    		$this->row_actions( $actions )
    	);	
		
	}
	
	/**
	 * Checkbox column
	 * @param array $item
	 */
	function column_cb( $item ){
		return sprintf(
			'<input type="checkbox" name="%1$s" value="%2$s" id="%3$s" class="cvm-video-checkboxes">',
			'cvm_video[]',
			$item['ID'],
			'cvm-video-'.$item['ID']
		);
	}
	
	/**
	 * Vimeo video ID column
	 * @param array $item
	 */
	function column_video_id( $item ){
		$meta = get_post_meta( $item['ID'], '__cvm_video_data', true );
		return $meta['video_id'];
	}
	
	/**
	 * Video duration column
	 * @param array $item
	 */
	function column_duration( $item ){
		$meta = get_post_meta( $item['ID'], '__cvm_video_data', true );
		return '<span id="duration'.$item['ID'].'">'.cvm_human_time($meta['duration']).'</span>';
	}
	
	/**
	 * Display video categories
	 * @param array $item
	 */
	function column_category( $item ){
		global $CVM_POST_TYPE;	
		$taxonomy = $CVM_POST_TYPE->get_post_tax();
		if ( $terms = get_the_terms( $item['ID'], $taxonomy ) ) {
			$out = array();
			foreach ( $terms as $t ) {
				$url = add_query_arg(
					array(
						'post_type' => $CVM_POST_TYPE->get_post_type(),
						'page' 		=> 'cvm_videos',
						'cat'		=> $t->term_id
					)
				, 'edit.php');
				
				$out[] = sprintf('<a href="%s">%s</a>', $url, $t->name);
			}
			return implode(', ', $out);
		}else {
			return '&#8212;';
		}
	}
	
	/**
	 * Date column
	 * @param array $item
	 */
	function column_post_date( $item ){
		
		$output = sprintf( '<abbr title="%s">%s</abbr><br />', $item['post_date'], mysql2date( __( 'Y/m/d' ), $item['post_date'] ) );
		$output.= 'publish' == $item['post_status'] ? __('Published', 'cvm_video') : '';
		return $output;
		
	}
	
	function extra_tablenav($which){
		
		if( 'top' !== $which ){
			return ;
		}
		
		$selected = false;
		if( isset( $_GET['cat'] ) ){
			$selected = $_GET['cat'];
		}
		
		$args = array(
			'show_option_all' => __('All categories', 'cvm_video'),
			'show_count' 	=> 1,
			'taxonomy' 		=> 'vimeo-videos',
			'name'			=> 'cat',
			'id'			=> 'cvm_video_categories',
			'selected'		=> $selected,
			'hide_if_empty'	=> true,
			'echo'			=> false
		);
		$categories_select = wp_dropdown_categories($args);
		if( !$categories_select ){
			return;
		}		
		?>
		<label for="cvm_video_categories"><?php _e('Categories', 'cvm_video');?> :</label>
		<?php echo $categories_select;?>
		<?php submit_button( __( 'Filter', 'cvm_video' ), 'button-secondary apply', 'filter_videos', false );?>
		<?php		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns(){
		$columns = array(
			'cb'			=> '<input type="checkbox" class="cvm-video-list-select-all" />',
			'post_title'	=> __('Title', 'cvm_video'),
			'video_id'		=> __('Video ID', 'cvm_video'),
			'duration'		=> __('Duration', 'cvm_video'),
			'category'	=> __('Category', 'cvm_video'),
			'post_date' 	=> __('Date', 'cvm_video'),
		);    	
    	return $columns;
	}
	
	/**
     * (non-PHPdoc)
     * @see WP_List_Table::prepare_items()
     */    
    function prepare_items() {
    	
    	$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        global $CVM_POST_TYPE;
        $this->_column_headers = array($columns, $hidden, $sortable);
                
    	$per_page 		= 20;
    	$current_page 	= $this->get_pagenum();
        
    	$search_for = '';
    	if( isset($_REQUEST['s']) ){
    		$search_for = esc_attr( stripslashes( $_REQUEST['s'] ) );
    	}

    	$category = false;
    	if( isset( $_GET['cat'] ) && $_GET['cat'] ){
    		$category = $_GET['cat'];
    	}
    	
        $args = array(
			'post_type'			=> $CVM_POST_TYPE->get_post_type(),
			'orderby' 			=> 'post_title',
		    'order' 			=> 'ASC',
	    	'posts_per_page'	=> $per_page,
	    	'offset'			=> ($current_page-1) * $per_page,
        	'post_status'		=> 'publish',
			's'					=> $search_for        	
        );
        if( $category ){
        	$args['tax_query'] = array(
        		array(
        			'taxonomy' => 'vimeo-videos', 
        			'field' => 'id', 
        			'terms' => $category
        		)
        	);
        }
        
        
		$query = new WP_Query( $args );
		
		$data = array();    
        if( $query->posts ){
        	foreach($query->posts as $k => $item){
        		$data[$k] = (array)$item;
        	}
        }
        
        $total_items = $query->found_posts;
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                     
            'total_pages' => ceil($total_items/$per_page)  
        ) );
    }
	
}