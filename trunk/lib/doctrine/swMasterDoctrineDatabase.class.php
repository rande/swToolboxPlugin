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
 * This class attempt to append raw SQL into a Doctine_Query, SQL added with addSqlQueryPart
 * is removed when the SQL query is created.
 *
 * @package    swToolboxPlugin
 * @subpackage doctrine
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swMasterDoctrineDatabase extends sfDoctrineDatabase
{

  public function getSlaveNames()
  {

    return $this->getParameter('slaves', array());
  }

  public function initializeSlave(sfDatabaseManager $database_manager)
  {

    // pick one random name
    $slaves = $this->getSlaveNames();
    $slave_name = $slaves[array_rand($slaves, 1)];

    try
    {
      $connection = $database_manager->getDatabase($slave_name)->getDoctrineConnection();

      if (sfContext::hasInstance())
      {
        sfContext::getInstance()->getLogger()->log('{swMasterDoctrine} select slave connection : '.$slave_name);
      }


    }
    catch(sfDatabaseException $e)
    {
      // no database with this name => fallback to the master
      $connection = $this->getDoctrineConnection();

      if (sfContext::hasInstance())
      {
        sfContext::getInstance()->getLogger()->crit('{swMasterDoctrine} fallback to the master ');
      }
    }

     
    $this->getDoctrineConnection()->setSlave($connection);
  }
}
