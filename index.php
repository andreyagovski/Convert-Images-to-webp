<?php
/*
Plugin Name: Convert Images to WebP
Description: Plugin for auto-converting images to WebP on upload. It has option to regenerate every attachement that is already uploaded on the website.
Version: 1.0
Author: Andrey Agovski
License: GPL
*/

/* Runs when plugin is activated */
register_activation_hook(__FILE__, 'convert_images_to_webp_install'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'convert_images_to_webp_remove' );

function convert_images_to_webp_install() {
    
}

function convert_images_to_webp_remove() {
    /* Deletes the database field */
    delete_option('convert_images_to_webp_enable_auto_regenerate_webp');
    delete_option('convert_images_to_webp_quality_webp');
}

function convert_images_to_webp_register_settings() {

    add_option( 'convert_images_to_webp_enable_auto_regenerate_webp', 0);
    register_setting( 'convert_images_to_webp_options_group', 'convert_images_to_webp_enable_auto_regenerate_webp', 'convert_images_to_webp_callback' );

    add_option( 'convert_images_to_webp_quality_webp', 80);
    register_setting( 'convert_images_to_webp_options_group', 'convert_images_to_webp_quality_webp', 'convert_images_to_webp_callback' );

}
add_action('admin_init', 'convert_images_to_webp_register_settings' );


function convert_images_to_webp_admin_menu() {
    add_options_page('Convert Images to WebP', 'Convert Images to WebP', 'manage_options',
    'convert-images-webp', 'convert_images_to_webp_html_page');
}
add_action('admin_menu', 'convert_images_to_webp_admin_menu');


function getDirContents($dir, &$results = array()){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path;
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}


