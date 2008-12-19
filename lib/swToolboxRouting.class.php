<?php

/*
 * This file is part of the swToolbox package.
 * (c) Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    swToolbox
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swToolboxRouting
{
  /**
   * Listens to the routing.load_configuration event.
   *
   * @param sfEvent An sfEvent instance
   */
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();

    // preprend our routes
    $r->prependRoute('sw_toolbox_retrieve_dynamic_values', new sfRoute('/sw-toolbox/dynamic-values/*', array(
      'module' => 'swToolbox', 
      'action' => 'retrieveDynamicValues',
    )));

  }
}