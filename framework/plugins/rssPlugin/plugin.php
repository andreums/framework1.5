<?php
/**
 * @author andreu
 *
 */
class rssPlugin extends BasePlugin {

    /**
     * The feed object
     *
     * @var FeedWriter
     */
    private $_feed;

    private $_remoteRSS;

    private $__rssProxy;

    /*
     * Sets the options of the plugin
     *
     * @access protected
     * @return void
     */
    protected function _setOptions($options) {
        $this->_options = $options;
        return;
    }

    /**
     * Sets up the plugin with the default values and options
     * specified in plugin.xml
     *
     * @access protected
     * @return void
     */
    protected function _setUp() {

        $this->_remoteRSS = array ();
        $this->_rssProxy = array();

        $this->_loadClasses();
        $this->_feed = new FeedWriter();

        $title = $this->_filter->encodeHTML($this->_getOption("channelTitle"));

        $this->_feed->setVersion($this->_getOption("feedType"));
        $this->_feed->setTitle($title);
        $this->_feed->setDescription($this->_getOption("channelDescription"));
        $this->_feed->setLink($this->_getOption("channelLink"));
        $this->_feed->setImage("Logotipo",$this->_getOption("channelImage"),$this->_getOption("channelImage"));
        $this->_feed->setChannelElement("language",$this->_getOption("channelLanguage"));
        $this->_feed->setChannelElement('pubDate', date(DATE_RSS, time()));
    }


    /**
     * Loads all the needed classes to use within
     * this plugin
     *
     * @return void
     */
    private function _loadClasses() {
        try {
            $route = $this->getPluginPath().DS."FeedWriter.php";
            require_once $route;

            $route = $this->getPluginPath().DS."FeedItem.php";
            require_once $route;

        }
        catch (Exception $ex) {
            trigger_error("Plugin | RSS Generator cannot load the classes that it needs",E_USER_NOTICE);
        };
    }


    /**
     * Generates the feed
     *
     * @return void
     */
    public function generateFeed() {
        header('Content-type: text/xml');
        $this->_feed->genarateFeed();
    }

    /**
     * Generates a element in the current feed
     *
     * @access public
     * @param $title string The title of the element
     * @param $link  string The link to the element
     * @param $pubDate DateTime the date of the publication
     * @param $description string The description of the element
     * @return void
     */
    public function addContent($title,$link,$pubDate,$description) {
        $item = $this->_feed->createNewItem();
        $item->setTitle($title);
        $item->setLink($link);
        $item->setDate($pubDate);
        $item->setDescription($description);
        $this->_feed->addItem($item);
    }

    /**
     * Generate a enclosure element in the current feed
     *
     * @access public
     * @param $title string The title of the element
     * @param $link  string The link to the element
     * @param $pubDate DateTime the date of the publication
     * @param $description string The description of the element
     * @param $enclosure array An array with the enclosure
     * @return void
     */
    public function addEnclosureContent($title,$link,$pubDate,$description,$enclosure=array()) {
        $item = $this->_feed->createNewItem();
        $item->setTitle($title);
        $item->setLink($link);
        $item->setDate($pubDate);
        $item->setDescription($description);
        $item->setEncloser($enclosure["url"],$enclosure["length"],$enclosure["type"]);
        $this->_feed->addItem($item);
    }



    /**
     * Loads an external RSS feed
     *
     * @param $url The URL of the external feed
     * @return void
     */
    public function loadRSSFeed($url) {
        try {
            $rssContents = @file_get_contents($url);
        }
        catch (Exception $ex) {
            $rssContents = DownloadUrl($url);
        }

        $rss = simplexml_load_string($rssContents);

        $version = (string) $rss["version"];
        $title = (string) $rss->channel->title;
        $description = (string) $rss->channel->description;
        $link = (string) $rss->channel->link;
        $language = (string) $rss->channel->language;

        $imageTitle = (string) $rss->channel->image->title;
        $imageURL =   (string) $rss->channel->image->url;
        $imageLink =  (string) $rss->channel->image->link;
        $pubDate = (string) $rss->channel->pubDate;

        $this->_remoteRSS = array(
            "version"     => $version,
            "title"       => $title,
            "description" => $description,
        	"link"		  => $link,
            "language"    => $language,
            "image"       => array (
                                "title" => $imageTitle,
                                "url"   => $imageURL,
                                "link"  => $imageLink
                             ),
            "pubDate"     => $pubDate,
            "items"       => array ( )

        );

        if (isset($rss->channel->item)) {
            foreach ($rss->channel->item as $item) {
                $channelItem = array ();
                $channelItem["title"] = (string) $item->title;
                $channelItem["link"]  = (string) $item->link;
                $channelItem["pubDate"] = (string) $item->pubDate;
                $channelItem["description"] = (string) $item->description;
                if (isset($item->enclosure)) {
                    $channelItem["enclosure"] = array();
                    $channelItem["enclosure"]["url"] = (string) $item->enclosure->url;
                    $channelItem["enclosure"]["length"] = (string) $item->enclosure->length;
                    $channelItem["enclosure"]["type"] = (string) $item->enclosure->type;
                }
                $this->_remoteRSS["items"][] = $channelItem;
            }
        }
    }


    /**
     * Generates a RSS feed from an external RSS feed
     *
     * @param $url string The URL of the external feed
     * @return void
     */
    public function rssProxy($url) {

        $this->_remoteRSS = array ();
        $this->_feed = new FeedWriter();
        $this->loadRSSFeed($url);

        $version = $this->_remoteRSS["version"];
        $version = floatval($version);
        if ($version<=1.0) {
            $version = RSS1;
        }
        else {
            $version = RSS2;
        }
        $this->_feed->setVersion($version);
        $this->_feed->setTitle($this->_remoteRSS["title"]);
        $this->_feed->setDescription($this->_remoteRSS["description"]);
        $this->_feed->setLink($this->_remoteRSS["link"]);
        $this->_feed->setImage($this->_remoteRSS["image"]["title"],$this->_remoteRSS["image"]["url"],$this->_remoteRSS["image"]["link"]);
        $this->_feed->setChannelElement("language",$this->_remoteRSS["language"]);
        $this->_feed->setChannelElement('pubDate', $this->_remoteRSS["pubDate"]);

        if (isset($this->_remoteRSS["items"]) && (count($this->_remoteRSS["items"])>0 ) ) {
            foreach($this->_remoteRSS["items"] as $item) {
                $title       = $this->_filter->encodeHTML($item["title"]);
                $link        = $item["link"];
                $pubDate     = $item["pubDate"];
                $description = $item["description"];

                if (isset($item["enclosure"])) {
                    $enclosure = array(
                    	"url"     => $item["enclosure"]["url"],
	            		"length"  => $item["enclosure"]["length"],
	            		"type"    => $item["enclosure"]["type"]
                    );
                    $this->addEnclosureContent($title,$link,$pubDate,$description,$enclosure);
                }
                else {
                    $this->addContent($title,$link,$pubDate,$description);
                }
            }
        }

        header('Content-type: text/xml');
        $this->_feed->genarateFeed();

    }

    /**
     * Loads an external RSS feed, and displays it
     *
     * @param $url string The url of the RSS feed
     * @return void
     */
    public function displayRSSFeed($url) {
        $this->rssProxy($url);
    }

    /**
     * Loads an external RSS feed and displays it on a page
     *
     * @param $url string The url of the RSS feed
     * @return void
     */
    public function displayRSSFeedInPage($url) {
        $this->loadRSSFeed($url);
        $this->addVariable("feed",$this->_remoteRSS);
        $this->renderView("channelView");
    }




};
?>