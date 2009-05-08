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
 * @subpackage debug
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swFilterFunctionalTest extends sfFilter
{

  private static $code = "";

  protected $with = null;

  const LINK_EREG = '/<a(.*?)href=(\'|")([^> ]*)(\'|")([^>]*)>(.*?)<\/a>/i';

  public function linkCallback($matches)
  {

    $link = $matches[3];

    if(strlen($link) > 0 && $link[0] != '#')
    {
      if(!strpos($link, '?'))
      {
        $link .= '?';
      }
      else
      {
        $link .= '&';
      }

      $link_content = $matches[6];
      if(strpos($link_content, '<img') !== false)
      {
        preg_match('/alt=(\'|")([^$1]*)(\'|")/i', $link_content, $alt_result);

        if(isset($alt_result[2])) 
        {
          $link_content = $alt_result[2];
        }
      } 

      $link .= '_sw_func_link=' . $link_content;
    }

    return sprintf('<a%shref=%s%s%s%s>%s</a>',
      $matches[1],
      $matches[2],
      $link,
      $matches[4],
      $matches[5],
      $matches[6]
    );
  }
  
  public function execute($filterChain)
  {

    $request   = $this->context->getRequest();
    $response  = $this->context->getResponse();
    $user      = $this->context->getUser();

    $is_first_call = $this->isFirstCall();
    if($is_first_call)
    {
      if($request->hasParameter('_sw_func_reset'))
      {
        $user->setAttribute('sw_func_code', '', 'swToolbox');
        $filterChain->execute();

        return;
      }

      if($request->hasParameter('_sw_func_enabled'))
      {
        if((int)$request->getParameter('_sw_func_enabled') == 1)
        {
          $user->setAttribute('sw_func_enabled', true, 'swToolbox');
          $user->setAttribute('sw_func_code', '', 'swToolbox');
          unset($_GET['_sw_func_enabled']);
        }

        if((int)$request->getParameter('_sw_func_enabled') == 0)
        {
          $user->setAttribute('sw_func_enabled', false, 'swToolbox');
          $user->setAttribute('sw_func_code', null, 'swToolbox');
          unset($_GET['_sw_func_enabled']);
        }
      }

      if($user->getAttribute('sw_func_enabled', false, 'swToolbox'))
      {
        self::$code = $user->getAttribute('sw_func_code', '', 'swToolbox');
      }
    }

    if(!$user->getAttribute('sw_func_enabled', false, 'swToolbox'))
    {
      $filterChain->execute();

      return;
    }

    $action = $this->context->getActionStack()->getLastEntry();
    $route = $this->context->getRouting()->getCurrentInternalUri();

    $url = $request->getPathInfo();
    
    if($is_first_call)
    {
      if($from_link = $request->getParameter('_sw_func_link'))
      {
        unset($_GET['_sw_func_link']);
        $this->raw("\$browser");
        $this->with('response');
          $this->create('contains', $from_link);
          $this->create('click', $from_link, $_GET);
        $this->end();

        $this->raw("  /* " . $this->createPHP('call', $url, 'get', $_GET)." */ ");
        $this->raw(";\n\n");
        $this->raw("\$browser");
      }
      else if($user->getAttribute('sw_func_executing', false, 'swToolbox'))
      {
        // behing redirected or an error occur during execution
        $this->raw(";");
        $this->raw("\$browser");
        $this->with('response');
          $this->create('isRedirected', true);
          $this->create('isStatusCode', 302);
        $this->end();
        $this->create('followRedirect');
        $this->raw(";\n\n");
        $this->raw("\$browser");
        $user->setAttribute('sw_func_executing', false, 'swToolbox');
      }
      else if($request->isMethod('post'))
      {
        $this->raw("\$browser");
        $this->create('call', $url, 'post', $this->getVarsFromRequest($request));
        $this->raw(
          "  /* " . 
          $this->createPHP('get', $url) . "\n" .
          $this->createPHP('click', 'alt or value of submit here', $this->getVarsFromRequest($request)) .
          " */ "
        );
      }
      else
      {
        $this->raw("\$browser");
        $this->create('call', $url, 'get',  $_GET);
      }
    }

    if(!$is_first_call)
    {
      $this->raw(";\n\n");
      $this->raw("\$browser");
    }
    
    $this->with('request');    
      if(!$is_first_call)
      {
        $this->create('isForwardedTo', $action->getModuleName(), $action->getActionName());
      } 
      else
      {
        $this->create('isParameter', 'module', $action->getModuleName());
        $this->create('isParameter', 'action', $action->getActionName());        
      }
    $this->end();


    $user->setAttribute('sw_func_code', self::getRawPhp(), 'swToolbox');
    $user->setAttribute('sw_func_executing', true, 'swToolbox');

    $filterChain->execute();

    $user->setAttribute('sw_func_executing', false, 'swToolbox');

    $this->with('response');
      $this->create('isStatusCode', $response->getStatusCode());
    $this->end();
    
    $this->raw(";\n\n");

    $content = $response->getContent();

    // add link name
    $content = preg_replace_callback(self::LINK_EREG, array($this, 'linkCallback'), $content);

    $response->setContent($content);

    $user->setAttribute('sw_func_code', self::getRawPhp(), 'swToolbox');
  }

  public function getVarsFromRequest($request)
  {
    $vars = $request->getParameterHolder()->getAll();
    
    unset(
      $vars['module'],
      $vars['action']
    );
    
    return $vars;
  }
  
  public function with($name)
  {
    $this->with = $name;
    $this->raw("  ->with('$name')->begin()");
  }

  public function end()
  {
    if(!is_null($this->with))
    {
      $this->with = null;
      $this->raw("  ->end()");
    }
  }

  private function raw($code, $rl = null)
  {
     
    if(is_null($rl) && substr($code, -1) != "\n")
    {
      $rl = "\n";
    }

    self::$code .= $code.$rl;
  }

  public static function getRawPhp()
  {

    return self::$code;
  }

  private function createPHP()
  {
    $arguments = func_get_args();
    $method = array_shift($arguments);

    $args = array();
    foreach($arguments as $index => $argument)
    {
      if(is_string($argument))
      {
        $arguments[$index] = var_export($argument, true);

        continue;
      }

      if(is_array($argument))
      {
        if(count($argument) == 0)
        {
          $arguments[$index] = 'array()';
        }
        else
        {
          $arguments[$index] = var_export($argument, true);
        }
      }
    }

    $indent = "  ";
    if(!is_null($this->with))
    {
      $indent = "    ";
    }

    return sprintf("%s->%s(%s)", $indent, $method, implode(", ", $arguments));
  }

  private function create()
  {
    $args = func_get_args();
    $this->raw(call_user_func_array(array($this, 'createPHP'), $args));
  }
}