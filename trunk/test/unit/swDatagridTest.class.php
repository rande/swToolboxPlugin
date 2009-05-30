<?php

include(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(null, new lime_output_color());

class myModel extends Doctrine_Record
{
  
}

class myDatagrid extends swDoctrineDatagrid
{
  
  public function configureDatagrid()
  {
    $this->addFilter(
      'field1',
      null,
      new sfWidgetFormInput,
      new sfValidatorPass
    );
    
    $this->addFilter(
      'field2',
      null,
      new sfWidgetFormInput,
      new sfValidatorPass
    );
    
    $this->getWidgetSchema()->getFormFormatter()->setTranslationCatalogue("myCatalogue");
  }
  
  public function getModelName()
  {
    return "myModel";
  }
}

$datagrid = new myDatagrid;

echo $datagrid;