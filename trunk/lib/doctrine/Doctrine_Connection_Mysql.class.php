<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Doctrine_Connection_Mysqlclass
 *
 * @author thomas
 */

class Doctrine_Connection_Mysql extends Doctrine_Connection_Common
{
    /**
     * @var string $driverName                  the name of this connection driver
     */
    protected $driverName = 'Mysql';

    protected 
      $slave = null,
      $transaction_cpt = null;
      
    /**
     * the constructor
     *
     * @param Doctrine_Manager $manager
     * @param PDO|Doctrine_Adapter $adapter     database handler
     */
    public function __construct(Doctrine_Manager $manager, $adapter)
    {
        $this->setAttribute(Doctrine::ATTR_DEFAULT_TABLE_TYPE, 'INNODB');
        $this->supported = array(
                          'sequences'            => 'emulated',
                          'indexes'              => true,
                          'affected_rows'        => true,
                          'transactions'         => true,
                          'savepoints'           => false,
                          'summary_functions'    => true,
                          'order_by_text'        => true,
                          'current_id'           => 'emulated',
                          'limit_queries'        => true,
                          'LOBs'                 => true,
                          'replace'              => true,
                          'sub_selects'          => true,
                          'auto_increment'       => true,
                          'primary_key'          => true,
                          'result_introspection' => true,
                          'prepared_statements'  => 'emulated',
                          'identifier_quoting'   => true,
                          'pattern_escaping'     => true
                          );

        $this->properties['string_quoting'] = array('start' => "'",
                                                    'end' => "'",
                                                    'escape' => '\\',
                                                    'escape_pattern' => '\\');

        $this->properties['identifier_quoting'] = array('start' => '`',
                                                        'end' => '`',
                                                        'escape' => '`');

        $this->properties['sql_comments'] = array(
                                            array('start' => '-- ', 'end' => "\n", 'escape' => false),
                                            array('start' => '#', 'end' => "\n", 'escape' => false),
                                            array('start' => '/*', 'end' => '*/', 'escape' => false),
                                            );

        $this->properties['varchar_max_length'] = 255;

        parent::__construct($manager, $adapter);
    }

    /**
     * Overrides connect Method, to add specific attributes
     * PDO emulate prepares is required to avoid bugs on mysql < 5.1
     * when trying to prepare DROP DATABASE or CREATE DATABASE statements
     *
     * @see Doctrine_Connection :: connect();
     * @return boolean connected
     */
     public function connect()
     {
         $connected = parent::connect();
         $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

         return $connected;
     }


    /**
     * returns the name of the connected database
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->fetchOne('SELECT DATABASE()');
    }

    /**
     * Set the charset on the current connection
     *
     * @param string    charset
     */
    public function setCharset($charset)
    {
        $query = 'SET NAMES ' . $this->quote($charset);

        $this->exec($query);
    }

    /**
     * Execute a SQL REPLACE query. A REPLACE query is identical to a INSERT
     * query, except that if there is already a row in the table with the same
     * key field values, the REPLACE query just updates its values instead of
     * inserting a new row.
     *
     * The REPLACE type of query does not make part of the SQL standards. Since
     * practically only MySQL implements it natively, this type of query is
     * emulated through this method for other DBMS using standard types of
     * queries inside a transaction to assure the atomicity of the operation.
     *
     * @access public
     *
     * @param string $table name of the table on which the REPLACE query will
     *  be executed.
     * @param array $fields associative array that describes the fields and the
     *  values that will be inserted or updated in the specified table. The
     *  indexes of the array are the names of all the fields of the table. The
     *  values of the array are also associative arrays that describe the
     *  values and other properties of the table fields.
     *
     *  Here follows a list of field properties that need to be specified:
     *
     *    value:
     *          Value to be assigned to the specified field. This value may be
     *          of specified in database independent type format as this
     *          function can perform the necessary datatype conversions.
     *
     *    Default:
     *          this property is required unless the Null property
     *          is set to 1.
     *
     *    type
     *          Name of the type of the field. Currently, all types Metabase
     *          are supported except for clob and blob.
     *
     *    Default: no type conversion
     *
     *    null
     *          Boolean property that indicates that the value for this field
     *          should be set to null.
     *
     *          The default value for fields missing in INSERT queries may be
     *          specified the definition of a table. Often, the default value
     *          is already null, but since the REPLACE may be emulated using
     *          an UPDATE query, make sure that all fields of the table are
     *          listed in this function argument array.
     *
     *    Default: 0
     *
     *    key
     *          Boolean property that indicates that this field should be
     *          handled as a primary key or at least as part of the compound
     *          unique index of the table that will determine the row that will
     *          updated if it exists or inserted a new row otherwise.
     *
     *          This function will fail if no key field is specified or if the
     *          value of a key field is set to null because fields that are
     *          part of unique index they may not be null.
     *
     *    Default: 0
     *
     * @return integer      the number of affected rows
     */
    public function replace(Doctrine_Table $table, array $fields, array $keys)
    {
        if (empty($keys)) {
            throw new Doctrine_Connection_Exception('Not specified which fields are keys');
        }

        $columns = array();
        $values = array();
        $params = array();
        foreach ($fields as $fieldName => $value) {
            $columns[] = $table->getColumnName($fieldName);
            $values[] = '?';
            $params[] = $value;
        }

        $query = 'REPLACE INTO ' . $table->getTableName() . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';

        return $this->exec($query, $params);
    }


