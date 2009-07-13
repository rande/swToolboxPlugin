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

class swToolboxPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    if($this->configuration instanceof sfApplicationConfiguration)
    {
      if(sfConfig::get('app_swToolbox_autoload_helper', true))
      {
        $this->configuration->loadHelpers(array('swToolbox'));
        
      }
      
      // Cross link application 
      $cla = sfConfig::get('app_swToolbox_cross_link_application', array());
      if (array_key_exists($this->configuration->getApplication(), $cla) && $cla[$this->configuration->getApplication()]['enabled'])
      {
        $this->dispatcher->connect('routing.load_configuration', array('swToolboxRoutingCrossApplicationRouting', 'listenToRoutingLoadConfigurationEvent'));
      }
    }
    
    // register swToolbox routes
    if (sfConfig::get('app_swToolbox_routes_register', true) && in_array('swToolbox', sfConfig::get('sf_enabled_modules', array())))
    {
      $this->dispatcher->connect('routing.load_configuration', array('swToolboxRouting', 'listenToRoutingLoadConfigurationEvent'));
    }
       
    // sendMail option
    $this->dispatcher->connect('component.method_not_found', array('swToolbox', 'componentMethodNotFound'));
    $this->dispatcher->connect('configuration.method_not_found', array('swToolbox', 'configurationMethodNotFound'));
    
    // functionnal test debug panel
    if (sfConfig::get('sf_web_debug'))
    {
      
      $this->dispatcher->connect('debug.web.load_panels', array('swFunctionalUnitTestDebugPanel', 'listenToAddPanelEvent'));
    }

    // Menu Manager
    $this->dispatcher->connect('sw_menu_manager.register_listener', array('swMenuManager', 'listenToModuleMenuHandler'));
  }
}