function convert_images_to_webp_renderForm(){

    ob_start();
    ?>

        <h1>Plugin for converting images to WebP Format</h1>
        <p>
            # Intro
        </p>
        <p>
            This is a conversion plugin from jpg/png/bmp to the next-gen webp image format that is required for better perfromances on Google's Page speed insights.
        </p>
        <p>
            # How to enable automatic conversion and convert current images on the website
        </p>
        <p>
            1. We need to enable the option to automatically convert images that will be uploaded from the media library
            2. Go to: Settings -> Convert Images to Webp -> and enable the option "Enable auto generating images after upload:"
            3. Adjust the quality of the webp images (1-100), 90 is optimal to have a good quality and conversion.
            4. To convert the previous images that were already uploaded on the website choose the year folders and press "Convert All"
            5. You can even convert images that are in the themes folder, the button under convert all: "Convert the Themes Folder"
        </p>
        <p>
            # How to display the webp images on frontend
        </p>
        <p>
            We currently use the b-lazy js library to lazy load the images on the website. This plugin has a modified version of b-lazy to load a webp images if the browser supports them. To do this you need to:
        </p>
        <p>
            1. Add additional data-src to the <xmp style="display: inline;"><img></xmp> or <xmp style="display: inline;"><div></xmp> called "data-src-webp", to fetch the webp image from an URL you need to use the function: "checkForWebp($url)"<br>
            Example:
        </p>
<xmp><?= '<?php
    $image = get_field(\'hero_image\')[\'sizes\'][\'hero\']; //we get the url from the field hero_image with a "hero" image size:
    $image_webp = checkForWebp($image); //the server will check if the webp image exists here, will return empty string if there is no image converted in webp
?>

<img class="b-lazy" data-src="<?= $image; ?>" data-src-webp="<?= $image_webp; ?>" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" />'; ?>
        </xmp>
        <p>2. That's it!</p>
        
       

        <form method="post" action="options.php">
            <?php settings_fields( 'convert_images_to_webp_options_group' ); ?>

                <p>
                    <h2>Choose the quality of compression to WebP</h2>
                    <div><input type="number" name="convert_images_to_webp_quality_webp" value="<?= get_option('convert_images_to_webp_quality_webp'); ?>" /></div>
                </p>

                <hr>
                    <p>
                        <input type="checkbox" 
                            id="convert_images_to_webp_enable_auto_regenerate_webp" 
                            name="convert_images_to_webp_enable_auto_regenerate_webp" 
                            value="1>" 
                            <?= get_option('convert_images_to_webp_enable_auto_regenerate_webp')==1 ? 'checked' : ''; ?> 
                        />
                        <label for="convert_images_to_webp_enable_auto_regenerate_webp">Enable auto generating images after upload: </label>
                    </p>
                <hr>
           
            <?php submit_button(); ?>
        </form>

        
        <p>
        <hr>
            <h3>Convert all images in the upload dir to WebP by Date?</h3>
            <div>

                <form method="post">
                    <?php
                        //ajax function to convert the images in parts
                        if(isset($_POST['main_folder'])):
                            
                            ?>
                            <div class="import-process notice">
                                <p>Converting: <span id="import-part">0</span> images. <b>Do not close this page!</b></p>
                            </div>
                            <div class="loader"></div>
                            <script>
                                jQuery(document).ready(function(){

                                    var data = {
                                        action: 'convert_webp_images',
                                        page: 1,
                                        num_images: 0,
                                        main_folder: "<?= $_POST['main_folder'] ?>",
                                        sub_folder: "<?= $_POST['sub_folder']; ?>"
                                    };

                                    do_ajax(data);

                                    function do_ajax(data){
                                        jQuery.ajax({
                                            type: 'POST',
                                            data: data,
                                            url: '<?= admin_url('admin-ajax.php'); ?>',
                                            dataType: 'json',
                                            success: function (resp) {

                                                if(resp.status=="end"){
                                                    jQuery('.import-process').html('<p><strong>Conversion complete!</strong><br><span id="import-part">'+ resp.num_images +'</span> images were successfully converted to WebP format.</p>');
                                                    jQuery('.loader').fadeOut(100);
                                                }else{
                                                    jQuery('#import-part').text(resp.num_images);
                                                    var data = {
                                                        action: 'convert_webp_images',
                                                        page: resp.page,
                                                        main_folder: "<?= $_POST['main_folder'] ?>",
                                                        sub_folder: "<?= $_POST['sub_folder']; ?>",
                                                        num_images : resp.num_images
                                                    };
                                                    if(resp!=0){
                                                        do_ajax(data);
                                                    }
                                                }
                                            },
                                        });
                                    }

                                });
                            </script>

                            <?php
                        endif;
                    

                        
                        $upload_dir = wp_get_upload_dir();
                        $directories = glob($upload_dir['basedir'] . '/*' , GLOB_ONLYDIR);
                    ?>
                    <p>
                        <select name="main_folder" id="main_folder">
                            <?php
                            foreach($directories as $dir){
                                $dir_name = str_replace('\\', '/', $dir);
                                $dir_name = explode('/', $dir);
                                $dir_name = $dir_name[count($dir_name)-1];
                                $dir_selected = date('Y')==$dir_name ? 'selected' : '';
                                echo "<option ". $dir_selected .">" . $dir_name . "</option>";
                            }
                            ?>
                        </select>
                        <select name="sub_folder" id="sub_folder">
                            <?php
                            foreach($directories as $dir){
                                $dir_name = str_replace('\\', '/', $dir);
                                $dir_name = explode('/', $dir);
                                $dir_name = $dir_name[count($dir_name)-1];
                                if(date('Y')==$dir_name){
                                    $sub_dirs = glob($dir . '/*' , GLOB_ONLYDIR);
                                    foreach($sub_dirs as $dir){
                                        $dir_name = str_replace('\\', '/', $dir);
                                        $dir_name = explode('/', $dir);
                                        $dir_name = $dir_name[count($dir_name)-1];
                                        $dir_selected = date('Y')==$dir_name ? 'selected' : '';
                                        echo "<option ". $dir_selected .">" . $dir_name . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>

                        <input type="submit" class="button" value="Convert them ALL!" />
                    </p>
                </form>

                <?php

                    if(isset($_POST['themes_folder'])){
                        
                        ?>
                        <div class="import-process notice">
                            <p>Converting: <span style="display: inline-block; font-size: 18px; font-weight: bold;" id="import-part">0</span> images. <b>Do not close this page!</b></p>
                        </div>
                        <div class="loader"></div>
                        <script>
                            jQuery(document).ready(function(){

                                var data = {
                                    action: 'convert_webp_images',
                                    page: 1,
                                    num_images: 0,
                                    main_folder: "themes"
                                };

                                do_ajax(data);

                                function do_ajax(data){
                                    jQuery.ajax({
                                        type: 'POST',
                                        data: data,
                                        url: '<?= admin_url('admin-ajax.php'); ?>',
                                        dataType: 'json',
                                        success: function (resp) {

                                            if(resp.status=="end"){
                                                jQuery('.import-process').html('<p><strong>Conversion complete!</strong><br><span id="import-part">'+ resp.num_images +'</span> images were successfully converted to WebP format.</p>');
                                                jQuery('.loader').fadeOut(100);
                                            }else{
                                                jQuery('#import-part').text(resp.num_images);
                                                var data = {
                                                    action: 'convert_webp_images',
                                                    page: resp.page,
                                                    main_folder: "themes",
                                                    num_images : resp.num_images
                                                };
                                                if(resp!=0){
                                                    do_ajax(data);
                                                }
                                            }
                                        },
                                    });
                                }

                            });
                        </script>

                        <?php
                        
                    } 
                ?>

                <form id="form-convert-themes" method="post">
                    <input type="hidden" name="themes_folder" value="1" />
                    <input type="submit" class="button" value="Convert the Themes Folder" />
                </form>

            </div>
        </p>

    <?php
    print ob_get_clean();
}

//functions for converting/removing images to WebP
include 'functions.php';

//include the hooks for autogenerating images to webp
//activate the hooks if auto generate is enabled
if(get_option('convert_images_to_webp_enable_auto_regenerate_webp')==1){
    include 'hooks.php';
}

include 'ajax.php';





function convert_images_to_webp_html_page() {

    print '<div class="main-plugin-wrap notice">';

        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        convert_images_to_webp_renderForm();

    print '</div>';

?>
    <style>
        .main-plugin-wrap{
            padding: 25px;
            margin: 20px;
            background: white;
        }
        .notice{
            margin-left: 0px !important;
        }
        .notice .notice-green,
        .notice .notice-red{
            color: white;
            padding: 10px 20px;
            font-size: 16px;
        }

        .notice .notice-green{
            background: #489e48;
            border-left: 5px solid #0c800c;
        }

        .notice .notice-red{
            background: #b54d4d;
            border-left: 5px solid #962c2c;
        }

        .import-process p{
            font-size: 16px;
        }
        #import-part{
            display: inline-block;
            font-size: 18px;
            font-weight: bold;
            padding: 5px;
            height: 26px;
            text-align: center;
            color: white;
            background: #46b450;
            border-radius: 3px;
            margin-right: 10px;
        }
        .loader {
            height: 4px;
            width: 100%;
            position: relative;
            overflow: hidden;
            background-color: #ddd;
        }
        .loader:before{
            display: block;
            position: absolute;
            content: "";
            left: -200px;
            width: 200px;
            height: 4px;
            background-color: #489e48;
            animation: loading 2s linear infinite;
        }

        @keyframes loading {
            from {left: -200px; width: 30%;}
            50% {width: 30%;}
            70% {width: 70%;}
            80% { left: 50%;}
            95% {left: 120%;}
            to {left: 100%;}
        }

        #convertAllImages {
            font-size: 12px;
        }

        #form-convert-themes{
            margin-top: 25px;
        }

    </style>

<?php

}

?>