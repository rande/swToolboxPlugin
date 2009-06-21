# swUserContextCacheFilter

This class can replace the default cache handler class. The class always executes an user context action.

Let's take this example with an ecommerce website, you might want to cache the full action, however some parts are user specific.
So you can have a viewProduct action and a viewProduct_UserContext action in your actions.class.php

* viewProduct : execute page related action, get the product and other related information
* viewProduct_UserContext : execute user specific action for the current page, like testing if the product is already in the client's basket.

## Installation

* edit the filters.yml

        [yml]
        cache:
          class: swUserContextCacheFilter
          param:
            condition: on # always set to on