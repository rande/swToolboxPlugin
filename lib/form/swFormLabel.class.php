<?php

class swFormLabel 
{
  protected
    $label,
    $required,
    $format;
    
  
  public function __construct($label, $required = false, $format = "%s")
  {
    $this->label = $label;
    $this->required = $required;
    $this->format = $format;
  }
  
  public function __toString()
  {
    
    return $this->label;
  }
  
  public function isRequired()
  {
    
    return $this->required;
  }
  
  public function getLabel()
  {
    
    return $this->label;
  }
  
  public function getFormat()
  {
    
    return $this->format;
  }
}