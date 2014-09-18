<?php
// http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
/*
The second argument is the file which the path is relative to. It's optional so you can get the relative path regardless the webpage your currently are. In order to use it with @Young or @Gordon example, because you want to know the relative path to $b from $a, you'll have to use

getRelativePath($b, $a);
*/

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

function createPath($path) {
    if (is_dir($path)) return true;
    $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
    $return = createPath($prev_path);
    return ($return && is_writable($prev_path)) ? mkdir($path) : false;
}


function findFiles($directory, $extensions = array()) {

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
            foreach(glob("{$directory}/*.{$extension}") as $file) {
                // separate by extension
                //$files[$extension][] = $file;
                $files[] = $file;
            }
        }
    }
    return $files;
}
/*var_dump(findFiles(SITEROOT."/img/", array (
    "jpg",
    "pdf",
    "png",
    "html"
)));
*/


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