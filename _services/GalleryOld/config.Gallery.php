<?php
	/* -- per page --- */
	$this->config['per_page']['user'] = 10;
	$this->config['per_page']['sidebar'] = 10;
	$this->config['per_page_folder']['user'] = 1;
	
	$this->config['per_page']['admin'] = 40;
	$this->config['per_page']['small'] = 12;
	$this->config['per_page']['addon'] = 6;
	$this->config['per_page']['wysiwyg'] = 9;
	
	
	$this->config['sort']['admin'] = 'date';
	$this->config['sort']['user'] = 'date';
	$this->config['sortDA']['admin'] = 'asc';
	$this->config['sortDA']['user'] = 'desc';
	
	/* --- upload ---*/
	$this->config['max_file_size'] = 3145728; //3MB
	$this->config['max_uploads'] = 10;
	$this->config['valid_file_types'] = '(jpeg|jpg|png|gif)';
    $this->config['upload_dir'] = '_uploads/gallery';
    $this->config['upload_prefix'] = 'upload_';
    
    
    /* --- exif meta items --- */
    $this->config['exif']['flash_id'] = 7; // do not change [hard coded in database]
   // $this->config['exif']['shot_date'] = 6;// do not change [hard coded in database]
    $this->config['exif']['model'] = 5;// do not change [hard coded in database]
    /**
     * 
    Array
(
    [resolutionUnit] => Inches
    [FileName] => ../../_uploads/gallery/upload_0343455001305206401.jpg
    [FileSize] => 589253 bytes
    [FileDateTime] => 12-May-2011 15:20:01
    	[FlashUsed] => 0
    [make] => Canon
    	[model] => Canon EOS 20D
    [xResolution] => 72.00 (72/1) 0
    [yResolution] => 72.00 (72/1) 0
    [software] => Aperture 3.0.3
    [fileModifiedDate] => 2010:07:20 19:13:58
    [exposureTime] =>  0.050 s (1/20) (1/20)
    [fnumber] => f/3.5
    [exposure] => program (auto)
    [isoEquiv] => 800
    [exifVersion] => 0221
    [DateTime] => 2010:07:20 19:13:58
    	[dateTimeDigitized] => 2010:07:20 19:13:58
    [aperture] => 3.50000712636
    [exposureBias] => 0.00 (0/1)
    [meteringMode] => Reserved
    [flashUsed] => No
    [focalLength] => 18.00 (18/1)
    [flashpixVersion] => 0100
    [colorSpace] => 
    [Width] => 1168
    [Height] => 1752
    [focalPlaneYResolution] => 3959.32 (233600/59)
    [customRendered] => Reserved
    [exposureMode] => Auto Exposure
    [whiteBalance] => 0
    [screenCaptureType] => Standard
    [CCDWidth] => 7.49mm
    [IsColor] => 1
    [Process] => 192
    [resolution] => 1168x1752
    [color] => Color
    [jpegProcess] => Baseline
)*/
    
    /*--- settings --- */
    $this->config['shop']['enable_shop'] = false;
    $this->config['shop']['add_uploads_to_shop'] = false;
    $this->config['shop']['meta_group'] = 1; // do not change [hard coded in database]
    $this->config['shop']['meta_visible_id'] = 1; // do not change [hard coded in database]
    
    $this->config['client']['thumb_title_length'] = 30; //length of the image title displayed at view album
    $this->config['client']['thumb_height'] = 200;
    $this->config['client']['thumb_width'] = 200;
    $this->config['client']['render_lightbox'] = false;
    $this->config['client']['calc_first_folder_image'] = false;
    
    $this->config['small']['thumb_title_length'] = 310; //length of the image title displayed at view album
    $this->config['small']['thumb_height'] = 50;
    $this->config['small']['thumb_width'] = 50;
    
    $this->config['addon']['thumb_title_length'] = 310; //length of the image title displayed at view album
    $this->config['addon']['thumb_height'] = 50;
    $this->config['addon']['thumb_width'] = 50;
    
    $this->config['wysiwyg']['thumb_title_length'] = 310; //length of the image title displayed at view album
    $this->config['wysiwyg']['thumb_height'] = 50;
    $this->config['wysiwyg']['thumb_width'] = 50;
    
    $this->config['admin']['upload_just_for_admin'] = true;
    $this->config['admin']['folder_no_image_path'] = 'folder_no_image.png';
    
    $this->config['admin']['can_see_extra_albums'] = array('root');
    
    /* ---- template ---- */
    $this->config['tpl_root'] = '_services/Gallery/';
    $this->config['loc_file'] = $GLOBALS['config']['root'].'/_localization/Gallery.loc.php';
    $this->config['no_image_path'] = '/img/services/gallery/no_image.gif';
    
    //admin templates
    $this->config['tpl']['admin/main'] = $this->config['tpl_root'].'admin/main';
    $this->config['tpl']['admin/sidebar_albums'] = $this->config['tpl_root'].'admin/sidebar_albums';
    $this->config['tpl']['admin/upload'] = $this->config['tpl_root'].'admin/upload';
    $this->config['tpl']['admin/new_album'] = $this->config['tpl_root'].'admin/new_album';
    $this->config['tpl']['admin/new_folder'] = $this->config['tpl_root'].'admin/new_folder';
    $this->config['tpl']['admin/list'] = $this->config['tpl_root'].'admin/list';
    $this->config['tpl']['admin/edit'] = $this->config['tpl_root'].'admin/edit';
    $this->config['tpl']['admin/view_album'] = $this->config['tpl_root'].'admin/view_album';
    $this->config['tpl']['admin/view_folder'] = $this->config['tpl_root'].'admin/view_folder';
    $this->config['tpl']['admin/view_image'] = $this->config['tpl_root'].'admin/view_image';
    $this->config['tpl']['admin/edit_album'] = $this->config['tpl_root'].'admin/edit_album';
    $this->config['tpl']['admin/edit_folder'] = $this->config['tpl_root'].'admin/edit_folder';
    $this->config['tpl']['admin/edit_image'] = $this->config['tpl_root'].'admin/edit_image';

    $this->config['tpl']['small/album'] = $this->config['tpl_root'].'small/album';
    $this->config['tpl']['small/folder'] = $this->config['tpl_root'].'small/folder';
    
    $this->config['tpl']['addon/album'] = $this->config['tpl_root'].'addon/album';
    $this->config['tpl']['addon/folder'] = $this->config['tpl_root'].'addon/folder';
    $this->config['tpl']['addon/upload'] = $this->config['tpl_root'].'addon/upload';
    
    $this->config['tpl']['wysiwyg/album'] = $this->config['tpl_root'].'wysiwyg/album';
    $this->config['tpl']['wysiwyg/folder'] = $this->config['tpl_root'].'wysiwyg/folder';
    $this->config['tpl']['wysiwyg/upload'] = $this->config['tpl_root'].'wysiwyg/upload';
    
    //user templates
    $this->config['tpl']['view_albums'] = $this->config['tpl_root'].'view_albums';
    $this->config['tpl']['view_folder'] = $this->config['tpl_root'].'view_folder';
    $this->config['tpl']['view_album'] = $this->config['tpl_root'].'view_album';
    $this->config['tpl']['view_image'] = $this->config['tpl_root'].'view_image';
    
    $this->config['css_file'] = 'gallery.css';
    $this->config['css_file_admin'] = 'gallery_admin.css';
    $this->config['js_file_admin'] = 'gallery_admin.js';
    
    
    /** ======  new view template ====== **/
    /*
     * note that viewtypes are placed dynamically for better understanding
     */
    $this->config['tpl']['view/'.GalleryFrontView::VIEWTYPE_SPLIT.'/album'] = $this->config['tpl_root'].'view/'.GalleryFrontView::VIEWTYPE_SPLIT.'_album';
    $this->config['tpl']['view/'.GalleryFrontView::VIEWTYPE_BROWSER.'/album'] = $this->config['tpl_root'].'view/'.GalleryFrontView::VIEWTYPE_BROWSER.'_album';
    $this->config['tpl']['view/'.GalleryFrontView::VIEWTYPE_VIEWER.'/album'] = $this->config['tpl_root'].'view/'.GalleryFrontView::VIEWTYPE_VIEWER.'_album';
    
    $this->config['tpl']['view/'.GalleryFrontView::VIEWTYPE_SPLIT.'/folder'] = $this->config['tpl_root'].'view/'.GalleryFrontView::VIEWTYPE_SPLIT.'_folder';
    $this->config['tpl']['view/'.GalleryFrontView::VIEWTYPE_BROWSER.'/folder'] = $this->config['tpl_root'].'view/'.GalleryFrontView::VIEWTYPE_BROWSER.'_folder';
    $this->config['tpl']['view/'.GalleryFrontView::VIEWTYPE_VIEWER.'/folder'] = $this->config['tpl_root'].'view/'.GalleryFrontView::VIEWTYPE_VIEWER.'_folder';
    
?>