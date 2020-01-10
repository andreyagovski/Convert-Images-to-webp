<?php



add_action( 'wp_ajax_nopriv_convert_webp_images', 'convert_webp_images' );
add_action( 'wp_ajax_convert_webp_images', 'convert_webp_images' );

function convert_webp_images(){

    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $main_folder = isset($_REQUEST['main_folder']) ? $_REQUEST['main_folder'] : date('Y');
    
    $sub_folder = isset($_REQUEST['sub_folder']) ? $_REQUEST['sub_folder'] : '';

    $num_images = isset($_REQUEST['num_images']) ? $_REQUEST['num_images'] : 0;
    $row = 1;
    $images_per_page = 150;

    $end = $images_per_page*$page;
    $start = $end - $images_per_page;

    $wp_dir = wp_upload_dir();
    
    $upload_dir = ($main_folder!="themes") ? $wp_dir['basedir'] . '/' . $main_folder . '/' . $sub_folder : get_template_directory();

    $files = array_diff(scandir($upload_dir), array('.', '..'));

    $results = getDirContents($upload_dir);
    $filter_results = array();

    $extensions = ['jpg', 'jpeg', 'bmp', 'png'];
    
    if(!empty($results)){
        foreach($results as $file){
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if(in_array($ext, $extensions)){
                array_push($filter_results, $file);
            }
        }
    }

    if(!empty($filter_results)){
        foreach($filter_results as $file){
            if ($row > $start && $row <= $end){
                if(convertImagesToWebp($file)){
                    $num_images++;
                }
            }
            $row++;
        }
    }

    $page++;
    
    if($end>$row){
        wp_die(json_encode(array(
            'status' => 'end',
            'num_images' => $num_images
        )));

    }else{

        wp_die(json_encode(array(
            'page' => $page,
            'status' => 'importing',
            'num_images' => $num_images
        )));

    }
    wp_die(json_encode(array(
        'page' => $page,
        'status' => 'end',
        'num_images' => $num_images
    )));
}
