<?php
include_once("FeedGenerator.class.php");

$generator = new FeedGenerator();
$generator->setTitle('My feed');
$generator->setDescription('A non-existent feed does not have a description');
$generator->setURL("http://" . $_SERVER['SERVER_NAME']);
$generator->setItemCount(15);
$generator->go();

$generator->save("rss.xml");

echo '<a href="rss.xml"/>RSS</a>';
?>