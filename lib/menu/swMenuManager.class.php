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
class swMenuManager
{
  
  static 
    $instance;

  protected
    $menu_handlers = array(),
    $menus = array(),
    $bound = false,
    $selector = null,
    $references = array();
  
  private function __construct()
  {
    $this->menus = array(
      'main'    => new swMenuElement,
      'sidebar' => new swMenuElement,
      'footer'  => new swMenuElement
    );
  } 
  
  public static function getInstance()
  {
    if(is_null(self::$instance))
    {
      self::$instance = new swMenuManager;
    }
    
    return self::$instance;
  }
  
  public function setSelector($selector)
  {
    $this->selector = $selector;
  }
  
  public function getSelector()
  {
    
    return $this->selector;
  }
  
  public function getMenu($name)
  {
    
    if(!isset($this->menus[$name]))
    {
      throw new sfException('The menu : '.$name.' is not defined');
    }
    
    if(!$this->menus[$name]->isBound())
    {
      $this->buildMenu();
    }
    
    return $this->menus[$name];
  }
  
  public function registerHandler($handler)
  {
    $this->menu_handlers[] = $handler;
  }
  
  public function buildMenu()
  {
    
    if($this->bound)
    {
      
      return;
    }
  
    $this->bound = true;
    
    $action_stack = sfContext::getInstance()->getActionStack()->getLastEntry();
    
    $params = array(
      'menus'  => &$this->menus,
      'action' => $action_stack,
    );
    
    $methods = array(
      'buildMenu_'.$action_stack->getModuleName().'_'.$action_stack->getActionName(),
      'buildMenu_'.$action_stack->getActionName(),
      'buildMenu',
    );

    foreach($this->menu_handlers as $handler)
    {
      
      if(is_string($handler) && sfAutoload::getInstance()->loadClass($handler))
      {
        $handler = new $handler;
      }
      
      if(is_object($handler))
      {
        foreach($methods as $method )
        {
          if(method_exists($handler, 'canBuild') && !call_user_func_array(array($handler, 'canBuild'), $params))
          {
            continue;
          }
          
          if(method_exists($handler, $method))
          {
            call_user_func_array(array($handler, $method), $params);
            
            continue;
          }
        }
      }

      if(is_callable($handler))
      {
        call_user_func_array($handler, $params);
      }
    }
  }
  
  /**
   * register a menu handler, a menu handler is used to define only branch of
   * the menu structure. 
   * 
   * @param sfEvent $e
   * @return unknown_type
   */
  public static function listenToModuleMenuHandler(sfEvent $e)
  {
    self::getInstance()->registerHandler($e->getSubject());
    
    $e->setProcessed(true);
  }
}