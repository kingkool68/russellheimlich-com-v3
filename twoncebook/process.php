<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Twoncebook - Twitter, Pownce, and Facebook updates all on one page!</title>
<style type="text/css">
@import url(style.css);
</style>
</head>
<body>
<div id="simplelife">
  <!-- Lifestream-->
  <ul>
<?php

//simplepie load
if(!class_exists("SimplePie")){
    include_once('simplepie.inc');
}

//timezone code
if (function_exists("date_default_timezone_set")) {
  date_default_timezone_set('America/New_York');   
}else{
   putenv('TZ=America/New_York');
}

$twitter = 'http://twitter.com/statuses/friends_timeline/' . $_POST['twitter'] . '.rss';
$pownce = 'http://pownce.com/feeds/public/' . $_POST['pownce'] . '/';	
$facebook = $_POST['facebook'];

$feeds = array($twitter,$pownce,$facebook);
$feeds = array_diff($feeds, array(""));

$today = date('Y-m-d');

$feed = new SimplePie($feeds, 'cache', 60*15);

// Set up date variable.
$stored_date = '';
 
// Go through all of the items in the feed
foreach ($feed->get_items(0,50) as $item)
{
	//Permalink
	$url = $item->get_permalink();
	
	// What is the date of the current feed item?
	$item_date = $item->get_date('Y-m-d');
        $class = 'twoncebook';
		$icon = 'http://del.icio.us/favicon.ico';
		$title = 'Twoncebook!';	
			
 
	// Is the item's date the same as what is already stored?
	// - Yes? Don't display it again because we've already displayed it for this date.
	// - No? So we have something different.  We should display that.
	if ($stored_date != $item_date)
	{
		// Since they're different, let's replace the old stored date with the new one
		$stored_date = $item_date;
		$difference = (strtotime($today) - strtotime($item->get_date('Y-m-d'))) / 86400; //Finds the difference and returns the number of days.
		if($difference == 0) {
			$difference = 'Today';
		}
		else if($difference == 1) {
			$difference = 'Yesterday';
		} else {
			$difference = round($difference) . ' days ago';
		}
		
 		// Display it on the page
		echo '</ul><h2 class="date">' . $difference . '</h2>' . "\r\n<ul>";
	}

        //Decide where the link came from and set class accordingly.
        //Just add an extra if section, with a segment of the permalink to set a new class.
        //EG, flickr feed links to flickr.com delicious links everywhere, so we treat any unknown as delicious link, but anything containing flickr.com as flickr.

if (strpos($url, 'facebook') !== false) {
    $class = 'facebook';
	$icon = 'http://www.facebook.com/favicon.ico';
	$title = 'Status updates from Facebook';
	
	echo '<li class="item '. $class .'"><img src="' . $icon . '" title="' . $title . '"><a class="time" href="' . $item->get_permalink() . '">' . $item->get_date('g:i a') . ' - </a><p>' . $item->get_title() . '</p></li>' . "\r\n"; 
}

if (strpos($url, 'twitter') !== false) {
  $class = 'twitter';
  $icon = 'http://assets1.twitter.com/images/favicon.ico';
  $title = 'Twitter';
  
  echo '<li class="item '. $class .'"><img src="' . $icon . '" title="' . $title . '"><a class="time" href="' . $item->get_permalink() . '">' . $item->get_date('g:i a') . ' - </a><p>' . $item->get_description() . '</p></li>' . "\r\n"; 
}
if (strpos($url, 'pownce') !== false) {
  $class = 'pownce';
  $icon = 'http://pownce.com/img/favicon.ico';
  $title = 'Pownce';
  
  echo '<li class="item '. $class .'"><img src="' . $icon . '" title="' . $title . '"><a class="time" href="' . $item->get_permalink() . '">' . $item->get_date('g:i a') . ' - </a><p>' . $item->get_description() . '</p></li>' . "\r\n"; 
}

}
?>

<cite>Lifestream powered by <a href="http://kierandelaney.net/blog/projects/simplelife/">SimpleLife</a> </cite>
</div>

</body>
</html>