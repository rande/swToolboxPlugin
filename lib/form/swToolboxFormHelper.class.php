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
   * As sf1.1.X, there is a bug with associative files upload
   * 
   * solution from http://fr2.php.net/manual/en/features.file-upload.multiple.php
   */
  static function convertFileInformation($taintedFiles)
  {
    // the bug has been fixed in 1.2.7
    // see http://trac.symfony-project.org/ticket/5075
    if(version_compare(SYMFONY_VERSION, '1.2.6', '>'))
    {
      
      return $taintedFiles;
    }
    
    $newOrdering = array();
    foreach(array_keys($taintedFiles) as $attr)
    {
       self::groupFileInfoByVariable($newOrdering, $taintedFiles[$attr], $attr);
    }
    
    return $newOrdering;
  }
  
  static private function groupFileInfoByVariable(&$top, $info, $attr) {
    if (is_array($info)) {
      foreach ($info as $var => $val) {
        if (is_array($val)) {
          self::groupFileInfoByVariable($top[$var], $val, $attr);
        } else {
          $top[$var][$attr] = $val;
        }
      }
    } else {
      $top[$attr] = $info;
    }

    return true;
  }
  
  /**
   * Reset the form label
   *
   */
  static public function resetFormLabels(sfForm $form, array $options = array())
  {
    $options['prefix']           = isset($options['prefix']) ? $options['prefix'] : sfConfig::get('app_swToolbox_form_label_prefix', 'label_');;
    $options['catalogue']        = isset($options['catalogue']) ? $options['catalogue'] : false;
    $options['mandatory_format'] = isset($options['mandatory_format']) ? $options['mandatory_format'] : sfConfig::get('app_swToolbox_form_mandatory_format', '%s');
    $options['force_labels']     = isset($options['force_labels']) ? $options['force_labels'] : array();
    
    $callable = sfWidgetFormSchemaFormatter::getTranslationCallable();

    if(!$callable instanceof swResetLabelTranslation)
    {
      $proxier_callable = new swResetLabelTranslation($callable, $options['mandatory_format']);
      sfWidgetFormSchemaFormatter::setTranslationCallable($proxier_callable);
    }
    
    $callable = sfWidgetFormSchemaFormatter::getTranslationCallable();
    
    self::resetSchemaLabels($form->getWidgetSchema(), $form->getValidatorSchema(), $options);
  }
  
  static private function resetSchemaLabels(sfWidgetFormSchema $widget_schema, sfValidatorSchema $validator_schema, array $options)
  {
    if($options['catalogue'] !== false)
    {
      $widget_schema->getFormFormatter()->setTranslationCatalogue($options['catalogue']);
    }
    
    foreach($widget_schema->getFields() as $name => $child_widget_schema)
    { 
      $text_label = isset($options['force_labels'][$name]) ? $options['force_labels'][$name] : strtolower($options['prefix'].$name);

      if(isset($validator_schema[$name]) && $validator_schema[$name]->getOption('required'))
      {
        $label = new swFormLabel($text_label, true, $options['mandatory_format']);
      }
      else
      {
        $label = new swFormLabel($text_label);
      }
      
      $child_widget_schema->setLabel($label);
      
      if($child_widget_schema instanceof sfWidgetFormSchema)
      {
        self::resetSchemaLabels($child_widget_schema, $validator_schema[$name], $options);
      }
    }
  }
  
  static public function generateJsonInformation($form)
  {
    $errors = array();
    $bound = $form->isBound();
    
    if(count($form->getErrorSchema()->getErrors()) > 0)
    {
      foreach($form->getErrorSchema()->getErrors() as $field => $error)
      {
        $errors[$field] = $error->getMessage();
      }
    }
    
    $information = array(
      'hasErrors' => count($form->getErrorSchema()->getErrors()) > 0 ? true : false,
      'errors' => $errors,
      'bound'  => $bound,
      'values' => $form->getValues(),
      //'default' => $form->getDefaults(),
    );
    
    return json_encode($information);
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
  
  /**
   * Force the form to only use specifics fieds and optionaly reorder the form
   *
   * @param sfForm $form
   * @param array $fields
   * @param boolan $use_order
   */
  public static function useOnly(sfForm $form, array $fields = array(), $use_order = false)
  {
    
    foreach($form as $field => $widget)
    {
      if(!in_array($field, $fields) && !in_array($field, array('sort_by', 'sort_order')))
      {

        $form->offsetUnset($field);

        continue;
      }
    }
    
    if($use_order)
    {
      foreach($fields as $pos => $field)
      {
        $form->getWidgetSchema()->moveField($field, sfWidgetFormSchema::LAST);
      }
    }
  }
}