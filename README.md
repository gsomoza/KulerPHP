The PHP Kuler Library
=====================
A PHP wrapper for the Kuler API

Sample Usage
-----
```php
require_once "Kuler/Api.php";

$kuler = new Kuler_Api('api_key');
$themes = $kuler->get('recent');

$colors = $themes[0]->getSwatchesHex(true);  
//>> array("#516373", "#6C838C", "#F2E8C9", "#F2B999", "#F2F2F2")

$themeUrl = $themes[0]->getUrl();  
//>> http://kuler.adobe.com/#themeID/1400104

//etc.
```