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
class swBrowser extends sfBrowser
{

  /**
   *
   * add file to browser, use this method when you POST data to a functional test
   * the implementation in sf works only with 'click' event
   * 
   * @param $elementName string ie, myform[data]
   * @param $filename string path to the file
   * @param $type string mimetype of the file
   * @param $create_tmp boolean create a tmp file of the filename, so original file is never altered, default is true
   *
   * @return swBrowser
   **/
  public function addFile($elementName, $filename, $type = '', $create_tmp = true)
  {

    if (is_readable($filename))
    {
      if($create_tmp)
      {
        $temp_name = tempnam(sys_get_temp_dir(), 'sf_test_file_').'_'.basename($filename);
        copy($filename, $temp_name);

        $filename = $temp_name;
      }

      $fileError = UPLOAD_ERR_OK;
      $fileSize = filesize($filename);
    }
    else
    {
      $fileError = UPLOAD_ERR_NO_FILE;
      $fileSize = 0;
    }

    $this->parseArgumentAsArray($elementName, array(
      'name' => basename($filename),
      'type' => $type,
      'tmp_name' => $filename,
      'error' => $fileError,
      'size' => $fileSize
    ), $this->files);

    return $this;
  }

}