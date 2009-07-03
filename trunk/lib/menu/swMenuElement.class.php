<?php

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