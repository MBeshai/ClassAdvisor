<?php

session_start();


include 'includes/CommonMethods.php';

$COMMON = new Common(false);
$takenClasses_arr = array();
$takenClasses_object_arr = array();
$CS_PRE_REQS = array("cmsc201","cmsc202", "cmsc203","cmsc313","cmsc331","cmsc435", "cmsc341", "cmsc421", "cmsc447","cmsc461","cmsc471", "cmsc481","stat355","math150","math151", "math152","biol141", "chem101", "chem102","phys121","math152","phys122");
$PreReqs_User_Has = array();


  // Get all of the selected class from the form and add them to our array
foreach ($_POST['classes'] as $selectedOption)
   { 
     //echo $selectedOption."\n";
     array_push($takenClasses_arr, $selectedOption);

   }



// loop to determine the prereqs that user has taken so we can 
$PreReqs_User_Has = fetch_user_preReqs($takenClasses_arr, $CS_PRE_REQS);
//show_array($PreReqs_User_Has);

$classes = fetch_classes_user_can_take($takenClasses_arr, $PreReqs_User_Has, $COMMON);


// store our variable in session so we can pass to the other file
$_SESSION['classes'] = $classes;

//show_array($classes);



// push to the other file so we can display the information to the user 
header('Location: studentInfo.php');









function can_user_take_software($arr)
{
  // this function will handle the specific case where the in order to take 447, any 400 elective must be take. This will loop through the classes taken by the user and use string matching to return a boolean indicating if the user has a 400 level or not

  foreach($arr as $class)
    {
      $pos = strpos($class, "cmsc4");
      if ($pos !== false) {
	return true;
      }
    }

  return false;
}

function fetch_classes_user_can_take($classesTaken,$PreReqsTaken, $db)
{

  // this function will use the array of prereqs that the user has to determine what classes can be taken
  // need to loop through all of the pre reqs the user has and perform a DB look up to see what classes the user can take using the PreReqs column

  $classes_userCanTake = array();

  foreach($PreReqsTaken as $preReq)
    { 
      // generate the sql to get all of the classes that the user can take with that specific prereq
      $sql = "SELECT `Identifier` FROM `Class_Master` WHERE `PreReqs` = '" .$preReq . "' ORDER BY `Identifier`;";
      $rs = $db->executeQuery($sql, $_SERVER["SCRIPT_NAME"]);
      while ($row = mysql_fetch_assoc($rs)) 
	{
	  // check to make sure we are not double adding classes, checking both the classes that the user can take and the classes the user has already taken 
	  if (in_array($row["Identifier"], $classes_userCanTake) == false and in_array($row["Identifier"], $classesTaken)== false)
	    { array_push($classes_userCanTake, $row["Identifier"]); }

	}


    }

  // check to see if the user can take software
  if(can_user_take_software($classesTaken) == true)
    {array_push($classes_userCanTake, "cmsc447");}

  // going to handle the science options here
  

  // finally once everything is dne we can return the array
  return $classes_userCanTake;

}

function handleSciencePath($classesTaken, $canTake, $db)
{
  
  // these will hold the status of the classes the user has taken
  $physicsPath;
  $bioPath;
  $chemPath;
  $filter;
  $criteriaMet = false;
  $scienceClasses = get_science_classes($db);


  // call all of the functions that check the conditions and set boolean flags
  $hasSciencePath = hasSciencePath($classesTaken);
  $hasCredits = hasCredits($classesTaken, $db);
  $hasLab = hasLab($classesTaken);


  // if all of the criteria are met then we do not need to touch the array and we can simply return it
  if($hasSciencePath == true and $hasCredits == true and $hasLab == true)
    return $canTake;

// this function will go through the process of going through science classes and determing what the user needs to
// checking to see if there any science classes at all. If not need to push the main ones to start the sequence
  if (in_array("chem101", $classesTaken) == false and in_array("phys121", $classesTaken) == false and in_array("biol141", $classesTaken) == false)
    {
      array_push($canTake, "chem101");
      array_push($canTake, "phys121");
      array_push($canTake, "biol141");
      return $canTake;
    }

  // check to see what path the user has taken so we can add the respective class to the classes taken
  // ie. if the user has taken chem101 we want to add chem102 to the list to ensure path integirty
  if($hasSciencePath == false)
    {
      $criteriaMet = false;
      if(in_array("chem101", $classesTaken) == true)
	{
	  $chemPath = true;
	  $bioPath = false;
	  $physicsPath = false;
	  $filter = "chem10";
	}
      else if (in_array("biol141", $classesTaken) == true)
	{
	  $chemPath = false;
	  $bioPath = true;
	  $physicsPath = false;
	  $filter = "bio14";
	}
      else
	{
	  $chemPath = false;
	  $bioPath = false;
	  $physicsPath = true;
	  $filter = "phys12";
	}
    
      // now add the filter and get all of the classes 
      $sql = "SELECT * FROM `Class_Master` WHERE `Identifier` LIKE '".$filter."%';";
      $rs = $db->executeQuery($sql, $_SERVER["SCRIPT_NAME"]);
      
      // now that we have the classes that follow those initial science classes we can add them to the classes the user can take
      while ($row = mysql_fetch_assoc($rs))
	{
	  if(in_array($row["Identifier"], $classesTaken) == false and in_array($row["Identifier"], $canTake) == false)
	    array_push($canTake, $row["Identifier"]);
	}
    }

  // next we can check to credit count. If we dont we can add the rest of the science classes
  if($hasCredits == false)
    {
      //push all of the science classes to the classes the user can take
      $criteriaMet = false;
      
      foreach($scienceClasses as $s_class)
	{
	  if(in_array($s_class, $classesTaken) == false and in_array($row["Identifier"], $canTake) == false)
	    array_push($canTake, $s_class);
	}

    }

  else
    {
      // if the user has met the credit requirement we need to check for the lab
      if($hasLab == true)
	{
	  // all the criteria has been met at this point so we can delete all of the science options from the canTake List
	  $criteriaMet = true;
	  
	}
      else
	{
	  // the user doesnt have a lab class so we need to add all lab classes to the list of classes taken 
	  $criteriaMet = false;
	}
      
    }

}




