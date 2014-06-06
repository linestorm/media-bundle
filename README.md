Media Module Bundle for LineStorm CMS
=====================================

[![Build Status](https://travis-ci.org/linestorm/media-bundle.svg?branch=master)](https://travis-ci.org/linestorm/media-bundle)

The Media Module Bundle for LineStorm CMS provides image management for the CMS

Installation
============

1. Download bundle using composer
2. Enable the Bundle
3. Configure the Bundle
4. Installing Assets
5. Configuring Assets

Step 1: Download bundle using composer
--------------------------------------

Add `linestorm/media-bundle` to your `composer.json` file, or download it by running the command:

```bash
$ php composer.phar require linestorm/media-bundle
```

Step 2: Enable the bundle
-------------------------

Enable the media bundle in the `app/AppKernel.php`:

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new LineStorm\MediaBundle\MediaBundle(),
    );
}
```

Step 3: Configure the Bundle
----------------------------

Add the default media provider in the `linestorm_cms_media` namespace inside the `app/config/config.yml` file. The default
is `local_storeage`

```yml
line_storm_media:
  default_provider: local_storeage
```

If you want to use `local_storage`, you will need to add it to your `Resources/config/services.yml` file:

```yml
parameters:
  linestorm.cms.media_provider.local_storeage.entity.class: Acme\DemoBundle\Entity\Media

services:
  linestorm.cms.media_provider.local_storeage:
        class: LineStorm\MediaBundle\Media\LocalStorageMediaProvider
        arguments:
            - @doctrine.orm.default_entity_manager
            - %linestorm.cms.media_provider.local_storeage.entity.class%
            - @security.context
            - /path/to/store/directory/
            - /web/path/
        tags:
            - { name: linestorm.cms.media_provider }
```

See [Creating a media provder](docs/media_provider.md) for creating your own
provider.


Step 4: Installing Assets
-------------------------

###Bower
Add [.bower.json](.bower.json) to the dependencies

###Manual
Download the modules in [.bower.json](.bower.json) to your assets folder



Step 5: Configuring Assets
-------------------------

You will need to add these dependency paths to your requirejs config:

```js
requirejs.config({
    paths: {
        // ...

        // cms media library
        cms_media:          '/path/to/bundles/linestormmedia/js/media',
        cms_media_list:     '/path/to/bundles/linestormmedia/js/list'
    }
});
```
