<?php

/*
 * Slides (V2)
 * 
 * This is the second iteration of TF Slides, with the goal of creating a slider system that scales. Additional
 * slide types should be easy to add, manage and display.
 * 
 */


/**
 * Register Custom Post Type
 */
function create_slider_postype() {

    $args = array(
        'label' => __( 'Slider' ),
        'can_export' => true,
        'public' => true,
        'show_ui' => false,
        'show_in_nav_menus' => false,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array( "slug" => "food-menu" ),
        'supports'=> array('title', 'thumbnail', 'editor', 'custom-fields') ,
    );

	register_post_type( 'tf_slider', $args);

}

add_action( 'init', 'create_slider_postype' );

/**
 * Register Slider Page
 */
function themeforce_slider_addpage() {
    add_submenu_page('themes.php', 'Slider Page Title', 'Slides', 'manage_options', 'tf_slider', 'tf_slider_page');
}

add_action( 'admin_menu', 'themeforce_slider_addpage' );

add_action( 'load-appearance_page_tf_slider', function() {
	
	TF_Upload_Image_Well::enqueue_scripts();
	
} );

// Load jQuery & relevant CSS

/**
 * Load Slider JS Scripts
 */
function themeforce_slider_scripts() {
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_script( 'thickbox' );
    wp_enqueue_script( 'tfslider', TF_URL . '/assets/js/themeforce-slider2.js', array( 'jquery'), TF_VERSION  );
}

add_action( 'admin_print_scripts-appearance_page_tf_slider', 'themeforce_slider_scripts' );

/**
 * Create Slider Page
 */
