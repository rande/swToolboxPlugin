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
 * Set the title of the current page
 *   usage : <h1><?php echo sw_t('Blog Posts') ?></h1>
 * 
 * return string the title provided
 */
function sw_t($title)
{

  if(sfConfig::get('app_swToolbox_page_title_enabled', true))
  {
    sfContext::getInstance()->getResponse()->setTitle($title.sfConfig::get('app_swToolbox_page_title_suffix', ''));
  }
  
  return $title;
}


/**
 * Original code from sfPagerNavigationPlugin, available at http://www.symfony-project.org/plugins/sfPagerNavigationPlugin
 * Copyright (c) 2004-2007 Francois Zaninotto
 * 
 * Adapted to use symfony 1.2 features.
 * 
 * Outputs a regular navigation navigation.
 * It outputs a series of links to the first, previous, next and last page
 * as well as to the 5 pages surrounding the current page.
 *
 * @param  object sfPager object of the current pager 
 * @param  string 'module/action' or 'rule' of the paginated action
 * @param  array parameters to create the final url
 * @param  array of options to render the link_to element
 * 
 * @return string XHTML code containing links
 */
function sw_pager_navigation()
{
  $arguments = func_get_args();
  
  if (empty($arguments[1]) || '@' == substr($arguments[1], 0, 1) || false !== strpos($arguments[1], '/'))
  {
    return call_user_func_array('sw_pager_navigation1', $arguments);
  }
  else
  {
    
    if (!array_key_exists(3, $arguments))
    {
      $arguments[3] = array();
    }
    
    return call_user_func_array('sw_pager_navigation2', $arguments);
  }
}

/**
 * Original code from sfPagerNavigationPlugin, available at http://www.symfony-project.org/plugins/sfPagerNavigationPlugin
 * Copyright (c) 2004-2007 Francois Zaninotto
 * 
 * Outputs a regular navigation navigation.
 * It outputs a series of links to the first, previous, next and last page
 * as well as to the 5 pages surrounding the current page.
 *
 * @param  object sfPager object of the current pager 
 * @param  string 'module/action' or '@rule' of the paginated action
 * @return string XHTML code containing links
 */
function sw_pager_navigation1($pager, $uri)
{
  $navigation = '';
 
  if ($pager->haveToPaginate())
  {  
    $uri .= (preg_match('/\?/', $uri) ? '&' : '?').'page=';
 
    // First and previous page
    if ($pager->getPage() != 1)
    {
      $navigation .= link_to(image_tag(sfConfig::get('sf_admin_web_dir') . '/images/first.png', 'align=absmiddle'), $uri.'1');
      $navigation .= link_to(image_tag(sfConfig::get('sf_admin_web_dir') . '/images/previous.png', 'align=absmiddle'), $uri.$pager->getPreviousPage()).'&nbsp;';
    }
 
    // Pages one by one
    $links = array();
    foreach ($pager->getLinks() as $page)
    {
      $links[] = link_to_unless($page == $pager->getPage(), $page, $uri.$page);
    }
    $navigation .= join('&nbsp;&nbsp;', $links);
 
    // Next and last page
    if ($pager->getPage() != $pager->getLastPage())
    {
      $navigation .= '&nbsp;'.link_to(image_tag(sfConfig::get('sf_admin_web_dir') . '/images/next.png', 'align=absmiddle'), $uri.$pager->getNextPage());
      $navigation .= link_to(image_tag(sfConfig::get('sf_admin_web_dir') . '/images/last.png', 'align=absmiddle'), $uri.$pager->getLastPage());
    }
 
  }
 
  return $navigation;
}

/**
 * Original code from sfPagerNavigationPlugin, available at http://www.symfony-project.org/plugins/sfPagerNavigationPlugin
 * Copyright (c) 2004-2007 Francois Zaninotto
 * 
 * Adapted to use symfony 1.2 features.
 * 
 * Outputs a regular navigation navigation.
 * It outputs a series of links to the first, previous, next and last page
 * as well as to the 5 pages surrounding the current page.
 *
 * @param  object sfPager object of the current pager 
 * @param  string 'module/action' or 'rule' of the paginated action
 * @param  array parameters to create the final url
 * @param  array of options to render the link_to element
 * 
 * @return string XHTML code containing links
 */
