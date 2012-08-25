<?php

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
        
    public function go()
    {
        $this->crawler->go();
        $this->buildRssDocument();
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
        $this->itemCache[$id] = new FeedItem($element);
    }
        
    private function buildRssDocument()
    {
        // build outer Rss document framework
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
        
        uasort($this->itemCache, 'cmp');
        
        // loop items to max count adding them to the channel
        $count = 0;
        foreach($this->itemCache as $key => $value)
        {
            $this->createRssItem($value);
            $count += 1;
            if($count >= $this->itemCount)
            {
                break;
            }
        }
    }
    
    private function createRssItem(FeedItem $item)
    {
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
}

?>