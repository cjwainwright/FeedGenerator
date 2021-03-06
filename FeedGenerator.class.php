<?php
/**
    Copyright (c) 2012, C J Wainwright, http://cjwainwright.co.uk

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    "Software"), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
    LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
    OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
    WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

require_once("FeedCrawler.class.php");
require_once("FeedItem.class.php");

class FeedGenerator
{
    private $docRss;
    private $crawler;
    private $itemCache;
    private $itemCount = 10;
    private $feedURL;
    private $feedTitle;
    private $feedDescription;

    function __construct()
    {
        $this->logger = function(){};
        $this->crawler = new FeedCrawler($this);
        $this->itemCache = array();
    }

    public function setTitle($title)
    {
        $this->feedTitle = $title;
    }
    
    public function setDescription($description)
    {
        $this->feedDescription = $description;
    }

    public function setURL($url)
    {
        $this->feedURL = $url;
        $this->crawler->setURL($url);
    }
    
    public function addURLFilterRule($rule)
    {
        $this->crawler->addURLFilterRule($rule);
    }
    
    public function setItemCount($itemCount)
    {
        $this->itemCount = $itemCount;
    }
    
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
        
    public function go()
    {
        $this->log('Beginning crawl');
        $this->crawler->go();
        $this->buildRssDocument();
        $this->log('Done');
    }
    
    public function writeResponse()
    {
        header('content-type: text/xml');
        echo $this->docRss->saveXML();    
    }
    
    public function save($fileName)
    {
        $this->docRss->save($fileName);
    }
    
    public function addRssItem($url, DOMElement $element)
    {
        $id = $element->getAttribute("data-feed-id");
        if($element->hasAttribute("data-feed-url") == false)
        {
            $element->setAttribute("data-feed-url", $url);
        }
        
        //TODO - check existing items in cache and use most specific URL
        $this->log('Caching feed item: ' . $id);
        $this->itemCache[$id] = new FeedItem($element);
    }
        
    private function buildRssDocument()
    {
        // build outer Rss document framework
        $this->log('Building RSS document');

        $docRss = new DOMDocument();
        $nodeRss = $docRss->appendChild($docRss->createElement('rss'));
        $nodeRss->setAttribute('version', '2.0');
        
        $nodeChannel  = $nodeRss->appendChild($docRss->createElement('channel'));
        
        $nodeTitle = $nodeChannel->appendChild($docRss->createElement('title'));
		$nodeTitle->appendChild($docRss->createTextNode($this->feedTitle));

        $nodeLink = $nodeChannel->appendChild($docRss->createElement('link'));
		$nodeLink->appendChild($docRss->createTextNode($this->feedURL));

        $nodeDescription = $nodeChannel->appendChild($docRss->createElement('description'));
		$nodeDescription->appendChild($docRss->createTextNode($this->feedDescription));
        
        $this->docRss = $docRss;
        $this->nodeChannel = $nodeChannel;
        
        // order items by date descending
        function cmp($a, $b)
        {
            if($a->date == $b->date) 
            {
                return 0;
            }
            return ($a->date < $b->date) ? 1 : -1;
        }
        
        $this->log('Sorting items');
        uasort($this->itemCache, 'cmp');
        
        // loop items to max count adding them to the channel
        $count = 0;
        foreach($this->itemCache as $key => $value)
        {
            $this->createRssItem($value);
            $count += 1;
            if($count >= $this->itemCount)
            {
                $this->log('Reached maximum count ' . $count);
                break;
            }
        }
    }
    
    private function createRssItem(FeedItem $item)
    {
        $this->log('Creating item: ' . $item->title);

        $docRss = $this->docRss;
        $nodeChannel = $this->nodeChannel;
        
        $nodeItem = $nodeChannel->appendChild($docRss->createElement('item'));
        
        $nodeTitle = $nodeItem->appendChild($docRss->createElement('title'));
		$nodeTitle->appendChild($docRss->createTextNode($item->title));

        $dateString = date("D, j M Y H:i:s T", $item->date); //e.g. Wed, 8 Jan 2003 13:59:03 GMT
        $nodeDate = $nodeItem->appendChild($docRss->createElement('pubDate'));
		$nodeDate->appendChild($docRss->createTextNode($dateString));

        $nodeLink = $nodeItem->appendChild($docRss->createElement('link'));
		$nodeLink->appendChild($docRss->createTextNode($item->url));

        $nodeDescription = $nodeItem->appendChild($docRss->createElement('description'));
		$nodeDescription->appendChild($docRss->createTextNode($item->description));
    }
    
    public function log($message)
    {
        $logger = $this->logger;
        $logger($message);
    }
}

?>