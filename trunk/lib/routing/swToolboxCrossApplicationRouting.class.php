<?php

/*
 *  $Id$
 *
 * (c) 2008 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.soleoweb.com>.
 */

/**
 *
 * @package    swToolboxPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swToolboxRoutingCrossApplicationRouting
{
  /**
   * Listens to the routing.load_configuration event.
   *
   * @param sfEvent An sfEvent instance
   */
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();

    $config = sfConfig::get('app_swToolbox_swToolboxCrossApplicationRouting', array());
    
    if(!sfContext::hasInstance())
    {
      return;
    }
    
    $configuration = sfContext::getInstance()->getConfiguration();
    $env = $configuration->getEnvironment();
    $app = $configuration->getApplication();
    
    if(!array_key_exists($app, $config) || !$config[$app]['enabled'])
    {
      return;
    }
    
    $host = isset($config[$app]['host'][$env]) ? $config[$app]['host'][$env] : null;
    
    
    //
    foreach($config[$app]['load'] as $app_to_load)
    {
      $config_handler = new swCrossApplicationRoutingConfigHandler;
      $config_handler->setApp($app_to_load);
      $config_handler->setHost($host);
      
      $routes = $config_handler->evaluate(array(sfConfig::get('sf_apps_dir').'/'.$app_to_load.'/config/routing.yml'));
      
      foreach($routes as $name => $route)
      {
        $r->appendRoute($name, $route);
      }
    }
  }
}