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

    return trim($text);
  }
  
  /**
   * Helper method to be call inside a form. This is part of the "Dynamic Form Values" form feature
   *
   * @param sfForm $form
   * @param sfWidget $widget
   * @param array $elements
   */
  static function updateFormElements(sfForm $form, sfWidget $widget, array $elements)
  {
    $context = sfContext::getInstance();
    
    // add external javascript files
    $context->getResponse()->addJavascript('/js/jQuery/jquery.min.js');
    $context->getResponse()->addJavascript('/swToolboxPlugin/js/swToolbox.js', 'last');
    
    // find the form format
    $format = $form->getWidgetSchema()->getNameFormat();
    if(($pos = strpos($format, '[')) > 0)
    {
      $format = substr($format, 0, $pos);
    }
    
    // define the class
    $class = $context->getModuleName().'/'.get_class($form);

    // define the callback url
    $url = $context->getController()->genUrl('@sw_toolbox_retrieve_dynamic_values');
    
    // add the onchange event listener to the widget
    $widget->setAttribute('onchange', 'swToolbox.updateFormElements("'.$url.'", this, "'.$class.'", "'.$format.'", ["'.implode('", "', $elements).'"]);');
  }
}