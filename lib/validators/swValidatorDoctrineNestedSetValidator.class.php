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
 * Validate the form
 * 
 * @author Thomas Rabaix <thomas.rabaix@gmail.com>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.soleoweb.com
 * 
 * SVN : $Id$
 **/
class swValidatorDoctrineNestedSet extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * model:      The model class (required)
   *  * query:      A query to use when retrieving objects
   *  * column:     The column name (null by default which means we use the primary key)
   *                must be in field name format
   *  * connection: The Doctrine connection to use (null by default)
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('object');
    $this->addOption('model', get_class($options['object']));
    $this->addOption('connection', null);
    $this->addOption('full_tree', false);
    
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $object = $this->getOption('object');

    $parent = Doctrine::getTable($this->getOption('model'))->find($value);
     
    if (!$parent)
    {
      throw new sfValidatorError($this, 'invalid parent reference', array('value' => $value));
    }

    if($this->getOption('full_tree'))
    {
      return $parent;
    }
    
    if($parent->getNode()->isDescendantOf($object))
    {
      throw new sfValidatorError($this, 'invalid change [1]', array('value' => $value));
    }

    if($parent->identifier() == $object->identifier() && !$object->getNode()->isRoot())
    {
      throw new sfValidatorError($this, 'invalid change [2]', array('value' => $value));
    }
    
    return $parent;
  }

}