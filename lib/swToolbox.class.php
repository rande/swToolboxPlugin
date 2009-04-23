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
 * @package    swToolbox
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swToolbox
{
  
  /**
   * modified version of Doctrine_Inflector::urilize
   *
   * @param unknown_type $text
   * @return unknown
   */
  static public function buildUrl($text)
  {

    $text = trim($text);
        
    // Remove all non url friendly characters with the unaccent function
    $text = Doctrine_Inflector::unaccent($text);

    // More stripping. Replace spaces with dashes
    $text = strtolower(preg_replace('/[^A-Z^a-z^0-9^\/]+/', '-',
      preg_replace('/([a-z\d])([A-Z])/', '\1_\2',
        preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2',
          preg_replace('/::/', '/', $text)))
    ));

    $text = str_replace(array('/'), array('-'), $text);
    
    return trim($text);
  }
  
  /**
   * Deprecated : use sfToolboxForm::updateElements
   */
  static function updateFormElements(sfForm $form, sfWidget $widget, array $elements)
  {
    
    throw new RuntimeException('Please update the '.get_class($form).' sfForm class. This method is now deprecated, use swToolboxFormHelper::updateFormElement');
  }
  
  /**
   * Deprecated : use sfToolboxForm::convertFileInformation
   */
  static function convertFileInformation($taintedFiles)
  {
    
    return swToolboxFormHelper::convertFileInformation($taintedFiles);
  }
  
  /**
   * Listen to the configuration.method_not_found to extends 
   * the configuration object
   *
   * @param sfEvent $event
   */
  static function configurationMethodNotFound(sfEvent $event)
  {
    $params = $event->getParameters();
    
    switch($params['method'])
    {
      case 'registerZend':
        self::registerZend($event);
        break;
    }
  }
  
  /**
   * Listen to the component.method_not_found to extends 
   * the configuration object
   *
   * @param sfEvent $event
   */
  static function componentMethodNotFound(sfEvent $event)
  {
    $params = $event->getParameters();
    
    switch($params['method'])
    {
      case 'sendEmail':
      case 'sendMail':
        $event->setReturnValue(self::sendMailFromEvent($event));
        break;
    }

  }
  
  static private $zendLoaded = false;
  
  /**
   * Register zend
   *
   * @param sfEvent $event
   */
  static function registerZend(sfEvent $event)
  {
    $event->setProcessed(true);
    
    if (self::$zendLoaded)
    {
    
      return;
    }
    
    if(sfConfig::get('app_swToolbox_register_zend', true))
    {
      set_include_path(sfConfig::get('sf_lib_dir').'/vendor'.PATH_SEPARATOR.get_include_path());
    }

    if(!sfAutoload::getInstance()->autoload('Zend_Loader'))
    {
      throw new LogicException('Please install Zend Framework Library inside : '.sfConfig::get('sf_lib_dir').'/vendor');
    }
    
    self::$zendLoaded = true;
  }
  
  static public function sendMail($moduleName, $actionName, $vars)
  {
    $config = sfConfig::get('app_swToolbox_mail');
    $context = sfContext::getInstance();
        
    // 2. REGISTER ZEND CLASS
    $context->getConfiguration()->registerZend();
    
    // 3. CREATE THE ACTION
    $action = $context->getController()->getAction($moduleName, $actionName);
    
    // check for a module config.php
    $moduleConfig = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/config/config.php';
    if (is_readable($moduleConfig))
    {
      require_once($moduleConfig);
    }
    
    // 4. EXECUTE THE ACTION
    $action->getVarHolder()->add($vars);
    $action->execute($context->getRequest());

    // 5. RENDER THE MAIL
    $view = new swMailView($context, $moduleName, $actionName, 'swMailView');

    foreach($action->getVarHolder()->getAll() as $name => $value)
    {
      $view->setAttribute($name, $value);
    }
    
    // define decorator
    if($config['decorator']['enabled'])
    {
      $view->setDecorator(true);
      $view->setDecoratorDirectory($config['decorator']['directory']); 
    }
    else
    {
      $view->setDecorator(false);
    }
    
    // text version
    try 
    {
      $template = $actionName.'Success.text.php';
      $template_dir = $context->getConfiguration()->getTemplateDir($moduleName, $template);
      
      $view->setDirectory($template_dir);
      $view->setTemplate($template);
      if($view->isDecorator())
      {
        $view->setDecoratorTemplate($config['decorator']['template'].'.text.php');
      }
      
      $text_version = $view->render($action->getVarHolder()->getAll());
      
      $action->mail->setBodyText($text_version, $config['charset'], $config['encoding']);
    } 
    catch(sfRenderException $e)
    {}

    // html version
    try {
      $template = $actionName.'Success.html.php';
      $template_dir = $context->getConfiguration()->getTemplateDir($moduleName, $template);
      
      $view->setDirectory($template_dir);
      $view->setTemplate($template);
      if($view->isDecorator())
      {
        $view->setDecoratorTemplate($config['decorator']['template'].'.html.php');
      }
      
      $html_version = $view->render($action->getVarHolder()->getAll());
      
      $action->mail->setBodyHtml($html_version, $config['charset'], $config['encoding']);
    }
    catch(sfRenderException $e)
    {}

    // 6. SEND THE MAIL
    $transport_class = $config['transport']['class'];
    $transport_settings = $config['transport']['parameters'];
    
    if(!is_array($transport_settings))
    {
      $transport_settings = array($transport_settings);
    }
    
    if(!sfAutoload::getInstance()->loadClass($transport_class))
    {
      throw new LogicException('Please configure the mail swToolboxPlugin settings');
    }
    
    $reflection_class = new ReflectionClass($transport_class);
    $transport_class = $reflection_class->newInstanceArgs($transport_settings);
    
    $action->mail->send($transport_class);
    
    return $action->mail;
  }
  
  static public function sendMailFromEvent(sfEvent $event)
  {
    $event->setProcessed(true);
    
    $params = $event->getParameters();
    if(count($params['arguments']) == 3)
    {
      list($moduleName, $actionName, $vars) = $params['arguments'];
    }
    else
    {
      list($moduleName, $actionName) = $params['arguments'];
      $vars = array();
    }
    
    return self::sendMail($moduleName, $actionName, $vars);
  }
}