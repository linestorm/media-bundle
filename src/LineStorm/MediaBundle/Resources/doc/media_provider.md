Media Providers
===============

Media providers find media for the CMS. It is done this way so we can fetch media from anywhere we want, just by using
the MediaManager.

Creating a media provider is as simple as implementing the LineStorm\MediaBundle\Media\MediaProviderInterface interface.
You can also extend the LineStorm\MediaBundle\Media\AbstractMediaProvider to make it a bit easier.

Lets say we wanted to create a media provider that calls an API for media. Start by extending AbstractMediaProvider:

```php
<?php

namespace LineStorm\MediaBundle\Media;

use LineStorm\MediaBundle\Media\Exception\MediaFileAlreadyExistsException;
use LineStorm\MediaBundle\Media\Exception\MediaFileDeniedException;

class LocalStorageMediaProvider extends AbstractMediaProvider implements MediaProviderInterface
{
    protected $id = 'api_storeage';

    protected $form = 'api_storage_form_service_id';

    protected $api;

    public function __construct(MediaApi $api){
        $this->api = $api;
    }
}

```

Here, we have setup our provider id and form service. The form service is a is just a standard symfont2 form that we can
use to edit and save our media object. As such, creating the 'api_storage_form_service_id' service is not covered here.
The MediaApi is a mythical class that will be providing our API data. This could behind the scenes be calling cURL. The
MediaApi will return a MediaDocument, which holds the media data. This will be what the FormType is tied to.

In order to satisfy MediaProviderInterface, we must implement a few basic methods:

* getId
* getForm
* find
* findBy
* findByHash
* store
* update

We do not need to worry about getId and getForm as they are inherited from AbstractMediaProvider and return $id and
$form. If you don't want or need to use the FormType as a service, overridge the getForm implementation.

Lets continue and add our find, findBy and findByHash methods:

```php
    /**
     * @inheritdoc
     */
    public function find($id)
    {
        // call the api and get an array Id
        return $this->api->find($id);
    }

    /**
     * @inheritdoc
     */
    public function findByHash($hash)
    {
        // search the api via an image hash. This will block duplicate images. return null to allow duplicate images.
        return $this->api->findOneBy(array('hash' => $hash));
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $order = array(), $limit = null, $offset = null)
    {
        // find my any other criteria
        return $this->api->findBy($criteria, $order, $limit, $offset);
    }
```

Lastly, we want to implement store and update.
