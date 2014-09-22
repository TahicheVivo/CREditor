<?php
// http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php


/**
 * URL Slug
 * @param str $str
 * @return str
 */
function url_slug($str)
{	
	#convert case to lower
	$str = strtolower($str);
	#remove special characters
	$str = preg_replace('/[^a-zA-Z0-9]/i',' ', $str);
	#remove white space characters from both side
	$str = trim($str);
	#remove double or more space repeats between words chunk
	$str = preg_replace('/\s+/', ' ', $str);
	#fill spaces with hyphens
	$str = preg_replace('/\s+/', '-', $str);
	return $str;
}


function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){ 
        // creating a cut resource 
        $cut = imagecreatetruecolor($src_w, $src_h); 

        // copying relevant section from background to the cut resource 
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h); 
        
        // copying relevant section from watermark to the cut resource 
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h); 
        
        // insert cut resource to destination image 
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct); 
    } 


/*
The second argument is the file which the path is relative to. It's optional so you can get the relative path regardless the webpage your currently are. In order to use it with @Young or @Gordon example, because you want to know the relative path to $b from $a, you'll have to use

getRelativePath($b, $a);
*/

function getRelativePath($path, $from = __FILE__ )
{
    $path = explode(DIRECTORY_SEPARATOR, $path);
    $from = explode(DIRECTORY_SEPARATOR, dirname($from.'.'));
    $common = array_intersect_assoc($path, $from);

    $base = array('.');
    if ( $pre_fill = count( array_diff_assoc($from, $common) ) ) {
        $base = array_fill(0, $pre_fill, '..');
    }
    $path = array_merge( $base, array_diff_assoc($path, $common) );
    return implode(DIRECTORY_SEPARATOR, $path);
}


function make_folders( $destination )
{
	if ( empty( $destination ) || !strstr( $destination, "/" ) )
		return false;

	$dir_array = explode( "/", dirname( $destination ) );
	$dir = "";

	foreach ( $dir_array as $part )
	{
		$dir.=$part . '/';
		if ( !is_dir( $dir ) && strlen( $dir ) > 0 )
		{
			mkdir( $dir );
			chmod( $dir, 0777 );
		}
	}

	return $destination;
}
// esta funciona mejor
function createPath($path) {
    if (is_dir($path)) return true;
    $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
    $return = createPath($prev_path);
    return ($return && is_writable($prev_path)) ? mkdir($path) : false;
}


function findFiles($directory, $extensions = array()) {
// findFiles($_SESSION['destacados_img_folder'], $ext=array("png","gif","jpg"));
    function glob_recursive($directory, &$directories = array()) {
        foreach(glob($directory, GLOB_ONLYDIR | GLOB_NOSORT) as $folder) {
            $directories[] = $folder;
            glob_recursive("{$folder}/*", $directories);
        }
    }
    glob_recursive($directory, $directories);
    
    if(!count($directories)) $directories[]=$directory;
    $files = array ();
    foreach($directories as $directory) {
        foreach($extensions as $extension) {
        // original --  foreach(glob("{$directory}/*.{$extension}") as $file) {
        // daba mal la ruta ---> directorio//archivo.jpg
            foreach(glob("{$directory}*.{$extension}") as $file) {
                // separate by extension
                //$files[$extension][] = $file;
                $files[] = $file;
            }
        }
    }
    return $files;
}





function readImagesFolder(){
if(isset($_SESSION['destacados_img_folder']) ){
		$images = glob($_SESSION['destacados_img_folder'] . "{*.jpg,*.gif, *.png}", GLOB_BRACE);
		return $images;
}
return false;
}





class Images
{

	/**
	 * Calls the PHPThumb methods.
	 *
	 * @param string $method
	 * @param mixed $args
	 * @return mixed
	 */
	function __call( $method, $args )
	{
		return call_user_func_array( array( $this->images, $method ), $args );
	}

	/**
	 * Resize an image.
	 *
	 * @param file $from
	 * @param file $to
	 * @param integer $width
	 * @param integer $height
	 * @param boolean $crop
	 * @return boolean
	 */
	public function resizeAndSave( $from, $to, $width, $height, $crop = false )
	{
		require_once dirname( __FILE__ ) . '/PHPThumb/ThumbLib.inc.php';

		$thumb = PhpThumbFactory::create( $from, array(
					'resizeUp' => true,
					'jpegQuality' => 88
						) );

		if ( false === $crop )
		{
			$thumb->resize( $width, $height );
		}
		else
		{
			$thumb->adaptiveResize( $width, $height );
		}

		$fileinfo = pathinfo( $to );
		$fileinfo['extension'] = str_replace( 'jpeg', 'jpg', $fileinfo['extension'] );

		$thumb->save( $to, $fileinfo['extension'] );

		return true;
	}

	/**
	 * Upload and resize an image.
	 *
	 * @param file $from
	 * @param file $to
	 * @param integer $width
	 * @param integer $height
	 * @param boolean $crop
	 * @return boolean
	 */
	public function uploadResizeAndSave( $post_file, $destination, $width, $height, $crop = false )
	{
		$old_name = $post_file['tmp_name'];
		$upload_info = pathinfo( $old_name );
		$new_name = $upload_info['dirname'] . '/' . $post_file['name'];

		move_uploaded_file( $old_name, $new_name );

		self::resizeAndSave( $new_name, $destination, $width, $height, $crop );

		return true;
	}
}