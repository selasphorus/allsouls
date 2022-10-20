<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}

/*********** POST/EVENT RELATIONSHIPS ***********/

function get_related_event( $post_id = null, $post_type = null, $link = true, $link_text = null ) {
	
	$info = ""; // init
	if ($post_id === null) { $post_id = get_the_ID(); }
	
	// If we don't have actual values for both parameters, there's not enough info to proceed
	if ($post_id === null || $post_type === null) { return null; }
	
	$event_id = get_related_posts( $post_id, $post_type, 'event', 'single' ); // get_related_posts( $post_id = null, $related_post_type = null, $related_field_name = null, $return = 'all' )
	//echo "event_id: $event_id; post_id: $post_id"; // tft
	//$info .= "<!-- event_id: $event_id; post_id: post_id -->"; // tft
	
	if ($event_id && $event_id !== "no posts") {
		if ($link === true) { 
			$info .= '<a href="'. esc_url(get_the_permalink($event_id)) . '" title="'.get_the_title($event_id).'">';
			if ($link_text !== null) { $info .= $link_text; } else { $info .= get_the_title($event_id); }
			$info .= '</a>';
		} else {
			$info .= get_the_title($event_id);
		}
		//$info .= '<a href="'. esc_url(get_the_permalink($event_id)) . '" title="event_id: '.$event_id.'/post_id: '.$post_id.'">' . get_the_title($event_id) . '</a>';
	} else {
		//$info .= "<!-- event_id: $event_id; post_id: post_id -->";
		return null;
	}
	//$info .= '<a href="'. esc_url(get_permalink($event_id)) . '">' . get_the_title($event_id) . '</a>';
	
	return $info;
	
}

// WIP: Get Related Events based on program info
function get_related_events ( $meta_field, $term_id ) {

    // Init vars
    $arr_results = array();
    $info = "";
    $meta_key = "";
    
    // Determine meta_key based on field name, with XYZ as a wildcard placeholder (must do this to avoid hashing)
    if ( $meta_field == "program_label" ) {
        $meta_key = "program_items_XYZ_item_label";
    } else if ( $meta_field == "program_item" ) {
        $meta_key = "program_items_XYZ_program_item";
    } else if ( $meta_field == "role" ) {
        $meta_key = "personnel_XYZ_role";
    } else if ( $meta_field == "person" ) {
        $meta_key = "personnel_XYZ_person";
    }
    
    $info .= "meta_field: ".$meta_field."; meta_key: ".$meta_key."; term_id: ".$term_id."<br />";
    
    // Build query args
    $args = array(
        'posts_per_page'=> -1,
        'post_type'		=> 'event',
        'meta_query'	=> array(
            array(
                'key'		=> $meta_key,
                'compare' 	=> 'LIKE',
                //'value' 	=> $term_id,
                'value' 	=> '"' . $term_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
            )
        ),
        'orderby'	=> 'meta_value',
		'order'     => 'DESC',
		'meta_key' 	=> '_event_start_date',
    );
    
    $query = new WP_Query( $args );
    $event_posts = $query->posts;
    
    $arr_results['event_posts'] = $event_posts;

    $info .= "args: <pre>".print_r($args, true)."</pre>";
    $info .= "event_posts: <pre>".print_r($event_posts, true)."</pre>";
    $info .= "Last SQL-Query: <pre>{$query->request} </pre><br />";
    
    if ( $event_posts ) {
        // WIP
    } else {
        $info .= "No related events found.<br />";
        //$info .= "Last SQL-Query: <pre>{$query->request} </pre><br />";
        //$info .= "Query object: <pre>{$query} </pre><br />";
    }
    
    $arr_results['info'] = $info;
    
    return $arr_results;
    
}


/*********** EVENT PROGRAMS ***********/

add_shortcode('display_event_program', 'get_event_program_content');
// Get program per ACF fields for Event post
function get_event_program_content( $post_id = null ) {
	
	$info = ""; // init
	if ( $post_id == null ) { $post_id = get_the_ID(); }
    
    // What type of program is this? Service order or concert program?
    $program_type = get_post_meta( $post_id, 'program_type', true );
    
    // What program order? (default is personnel first)
    $program_order = get_post_meta( $post_id, 'program_order', true );
    $info .= "<!-- post_id: $post_id >> program_order: $program_order -->"; // tft
	
    $info .= '<div class="event_program '.$program_type.' '.$program_order.'">';
    
    if ( $program_order == "first_program_items" ) {
        $info .= get_event_program_items( $post_id );
        $info .= get_event_personnel( $post_id );	   
    } else {
        $info .= get_event_personnel( $post_id );
        $info .= get_event_program_items( $post_id );
    }
    
	$info .= '</div>';
	
    // TODO: get and display program_pdf?
	//$info .= make_link($program_pdf,"Download Leaflet PDF", null, "_blank"); // make_link( $url, $linktext, $class = null, $target = null)
	
	return $info;
	
}


