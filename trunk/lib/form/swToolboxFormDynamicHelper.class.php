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
 * @subpackage form
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swToolboxFormHelper
{
  /**
   * Helper method to be call inside a form. This is part of the "Dynamic Form Values" form feature
   *
   * @param sfForm $form
   * @param sfWidget $widget
   * @param array $elements
   */
  static function updateFormElement(sfForm $form, array $options)
  {
    
    if(!sfContext::hasInstance())
    {
      
      return;
    }
    
    $widgetSchema = isset($options['widgetSchema']) ? $options['widgetSchema'] : $form->getWidgetSchema();
    $field        = $options['field'];
    $event        = isset($options['event']) ? $options['event'] : 'onchange';
    
    $context = sfContext::getInstance();
    
    // add external javascript files
    $context->getResponse()->addJavascript('/js/jQuery/jquery.min.js');
    $context->getResponse()->addJavascript('/swToolboxPlugin/js/swToolbox.js', 'last');
    
    // define the class
    $class = get_class($form);
    if(strlen($context->getModuleName()) > 0)
    {
       $class = $context->getModuleName().'/'.$class;
    }

    // define the callback url
    $url = $context->getController()->genUrl('@sw_toolbox_retrieve_dynamic_values');
    
    // add the onchange event listener to the widget
    $widgetSchema[$field]->setAttribute($event, 'swToolbox.updateFormElements("'.$url.'", this, "'.$class.'");');
  }

  
  /**
   * return the format used in the bind action
   *
   * @return string or null if a format is required to bound the form
   **/
  static function getBindParameter($format)
  {
    
    if(ereg('([^\[]*)\[%s\]', $format, $results))
    {
    
      return $results[1];
    }
    
    return null;
  }
  
  /**
   * get the widget schema from the name
   *
   * $name = 'myform[field][toto]'
   *
   * @return array with the widgetSchema and the field name
   **/
  static function getWidgetSchemaFromName($form, $name)
  {
    $widgetSchema = null;
    $field        = null;
    $fields       = explode('[',$name);
    $shift        = false;
    
    if(self::getBindParameter($form->getWidgetSchema()->getNameFormat()) !== null)
    {
      array_shift($fields);
      $shift = true;
    }

    if(count($fields) == 1)
    {
      $widgetSchema = $form->getWidgetSchema();
      $field = $shift ? substr($fields[0], 0, -1) : $name;
    }
    else
    {
      $field = substr(array_pop($fields), 0, -1);
      $widget_schema = $form->getWidgetSchema();
      
      foreach($fields as $schema_name)
      {
        $schema_name = substr($schema_name, 0, -1);
        $widget_schema = $widget_schema[$schema_name];
      }
      
      if($widget_schema instanceof sfWidgetFormSchema)
      {
        $widgetSchema = $widget_schema;
      }
    }
    
    if($widgetSchema instanceof sfWidgetFormSchema && !array_key_exists($field, $widgetSchema->getFields()))
    {
      $widgetSchema = null;
      $field        = null;        
    }
    
    return array('widgetSchema' => $widgetSchema, 'field' => $field);
  }
  
  public static function generateValuesById($widgetSchema, $values, &$results = array())
  {
    if(!is_array($values))
    {
      $values = array();
    }
    
    foreach($values as $name => $value)
    {
      if($widgetSchema[$name] instanceof sfWidgetFormSchema)
      {
        self::generateValuesById($widgetSchema[$name], $value, $results);
      }
      else
      {        
        $widget_name = $widgetSchema->generateName($name);
        $id = $widgetSchema->generateId($widget_name);
        
        if(!$value instanceof swFormDynamicResult)
        {
          $value = new swFormDynamicResult($value);
        }
        
        $results[$id] = $value->render($name, $widgetSchema);
      }
    }
    
    return $results;
  }
}