function tf_slider_page() {

    ?>

    <div class="wrap tf-slides-page">
    <div class="tf-options-page">

    <?php screen_icon(); ?>
    <h2>Slides</h2>

    <form method="post" action="" name="" onsubmit="return checkformf( this );">
    <ul id="tf-slides-list">
    
    	<?php

        // - query -

		$args = array(
		    'post_type' => 'tf_slider',
		    'post_status' => 'publish',
		    'orderby' => 'meta_value_num',
		    'meta_key' => '_tfslider_order',
		    'order' => 'ASC',
		    'posts_per_page' => 99
		);

		$my_query = null;
		$my_query = new WP_query( $args );

        while ( $my_query->have_posts() ) : $my_query->the_post();
            
            // - variables -
			
            $custom = get_post_custom( get_the_ID() );
            $id = ( $my_query->post->ID );

            $order = $custom["_tfslider_order"][0];
            $type = $custom["_tfslider_type"][0];
            if ( !$type ) { $type = 'image'; }

            $header = $custom["tfslider_header"][0];
            $desc = $custom["tfslider_desc"][0];
            $button = $custom["tfslider_button"][0];
            $link = $custom["tfslider_link"][0];

            // - slide types per theme

            switch( TF_THEME ) {

                case 'baseforce':
                $types = array('image','content');
                break;

                default:
                $types = array('image');

            }

            // - image (with fallback) -

            $meta_image = $custom["tfslider_image"][0];
            /*

            Issue
            https://github.com/themeforce/framework/issues/84
            ==================================================

            We have images that are not bound to the post which is an issue
            somewhat for our imagewell as it requires image ID's to pass
            through it. $val is the image ID which is then passed onto to
            the imagewell further below. $meta_image is the old legacy image
            we've been using.

            $val = get_post_thumbnail_id( get_the_ID() );

            if ( !$val && $meta_image ) {
                $attach_id = wp_insert_attachment( $attachment, $meta_image, 37 );
                set_post_thumbnail( $post_id, $attach_id );
                $val = get_post_thumbnail_id( get_the_ID() );
            }

            */

            $post_image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );

            if ( $post_image[0] ) {
                $image = $post_image[0];
            } else {
                $image = $meta_image;
            }

            $thumbnail = wpthumb( $image, 'width=680&height=180&crop=1', false);
            
            // Warning Statement

            if ( $image ) { $imagesize = getimagesize($image); }
            
            if ( $imagesize ) {
                if ( $imagesize[0] < TF_SLIDERWIDTH && $imagesize[1] < TF_SLIDERHEIGHT ) {

                    $warning = '<div class="tf-notice slide-notice">Oops, the <strong>dimensions</strong> of the image below aren\'t quite enough. Please ensure the image is at least <strong>' . TF_SLIDERWIDTH . 'px wide by ' . TF_SLIDERHEIGHT . 'px high.</strong></div>';

                } else {
                    
                    if ($imagesize[0] < TF_SLIDERWIDTH ) {
                    	$warning = '<div class="tf-notice slide-notice">Oops, the <strong>width</strong> of the image below is too short. Please ensure the image is at least <strong>' . TF_SLIDERWIDTH . 'px wide.</strong></div>';
                    }
                    
                    if ($imagesize[1] < TF_SLIDERHEIGHT ) {
                    	$warning = '<div class="tf-notice slide-notice">Oops, the <strong>height</strong> of the image below is too short. Please ensure the image is at least <strong>' . TF_SLIDERHEIGHT . 'px high.</strong></div>';
                    }
                }
            }      
                
             // Display Slide

            ?>
            
            <li id="listItem_<?php echo $id; ?>" class="menu-item-handle slide-item">

                <input type="hidden" name="slider[id][<?php echo $id; ?>]" value="<?php echo $id; ?>" />

                <?php

                /*
                Issue
                https://github.com/themeforce/framework/issues/84
                ==================================================

                echo '<div class="slide-thumbnail">';

                $value['allowed_extensions'] = $value['allowed_extensions'] ? $value['allowed_extensions'] : array( 'jpeg', 'jpg', 'png', 'gif' );
                $drop_text = ! empty( $value['drop_text'] ) ? $value['drop_text'] : __( 'Drop image here', 'themeforce');

                $value['size'] = $value['size'] ? $value['size'] : 'width=440&height=220&crop=1';

                $uploader = new TF_Upload_Image_Well( $value['id'], $val, $value['size'], $drop_text, $value['allowed_extensions'] );
                $uploader->admin_print_styles();
                $uploader->html();

                */

                ?>

                <div class="slide-thumbnail" style="background-image:url(<?php if ( $thumbnail ) {echo $thumbnail;} else { echo TF_URL . '/assets/images/slider-empty.jpg'; } ?>)">

                    <!-- Controls -->

                    <div class="slide-itembar-control">
                            <div class="slide-icon-move"></div>
                            <div class="slide-icon-edit"></div>
                            <div class="slide-icon-delete"></div>
                    </div>

                    <!-- Image Warning -->

                    <?php if ( $warning ) { echo $warning; } ?>

                    <!-- Auto-Updating Preview -->

                    <!-- <input type="button" class="slide-switchimage tf-tiny" value="Switch Image" /> -->

                    <!-- Auto-Updating Preview -->

                    <div class="slide-content-preview">
                            <div class="preview-header"><?php echo $header; ?></div>
                            <div class="preview-desc"><?php echo $desc; ?></div>
                            <div class="preview-button"><?php echo $button; ?></div>
                    </div>

                </div>

                <div class="slide-edit">

                    <div class="clear"></div>

                    <!-- Slide Type Selection -->

                    <div class="slide-type-selection">

                        <div class="label" style="float:left;line-height:33px;font-weight:bold;margin-right:10px;">Slide Design</div>

                        <?php

                        foreach ($types as $item) {

                            if ($item == $type) {
                                $checked = 'checked="checked"';
                            } else {
                                $checked = '';
                            }

                            echo '<input type="radio" name="slide-type-' . $id . '" id="' . $item . '-' . $id . '" value="' . $item . '" ' . $checked . '/>';
                            echo '<label for="' . $item . '-' . $id . '"><img src="' . TF_URL . '/assets/images/slide-type-' . $item . '.png" /></label>';

                        };

                        ?>

                    </div>

                    <div class="clear"></div>

                    <!-- Slide Type : Image -->

                    <div class="slide-edit-image">

                        <input class="slide-content-link" data-meta="link" type="text" placeholder="Slide Link URL" value="<?php echo $link; ?>" />

                    </div>

                    <!-- Slide Type : Content -->

                    <div class="slide-edit-content">

                        <input class="slide-content-header" type="text" data-meta="header" placeholder="Title / Header" value="<?php echo $header; ?>" />
                        <textarea class="slide-content-desc" data-meta="desc" rows="2"><?php echo $desc; ?></textarea>
                        <input class="slide-content-button" data-meta="button" type="text" placeholder="Button Text" value="<?php echo $button; ?>" />
                        <input class="slide-content-link" data-meta="link" type="text" placeholder="Button Link URL" value="<?php echo $link; ?>" />

                    </div>

                </div>

                <!-- Slide Data : Order -->

                <input type="hidden" name="slider[order][<?php echo $id; ?>]" value="<?php $order; ?>" />

        </li>
                         
		<?php endwhile; ?>

    </ul> 
    
    <input type="hidden" name="update_post" value="1"/> 

    </form>

    <div style="clear:both"></div>

    <h3>Create New Slide</h3>
    <div class="tf-settings-wrap">
    	<form class="form-table" method="post" action="" name="" onsubmit="return checkformf( this );">
    	
    	<table>
   			<tr>
			    <?php 
			    // TODO Would be nice to have the 250x100 thumbnail replace the upload button once the image is ready 
			    ?>
			    <th><label>Pick an Image<span class="required">*</span></label></th>
			    <td><?php
			    if ( get_option( $value['id'] ) != "") { 
			    	$val = stripslashes(get_option( $value['id'])  ); 
			    } else { 
			    	$val =  $value['std']; 
			    }
			    
			    $well = new TF_Upload_Image_Well( 'tfslider_image', $val, 'width=350&height=200&crop=1' );
			    $well->html();
			    ?>
			    </td>
			</tr>

		</table>
		</div>

        <input type="hidden" name="new_post" value="1"/>
        <input style="margin-top:25px" type="submit" name="submitpost" class="tf-button tf-major right" value="Create New Slide"/>
    	    
    	</form>
    </div>
