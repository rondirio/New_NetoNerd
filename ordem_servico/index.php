<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de OS - NetoNerd</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .os-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-bottom: 30px;
        }

        .header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 0.9em;
        }

        .os-numero {
            text-align: right;
            font-size: 1.2em;
            color: #333;
            margin-bottom: 20px;
        }

        .os-numero span {
            font-weight: bold;
            color: #667eea;
            font-size: 1.5em;
        }

        .section-title {
            font-size: 1.3em;
            color: #333;
            margin: 25px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-row {
            display: grid;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-row.cols-2 {
            grid-template-columns: 1fr 1fr;
        }

        .form-row.cols-3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            font-family: inherit;
        }

        .assinaturas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }

        .assinatura-box {
            text-align: center;
        }

        .linha-assinatura {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 10px;
            font-weight: 600;
        }

        .btn-container {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        button {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-salvar {
            background: #10b981;
            color: white;
        }

        .btn-salvar:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-nova {
            background: #667eea;
            color: white;
        }

        .btn-nova:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }

        .btn-imprimir {
            background: #6b7280;
            color: white;
        }

        .btn-imprimir:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .historico {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .historico h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .historico-item {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: start;
            transition: border-color 0.3s;
        }

        .historico-item:hover {
            border-color: #667eea;
        }

        .historico-info p {
            margin-bottom: 5px;
        }

        .historico-info .os-num {
            font-weight: bold;
            color: #667eea;
            font-size: 1.1em;
        }

        .historico-info .cliente {
            font-weight: bold;
            color: #333;
        }

        .historico-info .detalhe {
            color: #666;
            font-size: 0.9em;
        }

        .btn-excluir {
            background: #ef4444;
            color: white;
            padding: 8px 12px;
            font-size: 0.9em;
        }

        .btn-excluir:hover {
            background: #dc2626;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .os-card {
                box-shadow: none;
                padding: 20px;
            }

            .btn-container,
            .historico {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .form-row.cols-2,
            .form-row.cols-3 {
                grid-template-columns: 1fr;
            }

            .assinaturas {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .btn-container {
                flex-direction: column;
            }

            button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="os-card">
            <!-- Cabeçalho -->
            <div class="header">
                <h1>NetoNerd</h1>
                <p>CNPJ: 51.243.583/0001-12</p>
                <p>Assistência Técnica em Informática</p>
            </div>

            <div class="os-numero">
                <p>Ordem de Serviço Nº <span id="numeroOS">001</span></p>
            </div>

            <!-- Datas -->
            <div class="form-row cols-3">
                <div class="form-group">
                    <label for="dataEntrada">Data de Entrada</label>
                    <input type="date" id="dataEntrada">
                </div>
                <div class="form-group">
                    <label for="prazo">Prazo de Entrega</label>
                    <input type="date" id="prazo">
                </div>
                <div class="form-group">
                    <label for="dataEntrega">Data de Entrega</label>
                    <input type="date" id="dataEntrega">
                </div>
            </div>

            <!-- Dados do Cliente -->
            <h3 class="section-title">Dados do Cliente</h3>
            <div class="form-group">
                <label for="nomeCliente">Nome Completo</label>
                <input type="text" id="nomeCliente" placeholder="Nome do cliente">
            </div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" placeholder="(00) 00000-0000">
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" placeholder="email@exemplo.com">
                </div>
            </div>

            <!-- Especificações do Equipamento -->
            <h3 class="section-title">Especificações do Equipamento</h3>
            <div class="form-group">
                <label for="modeloComputador">Modelo do Computador</label>
                <input type="text" id="modeloComputador" placeholder="Ex: Dell Inspiron 15, Notebook Acer Aspire 5">
            </div>
            <div class="form-row cols-3">
                <div class="form-group">
                    <label for="processador">Processador</label>
                    <input type="text" id="processador" placeholder="Ex: Intel i5 10ª Gen">
                </div>
                <div class="form-group">
                    <label for="memRam">Memória RAM</label>
                    <input type="text" id="memRam" placeholder="Ex: 8GB DDR4">
                </div>
                <div class="form-group">
                    <label for="memSecundaria">Armazenamento</label>
                    <input type="text" id="memSecundaria" placeholder="Ex: SSD 256GB">
                </div>
            </div>

            <!-- Serviços -->
            <h3 class="section-title">Serviços</h3>
            <div class="form-group">
                <label for="servicoPedido">Serviço Solicitado</label>
                <textarea id="servicoPedido" rows="3" placeholder="Descreva o problema relatado pelo cliente..."></textarea>
            </div>
            <div class="form-group">
                <label for="servicoFeito">Serviço Realizado</label>
                <textarea id="servicoFeito" rows="3" placeholder="Descreva os serviços realizados..."></textarea>
            </div>
            <div class="form-group">
                <label for="conclusao">Conclusão/Observações</label>
                <textarea id="conclusao" rows="2" placeholder="Observações finais, recomendações, etc..."></textarea>
            </div>
            <div class="form-group" style="max-width: 300px;">
                <label for="valor">Valor do Serviço (R$)</label>
                <input type="text" id="valor" placeholder="0,00">
            </div>

            <!-- Assinaturas -->
            <div class="assinaturas">
                <div class="assinatura-box">
                    <div class="linha-assinatura">Técnico Responsável</div>
                </div>
                <div class="assinatura-box">
                    <div class="linha-assinatura">Cliente</div>
                </div>
            </div>

            <!-- Botões -->
            <div class="btn-container">
                <button class="btn-salvar" onclick="salvarOS()">
                    ✓ Salvar OS
                </button>
                <button class="btn-nova" onclick="novaOS()">
                    + Nova OS
                </button>
                <button class="btn-imprimir" onclick="window.print()">
                    🖨️ Imprimir
                </button>
            </div>
        </div>

        <!-- Histórico -->
        <div class="historico" id="historicoContainer" style="display: none;">
            <h3>Histórico de Ordens de Serviço</h3>
            <div id="historicoLista"></div>
        </div>
    </div>

    <script>
        // Inicializar data de entrada com data atual
        document.getElementById('dataEntrada').valueAsDate = new Date();

        // Array para armazenar histórico de OS
        let historico = [];
        let numeroAtual = 1;

        function salvarOS() {
            const nomeCliente = document.getElementById('nomeCliente').value;
            const servicoPedido = document.getElementById('servicoPedido').value;

            if (!nomeCliente || !servicoPedido) {
                alert('⚠️ Preencha pelo menos o nome do cliente e o serviço pedido!');
                return;
            }

            const os = {
                id: Date.now(),
                numero: document.getElementById('numeroOS').textContent,
                dataEntrada: document.getElementById('dataEntrada').value,
                prazo: document.getElementById('prazo').value,
                dataEntrega: document.getElementById('dataEntrega').value,
                nomeCliente: nomeCliente,
                telefone: document.getElementById('telefone').value,
                email: document.getElementById('email').value,
                modeloComputador: document.getElementById('modeloComputador').value,
                processador: document.getElementById('processador').value,
                memRam: document.getElementById('memRam').value,
                memSecundaria: document.getElementById('memSecundaria').value,
                servicoPedido: servicoPedido,
                servicoFeito: document.getElementById('servicoFeito').value,
                conclusao: document.getElementById('conclusao').value,
                valor: document.getElementById('valor').value
            };

            historico.push(os);
            atualizarHistorico();
            alert('✅ OS salva com sucesso!');
        }

        function novaOS() {
            numeroAtual++;
            document.getElementById('numeroOS').textContent = String(numeroAtual).padStart(3, '0');
            
            // Limpar todos os campos
            document.getElementById('dataEntrada').valueAsDate = new Date();
            document.getElementById('prazo').value = '';
            document.getElementById('dataEntrega').value = '';
            document.getElementById('nomeCliente').value = '';
            document.getElementById('telefone').value = '';
            document.getElementById('email').value = '';
            document.getElementById('modeloComputador').value = '';
            document.getElementById('processador').value = '';
            document.getElementById('memRam').value = '';
            document.getElementById('memSecundaria').value = '';
            document.getElementById('servicoPedido').value = '';
            document.getElementById('servicoFeito').value = '';
            document.getElementById('conclusao').value = '';
            document.getElementById('valor').value = '';
        }

        function atualizarHistorico() {
            const container = document.getElementById('historicoContainer');
            const lista = document.getElementById('historicoLista');

            if (historico.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';
            lista.innerHTML = '';

            historico.forEach(os => {
                const item = document.createElement('div');
                item.className = 'historico-item';
                item.innerHTML = '<div class="historico-info">' +
                    '<p><span class="os-num">OS Nº ' + os.numero + '</span> - <span class="cliente">' + os.nomeCliente + '</span></p>' +
                    '<p class="detalhe">' + os.modeloComputador + '</p>' +
                    '<p class="detalhe">Entrada: ' + formatarData(os.dataEntrada) + '</p>' +
                    '<p class="detalhe">' + os.servicoPedido + '</p>' +
                    '</div>' +
                    '<button class="btn-excluir" onclick="removerOS(' + os.id + ')">🗑️ Excluir</button>';
                lista.appendChild(item);
            });
        }

        function removerOS(id) {
            if (confirm('Deseja realmente excluir esta OS?')) {
                historico = historico.filter(os => os.id !== id);
                atualizarHistorico();
            }
        }

        function formatarData(data) {
            if (!data) return '';
            const partes = data.split('-');
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }
    </script>
</body>
</html>