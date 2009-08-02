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
 * @package    swToolboxPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swMenuElement 
{
  
  static 
    $idx = array();
    
  protected
    $name     = null,
    $route    = null,
    $child    = array(),
    $selected = false,
    $uid      = null,
    $module   = null,
    $formatter = null,
    $bound     = false,
    $style    = '',
    $class    = '',
    $id       = '',
    $parent   = ''
    ;
  
  public function __construct(array $options = array())
  {
    $this->name     = isset($options['name']) ? $options['name'] : null;
    $this->route    = isset($options['route']) ? $options['route'] : null;
    $this->id       = isset($options['id']) ? $options['id'] : null;
    $this->style    = isset($options['style']) ? $options['style'] : null;
    $this->class    = isset($options['class']) ? $options['class'] : null;
  }
  
  public function getFormatter()
  {
    if(!$this->formatter instanceof swMenuElementFormatter)
    {
      $this->formatter = new swMenuElementFormatter;
    }
    
    return $this->formatter;
  }
  
  public function setFormatter(swMenuElementFormatter $formatter)
  {
    $this->formatter = $formatter;
  }
  
  public function render($level = 0)
  {
    
    return $this->getFormatter()->render($this, $level);
  }
  
  public function addChild(swMenuElement $child_element)
  {
    if(!is_null($child_element->getId()))
    {
      if(isset(self::$idx[$child_element->getId()]))
      {
        foreach($child_element->getChildren() as $child)
        {
          self::$idx[$child_element->getId()]->addChild($child);
        }
        
        return;
      }
      else
      {
        self::$idx[$child_element->getId()] = $child_element;
      }
    }
    
    $this->child[] = $child_element;
  }
  
  
  public function getRoute()
  {
    
    return $this->route;
  }
  
  public function setRoute($route)
  {
    $this->route = $route;
  }
  
  public function getLinkParams()
  {
    
    return array();
  }
  
  public function setBound($bool)
  {
    $this->bound = $bool;
  }
  
  public function isBound()
  {
    
    return $this->bound;
  }
  
  public function getClass()
  {
    
    return $this->class;
  }
  
  public function getStyle()
  {
    
    return $this->style;
  }
  
  public function getId()
  {
    
    return $this->id;
  }
  
  public function setName($name)
  {
    $this->name = $name;
  }
  
  public function getName()
  {
    
    return $this->name;
  }
  
  public function getChildren()
  {
    
    return $this->child;
  }
}