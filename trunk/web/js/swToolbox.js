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
  updateInProgress: false,
  updateFormElements: function(url, widget, class_name) {
    if(swToolbox.updateInProgress)
    {
      alert('Update in progress, please wait !');
      
      return;
    }
    
    swToolbox.updateInProgress = true;
    
    // get the parent form
    var form = widget.form;
    var data = '_sw_class=' + class_name + '&_sw_name=' + widget.name + '&' + jQuery(form).serialize();
    
    jQuery.ajax({
      type: 'GET',
      url: url,
      dataType: "json",
      data: data,
      cache: false,
      form: form,
      success: swToolbox.handleUpdateFormElementsResponse
    })
  },
  
  handleUpdateFormElementsResponse: function(data, textStatus) {

    for(var param in data) {      
      var elm = jQuery("#" + param, this.form);
      
      if(data[param].html != undefined)
      {
        // we force the id, as some symfony widgets do not provide a main id, like radiobutton
        var new_elm = jQuery(data[param].html);
        new_elm.attr('id', param); 
        
        elm.replaceWith(new_elm);
        elm = jQuery("#" + param, this.form);
      }
      else if(data[param].value != undefined)
      {
        elm.val(data[param].value);
      }
    }
    
    swToolbox.updateInProgress = false;
  }
}