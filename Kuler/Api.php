<?php
/**
 * Kuler API library for PHP
 * 
 * @author Gabriel Somoza (me@gabrielsomoza.com)
 * @link http://gabrielsomoza.com
 * @version 0.1.0

 * @category Kuler
 * @package PHP Kuler API Library
 * @link http://learn.adobe.com/wiki/display/kulerdev/B.+Feeds
 * 
 * @license http://creativecommons.org/licenses/by-nc-nd/3.0/ Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported License
 * @copyright Copyright (c) 2011, Gabriel Somoza
 */

define('KULER_PATH', realpath(dirname(__FILE__)));

require_once KULER_PATH . '/Theme.php';
require_once KULER_PATH . '/Comment.php';
require_once KULER_PATH . '/Exception.php';

class Kuler_Api {
    /**
     * The API base url
     */
    const URL_BASE = 'http://kuler-api.adobe.com/';
    
    /**
     * Get RSS Feeds
     */
    const URL_GET = 'rss/get.cfm?';
    
    /**
     * Search RSS Feeds
     */
    const URL_SEARCH = 'rss/search.cfm?';
    
    /**
     * Comments RSS Feeds
     */
    const URL_COMMENTS = 'rss/comments.cfm?';
    
    /**
     * Theme Thumbnail Generator
     */
    const URL_THUMBNAIL = 'rss/png/generateThemePng.cfm?';
    
    /**
     * Theme View URL (for the public website)
     */
    const URL_VIEW = '#themeID/';

    /**
     *
     * @var type 
     */
    protected $key;
    
    /**
     * Stores the raw response
     * @var SimpleXMLElement
     */
    protected $response;
    
    /**
     * Allowed listTypes for the 'Get' RSS
     * @var Array
     */
    protected $listTypes;

    /**
     * Initializes the Kuler object
     * @param string $key Your Kuler API Key
     */
    public function __construct($key) {
        if (!isset($key) || empty($key))
            throw new Kuler_Exception('Please provide an API key to use Kuler');
        $this->key = $key;
        $this->listTypes = array('recent', 'popular', 'rating', 'random');
    }

    /**
     * Returns a list of feeds of a specified type.
     * 
     * @param string $type One of the strings 'recent' (the default), 'popular', 'rating', or 'random'.
     * @param int $startIndex  A 0-based index into the list that specifies the first item to display. Default is 0, which displays the first item in the list.
     * @param int $itemsPerPage The maximum number of items to display on a page, in the range 1..100. Default is 20.
     * @param int $timeSpan Value in days to limit the set of themes retrieved. Default is 0, which retrieves all themes without time limit.
     * @return Array An array of KulerItem objects.
     */
    public function get($type = 'recent', $startIndex = 0, $itemsPerPage = 20, $timeSpan = 0) {
        if (!in_array($type, $this->listTypes))
            throw new Kuler_Exception('Invalid List Type: ' . $type);
        $params = array_filter(array(
            'listType' => $type,
            'startIndex' => (int) $startIndex,
            'itemsPerPage' => (int) $itemsPerPage,
            'timeSpan' => (int) $timeSpan,
        ));
        $this->request(self::URL_GET, $params);
        return $this->getItems();
    }

    /**
     * Returns a list of feeds that meet specified search criteria.
     * 
     * @param type $query A search filter. This can be one of the predefined filters listed below, or a simple string term to 
     *                    search on; for example, "blue". If you specify a simple term, the search looks for that term in 
     *                    theme titles, tags, author names, themeIDs, authorIDs, and hexValues. By default, retrieves all
     *                    available feeds.<br/><br/>
     *                    These filters are available:
     *                    <ul><li>themeID - search on a specific themeID</li>
     *                        <li>userID - search on a specific userID</li>
     *                        <li>email - search on a specific email</li>
     *                        <li>tag - search on a tag word</li>
     *                        <li>hex - search on a hex color value (can be in 
     *                            the format "ABCDEF" or "0xABCDEF")</li>
     *                        <li>title - search on a theme title</li></ul>
     * @param int $startIndex  A 0-based index into the list that specifies the first item to display. Default is 0, which displays the first item in the list.
     * @param int $itemsPerPage The maximum number of items to display on a page, in the range 1..100. Default is 20.
     * @return Array An array of KulerItem objects.
     * 
     * <b>Example usage:</b>
     * <code>
     * $kuler->search('email:me@gabrielsomoza.com')
     * </code>
     */
    public function search($query = '', $startIndex = 0, $itemsPerPage = 20) {
        $params = array_filter(array(
            'searchQuery' => (string) $query,
            'startIndex' => (int) $startIndex,
            'itemsPerPage' => (int) $itemsPerPage,
                ));
        $this->request(self::URL_SEARCH, $params);
        return $this->getItems();
    }

