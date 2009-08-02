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
 * @subpackage mail
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swMail extends Zend_Mail {
  public function __construct($charset = null) {
    if(is_null($charset)) {
      $config = sfConfig::get('app_swToolbox_mail');
      $charset = $config['charset'];
    }

    parent::__construct($charset);
  }

  public function quickView() {
    $mail = "";
    foreach($this->getParts() as $part) {
      $mail .= $part->getContent();
    }

    return $mail;
  }

  public function __toString() {

    return $this->quickView();
  }

  public function getRecipientsTo() {
    
    return $this->_to;
  }

  public function getRecipientsBcc() {

    return $this->_to;
  }

  public function getRecipientsCc() {

    return $this->_to;
  }

  public function getHeader($name) {
    if(isset($this->_headers[$name])) {
      
      return $this->_headers[$name];
    }

    return null;
  }

  public function getPrintableTo()
  {
    
    return $this->_getPrintable($this->getHeader('To'));
  }

  private function _getPrintable($tos)
  {
    if(!is_array($tos))
    {

      return '';
    }

    $to = array();
    foreach($tos as $name => $email)
    {

      if($name === 'append')
      {

        continue;
      }

      $to[] = $email;
    }

    return implode(", ", $to);
  }
  
  public function getPrintableBcc()
  {

    return $this->_getPrintable($this->getHeader('Bcc'));
  }

  public function getPrintableCc()
  {

    return $this->_getPrintable($this->getHeader('Cc'));
  }

  public function getPrintableReplyTo()
  {

    return $this->_getPrintable($this->getHeader('ReplyTo'));
  }
}