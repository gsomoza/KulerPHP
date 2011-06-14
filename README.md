The PHP Kuler Library
=====================
A PHP wrapper for the Kuler API

Usage
-----
```php
$kuler = new Kuler_Api('api_key');
$themes = $kuler->get('recent');

$colors = $themes[0]->getSwatchesHex(true);
$themeUrl = $themes[0]->getUrl();
//etc.
```