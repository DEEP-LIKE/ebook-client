
  function upload_image()
  {
    var bar = $('#bar');
    var percent = $('#percent');
    $('#loadFileForm').ajaxForm({
      beforeSubmit: function() {
        document.getElementById("progress_div").style.display="block";
        var percentVal = '0%';
        bar.width(percentVal)
        percent.html(percentVal);
      },

      uploadProgress: function(event, position, total, percentComplete) {
        var percentVal = percentComplete + '%';
        bar.width(percentVal)
        percent.html(percentVal);
      },

      success: function() {
        var percentVal = '100%';
        bar.width(percentVal)
        percent.html(percentVal);
      },

      complete: function(xhr) {
        console.log('Upload complete:', xhr);
        console.log('Response status:', xhr.status);
        console.log('Response JSON:', xhr.responseJSON);
        
        try {
          var response;
          
          // Intentar parsear la respuesta
          if (xhr.responseJSON) {
            response = xhr.responseJSON;
          } else if (xhr.responseText) {
            response = JSON.parse(xhr.responseText);
          } else {
            throw new Error('No response data');
          }
          
          if (response && response.success) {
            $('#results').html('<div style="color: green; padding: 10px; background: #e8f5e8; border-radius: 5px; margin-top: 10px;">✅ ' + 
                              response.message + '<br>' + 
                              (response.html || '') + '</div>');
            
            // Actualizar la lista de subdominios activos si está disponible
            if (response.newSubdomains && response.newSubdomains.length > 0) {
              updateSubdomainsList(response.newSubdomains);
            }
          } else if (response && response.success === false) {
            $('#results').html('<div style="color: red; padding: 10px; background: #ffe8e8; border-radius: 5px; margin-top: 10px;">❌ Error: ' + 
                              (response.message || 'Error desconocido') + '</div>');
          } else {
            $('#results').html('<div style="color: orange; padding: 10px; background: #fff3cd; border-radius: 5px; margin-top: 10px;">⚠️ Respuesta inesperada del servidor. Status: ' + xhr.status + '</div>');
          }
        } catch (e) {
          console.error('Error parsing response:', e);
          $('#results').html('<div style="color: red; padding: 10px; background: #ffe8e8; border-radius: 5px; margin-top: 10px;">❌ Error procesando respuesta del servidor<br>Status: ' + xhr.status + '<br>Response: ' + (xhr.responseText ? xhr.responseText.substring(0, 200) + '...' : 'Sin respuesta') + '</div>');
        }
      }
    });
  }

  // Función para actualizar la lista de subdominios activos
  function updateSubdomainsList(newSubdomains) {
    // Buscar la lista de subdominios
    var subdomainsList = $('p:contains("Subdominios activos:")').next('ul');
    
    if (subdomainsList.length === 0) {
      subdomainsList = $('strong:contains("Subdominios activos:")').parent().next('ul');
    }
    
    if (subdomainsList.length > 0) {
      // Limpiar la lista actual
      subdomainsList.empty();
      
      // Agregar los nuevos subdominios
      newSubdomains.forEach(function(subdomain) {
        var currentDomain = window.location.hostname;
        var subdomainUrl = 'https://' + subdomain + '.' + currentDomain;
        
        subdomainsList.append(
          '<li><a href="' + subdomainUrl + '" target="_blank">' + subdomain + '.' + currentDomain + '</a></li>'
        );
      });
      
      console.log('Lista de subdominios actualizada con:', newSubdomains);
    } else {
      console.log('No se pudo encontrar la lista de subdominios para actualizar');
    }
  }