    /**
     * Returns a list of comments for either a specified theme (if a themeID is 
     * provided) or for all of a member's themes (if an email is provided).
     * 
     * @param int $themeID When this value is used, all comments are retrieved for the specified theme.
     * @param string $email When this value is used, comments are retrieved for themes created by this user
     * @param int $startIndex  A 0-based index into the list that specifies the first item to display. Default is 0, which displays the first item in the list.
     * @param int $itemsPerPage The maximum number of items to display on a page, in the range 1..100. Default is 20.
     * @return Array An array of KulerItem objects.
     */
    public function comments($themeID = null, $email = null, $startIndex = 0, $itemsPerPage = 20) {
        $params = array_filter(array(
            'themeID' => (int) $themeID,
            'email' => (string) $email,
            'startIndex' => (int) $startIndex,
            'itemsPerPage' => (int) $itemsPerPage,
                ));
        $this->request(self::URL_COMMENTS, $params);
        return $this->getComments();
    }

    /**
     * Retrieves a thumbnail of a specific theme.
     * 
     * @param int $themeID The id of a specified theme.
     * @return string The URL of the Theme's thumbnail.
     */
    public static function generateThemePng($themeID) {
        if (!is_numeric($themeID))
            throw new Kuler_Exception('Invalid Theme ID');
        $params = array(
            'themeid' => (int) $themeID,
            'key' => $this->key,
        );
        return self::buildUrl(self::URL_THUMBNAIL, $params);
    }
    
    /**
     * A better alias for generateThemePng()
     * @see Kuler::generateThemePng()
     */
    public static function getThemeThumbnail($themeID) {
        return self::generateThemePng($themeID);
    }

    /**
     * Generates the URL used to view a specific theme directly in the kuler public website.
     * 
     * @param int $themeID The id of a specified theme.
     * @return string The Theme's public URL
     */
    public static function viewThemeUrl($themeID) {
        if (!is_numeric($themeID))
            throw new Kuler_Exception('Invalid Theme ID');
        return self::buildUrl(self::URL_VIEW, (string) $themeID);
    }
    
    /**
     * A better alias for viewThemeUrl()
     * @see Kuler::viewThemeUrl()
     */
    public static function getThemeUrl($themeId) {
        return self::viewThemeUrl($themeID);
    }

    /**
     * Retrieves data from the Kuler API
     * 
     * @param string $endpoint The API endpoint to query against.
     * @param Array $params Parameters that will be parsed by http_build_query and appended to the URL.
     * @uses http_build_query()
     * @uses SimpleXmlElement
     * @see self::URL_* constants
     * @return SimpleXmlElement The XML response parsed into a SimpleXmlElement.
     */
    protected function request($endpoint, $params) {
        if (isset($params['itemsPerPage']) && ($params['itemsPerPage'] > 100 || $params['itemsPerPage'] < 1))
            throw new Kuler_Exception('The number of items per page must be between 1 and 100.');

        $params = array_merge(array(
            'key' => $this->key,
                ), $params);

        try {
            $this->response = new SimpleXmlElement(self::buildUrl($endpoint, $params), NULL, true);
        } catch (Exception $e) {
            throw new Kuler_Exception('Error retrieving the feed: ' . $e->getMessage());
        }
        return $this->response;
    }
    
    /**
     * Public accessor to the response element.
     * @return SimpleXmlElement
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Builds a URL to communicate with the Kuler API.
     * 
     * @param string $endpoint The API endpoint to query against.
     * @param string|Array $params Parameters that will be parsed by http_build_query and appended to the URL.
     * @return string The resulting URL 
     */
    protected static function buildUrl($endpoint, $params) {
        if (is_array($params))
            $params = http_build_query($params);
        $requestUrl = self::URL_BASE . $endpoint;
        return $requestUrl . $params;
    }

    protected function getComments() {
        return array_map(array($this, 'createComment'), $this->getXmlItems());
    }

    protected function createComment($xmlItem) {
        return new Kuler_Comment($xmlItem);
    }

    protected function getItems() {
        return array_map(array($this, 'createItem'), $this->getXmlItems());
    }

    protected function createItem($xmlItem) {
        return new Kuler_Theme($xmlItem);
    }

    protected function getXmlItems() {
        return $this->getResponse()->channel->xpath('item');
    }

}
