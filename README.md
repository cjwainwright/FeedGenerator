FeedGenerator
=============

A simple little PHP program that generates an RSS feed for your site by crawling each page looking for appropriately marked content. 
This project makes use of the [PHPCrawl webcrawler library](http://phpcrawl.cuab.de/) to do the crawling of the site.

Usage
-----

### Generating the feed

An example of how to use FeedGenerator is included in GenerateRSSExample.php

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

On the first line we reference the main FeedGenerator.class.php. 

We then proceed to create a new `FeedGenerator` object, 
on which we can set various properties: a title and description for your feed, a URL from which the generator will start crawling 
your site looking for content, and the number of items we wish to limit the feed to.

We then tell the generator to start trawling the site with a call to `go`.

Finally we save the generated feed out to an xml file and output a link to the file so we can inspect how it came out. 
Thus to generate the feed we simply need to visit this PHP file. Note, it may take a while if your site is large.

### Marking HTML content for inclusion in the feed

To mark a section of your site to be included in the feed it needs to be enclosed in a single html element (you can normally wrap things in a `<span></span>` without affecting your layout if needs be).
The element must have an attribute `data-feed-id` set to some value, uniquely chosen for this feed entry. 
Further attributes can them be specified on the element to provide information about the feed entry, the full list of supported attributes is

* `data-feed-id` - The unique id for this feed item
* `data-feed-title` - The title of the feed item
* `data-feed-date` - The date of the feed item, can be in any format handled by PHP's strtotime function
* `data-feed-url` - Allows explicitly setting the URL for the feed item's link, if not set the link will be set as the URL of the (last) page the crawler found the item on.

So for example you may have something like

    <div data-feed-id="mythoughtsonthenatureofexistence" data-feed-title="My thoughts on the nature of existence" data-feed-date="10 Apr 2010">
        It seems that the physical world is merely a view on the underlying logic that determines it. Should the whole of existence be reducible to a mere mathematical
        equation, is the existence of this equation not equivalent to the physical world it predicts. Were another equation to be writ (or imagined, or merely possible),
        are not all the consequences of it's logic enough to say that it is another world perhaps containing entities with the power of mind to come to similar conclusions.
    </div>

When the FeedGenerator discovers this element it will create a feed item and use this element as the feed items description (i.e. content).