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
 * sfWidgetFormRadio represents one radio HTML tag.
 *
 * @package    symfony
 * @subpackage widget
 * @author    Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swWidgetFormjQueryStarRating extends sfWidgetFormSelectRadio
{
  
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options = array(), $attributes = array());
    
    $this->addOption('star_class', 'star');
    //$this->addOption('split', 0);
    $this->addOption('nocancel', 1);
    
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $class = sprintf('%s {split: 0, nocancel: %d}', 
      $this->getOption('star_class'), 
      //$this->getOption('split'), 
      $this->getOption('nocancel')
    );
    
    if(isset($attributes['class'])) {
      $attributes['class'] .= ' '.$class;
    }
    else
    {
      $attributes['class'] = $class;
    }

    return parent::render($name, $value, $attributes, $errors);
  }

  protected function formatChoices($name, $value, $choices, $attributes)
  {
    $inputs = array();
    foreach ($choices as $key => $option)
    {
      $baseAttributes = array(
        'name'  => substr($name, 0, -2),
        'type'  => 'radio',
        'value' => self::escapeOnce($key),
        'id'    => $id = $this->generateId($name, self::escapeOnce($key)),
      );

      if (strval($key) == strval($value === false ? 0 : $value))
      {
        $baseAttributes['checked'] = 'checked';
      }

      $inputs[] = array(
        'input' => $this->renderTag('input', array_merge($baseAttributes, $attributes)),
        'label' => $this->renderContentTag('label', $option, array('for' => $id, 'class' => 'star-label')),
      );
    }

    return call_user_func($this->getOption('formatter'), $this, $inputs);
  }
  
  public function formatter($widget, $inputs)
  {
    $rows = array();
    foreach ($inputs as $input)
    {
      $rows[] = $input['input'].$this->getOption('label_separator').$input['label'];
    }

    return implode($this->getOption('separator'), $rows);
  }
  
  public function getJavascripts()
  {
    
    return array(
      '/swToolbox/js/starrating/jquery.MetaData.js',
      '/swToolbox/js/starrating/jquery.rating.pack.js',
    );
  }
  
  public function getStylesheets()
  {
    
    return array(
      '/swToolbox/js/starrating/jquery.rating.css'
    );
  }
}
