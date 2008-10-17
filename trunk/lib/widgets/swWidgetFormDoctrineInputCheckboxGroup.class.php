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
 * swWidgetFormDoctrineInputCheckboxGroup represents a group of radiobox HTML tag 
 * for a model where you can select multiple values.
 *
 * @package    swToolboxPlugin
 * @subpackage widgets
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swWidgetFormDoctrineInputCheckboxGroup extends sfWidgetFormDoctrineSelectMany
{
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }
    
    $separator = "\n";
    if(array_key_exists('separator', $attributes))
    {
      $separator = $attributes['separator']."\n";
      unset($attributes['separator']);
    }
    
    return $this->renderContentTag('div', "\n".implode($separator, $this->getInputForCheckboxGroup($value, $choices, $name))."\n", $attributes);
  }

  /**
   * Returns an array of input tags for the given choices
   *
   * @param  string $value    The selected value
   * @param  array  $choices  An array of choices
   * @param  string $name     The field name
   *
   * @return array  An array of option tags
   */
  protected function getInputForCheckboxGroup($value, $choices, $name)
  {
    $mainAttributes = $this->attributes;
    $this->attributes = array();

    $options = array();
    foreach ($choices as $key => $option)
    {
      $attributes = array('value' => self::escapeOnce($key));
      if ((is_array($value) && in_array(strval($key), $value)) || strval($key) == strval($value))
      {
        $attributes['checked'] = 'checked';
      }

      $attributes['type'] = 'checkbox';
      $attributes['value'] = $key;
      $attributes['name'] = $name.'[]';

      $options[] = $this->renderContentTag('input', self::escapeOnce($option), $attributes);
    }

    $this->attributes = $mainAttributes;

    return $options;
  }
}