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
 * This class attempt to append raw SQL into a Doctine_Query, SQL added with addSqlQueryPart
 * is removed when the SQL query is created.
 * 
 * @package    swToolboxPlugin
 * @subpackage doctrine
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swQuery extends Doctrine_Query
{

  protected $_sql_append_parts = array(
    'from'      => array(),
    'where'     => array(),
  );

  /**
   * Append SQL into the generated SQL from DQL Part
   * The options arguments can be use to load related information from the SQL
   * 
   * Sample :
   * $query->appendSqlPart(
   *     'from', 
   *     'LEFT JOIN my_table mt ON ( r.id = mt.object_id AND mt.object_type = 1 )',
   *     array(
   *       'addTableAlias' => array('mt', 'MyTable'),
   *       'addQueryComponent' => array('MyTable', array('table' => Doctrine::getTable('MyTable'), 'map' => null))
   *     )
   *   );
   *
   * @param string $name
   * @param string $sql
   * @param array $options
   */
  public function appendSqlPart($name, $sql, array $options = array())
  {
    if (!isset($this->_sql_append_parts[$name])) {
      throw new Doctrine_Query_Exception('Unknown query part ' . $name);
    }
    
    $this->_sql_append_parts[$name][] = array($sql, $options);
  }
  
  /**
   * Retrieve the select part of current DQL
   *
   * @return array of selected elements
   */
  public function getSelect()
  {
    return $this->_dqlParts['select'];
  }
  
  /**
   * Execute the native _processDqlQueryPart method and append the raw SQL
   *
   * @see Doctrine_Query
   */
  protected function _processDqlQueryPart($queryPartName, $queryParts)
  {
    parent::_processDqlQueryPart($queryPartName, $queryParts);
    
    if(array_key_exists($queryPartName, $this->_sql_append_parts))
    {
      
      foreach($this->_sql_append_parts[$queryPartName] as $value)
      {
        list($sql, $options) = $value;
        
        foreach($options as $name => $values)
        {
          if(is_callable(array($this, $name)))
          {
            call_user_func_array(array($this, $name), $values);
          }
        }
        
        $this->addSqlQueryPart($queryPartName, $sql);
      }
    }
  }
  
  protected function addQueryComponent($componentAlias, $options)
  {
    $this->_queryComponents[$componentAlias] = $options; 
  }
}