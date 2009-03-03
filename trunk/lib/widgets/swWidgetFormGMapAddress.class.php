<?php
/*
 *  $Id$
 *
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
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
 * swWidgetFormGMapAddress represents a gmap address widget
 *
 * @package    swToolboxPlugin
 * @subpackage widget
 * @author    Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swWidgetFormGMapAddress extends sfWidgetFormSchema
{
  public function __construct($fields = null, $options = array(), $attributes = array(), $labels = array(), $helps = array())
  {
    $fields = array(
      'address' => new sfWidgetFormInput(array(), array('style' => 'width: 300px;')),
      'lat'     => new sfWidgetFormInput(array(), array('readonly' => true)),
      'lng'     => new sfWidgetFormInput(array(), array('readonly' => true)),
    );
    
    parent::__construct($fields, $options, $attributes, $labels, $helps);
  }
  
  public function getJavascripts()
  {
    return array(
      '/swToolboxPlugin/js/swGmapWidget.js'
    );
  }
  
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    
    // generate field id
    $lat_id     = $this->generateId($name.'[lat]');
    $lng_id     = $this->generateId($name.'[lng]');
    $address_id = $this->generateId($name.'[address]');
    $map_id     = $this->generateId($name.'[map]');
    $lookup_id  = $this->generateId($name.'[lookup]');

    // get the inner form formatter
    $subFormatter = new swFormatterGMapAddress($this);
    
    // render address field
    $address_field = $subFormatter->formatRow(
      $subFormatter->generateLabel('address'), 
      $this->renderField('address', isset($value['address']) ? $value['address'] : null, $attributes, array()), 
      isset($errors['address']) ? $errors['address'] : array(),
      $this->getHelp($name)
    );

    // render lng field
    $lng_field = $subFormatter->formatRow(
      $subFormatter->generateLabel('lng'), 
      $this->renderField('lng', isset($value['lng']) ? $value['lng'] : null, $attributes, array()), 
      isset($errors['lng']) ? $errors['lng'] : array(),
      $this->getHelp($name)
    );

    // render lat field
    $lat_field = $subFormatter->formatRow(
      $subFormatter->generateLabel('lat'), 
      $this->renderField('lat', isset($value['lat']) ? $value['lat'] : null, $attributes, array()), 
      isset($errors['lat']) ? $errors['lat'] : array(),
      $this->getHelp($name)
    );
    
    // render the javascript code for the widget
    $javascript = sprintf(
      '
      <script>
        jQuery(window).bind("load", function() {
          new swGmapWidget({ 
            lng: "%s", 
            lat: "%s", 
            address: "%s", 
            lookup: "%s", 
            map: "%s" 
          });
        })
      </script>
    ',
    $lng_id,
    $lat_id,
    $address_id,
    $lookup_id,
    $map_id
    );
    
    // render the html code for the widget
    $html = sprintf('
      <div id="%s" class="sw-gmap-widget">
        %s <input type="submit" value="lookup address"  id="%s" /> <br />
       %s - %s <br /> 
         <div id="%s" style="width: 500px; height: 300px"></div>
       </div>', 
      $this->generateId($name),
      $address_field,
      $lookup_id,
      $lat_field,
      $lng_field,
      $map_id
    );
    
    return $html.$javascript;
    
  }
}