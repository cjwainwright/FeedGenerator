<!DOCTYPE html>
<html>
    <head>
        <style type="text/css">
            body {
                font-family: arial, helvetica, sans-serif;
                padding: 20px;
            }
            
            .feed-link {
                font-size: 2em;
                margin-bottom:20px;
            }
            
            .log {
                color: #555;
            }
        </style>
    </head>
    <body>
        <a class="feed-link" href="rss.xml"/>Generated RSS</a>
        <div class="log">
            <h1>Log</h1>
            <?php
            include_once("includes/feed/FeedGenerator.class.php");

            $generator = new FeedGenerator();
            $generator->setTitle('My feed');
            $generator->setDescription('A non-existent feed does not have a description');
            $generator->setURL("http://" . $_SERVER['SERVER_NAME']);
            $generator->setItemCount(15);
            $generator->setLogger(function($message) { echo "<div class='log-item'>$message</div>"; });
            $generator->go();

            $generator->save("rss.xml");
            ?>
        </div>
    </body>
</html>