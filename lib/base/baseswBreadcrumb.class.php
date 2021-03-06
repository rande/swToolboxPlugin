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
 * @package    swBreadcrumb
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
abstract class baseswBreadcrumb
{

  private static $entry = array();

  public static function add($breadcrumb = null, $url = null, $title = null, $image = null)
  {
    self::$entry[] = array(
      'breadcrumb' => $breadcrumb, 
      'url' => $url, 
      'title' => $title ? $title : strip_tags($breadcrumb), 
      'image' => $image
    );
  }

  public static function get() 
  {

    return self::$entry;
  }

  public static function reset()
  {

    self::$entry = array();
  }

  public static function clean()
  {

    self::reset();
  }


  public function define()
  {
    $action_stack = sfContext::getInstance()->getController()->getActionStack()->getLastEntry();

    $module_name = $action_stack->getModuleName();
    $action_name = $action_stack->getActionName();

    $function_name = $module_name.'_'.$action_name;

    if(method_exists($this, $function_name))
    {
      call_user_func(array($this, $function_name), $action_stack->getActionInstance());
    }
  }


}