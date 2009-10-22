<?php

/**
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
 * 
 */

/**
 * Create a datagrid based on sfForm framework from symfony-project
 * 
 * The sfForm is used to define filters and display the filters elements.
 * 
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com> 
 *
 * SVN : $Id$
 */
abstract class swDatagrid extends sfForm
{

  protected 
    $pager,
    $filters = array(),
    $schema_format_name = 'filters',
    $cached_results = false
  ;
  
  /**
   * @see sfFrom
   * 
   */
  public function __construct($params = array(), $options = array(), $CSRFSecret = null)
  {
    // validator options
    $options['allow_extra_fields'] = isset($options['allow_extra_fields']) ? $options['allow_extra_fields'] : true;
    $options['filter_extra_fields'] = isset($options['filter_extra_fields']) ? $options['filter_extra_fields'] : false;
    $options['disabled_init'] = isset($options['disabled_init']) ? $options['disabled_init'] : false;
    
    parent::__construct(array(), $options, $CSRFSecret);

    // init sort features
    $params['sort_by'] = isset($params['sort_by']) ? $params['sort_by'] : null;
    $params['sort_order'] = isset($params['sort_order']) ? $params['sort_order'] : null;
   
    // init values
    $defaults = $this->prepareDefaultValues($params);
    $this->setDefaults($defaults);
    $this->setStoredValues($defaults);

    if(!$options['disabled_init'])
    {
      $this->init();
    }
    
  }

  /**
   * 
   * add a new filter field into the datagrid
   * 
   * @param string $name name of the field
   * @param mixed $default_value 
   * @param sfFormWidget $widget
   * @param sfValidatorBase $validator
   * @param string $label
   * 
   */
  public function addFilter($name, $default_value, $widget, $validator, $label = null)
  {
    
    $this->filters[$name] = $default_value;
    $this->widgetSchema[$name] = $widget;
    $this->validatorSchema[$name] = $validator;
    
    if($label)
    {
      $this->widgetSchema->setLabel($name, $label);
    }
  }
  
  
  /**
   * 
   * @param $field
   * @return boolean true if the sortable field exists
   */
  public function hasSortableField($field)
  {
    
    return array_key_exists($field, $this->getSortableFields());
  }
  
  /**
   * 
   * @return array of sortable field
   */
  public function getSortableFields()
  {
    $fields = array();
    
    foreach ($this->widgetSchema->getFields() as $name => $widget)
    {
      if(in_array($name, array('sort_by', 'sort_order')))
      {
        
        continue;
      }
      
      $fields[$name] = $widget->getLabel() ? $widget->getLabel() : $name; 
    }
    
    return $fields;
  }
  
  public function getSortBy()
  {
    return $this->getValue('sort_by');
  }
  
  public function getSortOrder()
  {
    return $this->getValue('sort_order');
  }
  
  /**
   * 
   * Get the url for the current sort field
   * 
   * @param $url
   * @param $sort_by
   * @param $sort_order
   * @return unknown_type
   */
  public function getSortUrl($url, $sort_by, $sort_order = null)
  {

    if(!$this->hasSortableField($sort_by))
    {
      
      return $text;
    }
    
    if(is_null($sort_order))
    {
      $sort_order = $this->getValue('sort_by') == $sort_by ? ($this->getValue('sort_order') == 'ASC' ? 'ASC' : 'DESC') : 'DESC';
    }
    
    $url .= '?' . $this->getQueryString(array(
      'sort_by'    => $sort_by,
      'sort_order' => $sort_order
    ));
    
    return url_for($url);
  }
  
  /**
   * 
   * get the link for the current sort field
   * 
   * @param $text
   * @param $url
   * @param $sort_by
   * @param $sort_order
   * @param $options
   * @return unknown_type
   */
  public function getSortLink($text, $url, $sort_by, $sort_order = null, $options = array())
  {
    if(!$this->hasSortableField($sort_by))
    {
      
      return $text;
    }
    
    if(is_null($sort_order))
    {
      $sort_order = $this->getValue('sort_by') == $sort_by ? ($this->getValue('sort_order') == 'ASC' ? 'DESC' : 'ASC') : 'DESC';
    }
    
    $url .= '?' . $this->getQueryString(array(
      'sort_by'    => $sort_by,
      'sort_order' => $sort_order 
    ));
     
    return link_to($text, $url , $options);
  }
  
  /**
   * 
   * @see vendor/symfony/lib/form/sfForm#setup()
   */
  public function setup()
  {
    // format the datagrid
    $this->widgetSchema->addFormFormatter('swSchemaFormatterDatagrid', new swSchemaFormatterDatagrid($this->widgetSchema));
    $this->widgetSchema->setFormFormatterName('swSchemaFormatterDatagrid');
    $this->widgetSchema->setNameFormat($this->schema_format_name.'[%s]');
    
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('datagrid');
    
    $this->validatorSchema->setOption('allow_extra_fields', $this->getOption('allow_extra_fields'));
    $this->validatorSchema->setOption('filter_extra_fields', $this->getOption('filter_extra_fields'));
    
    $this->addFilter(
      'sort_by', 
      null, 
      new sfWidgetFormSelect(array(
        'choices' => $this->getSortableFields()
      )), 
      new sfValidatorChoice(array(
        'choices' => array_keys($this->getSortableFields()),
        'required' => false
      ))
    );
    
    $this->addFilter(
      'sort_order', 
      null,
      new sfWidgetFormSelect(array(
        'choices' => array('ASC' => 'ASC', 'DESC' => 'DESC')
      )), 
      new sfValidatorChoice(array(
        'choices' => array('ASC', 'DESC'),
        'required' => false
      ))
    );
    
    $this->setupDatagrid();
  }

