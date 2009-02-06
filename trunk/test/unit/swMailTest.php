<?php

include(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);
sfContext::createInstance($configuration);

swToolbox::registerZend(new sfEvent(new stdClass, 'prout'));

$reflection_class = new ReflectionClass('Zend_Mail_Transport_Sendmail');
$transport_class = $reflection_class->newInstanceArgs(array('-f thomas.rabaix@soleoweb.com'));

$t->ok($transport_class instanceof Zend_Mail_Transport_Sendmail);

$reflection_class = new ReflectionClass('Zend_Mail_Transport_Smtp');
$transport_class = $reflection_class->newInstanceArgs(array(
  '127.0.0.1', 
  array('auth' => 'Plain')
));

$t->ok($transport_class instanceof Zend_Mail_Transport_Smtp);
