<?php
// get posted data into local variables

$EmailFrom = Trim(stripslashes($_POST['emailFrom'])); 
$EmailTo = "kingkool68";
$EmailTo .= "@gmail.com";
$Site = Trim(stripslashes($_POST['site'])); 
$Name = Trim(stripslashes($_POST['realname']));
$Message =  Trim(stripslashes($_POST['message']));
$Subject = "You have an email from your form!";
$humanCheck = $_POST['humanCheck']; 

// validation
$validationOK=true;
if (Trim($EmailFrom)=="") $validationOK=false;
if (Trim($Name)=="") $validationOK=false;
if (Trim($Message)=="") $validationOK=false;
if (!$humanCheck=="") $validationOK=false;
if (!preg_match('/russellheimlich.com/i',$_SERVER['HTTP_REFERER'])) $validationOK=false;
if (!$validationOK) {
  print "<meta http-equiv=\"refresh\" content=\"0;URL=error.html\">";
  exit;
}

// prepare email body text
$Body = "";
$Body .= "Name: ";
$Body .= $Name;
$Body .= "\n";
$Body .= "Email: ";
$Body .= $EmailFrom;
$Body .= "\n";
$Body .= "Site: ";
$Body .= $Site;
$Body .= "\n";
$Body .= $Message;
$Body .= "\n";
$Body .= "--- End of Transmission ---";


// send email 
$success = mail($EmailTo, $Subject, $Body, "From: <$EmailFrom>");
// redirect to success page 
if ($success){
  print "<meta http-equiv=\"refresh\" content=\"0;URL=success.html\">";
}
else{
  print "<meta http-equiv=\"refresh\" content=\"0;URL=error.html\">";
}
?>