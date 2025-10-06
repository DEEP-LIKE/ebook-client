
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
      timeout: 300000, // 5 minutos para dar tiempo al API
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

// Función para procesamiento por chunks (evita timeouts)
function regenerate_sites_chunked() {
    console.log('regenerate_sites_chunked() called');
    
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
    
    // Inicializar variables de progreso
    var totalSites = 0;
    var processedSites = 0;
    var currentOffset = 0;
    var allResults = [];
    
    // Mostrar progreso inicial
    document.getElementById("progress_div").style.display = "block";
    $('#bar').width('0%');
    $('#percent').html('Iniciando...');
    $('#results').html('<div style="color: blue; padding: 10px; background: #e8f4fd; border-radius: 5px; margin-top: 10px;">🔄 PROCESAMIENTO POR CHUNKS...<br>⚡ Evita timeouts procesando sitios en lotes pequeños<br>📊 Iniciando...</div>');
    
    // Función recursiva para procesar chunks
    function processNextChunk() {
        console.log('Processing chunk with offset:', currentOffset);
        
        // Preparar datos para enviar
        var formData = new FormData();
        formData.append('chunked', 'true');
        formData.append('offset', currentOffset);
        formData.append('security_token', securityToken);
        
        if (requireFile && $('#security_file')[0].files[0]) {
            formData.append('zip_file', $('#security_file')[0].files[0]);
        }
        
        // Hacer petición AJAX para este chunk
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 120000, // 2 minutos por chunk
            success: function(response) {
                console.log('Chunk response:', response);
                
                if (response && response.success) {
                    // Actualizar totales
                    if (totalSites === 0) {
                        totalSites = response.totalSites || 0;
                    }
                    processedSites = response.processedSites || 0;
                    
                    // Actualizar progreso
                    var progressPercent = totalSites > 0 ? Math.round((processedSites / totalSites) * 100) : 0;
                    $('#bar').width(progressPercent + '%');
                    $('#percent').html(progressPercent + '%');
                    
                    // Actualizar mensaje
                    $('#results').html('<div style="color: blue; padding: 10px; background: #e8f4fd; border-radius: 5px; margin-top: 10px;">' +
                        '🔄 PROCESANDO CHUNK...<br>' +
                        '📊 Progreso: ' + processedSites + ' de ' + totalSites + ' sitios<br>' +
                        '✅ ' + response.message + '</div>');
                    
                    // Guardar resultados
                    allResults.push(response);
                    
                    // Verificar si hay más chunks
                    if (response.hasMore && response.nextOffset !== null) {
                        currentOffset = response.nextOffset;
                        // Continuar con el siguiente chunk después de una pausa
                        setTimeout(processNextChunk, 1000);
                    } else {
                        // Proceso completado
                        $('#bar').width('100%');
                        $('#percent').html('100%');
                        
                        // Combinar todos los resultados
                        var allHtml = '';
                        var allSubdomains = [];
                        
                        allResults.forEach(function(result) {
                            if (result.html) allHtml += result.html;
                            if (result.newSubdomains) {
                                result.newSubdomains.forEach(function(sub) {
                                    if (allSubdomains.indexOf(sub) === -1) {
                                        allSubdomains.push(sub);
                                    }
                                });
                            }
                        });
                        
                        $('#results').html('<div style="color: green; padding: 10px; background: #e8f5e8; border-radius: 5px; margin-top: 10px;">' +
                            '✅ PROCESO COMPLETADO<br>' +
                            '🎉 Se generaron ' + totalSites + ' sitios exitosamente<br>' +
                            allHtml + '</div>');
                        
                        // Actualizar lista de subdominios
                        if (allSubdomains.length > 0) {
                            updateSubdomainsList(allSubdomains);
                        }
                    }
                } else {
                    // Error en el chunk
                    $('#results').html('<div style="color: red; padding: 10px; background: #ffe8e8; border-radius: 5px; margin-top: 10px;">' +
                        '❌ Error en chunk: ' + (response.message || 'Error desconocido') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Chunk error:', error, status, xhr);
                $('#results').html('<div style="color: red; padding: 10px; background: #ffe8e8; border-radius: 5px; margin-top: 10px;">' +
                    '❌ Error de conexión en chunk: ' + error + '<br>' +
                    'Status: ' + xhr.status + '</div>');
            }
        });
    }
    
    // Iniciar el procesamiento
    processNextChunk();
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