</div>
<div style="clear:both"></div>
<?php
}


// Update Slide Order

add_action( 'wp_ajax_tf_slides_update_order', function() {

    $post_id = (int) $_POST['postid'];
    $order_id = (int) $_POST['neworder'];

    update_post_meta( $post_id, '_tfslider_order', $order_id );

} );

// Update Slide Type

add_action( 'wp_ajax_tf_slides_update_type', function() {

    $post_id = $_POST['postid'];
    $type = $_POST['type'];

    update_post_meta( $post_id, '_tfslider_type', $type );

} );

// Update Slide Content

add_action( 'wp_ajax_tf_slides_update_content', function() {

    $post_id = (int) $_POST['postid'];
    $key = 'tfslider_' . $_POST['key'];
    $value = $_POST['value'];

    update_post_meta( $post_id, $key, $value );

    echo 'ok';

} );

// Delete Slide

add_action( 'wp_ajax_tf_slides_delete', function() {

    $post_id = (int) $_POST['postid'];

    wp_delete_post( $post_id, true );

} );

// Save New Slide
// Needs to be updated to Slides V2

function themeforce_slider_catch_submit() {

        // Grab POST Data
    
        if ( isset($_POST['new_post'] ) == '1') {
        $post_title = 'Slide'; // New - Static as one field is always required between post title & content. This field will always be hidden now.

        $imageurl = reset( wp_get_attachment_image_src( $_POST['tfslider_image'], 'large' ) );
        $imageid = (int) $_POST['tfslider_image'];
        
        if ( !$imageurl ) {$imageurl = TF_URL . '/assets/images/slider-empty.jpg'; }
        $link = $_POST['tfslider_link'];
        $button = $_POST['tfslider_button'];

        $new_post = array(
              'ID' => '',
              'post_type' => 'tf_slider',
              'post_author' => $user->ID, 
              'post_content' => 'Slides do not have any WP content, everything is stored in meta.',
              'post_title' => $post_title,
              'post_status' => 'publish',
            );

        // Create New Slide
        $post_id = wp_insert_post( $new_post );
        
        // Update Meta Data
        $order_id = intval( $post_id )*100;
        
        set_post_thumbnail( $post_id, $imageid );
        
        update_post_meta( $post_id, '_tfslider_order', $order_id);
        update_post_meta( $post_id, 'tfslider_image', $imageurl);

        // Exit
        wp_redirect( wp_get_referer() );
        exit;
        }
}

add_action('admin_init', 'themeforce_slider_catch_submit');

// Needs to be updated to Slides V2

