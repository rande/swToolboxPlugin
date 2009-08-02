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
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swMenuElementFormatter
{
  
  public function renderLink($element, $level = 0)
  {
    $name = str_repeat("&nbsp;&nbsp;", $level * 2).$element->getName();
    $link_to = $name;
    
    if($element->getRoute())
    {
      $link_to = link_to($name, $element->getRoute(), $element->getLinkParams());
    }
    
    return $link_to; 
  }
  
  public function render(swMenuElement $element, $level = 0)
  {
    
    $html = '';
    
    if($level > 0)
    {
      $html .= sprintf("<li class='%s' style='%s' id='%s'>%s",
        $element->getClass(),
        $element->getStyle(),
        $element->getId(),
        $this->renderLink($element, $level))
      ;
    }
    
    if(count($element->getChildren()) > 0)
    {
      $html .= sprintf("<ul class='sw-menu-element sw-menu-element-group-level-%s'>", $level);
      foreach($element->getChildren() as $child)
      {
        $html .= $child->render($level + 1);
      }
      
      $html .= "</ul>";
    }
    
    if($level > 0)
    {
      $html .= "</li>";
    }
    
    return $html;
  }
}