<?php

require_once("url_to_absolute/url_to_absolute.php");

class FeedItem
{
    public $url;
    public $title;
    public $date;
    public $description;

    function __construct(DOMElement $element)
    {
        $this->url = $element->getAttribute("data-feed-url");
        $this->title = $element->getAttribute("data-feed-title");
        $this->date = strtotime($element->getAttribute("data-feed-date"));
        
        $this->makeLinksAbsolute($this->url, $element);
        
        $this->description = $element->ownerDocument->saveXML($element);
    }
    
    private function makeLinksAbsolute($baseUrl, DOMElement $element)
    {
        $xpath = new DOMXPath($element->ownerDocument);
        $hrefs = $xpath->query("//*[@href]", $element);
        if(is_null($hrefs) == false)
        {
            foreach($hrefs as $href)
            {
                $relativeUrl = $href->getAttribute("href");
                $href->setAttribute("href", url_to_absolute($baseUrl, $relativeUrl));
            }
        }
        
        $srcs = $xpath->query("//*[@src]", $element);
        if(is_null($srcs) == false)
        {
            foreach($srcs as $src)
            {
                $relativeUrl = $src->getAttribute("src");
                $src->setAttribute("src", url_to_absolute($baseUrl, $relativeUrl));
            }
        }
    }
}

?>