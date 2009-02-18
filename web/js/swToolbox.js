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
 
var swToolbox = {
  updateFormElements: function(url, widget, class_name, format, elements) {
    
    // get the parent form
    var form = widget.form;
    var data = '_sw_class=' + class_name + '&_sw_format=' + format + '&' + jQuery(form).serialize() + '&_sw_elements=' + elements + '&_sw_from=' + widget.name;
    
    jQuery.ajax({
      type: 'GET',
      url: url,
      dataType: "json",
      data: data,
      cache: false,
      elements: elements,
      form: form,
      format: format,
      success: swToolbox.handleUpdateFormElementsResponse
    })
  },
  
  handleUpdateFormElementsResponse: function(data, textStatus) {
    for(var param in data) {
      jQuery('#' + this.format + '_' + param, this.form).val(data[param]);
    }
  }
}