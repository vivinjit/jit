<?php
     /* 
    Plugin Name: Vertical News Scroller
    Plugin URI:https://www.i13websolution.com/product/wordpress-vertical-news-scroller-pro/
    Author URI:http://www.i13websolution.com
    Description: Plugin for scrolling Vertical News on wordpress theme.Admin can add any number of news.
    Author:I Thirteen Web Solution
    Text Domain:vertical-news-scroller
    Version:1.15
    */

    //error_reporting(0);
    //add_action( 'admin_init', 'vertical_news_scroller_plugin_admin_init' );
    register_activation_hook(__FILE__,'install_newsscroller');
    register_deactivation_hook(__FILE__,'vns_vertical_news_remove_access_capabilities');
    add_shortcode('print_vertical_news_scroll', 'print_verticalScroll_func' ); 
    add_action('admin_menu',    'scrollnews_plugin_menu');  
    add_filter('widget_text', 'do_shortcode');
    /* Add our function to the widgets_init hook. */
    add_action( 'widgets_init', 'verticalScrollSet' );

    add_action('plugins_loaded', 'vns_load_lang_for_vertical_news_scroller');
    add_action('wp_enqueue_scripts', 'news_scroller_load_styles_and_js');

    add_action( 'upgrader_process_complete',  'vns_vertical_news_upgrader_process_complete', 10, 4 );

    function vns_load_lang_for_vertical_news_scroller() {

            load_plugin_textdomain( 'vertical-news-scroller', false, basename( dirname( __FILE__ ) ) . '/languages/' );
            add_filter( 'map_meta_cap',  'map_vns_vertical_news_scroller_meta_caps', 10, 4 );
            add_filter( 'user_has_cap', 'vns_vertical_news_admin_cap_list' , 10, 4 );
    }
    
    function vns_vertical_news_admin_cap_list($allcaps, $caps, $args, $user){
        
        
        if ( ! in_array( 'administrator', $user->roles ) ) {
            
            return $allcaps;
        }
        else{
            
            if(!isset($allcaps['vns_vertical_news_scroller_view_news'])){
                
                $allcaps['vns_vertical_news_scroller_view_news']=true;
            }
            
            if(!isset($allcaps['vns_vertical_news_scroller_add_news'])){
                
                $allcaps['vns_vertical_news_scroller_add_news']=true;
            }
            
            if(!isset($allcaps['vns_vertical_news_scroller_edit_news'])){
                
                $allcaps['vns_vertical_news_scroller_edit_news']=true;
            }
            
            if(!isset($allcaps['vns_vertical_news_scroller_delete_news'])){
                
                $allcaps['vns_vertical_news_scroller_delete_news']=true;
            }
            
        }
        
        return $allcaps;
    }
    function map_vns_vertical_news_scroller_meta_caps( array $caps, $cap, $user_id, array $args  ) {
        
        
        if ( ! in_array( $cap, array( 
                                      'vns_vertical_news_scroller_view_news',
                                      'vns_vertical_news_scroller_add_news',
                                      'vns_vertical_news_scroller_edit_news',
                                      'vns_vertical_news_scroller_delete_news'
                                    ), true ) ) {
            
			return $caps;
         }

       

   
        $caps = array();

        switch ( $cap ) {
              
              
                case 'vns_vertical_news_scroller_view_news':
                        $caps[] = 'vns_vertical_news_scroller_view_news';
                        break;
              
                case 'vns_vertical_news_scroller_add_news':
                        $caps[] = 'vns_vertical_news_scroller_add_news';
                        break;
              
                case 'vns_vertical_news_scroller_edit_news':
                        $caps[] = 'vns_vertical_news_scroller_edit_news';
                        break;
              
                case 'vns_vertical_news_scroller_delete_news':
                        $caps[] = 'vns_vertical_news_scroller_delete_news';
                        break;
              
                default:
                        
                        $caps[] = 'do_not_allow';
                        break;
        }

      
     return apply_filters( 'vns_vertical_news_scroller_map_meta_caps', $caps, $cap, $user_id, $args );
}
    

