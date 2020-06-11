<h3>
	<a href="index.php">Home</a> | <a href="?action=create">Add New</a>
</h3>
<hr>
<?php
//-----------------------------------------------------------
if (isset($_GET['action'])){
	switch($_GET['action']){
		case "create":
			create_data();
			break;
		case "read":
			read_data();
			break;
		case "update":
			update_data();
			break;
		case "delete":
			delete_data();
			break;		
		default:			
			echo "<h3>action <i>".$_GET['action']."</i> tidak ada!</h3>";
			read_data();
	}
}else{
	read_data();
}


//-----------------------------------------------------------
function create_data(){
	error_reporting( ~E_NOTICE ); // avoid notice
	require_once 'dbconfig.php';

	if(isset($_POST['btnsave'])){
		$username = $_POST['user_name'];// user name
		$userjob = $_POST['user_job'];// user email
		$imgFile = $_FILES['user_image']['name'];
		$tmp_dir = $_FILES['user_image']['tmp_name'];
		$imgSize = $_FILES['user_image']['size'];


		if(empty($username)){
			$errMSG = "Please Enter Username.";
		}else if(empty($userjob)){
			$errMSG = "Please Enter Your Job Work.";
		}else if(empty($imgFile)){
			$errMSG = "Please Select Image File.";
		}else{
			$upload_dir = 'user_images/'; // upload directory
			$imgExt = strtolower(pathinfo($imgFile,PATHINFO_EXTENSION)); // get image extension
			// valid image extensions
			$valid_extensions = array('jpeg', 'jpg', 'png', 'gif'); // valid extensions
			// rename uploading image
			$userpic = rand(1000,1000000).".".$imgExt;
			// allow valid image file formats
			if(in_array($imgExt, $valid_extensions)){
				// Check file size '5MB'
				if($imgSize < 5000000){
					move_uploaded_file($tmp_dir,$upload_dir.$userpic);
				}else{
					$errMSG = "Sorry, your file is too large.";
				}
			}else{
				$errMSG = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";  
			}
		}
		
		// if no error occured, continue ....
		if(!isset($errMSG)){
			$stmt = $DB_con->prepare('INSERT INTO tbl_users(userName,userProfession,userPic) VALUES(:uname, :ujob, :upic)');
			$stmt->bindParam(':uname',$username);
			$stmt->bindParam(':ujob',$userjob);
			$stmt->bindParam(':upic',$userpic);

			if($stmt->execute()){
				$successMSG = "new record succesfully inserted ...";
				echo '<img src="loading.gif"><br>';
				header("refresh:1;index.php");
			}else{
				$errMSG = "error while inserting....";
			}
		}
	}

	echo $errMSG;
	echo $successMSG;
	?>

	<form method="post" enctype="multipart/form-data" class="form-horizontal">
		<table class="table table-bordered table-responsive">
			<tr>
				<td><label >Username:</label></td>
				<td><input autofocus type="text" name="user_name" value="<?php echo $username; ?>" /></td>
			</tr>
			<tr>
				<td><label >Profession(Job):</label></td>
				<td><input type="text" name="user_job" value="<?php echo $userjob; ?>" /></td>
			</tr>
			<tr>
				<td><label >Photo Profile:</label></td>
				<td><input type="file" name="user_image" accept="image/*" /></td>
			</tr>
			<tr>
				<td colspan="2"><button type="submit" name="btnsave" class="btn btn-default">Save</button>
			</td>
			</tr>
		</table>
	</form>
<?php

}


//-----------------------------------------------------------
function read_data(){
	echo $successMSG;
	echo $errMSG;

	require_once 'dbconfig.php';
	
	$stmt = $DB_con->prepare('SELECT userID, userName, userProfession, userPic FROM tbl_users ORDER BY userID DESC');
	$stmt->execute();

	if($stmt->rowCount() > 0){
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
			extract($row);
			?>
			<img src="user_images/<?php echo $userPic; ?>" class="img-rounded" width="100px" height="100px" />
			<br>
			Username: <?php echo $userName; ?>
			<br>
			Job: <?php echo $userProfession; ?>			
			<br>
			<a href="?action=update&edit_id=<?php echo $userID; ?>">Edit</a> | 
			<a href="?action=delete&delete_id=<?php echo $userID; ?>" onclick="return confirm('sure to delete ?')">Delete</a>
			<hr>
			<?php
		}
	}else{
		echo "No Data Found ...";        
	}
}