// Program/Event personnel via Event CPT & ACF
add_shortcode('display_event_personnel', 'get_event_personnel');
function get_event_personnel( $atts = [] ) {
    
    $a = shortcode_atts( array(
		'id'        => get_the_ID(),
        'run_updates' => false,
        'display' => 'table'       
    ), $atts );
    
    $post_id = $a['id'];
    $run_updates = $a['run_updates'];
    $display = $a['display'];
    
    $info = "";
    
    // *** WIP ***
    //if ( devmode_active() || is_dev_site() ) { $run_updates = true; } // TMP disabled 03/25/22
    //if ( devmode_active() || ( is_dev_site() && devmode_active() )  ) { $run_updates = true; } // ???
    
    $info .= "<!-- Event Personnel for post_id: $post_id -->";
	if ( $display == 'dev' ) { $info .= '<div>'; } //$info .= '<div class="code">'; }
    
    // What type of program is this? Service order or concert program?
    $program_type = get_post_meta( $post_id, 'program_type', true );
    $info .= "<!-- program_type: $program_type -->";
    
    // Program Layout -- left or centered?
    $program_layout = get_post_meta( $post_id, 'program_layout', true );
    
    $info .= "<!-- run_updates: $run_updates -->";
    //$info .= "<!-- display: $display -->";
    
	// Get the program personnel repeater field values (ACF)
    $rows = get_field('personnel', $post_id);  // ACF function: https://www.advancedcustomfields.com/resources/get_field/ -- TODO: change to use have_rows() instead?
    /*
    if ( have_rows('personnel', $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
        while ( have_rows('personnel', $post_id) ) : the_row();
            $XXX = get_sub_field('XXX'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
        endwhile;
    } // end if
    */
    if ( empty($rows) ) { $rows = array(); } //$rows = (!empty(get_field('personnel', $post_id))) ? 'default' : array();
    $info .= "<!-- ".count($rows)." personnel rows -->\n"; // tft
    
    // Loop through the personnel rows and accumulate data for display
	if ( count($rows) > 0 ) {
        
        $table_classes = "event_program program personnel ".$program_layout;
        
        $i = 0; 
		//$i = 1; // row index counter init -- why not zero? see https://www.advancedcustomfields.com/resources/update_sub_field/#notes
        $deletion_count = 0;
        
		$table = '<table class="'.$table_classes.'">';
        $table .= '<tbody>';

        foreach ($rows as $row) {
            
            // initialize vars
            $placeholder_label = false;
            $placeholder_item = false;
            $person_role = null;
            $person_name = null;
            $delete_row = false;
            $row_info = "";
            
            // Should this row be displayed on the front end?
            if ( isset($row['show_row']) && $row['show_row'] != "" ) { 
                $show_row = $row['show_row'];
                $row_info .= "<!-- show_row => ".$row['show_row']." -->"; // tft
            } else { 
                $show_row = 1; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
                $row_info .= "<!-- default: show_row = 1 -->"; // tft
            }
            //if ( isset($row['show_row']) ) { $show_row = $row['show_row']; } else { $show_row = 1; } // Should this row be displayed on the front end?
            
            if ( $display == 'dev' ) { // || devmode_active()
            
                $row_info .= "<code>";                
                $row_info .= "personnel row [$i]: <pre>".print_r($row, true)."</pre>";                
                /*
                if ( isset($row['role']) )          { 
                	$row_info .= "role => ".$row['role']."; ";
                	$row_info .= "row['role']: <pre>".print_r($row['role'], true)."</pre>";
                }
                if ( isset($row['role'][0]) )       { $row_info .= "role[0] => ".$row['role'][0]."; "; } // relates to old version where role was stored as CPT instead of taxonomy?
                
                if ( isset($row['role_old']) )      { $row_info .= "role_old => ".$row['role_old']."; "; }
                if ( isset($row['role_txt']) )      { $row_info .= "role_txt => ".$row['role_txt']."; "; }
                if ( isset($row['person']) )        { $row_info .= "person => ".$row['person']."; "; }
                if ( isset($row['person'][0]) )     { $row_info .= "person[0] => ".$row['person'][0]."; "; }
                if ( isset($row['ensemble']) )      { $row_info .= "ensemble => ".$row['ensemble']."; "; }
                if ( isset($row['ensemble'][0]) )   { $row_info .= "ensemble[0] => ".$row['ensemble'][0]."; "; }
                if ( isset($row['person_txt']) )    { $row_info .= "person_txt => ".$row['person_txt'].""; }
                */
                $row_info .= "</code><hr />";
            } else {
                //$row_info .= "<!-- personnel row [$i]: ".print_r($row, true)." -->";
                //$row_info .= "<!-- personnel row [$i]: <pre>".print_r($row, true)."</pre> -->";
            }
            
            // Troubleshooting
            $row_info .= "<!-- i: [$i]; post_id: [$post_id]; program_type: [$program_type]; display: [$display]; run_updates: [$run_updates] -->";
            $row_info .= "<!-- personnel row [$i]: <pre>".print_r($row, true)."</pre> -->";
            
            // Set up the args array for the personnel role/person functions
            // --------------------
            $personnel_args = array( 'index' => $i, 'post_id' => $post_id, 'row' => $row, 'program_type' => $program_type, 'display' => $display, 'run_updates' => $run_updates );
            
            // Get the person role
            // --------------------
            $arr_person_role = get_personnel_role( $personnel_args );
            //$arr_person_role = get_personnel_role( $i, $row, $program_type, $post_id, $run_updates, $display );
            //$row_info .= "<!-- arr_person_role row [$i]: <pre>".print_r($arr_person_role, true)."</pre> -->"; // tft
            $person_role = $arr_person_role['person_role'];
            $row_info .= $arr_person_role['info'];
            
            // Get the person
            // --------------------
            $arr_person = get_personnel_person( $personnel_args );
            //$arr_person = get_personnel_person( $i, $row, $program_type, $post_id, $run_updates, $display );
            $person_name = $arr_person['person_name'];
            $row_info .= $arr_person['info'];
            
            $row_info .= "<!-- person_role: [$person_role]; person_name: [$person_name] -->";
            
            // Check for extra (empty) import rows -- prep to delete them
            if (( empty($person_role) && empty($person_name) ) 
                || ( ( $person_role == "x" || $person_role == "MATCH_DOUBLE (( post_title :: " ) && ( $person_name == "x" || empty($person_name) ) )
                ) {
                $delete_row = true;
                $row_info .= "<!-- row $i to be deleted! [person_role: $person_role; person_name: $person_name] -->";
            }
            
            if ( $run_updates == true ) { 
                $do_deletions = true; // tft
            } else {
                $do_deletions = false; // tft
            }
            // *** NB: tmp override
            $do_deletions = false; // tft
            
            // If the row is empty/x-filled and needs to be deleted, then do so
            if ( $delete_row == true ) {
                
                //allsouls_log("divline1");
                //allsouls_log("personnel row to be deleted:");
                //allsouls_log( print_r($row, true) );
                //$row_info .= "<!-- <pre>".print_r($row, true)."</pre> -->"; // tft
                
                // ... but only run the action if this is the first deletion for this post_id in this round
                // ... because if a row has already been deleted then the row indexes will have changed for all the personnel rows
                // ... and though it would likely not be so difficult to reset the row index accordingly, for now let's proceed with caution...
                if ( $deletion_count == 0 && $do_deletions == true) {

                    if ( delete_row('personnel', $i, $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/delete_row/ -- syntax: delete_row($selector, $row_num, $post_id) 
                        $row_info .= "<!-- [personnel row $i deleted] -->";
                        $deletion_count++;
                        //allsouls_log("[personnel row $i deleted successfully]");
                    } else {
                        $row_info .= "<!-- [deletion failed for personnel row $i] -->";
                        //allsouls_log("[failed to delete personnel row $i]");
                    }
                    
                } else {
                    
                    if ( $do_deletions == true ) {
                        $row_info .= "<!-- [$i] row to be deleted on next round due to row_index issues. -->";
                        //allsouls_log("row to be deleted on next round due to row_index issues.");
                    } else {
                        $row_info .= "<!-- [$i] row to be deleted when do_deletions is re-enabled. -->";
                        //allsouls_log("row to be deleted when do_deletions is re-enabled.");
                    }
                    
                }
                
            }
                
			if ( $delete_row != true ) { // $display == 'table' && 
				$tr_class = "program_objects";
				if ( $show_row == "0" ) { $tr_class .= " hidden"; }
				$table .= '<tr class="'.$tr_class.'">';
			}
			
			if ( $run_updates == true || is_dev_site() || devmode_active() ) {
				$table .= "<!-- *** START row_info *** -->";
                $table .= $row_info; // Display comments w/ in row for ease of parsing dev notes
                $table .= "<!-- *** END row_info *** -->";
			}
			
			if ( $delete_row != true ) {
			
				if ( $program_type == "concert_program" ) {
					
					$td_class = "concert_program_personnel";
					$role_class = "person_role";
					$item_class = "person";
					
					if ( $placeholder_label == true )	{ $role_class .= " placeholder"; }
					if ( $placeholder_item == true ) 	{ $item_class .= " placeholder"; }
					
					$table .= '<td colspan="2" class="'.$td_class.'">';
					$table .= '<span class="'.$item_class.'">'.$person_name.'</span>, ';
					$table .= '<span class="'.$role_class.'">'.$person_role.'</span>';
					$table .= '</td>';
					
				} else {
				
					$td_class = "program_label";
					if ( $placeholder_label == true ) { $td_class .= " placeholder"; }
					$table .= '<td class="'.$td_class.'">'.$person_role.'</td>';
					$td_class = "program_item";
					if ( $placeholder_item == true ) { $td_class .= " placeholder"; }
					$table .= '<td class="'.$td_class.'">'.$person_name.'</td>';
					
				}
                
				$table .= '</tr>';
			}
            
            $i++;
            
        } // end foreach $rows

		$table .= '</tbody>';
        $table .= '</table>';
        
        $info .= $table;

        // TODO: remove program-personnel-placeholders tag when ALL personnel placeholders have been replaced...
        //if ( $placeholder_label == false && $placeholder_item == false ) { 
            //$row_info .= allsouls_remove_post_term( $post_id, 'program-personnel-placeholders', 'admin_tag', true ); 
        //
        
    } // end if $rows
	
    if ( $display == 'dev' ) {
        $info = str_replace('<!-- ','<code>',$info);
        $info = str_replace(' -->','</code><br />',$info);
        $info = str_replace("\n",'<br />',$info);
        if ( $display == 'dev' ) { $info .= '</div>'; }
    }
    
	return $info;
}

function get_personnel_role ( $a = array() ) {
	
	// Init vars
	$results = array();
	$info = "";
	$person_role = "";
    $placeholder_label = false;
	
	//$info .= "<!-- get_personnel_role -->"; // tft
	
    //get_personnel_role( $i, $row, $program_type, $post_id, $run_updates, $display );
    
	if ( isset($a['index']) )     	{ $i = $a['index'];             		} else { $i = null; }
    if ( isset($a['post_id']) )     { $post_id = $a['post_id'];             } else { $post_id = null; }
    if ( isset($a['row']) )         { $row = $a['row'];                     } else { $row = null; }
    if ( isset($a['program_type']) ){ $program_type = $a['program_type'];   } else { $program_type = "service_order"; }
    if ( isset($a['run_updates']) ) { $run_updates = $a['run_updates'];     } else { $run_updates = false; }
    if ( isset($a['display']) ) 	{ $display = $a['display'];     		} else { $display = ""; }
	
    // First, look for a proper taxonomy term (Personnel Role)
	if ( isset($row['role']) && !empty($row['role']) ) { 
		$term = get_term( $row['role'] );
		if ($term) { $person_role = $term->name; }
	}
	
	// If no role has been set via the Personnel Roles taxonomy, then look for a placeholder value
	if ( empty($person_role) ) {
		
		if ( isset($row['role_old']) && $row['role_old'] != "" ) {
			$info .= "<!-- role is empty -> use placeholder role_old -->";
			$person_role = get_the_title($row['role_old'][0]);
			$placeholder_label = true;
		} else if ( isset($row['role_txt']) && $row['role_txt'] != "" && $row['role_txt'] != "x" ) {                    
			$info .= "<!-- role is empty -> use placeholder role_txt -->";
			$person_role = $row['role_txt'];
			$placeholder_label = true;                    
		}
		
		// Fill in Placeholder -- see if a matching record can be found to fill in a proper person_role
		if ( $placeholder_label == true && $run_updates == true  ) { 
			$title_to_match = $person_role;
			$info .= "<!-- seeking match for placeholder value: '$title_to_match' -->";
			$match_args = array('index' => $i, 'post_id' => $post_id, 'item_title' => $title_to_match, 'repeater_name' => 'personnel', 'field_name' => 'role', 'taxonomy' => 'true', 'display' => $display );
			$match_result = match_placeholder( $match_args );
			$info .= $match_result;
		} else {
            $info .= "<!-- NO match_placeholder for personnel_role -->";
			$info .= allsouls_add_post_term( $post_id, 'program-personnel-placeholders', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
		}
		
	}
	
	$results['person_role'] = $person_role;
	$results['info'] = $info;
	
	return $results;
            
}

function get_personnel_person ( $a = array() ) {

	// TODO Add param: person_role
	
	// Init vars
	$results = array();
	$info = "";
	$person_name = "";
	
	//$info .= "<!-- get_personnel_person -->"; // tft
	//$info .= "<!-- get_personnel_person -- row: ".print_r($row, true)." -->"; // tft
	
	if ( isset($a['index']) )     	{ $i = $a['index'];             		} else { $i = null; }
    if ( isset($a['post_id']) )     { $post_id = $a['post_id'];             } else { $post_id = null; }
    if ( isset($a['row']) )         { $row = $a['row'];                     } else { $row = null; }
    if ( isset($a['program_type']) ){ $program_type = $a['program_type'];   } else { $program_type = "service_order"; }
    if ( isset($a['run_updates']) ) { $run_updates = $a['run_updates'];     } else { $run_updates = false; }
    if ( isset($a['display']) ) 	{ $display = $a['display'];     		} else { $display = ""; }
	
	if ( isset($row['person'][0]) ) { 
                
		$person_name = get_the_title($row['person'][0]);
		//$person_name = make_link( get_permalink($row['person'][0]), $person_name );
		// TODO: get website for person if any and make link
		
		if ( $program_type == "concert_program" ) {
			$person_id = $row['person'][0];
			if ( isset($row['person_url']) && $row['person_url'] != "" ) { 
                $person_url = $row['person_url'];
            } else {
                $person_url = get_post_meta( $person_id, 'website', true );
            }
			if ( $person_url ) { $person_name = make_link( $person_url, $person_name, null, "_blank" ); } // make_link( $url, $linktext, $class = null, $target = null)
		} else {
			// And/or link to person page on STC site listing events, sermons, &c.
		}
		
	}
	
	if ( empty($person_name) ) {
		
		if ( isset($row['ensemble'][0]) ) { 
			$ensemble_obj = $row['ensemble'][0];
			if ($ensemble_obj) { 
				$person_name = $ensemble_obj->post_title;
			}
		}
		
		if ( empty($person_name) ) {
			if ( isset($row['person_txt']) && $row['person_txt'] != "" && $row['person_txt'] != "x" ) { 
				
				$info .= "<!-- person is empty -> use placeholder person_txt -->";
				$placeholder_item = true;
				$person_name = $row['person_txt'];
				
				// Fill in Placeholder -- see if a matching record can be found to fill in a proper person_name
				if ( $run_updates == true ) {
					$title_to_match = $person_name;
					$info .= "<!-- seeking match for placeholder value: '$title_to_match' -->";
					$match_args = array('index' => $i, 'post_id' => $post_id, 'item_title' => $title_to_match, 'item_label' => $person_role, 'repeater_name' => 'personnel', 'field_name' => 'person', 'display' => $display );
					$match_result = match_placeholder( $match_args );
					$info .= $match_result;
				} else {
                    $info .= "<!-- NO match_placeholder for personnel_person -->";
					$info .= allsouls_add_post_term( $post_id, 'program-personnel-placeholders', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
				}
				
			}
		}
			
	}
	
	$results['person_name'] = $person_name;
	$results['info'] = $info;
	
	return $results;
}


// Program items per Events CPT & ACF -- NEW way
add_shortcode('display_event_program_items', 'get_event_program_items');
function get_event_program_items( $atts = [] ) {
    
	$a = shortcode_atts( array(
		'id'        => get_the_ID(),
        'run_updates' => false,
        'display' => 'table'       
    ), $atts );
    
    $post_id = $a['id'];
    $run_updates = $a['run_updates'];
    $display = $a['display'];
    $info = "";
    if ( $display == 'table' ) { $table = ""; }
    $program_composers = array();
    
    // TODO: deal more thoroughly w/ non-table display option, or eliminate that parameter altogether.
	
	if ($post_id == null) { $post_id = get_the_ID(); }
	$info .= "<!-- Event Program Items for post_id: $post_id -->";
    if ( is_dev_site() ) { $info .= "<!-- DEV -->"; } else { $info .= "<!-- NOT dev -->"; }
    //$info .= "<!-- display: $display -->";
    
    // What type of program is this? Service order or concert program?
    $program_type = get_post_meta( $post_id, 'program_type', true );
    $info .= "<!-- program_type: $program_type -->";
    
    // Program Layout -- left or centered?
    $program_layout = get_post_meta( $post_id, 'program_layout', true );
	
    /*** WIP ***/
    //if ( devmode_active() || is_dev_site() ) { $run_updates = true; } // TMP(?) disabled 03/25/22
    //if ( devmode_active() || ( is_dev_site() && devmode_active() )  ) { $run_updates = true; } // ???
    
	// Get the program item repeater field values (ACF)
    $rows = get_field('program_items', $post_id); // ACF function: https://www.advancedcustomfields.com/resources/get_field/ -- TODO: change to use have_rows() instead?
    /*
    if ( have_rows('program_items', $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
        while ( have_rows('program_items', $post_id) ) : the_row();
            $XXX = get_sub_field('XXX'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
        endwhile;
    } // end if
    */
    
    if ( empty($rows) ) { $rows = array(); }
    //$rows = (!empty(get_field('program_items', $post_id))) ? 'default' : array();
    
    $info .= "<!-- ".count($rows)." program_items rows -->"; // tft
    
    if ( count($rows) > 0 ) {
        
        $table_classes = "event_program program ".$program_layout;
        
        $i = 0; 
		//$i = 1; // row index counter init -- why not zero? see https://www.advancedcustomfields.com/resources/update_sub_field/#notes
        $deletion_count = 0;
        
        if ( $display == 'table' ) {
            $table = '<table class="'.$table_classes.'">';
            $table .= '<tbody>';
        }

        foreach( $rows as $row ) {
            
            // TODO: check if row is empty >> next
            
            // Initialize variables
            $row_info = "";
            //
            $placeholder_label = false;
            $placeholder_item = false;
            //
            $program_item_label = null;
            $program_item_name = null;
            //
            $show_person_dates = true;
            //
            $label_update_required = false;
            $delete_row = false; // init
        
            $row_info .= "<!-- program row [$i]: ".print_r($row, true)." -->"; // tft
            
            // Is this a header row?
            $is_header = $row['is_header'];
        
            // Should this row be displayed on the front end?
            // TODO: modify to simplify as below -- set to true/false based on stored value, if any
            if ( isset($row['show_row']) && $row['show_row'] != "" ) { 
                $show_row = $row['show_row'];
                $row_info .= "<!-- show_row => ".$row['show_row']." -->"; // tft
            } else { 
                $show_row = 1; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
                $row_info .= "<!-- default: show_row = 1 -->"; // tft
            }
        
            // Should we display the item label for this row?
            if ( isset($row['show_item_label']) && $row['show_item_label'] == "0" ) { 
                $show_item_label = false;
                $row_info .= "<!-- show_item_label = 0, i.e. false -->"; // tft
            } else { 
                $show_item_label = true; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
                $row_info .= "<!-- default: show_item_label = true -->"; // tft
            }
                
            // Should the item title for this row be displayed on the front end?
            if ( isset($row['show_item_title']) && $row['show_item_title'] == "0" ) { 
                $show_item_title = false;
                $row_info .= "<!-- show_item_title = 0, i.e. false -->"; // tft
            } else { 
                $show_item_title = true; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
                $row_info .= "<!-- default: show_item_title = true -->"; // tft
            }
        
        	// Get the item label
            // --------------------
            if ( $show_item_label == true ) {
				$arr_item_label = get_program_item_label( array( 'index' => $i, 'post_id' => $post_id, 'row' => $row, 'program_type' => $program_type, 'run_updates' => $run_updates ) );
                //$arr_item_label = get_program_item_label( $post_id, $row, $program_type, $run_updates ); // some way to avoid having to pass post_id (for matching)
				$program_item_label = $arr_item_label['item_label'];
				$row_info .= $arr_item_label['info'];
            }
            
            // Get the program item name
            // --------------------
            // TODO: figure out how to not need to pass so many parameters
            $arr_item_name = array(); // tft
            $arr_item_name = get_program_item_name( array( 'index' => $i, 'post_id' => $post_id, 'row' => $row, 'program_item_label' => $program_item_label, 'show_item_title' => $show_item_title, 'program_type' => $program_type, 'program_composers' => $program_composers, 'run_updates' => $run_updates ) );
            //$arr_item_name = get_program_item_name( $post_id, $program_item_label, $show_item_title, $program_type, $program_composers, $run_updates );
            if ( $arr_item_name['title_as_label'] != "" ) { 
            	$program_item_label = $arr_item_name['title_as_label'];
            	$row_info .= "<!-- title_as_label -->";
            }
            if ( $arr_item_name['item_name'] ) { $program_item_name = $arr_item_name['item_name']; }
            if ( $arr_item_name['program_composers'] ) { $program_composers = $arr_item_name['program_composers']; } // TODO: figure out how to pass program_composers *by reference*
            $row_info .= "<!-- START arr_item_name['info'] -->";
            $row_info .= $arr_item_name['info']; // info is already commented
            $row_info .= "<!-- END arr_item_name['info'] -->";
            //$row_info .= "arr_item_name['info']: <pre>".$arr_item_name['info']."</pre>";
            //$row_info .= "program_item_name: $program_item_name";
            //$row_info .= "<!-- program_item_name: ".$program_item_name." -->";
            //$row_info .= "<!-- arr_item_name info: ".$arr_item_name['info']." -->";
            /****************/
            
            // Cleanup/Deletion of extra/empty program rows
            // --------------------
            // Check for extra/empty rows -- prep to delete them
            if ( empty($program_item_label) && empty($program_item_name) ) {
                // Empty row -- no label, no item
                $delete_row = true;
                $row_info .= "<!-- [$i] delete the row, because everything is empty. -->";
            } else if ( ( $program_item_label == "x" || $program_item_label == "")
                && ( $program_item_label == "x" || $program_item_label == "*NULL*" || $program_item_label == "" ) 
                && ( $program_item_name == "x" || $program_item_name == "*NULL*" || $program_item_name == "" ) 
               ) {
                // Both label and item are either placeholder junk or empty
                $delete_row = true;
                $row_info .= "<!-- [$i] delete the row, because everything is meaningless. -->";
            } else if ( $program_item_label == "*NULL*" || $program_item_name == "*NULL*" ) {
                // TODO: ???
                if ( $program_item_label == "*NULL*" ) { $program_item_label = ""; }
                if ( $program_item_name == "*NULL*" ) { $program_item_name = ""; }
            }
            
            if ( $run_updates == true ) { 
                $do_deletions = true; // tft
            } else {
                $do_deletions = false; // tft
            }
            $do_deletions = false; // tft -- failsafe!
            
            // If the row is empty/x-filled and needs to be deleted, then do so
            if ( $delete_row == true ) {
                
                //allsouls_log("divline1");
                //allsouls_log("program row to be deleted:");
                //allsouls_log( print_r($row, true) );
                $row_info .= "<!-- row: ".print_r($row, true)." -->";
                $row_info .= "<!-- [$i] program row to be deleted -->";
                $row_info .= "<!-- [$i] program row: item_label_txt='".$row['item_label_txt']."'; item_label='".$row['item_label']."'; program_item_txt='".$row['program_item_txt']."' -->";
                //$row_info .= "<!-- [$i] program row: program_item='".print_r($row['program_item'], true)."' -->";
                
                // ... but only run the action if this is the first deletion for this post_id in this round
                // ... because if a row has already been deleted then the row indexes will have changed for all the personnel rows
                // ... and though it would likely not be so difficult to reset the row index accordingly, for now let's proceed with caution...
                if ( $deletion_count == 0 && $do_deletions == true ) {

                    if ( delete_row('program_items', $i, $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/delete_row/ -- syntax: delete_row($selector, $row_num, $post_id) 
                        $row_info .= "<!-- [program row $i deleted] -->";
                        $deletion_count++;
                        //allsouls_log("[program row $i deleted successfully]");
                    } else {
                        $row_info .= "<!-- [deletion failed for program row $i] -->";
                        //allsouls_log("[failed to delete program row $i]");
                    }
                    
                } else {
                    
                    if ( $do_deletions == true ) {
                        $row_info .= "<!-- [$i] row to be deleted on next round due to row_index issues. -->";
                        //allsouls_log("row to be deleted on next round due to row_index issues.");
                    } else {
                        $row_info .= "<!-- [$i] row to be deleted when do_deletions is re-enabled. -->";
                        //allsouls_log("row to be deleted when do_deletions is re-enabled.");
                    }
                }
                
            }
            
            //if ( ( !empty($program_item_label) || !empty($program_item_name) ) || ( $is_header == 1 ) ) {
                
            // Display the row if it's a header, or if BOTH item_label and item_name are not empty
            // --------------------
            
			// Set up the table row
			if ( $display == 'table' && $delete_row != true ) {
				$tr_class = "program_objects";
				if ( $show_row == '0' || $show_row == 0 ) { $tr_class .= " hidden"; } else { $tr_class .= " show_row"; }
				if ( $show_person_dates == false ) { $tr_class .= " hide_person_dates"; } else { $tr_class .= " show_person_dates"; }
				$table .= '<tr class="'.$tr_class.'">';
			}
			
			// Insert row_info for troubleshooting
			if ( is_dev_site() || devmode_active() ) {
				if ( $display == 'table' ) {
					$table .= $row_info; // Display comments w/ in row for ease of parsing dev notes
				} else {
					$info .= $row_info;
				}
			}
			
			// Add the table cells and close out the row
			if ( $display == 'table' && $delete_row != true ) {
				if ( $is_header == 1 ) {
					$td_class = "header";
					if ( $placeholder_label == true ) { $td_class .= " placeholder"; }
					$table .= '<td class="'.$td_class.'" colspan="2">'.$program_item_label.'</td>';
				} else {
					
                    $td_class = "program_label";
					
					if ( $show_item_label != true || empty($program_item_label) ) { $td_class .= " no_label"; }
					if ( $placeholder_label == true ) { $td_class .= " placeholder"; }
					if ( $label_update_required == true ) { $td_class .= " update_required"; }
                    
                    $table .= '<td class="'.$td_class.'">'.$program_item_label.'</td>';
                    $td_class = "program_item";
                    if ( $placeholder_item == true ) { $td_class .= " placeholder"; }
                    $table .= '<td class="'.$td_class.'">'.$program_item_name.'</td>';
                    
				}
				$table .= '</tr>';
			}
			
			// --------------------
            
            $i++;
        
        } // END foreach( $rows as $row )
        
        // --------------------
		
		// Close the table
        if ( $display == 'table' ) {
            $table .= '</tbody>';
            $table .= '</table>';
        }
        
    } // end if $rows
	
    if ( $display == 'table' ) {
        $info .= $table;
    } else if ( $display == 'dev' ) {
        $info = str_replace('<!-- ','',$info);
        $info = str_replace(' -->','<br />',$info);
        $info .= '</p>';
    }
    
	return $info;
}

function get_program_item_label ( $a = array() ) {
	
	// Init vars
	$results = array();
	$info = "";
	$item_label = "";
	$placeholder_label = false;
	$label_update_required = false;
    
    //$info .= "args as passed to get_program_item_label: <pre>".print_r($a,true)."</pre>";
    
    if ( isset($a['index']) )     	{ $i = $a['index'];             		} else { $i = null; }
    if ( isset($a['post_id']) )     { $post_id = $a['post_id'];             } else { $post_id = null; }
    if ( isset($a['row']) )         { $row = $a['row'];                     } else { $row = null; }
    if ( isset($a['program_type']) ){ $program_type = $a['program_type'];   } else { $program_type = null; }
    if ( isset($a['run_updates']) ) { $run_updates = $a['run_updates'];     } else { $run_updates = false; }
	        
	if ( isset($row['item_label'][0]) ) { 
	
		$item_label = get_the_title($row['item_label'][0]);
		$info .= "<!-- program_item_label = row['item_label'][0] -->";

	} else if ( isset($row['item_label']) && !empty($row['item_label']) ) { 

		$term = get_term( $row['item_label'] );
		if ( !empty($term) ) { 
			$item_label = $term->name;
			$info .= "<!-- program_item_label = row['item_label']: ".$row['item_label']." -->";
		} else {
			$info .= "<!-- no term found for: ".$row['item_label']." -->";
		}
	}
    
	if ( !empty ($item_label)) {
		
		// TODO: if a proper item_label is set, delete item_label_old
		
	} else {
		
		// Program item label is empty -- look for a placeholder value
		$info .= "<!-- program_item_label is empty -> use placeholder -->";
		
		if ( isset($row['item_label_old'][0]) && $row['item_label_old'][0] != "" ) {
			
			$label_update_required = true;
			$item_label = get_the_title($row['item_label_old'][0]);
			$info .= "<!-- item_label_old[0]: ".$row['item_label_old'][0]." -->";
			
		} else if ( isset($row['item_label_old'] ) && $row['item_label_old'] != "" ) { 
			
			$label_update_required = true;
			$item_label = get_the_title($row['item_label_old']);
			$info .= "<!-- item_label_old: ".print_r($row['item_label_old'], true)." -->";
			
		} else if ( isset($row['item_label_txt']) && $row['item_label_txt'] != "" && $row['item_label_txt'] != "x" ) { 
			
			$placeholder_label = true;
			$item_label = $row['item_label_txt'];
			$info .= "<!-- item_label_txt: ".print_r($row['item_label_txt'], true)." -->";
			
		}
                
		// Fill in Placeholder -- see if a matching record can be found to fill in a proper item_label
		if ( ($label_update_required == true || $placeholder_label == true) && $run_updates == true ) {
			$title_to_match = $item_label;
			$info .= "<!-- seeking match for placeholder value: '$title_to_match' -->";
			$match_args = array('index' => $i, 'post_id' => $post_id, 'item_title' => $title_to_match, 'repeater_name' => 'program_items', 'field_name' => 'item_label', 'taxonomy' => 'true', 'display' => $display );
			$match_result = match_placeholder( $match_args );
			$info .= $match_result;
		} else {
            $info .= "<!-- NO match_placeholder for program_item_label -->";
			$info .= allsouls_add_post_term( $post_id, 'program-item-placeholders', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
		}
		
	}
    
	//
	$results['item_label'] = $item_label;
	$results['info'] = $info;
	
	return $results;
	
}

// TODO: either figure out a more elegant/efficient way to pass the args
function get_program_item_name ( $a = array() ) {

	// WIP!
	// Init vars
	$results = array();
	$info = "";
	$program_item_name = "";
	$title_as_label = "";
	
    //$info .= "args as passed to get_program_item_label: <pre>".print_r($a,true)."</pre>";
    
    if ( isset($a['index']) )     	{ $i = $a['index'];             		} else { $i = null; }
    if ( isset($a['post_id']) )     { $post_id = $a['post_id'];             } else { $post_id = null; }
    if ( isset($a['row']) )         { $row = $a['row'];                     } else { $row = null; }
    if ( isset($a['program_item_label']) ){ $program_item_label = $a['program_item_label'];   } else { $program_item_label = null; }
    if ( isset($a['show_item_title']) ){ $show_item_title = $a['show_item_title'];   } else { $show_item_title = true; }
    if ( isset($a['program_type']) ){ $program_type = $a['program_type'];   } else { $program_type = "service_order"; }
    if ( isset($a['program_composers']) ){ $program_composers = $a['program_composers'];   } else { $program_composers = null; }
    if ( isset($a['run_updates']) ) { $run_updates = $a['run_updates'];     } else { $run_updates = false; }
    
	// Is this a header row?
    if ( isset($row['is_header']) ) { $is_header = $row['is_header']; } else { $is_header = 0; }
    
    // TODO: deal w/ possibility of MULTIPLE program items in a single row -- e.g. "Anthems"
    // TODO: add option to display all movements/sections of a musical work
    
    //$info .= "<!-- *************** -->";
    $info .= "<!-- row: ".print_r($row, true)." -->"; // tft
    //$info .= "<!-- program_item: ".print_r($row['program_item'], true)." -->"; // tft
    
    $i = 1; // init counter
    
	if ( isset($row['program_item'][0]) ) {
        
        $info .= "<!-- isset: row['program_item'][0] -->";
        //$info .= "<!-- program_item: ".print_r($row['program_item'], true)." -->"; // tft
        $num_items = count($row['program_item']);
		if ( $num_items > 1 ) {
			$info .= "<!-- *** $num_items program_items found for this row! *** -->";
		}

        foreach ( $row['program_item'] as $program_item ) {
        
        	$program_item_obj_id = $program_item; // ACF is now set to return ID for relationship field, not object
        	//$program_item_obj_id = $row['program_item'][0]; // ACF is now set to return ID for relationship field, not object
        	//$program_item_obj = $row['program_item'][0];
        	//$program_item_obj_id = $program_item_obj->ID;
				
            if ( $program_item_obj_id ) {

                $item_post_type = get_post_type( $program_item_obj_id );
                //$info .= "<!-- item_post_type: $item_post_type -->";
                    
                $info .= "<!-- get_program_item_name via postmeta -->";
                $info .= "<!-- item_post_type: $item_post_type -->";

                if ( $item_post_type == 'repertoire' ) {

                    // Get the name of the Musical Work using get_rep_info fcn
                    // FCN: get_rep_info( $post_id = null, $format = 'display', $show_authorship = true, $show_title = true )
                    if ( empty($program_item_label) ) {

                        $info .= "<!-- program_item_label is empty >> use title in left col -->";

                        // If the label is empty, use the title of the musical work in the left-col position and use the composer name/dates in the right-hand column.
                        $title_as_label .= get_rep_info( $program_item_obj_id, 'display', false, true ); // item name withOUT authorship info
                        $item_name = get_authorship_info( array( 'post_id' => $program_item_obj_id ), 'concert_item', false ); // not abbr

                    } else {

                        $item_name = get_rep_info( $program_item_obj_id, 'display', true, $show_item_title ); // item name with authorship info

                    }

                    $info .= "<!-- item_name: $item_name -->";

                    // Store the composer ID(s) so as to check to determine whether to show person_dates or not (goal is to show each composer's dates only once per program)
                    $anon = is_anon($program_item_obj_id);
                    if ( $anon != true ) { $composer_ids = get_composer_ids( $program_item_obj_id ); } else { $composer_ids = array(); }
                    $author_ids = get_author_ids( $program_item_obj_id, false ); // get_author_ids ( $post_id = null, $include_composers = true )
                    //$info .= "<!-- author_ids: ".print_r($author_ids, true)." -->"; // tft

                    // TODO: also check to see if the work is excerpted from another work. The goal is to show the opus/cat num and composer only once per excerpted work per program.

                    if ( (count($composer_ids) > 0 || count($author_ids) > 0) && ! $is_header ) {

                        // Don't include composer ids in the array for header rows, because in those cases the program item (if any) is hidden.
                        if ( $anon != true ) { 
                            $info .= "<!-- composer_ids: ".print_r($composer_ids, true)." -->"; 
                        } else { 
                            $info .= "<!-- count(composer_ids): ".count($composer_ids)." -->";
                        }
                        if ( count($program_composers) > 0 ) {

                            if ( count($composer_ids) > 0 ) {
                                $ids_intersect = array_intersect($program_composers, $composer_ids);
                            } else {
                                $ids_intersect = array_intersect($program_composers, $author_ids);
                            }

                            $info .= "<!-- ids_intersect: ".print_r($ids_intersect, true)." -->";
                            if ( count($ids_intersect) > 0 ) { $show_person_dates = false; }

                            if ( count($composer_ids) > 0 ) {
                                $program_composers = array_unique(array_merge($program_composers, $composer_ids));
                            } else {
                                $program_composers = array_unique(array_merge($program_composers, $author_ids));
                            }

                        } else {

                            if ( count($composer_ids) > 0 ) {
                                $program_composers = $composer_ids;
                            } else {
                                $program_composers = $author_ids;
                            }

                        }
                        $info .= "<!-- program_composers: ".print_r($program_composers, true)." -->";

                    } else if ( !count($author_ids) > 0 ) {

                        $info .= "<!-- author_ids is empty array -->";

                    } // END if ( count($author_ids) > 0 && ! $is_header ) {

                } else if ( $item_post_type == 'sermon' ) {

                    $sermon_author = get_the_title( get_post_meta( $program_item_obj_id, 'sermon_author', true ) );
                    $item_name = $sermon_author.": ".make_link( get_permalink($program_item_obj_id), get_the_title($program_item_obj_id) );

                } else if ( $item_post_type == 'reading' ) {

                    $info .= "<!-- item_post_type: reading -->"; // tft

                    $post_title = get_the_title($program_item_obj_id);
                    if ( preg_match('/\[(.*)\]/',$post_title) ) {
                        $item_name = do_shortcode( $post_title ); // wip
                    } else {
                        $item_name = $post_title;
                    }

                    $info .= "<!-- post_title: '$post_title' -->"; // tft

                } else { // Not of posttype repertoire, sermon, or reading

                    $post_title = get_the_title($program_item_obj_id);
                    if ( preg_match('/\[(.*)\]/',$post_title) ) {
                        $item_name = do_shortcode( $post_title );
                    } else {
                        $item_name = $post_title;
                    }

                }                

            }  

            $program_item_name .= $item_name;
            
            // Add spacer, in the case of multiple program items
            if ( $num_items > 1 && $i != $num_items ) {
                if ( $title_as_label != "" ) { $title_as_label .= '<p class="spacer">&nbsp;</p>'; }
                if ( $program_item_name != "" ) { $program_item_name .= '<p class="spacer">&nbsp;</p>'; }
            }

            $i++;

        } // end foreach program_item
        
        // Is there a program note for this item? If so, append it to the item name
        if ( isset($row['program_item_note']) && $row['program_item_note'] != "" ) {
            if ( $title_as_label != "" ) { 
                $title_as_label .= "<br /><em>".$row['program_item_note']."</em>";
            } else if ( $program_item_name != "" ) { 
                $program_item_name .= "<br /><em>".$row['program_item_note']."</em>";
            }            
        }

    } // END if ( isset($row['program_item'][0]) )

    $info .= "<!-- program_item_name: $program_item_name -->";
    
    if ( empty($program_item_name) ) {
        
        $info .= "<!-- program_item_name is empty >> placeholder -->";
        
        if ( isset($row['program_item_txt']) && $row['program_item_txt'] != "" && $row['program_item_txt'] != "x" ) { 

            $placeholder_item = true;
            $program_item_name = $row['program_item_txt'];

            // Fill in Placeholder -- see if a matching record can be found to fill in a proper program_item
            if ( $run_updates == true ) {

                if ( isset($row['program_item_title_for_matching']) && $row['program_item_title_for_matching'] != "" ) {
                    $title_to_match = $row['program_item_title_for_matching'];
                    //$row_info .= "<!-- title_to_match = program_item_title_for_matching -->";
                } else {
                    $title_to_match = $program_item_name;
                    //$row_info .= "<!-- title_to_match = program_item_name -->";
                }
                //$row_info .= "<!-- title_to_match: [$title_to_match] -->";

                $info .= "<!-- seeking match for placeholder value: '$title_to_match' -->";
                $match_args = array('index' => $i, 'post_id' => $post_id, 'item_title' => $title_to_match, 'item_label' => $program_item_label, 'repeater_name' => 'program_items', 'field_name' => 'program_item' ); // , 'display' => $display
                $match_result = match_placeholder( $match_args );
                $info .= $match_result;

            } else {
                $info .= "<!-- NO match_placeholder for program_item_name -->";
                $info .= allsouls_add_post_term( $post_id, 'program-item-placeholders', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
            }
        }
    }
    
	//
	$results['title_as_label'] = $title_as_label; // if using musical work title in place of label... TODO: make this less convoluted.
	$results['item_name'] = $program_item_name;
	$results['program_composers'] = $program_composers;
	$results['info'] = $info;
	
	return $results;
	
}

// WIP
function match_program_placeholders() {
    
    // (1) Personnel: person_roles
    // (2) Personnel: persons
    // (3) Program Items: item_labels
    // (4) Personnel: program_items
    
}


// Clean up Event Personnel: fill in real values from placeholders; remove obsolete/orphaned postmeta
add_shortcode('event_personnel_cleanup', 'event_personnel_cleanup');
function event_personnel_cleanup(  $atts = [] ) {
	
    if ( !current_user_can('administrator') ) {
        // Not an admin? Don't touch my database!
        return false;
    }
	$a = shortcode_atts( array(
		'id'        => null, //get_the_ID(),
        'num_posts' => 5,
    ), $atts );
    
	//$event_post_id = $a['id'];
	$num_posts = $a['num_posts'];
    
    $info = "";
    
    // Get all posts w/ personnel rows
    $args = array(
		'post_type'   => 'event',
		'post_status' => 'publish',
        'posts_per_page' => $num_posts,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'     => 'personnel',
                'compare' => 'EXISTS'
            ),
            array(
                'key'     => 'personnel',
                'compare' => '!=',
                'value'   => 0,
            ),
            array(
                'key'     => 'personnel',
                'compare' => '!=',
                'value'   => '',
            )
        ),
        'orderby'   => 'ID meta_key',
        'order'     => 'ASC',
        'tax_query' => array(
            //'relation' => 'AND', //tft
            array(
                'taxonomy' => 'admin_tag',
                'field'    => 'slug',
                'terms'    => array( 'program-personnel-placeholders' ),
                //'terms'    => array( 'program-placeholders' ),
                //'terms'    => array( $admin_tag_slug ),
                //'operator' => 'NOT IN',
            ),
            /*
            array(
                'taxonomy' => 'event-categories',
                'field'    => 'slug',
                'terms'    => 'choral-services',//'terms'    => 'worship-services',
                
            )*/
        ),
    );
    $result = new WP_Query( $args );
    $posts = $result->posts;
    
    if ( $posts ) {
        
        $info .= "Found ".count($posts)." event posts with personnel postmeta.<br /><br />";
        //$info .= "args: <pre>".print_r($args, true)."</pre>";
        //$info .= "Last SQL-Query: <pre>".$result->request."</pre>";
        
        foreach ( $posts AS $post ) {
        
            setup_postdata( $post );
            $post_id = $post->ID;
            $meta = get_post_meta( $post_id );
            //$post_info .= "post_meta: <pre>".print_r($meta, true)."</pre>";
            $post_info = ""; // init
            $num_repeater_rows = 0;
            $arr_repeater_rows_indices = array();
            
            $info .= '<div class="code">';
            $info .= "post_id: $post_id<br />";
            
            foreach ( $meta as $key => $value ) {
                
                if (strpos($key, 'personnel') == 0) { // meta_key starts w/ 'personnel' (no underscore)
                    $post_info .= "<code>$key => ".$value[0]."</code><br />";
                    if ($key == 'personnel') {
                        $num_repeater_rows = $value[0];
                    } else { // if (strpos($key, 'personnel_') == 0) -- meta_key starts w/ 'personnel_' (with underscore)
                        // Get the int indicating the row index, and add it to the array if it isn't there already
                        $int_str = preg_replace('/[^0-9]+/', '', $key);
                        if ( $int_str != "" && ! in_array($int_str, $arr_repeater_rows_indices) ) { $arr_repeater_rows_indices[] = $int_str; }
                    }
                }
                
                if ( empty($value) || $value == "x" ) {
                    // Delete empty or placeholder postmeta
                    //delete_post_meta( int $post_id, string $meta_key, mixed $meta_value = '' )
                    //delete_post_meta( $post_id, $key, $value );
                    /*if ( delete_post_meta( $post_id, $key, $value ) ) {
                        $post_info .= "delete_post_meta ok for post_id [$post_id], key [$key], post_id [$key]<br />";
                    } else {
                        $post_info .= "delete_post_meta FAILED for post_id [$post_id], key [$key], post_id [$key]<br />";
                    }*/
                }
                
            }
            
            // Check to see if 'personnel' int val matches number of rows indicated by postmeta fields
            if ( count($arr_repeater_rows_indices) != $num_repeater_rows ) {
                $post_info .= "<code>".print_r($arr_repeater_rows_indices, true)."</code><br />";
                $post_info .= "personnel official count [$num_repeater_rows] is ";
                if ( $num_repeater_rows < count($arr_repeater_rows_indices) ) {
                    $post_info .= "LESS than";
                } else if ( $num_repeater_rows > count($arr_repeater_rows_indices)) {
                    $post_info .= "GREATER than";
                }
                $post_info .= " num of repeater rows in postmeta [".count($arr_repeater_rows_indices)."] => cleanup required!<br />";
                $post_info .= allsouls_add_post_term( $post_id, 'cleanup-required', 'admin_tag', true ); // $post_id, $arr_term_slugs, $taxonomy, $return_info
            }
                    
                // Remove row via ACF function -- ???
                    /*if ( delete_row('personnel', $i, $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/delete_row/ -- syntax: delete_row($selector, $row_num, $post_id) 
                        $row_info .= "<!-- [personnel row $i deleted] -->";
                        $deletion_count++;
                        allsouls_log("[personnel row $i deleted successfully]");
                    } else {
                        $row_info .= "<!-- [deletion failed for personnel row $i] -->";
                        allsouls_log("[failed to delete personnel row $i]");
                    }*/
            
            $post_info .= "<br />";
            // TODO: figure out how to show info ONLY if changes have been made -- ??
            $post_info .= get_event_personnel( $post_id, true, 'dev' ); // get_event_personnel( $post_id, $run_updates )
            //$post_info .= get_event_program_items( $post_id, true, 'dev' );
            
            $info .= $post_info;
            $info .= '</div>';
            
        }
        
    } else {
        
        $info .= "No matching posts found.<br />";
        $info .= "args: <pre>".print_r($args, true)."</pre>";
        $info .= "Last SQL-Query: <pre>".$result->request."</pre>";
        
    }
    
    return $info;
    
}


function get_event_programs_containing_post( $post_id = null ) { // formerly get_program_containing_post
    
    global $post;	
    $info = ""; // init
    $arr_event_ids = array(); // init
	if ($post_id == null) { $post_id = get_the_ID(); }
        
    // Go straight to the DB and get ONLY the post IDs of relevant related event posts...
    global $wpdb;
    
    $sql = "SELECT `post_id` 
            FROM $wpdb->postmeta
            WHERE `meta_key` LIKE 'program_items_%_program_item'
            AND `meta_value` LIKE '%".'"'.$post_id.'"'."%'";
    
    /*$sql = "SELECT `post_id` 
            FROM $wpdb->postmeta, $wpdb->posts
            WHERE $wpdb->postmeta.`meta_key` LIKE 'program_items_%_program_item'
            AND $wpdb->postmeta.`meta_value` LIKE '%".'"'.$post_id.'"'."%'
            AND $wpdb->postmeta.`post_id`=$wpdb->posts.`ID`
            AND $wpdb->posts.`post_type`='event'";*/
    
    /*$sql = "SELECT `post_id` 
            FROM $wpdb->postmeta, $wpdb->posts
            WHERE `meta_key` LIKE 'program_items_%_program_item'
            AND `meta_value` LIKE '%".'"'.$post_id.'"'."%'
            AND `post_id`=`ID`
            AND `post_type`='event'";*/

    $arr_ids = $wpdb->get_results($sql);
    
    // Flatten the array by a layer; remove non-event posts
    foreach( $arr_ids as $arr ) {
        
        $related_post_id = $arr->post_id;
        if ( get_post_type( $related_post_id ) == 'event' ) {
            $arr_event_ids[] = $related_post_id;
        }        
            
        /*
        //$related_post = get_post( $related_post_id );
        //$related_post_type = $related_post->post_type;

        // if it is a legit published event, then show the info
        if ( $related_post_type == 'event' ) {
            $arr_event_ids[] = $related_post_id;
        }
        
        //$arr_event_ids[] = $arr->post_id;
        */
    }
    
    /*foreach( $arr_ids as $arr ) {
        $arr_event_ids[] = $arr->post_id;
    }*/
    
    /* OLD approach -- very very slow
        
    $args = array(
        'posts_per_page'=> -1,
        'post_type'		=> 'event',
        'meta_query'	=> array(
            array(
                'key'		=> "program_items_XYZ_program_item", // name of custom field, with XYZ as a wildcard placeholder (must do this to avoid hashing)
                'compare' 	=> 'LIKE',
                'value' 	=> '"' . $post_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
            )
        )
    );

    $query = new WP_Query( $args );
    $arr_posts = $query->get_posts();
    //$info .= "args: <pre>".print_r($args, true)."</pre>"; // tft
    //$info .= "Last SQL-Query: <pre>".$query->request."</pre>";
    //$info .= "arr_posts: <pre>".print_r($arr_posts, true)."</pre>"; // tft

    wp_reset_query();
        
    }*/
    
    return $arr_event_ids;
	
}



/*** EVENTS/WEBCASTS ***/

add_shortcode( 'display_webcasts', 'atc_display_webcast_events' );
function atc_display_webcast_events() {
	
	// Ensure the global $post variable is in scope
	//global $post; // ??? Is this actually necessary here?
	$info = "";
	
    // Query Events Manager [EM] posts
    // TODO: test this...
    $args = array(
        'post_type'         => 'event',
        'posts_per_page'    => 5,
        'scope'   	        => 'future',
        'tax_query'         => array(
            array(
                'taxonomy' 	=> 'event-categories',
                'field' 	=> 'slug',
                'terms' 	=> 'webcasts'
            )
        )
    );

    $result = new WP_Query( $args );
    $upcoming_events = $result->posts;

	// Loop through the events: set up each one as
	// the current post then use template tags to
	// display the title and content
	if (count($upcoming_events) > 0) { $info .= "<h2>Upcoming</h2>"; }
	foreach ( $upcoming_events as $post ) {
        setup_postdata( $post );

        // This time, let's throw in an event-specific
        // template tag to show the date after the title!
        $info .= '<h4>' . $post->post_title . '</h4>';
        $event_date = get_post_meta( $post->ID, '_event_start_date', true );
        $info .= '<p>' . $event_date . '</p>';
        //$event_date = get_post_meta( $event_id, '_event_start_date', true );
	}
    
    // Query Events Manager [EM] posts
    // TODO: test this...
    $args = array(
        'post_type'         => 'event',
        'posts_per_page'    => 5,
        'scope'   	        => 'past',
        'tax_query'         => array(
            array(
                'taxonomy' 	=> 'event-categories',
                'field' 	=> 'slug',
                'terms' 	=> 'webcasts'
            )
        )
    );

    $result = new WP_Query( $args );
    $past_events = $result->posts;
	
	if (count($past_events) > 0) { $info .= "<h2>Past</h2>"; }
	foreach ( $past_events as $post ) {
        setup_postdata( $post );

        // This time, let's throw in an event-specific
        // template tag to show the date after the title!
        $info .= '<h3><a href="'. get_permalink($post->ID) . '">' . $post->post_title . '</a></h3>';
        $event_date = get_post_meta( $post->ID, '_event_start_date', true );
        $info .= '<p>' . $event_date . '</p>';
	}
	
	return $info;
}

/*** EM Events Manager Customizations ***/

// Function to modify default #_EVENTLINK placeholder
add_filter('em_event_output_placeholder','allsouls_placeholders',1,3); // TMP DISABLED 03/25/22
function allsouls_placeholders( $replace, $EM_Event, $result ) {
    
    $post_id = $EM_Event->post_id;
    //$event_id = $EM_Event->ID;
    
    $event_title = $EM_Event->event_name;
    //$event_title = get_the_title($post_id); // breaks things. why?!?!?!
    
    $event_title = remove_bracketed_info($event_title);
    //$event_title = htmlentities($event_title); // In case there are apostrophes (probably only possible in the short_title?)
    
    if ( is_dev_site() ) {
        
        //$event_title = get_the_title($EM_Event->ID); // For some reason this is breaking things on the live site, but only when event titles have info in brackets with space around hyphen -- e.g. 2022 - Shrine Prayers
        
        // Get the series title, if any
        $series_id = null;
        $series_title = "";

        $event_series = get_post_meta( $post_id, 'event_series', true );
        if ( isset($event_series['ID']) ) { 
            $series_id = $event_series['ID'];
            $prepend_series_title = get_post_meta( $series_id, 'prepend_series_title', true );
            if ( $prepend_series_title == 1 ) { $series_title = get_the_title( $series_id ); }
        }

        // Prepend series_title, if applicable
        if ( $series_title != "" ) { $event_title = $series_title.": ".$event_title; }
    }
    
    if ( $result == '#_EVENTLINK' ) {
        
        $event_link = esc_url($EM_Event->get_permalink());
        //$replace = '<a href="'.$event_link.'">'.get_the_title($EM_Event->ID).'</a>';
        $replace = '<a href="'.$event_link.'">'.$event_title.'</a>';
        
        //if ( is_dev_site() ) { $replace .= "<!-- series_id: ".$series_id."; series_title: ".$series_title." -->"; }
        
    } else if ( $result == '#_EDITEVENTLINK' ) {
        
        if( $EM_Event->can_manage('edit_events','edit_others_events') ){
            $link = esc_url($EM_Event->get_edit_url());
            $link .= "&post_type=event";
            $replace = '<a href="'.$link.'">'.esc_html(sprintf(__('Edit Event','events-manager'))).'</a>';
        }
        
    } else if ( $result == '#_EVENTNAME' ) {
        
        $replace = $event_title;
        //$replace .= "<!-- event ID: [".$EM_Event->ID."]; post_id: [".$post_id."] -->"; // tft
        
        // TODO: coordinate/consolidate vis-a-vis function clean_title()... Why doesn't that fcn take care of FullCalendar display?
        /*if (strpos($replace, '[') != false) { 
            $replace = preg_replace('/\[[^\]]*\]([^\]]*)/', '$1', $replace);
            $replace = preg_replace('/([^\]]*)\[[^\]]*\]/', '$1', $replace);
        }*/
        
    }  else if ( $result == '#_EVENTNAMESHORT' ) {
        
        // Get the short_title, if any
        if ( $post_id && $post_id != "" ) { 
            $short_title = get_post_meta( $post_id, 'short_title', true );
            // If a short_title is set, use it
            if ( $short_title ) { $event_title = $short_title; }
        }
        $replace = $event_title;
        
    } else if ( $result == '#_EVENTIMAGE' ) {
        
        // Modified version of default to actually show image & caption only under certain circumstances
        
        $post_id = $EM_Event->ID;
        $show_image = true;
        
        if ( has_term( 'webcasts', 'event-categories', $post_id ) 
            || has_term( 'webcasts', 'category', $post_id ) 
            || has_term( 'video-webcasts', 'event-categories', $post_id ) 
            || has_term( 'video-webcasts', 'category', $post_id )) {
            $webcast_status = get_webcast_status( $post_id );
            $webcast_format = get_field('webcast_format', $post_id);
            $video_id = get_field('video_id', $post_id);
            if ( $webcast_format == "video" ) {
                $url_ondemand = get_field('url_ondemand', $post_id);
            }
            
            // If we've got a video_id and the status is live or on demand, then don't show the image
            // && in_array( $webcast_status, array('before', 'live', 'on_demand')
            
            if ( ( !empty($video_id) && ( $webcast_status == "live" || $webcast_status == "on_demand" || $webcast_format == "vimeo" ) ) // Vimeo  || $webcast_format == "vimeo_recurring"
                || ( !empty($video_id) && $webcast_format == "youtube" ) // YouTube
                || ( $webcast_format == "video" && ( !empty($url_ondemand) ) ) // YouTube
                //|| ( !empty($video_id) && ( has_term( 'video-webcasts', 'event-categories', $post_id ) || has_term( 'video-webcasts', 'category', $post_id ) ) ) // Non-Vimeo (i.e. Flowplayer) Video webcast
               ) { 
                $show_image = false;
                $replace = "";
            }
        }
        
        if ( $show_image == true ) {
            
            $classes = "post-thumbnail stc";
            $caption = featured_image_caption($EM_Event->ID);

            if ( !empty($caption) && $caption != '<p class="zeromargin">&nbsp;</p>' ) {
                $classes .= " has_caption";
            }
            
            $replace .= $caption."<!-- allsouls_placeholders -->";
            $replace = '<div class="'.$classes.'">'.$replace.'</div>';
            
        } else {
            $replace .= "<!-- webcast_status: $webcast_status; webcast_format: $webcast_format; video_id: $video_id -->";
            $replace .= "<br /><!-- stc-calendar >> allsouls_placeholders -->"; // If there's no featured image, add a line break to keep the spacing
        }
        
    } else if ( $result == '#_EVENTCLASSES' ) {
        
        $replace = "";
        $post_id = $EM_Event->ID;
        $arr_terms = get_the_terms( $post_id, "event-categories" );
        // WIP/TODO: get event category color?
        foreach ( $arr_terms as $term ) {
            if ( $term->parent ) {
                $parent_term = get_term( $term->parent );
                $replace .= $parent_term->slug." ";
            }
            $replace .= $term->slug." ";
        }
        
    } else if ( $result == '#_DAYTITLE' ) {
        
        //$replace = "day_title for".$EM_Event->start_date; // tft
        $atts = array('the_date'=>$EM_Event->start_date);
        $replace = get_day_title($atts);
        
    } else if( preg_match('/^#_DAYTITLE\{?/', $result) ){
        
        //$replace = "day_title for".$EM_Event->start_date; // tft
        $args = explode(',', preg_replace('/#_DAYTITLE\{(.+)\}/', '$1', $result));
        $atts = array('the_date'=>$EM_Event->start_date);
        $replace = get_day_title($atts);
        
    }
    
    return $replace;
}

// Set order of display to reverse chronological for event category archives
// https://wordpress.org/support/topic/set-event-ordering-for-_categorypastevents-placeholder/
add_filter('em_category_output_placeholder','cat_em_placeholder_mod',1,3);
function cat_em_placeholder_mod($replace, $EM_Category, $result){
	if ( $result == '#_CATEGORYPASTEVENTS' ) {
	   $em_termID = $EM_Category->term_id;
	   $args = array('category'=>$em_termID,'order'=>'DESC','scope'=>'past','pagination'=>1, 'limit'=>20);
	   $args['format'] = get_option('dbem_category_event_list_item_format');
	   $args['format_header'] = get_option('dbem_category_event_list_item_header_format');
	   $args['format_footer'] = get_option('dbem_category_event_list_item_footer_format');
       $replace = EM_Events::output($args);
	}
	return $replace;
}

add_filter( 'em_content_events_args', 'exclude_unlisted_events' );
function exclude_unlisted_events ( $args ) {
    $args['tag'] = "-unlisted";
    return $args;
}

// SEE INSTEAD -- custom code in events-manager/classes/em-events.php -- search for "atc"
//add_filter('em_events_output_grouped_args','em_args_mod',1,3);
function em_args_mod($args){
    
    // This was WIP because day_title shortcode wasn't working in context of events_list_grouped -- i.e. on pages where that EM shortcode is in use. 
    if ( isset($args['format_header']) && ! is_page('events') ) {
        //$args['format_header'] = apply_shortcodes( $args['format_header'] );
        //$args['format_header'] = "***".$args['format_header']."***"; // tft
        $args['format_header'] = "***";
    }
    
    if ( isset($args['header_format']) && ! is_page('events') ) {
              
        //$args['header_format'] = str_replace('[day_title the_date="#s"]', '<!-- TBD: day_title -->', $args['header_format']); // ok for testing
        //$args['header_format'] = str_replace('[day_title the_date="#s"]', do_shortcode('[day_title the_date="2020-11-22"]'), $args['header_format']); // tft -- ok -- but not very useful
        //$args['header_format'] = str_replace('[day_title the_date="#s"]', "do_shortcode('[day_title the_date=\"#s\"]')", $args['header_format']); // nope
        //$args['header_format'] = str_replace('[day_title the_date="#s"]', do_shortcode('[day_title the_date="#s"]'), $args['header_format']); // almost -- but shortcode can't get actual val of #s
        //$args['header_format'] = str_replace('[day_title the_date="#s"]', '#_DAYTITLE{#s}', $args['header_format']); // nope -- just outputs placeholder as string with translated date  
        
        //$header_format = "do_shortcode('[day_title the_date=\"#s\"]')";
        //$args['header_format'] = str_replace('[day_title the_date="#s"]', $header_format, $args['header_format']); // ??
        
        //$args['header_format'] = apply_shortcodes( $args['header_format'] );
        
        //// For now, just hide the day_title shortcode -- can't get it to run except on main calendar page
        
        //$args['header_format'] .= "***"; // tft
        
	}
    //allsouls_log("em_events_output_grouped_args: ".print_r($args, true));
	return $args;
}


// Create custom scopes: "Upcoming", "This Week", "This Season", "Next Season", "This Year", "Next Year"
function allsouls_em_custom_scopes( $scope = null ) {
	
	if( empty($scope) ) {
		return null;
	}
	
	// Init results vars
	$dates = array();
	$start_date = null;
	$end_date = null;
	
    // get info about today's date
    $today = time(); //$today = new DateTime();
	$year = date_i18n('Y');
	
	// Define basic season parameters
	$season_start = strtotime("September 1st");
	$season_end = strtotime("July 1st");
	
	if ( $scope == 'today-onward' ){
        
        $start_date = date_i18n("Y-m-d"); // today
        $decade = strtotime($start_date." +10 years");
        $end_date = date_i18n("Y-m-d",$decade);
    
    } else if ( $scope == 'upcoming' ){
    
    	// Get start/end dates of today plus six
        
        $start_date = date_i18n("Y-m-d"); // today
        $seventh_day = strtotime($start_date." +6 days");
        $end_date = date_i18n("Y-m-d",$seventh_day);
    
    } else if ( $scope == 'this-week' ){
    
    	// Get start/end dates for the current week
        
        $sunday = strtotime("last sunday");
        $sunday = date_i18n('w', $sunday)==date('w') ? $sunday+7*86400 : $sunday;
        $saturday = strtotime(date("Y-m-d",$sunday)." +6 days");
        $start_date = date_i18n("Y-m-d",$sunday);
        $end_date = date_i18n("Y-m-d",$saturday);
    
    } else if ( $scope == 'this-season' ){
    
    	// Get actual season start/end dates
		if ($today < $season_start){
			$season_start = strtotime("-1 Year", $season_start);
		} else {
			$season_end = strtotime("+1 Year", $season_end);
		}
		
		$start_date = date_i18n('Y-m-d',$season_start);
		$end_date = date_i18n('Y-m-d',$season_end);
    
    } else if( $scope == 'next-season' ){
    
		// Get actual season start/end dates
		if ($today > $season_start){
			$season_start = strtotime("+1 Year", $season_start);
			$season_end = strtotime("+2 Year", $season_end);
		} else {
			$season_end = strtotime("+1 Year", $season_end);
		}
		
		$start_date = date_i18n('Y-m-d',$season_start);
		$end_date = date_i18n('Y-m-d',$season_end);
    
    } else if( $scope == 'this-year' ){
    
    	$start = strtotime("January 1st, {$year}");
    	$end = strtotime("December 31st, {$year}");
		
		$start_date = date_i18n('Y-m-d',$start);
		$end_date = date_i18n('Y-m-d',$end);
    
    } else if( $scope == 'next-year' ){
    
    	$year = $year+1;
    	$start = strtotime("January 1st, {$year}");
    	$end = strtotime("December 31st, {$year}");
		
		$start_date = date_i18n('Y-m-d',$start);
		$end_date = date_i18n('Y-m-d',$end);
    
    }
	
	$dates['start'] = $start_date;
	$dates['end'] 	= $end_date;
	
	return $dates;
	
}
// SEE BELOW: ..._build_sql_conditions


// Convert custom scopes to array to allow for proper filtered results to display in Admin
// WIP -- tmp disabled because not fully working
//add_filter( 'em_object_build_sql_conditions_args', 'allsouls_em_custom_scope_arg',10,1); // CMS(?)
function allsouls_em_custom_scope_arg( $args = array() ){

	if( is_admin() && !empty($args['scope']) ) {
		
		$scope = $args['scope'];
		if ( ! is_array($scope) ) {
			//allsouls_log("args['scope']".$args['scope']);
		} else {
			//allsouls_log("args['scope']". print_r($args['scope'],true) );
		}
		
        // If this is the main admin events page...
        //allsouls_log("args: ". print_r($args, true) );
        //https://dev.saintthomaschurch.org/wp-admin/edit.php?s&post_status=all&post_type=event
        
		// TODO: figure out how to eliminate redundancy of array declaration w/ allsouls_em_scopes
		$my_scopes = array( 'this-season', 'next-season', 'this-year', 'next-year');
		
		if ( in_array($scope, $my_scopes) ) {		
			
			allsouls_log($scope." is a custom scope.");
			$arr_dates = allsouls_em_custom_scopes($scope);
		
			if ( $arr_dates) {
				$start_date = $arr_dates['start'];
				$end_date 	= $arr_dates['end'];
				if ( !empty($start_date) && !empty($end_date) ) {
					$args['scope'] = array( $start_date, $end_date );
				}
			}
			
		} else {
			
		}
		
	}
    
    return $args;
}

// Register custom scopes
// TODO: figure out why this isn't working. New scopes show up in EM dropdown in CMS, but don't have any effect
add_filter( 'em_get_scopes', 'allsouls_em_scopes', 10, 1);
function allsouls_em_scopes($scopes){
    $my_scopes = array(
		'upcoming' => 'Upcoming',
		'this-week' => 'This Week',
        //'next-month' => __('Events next month','events-manager'),
        'this-season' => 'This Season',
		//'this-season' => __('Events this season','events-manager'),
        'next-season' => 'Next Season',
        'this-year' => 'This Year',
        'next-year' => 'Next Year'
    );
	$scopes = array_merge($scopes, $my_scopes);
    //return $scopes + $my_scopes;
	return $scopes;
	
}

/*
 * This snippet makes recurring events public 
 * eg. allow custom sidebars "Default Sidebar" metabox to appear when creating recurring event
 */
add_filter('em_cp_event_recurring_public','__return_true');


/***** wip *****/

// Add "event_series" to accept EM search parameters (attributes)
add_filter('em_events_get_default_search','allsouls_custom_event_search_parameters',1,2);
add_filter('em_calendar_get_default_search','allsouls_custom_event_search_parameters',1,2);
function allsouls_custom_event_search_parameters($args, $array){
    
    $args['series'] = false; // registers 'series' (ID) as an acceptable value, although set to false by default
    if( !empty($array['series']) && is_numeric($array['series']) ){
        $args['series'] = $array['series'];
    }
    return $args;
    
}

// TODO: combine this with scope-related em_events_build_sql_conditions filter function?
add_filter( 'em_events_build_sql_conditions', 'allsouls_custom_event_search_build_sql_conditions',1,2);
function allsouls_custom_event_search_build_sql_conditions($conditions, $args){
    
    allsouls_log( "divline2" );
    allsouls_log( "function called: allsouls_custom_event_search_build_sql_conditions" );
    
    allsouls_log( "[allsouls_custom_event_search...] conditions: ".print_r($conditions, true) );
    allsouls_log( "[allsouls_custom_event_search...] args: ".print_r($args, true) );
    
    global $wpdb;
    
    if( !empty($args['series']) && is_numeric($args['series']) ){
        
        allsouls_log( "[allsouls_custom_event_search...] series is set and valid: ".$args['series'] );
        $meta_value = '%"'.$args['series'].'"%';
        $sql = $wpdb->prepare(
            "SELECT `event_id` FROM ".EM_EVENTS_TABLE.", `wpallsouls_postmeta` WHERE `meta_value` LIKE %s AND `meta_key`='event_series' AND ".EM_EVENTS_TABLE.".`post_id` = `wpallsouls_postmeta`.`post_id`", $meta_value
        ); // 
        //$sql = $wpdb->prepare("SELECT post_id FROM `wpallsouls_postmeta` WHERE meta_value=%s AND meta_key='event_series'", $args['event_series']);
        //$sql = $wpdb->prepare("SELECT object_id FROM ".EM_META_TABLE." WHERE meta_value=%s AND meta_key='event_series'", $args['event_series']);
        $conditions['series'] = "event_id IN ($sql)";
        
    }
    
    // The following seems to effect only front-end display. Look into affecting back-end display, also.
    if( !empty($args['scope']) ) {
		
        allsouls_log( "[allsouls_custom_event_search...] scope: ".print_r( $args['scope'],true ) );
        
		$scope = $args['scope'];
		$arr_dates = allsouls_em_custom_scopes($scope);
		
		if ( $arr_dates) {
			$start_date = $arr_dates['start'];
			$end_date 	= $arr_dates['end'];
			if ( !empty($start_date) && !empty($end_date) ) {
				$conditions['scope'] = " (event_start_date BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE)) OR (event_end_date BETWEEN CAST('$end_date' AS DATE) AND CAST('$start_date' AS DATE))";
			}
		}
		
	}
    
    allsouls_log( "[allsouls_custom_event_search...] modified conditions: ".print_r($conditions, true) );
    
    return $conditions;
}

// Program/Event info via Event CPT & ACF -- for Admin use/Troubleshooting
add_shortcode('display_event_stats', 'display_event_stats');
function display_event_stats( $post_id = null ) {
	
	$info = ""; // init
    
    extract( shortcode_atts( 
        array( 
            'post_id' => 'post_id'
         ), $post_id ) ); 
    
	if ( $post_id == null ) { $post_id = get_the_ID(); }
    $info .= 'ID: <span class="nb">'.$post_id.'</span>; ';
	$post   = get_post( $post_id );
    $post_meta = get_post_meta( $post_id );
    
    $recurrence_id = get_post_meta( $post_id, '_recurrence_id', true );
    if ( $recurrence_id ) { $info .= 'RID: <span class="nb">'.$recurrence_id.'</span>; '; }
    
    $parent_id = $post->post_parent;
    if ( $parent_id ) { $info .= 'parent_id: <span class="nb">'.$parent_id.'</span>; '; }
    
	// Get the personnel & program_items repeater field values (ACF)
	$personnel = get_field('personnel', $post_id);
    if ( $personnel && count($personnel) > 0 ) { $info .= '<span class="nb">'.count($personnel).'</span>'." pers.; "; }
	
	$program_items = get_field('program_items', $post_id);
    if ( $program_items && count($program_items) > 0 ) { $info .= '<span class="nb">'.count($program_items).'</span>'." prog.; "; }
	
    //Variable: Additional characters which will be considered as a 'word'
    $char_list = ""; /** MODIFY IF YOU LIKE.  Add characters inside the single quotes. **/
    //$char_list = '0123456789'; /** If you want to count numbers as 'words' **/
    //$char_list = '&@'; /** If you want count certain symbols as 'words' **/
    $word_count = str_word_count(strip_tags($post->post_content), 0, $char_list);
    $info .= '[<span class="nb">'.$word_count.'</span> words]';
    
    //$info .= "<pre>".print_r($post,true)."</pre>";
    //$info .= "<pre>".print_r($post_meta,true)."</pre>";    
    //$info .= "Delete"; // add delete link...
    
	return $info;
}

// Tidier slugs for recurring event instances
/*
function append_slug($data) {
    global $post_ID;

    //if (empty($data['post_name'])) {
    if (!empty($data['post_name']) && $data['post_status'] == "publish" && $data['post_type'] == "post") {
    
        if( !is_numeric(substr($data['post_name'], -4)) ) {
              $random = rand(1111,9999);
              $data['post_name'] = sanitize_title($data['post_title'], $post_ID);
              $data['post_name'] .= '-' . $random;
          }
          
        $data['post_name'] = sanitize_title($data['post_title'], $post_ID);
        $data['post_name'] .= '-' . generate_arbitrary_number_here();
    }

    return $data;
}

add_filter('wp_insert_post_data', 'append_slug', 10); 
*/

?>