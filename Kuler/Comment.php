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

/**
 * Provides encapsulation for an XML Comment retrieved from the API.
 */
class Kuler_Comment {

    protected $_comment;
    
    /**
     * Enables creation of a Kuler_Comment object out of a comment XML Item. 
     * Checks the existance of the 'author' field to make sure the item was 
     * loaded correctly.
     * 
     * @param SimpleXMLElement $xmlItem 
     */
    public function __construct(SimpleXMLElement $xmlItem) {
        $namespaces = $xmlItem->getDocNamespaces();
        $this->_comment = $xmlItem->children($namespaces['kuler'])->commentItem;
        if (!isset($this->_comment->author))
            throw new Kuler_Exception('Error loading the Comment item');
    }

    /**
     * Automatic accessors for the $_comment object. The return value is a 
     * SimpleXMLElement and should be properly casted in most situations.
     * 
     * @param string $name
     * @return SimpleXMLElement The value of retrieving $name from the internal $_comment object.
     */
    public function __get($name) {
        $property = lcfirst($name);
        if (property_exists($this->_comment, $property)) {
            return $this->_comment->$property;
        } else
            throw new Kuler_Exception('Property \'' . $name . '\' not found');
    }

    /**
     * Comments also have some information about the theme they belong to. This
     * function creates a Kuler_Theme with that information. <br/><br/>
     * Its important to note that doing it this way won't yield the same results 
     * as loading the theme through the Get RSS (the latter method will return
     * a more complete set of properties). In any case, the object's utility 
     * methods should work in both cases.
     * 
     * @return Kuler_Theme A Kuler_Theme object initialized with the comment's
     * theme data.
     */
    public function getTheme() {
        return new Kuler_Theme($this->_comment);
    }

}
