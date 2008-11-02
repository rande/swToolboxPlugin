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
class baseSwToolboxActions extends sfActions
{
  
  /**
   * Call back action which is called by a widget with the 'onchange' property
   *
   * @param sfRequest $request
   * @return string json
   */
  public function executeRetrieveDynamicValues($request)
  {
    
    $class_path = $request->getParameter('_sw_class');
    $format     = $request->getParameter('_sw_format');
    $from       = $request->getParameter('_sw_from');
    $elements   = explode(',', $request->getParameter('_sw_elements', ''));
    
    // get the from : the modified field
    if(!ereg(".*\[(.*)\]", $from, $results) || count($results) != 2)
    {
      return sfView::NONE;
    }
    
    $from = $results[1];

    // get and load the form class
    $class = $class_path;
    $path = explode('/', $class_path);
    if(count($path) == 2)
    {
      $class = $path[1];
    }
      
    $loader = sfAutoload::getInstance();
    
    if(!$loader->loadClass($class_path))
    {
      // not a module class, try the global scope
      if(!$loader->loadClass($class))
      {
        return sfView::NONE;
      }
    }
    
    $form = new $class;
    $form->setDefaults($request->getParameter($format));
    
    if(!method_exists($form, 'getDynamicValues'))
    {
      return sfView::NONE;
    }
    
    $values = $form->getDynamicValues($from, $elements);
    
    echo json_encode($values);

    return sfView::NONE;
    
  }
}