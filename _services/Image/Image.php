<?php
	require_once($GLOBALS['config']['root'].'_services/Image/classes/exifReader.inc');

	class Image extends Service implements IService {
	
		private $formats = array('gif', 'jpg', 'png');
		
		/**
		 * @var Filehandler
		 */
		private $fh = null;
		
		function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Image/Image.config.php');
            $this->fh = $this->sp->ref('Filehandler');
            /*if(!file_exists($GLOBALS['config']['root'].'/'.$this->config['cache_folder'])){
            	mkdir($GLOBALS['config']['root'].'/'.$this->config['cache_folder']);
            }*/
        }
        
        public function view($args){
        	$this->debugVar('sdf');
			return '';
		}
		
	    public function run($args){ 
	    	if(isset($args['src']) && isset($args['action']) && $args['action'] == 'clear_cache'){
	    		$this->clearCache($args['src']);
	    	}
	    	
	    	return true; 
	   	}
		
        public function admin($args){ return ''; }
        
        public function data($args){ 
        	if(isset($args['path']) && $args['path'] != '' && file_exists($this->fh->getPath($args['path']))){

				$cacheName = str_replace('/', '_', $args['path']);
				$cacheName = explode('.', $cacheName);
				
				$cacheFolder = $this->config['cache_folder'].'/'.$cacheName[0].'_'.$cacheName[1].'/'; 
				$cacheName = $cacheFolder.((isset($args['width']))?$args['width']:'').'x'.((isset($args['height']))?$args['height']:'').'_'.((isset($args['mode']))?$args['mode']:'').'.'.$cacheName[1];
				
				if(!file_exists($this->fh->getPath($cacheName))){					
					
					//if(!file_exists($GLOBALS['config']['root'].'/'.$cacheFolder)) mkdir($GLOBALS['config']['root'].'/'.$cacheFolder, 0777, true);	
					$info = getimagesize($this->fh->getPath($args['path']));
					header('Content-type: '.$info['mime']);
					if(isset($args['width']) && isset($args['height']) && ($args['width'] < $info[0] || $args['height'] < $info[1])){
						$image = $this->getImage($this->fh->getPath($args['path']));
						
						if(isset($args['mode']) && $args['mode'] == 'cropscale'){
							$image = $this->resizeCropImage($image, $args['width'], $args['height'], isset($args['upscale']));
						} else {
							$image = $this->resizeImage($image, $args['width'], $args['height'], isset($args['upscale']));
						}
						
						if(!file_exists($this->fh->getPath($cacheName)))  fclose($this->fh->openFile($cacheName, 'w'));
						$this->compressSaveImage($image, $this->formats[$info[2]-1], $this->fh->getPath($cacheName));
						imagedestroy($image);
						
						$image = $this->fh->openFile($this->fh->getPath($cacheName), 'r');
						echo fread($image, filesize($this->fh->getPath($cacheName)));
						fclose($image);
					} else {
						$image = $this->fh->openFile($args['path'], 'r');
						echo fread($image, filesize($this->fh->getPath($args['path'])));
						fclose($image);
					}
				} else {
					$image = $this->fh->openFile($cacheName, 'r');
					$info = getimagesize($this->fh->getPath($cacheName));
					header('Content-type: '.$info['mime']);
					echo fread($image, filesize($this->fh->getPath($cacheName)));
					fclose($image);
				}
			}
			return '';
        }
		
		public function setup(){
        	
        }
        
		/**
		 * Returns the reference to the gd library image object
		 * @return resource
		 */
		private function getImage($src){
			if(is_readable($src)){
				$info = getimagesize($src);
				$image;
				switch($info[2]){
					case 1:$image=imagecreatefromgif($src);break;
					case 2:$image=imagecreatefromjpeg($src);break;
					case 3:$image=imagecreatefrompng($src);imageAlphaBlending($image, false);imageSaveAlpha($image, true);break;
					default :$image=false;break;
				}
				return $image;
			}
			return false;
		}
		
		/**
		 * Returns the reference to the resized gd image object
		 * @return resource
		 */
		private function resizeImage($image, $width, $height, $upscale = false){	
			$w = imagesx($image);
			$h = imagesy($image);
			$tw = $width;
			$th = $height;
			if(!$upscale){
				if($w < $tw) $width = $w;
				if($h < $th) $height = $h;
			}
			
			if($w != $width || $h != $height){
			
				if($w*$height > $h*$width) {
					$new_width = $width;
					$percent = $new_width*100/$w;
					$new_height = round($percent*$h/100);
				} else if($w*$height < $h*$width) {
					$new_height = $height;
					$percent = $new_height*100/$h;
					$new_width = round($percent*$w/100);
				} else{
					$new_width = $width;
					$new_height = $height;
				}
				
				$new = imagecreatetruecolor($new_width,$new_height);
				imageAlphaBlending($new, false);
				imageSaveAlpha($new, true);
				imagecopyresampled($new, $image, 0, 0, 0, 0, $new_width, $new_height, $w, $h);
				
				return $this->unsharpMask($new, 40, 1, 0);
			} else {
				return $image;
			}
		}
		
		/**
		 * Returns the reference to the resized and cropped gd image object
		 * @return resource
		 */
		private function resizeCropImage($image, $width, $height, $upscale = false){	
			$w = imagesx($image);
			$h = imagesy($image);
			$tw = $width;
			$th = $height;
			if(!$upscale){
				if($w < $tw) $width = $w;
				if($h < $th) $height = $h;
			}
			
			if($w != $width || $h != $height){
			
				if($w*$height < $h*$width) {
					$new_width = $width;
					$percent = $new_width*100/$w;
					$new_height = round($percent*$h/100);
					$srcX = 0;
					$srcY = round(($new_height-$height)*100/(2*$percent));
				} else if($w*$height > $h*$width) {
					$new_height = $height;
					$percent = $new_height*100/$h;
					$new_width = round($percent*$w/100);
					$srcX = round(($new_width-$width)*100/(2*$percent));
					$srcY = 0;
				} else{
					$new_width = $width;
					$new_height = $height;
					$srcX = 0;
					$srcY = 0;
				}
				
				$new = imagecreatetruecolor($tw,$th);
				imageAlphaBlending($new, false);
				imageSaveAlpha($new, true);
				imagecopyresampled($new, $image, 0, 0, $srcX, $srcY, $width, $height, $w-$srcX*2, $h-$srcY*2);
				
				return $this->unsharpMask($new, 40, 1, 0);
			} else {
				return $image;
			}
		}
		
		/**
		 * Returns the reference to the compressed gd image object
		 * @return resource
		 */
		private function compressImage($image, $format){
			$out = false;
			ob_start();
			switch($format){
				case 'gif':imagegif($image);break;
				case 'jpg':imageinterlace($image, 1);imagejpeg($image, null, 90);break;
				case 'png':imagepng($image);break;
				default: return false;break;
			}
			$out = ob_get_contents();
			ob_end_clean();
			return $out;			
		}
		
		/**
		 * Save the reference to the compressed gd image under the given name
		 * @return void
		 */
		private function compressSaveImage($image, $format, $filename){
			switch($format){
				case 'gif':imagegif($image, $filename);break;
				case 'jpg':imageinterlace($image, 1);imagejpeg($image, $filename, 90);break;
				case 'png':imagepng($image, $filename);break;
				default: break;
			}		
		}
		
		/**
		 * Clears the cache for the image with the given url
		 * @param string $imageURL
		 */
		public function clearCache($imageURL){
			$cacheName = str_replace('/', '_', $imageURL);
			$cacheName = explode('.', $cacheName);
			
			$cacheFolder = $this->config['cache_folder'].'/'.$cacheName[0].'_'.$cacheName[1];
			
			$this->rrmdir($cacheFolder);
		}
		
		
		/**
		 * Helperfunction to delete a non-empty folder
		 * @param string $dir
		 */
		private function rrmdir($dir) {
			$this->fh->deleteDirectory($dir);
			/*if (is_dir($dir)) {
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
					}
				}
				reset($objects);
				rmdir($dir);
			}*/
		 } 
		
		/**
		 * This is a unsharp masking filter
		 * 
		 * @author Torstein Hønsi
		 * 
		 * @param $img A gd image reference
		 * @param $amount The amount of sharpening. Typically between 50 - 200
		 * @param $radius The radius of the blur filter. Typically between 0.5 - 1
		 * @param $threshold The masking threshold. Typically between 0 - 5
		 * 
		 * @return resource
		 */
		private function unsharpMask($img, $amount, $radius, $threshold)    { 
		
			////////////////////////////////////////////////////////////////////////////////////////////////  
			////  
			////                  Unsharp Mask for PHP - version 2.1.1  
			////  
			////    Unsharp mask algorithm by Torstein Hønsi 2003-07.  
			////             thoensi_at_netcom_dot_no.  
			////               Please leave this notice.  
			////  
			///////////////////////////////////////////////////////////////////////////////////////////////  
			
		    // Attempt to calibrate the parameters to Photoshop: 
		    if ($amount > 500)    $amount = 500; 
		    $amount = $amount * 0.016; 
		    if ($radius > 50)    $radius = 50; 
		    $radius = $radius * 2; 
		    if ($threshold > 255)    $threshold = 255; 
		     
		    $radius = abs(round($radius));     // Only integers make sense. 
		    if ($radius == 0) { 
		        return $img; imagedestroy($img); break;        } 
		    $w = imagesx($img); $h = imagesy($img); 
		    $imgCanvas = imagecreatetruecolor($w, $h); 
		    $imgBlur = imagecreatetruecolor($w, $h); 
		     
		    imageAlphaBlending($imgCanvas, false);
			imageSaveAlpha($imgCanvas, true);
			
			imageAlphaBlending($imgBlur, false);
			imageSaveAlpha($imgBlur, true);
		    
		
		    // Gaussian blur matrix: 
		    //                         
		    //    1    2    1         
		    //    2    4    2         
		    //    1    2    1         
		    //                         
		    ////////////////////////////////////////////////// 
		         
			//lets get this blurred
		    
		    if (function_exists('imageconvolution')) { // PHP >= 5.1  
		            $matrix = array(  
		            array( 1, 2, 1 ),  
		            array( 2, 4, 2 ),  
		            array( 1, 2, 1 )  
		        );  
		        imagecopy ($imgBlur, $img, 0, 0, 0, 0, $w, $h); 
		        imageconvolution($imgBlur, $matrix, 16, 0);  
		    }  
		    else {  
		
		    // Move copies of the image around one pixel at the time and merge them with weight 
		    // according to the matrix. The same matrix is simply repeated for higher radii. 
		        for ($i = 0; $i < $radius; $i++)    { 
		            imagecopy ($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left 
		            imagecopymerge ($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right 
		            imagecopymerge ($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center 
		            imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h); 
		
		            imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up 
		            imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down 
		        } 
		    } 
		
		    //and now lets do the masking
		    
		    if($threshold>0){ 
		        // Calculate the difference between the blurred pixels and the original 
		        // and set the pixels 
		        for ($x = 0; $x < $w-1; $x++)    { // each row
		            for ($y = 0; $y < $h; $y++)    { // each pixel       
		                $rgbOrig = ImageColorAt($img, $x, $y); 
		                $rOrig = (($rgbOrig >> 16) & 0xFF); 
		                $gOrig = (($rgbOrig >> 8) & 0xFF); 
		                $bOrig = ($rgbOrig & 0xFF); 
		                 
		                $rgbBlur = ImageColorAt($imgBlur, $x, $y); 
		                 
		                $rBlur = (($rgbBlur >> 16) & 0xFF); 
		                $gBlur = (($rgbBlur >> 8) & 0xFF); 
		                $bBlur = ($rgbBlur & 0xFF); 
		                 
		                // When the masked pixels differ less from the original 
		                // than the threshold specifies, they are set to their original value. 
		                $rNew = (abs($rOrig - $rBlur) >= $threshold)  
		                    ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))  
		                    : $rOrig; 
		                $gNew = (abs($gOrig - $gBlur) >= $threshold)  
		                    ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))  
		                    : $gOrig; 
		                $bNew = (abs($bOrig - $bBlur) >= $threshold)  
		                    ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))  
		                    : $bOrig; 
		                 
		                 
		                             
		                if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) { 
							$pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew); 
							ImageSetPixel($img, $x, $y, $pixCol); 
		                } 
		            } 
		        } 
		    } else{ 
		        for ($x = 0; $x < $w; $x++)    { // each row 
		            for ($y = 0; $y < $h; $y++)    { // each pixel 
		                $rgbOrig = ImageColorAt($img, $x, $y); 
		                $aOrig = (($rgbOrig >> 24) & 0xFF); 
		                $rOrig = (($rgbOrig >> 16) & 0xFF); 
		                $gOrig = (($rgbOrig >> 8) & 0xFF); 
		                $bOrig = ($rgbOrig & 0xFF); 
		                 
		                $rgbBlur = ImageColorAt($imgBlur, $x, $y); 
		                
		                $aBlur = (($rgbBlur >> 24) & 0xFF); 
		                $rBlur = (($rgbBlur >> 16) & 0xFF); 
		                $gBlur = (($rgbBlur >> 8) & 0xFF); 
		                $bBlur = ($rgbBlur & 0xFF); 
		                 
						$aNew = ($amount * ($aOrig - $aBlur)) + $aOrig; 
		                    if($aNew>127){$aNew=127;} 
		                    elseif($aNew<0){$aNew=0;} 
		                $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig; 
		                    if($rNew>255){$rNew=255;} 
		                    elseif($rNew<0){$rNew=0;} 
		                $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig; 
		                    if($gNew>255){$gNew=255;} 
		                    elseif($gNew<0){$gNew=0;} 
		                $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig; 
		                    if($bNew>255){$bNew=255;} 
		                    elseif($bNew<0){$bNew=0;} 
		                $rgbNew = ($aNew << 24) + ($rNew << 16) + ($gNew <<8) + $bNew; 
		                    ImageSetPixel($img, $x, $y, $rgbNew); 
		            } 
		        } 
		    } 
		    
		    imagedestroy($imgCanvas); 
		    imagedestroy($imgBlur); 
		     
		    return $img; 
		} 
		
		// ==============  IMAGE FUNCTIONS ===========
	 	/**
         * 
         * returnes an image object
         * @param $id
         */
        public function getImageObject($id){
        	$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'images WHERE i_id="'.mysql_real_escape_string($id).'"');
        	if(is_array($a)){
        		
       			return new TFImage($a['i_id'], $a['name'], $a['path'], $a['hash'], $a['status'], $a['u_date'], $a['u_id'], $a['shot_date']);

        	} else return null;
        }
		/**
		 * processes uploaded images and returnes image array
		 * @param unknown_type $maxUploadSize
		 */
		public function processUploadedImages($validTypes='', $maxUploadSize=-1) {
	  		
			$max_size = ($maxUploadSize == -1) ? $this->config['max_upload_size'] : $maxUploadSize;
	  		$validTypes = ($validTypes == '') ? $this->config['validTypes'] : $validTypes;
	  		
	  		$return_ar = array();
	  		
			$images = $this->sp->ref('UIWidgets')->getUploads(); // .------ get Uploadfiles from UIWidget
	  		
			if($images != array()){
	  			
				foreach($images as $img){
	  				
					if($img['size'] <= $this->config['max_file_size']){ 									// check size	
	  					
						if(preg_match("/\." . $this->config['valid_file_types'] . "$/i", $img['name'])){	// check types
	  						
							if(is_dir($GLOBALS['to_root'].$this->config['upload_dir'])){	
	  							
	  							$exts = split("[/\\.]", strtolower($img['name']));
	  							
	  							$newfilepath = $this->config['upload_folder'].'/'.$this->config['upload_prefix'].str_replace(array('.', ' '), array('', ''), microtime()).'.'.$exts[count($exts)-1];
	  							
	  							if(copy($img['tmp_name'], $GLOBALS['to_root'].$newfilepath)){ // moving to final destinition (copy instead of move_uploaded_file)
			        				
	  								unlink($img['tmp_name']);
			        				
	  								//exifdata
        							$exif = new ImagephpExifReader($GLOBALS['to_root'].$newfilepath);
        							//print_r($exif->getImageInfo());
        							$exif = $exif->getImageInfo();
        							
        							$shot_date = (isset($exif['dateTimeDigitized'])) ?  $exif['dateTimeDigitized'] :'';
        							
        							if($this->newImage($img['name'], $newfilepath, $shot_date)) {//save to database	
			        					$newid = $this->getImageIdByPath($newfilepath);
			        				
			        					$return_ar[] = $this->getImageObject($newid);
        							}
	  							} else {			//copying error
	  								$this->_msg($this->_('Could not copy file.', 'image'));
	  							} 
							
							} else {			//upload dir does not exist
	  							$this->_msg($this->_('Upload dir does not exist.', 'image'));
	  							return array();
	  						} 
	  						
	  					} else {		// wrong type
	  						$this->_msg($this->_('Wrong file type.', 'image'));
	  					} 
	  					
	  				} else {		// wrong file size
	  					$this->_msg($this->_('Wrong file size.', 'image'));
	  				} 
	  			} // --- - end foreach
	  		}
	  		return return_ar;
		}
		
		/**
         * 
         * Writes data of the new image (has to be already at final position) to the Database
         * @param string $name
         * @param string $path
         */
        public function newImage($name, $path, $shot_date='NULL'){
        	if(is_file($GLOBALS['config']['root'].$path)){
        		return $this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'images
        									(name, path, hash, status, u_id, u_date, shot_date) VALUES
        									("'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($name)).'", 
        									 "'.mysql_real_escape_string($path).'", 
        									 "'.md5_file($GLOBALS['config']['root'].$path).'", 
        									 "'.self::STATUS_ONLINE.'", 
        									 "'.$_SESSION['User']['id'].'", 
        									 NOW(), 
        									 "'.mysql_real_escape_string($shot_date).'")');
        	}
        }
        
        /**
         * 
         * Edits Image
         * @param $id
         * @param $title
         * @param $status
         */
		public function editImage($id, $title, $status){
        	return $this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'images
        								SET name="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($title)).'", 
        									status="'.mysql_real_escape_string($status).'" WHERE i_id="'.mysql_real_escape_string($id).'";');
        }
		
		/**
         * 
         * Deletes Image by given Path
         * @param $path
         */
        public function deleteImageByPath($path){
        	if(is_file($GLOBALS['config']['root'].$path)){
        		$this->deleteImageById($this->getImageIdByPath($path));
        	}
        }
        
		/**
         * 
         *  deletes images + meta and image links from database 
         *  unlinks images from filesystem and deletes image cache
         * @param int $id
         */
        public function deleteImageById($id){
        	if($id > -1 && $id != ''){
        		$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'images WHERE i_id = "'.mysql_real_escape_string($id).'"');
        		if(is_array($a)) {
        			$path = $a['path']; 
        			if($this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'images WHERE i_id="'.mysql_real_escape_string($id).'"')){
        				// delete file
        				if(is_file($GLOBALS['config']['root'].$path)) unlink($GLOBALS['config']['root'].$path);
	        			// delete cache
	        			$this->clearCache($path);
	        			// delete meta
	        			return true;
        			} else return false;
        		} else return false;
        	} else return false;
        }
        
		/**
         * returnes id of image by given path
         * @param unknown_type $path
         */
        private function getImageIdByPath($path){
        	$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'images WHERE path = "'.mysql_real_escape_string($path).'"');
        	if(is_array($a) && isset($a['i_id'])) return $a['i_id'];
        	else return -1;
        }
	}
?>