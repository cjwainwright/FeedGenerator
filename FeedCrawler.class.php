<?php

require_once("PHPCrawl_080/libs/PHPCrawler.class.php"); 

class FeedCrawler extends PHPCrawler
{
    private $generator;
    private $rootURL;

    function __construct(FeedGenerator $generator)
    {
        parent::__construct();
        
        $this->setFollowMode(2); //The crawler will only follow links that lead to the same host like the one in the root-url.
        $this->addContentTypeReceiveRule("#text/html#");
        $this->addURLFilterRule("#(jpg|jpeg|gif|png|bmp|ico)$# i");
        $this->addURLFilterRule("#(css|js|swf)$# i");
        $this->addURLFilterRule("#(xml|rss|zip|gz|ps|pdf|exe)$# i");
        
        $this->generator = $generator;
    }
    
    function handleDocumentInfo(PHPCrawlerDocumentInfo $PageInfo)
    {
        if(($PageInfo->http_status_code == 200) &&
           ($PageInfo->content))
        {
            $doc = new DOMDocument();
            $doc->loadHTML($PageInfo->content);
            
            $xpath = new DOMXPath($doc);
            $elements = $xpath->query("//*[@data-feed-id]");
            if(is_null($elements) == false)
            {
                foreach($elements as $element)
                {
                    $this->generator->addRssItem($PageInfo->url, $element);
                }
            }
        }
    }
}

?>