function themeforce_slider_display() {

    // Query Custom Post Types  
        $args = array(
            'post_type' => 'tf_slider',
            'post_status' => 'publish',
            'orderby' => 'meta_value_num',
            'meta_key' => '_tfslider_order',
            'order' => 'ASC',
            'posts_per_page' => 99
        );

        // - query -
        $my_query = null;
        $my_query = new WP_query( $args );

        $c = 1;
        
        while ( $my_query->have_posts() ) : $my_query->the_post();
                
            // - variables -
            $custom = get_post_custom( get_the_ID() );
            $id = ( $my_query->post->ID );
            $order = $custom["_tfslider_order"][0];
            $type = $custom["_tfslider_type"][0];

            $header = $custom["tfslider_header"][0];
            $desc = $custom["tfslider_desc"][0];
            $button = $custom["tfslider_button"][0];
            $link = $custom["tfslider_link"][0];

            // - image (with fallback support)
            $meta_image = $custom["tfslider_image"][0];
            $post_image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );

            if ( $post_image[0] ) {
                $image = $post_image[0];
            } else {
                $image = $meta_image;
            }

            // output

            // update mobile bg if not set yet
            if ($c == 1 && get_option( 'tf_mobilebg' ) == '') { 
            	update_option('tf_mobilebg', $image);
            }
            
            $c++;
            
            // **** Theme Specific
            
            if ( TF_THEME == 'baseforce' ) {

                switch($type) {

                    case 'content':

                        echo '<li class="slide-type-content">';
                        $b_image = wpthumb( $image, 'width=560&height=250&crop=1', false);
                        echo '<div class="slide-image" style="background-image:url(' . $b_image . ')"></div>';
                        echo '<div class="slide-content">';
                        echo '<h2>'. $header . '</h2>';
                        echo '<p>'. $desc . '</p>';
                        echo '<a class="slide-button" href="' . $link . '">'. $button . '</a>';
                        echo '</div>';
                        echo '</li>';

                    break;

                    default:

                        echo '<li class="slide-type-image">';
                        $b_image = wpthumb( $image, 'width=940&height=250&crop=1', false);
                        echo '<div class="slide-image" style="background-image:url(' . $b_image . ')"></div>';
                        echo '</li>';

                }

            }

             if ( TF_THEME == 'chowforce' ) {
               
				echo '<li>';
				    if ( $link ) {echo '<a href="' . $link . '">';}
				        $resized_image = wpthumb( $image, 'width=960&height=250&crop=1', false);
				        echo '<div class="slideimage-full" style="background:url(' . $resized_image . ') no-repeat;" alt="' . __('Slide', 'themeforce') . '"></div>';
				    if ( $link ) {echo '</a>';}
				echo '</li>';

              }
                
              if ( TF_THEME == 'pubforce' ) {
                    echo '<li>';
                        if ( $link ) {echo '<a href="' . $link . '">';}
                            $resized_image = wpthumb( $image, 'width=540&height=300&crop=1', false);
                            echo '<div class="slideimage" style="background:url(' . $resized_image . ') no-repeat;" alt="' . __('Slide', 'themeforce') . '"></div>';
                        if ( $link ) {echo '</a>';}
                    echo '</li>';
			  }   
                
              if ( TF_THEME == 'fineforce' )
                {
                    echo '<li>';
                        if ( $link ) {echo '<a href="' . $link . '">';}
                            $resized_image = wpthumb( $image, 'width=1000&height=250&crop=1', false);
                            echo '<div class="slideimage" style="background:url(' . $resized_image . ') no-repeat;" alt="' . __('Slide', 'themeforce') . '"></div>';
                        if ( $link ) {echo '</a>';}
                    echo '</li>';
                }   
                
             // fallback check   
             $emptycheck[] = $image;   
                    
        endwhile;
        
        // **** Theme Specific
        // fallback functions when no slides exist
        // TODO Consider replacing this with a more universal solution
        
        if ( $emptycheck == '' ) {
            
            if ( TF_THEME == 'chowforce' ) {
                echo '<li><div class="slideimage-full" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/defaults/slide1.jpg) no-repeat;" alt="Slide"></li>';
                echo '<li><div class="slidetext"><h3>Yelp Integration</h3><p>Want to show off your Yelp rating? That\'s no problem. If you\'re not in a Yelp country, but use Qype instead, that works too! Just add your API and you\'ll be all set.</p></div><div class="slideimage" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/defaults/slide2.jpg) no-repeat;" alt="Slide"></li>';
                echo '<li><div class="slidetext"><h3>No more PDF Menus</h3><p>With our designs, search engines will recognize your food menus and visitors won\'t have to download any PDF\'s or otherwise.</p></div><div class="slideimage" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/defaults/slide3.jpg) no-repeat;" alt="Slide"></li>';
                echo '<li><div class="slidetext"><h3>Foursquare Integration</h3><p>Display your Foursquare Photos & Tips without any problem. You can do similar things with Gowalla. All you need to do is sign-up for an API Key & enter it (everyone gets one and it takes 2 minutes).</p></div><div class="slideimage" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/defaults/slide4.jpg) no-repeat;" alt="Slide"></li>';
            }           
            
            if ( TF_THEME == 'pubforce' ) {
                echo '<li><div class="slideimage" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/defaults/slide1.jpg) no-repeat;" alt="Slide"></li>';
                echo '<li><div class="slideimage" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/defaults/slide2.jpg) no-repeat;" alt="Slide"></li>';
                echo '<li><div class="slideimage" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/defaults/slide3.jpg) no-repeat;" alt="Slide"></li>';
            }
            
            if ( TF_THEME == 'fineforce' ) {
                echo '<li><div class="slideimage" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/default_food_1.jpg) no-repeat;" alt="Slide"></li>';
                echo '<li><div class="slideimage" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/default_food_2.jpg) no-repeat;" alt="Slide"></li>';
                echo '<li><div class="slideimage" style="background:url(' . get_bloginfo( 'template_url' ) . '/images/default_food_3.jpg) no-repeat;" alt="Slide"></li>';
            }
            
        }

        }
?>