<?php 
error_reporting(0);
ini_set('display_errors', 0);
$servername = "host";
$username = "db-username";
$password = "db-password";
$dbname="db-name";

$conn = new mysqli($servername, $username, $password,$dbname);


?>
<html>
<body>

<form action="<?php echo $_SERVER['PHP_SELF'] ;?>" method="post" enctype="multipart/form-data">
   Title: <input type="text" name="title"><br>
  Caption: <input type="text" name="caption"><br>
   Select image to upload:
    <input type="file" style="width:200px" name="fileToUpload" id="fileToUpload" accept="image/*" capture="camera"><br>
	 
    <input type="submit" value="Upload Image" name="submit">
</form>

</body>
</html>
<?php



$target_dir = "files/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);





if ($uploadOk == 0) {
    echo "";
// if everything is ok, try to upload file
} else {
      if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file) ) {
          $title= $_POST["title"];
          $caption= $_POST["caption"];
		  
	  $sql = "INSERT INTO data (name, caption, file_extension ) VALUES ( '$title' ,  '$caption' , '$target_file')";

	
		if (mysqli_query($conn, $sql)) {
  

      echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded."; } 
   
     else {
        echo "Sorry, there was an error uploading your file.";
    }
}
}


?>