function hasCredits($classesTaken, $db)
{
  // this function will see if the user has reached the 12 credits of science electives required
  // get our full list of science classes
  $science_classes = get_science_classes($db);
  $numCredits = 0;
  foreach($science_classes as $s_class)
    {
      foreach($classesTaken as $t_class)
	{
	  if($t_class == $s_class)
	    {
	      // if we have a match we need to query to DB to get the number of credits for the class and then add it to our variable
	      $classCredit = getClassCredit($t_class, $db);
	      $numCredits = $numCredits + $classCredit;

	    }

	}

    }

  // see if we have the right amount of credits
  if($numCredits >= 12)
    return true;
  else
    return false;

}

function hasLab($classesTaken)
{
  // this function will search the classes that the user has taken to see if there is a lab course
  foreach($classesTaken as $class)
    {
      if($class == "chem102L" or $class == "ges286" or $class == "phys122L" or $class == "sci100")
	return true;
    }

  // if we reach this point then the user doers not have a lab and we can return false
  return false;
}

function hasSciencePath($classesTaken)
{
  // this function will check to see if the user has one of the valid science paths which can be the following
  // biol 141 142 OR phys 121 122 OR chem 101 102

  if (in_array("biol141", $classesTaken) == true and in_array("biol142", $classesTaken)== true)
    return true;

  if (in_array("chem101", $classesTaken) == true and in_array("chem102", $classesTaken)== true)
    return true;

  if (in_array("phys121", $classesTaken) == true and in_array("phys122", $classesTaken)== true)
    return true;

  // if we reach this point the user does not have the correct path so we return false
  return false;

}


function get_science_classes($db)
{
  // this function is responsible for making a call to DB and getting all of the possible science classes 
  // get all of the science classes and load them into the array
  $science_classes = array();
  $sql = "SELECT `Identifier` FROM `Science_Classes`;";
  $rs = $db->executeQuery($sql, $_SERVER["SCRIPT_NAME"]);
  while ($row = mysql_fetch_assoc($rs))
    {
      array_push($science_classes, $row["Identifier"]);
    }


  return $science_classes;

}


function getClassCredit($class, $db)
{
  // this function will return the number of credits for a class
  $sql = "SELECT `NumCredits` FROM `Class_Master` WHERE `Identifier` = '".$class."';";
  $rs = $db->executeQuery($sql, $_SERVER["SCRIPT_NAME"]);
  $row = mysql_fetch_row($rs);
  return $row[0];
}


function fetch_user_preReqs($takenClasses, $singleReqs)
{

  // This function will use the array of prereqs and the information recieved from the last form to key out which of the reqs the user has taken so we can use that to query the DB for the classes that the student can take for the next semester
  // NOTE *** Single Reqs --> Pre Reqs with one class     Compound Reqs --> Pre Reqs that require 2 classes

  

  // loop to determine the prereqs that user has taken so we can knock out the single reqs
  $arr = array();
  $b_400Found = false;
  foreach($singleReqs as $preReq)
    {

      foreach($takenClasses as $taken) 
	{
	  if($taken == $preReq) // if we have a match
	    {
	      array_push($arr, $preReq); // add it to our array
	    }
	}
    }

  

  // next we move onto the compound reqs
  if (in_array("cmsc331", $arr) == true and in_array("cmsc341", $arr) == true)
    {
      array_push($arr, "cmsc331;cmsc341");
    }
  
  if (in_array("cmsc313", $arr) == true and in_array("cmsc341", $arr) == true)
    {
      array_push($arr, "cmsc313;cmsc341");
    }
  
  if (in_array("cmsc461", $arr) == true and in_array("cmsc481", $arr) == true)
    {
      array_push($arr, "cmsc461;cmsc481");
    }

  if (in_array("cmsc341", $arr) == true and in_array("stat355", $arr) == true)
    {
      array_push($arr, "cmsc341;stat355");
    }

  if (in_array("cmsc421", $arr) == true and in_array("cmsc481", $arr) == true)
    {
      array_push($arr, "cmsc421;cmsc481");
    }

  if (in_array("cmsc435", $arr) == true and in_array("cmsc471", $arr) == true)
    {
      array_push($arr, "cmsc435;cmsc471");
    }

  if (in_array("chem101", $arr) == true and in_array("chem102", $arr) == true)
    {
      array_push($arr, "chem101;chem102");
    }

  if (in_array("phys121", $arr) == true and in_array("math152", $arr) == true)
    {
      array_push($arr, "phys121;math152");
    }

  if (in_array("phys121", $arr) == true and in_array("phys122", $arr) == true)
    {
      array_push($arr, "phys121;phys122");
    }





  // finally return the array
  return $arr;

}

function show_array($arr) // used for debugging
{
  foreach ($arr as $element)
    {echo $element . "<br>";}
}



// END OF PHP FILE *********************
?>