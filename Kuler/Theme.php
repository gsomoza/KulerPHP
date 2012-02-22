<?php
/**
 * Kuler API library for PHP
 * 
 * @author Gabriel Somoza (me@gabrielsomoza.com)
 * @link http://gabrielsomoza.com
 * @version 0.1.1

 * @category Kuler
 * @package PHP Kuler API Library
 * @link http://learn.adobe.com/wiki/display/kulerdev/B.+Feeds
 * 
 * @copyright Copyright (c) 2011, Gabriel Somoza
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Provides encapsulation for an XML Theme retrieved from the API.
 */
class Kuler_Theme {

    public $_theme;

    /**
     * Enables creation of a Kuler_Theme object out of a theme XML Item. 
     * Checks the existance of the 'themeID' field to make sure the item was 
     * loaded correctly.
     * 
     * @param SimpleXMLElement $xmlItem 
     */
    public function __construct(SimpleXMLElement $rssItem) {
        $namespaces = $rssItem->getDocNamespaces();
        $this->_theme = $rssItem->children($namespaces['kuler'])->themeItem;
        if (!isset($this->_theme->themeID))
            throw new Kuler_Exception('Error loading the Kuler item');
    }

    /**
     * Automatic accessors for the $_theme object. The return value is a 
     * SimpleXMLElement and should be properly casted in most situations.
     * 
     * @param string $name
     * @return SimpleXMLElement The value of retrieving the "theme{$name}" property from the internal $_theme object.
     */
    public function __get($name) {
        $property = 'theme' . ucfirst($name);
        if (property_exists($this->_theme, $property))
            return $this->_theme->$property;
        else
            throw new Kuler_Exception('Property \'' . $name . '\' not found');
    }

	/**
	 * Retrieves the theme's author's name
	 */
	public function getAuthorLabel(){
		return (string)$this->_theme->themeAuthor->authorLabel;
	}

	/**
	 * Retrieves the theme's author's Kuler ID
	 */
	public function getAuthorID(){
		return (int)$this->_theme->themeAuthor->authorID;
	}
	
	/**
	 * Retrieves the theme's title
	 */
	public function getTitle(){
		return (string)$this->_theme->themeTitle;
	}

    /**
     * Returns an Array of strings where each string is the HEX color of a swatch
     * in the theme.
     * 
     * @param boolean $prependPound If set, it will prepend the pound symbol (#) 
     * to each color - useful for direct HTML usage for example.
     * @return Array Each swatch in the theme in its hexadecimal format.
     */
    public function getSwatchesHex($prependPound = true) {
        $hex = array();
        foreach ($this->swatches->swatch as $swatch) {
            $hex[] = ($prependPound ? '#' : '') . (string) $swatch->swatchHexColor;
        }
        return $hex;
    }
    
    /**
     * Retrieves the thumbnail URL of the theme
     */
    public function getThumbnail() {
        return Kuler_Api::getThemeThumbnail((int) $this->ID);
    }

    /**
     * Returns the URL used to view the theme directly in the kuler public website.
     * 
     * @return string
     */
    public function getUrl() {
        return Kuler_Api::viewThemeUrl((int) $this->ID);
    }

}