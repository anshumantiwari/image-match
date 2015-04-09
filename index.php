<?php
error_reporting(0);
ini_set('display_errors', 0);
$servername = "your-host";
$username = "db-username";
$password = "db-password";
$dbname="db-name";


$comp= new compareImages();
$dir='files';
$files=$comp->scd($dir);

?>
<html>
<body>
<div id="main">
<article>
<div class="header_wrapper">
<div class="header">
            	<div class="logo"> <a href="http://www.sdclko.com/"><img src="images/logo.jpg" width="82" height="84" /></a></div>
                	<div class="college_name"><img src="images/college_name.jpg" width="500" height="100" /></div><br>
                    	
                        	<div class="clr"></div>
            	</div><div id="welcome">
				<h1>IMAGE GRAPH</h1>
				<h2>Science Exhibition 2014-15</h2></div>
<form action="<?php echo $_SERVER['PHP_SELF'] ;?>" method="post" enctype="multipart/form-data">
    <h1>CAPTURE AN IMAGE</h1>
    <input type="file" name="fileToUpload" id="fileToUpload" accept="image/*" capture="camera"><br>
   <input type="submit" name="submit" class="btn-style" value="SEARCH" />
</form>
</article>


</div>
<style>


#fileToUpload{
font-size:200%;

}
.header_wrapper {
width: 100%;
}
.header {
width: 100%
 ;
margin: 0px auto;
padding: 13px 0px 0px 0px;
}
#welcome{
margin-top:8%;

}
#welcome h1{
margin-left:15%;
font-size:40px;

}
#welcome h2{
margin-left:11%;
font-size:40px;

}


.logo {
width: auto;
height: auto;
float: left;
}

.college_name {
width: auto;
height: auto;
float: left;
padding: 0px 0px 0px 21px;
}

h1.output{
width:100%;
text-align:center;
text-transform:uppercase;

}

#main{margin-left:30%;



}



.btn-style{
margin-top:5%;
	border : solid 3px #d61754;
	border-radius : 3px;
	moz-border-radius : 3px;
	
	font-size : 250%;
	color : #e81935;
	padding : 1px 20px;
	background-color : #050005;

}
</style>
</body>
</html>
<?php
$conn = new mysqli($servername, $username, $password,$dbname);
$diru = "input/"; 
$dirHandle = opendir($diru); 
$values = array();
$ids= array();
$im="";
$d=0;
while ($file = readdir($dirHandle)) { 
   
    if(!is_dir($file)) { 
        unlink ("$diru"."$file"); // unlink() deletes the files
    }
}

closedir($dirHandle); 
$target_dir = "input/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);


// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
}
// Check connection

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
	
      foreach($files as $file){
if($file==='.'||$file==='..'){continue;}

$image2 = $dir.'/'.$file;

$sql="SELECT ID AS id FROM data WHERE file_extension='$image2'";
$result=mysqli_query($conn,$sql);
$ids[$d]= $result->fetch_object()->id;
$im= $comp->compare($target_file,$image2);
$values[$d] = $im;
$d++;



}
$min = array_keys($values, min($values));
$minim=$min[0];
$value= $values[$minim];
$id=$ids[$minim];
$sqli="SELECT caption AS cap FROM data WHERE ID='$id'";
$result1=mysqli_query($conn,$sqli);
echo '<img src="',$target_file.'"/>';
echo "<h1 class=\"output\">.".$result1->fetch_object()->cap.".</h1>"."<h2>Anshuman Tiwari &copy; X-B </h2>";
	
}


class compareImages
{



	private function mimeType($i)
	{
		/*returns array with mime type and if its jpg or png. Returns false if it isn't jpg or png*/
		$mime = getimagesize($i);
		$return = array($mime[0],$mime[1]);
      
		switch ($mime['mime'])
		{
			case 'image/jpeg':
				$return[] = 'jpg';
				return $return;
			case 'image/png':
				$return[] = 'png';
				return $return;
			default:
				return false;
		}
	}  
    
	private function createImage($i)
	{
		/*retuns image resource or false if its not jpg or png*/
		$mime = $this->mimeType($i);
      
		if($mime[2] == 'jpg')
		{
			return imagecreatefromjpeg ($i);
		} 
		else if ($mime[2] == 'png') 
		{
			return imagecreatefrompng ($i);
		} 
		else 
		{
			return false; 
		} 
	}
    
	public function resizeImage($i,$source)
	{
		/*resizes the image to a 8x8 squere and returns as image resource*/
		$mime = $this->mimeType($source);
      
		$t = imagecreatetruecolor(8, 8);
		
		$source = $this->createImage($source);
		
		imagecopyresized($t, $source, 0, 0, 0, 0, 8, 8, $mime[0], $mime[1]);
		
		return $t;
	}
    
    	private function colorMeanValue($i)
	{
		/*returns the mean value of the colors and the list of all pixel's colors*/
		$colorList = array();
		$colorSum = 0;
		for($a = 0;$a<8;$a++)
		{
		
			for($b = 0;$b<8;$b++)
			{
			
				$rgb = imagecolorat($i, $a, $b);
				$colorList[] = $rgb & 0xFF;
				$colorSum += $rgb & 0xFF;
				
			}
			
		}
		
		return array($colorSum/64,$colorList);
	}
    
    	private function bits($colorMean)
	{
		/*returns an array with 1 and zeros. If a color is bigger than the mean value of colors it is 1*/
		$bits = array();
		 
		foreach($colorMean[1] as $color){$bits[]= ($color>=$colorMean[0])?1:0;}
 
		return $bits;
 
	}
	public function scd($dir){
$files=scandir($dir);sort($files);reset($files);return $files;
}



    	public function compare($a,$b)
	{
		/*main function. returns the hammering distance of two images' bit value*/
		$i1 = $this->createImage($a);
		$i2 = $this->createImage($b);
		
		if(!$i1 || !$i2){return false;}
		
		$i1 = $this->resizeImage($i1,$a);
		$i2 = $this->resizeImage($i2,$b);
		
		imagefilter($i1, IMG_FILTER_GRAYSCALE);
		imagefilter($i2, IMG_FILTER_GRAYSCALE);
		
		$colorMean1 = $this->colorMeanValue($i1);
		$colorMean2 = $this->colorMeanValue($i2);
		
		$bits1 = $this->bits($colorMean1);
		$bits2 = $this->bits($colorMean2);
		
		$hammeringDistance = 0;
		
		for($a = 0;$a<64;$a++)
		{
		
			if($bits1[$a] != $bits2[$a])
			{
				$hammeringDistance++;
			}
			
		}
		  
		return $hammeringDistance;
	}
}
?>
