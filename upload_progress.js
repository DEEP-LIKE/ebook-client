
console.log('upload_progress.js loaded successfully');

// Manejar el checkbox de modo seguro
$(document).ready(function() {
    $('#require_file_security').change(function() {
        if ($(this).is(':checked')) {
            $('#file_upload_section').show();
        } else {
            $('#file_upload_section').hide();
        }
    });
});

function regenerate_sites() {
    console.log('regenerate_sites() called');
    var bar = $('#bar');
    var percent = $('#percent');
    
    // Verificar si se requiere modo seguro
    var requireFile = $('#require_file_security').is(':checked');
    var securityToken = $('#security_token').val();
    
    if (requireFile) {
        var fileInput = $('#security_file')[0];
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Por favor selecciona un archivo para el modo seguro.');
            return;
        }
    }
    
    // Mostrar progreso
    document.getElementById("progress_div").style.display="block";
    bar.width('0%');
    percent.html('Iniciando...');
    $('#results').html('<div style="color: blue; padding: 10px; background: #e8f4fd; border-radius: 5px; margin-top: 10px;">🔄 Regenerando sitios desde API...<br>⚡ Conectando al servidor...<br>⏱️ Proceso estimado: 30-60 segundos.</div>');
    
    // Simular progreso
    var progress = 0;
    var interval = setInterval(function() {
      progress += Math.random() * 10;
      if (progress > 90) progress = 90;
      bar.width(progress + '%');
      percent.html(Math.round(progress) + '%');
    }, 1000);
    
    // Preparar datos para enviar
    var formData = new FormData();
    formData.append('action', 'regenerate_sites');
    formData.append('security_token', securityToken);
    
    if (requireFile && $('#security_file')[0].files[0]) {
        formData.append('zip_file', $('#security_file')[0].files[0]);
    }
    
    // Hacer la petición AJAX
    $.ajax({
      url: window.location.href,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      timeout: 120000, // 2 minutos
      success: function(response) {
        clearInterval(interval);
        bar.width('100%');
        percent.html('100%');
        
        console.log('Success response:', response);
        handleResponse(response);
      },
      error: function(xhr, status, error) {
        clearInterval(interval);
        console.error('Error:', error);
        console.error('Status:', status);
        console.error('XHR:', xhr);
        console.error('Response Text:', xhr.responseText);
        
        var errorMsg = 'Error de conexión: ' + error;
        if (status === 'timeout') {
          errorMsg = 'Timeout: El proceso tomó demasiado tiempo. Los sitios pueden haberse generado correctamente.';
        } else if (xhr.responseText && xhr.responseText.includes('SyntaxError')) {
          errorMsg = 'Error de formato JSON. Revisa los logs del servidor.';
        } else if (xhr.status === 500) {
          errorMsg = 'Error interno del servidor (500). Revisa los logs.';
        }
        
        $('#results').html('<div style="color: red; padding: 10px; background: #ffe8e8; border-radius: 5px; margin-top: 10px;">❌ ' + errorMsg + '<br><small>Status: ' + xhr.status + '</small></div>');
      }
    });
}

function upload_image() {
    // Función legacy - redirigir a la nueva función
    regenerate_sites();
}

function force_regenerate_sites() {
    if (!confirm('⚠️ ATENCIÓN: Esto eliminará TODOS los sitios existentes y los regenerará desde cero.\n\n¿Estás seguro de que quieres continuar?')) {
        return;
    }
    
    console.log('force_regenerate_sites() called');
    var bar = $('#bar');
    var percent = $('#percent');
    
    // Verificar si se requiere modo seguro
    var requireFile = $('#require_file_security').is(':checked');
    var securityToken = $('#security_token').val();
    
    if (requireFile) {
        var fileInput = $('#security_file')[0];
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Por favor selecciona un archivo para el modo seguro.');
            return;
        }
    }
    
    // Mostrar progreso
    document.getElementById("progress_div").style.display="block";
    bar.width('0%');
    percent.html('Iniciando...');
    $('#results').html('<div style="color: orange; padding: 10px; background: #fff3cd; border-radius: 5px; margin-top: 10px;">🔄 REGENERACIÓN COMPLETA EN PROGRESO...<br>⚠️ Eliminando sitios existentes...<br>⚡ Regenerando desde cero...<br>⏱️ Proceso estimado: 60-90 segundos.</div>');
    
    // Simular progreso más lento para regeneración completa
    var progress = 0;
    var interval = setInterval(function() {
      progress += Math.random() * 5; // Más lento
      if (progress > 85) progress = 85;
      bar.width(progress + '%');
      percent.html(Math.round(progress) + '%');
    }, 1500);
    
    // Preparar datos para enviar
    var formData = new FormData();
    formData.append('action', 'force_regenerate_sites');
    formData.append('security_token', securityToken);
    
    if (requireFile && $('#security_file')[0].files[0]) {
        formData.append('zip_file', $('#security_file')[0].files[0]);
    }
    
    // Hacer la petición AJAX con timeout más largo
    $.ajax({
      url: window.location.href,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      timeout: 180000, // 3 minutos para regeneración completa
      success: function(response) {
        clearInterval(interval);
        bar.width('100%');
        percent.html('100%');
        
        console.log('Force regenerate success:', response);
        handleResponse(response);
      },
      error: function(xhr, status, error) {
        clearInterval(interval);
        console.error('Force regenerate error:', error);
        console.error('Status:', status);
        console.error('XHR:', xhr);
        console.error('Response Text:', xhr.responseText);
        
        var errorMsg = 'Error en regeneración completa: ' + error;
        if (status === 'timeout') {
          errorMsg = 'Timeout en regeneración completa. El proceso puede estar aún ejecutándose.';
        }
        
        $('#results').html('<div style="color: red; padding: 10px; background: #ffe8e8; border-radius: 5px; margin-top: 10px;">❌ ' + errorMsg + '<br><small>Status: ' + xhr.status + '</small></div>');
      }
    });
}

function handleResponse(response) {
    try {
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
        $('#results').html('<div style="color: orange; padding: 10px; background: #fff3cd; border-radius: 5px; margin-top: 10px;">⚠️ Respuesta inesperada del servidor.</div>');
      }
    } catch (e) {
      console.error('Error parsing response:', e);
      $('#results').html('<div style="color: red; padding: 10px; background: #ffe8e8; border-radius: 5px; margin-top: 10px;">❌ Error procesando respuesta del servidor</div>');
    }
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
