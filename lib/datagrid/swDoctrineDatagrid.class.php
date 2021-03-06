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
abstract class swDoctrineDatagrid extends swModelDatagrid
{

  protected
    $query = null;
  
    /**
   * build the query that will be handle by the pager
   * 
   * @param $query
   * @return query
   */
  function buildQuery(Doctrine_Query $query) 
  {

    return $query;
  }

  /**
   * Query to return when the datagrid is not valid
   * 
   * @return Doctrine_Query
   */
  public function getInvalidQuery()
  {
    $query = $this->getBaseQuery();
    $query->addWhere('1 <> 1'); // set an impossible query
    
    return $query;
  }
  
  
  /**
   * return the base query
   *
   * @return Doctrine_Query
   */
  public function getBaseQuery()
  {
    
    return Doctrine::getTable($this->getModelName())->createQuery();
  }
  
  /**
   * return the final query used by the pager
   *
   * @return Doctrine_Query
   */
  public function getQuery()
  {

    if(!$this->query)
    {
      $this->query = $this->isValid() ? $this->buildQuery($this->getBaseQuery()) : $this->getInvalidQuery();
    }
    
    return clone $this->query;
  }
  
  /**
   * prepare the pager once everything is setup
   */
  public function preparePager()
  {
    $page     = $this->getOption('page');
    $per_page = $this->getOption('per_page', 25);
    $query    = $this->getQuery();
    $class    = $this->getOption('pager_class', 'sfDoctrinePager'); // allow to pass custom class

    // define the hydration mode
    $query->setHydrationMode($this->getOption('hydration_mode', Doctrine::HYDRATE_RECORD));
    
    $this->pager = new $class($this->getModelName());
    $this->pager->setPage($page);
    $this->pager->setQuery($query);
    $this->pager->setMaxPerPage($per_page);
    $this->pager->init();
  }

  // PAGER PROXY METHODS
  public function getResults()
  {
    if($this->cached_results == false)
    {
      $args = func_num_args() == 0 ? array($this->getOption('hydration_mode', Doctrine::HYDRATE_RECORD)) : func_get_args();
      
      $this->cached_results = call_user_func_array(array($this->pager, 'getResults'), $args);
    }
    
    return $this->cached_results;
  }
}