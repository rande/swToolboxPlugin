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
 * @package    swUserContextCacheFilter
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swUserContextCacheFilter extends sfCacheFilter
{

  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    parent::execute($filterChain);

    $config = sfConfig::get('app_swToolbox_swUserContextCacheFilter', null);

    $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array(
      'swUserContextCacheFilter : starting filter'
    )));
    
    if(!$config)
    {
      $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array(
      'swUserContextCacheFilter : config not defined'
      )));

      return;
    }

    $actionInstance = $this->context->getController()->getActionStack()->getLastEntry()->getActionInstance();

    $module = $actionInstance->getModuleName();
    $action = $actionInstance->getActionName();

    $key = $module.'::'.$action;

    $modules = isset($config['modules']) ? $config['modules'] : null;

    // set action variables
    if($modules && !array_key_exists($key, $modules))
    {
      $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array(
      'swUserContextCacheFilter : not modules key defined : '.$key
      )));

      return;
    }
    else
    {
      $action .= '_UserContext';

      if($this->context->getController()->actionExists($module, $action))
      {
        $action = $this->context->getController()->getAction($module, $action);
        $action->execute($this->request);

        $options = isset($module[$key]['options']) ? $module[$key]['options'] : array();

        $this->executePolicy(
          isset($modules[$key]['policy']) ? $modules[$key]['policy'] : 'replace',
          $action->getVarHolder()->getAll(),
          $options
        );
      }
      else
      {
        $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array(
        'swUserContextCacheFilter : '.$action.' does not exists'
        )));

      }
    }

    // set global variables
    $common = isset($config['common']) ? $config['common'] : null;

    if(!$common || !isset($common['callback']))
    {

      $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array(
        'swUserContextCacheFilter : no callback information defined'
      )));

      return;
    }

    $options = isset($common['options']) ? $common['options'] : array();
    $this->executePolicy(
      isset($common['policy']) ? $common['policy'] : 'replace',
      call_user_func($common['callback'], $this->context),
      $options
    );

  }

  public function executePolicy($policy = 'replace', $vars = array(), $options = array())
  {
    $debug = array();
    foreach($vars as $name => $value)
    {
      $debug[] = '   '.$name .' => ' . $value;
    }
    
    $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array('swUserContextCacheFilter : execute policy : '.$policy.' with vars : ') + $debug));

    $content = $this->context->getResponse()->getContent();

    if($policy == 'json')
    {
      $content = $this->runJsonPolicy($content, $vars, $options);
    }
    else if($policy == 'all')
    {
      $content = $this->runReplacePolicy($content, $vars, $options);
      $content = $this->runJsonPolicy($content, $vars, $options);
    }
    else
    {
      $content = $this->runReplacePolicy($content, $vars, $options);
    }

    $this->context->getResponse()->setContent($content);
  }

  public function runReplacePolicy($content, $vars, $options)
  {
  
    $replace_vars = array();
    foreach($vars as $name => $value)
    {
      $replace_vars['##SW_CACHE_'.strtoupper($name).'##'] = $value;
    }

    return strtr($content, $replace_vars);
  }

  public function runJsonPolicy($content, $vars, $options)
  {
    $vars = json_encode($vars);

    $varname = isset($options['varname']) ? $options['varname'] : 'user_context';

    return $content . "\n<script>var $varname = $vars</script>";
  }
}