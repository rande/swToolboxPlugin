<?php

class swExtraMenuComponents extends sfComponents
{
  
  public function executeRenderMenu()
  {
    
    $name = isset($this->name) ? $this->name : 'main';
    
    $menu = swMenuManager::getInstance()->getMenu($name);
    
    $rendered = $menu->render();
    
    echo $rendered;
    
    return sfView::NONE;
  }
}