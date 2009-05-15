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
class swCrossApplicationRoutingConfigHandler extends sfRoutingConfigHandler
{
  
  protected 
    $app,
    $host;
  
  public function setApp($app)
  {
    $this->app = $app;
  }
  
  public function getApp()
  {
    return $this->app;
  }
  
  public function setHost($host)
  {
    $this->host = $host;
  }
  
  public function getHost()
  {
    return $this->host;
  }
  
  protected function parse($configFiles)
  {
    $routes = parent::parse($configFiles);
    
    foreach($routes as $name => $route)
    {
      $routes[$name][1][2]['sw_app'] = $this->app;
      $routes[$name][1][2]['sw_host'] = $this->host;
    }
    
    return $routes;
  }
  
  public function evaluate($configFiles)
  {
    $routeDefinitions = $this->parse($configFiles);

    $routes = array();
    foreach ($routeDefinitions as $name => $route)
    {
      $r = new ReflectionClass($route[0]);
      
      $routes[$name] = new swEncapsulateRoute($r->newInstanceArgs($route[1]), $this->host, $this->app);
      
    }

    return $routes;
  }
  

  
}