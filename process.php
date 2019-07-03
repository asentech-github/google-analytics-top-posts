<?php 
ini_set("display_errors", 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_FILES['files'])) {

    	$dir = explode("wp-content", __DIR__);
		require_once( $dir[0].'/wp-load.php' );

        $errors = array();

        $uploadyear = date("Y", strtotime('now'));
	    $uploadmonth = date("m", strtotime('now'));
	    
	    //$uploaddir = wp_upload_dir($uploadyear."/".$uploadmonth);
	    $uploaddir = wp_upload_dir();
	    wp_mkdir_p($uploaddir['basedir'].'/ga_top_posts');

        //$path = __DIR__.'/uploads/';
        //$path = $uploaddir["path"];
        $path = $uploaddir['basedir'].'/ga_top_posts';
		$extensions = array('p12');
		
        $all_files = count($_FILES['files']['tmp_name']);

        for ($i = 0; $i < $all_files; $i++) {

			$file_name = $_FILES['files']['name'][$i];
			$file_tmp = $_FILES['files']['tmp_name'][$i];
			$file_type = $_FILES['files']['type'][$i];
			$file_size = $_FILES['files']['size'][$i];
			$temp = explode('.', $_FILES['files']['name'][$i]);
			$file_ext = strtolower(end($temp));

			$file = $path."/".$file_name;

			if (!in_array($file_ext, $extensions)) {

				$errors[] = 'Extension not allowed: ' . $file_name . ' ' . $file_type;

			}

			if (empty($errors)) {

				//move_uploaded_file($file_tmp, $file);
			    $contents = file_get_contents($file_tmp);

			    file_put_contents($file, $contents);
				//echo "file--".$file;

			}

		}

		if ($errors) print_r($errors);
    }

}
