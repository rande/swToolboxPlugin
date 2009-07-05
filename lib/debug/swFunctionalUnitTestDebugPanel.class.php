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
 *
 * @package    swToolboxPlugin
 * @subpackage debug
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swFunctionalUnitTestDebugPanel extends sfWebDebugPanel
{
  public function getTitle()
  {
    
    return 'Functional Test';
  }
  
  public function getPanelTitle()
  {
    
    return 'Functional Test (autogenerated)';
  }
  
  public function getPanelContent()
  {
    
    return '
      <div id="sfDebugPanelFunctionalUnitTest"><div style="float:left">'.
      "<a href='?_sw_func_reset=1'>Reset</a> - ".
      (!sfContext::getInstance()->getUser()->getAttribute('sw_func_enabled', false, 'swToolbox') ?
      "<a href='?_sw_func_enabled=1'>Activate</a>" :
      "<a href='?_sw_func_enabled=0'>Deactivate</a>")
      .'<br /><textarea style="width:500px; height: 200px; font-family:courier">'.htmlspecialchars('
<?php

include(dirname(__FILE__).\'/../../bootstrap/functional.php\');

$browser = new sfTestFunctional(new sfBrowser());
$test    = $browser->test();
$conn    = Doctrine::getConnectionByTableName(\'your_model\');

$conn->beginTransaction();
'.swFilterFunctionalTest::getRawPhp().'

$conn->rollback();', ENT_COMPAT, 'UTF-8').'
</textarea>
      </div>
      <div style="float:left">
        <h2>References</h2>
        <ul>
          <li><a href="http://www.symfony-project.org/book/1_2/15-Unit-and-Functional-Testing#chapter_15_functional_tests" target="_new">Functional Test</a>
            <ul>
              <li><a href="http://www.symfony-project.org/book/1_2/15-Unit-and-Functional-Testing#chapter_15_sub_browsing_with_the_sftestbrowser_object" target="_new">Browsing with the sfTestBrowser Object</a></li>
              <li><a href="http://www.symfony-project.org/book/1_2/15-Unit-and-Functional-Testing#chapter_15_sub_using_assertions" target="_new">Using Assertions</a></li>
              <li><a href="http://www.symfony-project.org/book/1_2/15-Unit-and-Functional-Testing#chapter_15_sub_using_css_selectors" target="_new">Using CSS Selectors</a></li>
              <li><a href="http://www.symfony-project.org/book/1_2/15-Unit-and-Functional-Testing#chapter_15_sub_testing_for_errors" target="_new">Testing for errors</a></li>
              <li><a href="http://www.symfony-project.org/book/1_2/15-Unit-and-Functional-Testing#chapter_15_sub_working_in_the_test_environment" target="_new">Working in the Test Environment</a></li>          
            </ul>
          </li>
          <li><a href="http://www.symfony-project.org/plugins/swToolboxPlugin" target="_new">swToolboxPlugin (symfony plugin page)</a></li>
        </ul>
      </div>
     </div>
    ';
  }
  
  static public function listenToAddPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel('sw.functional_unit_test', new self($event->getSubject()));
  }
}