    /**
     * exec
     * @param string $query     sql query
     * @param array $params     query parameters
     *
     * @return PDOStatement|Doctrine_Adapter_Statement
     */
    public function exec($query, array $params = array())
    {
        $this->connect();

        try {
            if ( ! empty($params)) {
                $stmt = $this->prepare($query);
                $stmt->execute($params);

                return $stmt->rowCount();
            } else {
                $event = new Doctrine_Event($this, Doctrine_Event::CONN_EXEC, $query, $params);

                $this->getAttribute(Doctrine::ATTR_LISTENER)->preExec($event);
                if ( ! $event->skipOperation) {
                    $count = $this
                      ->getDbhFromQuery($query)
                      ->exec($query);

                    $this->_count++;
                }
                $this->getAttribute(Doctrine::ATTR_LISTENER)->postExec($event);

                return $count;
            }
        } catch (Doctrine_Adapter_Exception $e) {
        } catch (PDOException $e) { }

        $this->rethrowException($e, $this);
    }


        /**
     * execute
     * @param string $query     sql query
     * @param array $params     query parameters
     *
     * @return PDOStatement|Doctrine_Adapter_Statement
     */
    public function execute($query, array $params = array())
    {
        $this->connect();

        try {
            if ( ! empty($params)) {
                $stmt = $this->prepare($query);
                $stmt->execute($params);

                return $stmt;
            } else {
                $event = new Doctrine_Event($this, Doctrine_Event::CONN_QUERY, $query, $params);

                $this->getAttribute(Doctrine::ATTR_LISTENER)->preQuery($event);

                if ( ! $event->skipOperation) {
                    $stmt = $this
                      ->getDbhFromQuery($query)
                      ->query($query);
                      
                    $this->_count++;
                }
                $this->getAttribute(Doctrine::ATTR_LISTENER)->postQuery($event);

                return $stmt;
            }
        }
        catch (Doctrine_Adapter_Exception $e) { }
        catch (PDOException $e) { }

        $this->rethrowException($e, $this);
    }


        /**
     * prepare
     *
     * @param string $statement
     */
    public function prepare($statement)
    {
        $this->connect();

        try {
            $event = new Doctrine_Event($this, Doctrine_Event::CONN_PREPARE, $statement);

            $this->getAttribute(Doctrine::ATTR_LISTENER)->prePrepare($event);

            $stmt = false;

            if ( ! $event->skipOperation) {
                $stmt = $this
                  ->getDbhFromStatement($statement)
                  ->prepare($statement);
            }

            $this->getAttribute(Doctrine::ATTR_LISTENER)->postPrepare($event);

            return new Doctrine_Connection_Statement($this, $stmt);
        } catch(Doctrine_Adapter_Exception $e) {
        } catch(PDOException $e) { }

        $this->rethrowException($e, $this);
    }

    public function setSlave(Doctrine_Connection_Mysql $slave)
    {
      
      $this->slave = $slave;
    }

    public function getDbhFromStatement($statement)
    {

      $use = 0 === strpos(trim(strtolower($statement)), 'select') ? 'slave' : 'master';
      
      return $this->getDbhByType($use, $statement);
    }

    public function getDbhFromQuery($query)
    {

      $use = 0 === strpos(trim(strtolower($query)), 'select') ? 'slave' : 'master';

      return $this->getDbhByType($use, $query);
    }

    public function beginTransaction($savepoint = null)
    {

      $this->transaction_cpt++;

      return parent::beginTransaction($savepoint);
    }

    public function getDbhByType($type, $query = '')
    {
      
      if($type == 'slave' && $this->slave && $this->transaction_cpt == 0)
      {

        if (sfContext::hasInstance())
        {
          // sfContext::getInstance()->getLogger()->log('{swMasterDoctrine} use '.$this->slave->getName().' for the query : '.$query);
        }

        return $this->slave->getDbh();
      }

      if (sfContext::hasInstance())
      {
        // sfContext::getInstance()->getLogger()->log('{swMasterDoctrine} use '.$this->getName().' for the query : '.$query);
      }

      return $this->dbh;
    }

    public function commit($savepoint = null)
    {
      $this->transaction_cpt--;

      return parent::commit($savepoint);
    }

    /**
     * rollback
     * Cancel any database changes done during a transaction or since a specific
     * savepoint that is in progress. This function may only be called when
     * auto-committing is disabled, otherwise it will fail. Therefore, a new
     * transaction is implicitly started after canceling the pending changes.
     *
     * this method can be listened with onPreTransactionRollback and onTransactionRollback
     * eventlistener methods
     *
     * @param string $savepoint                 name of a savepoint to rollback to
     * @throws Doctrine_Transaction_Exception   if the rollback operation fails at database level
     * @return boolean                          false if rollback couldn't be performed, true otherwise
     */
    public function rollback($savepoint = null)
    {
      $this->transaction_cpt--;

      return parent::rollback($savepoint);
    }

}