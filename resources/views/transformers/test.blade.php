<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Transformadores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-transformation {
            transition: all 0.3s;
        }
        .card-transformation:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .transformation-progress {
            height: 10px;
            margin-top: 10px;
        }
        #dropzone {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        #dropzone:hover, #dropzone.dragover {
            background-color: #f8f9fa;
            border-color: #6c757d;
        }
        #dropzone.dragover {
            background-color: #e9ecef;
        }
        .result-container {
            display: none;
            margin-top: 30px;
            padding: 20px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .loading {
            text-align: center;
            display: none;
            margin: 20px 0;
        }
        .loading i {
            font-size: 2rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Teste de Transformadores</h1>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Upload de Arquivo para Transformação</h5>
                    </div>
                    <div class="card-body">
                        <div id="dropzone">
                            <i class="fas fa-upload fa-2x mb-2"></i>
                            <p>Arraste e solte um arquivo aqui ou clique para selecionar</p>
                            <input type="file" id="fileInput" class="d-none">
                        </div>
                        
                        <div class="selected-file alert alert-info d-none">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file me-2"></i>
                                    <span id="selected-filename">Nenhum arquivo selecionado</span>
                                </div>
                                <button class="btn btn-sm btn-outline-info" id="change-file">
                                    <i class="fas fa-exchange-alt"></i> Trocar
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Selecione uma transformação:</h5>
                            
                            <div class="transformation-filters mb-3">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary active" data-filter="all">Todos</button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="json">JSON</button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="xml">XML</button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="csv">CSV</button>
                                    <button type="button" class="btn btn-outline-primary" data-filter="xlsx">XLSX</button>
                                </div>
                            </div>
                            
                            <div class="row transformation-cards">
                                @forelse($transformationMaps as $map)
                                <div class="col-md-4 mb-3 transformation-item" data-from="{{ $map->from_type }}">
                                    <div class="card card-transformation">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $map->name }}</h5>
                                            <div class="badge bg-primary mb-2">{{ strtoupper($map->from_type) }} → {{ strtoupper($map->to_type) }}</div>
                                            <p class="card-text">{{ $map->description ?? 'Sem descrição' }}</p>
                                            <button class="btn btn-primary transform-btn w-100" 
                                                data-id="{{ $map->id }}" 
                                                data-from="{{ $map->from_type }}" 
                                                data-to="{{ $map->to_type }}">
                                                Transformar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        Nenhum mapa de transformação encontrado. Por favor, crie um mapa de transformação primeiro.
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="loading">
            <i class="fas fa-spinner fa-spin mb-2"></i>
            <p>Processando transformação...</p>
            <div class="progress transformation-progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
        
        <div class="result-container">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Resultado da Transformação</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <span id="result-message">Arquivo transformado com sucesso!</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>De:</strong> <span id="result-from-type">JSON</span>
                            <strong class="ms-3">Para:</strong> <span id="result-to-type">XML</span>
                        </div>
                        
                        <a href="#" id="download-link" class="btn btn-success" target="_blank">
                            <i class="fas fa-download me-2"></i> Baixar Arquivo Transformado
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="error-container alert alert-danger mt-4" style="display:none;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <span id="error-message"></span>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Drag and drop
            const dropzone = $('#dropzone');
            const fileInput = $('#fileInput');
            const selectedFile = $('.selected-file');
            const selectedFilename = $('#selected-filename');
            
            dropzone.on('click', function() {
                fileInput.click();
            });
            
            fileInput.on('change', function() {
                if (this.files.length > 0) {
                    selectedFilename.text(this.files[0].name);
                    selectedFile.removeClass('d-none');
                    dropzone.addClass('d-none');
                }
            });
            
            $('#change-file').on('click', function(e) {
                e.preventDefault();
                selectedFile.addClass('d-none');
                dropzone.removeClass('d-none');
                fileInput.val('');
            });
            
            // Drag and drop events
            dropzone.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            
            dropzone.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            
            dropzone.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                if (e.originalEvent.dataTransfer.files.length > 0) {
                    const file = e.originalEvent.dataTransfer.files[0];
                    fileInput[0].files = e.originalEvent.dataTransfer.files;
                    selectedFilename.text(file.name);
                    selectedFile.removeClass('d-none');
                    dropzone.addClass('d-none');
                }
            });
            
            // Filter transformations
            $('.transformation-filters button').on('click', function() {
                $('.transformation-filters button').removeClass('active');
                $(this).addClass('active');
                
                const filter = $(this).data('filter');
                
                if (filter === 'all') {
                    $('.transformation-item').show();
                } else {
                    $('.transformation-item').hide();
                    $(`.transformation-item[data-from="${filter}"]`).show();
                }
            });
            
            // Transform button click
            $('.transform-btn').on('click', function() {
                const mapId = $(this).data('id');
                const fromType = $(this).data('from');
                const toType = $(this).data('to');
                
                if (!fileInput[0].files.length) {
                    alert('Por favor, selecione um arquivo primeiro');
                    return;
                }
                
                const file = fileInput[0].files[0];
                const fileExtension = file.name.split('.').pop().toLowerCase();
                
                if (fileExtension !== fromType) {
                    alert(`Este transformador espera um arquivo ${fromType.toUpperCase()}, mas você selecionou um arquivo ${fileExtension.toUpperCase()}`);
                    return;
                }
                
                $('.error-container').hide();
                $('.result-container').hide();
                $('.loading').show();
                
                // Simulação de progresso
                let progress = 0;
                const progressBar = $('.progress-bar');
                const progressInterval = setInterval(function() {
                    progress += 5;
                    if (progress > 90) {
                        clearInterval(progressInterval);
                    }
                    progressBar.css('width', `${progress}%`);
                }, 300);
                
                // Criar FormData para envio do arquivo
                const formData = new FormData();
                formData.append('file', file);
                formData.append('transformation_map_id', mapId);
                
                // Enviar requisição AJAX
                $.ajax({
                    url: '/api/transformers/test',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        clearInterval(progressInterval);
                        progressBar.css('width', '100%');
                        
                        setTimeout(function() {
                            $('.loading').hide();
                            
                            if (response.success) {
                                $('#result-message').text(response.message);
                                $('#result-from-type').text(response.from_type.toUpperCase());
                                $('#result-to-type').text(response.to_type.toUpperCase());
                                $('#download-link').attr('href', response.output_url);
                                $('.result-container').show();
                            } else {
                                $('#error-message').text(response.message);
                                $('.error-container').show();
                            }
                        }, 500);
                    },
                    error: function(xhr) {
                        clearInterval(progressInterval);
                        $('.loading').hide();
                        
                        let errorMessage = 'Erro ao processar a transformação';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        $('#error-message').text(errorMessage);
                        $('.error-container').show();
                    }
                });
            });
        });
    </script>
</body>
</html> 