  /**
   * 
   * init the datagrid with the values
   * 
   * @return unknown_type
   */
  public function init()
  {
    $this->bind($this->getDefaults());
    
    $this->preparePager();
  }
  
  /**
   * define the values to use in the datagrid
   * 
   * @param $params
   * @return unknown_type
   */
  public function prepareDefaultValues(array $params)
  {

    $session_values = array();
    
    if($this->getOption('store', false))
    {
      $session_values = $this->getStoredValues();
    }

    if(array_key_exists('reset', $params))
    {
      $reset = true;
      $session_values = $params = array();
    }

    $base = count($params) == 2 ? $session_values : $params;
    
    $filters = array();
    
    foreach($this->filters as $name => $value)
    {
      $filters[$name] = array_key_exists($name, $base) ? $base[$name] : $value;
    }

    return $filters;
  }

  /**
   * return the namespace used by the session storage to store the values
   * 
   * @return string
   */
  public function getStoreNamespace()
  {
    
    return $this->getOption('store_namespace', get_class($this));
  }
  
  /**
   * return the value store into the session storage
   * 
   * @return array of default values
   */
  public function getStoredValues()
  {
    if($this->getOption('store', false) == false)
    {
      
      return array();
    }
    
    return sfContext::getInstance()->getUser()->getAttribute('filters', array(), $this->getStoreNamespace());
  }

  /**
   * define values to store into the session storage
   * 
   * 
   * @param $values
   * @return void
   */
  public function setStoredValues($values)
  {
    
    if($this->getOption('store', false) == false)
    {
      
      return ;
    }
    
    sfContext::getInstance()->getUser()->setAttribute('filters', $values, $this->getStoreNamespace());
  }

  public function configure()
  {
  
    $this->configureDatagrid();
    
  }

  /**
   * remove fields, use this method if you extends a base datagrid and you want
   * to remove any extra fields
   * 
   * @param array $fields
   * @param boolean $use_order
   * @return void
   */
  public function useOnly(array $fields = array(), $use_order = false)
  {
    
    foreach($this as $field => $widget)
    {
      if(!in_array($field, $fields) && !in_array($field, array('sort_by', 'sort_order')))
      {

        $this->offsetUnset($field);

        continue;
      }
    }
    
    if($use_order)
    {
      foreach($fields as $pos => $field)
      {
        $this->widgetSchema->moveField($field, sfWidgetFormSchema::LAST);
      }
    }
  }
  

  /**
   * return the query parameters that can be use in url
   * 
   * @param array $merge
   * @return array
   */
  function getQueryParameters($merge = array())
  {
    
    $value = array_merge($this->getValues(), $merge);
  
    if(strlen($this->schema_format_name) > 0)
    {
      
      $value = array($this->schema_format_name => $value);
    }
    
    return $value;
  }
  
  /**
   * return the values as a valid http query string
   * 
   * @param $merge
   * @return string
   */
  function getQueryString($merge = array())
  {
    
    return http_build_query($this->getQueryParameters($merge));
  }
  
  function setupDatagrid() {}

  function configureDatagrid() {}
  

  /**
   * return the yaml version of the current values
   * 
   * @return unknown_type
   */
  public function getYaml($inline = 2, $indent = 0)
  {
    $yaml = new sfYamlDumper();

    return $yaml->dump($this->getValues(), $inline, $indent);
  }
  
  public function getPager()
  {
    
    return $this->pager;
  }
  
  // PAGER PROXY METHODS
  public function getResults()
  {
    if($this->cached_results == false)
    {
      $this->cached_results =  $this->pager->getResults();
    }
    
    return $this->cached_results;
  }

  public function resetCachedResults()
  {
    
     $this->cached_results = false;
  }

  public function haveToPaginate()
  {
    
    return $this->pager->haveToPaginate();
  }
  
  public function getPage()
  {
    return $this->pager->getPage();
  }

  public function getLinks()
  {
    
    return $this->pager->getLinks();
  }
  
  public function getLastPage()
  {
    
    return $this->pager->getLastPage();
  }
  
  public function getFirstPage()
  {
    
    return $this->pager->getFirstPage();
  }
  
  public function getCurrentMaxLink()
  {
    
    return $this->pager->getCurrentMaxLink();
  }
  
  public function getNextPage()
  {
    
    return $this->pager->getNextPage();
  }
  
  public function getPreviousPage()
  {
    
    return $this->pager->getPreviousPage();
  }
  
  public function count()
  {
    
    return $this->pager->getNbResults();
  }

  public function getNbResults()
  {
    
    return $this->pager->getNbResults();
  }
}