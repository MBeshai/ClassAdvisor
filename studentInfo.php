<?php     

  // start the session and get the classes from the previous form
@session_start();
$classes = $_SESSION['classes'];
include('includes/CommonMethods.php');
$COMMON = new Common(false);


?>


<!DOCTYPE html>

<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <!--This is the header; the first line styles the page using the css page it links to.
    The second line gives the page a title when someone hovers over the tab.
    The third line gives the page the tab logo.
    -->
    <link rel="stylesheet" type="text/css" href="css/StyleSheet2.css">
    <title>UMBC CS Advising</title>
    <link rel="icon" href="http://styleguide.umbc.edu/files/2014/05/UMBCretrievers_LOGO.jpg" type="image/x-icon">

</head>
<body> <!--  This table holds the two pictures at the top of the page and the Header. Its done this way so everything stays in place relative to each other -->


<?php
			      // define variables and set to empty values
$fnameErr = $lnameErr = $idErr = $userNameErr = $emailErr = $semesterErr = $yearErr = "";
$fname = $lname = $id = $userName = $email = $semester = $year = "";
$b_fName = $b_lName = $b_id = $b_user = $b_email = $b_semester = $b_year = $b_classes = false; 
$successMessage = "";


$wants = $_POST["wants"];

// validate the first name field

  if (empty($_POST["fname"])) {
    $fnameErr = "Name required";
  } else {
    $fname = test_input($_POST["fname"]);
    // check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/",$fname)) {
      $fnameErr = "Only letters"; 
    }

    else
      {$b_fName = true;}
  }



// validate the last name field

  if (empty($_POST["lname"])) {
    $lnameErr = "Name required";
  } else {
    $lname = test_input($_POST["lname"]);
    // check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/",$lname)) {
      $lnameErr = "Only letters";
    }

    else
      {$b_lName = true;}
  }




// validate the ID field
if (empty($_POST["id"])) {
  $idErr = "ID required";
}
else
  {
    $id = test_input($_POST["id"]);
    $b_id = true;
  }



// validate the username field
if (empty($_POST["userName"])) {
  $userNameErr = "Username required";
}
else
  {
    $userName = test_input($_POST["userName"]);
    $b_user = true;
  }


// validate the email field
if (empty($_POST["email"])) {
  $emailErr = "Email required";
} else {
  $email = test_input($_POST["email"]);
  
  // check if e-mail address is well-formed
  $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

  if (preg_match($pattern, $email) === 1) {
    // emailaddress is valid
    $b_email = true;

  }
  else
    {
      $b_email = false;
      $emailErr = "Invalid";
    }

 
  }


// validate the semester field
if (empty($_POST["semester"])) {
  $semesterErr = "Semester required";
}

else
  {
    $semester = test_input($_POST["semester"]);
    $b_semester = true;
  }


// validate the year field
if (empty($_POST["year"])) {
  $yearErr = "Year required";
}

else
  {
    $year = test_input($_POST["year"]);
    $b_year = true;
  }


if(empty($wants))
  {
    $b_classes = false;
  }
else
  {
    $b_classes = true;
  }



// check the status of all the variables here. If everything is good we can upload to the database
if($b_fName == true and  $b_lName == true and  $b_id == true and  $b_user == true and  $b_email == true and  $b_semester == true and  $b_year == true and $b_classes == true)
  {

    $classesWant = class_toString($wants);
    $sql = "INSERT INTO `Student_Information`(`FirstName`, `LastName`, `UserName`, `Email`, `Semester`, `Year`, `ClassesTaken`) VALUES ('".$fname."','".$lname."','".$userName."','".$email."','".$semester."','".$year."','".$classesWant."')";
    $rs = $COMMON->executeQuery($sql, $_SERVER["SCRIPT_NAME"]);


    // if we are successful let the user know and clear the variables
    $fname = $lname = $id = $userName = $email = $semester = $year = "";
    //echo "Save Successful!";
    $successMessage = "Your Information Has Been Uploaded Successfuly!";
  }




function class_toString($classes)
{

  $want = "";
  foreach($classes as $class)
    {
      $want = $want . "," . $class;
    }


  return $want;
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

?>


<table>
    <tr>
        <td width="150px"><img src="http://styleguide.umbc.edu/files/2014/05/UMBCretrievers_LOGO.jpg" /></td>
        <td><h2>UMBC Computer Science Advising</h2></td>
        <td width="150px"><img src="http://styleguide.umbc.edu/files/2014/05/UMBCretrievers_LOGO.jpg" /></td>
    </tr>
</table>

<br>

  <div5> <h4> <?php echo $successMessage;  ?> </h4> </div5>


    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

<label>
  <p4>First Name:<p4><br>
  <p4>Last Name:<p4><br>
  <p4>ID:<p4><br>
  <p4>User Name:<p4><br>
  <p4>Email:<p4><br>
  <p4>Semester:<p4><br>
  <p4>Year:<p4><br>
        </label>
        <label>

   <input type="text" name="fname" value="<?php echo $fname;?>">
   <input type="text" name="lname" value="<?php echo $lname;?>">
   <input type="text" name="id" value="<?php echo $id;?>">
   <input type="text" name="userName" value="<?php echo $userName;?>">
   <input type="text" name="email" value="<?php echo $email;?>">
   <input type="text" name="semester" value="<?php echo $semester;?>">
   <input type="text" name="year" value="<?php echo $year;?>">

        </label>
<label>
  <span class="error">* <?php echo $fnameErr;?> </span> <br/>
  <span class="error">* <?php echo $lnameErr;?> </span> <br />
  <span class="error">* <?php echo $idErr;?></span> <br />


  <span class="error">* <?php echo $userNameErr;?> </span> <br />
  <span class="error">* <?php echo $emailErr;?></span><br />

  <span class="error">* <?php echo $semesterErr;?></span> <br />
  <span class="error">* <?php echo $yearErr;?> </span><br />


</label>

<label></label>
	
<br> <br>



  <label>

	Please Choose The Classes You Wish To Take: <br> <br>
			    
	<?php   

    // need to dynamically print out all of the checkboxes for the classes
    foreach($classes as $class)
    {
      echo "<input type='checkbox' name='wants[]' value='".$class."'>".$class." <br>";
    }
			      
	  ?>

	<br>
	<br>
    
	</label>
	

	<input type="submit" name="submit" value="Submit">

      </form>

    

  			      



</body>
</html>