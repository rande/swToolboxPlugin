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
 * @package    swToolbox
 * @subpackage form
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swResetLabelTranslation extends sfCallable
{
  static
    $callback;
    
  public function __construct($callable)
  {
    if($callable instanceof self)
    {
      throw new sfException('callable cannot be an instance of swResetLabelTranslation');
    }
    
    self::$callback = $callable;
  }
  
  /**
   * call the original callable method and add the formatting to it
   * (non-PHPdoc)
   * @see lib/vendor/symfony/lib/util/sfCallable#call()
   */
  
  public function call()
  {
    $arguments = func_get_args();
    
    $subject    = $arguments[0];
    $parameters = $arguments[1];
    $catalogue  = $arguments[2];
    $format     = "%s";
    
    if($subject instanceof swFormLabel)
    {
      $format  = $subject->isRequired() ? $subject->getFormat() : $format;
      $subject = $subject->getLabel();
    } 
    else if($subject instanceof swFormErrorMessage)
    {
      /*
       * Shame on me ... but there is no other choice ...
       *
       * Fabien, if you read me ...
       */
      return $subject->__toString();
    }

    if(method_exists($subject, 'getCatalogue') && is_null($subject->getCatalogue()))
    {
      $catalogue = $subject->getCatalogue();
    }
        
    if (!is_callable(self::$callback))
    {

      return sprintf($format, $subject); 
    }

    return sprintf($format, self::$callback instanceof sfCallable ? self::$callback->call($subject, $parameters, $catalogue) : call_user_func(self::$callback, $subject, $parameters, $catalogue));
  }
  
}