<?php


class swMenuElementFormatter
{
  
  public function renderLink($element, $level = 0)
  {
    $name = str_repeat("&nbsp;&nbsp;", $level * 2).$element->getName();
    $link_to = $name;
    
    if($element->getRoute())
    {
      $link_to = link_to($name, $element->getRoute(), $element->getLinkParams());
    }
    
    return $link_to; 
  }
  
  public function render(swMenuElement $element, $level = 0)
  {
    
    $html = '';
    
    if($level > 0)
    {
      $html .= sprintf("<li class='%s' style='%s' id='%s'>%s",
        $element->getClass(),
        $element->getStyle(),
        $element->getId(),
        $this->renderLink($element, $level))
      ;
    }
    if(count($element->getChildren()) > 0)
    {
      $html .= sprintf("<ul class='sw-menu-element sw-menu-element-group-level-%s'>", $level);
      foreach($element->getChildren() as $child)
      {
        $html .= $child->render($level + 1);
      }
      
      $html .= "</ul>";
    }
    
    if($level > 0)
    {
      $html .= "</li>";
    }
    
    return $html;
  }
}