function vns_vertical_news_scroller_add_access_capabilities() {
     
    // Capabilities for all roles.
    $roles = array( 'administrator' );
    foreach ( $roles as $role ) {
        
            $role = get_role( $role );
            if ( empty( $role ) ) {
                    continue;
            }
         
            
          
            
            if(!$role->has_cap( 'vns_vertical_news_scroller_view_news' ) ){
            
                    $role->add_cap( 'vns_vertical_news_scroller_view_news' );
            }
            
            if(!$role->has_cap( 'vns_vertical_news_scroller_add_news' ) ){
            
                    $role->add_cap( 'vns_vertical_news_scroller_add_news' );
            }
            
            if(!$role->has_cap( 'vns_vertical_news_scroller_edit_news' ) ){
            
                    $role->add_cap( 'vns_vertical_news_scroller_edit_news' );
            }
            
            if(!$role->has_cap( 'vns_vertical_news_scroller_delete_news' ) ){
            
                    $role->add_cap( 'vns_vertical_news_scroller_delete_news' );
            }
            
            
         
    }
    
    $user = wp_get_current_user();
    $user->get_role_caps();
    
}
      function news_scroller_load_styles_and_js(){
          
        if (!is_admin()) {                                                       

            wp_register_style( 'news-style', plugins_url('/css/newsscrollcss.css', __FILE__) );
            wp_register_script('newscript',plugins_url('/js/jv.js', __FILE__),array(),'2.0');

        }  
    }   

    function vns_table_column_exists( $table_name, $column_name ) {
       
	global $wpdb;
	$column = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
		DB_NAME, $table_name, $column_name
	) );
	if ( ! empty( $column ) ) {
		return true;
	}
	return false;
        
  } 
  
    function install_newsscroller(){

        global $wpdb;
        $table_name = $wpdb->prefix . "scroll_news";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE " . $table_name . " (
        id int(10) unsigned NOT NULL auto_increment,
        title varchar(1000) NOT NULL,
        content varchar(2000) NOT NULL,
        createdon datetime NOT NULL,
        custom_link varchar(1000) default NULL,
        category_id int(10) unsigned NOT NULL DEFAULT '1',
        PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
         if(vns_table_column_exists($table_name,'category_id')==false){

            $wpdb->query("ALTER TABLE $table_name ADD `category_id` int(10) unsigned NOT NULL DEFAULT '1' ");
            
         }
         
          vns_vertical_news_scroller_add_access_capabilities();


    } 

    function vns_vertical_news_upgrader_process_complete(){
        
        vns_vertical_news_scroller_add_access_capabilities();
    }
    
     function vns_vertical_news_remove_access_capabilities() {
         
            global $wp_roles;

            if ( ! isset( $wp_roles ) ) {
                    $wp_roles = new WP_Roles();
            }

            foreach ( $wp_roles->roles as $role => $details ) {
                    $role = $wp_roles->get_role( $role );
                    if ( empty( $role ) ) {
                            continue;
                    }

                    $role->remove_cap( 'vns_vertical_news_scroller_view_news' );
                    $role->remove_cap( 'vns_vertical_news_scroller_add_news' );
                    $role->remove_cap( 'vns_vertical_news_scroller_edit_news' );
                    $role->remove_cap( 'vns_vertical_news_scroller_delete_news' );

            }

            // Refresh current set of capabilities of the user, to be able to directly use the new caps.
            $user = wp_get_current_user();
            $user->get_role_caps();
    }

    function scrollnews_plugin_menu(){

        $hook_suffix_v_n=add_menu_page(__('Scroll news','vertical-news-scroller'), __("Manage Scrolling News",'vertical-news-scroller'), 'vns_vertical_news_scroller_view_news', 'Scrollnews-settings','managenews');
        add_action( 'load-' . $hook_suffix_v_n , 'vertical_news_scroller_plugin_admin_init' );
    }

    function vertical_news_scroller_plugin_admin_init(){
    
    	$url = plugin_dir_url(__FILE__);
    	wp_enqueue_script('jquery');
    	wp_enqueue_script( 'jquery.validate', $url.'js/jquery.validate.js' );
        wp_enqueue_style( 'admin-css', plugins_url('/css/admin-css.css', __FILE__) );
    
    
    }

    /* Function that registers our widget. */
    function verticalScrollSet() {
        register_widget( 'verticalScroll' );
    }


    function managenews(){

        $action='gridview';
        global $wpdb;


        if(isset($_GET['action']) and $_GET['action']!=''){


            $action=sanitize_text_field(trim($_GET['action']));
        }

        if(strtolower($action)==strtolower('gridview')){ 

            if ( ! current_user_can( 'vns_vertical_news_scroller_view_news' ) ) {
                
                wp_die( __( "Access Denied", "vertical-news-scroller" ) );
            }


        ?> 
        <div id="poststuff">
            <table><tr>
                    <td>
                          <div class="fb-like" data-href="https://www.facebook.com/i13websolution" data-layout="button" data-action="like" data-size="large" data-show-faces="false" data-share="false"></div>
                          <div id="fb-root"></div>
                            <script>(function(d, s, id) {
                              var js, fjs = d.getElementsByTagName(s)[0];
                              if (d.getElementById(id)) return;
                              js = d.createElement(s); js.id = id;
                              js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2&appId=158817690866061&autoLogAppEvents=1';
                              fjs.parentNode.insertBefore(js, fjs);
                            }(document, 'script', 'facebook-jssdk'));</script>
                      </td>
                    <td>
                        <a target="_blank" title="Donate" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=nvgandhi123@gmail.com&amp;item_name=Scroller News&amp;item_number=scroll news support&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=USD&amp;lc=US&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8">
                            <img id="help us for free plugin" height="30" width="90" src="<?php echo plugins_url( 'images/paypaldonate.jpg', __FILE__ );?>" border="0" alt="help us for free plugin" title="help us for free plugin">
                        </a>
                    </td>
                </tr>
            </table>
            <span><h3 style="color: blue;"><a target="_blank" href="https://www.i13websolution.com/product/wordpress-vertical-news-scroller-pro/"><?php echo __('UPGRADE TO PRO VERSION','vertical-news-scroller'); ?></a></h3></span>

            <?php 

                $messages=get_option('scrollnews_messages'); 
                $type='';
                $message='';
                if(isset($messages['type']) and $messages['type']!=""){

                    $type=sanitize_text_field($messages['type']);
                    $message=sanitize_text_field($messages['message']);

                }  


               if(trim($type)=='err'){ echo "<div class='notice notice-error is-dismissible'><p>"; echo $message; echo "</p></div>";}
               else if(trim($type)=='succ'){ echo "<div class='notice notice-success is-dismissible'><p>"; echo $message; echo "</p></div>";}
      

                update_option('scrollnews_messages', array());     
            ?>

            <div id="post-body" class="metabox-holder columns-2">  
                <div id="post-body-content" >
                    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
                    <h1><?php echo __("News",'vertical-news-scroller');?>&nbsp;&nbsp;<a class="button add-new-h2" href="admin.php?page=Scrollnews-settings&action=addedit"><?php echo __("Add New",'vertical-news-scroller');?></a> </h1>
                    <br/>    

                    <form method="POST" action="admin.php?page=Scrollnews-settings&action=deleteselected" id="posts-filter" onkeypress="return event.keyCode != 13;">


                        <div class="alignleft actions">
                            <select name="action_upper" id="action_upper">
                                <option selected="selected" value="-1"><?php echo __("Bulk Actions",'vertical-news-scroller');?></option>
                                <option value="delete"><?php echo __("Delete",'vertical-news-scroller');?></option>
                            </select>
                            <input type="submit" value="<?php echo __("Apply",'vertical-news-scroller');?>" class="button-secondary action" id="deleteselected" name="deleteselected" onclick="return confirmDelete_bulk();">
                        </div>
                        <br/>  
                        <br/>  
                        <br class="clear">
                         <?php
                                $setacrionpage='admin.php?page=Scrollnews-settings';

                                if(isset($_GET['order_by']) and $_GET['order_by']!=""){
                                  $setacrionpage.='&order_by='.sanitize_text_field($_GET['order_by']);   
                                }

                                if(isset($_GET['order_pos']) and $_GET['order_pos']!=""){
                                 $setacrionpage.='&order_pos='.sanitize_text_field($_GET['order_pos']);   
                                }

                                $seval="";
                                if(isset($_GET['search_term']) and $_GET['search_term']!=""){
                                 $seval=trim(sanitize_text_field($_GET['search_term']));   
                                }
                                

                            ?>
                        <?php 

                                $order_by='id';
                                $order_pos="asc";

                                if(isset($_GET['order_by']) and sanitize_sql_orderby($_GET['order_by'])!==false){

                                   $order_by=trim($_GET['order_by']); 
                                }

                                if(isset($_GET['order_pos'])){

                                   $order_pos=trim(sanitize_text_field($_GET['order_pos'])); 
                                }
                                 $search_term='';
                                if(isset($_GET['search_term'])){

                                   $search_term= sanitize_text_field($_GET['search_term']);
                                }
                                
                                $search_term_='';
                                if(isset($_GET['search_term'])){

                                   $search_term_='&search_term='.urlencode(sanitize_text_field($_GET['search_term']));
                                }
                                
                                $query="SELECT * FROM ".$wpdb->prefix."scroll_news";
                                $queryCount="SELECT count(*) FROM ".$wpdb->prefix."scroll_news";
                                if($search_term!=''){
                                  $query.=" where ( id like '%$search_term%' or title like '%$search_term%' ) "; 
                                  $queryCount.=" where ( id like '%$search_term%' or title like '%$search_term%' ) "; 
                                }

                                $order_by=sanitize_text_field(sanitize_sql_orderby($order_by));
                                $order_pos=sanitize_text_field(sanitize_sql_orderby($order_pos));

                                $query.=" order by $order_by $order_pos";
                                $rowsCount=$wpdb->get_var($queryCount);

                          ?>
                          <div style="padding-top:5px;padding-bottom:5px">
                                <b><?php echo __( 'Search','vertical-news-scroller');?> : </b>
                                  <input type="text" value="<?php echo $seval;?>" id="search_term" name="search_term">&nbsp;
                                  <input type='button'  value='<?php echo __( 'Search','vertical-news-scroller');?>' name='searchusrsubmit' class='button-primary' id='searchusrsubmit' onclick="SearchredirectTO();" >&nbsp;
                                  <input type='button'  value='<?php echo __( 'Reset Search','vertical-news-scroller');?>' name='searchreset' class='button-primary' id='searchreset' onclick="ResetSearch();" >
                            </div>  
                            <script type="text/javascript" >
                                jQuery('#search_term').on("keyup", function(e) {
                                       if (e.which == 13) {

                                           SearchredirectTO();
                                       }
                                  });   
                             function SearchredirectTO(){
                               var redirectto='<?php echo $setacrionpage; ?>';
                               var searchval=jQuery('#search_term').val();
                               redirectto=redirectto+'&search_term='+jQuery.trim(encodeURIComponent(searchval));  
                               window.location.href=redirectto;
                             }
                            function ResetSearch(){

                                 var redirectto='<?php echo $setacrionpage; ?>';
                                 window.location.href=redirectto;
                                 exit;
                            }
                            </script>
                        <div id="no-more-tables">
                            <table cellspacing="0" id="gridTbl" class="table-bordered table-striped table-condensed cf " >
                                <thead>
                                    <tr>
                                        <th class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>
                                         <?php if($order_by=="title" and $order_pos=="asc"):?>
                                            <th class="alignLeft"><a href="<?php echo $setacrionpage;?>&order_by=title&order_pos=desc<?php echo $search_term_;?>"><?php echo __('Title','vertical-news-scroller');?><img style="vertical-align:middle" src="<?php echo plugins_url('/images/desc.png', __FILE__); ?>"/></a></th>
                                        <?php else:?>
                                            <?php if($order_by=="title"):?>
                                                <th class="alignLeft"><a href="<?php echo $setacrionpage;?>&order_by=title&order_pos=asc<?php echo $search_term_;?>"><?php echo __('Title','vertical-news-scroller');?><img style="vertical-align:middle" src="<?php echo plugins_url('/images/asc.png', __FILE__); ?>"/></a></th>
                                            <?php else:?>
                                                <th class="alignLeft"><a href="<?php echo $setacrionpage;?>&order_by=title&order_pos=asc<?php echo $search_term_;?>"><?php echo __('Title','vertical-news-scroller');?></a></th>
                                            <?php endif;?>    
                                        <?php endif;?> 
                                        <?php if($order_by=="createdon" and $order_pos=="asc"):?>
                                            <th><a href="<?php echo $setacrionpage;?>&order_by=createdon&order_pos=desc<?php echo $search_term_;?>"><?php echo __('Published On','vertical-news-scroller');?><img style="vertical-align:middle" src="<?php echo plugins_url('/images/desc.png', __FILE__); ?>"/></a></th>
                                        <?php else:?>
                                            <?php if($order_by=="createdon"):?>
                                                <th><a href="<?php echo $setacrionpage;?>&order_by=createdon&order_pos=asc<?php echo $search_term_;?>"><?php echo __('Published On','vertical-news-scroller');?><img style="vertical-align:middle" src="<?php echo plugins_url('/images/asc.png', __FILE__); ?>"/></a></th>
                                            <?php else:?>
                                                <th><a href="<?php echo $setacrionpage;?>&order_by=createdon&order_pos=asc<?php echo $search_term_;?>"><?php echo __('Published On','vertical-news-scroller');?></a></th>
                                            <?php endif;?>    
                                        <?php endif;?>
                                        <th><span><?php echo __("Edit",'vertical-news-scroller');?></span></th>
                                        <th><span><?php echo __("Delete",'vertical-news-scroller');?></span></th>
                                    </tr> 
                                </thead>

                                <tbody id="the-list">
                                    <?php
                                      
                                        if($rowsCount > 0){

                                            global $wp_rewrite;
                                            $rows_per_page = 10;

                                            $current = (isset($_GET['paged'])) ? intval($_GET['paged']) : 1;
                                            $pagination_args = array(
                                                'base' => @add_query_arg('paged','%#%'),
                                                'format' => '',
                                                'total' => ceil($rowsCount/$rows_per_page),
                                                'current' => $current,
                                                'show_all' => false,
                                                'type' => 'plain',
                                            );


                                            $offset = ($current - 1) * $rows_per_page;

                                            $query.=" limit $offset, $rows_per_page";
                                            $rows = $wpdb->get_results ( $query,ARRAY_A);
                                            $delRecNonce=wp_create_nonce('delete_news');
                                            foreach($rows as $row ) {

                                                 
                                                $id=$row['id'];
                                                $editlink="admin.php?page=Scrollnews-settings&action=addedit&id=$id";
                                                $deletelink="admin.php?page=Scrollnews-settings&action=delete&id=$id&nonce=$delRecNonce";

                                            ?>
                                            <tr valign="top" >
                                                <td class="alignCenter check-column"   data-title="<?php echo __('Select Record','vertical-news-scroller'); ?>" ><input type="checkbox" value="<?php echo $row['id'] ?>" name="news[]"></td>
                                                <td class=""   data-title="<?php echo __('Name','vertical-news-scroller'); ?>" ><strong><?php echo stripslashes_deep($row['title']) ?></strong></td>  
                                                <td class="alignCenter"   data-title="<?php echo __('Published On','vertical-news-scroller'); ?>"><span><?php echo date(get_option('date_format').' '.get_option('time_format'), strtotime($row['createdon'])); ?></span></td>
                                                <td class="alignCenter"   data-title="<?php echo __('Edit','vertical-news-scroller'); ?>"><strong><a href='<?php echo $editlink; ?>' title="<?php echo __('Edit','vertical-news-scroller'); ?>"><?php echo __('Edit','vertical-news-scroller'); ?></a></strong></td>  
                                                <td class="alignCenter"   data-title="<?php echo __('Delete','vertical-news-scroller'); ?>"><strong><a href='<?php echo $deletelink; ?>' onclick="return confirmDelete();"  title="<?php echo __('Delete','vertical-news-scroller'); ?>"><?php echo __('Delete','vertical-news-scroller'); ?></a> </strong></td>  
                                            </tr>

                                            <?php 
                                            } 
                                        }
                                        else{
                                        ?>

                                        <tr valign="top" class="" id="">
                                            <td colspan="5" data-title="<?php echo __('No Record','vertical-news-scroller'); ?>" align="center"><strong><?php echo __("No News Found",'vertical-news-scroller');?></strong></td>  
                                        </tr>
                                        <?php 
                                        } 
                                    ?>      
                                </tbody>
                            </table>
                        </div>
                       <?php
                            if($rowsCount>0){

                                echo "<div class='pagination' style='padding-top:10px'>";
                                echo paginate_links($pagination_args);
                                echo "</div>";
                            }
                        ?>
                        <br/>
                        <div class="alignleft actions">
                            <select name="action" id="action_bottom">
                                <option selected="selected" value="-1"><?php echo __("Bulk Actions",'vertical-news-scroller');?></option>
                                <option value="delete"><?php echo __("Delete",'vertical-news-scroller');?></option>
                            </select>
                            <?php wp_nonce_field('action_news_mass_delete','mass_delete_nonce'); ?>
                            <input type="submit" value="<?php echo __("Apply",'vertical-news-scroller');?>" class="button-secondary action" id="deleteselected" name="deleteselected" onclick="return confirmDelete_bulk();">
                        </div>
                        <br/>
                        <br/>
                        <h3><?php echo __('To print this news scroller either you can use theme widget feature or use below shortcode','vertical-news-scroller'); ?></h3>
                        <h4><?php echo __('JQuery Scroller','vertical-news-scroller'); ?></h4>
                        <textarea style="text-align:left" cols="80" rows="3" onclick="this.focus(); this.select()">[print_vertical_news_scroll s_type="modern" maxitem="5" padding="10" add_link_to_title="1" show_content="1" modern_scroller_delay="5000" modern_speed="1700" height="200" width="100%" direction="up" ]</textarea>
                        <br/>
                        <h4><?php echo __('Marquee Scroller','vertical-news-scroller'); ?></h4>
                        <textarea style="text-align:left" cols="80" rows="3" onclick="this.focus(); this.select()">[print_vertical_news_scroll s_type="classic" maxitem="5" padding="10" add_link_to_title="1" show_content="1" delay="60" height="200" width="100%" scrollamount="1" direction="up" ]</textarea>
                    </form>
                    <script type="text/JavaScript">

                        function  confirmDelete(){
                            var agree=confirm("<?php echo __("Are you sure you want to delete this news ?",'vertical-news-scroller');?>");
                            if (agree)
                                return true ;
                            else
                                return false;
                        }
                        
                        function  confirmDelete_bulk(){
                            var topval=document.getElementById("action_bottom").value;
                            var bottomVal=document.getElementById("action_upper").value;
                       
                            if(topval=='delete' || bottomVal=='delete'){
                                
                            
                                var agree=confirm("<?php echo __('Are you sure you want to delete selected news?','vertical-news-scroller'); ?>");
                                if (agree)
                                    return true ;
                                else
                                    return false;
                            }
                        }
                    </script>


                    <br class="clear">
                </div>
                <div id="postbox-container-1" class="postbox-container"> 
                   

                    <div class="postbox"> 
                        <h3 class="hndle"><span></span><?php echo __('Access All Themes One price','vertical-news-scroller'); ?></h3> 
                        <div class="inside">
                            <center><a href="http://www.elegantthemes.com/affiliates/idevaffiliate.php?id=11715_0_1_10" target="_blank"><img border="0" src="<?php echo plugins_url( 'images/300x250.gif', __FILE__ );?>" width="250" height="250"></a></center>

                            <div style="margin:10px 5px">

                            </div>
                        </div></div>

                     <div class="postbox"> 
                        <h3 class="hndle"><span></span><?php echo __('Google For Business Coupon','vertical-news-scroller');?></h3> 
                            <div class="inside">
                                <center><a href="https://goo.gl/OJBuHT" target="_blank">
                                        <img src="<?php echo plugins_url( 'images/g-suite-promo-code-4.png', __FILE__ );?>" width="250" height="250" border="0">
                                    </a></center>
                                <div style="margin:10px 5px">
                                </div>
                            </div>

                        </div>
                </div>

            </div>  
        </div>  

        <?php 
        }   
        else if(strtolower($action)==strtolower('addedit')){
        ?>
        <br/>

        <span><h3 style="color: blue;"><a target="_blank" href="https://www.i13websolution.com/product/wordpress-vertical-news-scroller-pro/"><?php echo __('UPGRADE TO PRO VERSION','vertical-news-scroller'); ?></a></h3></span>
        <?php        
            if(isset($_POST['btnsave'])){

                 if(!check_admin_referer( 'action_news_add_edit','add_edit_nonce' )){
                
                        wp_die('Security check fail'); 
                   }
                   
                //edit save
                if(isset($_POST['newsid'])){

                    //add new

                    if ( ! current_user_can( 'vns_vertical_news_scroller_edit_news' ) ) {
                
                        $scrollnews_messages=array();
                        $scrollnews_messages['type']='err';
                        $scrollnews_messages['message']=__('Access Denied. Please contact your administrator','vertical-news-scroller');
                        update_option('scrollnews_messages', $scrollnews_messages);
                        $location="admin.php?page=Scrollnews-settings";
                        echo "<script type='text/javascript'> location.href='$location';</script>";
                        exit;


                    } 
                    
                    $title=trim(htmlentities(sanitize_text_field($_POST['newstitle']),ENT_QUOTES));
                    $newsurl=trim(htmlentities(esc_url_raw($_POST['newsurl']),ENT_QUOTES));
                    $contant=trim(strip_tags($_POST['newscont'],'<br><a><b><i><span><h1><h2><h3><h4><h5><h6><hr><p><ul><li>'));
                    $newsId=intval(htmlentities(sanitize_text_field($_POST['newsid']),ENT_QUOTES));

                    $location='admin.php?page=Scrollnews-settings';

                    try{
                        
                        $wpdb->update( 
                                        $wpdb->prefix."scroll_news", 
                                        array( 
                                                'title' => $title,	
                                                'content' => $contant
                                        ), 
                                        array( 'id' => $newsId ), 
                                        array( 
                                                '%s',	
                                                '%s'	
                                        ), 
                                        array( '%d' ) 
                                );
                        
                 
                        $scrollnews_messages=array();
                        $scrollnews_messages['type']='succ';
                        $scrollnews_messages['message']='News updated successfully.';
                        update_option('scrollnews_messages', $scrollnews_messages);


                    }
                    catch(Exception $e){

                        $scrollnews_messages=array();
                        $scrollnews_messages['type']='err';
                        $scrollnews_messages['message']='Error while updating news.';
                        update_option('scrollnews_messages', $scrollnews_messages);
                    }  

                    echo "<script> location.href='$location';</script>";
                }
                else{

                    //add new

                    if ( ! current_user_can( 'vns_vertical_news_scroller_add_news' ) ) {
                
                        
                        $scrollnews_messages=array();
                        $scrollnews_messages['type']='err';
                        $scrollnews_messages['message']=__('Access Denied. Please contact your administrator','vertical-news-scroller');
                        update_option('scrollnews_messages', $scrollnews_messages);
                        $location="admin.php?page=Scrollnews-settings";
                        echo "<script type='text/javascript'> location.href='$location';</script>";
                        exit;
                        
                    } 
                    
                    $title=trim(htmlentities(sanitize_text_field($_POST['newstitle']),ENT_QUOTES));
                    $newsurl=trim(htmlentities(sanitize_text_field($_POST['newsurl']),ENT_QUOTES));
                    $contant=trim(strip_tags($_POST['newscont'],'<br><a><b><i><span><h1><h2><h3><h4><h5><h6><hr><p><ul><li>'));
                    /*
                    $createdOn=@date( 'Y-m-d H:i:s', current_time( 'mysql' ));
                    if(get_option('time_format')=='H:i')
                        $createdOn=date('Y-m-d H:i:s',strtotime(current_time('mysql')));
                    else   
                        $createdOn=date('Y-m-d h:i:s',strtotime(current_time('mysql')));
                     * 
                     */
                    
                    $createdOn=current_time('mysql');

                        
                    $location='admin.php?page=Scrollnews-settings';

                    try{
                        
                        
                        $wpdb->insert( 
                                $wpdb->prefix."scroll_news", 
                                array( 
                                        'title' => $title, 
                                        'content' => $contant, 
                                        'createdon' => $createdOn, 
                                        'custom_link' => $newsurl, 
                                ), 
                                array( 
                                        '%s', 
                                        '%s', 
                                        '%s', 
                                        '%s', 
                                ) 
                        );
                    
                        $scrollnews_messages=array();
                        $scrollnews_messages['type']='succ';
                        $scrollnews_messages['message']=__("New news added successfully.",'vertical-news-scroller');
                        update_option('scrollnews_messages', $scrollnews_messages);


                    }
                    catch(Exception $e){

                        $scrollnews_messages=array();
                        $scrollnews_messages['type']='err';
                        $scrollnews_messages['message']=__("Error while adding news.",'vertical-news-scroller');
                        update_option('scrollnews_messages', $scrollnews_messages);
                    }  

                    echo "<script> location.href='$location';</script>";          

                } 

            }
            else{ 

            ?>
            <table><tr>
                    <td>
                          <div class="fb-like" data-href="https://www.facebook.com/i13websolution" data-layout="button" data-action="like" data-size="large" data-show-faces="false" data-share="false"></div>
                          <div id="fb-root"></div>
                            <script>(function(d, s, id) {
                              var js, fjs = d.getElementsByTagName(s)[0];
                              if (d.getElementById(id)) return;
                              js = d.createElement(s); js.id = id;
                              js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2&appId=158817690866061&autoLogAppEvents=1';
                              fjs.parentNode.insertBefore(js, fjs);
                            }(document, 'script', 'facebook-jssdk'));</script>
                      </td>
                    <td>
                        <a target="_blank" title="Donate" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=nvgandhi123@gmail.com&amp;item_name=Scroller News&amp;item_number=scroll news support&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=USD&amp;lc=US&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8">
                            <img id="help us for free plugin"  height="30" width="90" src="<?php echo plugins_url( 'images/paypaldonate.jpg', __FILE__ );?>" border="0" alt="help us for free plugin" title="help us for free plugin">
                        </a>

                    </td>
                </tr></table>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="wrap">
                            <?php if(isset($_GET['id']) and intval($_GET['id'])>0)
                                { 

                                     if ( ! current_user_can( 'vns_vertical_news_scroller_edit_news' ) ) {
                
                                        $scrollnews_messages=array();
                                        $scrollnews_messages['type']='err';
                                        $scrollnews_messages['message']=__('Access Denied. Please contact your administrator','vertical-news-scroller');
                                        update_option('scrollnews_messages', $scrollnews_messages);
                                        $location="admin.php?page=Scrollnews-settings";
                                        echo "<script type='text/javascript'> location.href='$location';</script>";
                                        exit;

                                    } 
                                    
                                    $id= intval(htmlentities(sanitize_text_field($_GET['id']),ENT_QUOTES));
                                    $query="SELECT * FROM ".$wpdb->prefix."scroll_news WHERE id=$id";
                                    $myrow  = $wpdb->get_row($query);

                                    if(is_object($myrow)){

                                        $title=stripslashes_deep($myrow->title);
                                        $newsurl=$myrow->custom_link;
                                        $contant=stripslashes_deep($myrow->content);

                                    }   

                                ?>

                                <h1><?php echo __("Update News",'vertical-news-scroller'); ?></h1>

                                <?php }else{ 

                                    
                                     if ( ! current_user_can( 'vns_vertical_news_scroller_add_news' ) ) {
                
                                        $scrollnews_messages=array();
                                        $scrollnews_messages['type']='err';
                                        $scrollnews_messages['message']=__('Access Denied. Please contact your administrator','vertical-news-scroller');
                                        update_option('scrollnews_messages', $scrollnews_messages);
                                        $location="admin.php?page=Scrollnews-settings";
                                        echo "<script type='text/javascript'> location.href='$location';</script>";
                                        exit;
                                    } 

                                    $title='';
                                    $newsurl='';
                                    $contant='';

                                ?>
                                <h1><?php echo __("Add News",'vertical-news-scroller'); ?> </h1>
                                <?php } ?>

                            <div id="poststuff">
                                <div id="post-body" class="metabox-holder columns-2">
                                    <div id="post-body-content">
                                        <form method="post" action="" id="addnews" name="addnews">

                                            <div class="stuffbox" id="namediv" style="width:100%">
                                                <h3><label for="link_name"><?php echo __("News Title",'vertical-news-scroller'); ?></label></h3>
                                                <div class="inside">
                                                    <input type="text" id="newstitle"  class="required"  size="30" name="newstitle" value="<?php echo $title;?>">
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                    <div style="clear:both"></div>
                                                    <p><?php echo __("This title will scroll",'vertical-news-scroller'); ?></p>
                                                </div>
                                            </div>
                                            <div class="stuffbox" id="namediv" style="width:100%">
                                                <h3><label for="link_name"><?php echo __("News Url",'vertical-news-scroller'); ?></label></h3>
                                                <div class="inside">
                                                    <input type="text" id="newsurl" class="required url2"   size="30" name="newsurl" value="<?php echo $newsurl; ?>">
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                    <div style="clear:both"></div>
                                                    <p><?php echo __('On news title click users will redirect to this url.','vertical-news-scroller'); ?></p>
                                                </div>
                                            </div>
                                            <div class="stuffbox" id="namediv" style="width:100%">
                                                <h3><label for="link_name"><?php echo __("News Content",'vertical-news-scroller'); ?></label></h3>
                                                <div class="inside">
                                                    <textarea cols="90" class="required" style="width:100%" rows="6" id="newscont" name="newscont"><?php echo $contant; ?></textarea>
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                    <div style="clear:both"></div>
                                                    <p><?php echo __('Two three lines summary','vertical-news-scroller'); ?></p>
                                                </div>
                                            </div>
                                            <?php if(isset($_GET['id']) and intval(sanitize_text_field($_GET['id']))>0){ ?> 
                                                <input type="hidden" name="newsid" id="newsid" value="<?php echo intval(sanitize_text_field($_GET['id']));?>">
                                                <?php
                                                } 
                                            ?>
                                                
                                            <?php wp_nonce_field('action_news_add_edit','add_edit_nonce'); ?>    
                                            <input type="submit" name="btnsave" id="btnsave" value="<?php echo __('Save Changes','vertical-news-scroller'); ?>" class="button-primary">&nbsp;&nbsp;<input type="button" name="cancle" id="cancle" value="<?php echo __('Cancel','vertical-news-scroller'); ?>" class="button-primary" onclick="location.href='admin.php?page=Scrollnews-settings'">

                                        </form> 
                                        <script>
                                            jQuery(document).ready(function() {  
                                                    jQuery("#addnews").validate({
                                                            errorClass: "news_error",
                                                            errorPlacement: function(error, element) {
                                                                error.appendTo( element.next().next().next());
                                                            }

                                                    })
                                            });

                                        </script> 

                                    </div>
                                </div>
                            </div>  
                        </div>      
                    </div>
                    <div id="postbox-container-1" class="postbox-container"> 

                        <div class="postbox"> 
                            <h3 class="hndle"><span></span><?php echo __('Access All Themes One price','vertical-news-scroller'); ?></h3> 
                            <div class="inside">
                                <center><a href="http://www.elegantthemes.com/affiliates/idevaffiliate.php?id=11715_0_1_10" target="_blank"><img border="0" src="<?php echo plugins_url( 'images/300x250.gif', __FILE__ );?>" width="250" height="250"></a></center>

                                <div style="margin:10px 5px">
                               
                                </div>
                            </div></div>

                        <div class="postbox"> 
                        <h3 class="hndle"><span></span><?php echo __('Google For Business Coupon','vertical-news-scroller');?></h3> 
                            <div class="inside">
                                <center><a href="https://goo.gl/OJBuHT" target="_blank">
                                        <img src="<?php echo plugins_url( 'images/g-suite-promo-code-4.png', __FILE__ );?>" width="250" height="250" border="0">
                                    </a></center>
                                <div style="margin:10px 5px">
                                </div>
                            </div>

                        </div>


                    </div> 

                </div>         

            </div>
            <?php 
            } 
        }else if(strtolower($action)==strtolower('delete')){

             $retrieved_nonce = '';
            
            if(isset($_GET['nonce']) and $_GET['nonce']!=''){
              
                $retrieved_nonce=sanitize_text_field($_GET['nonce']);
                
            }
            if (!wp_verify_nonce($retrieved_nonce, 'delete_news' ) ){
        
                
                wp_die('Security check fail'); 
            }
                
            if ( ! current_user_can( 'vns_vertical_news_scroller_delete_news' ) ) {

                $scrollnews_messages=array();
                $scrollnews_messages['type']='err';
                $scrollnews_messages['message']=__('Access Denied. Please contact your administrator','vertical-news-scroller');
                update_option('scrollnews_messages', $scrollnews_messages);
                $location="admin.php?page=Scrollnews-settings";
                echo "<script type='text/javascript'> location.href='$location';</script>";
                exit;
            } 
            
            $location='admin.php?page=Scrollnews-settings';
            $deleteId=intval(htmlentities(strip_tags($_GET['id']),ENT_QUOTES));

            try{
                
                    $wpdb->query( 
                               $wpdb->prepare( 
                                       "
                                       DELETE FROM ".$wpdb->prefix."scroll_news
                                        WHERE id = %d",
                                       $deleteId 
                               )
                       );



                $scrollnews_messages=array();
                $scrollnews_messages['type']='succ';
                $scrollnews_messages['message']=__('News deleted successfully.','vertical-news-scroller');
                update_option('scrollnews_messages', $scrollnews_messages);


            }
            catch(Exception $e){

                $scrollnews_messages=array();
                $scrollnews_messages['type']='err';
                $scrollnews_messages['message']=__('Error while deleting news.','vertical-news-scroller');
                update_option('scrollnews_messages', $scrollnews_messages);
            }  

            echo "<script> location.href='$location';</script>";

        }  
        else if(strtolower($action)==strtolower('deleteselected')){

             if(!check_admin_referer('action_news_mass_delete','mass_delete_nonce')){
               
                wp_die('Security check fail'); 
            }
            
            if ( ! current_user_can( 'vns_vertical_news_scroller_delete_news' ) ) {

                $scrollnews_messages=array();
                $scrollnews_messages['type']='err';
                $scrollnews_messages['message']=__('Access Denied. Please contact your administrator','vertical-news-scroller');
                update_option('scrollnews_messages', $scrollnews_messages);
                $location="admin.php?page=Scrollnews-settings";
                echo "<script type='text/javascript'> location.href='$location';</script>";
                exit;
            } 
            
            $location='admin.php?page=Scrollnews-settings'; 
            if(isset($_POST) and isset($_POST['deleteselected']) and  ( sanitize_text_field($_POST['action'])=='delete' or sanitize_text_field($_POST['action_upper'])=='delete')){

                if(sizeof($_POST['news']) >0){

                    $deleteto=$_POST['news'];
                    
                        try{

                            if(is_array($deleteto)){
                                
                                foreach ($deleteto as $deleteId){

                                    $deleteId=intval($deleteId);

                                     $wpdb->query( 
                                                $wpdb->prepare( 
                                                        "
                                                        DELETE FROM ".$wpdb->prefix."scroll_news
                                                         WHERE id = %d",
                                                        $deleteId 
                                                )
                                        );



                                }  
                                
                            }
                            $scrollnews_messages=array();
                            $scrollnews_messages['type']='succ';
                            $scrollnews_messages['message']=__('selected news deleted successfully.','vertical-news-scroller');
                            update_option('scrollnews_messages', $scrollnews_messages);


                        }
                        catch(Exception $e){

                            $scrollnews_messages=array();
                            $scrollnews_messages['type']='err';
                            $scrollnews_messages['message']=__('Error while deleting news.','vertical-news-scroller');
                            update_option('scrollnews_messages', $scrollnews_messages);
                        }  

                        echo "<script> location.href='$location';</script>";exit;


                }
                else{

                    echo "<script> location.href='$location';</script>";   
                }

            }
            else{

                echo "<script> location.href='$location';</script>";      
            }

        }    
    }
    
    function print_verticalScroll_func($atts){

        global $wpdb;
        extract( shortcode_atts( array('maxitem' => 5,), $atts ) );
        extract( shortcode_atts( array('padding' => 5,), $atts ) );
        extract( shortcode_atts( array('add_link_to_title' => 1,), $atts ) );
        extract( shortcode_atts( array('show_content' => 1,), $atts ) );
        extract( shortcode_atts( array('delay' => 60,), $atts ) );
        extract( shortcode_atts( array('modern_scroller_delay' => 5000,), $atts ) );
        extract( shortcode_atts( array('height' => 200,), $atts ) );
        extract( shortcode_atts( array('width' => 220,), $atts ) );
        extract( shortcode_atts( array('scrollamount' => 1,), $atts ) );
        extract( shortcode_atts( array('modern_speed' => 1700,), $atts ) );
        extract( shortcode_atts( array('s_type' => 'modern',), $atts ) );
        extract( shortcode_atts( array('direction' => 'up',), $atts ) );
         
        $maxitem=intval($maxitem);
        $padding=intval($padding);
        $add_link_to_title=intval($add_link_to_title);
        $show_content=intval($show_content);
        $delay=intval($delay);
        $modern_scroller_delay=intval($modern_scroller_delay);
        $height=intval($height);
        $width=sanitize_text_field($width);
        $scrollamount=intval($scrollamount);
        $modern_speed=intval($modern_speed);
        $s_type=sanitize_text_field($s_type);
        $direction=sanitize_text_field($direction);
        
        $randomNum=rand(0,10000);
        if($s_type=='classic'){
            $news_style='classic';  
        }
        else if($s_type=='modern'){
            $news_style='modern';  
        }
        global $wpdb;
        $query="SELECT * FROM ".$wpdb->prefix."scroll_news order by createdon DESC limit $maxitem";
        $rows=$wpdb->get_results($query,'ARRAY_A');
      
        
        wp_enqueue_style('news-style');
        wp_enqueue_script('jquery');
        wp_enqueue_script('newscript');
      
        
        ob_start();
    ?><!-- print_verticalScroll_func -->
      <?php if($news_style=='classic'){ ?>  
        <marquee height='<?php echo $height; ?>' direction="<?php echo strtolower($direction);?>"  onmouseout="this.start()" onmouseover="this.stop()" scrolldelay="<?php echo $delay; ?>" truespeed scrollamount="<?php echo $scrollamount; ?>" direction="up" behavior="scroll" >
         <?php } ?>  
         <div id="news-container_<?php echo $randomNum; ?>" class="news-container" style="max-width: <?php echo $width; ?>;visibility: hidden">
            <ul>
                <?php

                    foreach($rows as $row){
                    ?>
                    <li>
                        <div style="padding:<?php echo $padding; ?>px">
                            <div class="newsscroller_title"><?php if($add_link_to_title){?><a href='<?php echo $row['custom_link']; ?>'><?php } ?><?php echo  stripslashes_deep($row['title']); ; ?><?php if($add_link_to_title){?></a><?php } ?></div>
                            <div style="clear:both"></div>
                            <?php if($show_content){ ?>
                                <div class="scrollercontent">
                                    <?php echo nl2br(stripslashes_deep($row['content'])); ?>
                                </div>
                                <?php } ?>       
                        </div>
                         <div style="clear:both"></div>
                    </li>
                    <?php 
                    }

                ?>
            </ul>
        </div>
       <?php if($news_style=='classic'){ ?>  
            </marquee>
            <?php } ?>
        <?php if($news_style=='modern'){ ?>
            <script type="text/javascript">
        
                 <?php $intval= uniqid('interval_');?>
               
                    var <?php echo $intval;?> = setInterval(function() {

                    if(document.readyState === 'complete') {

                       clearInterval(<?php echo $intval;?>);
                        jQuery("#news-container_<?php echo $randomNum; ?>").css('visibility','visible');
                        jQuery(function(){
                                    jQuery('#news-container_<?php echo $randomNum; ?>').vTicker({ 
                                            speed: <?php echo $modern_speed; ?>,
                                            pause: <?php echo $modern_scroller_delay; ?>,
                                            animation: '',
                                            mousePause: true,
                                            height:<?php echo $height; ?>,
                                            direction:'<?php echo $direction; ?>'
                                    });                                            
                            });

                        
                     }    
                }, 100);

            </script><!-- end print_verticalScroll_func -->
            <?php
            }
            else { ?>
            
             <script type="text/javascript">
        
                 <?php $intval= uniqid('interval_');?>
               
                    var <?php echo $intval;?> = setInterval(function() {

                    if(document.readyState === 'complete') {

                       clearInterval(<?php echo $intval;?>);
                        jQuery("#news-container_<?php echo $randomNum; ?>").css('visibility','visible');
                    

                        
                     }    
                }, 100);

            </script>     
            <?php }
         ?>
        <?php
        $output = ob_get_clean();
        return $output; 
    }

    class verticalScroll extends WP_Widget {

        function __construct() {

            $widget_ops = array('classname' => 'verticalScroll', 'description' => 'Vertical news scroll');
            parent::__construct('verticalScroll', 'Vertical news scroll',$widget_ops);
        }

        function widget( $args, $instance ) {
            global $wpdb;
            
            if(is_array($args)){

                extract( $args );
            }

            wp_enqueue_style('news-style');
            wp_enqueue_script('jquery');
            wp_enqueue_script('newscript');

        
            $title = apply_filters('widget_title', empty( $instance['title'] ) ? 'News Scroll' :$instance['title']);   
            include_once(ABSPATH . WPINC . '/feed.php');
            echo @$before_widget;
            echo @$before_title.$title.$after_title;   
            $maxitem=empty( $instance['maxitem'] ) ? 5 :intval($instance['maxitem']); 
            $padding=empty( $instance['padding'] ) ? 5 :intval($instance['padding']); 
            $add_link_to_title=intval(($instance['add_link_to_title']==null) ? 0 :$instance['add_link_to_title']); 
            $show_content=intval(($instance['show_content']==null) ? 0 :$instance['show_content']); 
            $delay=empty( $instance['delay'] ) ? 5 :intval($instance['delay']); 
            $modern_scroller_delay=empty( $instance['modern_scroller_delay'] ) ? 5000 :intval($instance['modern_scroller_delay']); 
            $height=empty( $instance['height'] ) ? 200 :intval($instance['height']); 
            $scrollamt=empty( $instance['scrollamount'] ) ? 1 :intval($instance['scrollamount']); 
            $modern_speed=empty( $instance['modern_speed'] ) ? 1700 :intval($instance['modern_speed']); 
            $s_type=empty( $instance['s_type'] ) ? 'classic' :sanitize_text_field($instance['s_type']); 
            $direction=empty( $instance['direction'] ) ? 'up' :sanitize_text_field($instance['direction']); 

        
            $randomNum=rand(0,10000);
            $news_style='classic';
            
            $query="SELECT * FROM ".$wpdb->prefix."scroll_news order by createdon desc limit $maxitem";
            $rows=$wpdb->get_results($query,'ARRAY_A');
        ?>


        <?php if($s_type=='classic'){
                $news_style='classic';  
            }
            else if($s_type=='modern'){
                $news_style='modern';  
            }
        ?>
        <?php if($news_style=='classic'){ ?>  
            <marquee height='<?php echo $height; ?>' direction='<?php echo $direction;?>'  onmouseout="this.start()" onmouseover="this.stop()" scrolldelay="<?php echo $delay; ?>" scrollamount="<?php echo $scrollamt; ?>" direction="up" behavior="scroll" >
                <?php } ?>    
                <div id="news-container_<?php echo $randomNum; ?>" class="news-container" style="visibility: hidden">
                <?php if(!$show_content):?>
                 <style>.news-info{display:inline-block;}.news-img{padding-bottom: 20px}</style>
                <?php endif;?>
                <ul>
                <?php

                        foreach($rows as $row){
                        ?>
                        <li>
                            <div style="padding:<?php echo $padding; ?>px">
                                <div class="newsscroller_title"><?php if($add_link_to_title){?><a href='<?php echo $row['custom_link']; ?>'><?php } ?><?php echo  stripslashes_deep($row['title']) ; ?><?php if($add_link_to_title){?></a><?php } ?></div>
                                <div style="clear:both"></div>
                                <?php if($show_content){ ?>
                                    <div class="scrollercontent">
                                        <?php echo nl2br(stripslashes_deep($row['content'])); ?>
                                    </div>
                                    <?php } ?>       
                            </div>
                             <div style="clear:both"></div>
                        </li>
                        <?php 
                        }

                    ?>
                </ul>
            </div>
            <?php if($news_style=='classic'){ ?>  
            </marquee>
            <?php } ?>
        <?php if($news_style=='modern'){ ?>
            <script type="text/javascript">
        
             <?php $intval= uniqid('interval_');?>
               
                    var <?php echo $intval;?> = setInterval(function() {

                    if(document.readyState === 'complete') {

                       clearInterval(<?php echo $intval;?>);
                        jQuery("#news-container_<?php echo $randomNum; ?>").css('visibility','visible');
                        jQuery(function(){
                                jQuery('#news-container_<?php echo $randomNum; ?>').vTicker({ 
                                        speed: <?php echo $modern_speed; ?>,
                                        pause: <?php echo $modern_scroller_delay; ?>,
                                        animation: '',
                                        mousePause: true,
                                        height:<?php echo $height; ?>,
                                        direction:'<?php echo $direction;?>'
                                });                                            
                        });

                        }    
                }, 100);
            </script>
            <?php
            }
            else { ?>
                
             <script type="text/javascript">
        
             <?php $intval= uniqid('interval_');?>
               
                    var <?php echo $intval;?> = setInterval(function() {

                    if(document.readyState === 'complete') {

                       clearInterval(<?php echo $intval;?>);
                        jQuery("#news-container_<?php echo $randomNum; ?>").css('visibility','visible');
                       

                        }    
                }, 100);
            </script>
           <?php 
            }

            echo $after_widget; 
        }



        function update( $new_instance, $old_instance ) {


            $instance = $old_instance;
            $instance['title'] = sanitize_text_field($new_instance['title']);
            $instance['add_link_to_title'] = intval($new_instance['add_link_to_title']);
            $instance['maxitem'] = intval($new_instance['maxitem']);
            $instance['padding'] = intval($new_instance['padding']);
            $instance['show_content'] = intval($new_instance['show_content']);
            $instance['delay'] = intval($new_instance['delay']);
            $instance['scrollamount'] = intval($new_instance['scrollamount']);
            $instance['height'] = intval($new_instance['height']);
            $instance['s_type'] = sanitize_text_field($new_instance['s_type']);
            $instance['modern_scroller_delay'] = sanitize_text_field($new_instance['modern_scroller_delay']);
            $instance['modern_speed'] = intval($new_instance['modern_speed']);
            $instance['direction'] = sanitize_text_field($new_instance['direction']);
            return $instance;


        }
        function form( $instance ) {

            //Defaults
            $instance = wp_parse_args( (array) $instance, array('s_type'=>'classic','title' => 'News','maxitem' => 5,'padding' => 5,'show_content' => 1,'delay'=>5,'scrollamount'=>1,'add_link_to_title'=>1,'height'=>200,'modern_scroller_delay'=>5000,'modern_speed'=>1700,'direction'=>'up'));
            $scroller_type=$instance['s_type'];
            $direction=$instance['direction'];

            $randomNum=rand(0,10000);
        ?>
        <?php

            global $wpdb;
     
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('s_type'); ?>"><b><?php echo __('News Scroller Type:','vertical-news-scroller'); ?></b></label><br/>
            <input <?php if($scroller_type=='modern'){?>checked="checked" <?php } ?> type="radio" name="<?php echo $this->get_field_name('s_type');?>" onchange="chnageParam(this);" id="s_type_modern" value="modern"> <?php echo __('Modern','vertical-news-scroller'); ?>
            <input <?php if($scroller_type=='classic'){?>checked="checked" <?php } ?> type="radio" name="<?php echo $this->get_field_name('s_type');?>" onchange="chnageParam(this);"  id="s_type_classic" value="classic"> <?php echo __('Classic','vertical-news-scroller'); ?>
        </p>
        

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><b><?php echo __('Title:','vertical-news-scroller'); ?></b></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
        </p>
        <p>
            <input id="<?php echo $this->get_field_id('add_link_to_title'); ?>" name="<?php echo $this->get_field_name('add_link_to_title'); ?>"
                type="checkbox" <?php checked($instance['add_link_to_title'], 1); ?> value="1" />
            <label for="<?php echo $this->get_field_id('add_link_to_title'); ?>"><b><?php echo __('Add link to news title:','vertical-news-scroller'); ?></b></label>
        </p>
        <p><label for="<?php echo $this->get_field_id('maxitem'); ?>"><b><?php echo __('Max item from news:','vertical-news-scroller'); ?></b></label>
            <input class="widefat" id="<?php echo $this->get_field_id('maxitem'); ?>" name="<?php echo $this->get_field_name('maxitem'); ?>"
                type="text" value="<?php echo $instance['maxitem']; ?>" />
        </p>

        <p><label for="<?php echo $this->get_field_id('height'); ?>"><b><?php echo __('Height of scroller:','vertical-news-scroller'); ?></b></label>
            <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $instance['height']; ?>" />px
        </p>

        <p><label for="<?php echo $this->get_field_id('padding'); ?>"><b><?php echo __('Padding:','vertical-news-scroller'); ?></b></label>
            <input class="widefat" id="<?php echo $this->get_field_id('padding'); ?>" name="<?php echo $this->get_field_name('padding'); ?>" type="text" value="<?php echo $instance['padding']; ?>" />px
        </p>

        <p>
            <input id="<?php echo $this->get_field_id('show_content'); ?>" name="<?php echo $this->get_field_name('show_content'); ?>"
                type="checkbox" <?php checked($instance['show_content'], 1); ?> value="1" />
            <label for="<?php echo $this->get_field_id('show_content'); ?>"><b><?php echo __('Show news content:','vertical-news-scroller'); ?></b></label>
        </p>

        <p id='classic_delay_<?php echo $this->get_field_id('delay'); ?>' <?php if($scroller_type=='modern'){?>style="display:none" <?php }?>  ><label for="<?php echo $this->get_field_id('delay'); ?>"><b><?php echo __('Delay:','vertical-news-scroller'); ?></b></label>
            <input class="widefat" id="<?php echo $this->get_field_id('delay'); ?>" name="<?php echo $this->get_field_name('delay'); ?>" type="text" value="<?php echo $instance['delay']; ?>" /><?php echo __('Micro Sec','vertical-news-scroller'); ?>
        </p>

        <p id='modern_delay_<?php echo $this->get_field_id('modern_scroller_delay'); ?>' <?php if($scroller_type=='classic'){?>style="display:none" <?php }?>  ><label for="<?php echo $this->get_field_id('delay'); ?>"><b><?php echo __('Delay:','vertical-news-scroller'); ?></b></label>
            <input class="widefat" id="<?php echo $this->get_field_id('modern_scroller_delay'); ?>" name="<?php echo $this->get_field_name('modern_scroller_delay'); ?>" type="text" value="<?php echo $instance['modern_scroller_delay']; ?>" />
        </p>

        <p id='modern_speed_<?php echo $this->get_field_id('modern_speed'); ?>' <?php if($scroller_type=='classic'){?>style="display:none" <?php }?>  ><label for="<?php echo $this->get_field_id('modern_speed'); ?>"><b><?php echo __('Speed:','vertical-news-scroller'); ?></b></label>
            <input class="widefat" id="<?php echo $this->get_field_id('modern_speed'); ?>" name="<?php echo $this->get_field_name('modern_speed'); ?>" type="text" value="<?php echo $instance['modern_speed']; ?>" />
        </p>
        <p id='classic_scrollamount_<?php echo $this->get_field_id('scrollamount'); ?>' <?php if($scroller_type=='modern'){?>style="display:none" <?php }?> ><label for="<?php echo $this->get_field_id('scrollamount'); ?>"><b><?php echo __('Scroll Amount:','vertical-news-scroller'); ?></b></label>
            <input class="widefat" id="<?php echo $this->get_field_id('scrollamount'); ?>" name="<?php echo $this->get_field_name('scrollamount'); ?>" type="text" value="<?php echo $instance['scrollamount']; ?>" /><?php echo __('(Ie 1,2,3)','vertical-news-scroller'); ?>
        </p>
         <p>
            <label for="<?php echo $this->get_field_id('direction'); ?>"><b><?php echo __('Direction:','vertical-news-scroller'); ?></b></label><br/>
            <input <?php if($direction=='up'){?>checked="checked" <?php } ?> type="radio" name="<?php echo $this->get_field_name('direction');?>"  id="direction_up" value="up"> <?php echo __('Up','vertical-news-scroller'); ?>
            <input <?php if($direction=='down'){?>checked="checked" <?php } ?> type="radio" name="<?php echo $this->get_field_name('direction');?>"  id="direction_down" value="down"> <?php echo __('Down','vertical-news-scroller'); ?>
        </p>
        <script>
            function chnageParam(newstype){
                
                if(newstype.value=='classic'){
                    
                    jQuery("[id$=-delay]").show();      
                    jQuery("[id$=-scrollamount]").show();      

                    jQuery("[id$=modern_scroller_delay]").hide();      
                    jQuery("[id$=modern_speed]").hide();      



                }
                else{

                    jQuery("[id$=modern_scroller_delay]").show();      
                    jQuery("[id$=modern_speed]").show();      
                    jQuery("[id$=-delay]").hide();      
                    jQuery("[id$=-scrollamount]").hide();      


                } 
            }
        </script>

        <?php
        } // function form
    } // widget class

    function vnsp_remove_extra_p_tags($content){

        if(strpos($content, 'print_verticalScroll_func')!==false){
        
            
            $pattern = "/<!-- print_verticalScroll_func -->(.*)<!-- end print_verticalScroll_func -->/Uis"; 
            $content = preg_replace_callback($pattern, function($matches) {


               $altered = str_replace("<p>","",$matches[1]);
               $altered = str_replace("</p>","",$altered);
              
                $altered=str_replace("&#038;","&",$altered);
                $altered=str_replace("&#8221;",'"',$altered);
              

              return @str_replace($matches[1], $altered, $matches[0]);
            }, $content);

              
            
        }
        
        $content = str_replace("<p><!-- print_verticalScroll_func -->","<!-- print_verticalScroll_func -->",$content);
        $content = str_replace("<!-- end print_verticalScroll_func --></p>","<!-- end print_verticalScroll_func -->",$content);
        
        
        return $content;
  }

  add_filter('widget_text_content', 'vnsp_remove_extra_p_tags', 999);
  add_filter('the_content', 'vnsp_remove_extra_p_tags', 999);

?>