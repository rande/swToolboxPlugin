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
 * The result is a sfDoctrinePager
 * 
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com> 
 *
 * SVN : $Id$
 */
abstract class swDoctrineDatagrid extends sfForm
{
  protected $pager;
  protected $filters;
  
  public function __construct($params = array(), $options = array(), $CSRFSecret = null)
  {
    parent::__construct(array(), $options, $CSRFSecret);

    // add page
    $page = (isset($options['page']) ? $options['page'] : null);
    $this->addFilter('_page', $page, new sfWidgetFormInputHidden, new sfValidatorPass);
    $params['_page'] = $page;
    
    // add sort option
    $order_by = (isset($options['order_by']) ? $options['order_by'] : null);
    $this->addFilter('_order_by', $page, new sfWidgetFormInputHidden, new sfValidatorPass);
    $params['_order_by'] = $page;
    
    $order_by = (isset($options['order_field']) ? $options['order_field'] : null);
    $this->addFilter('_order_field', $page, new sfWidgetFormInputHidden, new sfValidatorPass);
    $params['_order_field'] = $page;
    
    // init values
    $defaults = $this->prepareDefaultValues($params);
    $this->setDefaults($defaults);
    $this->setStoredValues($defaults);

    $this->init();
  }

  public function addFilter($name, $default_value, $widget, $validator)
  {
    $this->filters[$name] = $default_value;
    $this->widgetSchema[$name] = $widget;
    $this->validatorSchema[$name] = $validator;
  }
  
  public function setup()
  {
    // format the datagrid
    $this->widgetSchema->addFormFormatter('swSchemaFormatterDatagrid', new swSchemaFormatterDatagrid($this->widgetSchema));
    $this->widgetSchema->setFormFormatterName('swSchemaFormatterDatagrid');
    $this->widgetSchema->setNameFormat('filters[%s]');
    
    $this->setupDatagrid();
  }

  public function init()
  {
    $this->bind($this->getDefaults());
    $this->preparePager();
  }
  
  public function prepareDefaultValues(array $params)
  {
    $session_values = $this->getStoredValues();

    if(array_key_exists('reset', $params))
    {
      $reset = true;
      $session_values = $params = array();
    }

    $filters = array();

    $base = count($params) <= 1 ? $session_values : $params;
    
    foreach($this->filters as $name => $value)
    {
      $filters[$name] = isset($base[$name]) ? $base[$name] : $value;
    }
    
    return $filters;
  }

  public function getStoredValues()
  {
    return sfContext::getInstance()->getUser()->getAttribute('filters', array(), get_class($this));
  }

  public function setStoredValues($values)
  {
    sfContext::getInstance()->getUser()->setAttribute('filters', $values, get_class($this));
  }

  public function configure()
  {
    $this->configureDatagrid();

    
  }

  abstract function getModelName();

  function setupDatagrid() {}

  function configureDatagrid() {}

  function buildQuery(Doctrine_Query $query) {

    return $query;
  }

  public function preparePager()
  {
    $this->pager = new sfDoctrinePager($this->getModelName());
    $this->pager->setPage($this->getValue('page'));
    $query = $this->pager->getQuery();
    $this->pager->setQuery($this->buildQuery($query));
    
    $this->pager->setMaxPerPage($this->getOption('per_page', 25));
    $this->pager->init();
  }

  public function getPager()
  {
    return $this->pager;
  }
  
  // PAGER PROXY METHODS
  public function getResults()
  {
    return $this->pager->getResults();
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