function sw_pager_navigation2($pager, $uri, $params = array(), $options = array())
{
  $navigation = '';
 
  if ($pager->haveToPaginate())
  {  
    // First and previous page
    if ($pager->getPage() != 1)
    {
      $params['page'] = 1; 
      $navigation .= link_to2(image_tag(sfConfig::get('sf_admin_web_dir') . '/images/first.png', 'align=absmiddle'), $uri, $params, $options);
      
      $params['page'] = $pager->getPreviousPage();
      $navigation .= link_to2(image_tag(sfConfig::get('sf_admin_web_dir') . '/images/previous.png', 'align=absmiddle'), $uri, $params, $options).'&nbsp;';
    }
 
    // Pages one by one
    $links = array();
    foreach ($pager->getLinks() as $page)
    {
      $params['page'] = $page;
      $links[] = link_to2($page, $uri, $params, $options);
    }
    $navigation .= join('&nbsp;&nbsp;', $links);
 
    // Next and last page
    if ($pager->getPage() != $pager->getLastPage())
    {
      $params['page'] = $pager->getNextPage();
      $navigation .= '&nbsp;'.link_to2(image_tag(sfConfig::get('sf_admin_web_dir') . '/images/next.png', 'align=absmiddle'), $uri, $params, $options);
      
      $params['page'] = $pager->getLastPage();
      $navigation .= link_to2(image_tag(sfConfig::get('sf_admin_web_dir') . '/images/last.png', 'align=absmiddle'), $uri, $params, $options);
    }
 
  }
 
  return $navigation;
}

function sw_get_api_loader_config()
{
  static $config;
  
  if(is_null($config))
  {
    $config = sfConfig::get('app_swToolbox_api_loader');
    if($config == null)
    {
      throw new RuntimeException('app_swToolbox_api_loader is null');
    }

    $host = sfContext::getInstance()->getRequest()->getHost();

    if(!array_key_exists($host, $config))
    {
      throw new RuntimeException('no configuration set for the current host');
    }
   
    $config = $config[$host];
  }
  
  return $config;
}
/**
 *
 * Insert the google map api script into the page
 **/
function sw_google_map_api()
{
  
  $config = sw_get_api_loader_config();
  
  if(!array_key_exists('google_api_key', $config))
  {
    
    return '';
  }
  
  $map_url = $config['google_map_url'];
  $api_version = $config['google_map_version'];
  $key     = $config['google_api_key'];
  
  return sprintf('<script src="%s?file=api&amp;v=%s&amp;sensor=false&amp;key=%s" type="text/javascript"></script>',
    $map_url,
    $api_version,
    $key
  );
}

function sw_google_analytics($version = 'ga')
{
  $config = sw_get_api_loader_config();
  
  if(!array_key_exists('google_analytics', $config))
  {
    
    return '';
  }
  
  $keys = $config['google_analytics'];
  if(!is_array($keys))
  {
    $keys = array($keys);
  }
  
  $html = '';
  if($version == 'urchin')
  {
    $html .= '<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>';
    
    foreach($keys as $key)
    {
      $html .= sprintf('<script type="text/javascript">_uacct = "%s"; urchinTracker();</script>', $key);
    }
  }
  
  if($version == 'ga')
  {
    $html .= '<script type="text/javascript">';
    $html .= 'var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");';
    $html .= 'document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));';
    $html .= '</script>';
    
    foreach($keys as $key)
    {
      $html .= sprintf('<script type="text/javascript">var pageTracker = _gat._getTracker("%s"); pageTracker._trackPageview();</script>', $key);
    }
  }
  
  return $html;
}

function sw_get_user_notice()
{
  
  $user = sfContext::getInstance()->getUser();
  $html = '';
  
  if($user->hasFlash('notice-ok') || $user->hasFlash('sw-notice-ok'))
  {
    $html .= '<div class="sw-notice sw-notice-ok">' . $user->getFlash('notice-ok').$user->getFlash('sw-notice-ok').'</div>';
  }
  
  if($user->hasFlash('notice-error') || $user->hasFlash('sw-notice-error'))
  {
    $html .= '<div class="sw-notice sw-notice-error">' . $user->getFlash('notice-error').$user->getFlash('sw-notice-error').'</div>';
  }
  
  if(strlen($html) > 0)
  {
    $html = '<div class="sw-notices">'.$html.'</div>';
  }
  
  return $html;
}