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

if (sfConfig::get('app_swToolbox_routes_register', true) && in_array('swToolbox', sfConfig::get('sf_enabled_modules', array())))
{
  $this->dispatcher->connect('routing.load_configuration', array('swToolboxRouting', 'listenToRoutingLoadConfigurationEvent'));
}

if (sfConfig::get('app_swToolbox_routes_register_cross_applications', false))
{
  $this->dispatcher->connect('routing.load_configuration', array('swToolboxRoutingCrossApplicationRouting', 'listenToRoutingLoadConfigurationEvent'));
}

if(sfConfig::get('app_swToolbox_autoload_helper', true))
{
  $this->loadHelpers(array('swToolbox'));
}


$this->dispatcher->connect('component.method_not_found', array('swToolbox', 'componentMethodNotFound'));
$this->dispatcher->connect('configuration.method_not_found', array('swToolbox', 'configurationMethodNotFound'));

if (sfConfig::get('sf_web_debug'))
{
  
  $this->dispatcher->connect('debug.web.load_panels', array('swFunctionalUnitTestDebugPanel', 'listenToAddPanelEvent'));
}
