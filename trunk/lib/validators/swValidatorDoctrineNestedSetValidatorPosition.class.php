<?php

/*
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
 * Validate the form, 
 * 
 * Some portions are inspired from the isicsPropelNestedSetPositionPlugin
 * 
 * @author Thomas Rabaix <thomas.rabaix@gmail.com>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.soleoweb.com
 * 
 * 
 * SVN : $Id$
 **/
class swValidatorDoctrineNestedSetPosition extends sfValidatorBase
{
  
  /**
   * Insert methods
   *
   * @var array insert methods
   **/
  protected static $insertMethods = array(
    'insertAsFirstChildOf',
    'insertAsLastChildOf',
    'insertAsPrevSiblingOf',
    'insertAsNextSiblingOf',
    'insertAsParentOf'
  );
  
  /**
   * Move methods
   *
   * @var array move methods
   **/
  protected static $moveMethods = array(
    '-',
    'moveAsFirstChildOf',
    'moveAsLastChildOf',
    'moveAsPrevSiblingOf',
    'moveAsNextSiblingOf'
  );
  
  
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * model:      The model class (required)
   *  * max_depth:  The max depth allowed
   *  * connection: The Doctrine connection to use (null by default)
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('object');
    
    $this->addOption('model', get_class($options['object']));
    $this->addOption('max_depth', null);
    $this->addOption('connection', null);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $object = $this->getOption('object');
    
    if(!$object->getTable()->hasTemplate('Doctrine_Template_NestedSet'))
    {
      throw new sfException('The object is not a Doctrine_Template_NestedSet');
    }
    
    if ($value['method'] == 'makeRoot')
    {
      if (!$object->exists())
      {
        $root_node = Doctrine::getTable($this->getOption('model'))->getTree()->findRoot($object->getRootId());
        if ($root_node)
        {
          throw new sfValidatorError($this, 'root node already exists', array('value' => $value));
        }
        
        return $value;
      }
      else if (!$object->getNode()->isRoot())
      {
        throw new sfValidatorError($this, 'root node already exists', array('value' => $value));
      }
      else
      {
        return null;
      }
    }

    $related_node = Doctrine::getTable($this->getOption('model'))->find($value['related_node']);
    
    if (!$related_node)
    {
      throw new sfValidatorError($this, 'invalid node', array('value' => $value));
    }
    
    if ($related_node->getRootId() != $object->getRootId())
    {
      throw new sfValidatorError($this, 'invalid scope', array('value' => $value));
    }
        
    if (!$object->exists() && !in_array($value['method'], self::$insertMethods))
    {
      throw new sfValidatorError($this, 'invalid insert method', array('value' => $value));
    }
    
    if ($object->exists() && !in_array($value['method'], self::$moveMethods))
    {
      throw new sfValidatorError($this, 'invalid move method', array('value' => $value));
    }
    
    if ($object->exists() && $value['method'] == '-')
    {
      return null;
    }
    
    if (substr($value['method'], -7) == 'ChildOf')
    {
      if (!is_null($this->getOption('max_depth')) && $related_node->getNode()->getLevel() >= $this->getOption('max_depth'))
      {
        throw new sfValidatorError($this, 'invalid depth: max depth is '.$this->getOption('max_depth'), array('value' => $value));
      }
    }
    else
    {
      if ($related_node->getNode()->isRoot())
      {
        throw new sfValidatorError($this, 'invalid position', array('value' => $value));
      }
      
      if (!is_null($this->getOption('max_depth')) && $related_node->getNode()->getLevel() > $this->getOption('max_depth'))
      {
        throw new sfValidatorError($this, 'invalid depth: max depth is '.$this->getOption('max_depth'), array('value' => $value));
      }
    }
    
    $value['related_node'] = $related_node;
    
    return $value;
  }
}