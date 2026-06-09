<?php 

ini_set('display_errors', '0');

require __DIR__ . '/config.php';
	
if(isset($_FILES['imgFile']['name']))
{
	if($_FILES['imgFile']['size'] > UPLOAD_MAX_SIZE) 
	{
		echo json_encode(array(false,'maximum size allowed 3 MB')); return;
	}
	
	$path = UPLOAD_DIR;
	$copy = $path . 'temp/temp.jpeg';
    
    switch($_FILES['imgFile']['type'])
    {
    	case 'image/jpeg': move_uploaded_file($_FILES['imgFile']['tmp_name'], $copy); break;
        case 'image/png' : imagejpeg(imagecreatefromstring(file_get_contents($_FILES['imgFile']['tmp_name'])), $copy); break;
        
        default: echo json_encode(array(false,'supported type: jpeg | png')); exit;
    }
    
    $src  = imagecreatefromjpeg($copy); 
    $size = getimagesize($copy);
    
    if($size[0] >= $size[1])
    {
    	$width = $size[0];
    	
    	if($size[1] <= 0)
    	{
    		echo json_encode(array(false,'picture uploaded is unusable')); return;
    	}
    	
		if($size[1] != 960)
		{
			$width  = ($size[0]*960)/$size[1];
			$canvas = imagecreatetruecolor($width,960);
			
			imagecopyresampled($canvas,$src,0,0,0,0,$width,960,$size[0],$size[1]);
		}
    	else $canvas = $src;
    	
    	$cropped = imagecrop($canvas,['x'=>($width-960)/2,'y'=>0,'width'=>960,'height'=>960]);
    }
    else
    {
    	$height = $size[1];
    	
    	if($size[0] <= 0)
    	{
    		echo json_encode(array(false,'picture uploaded is unusable')); return;
    	}
    	
		if($size[0] != 960)
		{
			$height = ($size[1]*960)/$size[0];
			$canvas = imagecreatetruecolor(960,$height);
			
			imagecopyresampled($canvas,$src,0,0,0,0,960,$height,$size[0],$size[1]);
		}
    	else $canvas = $src;
    	
    	$cropped = imagecrop($canvas,['x'=>0,'y'=>($height-960)/2,'width'=>960,'height'=>960]);
    }
    
	imagejpeg($cropped,$copy);
    $hash = md5_file($copy);
    	
    if(!in_array($hash.'.jpeg',scandir($path.'temp'))
    && !in_array($hash,scandir($path.'imgPool')))
    {
    	rename($copy,$path.'/temp/'.$hash.'.jpeg'); 
    	mkdir ($path. '/tempTiles/'.$hash); 
    	chmod ($path. '/tempTiles/'.$hash, 0755);
    	
    	copy(__DIR__.'/../main.php', $path.'/tempTiles/'.$hash.'/index.php');
    	makeTiles($cropped,320,3, 9, $path.'/tempTiles/'.$hash.'/tiles8/'  );
    	makeTiles($cropped,240,4,16, $path.'/tempTiles/'.$hash.'/tiles15/' );
    }
	else
		unlink($copy);
	
	echo json_encode(array(true,'pictures/tempTiles/'.$hash.'/'));
    
    unset($_FILES['imgFile']);
    exit;
}
	
function makeTiles($img, $size, $square, $max, $folder, $k = 1)
{
	mkdir($folder); 
	chmod($folder, 0755);
	
	for($x=0;$x<$square;++$x)
    for($y=0;$y<$square;++$y)
    {
    	$cropped = imagecrop($img,['x'=>$size*$y,'y'=>$size*$x,'width'=>$size,'height'=>$size]);
    	
        switch($k)
        {
        	case $max: imagejpeg($cropped, $folder.'/'.'star.jpeg'); break;
        	default  : imagejpeg($cropped, $folder.'/'. $k.'.jpeg');
        }
        
        $k++;
    }
	
	copy(__DIR__ . '/../main.php', $folder.'/index.php');
}
	
?>