//-----------------------------------------------------------
function update_data(){
	error_reporting( ~E_NOTICE );
	require_once 'dbconfig.php';

	if(isset($_GET['edit_id']) && !empty($_GET['edit_id'])){
		$id = $_GET['edit_id'];
		$stmt_edit = $DB_con->prepare('SELECT userName, userProfession, userPic FROM tbl_users WHERE userID =:uid');
		$stmt_edit->execute(array(':uid'=>$id));
		$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
		//extract($edit_row);
		if(!empty(extract($edit_row))){
			extract($edit_row);
		}else{
			$errMSG = "Edit ID not found!";
			header("Location: index.php");
		}
	}else{
		header("Location: index.php");
	}


	//----------------------
	if(isset($_POST['btn_save_updates'])){
		$username = $_POST['user_name'];// user name
		$userjob = $_POST['user_job'];// user email
		$imgFile = $_FILES['user_image']['name'];
		$tmp_dir = $_FILES['user_image']['tmp_name'];
		$imgSize = $_FILES['user_image']['size'];

		if($imgFile){
			$upload_dir = 'user_images/'; // upload directory 
			$imgExt = strtolower(pathinfo($imgFile,PATHINFO_EXTENSION)); // get image extension
			$valid_extensions = array('jpeg', 'jpg', 'png', 'gif'); // valid extensions
			$userpic = rand(1000,1000000).".".$imgExt;
			
			if(in_array($imgExt, $valid_extensions)){
				if($imgSize < 5000000){
					unlink($upload_dir.$edit_row['userPic']);
					move_uploaded_file($tmp_dir,$upload_dir.$userpic);
				}else{
					$errMSG = "Sorry, your file is too large it should be less then 5MB";
				}
			}else{
				$errMSG = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";  
			}
		}else{
			// if no image selected the old image remain as it is.
			$userpic = $edit_row['userPic']; // old image from database
		}


		// if no error occured, continue ....
		if(!isset($errMSG)){
			$stmt = $DB_con->prepare('UPDATE tbl_users 
			   SET userName=:uname, 
			   userProfession=:ujob, 
			   userPic=:upic 
			   WHERE userID=:uid');
			$stmt->bindParam(':uname',$username);
			$stmt->bindParam(':ujob',$userjob);
			$stmt->bindParam(':upic',$userpic);
			$stmt->bindParam(':uid',$id);

			if($stmt->execute()){
				$successMSG = "new record succesfully Updated ...";
				echo '<img src="loading.gif"><br>';
				header("refresh:1;index.php");
			}else{
				$errMSG = "Sorry Data Could Not Updated !";
			}
		}
	}
	echo $errMSG;
	echo $successMSG;
	?>
	<form method="post" enctype="multipart/form-data" class="form-horizontal">
		<table class="table table-bordered table-responsive">
			<tr>
				<td><label >Username:</label></td>
				<td><input type="text" name="user_name" value="<?php echo $userName; ?>" /></td>
			</tr>
			<tr>
				<td><label >Profession(Job):</label></td>
				<td><input type="text" name="user_job" value="<?php echo $userProfession; ?>" /></td>
			</tr>
			<tr>
				<td><label >Photo Profile:</label></td>
				<td>
				<img width="100px" height="100px" src="user_images/<?php echo $userPic; ?>"><br>
				<input type="file" name="user_image" accept="image/*" />
				</td>
			</tr>
			<tr>
				<td colspan="2"><button type="submit" name="btn_save_updates" >Update</button></td>
			</tr>
		</table>
	</form>	
	<?php
}


//-----------------------------------------------------------
function delete_data(){
	require_once 'dbconfig.php';
	if(isset($_GET['delete_id'])){
		// select image from db to delete
		$stmt_select = $DB_con->prepare('SELECT userPic FROM tbl_users WHERE userID =:uid');
		$stmt_select->execute(array(':uid'=>$_GET['delete_id']));
		$imgRow=$stmt_select->fetch(PDO::FETCH_ASSOC);
		unlink("user_images/".$imgRow['userPic']);

		// it will delete an actual record from db
		$stmt_delete = $DB_con->prepare('DELETE FROM tbl_users WHERE userID =:uid');
		$stmt_delete->bindParam(':uid',$_GET['delete_id']);
		$stmt_delete->execute();

		$successMSG = "Record succesfully deleted ...";
		echo '<img src="loading.gif"><br>';
		header("refresh:1;index.php");
	}
}
?>