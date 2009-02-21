<?php

include(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(18, new lime_output_color());

class NestedForm extends sfForm
{
  public function configure()
  {
    $this->widgetSchema['titi'] = new sfWidgetFormInput;
    $this->widgetSchema['tutu'] = new sfWidgetFormInput;
  }
}

class PrimaryForm extends sfForm
{
  public function configure()
  {
    $this->widgetSchema['toto'] = new sfWidgetFormInput;
    $this->widgetSchema['tata'] = new sfWidgetFormInput;
    
    $this->widgetSchema->setNameFormat('primary_form[%s]');
    
    $this->embedForm('nested_form', new NestedForm);
    
    swToolboxFormHelper::resetFormLabels($this, array(
      'prefix'    => 'label_',
      'catalogue' => 'myCatalogue'
    ));
    
    $name = $this->widgetSchema->generateName('nested_form[titi]');
    $widget = $this->widgetSchema['nested_form']->generateName('titi');
    
    swToolboxFormHelper::updateFormElement($this, array(
      'widgetSchema' => $this->widgetSchema['nested_form'],
      'field'        => 'titi'
    ));
    
    swToolboxFormHelper::updateFormElement($this, array(
      'widgetSchema' => $this->widgetSchema,
      'field'        => 'tata'
    ));
  }
  
  public function getDynamicValues(sfWidgetFormSchema $widgetSchema, $field)
  {
    
    if($widgetSchema == $this->widgetSchema['nested_form'] && $field == 'titi')
    {
      return array('nested_form' => array('tutu' => 'salut toi !'), 'tata' => 'Ca va ?!');
    }
    
    if($widgetSchema == $this->widgetSchema && $field == 'tata')
    {
      return array('nested_form' => array('tutu' => new swFormDynamicResult('Super !!')));
    }
  }
}

$form = new PrimaryForm;

$t->cmp_ok($form['toto']->renderLabel(), '==', '<label for="primary_form_toto">label_toto</label>', 'label set to : label_toto');
$t->cmp_ok($form['nested_form']['titi']->renderLabel(), '==', '<label for="primary_form_nested_form_titi">label_titi</label>', 'label set to : label_titi');


$_FILES = array(
  'name' => array(
    'binary_content' => 'Picture 3.png',
    'MediaFileFormats' => array(
      0 => array('bin' => 'Picture 2.png'),
      1 => array('bin' => null),
      2 => array('bin' => null),
    )
  ),
  'type' => array(
    'binary_content' => 'image/png',
    'MediaFileFormats' => array(
      0 => array('bin' => 'image/png'),
      1 => array('bin' => null),
      2 => array('bin' => null),
    )
  ),
  'tmp_name' => array(
    'binary_content' => '/private/var/tmp/phpxOoXl1',
    'MediaFileFormats' => array(
      0 => array('bin' => '/private/var/tmp/phpQl7zSc'),
      1 => array('bin' => null),
      2 => array('bin' => null),
    )
  ),
  'error' => array(
    'binary_content' => 0,
    'MediaFileFormats' => array(
      0 => array('bin' => 0),
      1 => array('bin' => 4),
      2 => array('bin' => 4),
    )
  ),
  'size' => array(
    'binary_content' => 363966,
    'MediaFileFormats' => array(
      0 => array('bin' => 920110),
      1 => array('bin' => 0),
      2 => array('bin' => 0),
    )
  ),
);

$correct_files = array (
  'binary_content' => 
  array (
    'name' => 'Picture 3.png',
    'type' => 'image/png',
    'tmp_name' => '/private/var/tmp/phpxOoXl1',
    'error' => 0,
    'size' => 363966,
  ),
  'MediaFileFormats' => 
  array (
    0 => 
    array (
      'bin' => 
      array (
        'name' => 'Picture 2.png',
        'type' => 'image/png',
        'tmp_name' => '/private/var/tmp/phpQl7zSc',
        'error' => 0,
        'size' => 920110,
      ),
    ),
    1 => 
    array (
      'bin' => 
      array (
        'name' => null,
        'type' => null,
        'tmp_name' => null,
        'error' => 4,
        'size' => 0,
      ),
    ),
    2 => 
    array (
      'bin' => 
      array (
        'name' => null,
        'type' => null,
        'tmp_name' => null,
        'error' => 4,
        'size' => 0,
      ),
    ),
  ),
);

$t->cmp_ok(swToolboxFormHelper::convertFileInformation($_FILES), '==', $correct_files, 'nested tainted files ok');

$result = '<input onchange="swToolbox.updateFormElements(&quot;plugins/swToolboxPlugin/test/unit/swFormTest.php/sw-toolbox/dynamic-values&quot;, this, &quot;PrimaryForm&quot;);" type="text" name="primary_form[tata]" id="primary_form_tata" />';
$t->cmp_ok($result, '==',$form['tata']->render(), 'Update Form Element onchange ok');

$action = $context->getController()->getAction('swToolbox', 'retrieveDynamicValues');

$params['_sw_name'] = 'primary_form[tata]';
$request = new sfWebRequest($context->getEventDispatcher(), $params, array(), array());
$action->execute($request);
$t->cmp_ok($request->getAttribute('_sw_error'), '==', 'unable to load the class', 'unable to load the class');

$params['_sw_class'] = 'NestedForm';
$request = new sfWebRequest($context->getEventDispatcher(), $params, array(), array());
$action->execute($request);
$t->cmp_ok($request->getAttribute('_sw_error'), '==', 'the NestedForm does not have a getDynamicValues method', 'the NestedForm does not have a getDynamicValues method');

$params['_sw_class'] = 'PrimaryForm';
$request = new sfWebRequest($context->getEventDispatcher(), $params, array(), array());

ob_start();
$return = $action->execute($request);
$js_son_result = ob_get_contents();
ob_end_clean();

$js_son = '{"primary_form_nested_form_tutu":{"value":"Super !!","options":[]}}';
$t->cmp_ok($return, '==', sfView::NONE, 'action return sfView::NONE');
$t->cmp_ok($js_son, '==', $js_son_result, 'json ok');

$nested_form = new NestedForm;

$format = 'array_var[%s]';
$t->cmp_ok(swToolboxFormHelper::getBindParameter($form->getWidgetSchema()->getNameFormat()), '==', 'primary_form', 'get bind parameter : primary_form');
$t->cmp_ok(swToolboxFormHelper::getBindParameter($nested_form->getWidgetSchema()->getNameFormat()), '==', null, 'form not bind to a parameter');

$name = 'titi';
$info = swToolboxFormHelper::getWidgetSchemaFromName($nested_form, $name);
$t->isa_ok($info['widgetSchema'], 'sfWidgetFormSchema', 'widgetSchema instance of sfWidgetFormSchema');

$name = 'primary_form[nested_form][titu]';
$info = swToolboxFormHelper::getWidgetSchemaFromName($form, $name);
$t->ok($info['widgetSchema'] == null, 'titu does not exists');

$name = 'primary_form[nested_form_errro][titu]';
$info = swToolboxFormHelper::getWidgetSchemaFromName($form, $name);
$t->ok($info['widgetSchema'] == null, 'titu does not exists');

$name = 'primary_form[tata]';
$info = swToolboxFormHelper::getWidgetSchemaFromName($form, $name);
$t->ok($info['widgetSchema'] instanceof sfWidgetFormSchema, 'widgetSchema instance of sfWidgetFormSchema');

$values = $form->getDynamicValues($info['widgetSchema'], $info['field']);
$correct_values = array (
  'nested_form' => 
  array (
    'tutu' =>  new swFormDynamicResult('Super !!'),
  ),
);
$t->cmp_ok($values, '==', $correct_values);

$name = 'primary_form[nested_form][titi]';
$info = swToolboxFormHelper::getWidgetSchemaFromName($form, $name);
$t->ok($info['widgetSchema'] instanceof sfWidgetFormSchema, 'widgetSchema instance of sfWidgetFormSchema');

$values = $form->getDynamicValues($info['widgetSchema'], $info['field']);
$correct_values = array (
  'nested_form' => 
  array (
    'tutu' => 'salut toi !',
  ),
  'tata' => 'Ca va ?!',
);

$t->cmp_ok($values, '==', $correct_values);

$values = swToolboxFormHelper::generateValuesById($form->getWidgetSchema(), $correct_values);
$json_result = '{"primary_form_nested_form_tutu":{"value":"salut toi !","options":[]},"primary_form_tata":{"value":"Ca va ?!","options":[]}}';

$t->cmp_ok(json_encode($values), '==', $json_result);

