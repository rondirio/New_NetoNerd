<?php
require_once __DIR__ . '/Database.php';

class Despesa {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Cria a tabela de despesas se não existir
     */
    public function criarTabela() {
        $sql = "CREATE TABLE IF NOT EXISTS despesas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome_conta VARCHAR(255) NOT NULL,
            descricao TEXT,
            valor DECIMAL(10, 2) NOT NULL,
            data_vencimento DATE NOT NULL,
            modo_pagamento ENUM('Dinheiro', 'Cartão Crédito', 'Cartão Débito', 'PIX', 'Boleto', 'Transferência') NOT NULL,
            debito_automatico BOOLEAN DEFAULT FALSE,
            recorrente BOOLEAN DEFAULT FALSE,
            dia_vencimento_recorrente INT NULL,
            status ENUM('Pendente', 'Pago', 'Vencido') DEFAULT 'Pendente',
            data_pagamento DATE NULL,
            categoria VARCHAR(100),
            observacoes TEXT,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_data_vencimento (data_vencimento),
            INDEX idx_status (status),
            INDEX idx_recorrente (recorrente)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        try {
            $this->conn->exec($sql);
            return true;
        } catch(PDOException $e) {
            throw new Exception("Erro ao criar tabela: " . $e->getMessage());
        }
    }
    
    /**
     * Adiciona uma nova despesa
     */
    public function adicionar($dados) {
        $sql = "INSERT INTO despesas (usuario_id, nome_conta, descricao, valor, data_vencimento, 
                modo_pagamento, debito_automatico, recorrente, dia_vencimento_recorrente, 
                categoria, observacoes, status) 
                VALUES (:usuario_id, :nome_conta, :descricao, :valor, :data_vencimento, 
                :modo_pagamento, :debito_automatico, :recorrente, :dia_vencimento_recorrente,
                :categoria, :observacoes, :status)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $dados['usuario_id'],
                ':nome_conta' => $dados['nome_conta'],
                ':descricao' => $dados['descricao'] ?? null,
                ':valor' => $dados['valor'],
                ':data_vencimento' => $dados['data_vencimento'],
                ':modo_pagamento' => $dados['modo_pagamento'],
                ':debito_automatico' => $dados['debito_automatico'] ?? 0,
                ':recorrente' => $dados['recorrente'] ?? 0,
                ':dia_vencimento_recorrente' => $dados['dia_vencimento_recorrente'] ?? null,
                ':categoria' => $dados['categoria'] ?? null,
                ':observacoes' => $dados['observacoes'] ?? null,
                ':status' => $dados['status'] ?? 'Pendente'
            ]);
            
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            throw new Exception("Erro ao adicionar despesa: " . $e->getMessage());
        }
    }
    
    /**
     * Atualiza uma despesa existente
     */
    public function atualizar($id, $dados) {
        $sql = "UPDATE despesas SET 
                nome_conta = :nome_conta,
                descricao = :descricao,
                valor = :valor,
                data_vencimento = :data_vencimento,
                modo_pagamento = :modo_pagamento,
                debito_automatico = :debito_automatico,
                recorrente = :recorrente,
                dia_vencimento_recorrente = :dia_vencimento_recorrente,
                categoria = :categoria,
                observacoes = :observacoes,
                status = :status
                WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':nome_conta' => $dados['nome_conta'],
                ':descricao' => $dados['descricao'] ?? null,
                ':valor' => $dados['valor'],
                ':data_vencimento' => $dados['data_vencimento'],
                ':modo_pagamento' => $dados['modo_pagamento'],
                ':debito_automatico' => $dados['debito_automatico'] ?? 0,
                ':recorrente' => $dados['recorrente'] ?? 0,
                ':dia_vencimento_recorrente' => $dados['dia_vencimento_recorrente'] ?? null,
                ':categoria' => $dados['categoria'] ?? null,
                ':observacoes' => $dados['observacoes'] ?? null,
                ':status' => $dados['status']
            ]);
        } catch(PDOException $e) {
            throw new Exception("Erro ao atualizar despesa: " . $e->getMessage());
        }
    }
    
    /**
     * Marca despesa como paga
     */
    public function marcarComoPago($id, $usuarioId = null) {
        // Se usuario_id foi fornecido, verificar se a despesa pertence ao usuário
        if ($usuarioId) {
            $sql = "UPDATE despesas SET status = 'Pago', data_pagamento = CURDATE() 
                    WHERE id = :id AND usuario_id = :usuario_id";
            $params = [':id' => $id, ':usuario_id' => $usuarioId];
        } else {
            $sql = "UPDATE despesas SET status = 'Pago', data_pagamento = CURDATE() WHERE id = :id";
            $params = [':id' => $id];
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($params);
            
            // Verificar se alguma linha foi afetada
            if ($stmt->rowCount() === 0) {
                throw new Exception("Despesa não encontrada ou você não tem permissão para alterá-la.");
            }
            
            return $result;
        } catch(PDOException $e) {
            throw new Exception("Erro ao marcar como pago: " . $e->getMessage());
        }
    }
    
    /**
     * Deleta uma despesa
     */
    public function deletar($id, $usuarioId = null) {
        // Se usuario_id foi fornecido, verificar se a despesa pertence ao usuário
        if ($usuarioId) {
            $sql = "DELETE FROM despesas WHERE id = :id AND usuario_id = :usuario_id";
            $params = [':id' => $id, ':usuario_id' => $usuarioId];
        } else {
            $sql = "DELETE FROM despesas WHERE id = :id";
            $params = [':id' => $id];
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($params);
            
            // Verificar se alguma linha foi afetada
            if ($stmt->rowCount() === 0) {
                throw new Exception("Despesa não encontrada ou você não tem permissão para deletá-la.");
            }
            
            return $result;
        } catch(PDOException $e) {
            throw new Exception("Erro ao deletar despesa: " . $e->getMessage());
        }
    }
    
    /**
     * Busca despesa por ID
     */
    public function buscarPorId($id, $usuarioId = null) {
        if ($usuarioId) {
            $sql = "SELECT * FROM despesas WHERE id = :id AND usuario_id = :usuario_id";
            $params = [':id' => $id, ':usuario_id' => $usuarioId];
        } else {
            $sql = "SELECT * FROM despesas WHERE id = :id";
            $params = [':id' => $id];
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch(PDOException $e) {
            throw new Exception("Erro ao buscar despesa: " . $e->getMessage());
        }
    }
    
    /**
     * Lista todas as despesas com filtros opcionais
     */
    public function listar($filtros = []) {
        $sql = "SELECT * FROM despesas WHERE 1=1";
        $params = [];
        
        // IMPORTANTE: Filtrar por usuário
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND usuario_id = :usuario_id";
            $params[':usuario_id'] = $filtros['usuario_id'];
        }
        
        if (!empty($filtros['mes'])) {
            $sql .= " AND MONTH(data_vencimento) = :mes";
            $params[':mes'] = $filtros['mes'];
        }
        
        if (!empty($filtros['ano'])) {
            $sql .= " AND YEAR(data_vencimento) = :ano";
            $params[':ano'] = $filtros['ano'];
        }
        
        if (!empty($filtros['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filtros['status'];
        }
        
        if (!empty($filtros['categoria'])) {
            $sql .= " AND categoria = :categoria";
            $params[':categoria'] = $filtros['categoria'];
        }
        
        $sql .= " ORDER BY data_vencimento ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            throw new Exception("Erro ao listar despesas: " . $e->getMessage());
        }
    }
    
    /**
     * Atualiza status de despesas vencidas
     */
    public function atualizarVencidas() {
        $sql = "UPDATE despesas SET status = 'Vencido' 
                WHERE data_vencimento < CURDATE() AND status = 'Pendente'";
        
        try {
            return $this->conn->exec($sql);
        } catch(PDOException $e) {
            throw new Exception("Erro ao atualizar vencidas: " . $e->getMessage());
        }
    }
    
    /**
     * Calcula total pago no mês
     */
    public function totalPagoMes($mes, $ano, $usuarioId = null) {
        $sql = "SELECT COALESCE(SUM(valor), 0) as total 
                FROM despesas 
                WHERE status = 'Pago' 
                AND MONTH(data_vencimento) = :mes 
                AND YEAR(data_vencimento) = :ano";
        
        $params = [':mes' => $mes, ':ano' => $ano];
        
        if ($usuarioId) {
            $sql .= " AND usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['total'];
        } catch(PDOException $e) {
            throw new Exception("Erro ao calcular total pago: " . $e->getMessage());
        }
    }
    
    /**
     * Calcula total pendente no mês
     */
    public function totalPendenteMes($mes, $ano, $usuarioId = null) {
        $sql = "SELECT COALESCE(SUM(valor), 0) as total 
                FROM despesas 
                WHERE status IN ('Pendente', 'Vencido')
                AND MONTH(data_vencimento) = :mes 
                AND YEAR(data_vencimento) = :ano";
        
        $params = [':mes' => $mes, ':ano' => $ano];
        
        if ($usuarioId) {
            $sql .= " AND usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['total'];
        } catch(PDOException $e) {
            throw new Exception("Erro ao calcular total pendente: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém estatísticas do mês
     */
    public function estatisticasMes($mes, $ano, $usuarioId = null) {
        $sql = "SELECT 
                COUNT(*) as total_contas,
                COUNT(CASE WHEN status = 'Pago' THEN 1 END) as pagas,
                COUNT(CASE WHEN status = 'Pendente' THEN 1 END) as pendentes,
                COUNT(CASE WHEN status = 'Vencido' THEN 1 END) as vencidas,
                COALESCE(SUM(CASE WHEN status = 'Pago' THEN valor END), 0) as valor_pago,
                COALESCE(SUM(CASE WHEN status IN ('Pendente', 'Vencido') THEN valor END), 0) as valor_pendente,
                COALESCE(SUM(valor), 0) as valor_total
                FROM despesas 
                WHERE MONTH(data_vencimento) = :mes 
                AND YEAR(data_vencimento) = :ano";
        
        $params = [':mes' => $mes, ':ano' => $ano];
        
        if ($usuarioId) {
            $sql .= " AND usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch(PDOException $e) {
            throw new Exception("Erro ao obter estatísticas: " . $e->getMessage());
        }
    }
    
    /**
     * Busca boletos pendentes (para integração com API)
     */
    public function boletosPendentes() {
        $sql = "SELECT * FROM despesas 
                WHERE modo_pagamento = 'Boleto' 
                AND status IN ('Pendente', 'Vencido')
                ORDER BY data_vencimento ASC";
        
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            throw new Exception("Erro ao buscar boletos: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém categorias únicas
     */
    public function obterCategorias() {
        $sql = "SELECT DISTINCT categoria FROM despesas WHERE categoria IS NOT NULL ORDER BY categoria";
        
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch(PDOException $e) {
            throw new Exception("Erro ao obter categorias: " . $e->getMessage());
        }
    }
    
    /**
     * Lista apenas despesas recorrentes
     */
    public function listarRecorrentes() {
        $sql = "SELECT * FROM despesas 
                WHERE recorrente = TRUE 
                ORDER BY dia_vencimento_recorrente ASC";
        
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            throw new Exception("Erro ao listar recorrentes: " . $e->getMessage());
        }
    }
    
    /**
     * Gera despesas recorrentes para o próximo mês
     */
    public function gerarRecorrentes() {
        try {
            // Calcular próximo mês
            $mesAtual = date('n');
            $anoAtual = date('Y');
            
            if ($mesAtual == 12) {
                $proxMes = 1;
                $proxAno = $anoAtual + 1;
            } else {
                $proxMes = $mesAtual + 1;
                $proxAno = $anoAtual;
            }
            
            // Buscar despesas recorrentes
            $recorrentes = $this->listarRecorrentes();
            $criadas = 0;
            
            foreach ($recorrentes as $despesa) {
                // Verificar se já existe para o próximo mês
                $sqlCheck = "SELECT COUNT(*) FROM despesas 
                            WHERE nome_conta = :nome_conta 
                            AND MONTH(data_vencimento) = :mes 
                            AND YEAR(data_vencimento) = :ano";
                
                $stmtCheck = $this->conn->prepare($sqlCheck);
                $stmtCheck->execute([
                    ':nome_conta' => $despesa['nome_conta'],
                    ':mes' => $proxMes,
                    ':ano' => $proxAno
                ]);
                
                $existe = $stmtCheck->fetchColumn();
                
                // Se não existe, criar
                if (!$existe) {
                    $diaVenc = $despesa['dia_vencimento_recorrente'];
                    
                    // Validar dia do mês
                    $ultimoDiaMes = cal_days_in_month(CAL_GREGORIAN, $proxMes, $proxAno);
                    if ($diaVenc > $ultimoDiaMes) {
                        $diaVenc = $ultimoDiaMes;
                    }
                    
                    $novaData = sprintf('%04d-%02d-%02d', $proxAno, $proxMes, $diaVenc);
                    
                    $sqlInsert = "INSERT INTO despesas (
                        nome_conta, descricao, valor, data_vencimento,
                        modo_pagamento, debito_automatico, recorrente, 
                        dia_vencimento_recorrente, categoria, observacoes, status
                    ) VALUES (
                        :nome_conta, :descricao, :valor, :data_vencimento,
                        :modo_pagamento, :debito_automatico, :recorrente,
                        :dia_vencimento_recorrente, :categoria, :observacoes, 'Pendente'
                    )";
                    
                    $stmtInsert = $this->conn->prepare($sqlInsert);
                    $stmtInsert->execute([
                        ':nome_conta' => $despesa['nome_conta'],
                        ':descricao' => $despesa['descricao'],
                        ':valor' => $despesa['valor'],
                        ':data_vencimento' => $novaData,
                        ':modo_pagamento' => $despesa['modo_pagamento'],
                        ':debito_automatico' => $despesa['debito_automatico'],
                        ':recorrente' => 1,
                        ':dia_vencimento_recorrente' => $despesa['dia_vencimento_recorrente'],
                        ':categoria' => $despesa['categoria'],
                        ':observacoes' => $despesa['observacoes']
                    ]);
                    
                    $criadas++;
                }
            }
            
            return $criadas;
            
        } catch(PDOException $e) {
            throw new Exception("Erro ao gerar recorrentes: " . $e->getMessage());
        }
    }
    
    /**
     * Remove recorrência de uma despesa (mantém a despesa atual)
     */
    public function removerRecorrencia($id) {
        $sql = "UPDATE despesas 
                SET recorrente = FALSE, dia_vencimento_recorrente = NULL 
                WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch(PDOException $e) {
            throw new Exception("Erro ao remover recorrência: " . $e->getMessage());
        }
    }
    
    /**
     * Conta quantas despesas recorrentes existem
     */
    public function contarRecorrentes() {
        $sql = "SELECT COUNT(*) FROM despesas WHERE recorrente = TRUE";
        
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            throw new Exception("Erro ao contar recorrentes: " . $e->getMessage());
        }
    }
    
    /**
     * Cria despesas parceladas
     */
    public function criarParceladas($dados, $totalParcelas) {
        try {
            // Gerar UUID para o grupo de parcelamento
            $grupoParcelamento = $this->gerarUUID();
            
            $parcelasCriadas = 0;
            $valorParcela = $dados['valor_total'] / $totalParcelas;
            
            // Data inicial
            $dataInicial = new DateTime($dados['data_vencimento']);
            
            // Criar cada parcela
            for ($i = 1; $i <= $totalParcelas; $i++) {
                // Calcular data de vencimento da parcela
                if ($i > 1) {
                    $dataInicial->modify('+1 month');
                }
                
                $despesaParcela = [
                    'usuario_id' => $dados['usuario_id'],
                    'nome_conta' => $dados['nome_conta'] . " ({$i}/{$totalParcelas})",
                    'descricao' => $dados['descricao'],
                    'valor' => round($valorParcela, 2),
                    'data_vencimento' => $dataInicial->format('Y-m-d'),
                    'modo_pagamento' => $dados['modo_pagamento'],
                    'debito_automatico' => $dados['debito_automatico'] ?? 0,
                    'recorrente' => 0,
                    'dia_vencimento_recorrente' => null,
                    'parcelado' => 1,
                    'parcela_atual' => $i,
                    'total_parcelas' => $totalParcelas,
                    'grupo_parcelamento' => $grupoParcelamento,
                    'categoria' => $dados['categoria'] ?? null,
                    'observacoes' => $dados['observacoes'] ?? null,
                    'status' => 'Pendente'
                ];
                
                $this->adicionarParcelada($despesaParcela);
                $parcelasCriadas++;
            }
            
            return [
                'parcelas_criadas' => $parcelasCriadas,
                'grupo_parcelamento' => $grupoParcelamento,
                'valor_parcela' => round($valorParcela, 2)
            ];
            
        } catch (Exception $e) {
            throw new Exception("Erro ao criar parcelas: " . $e->getMessage());
        }
    }
    
    /**
     * Adiciona uma despesa parcelada (interno)
     */
    private function adicionarParcelada($dados) {
        $sql = "INSERT INTO despesas (usuario_id, nome_conta, descricao, valor, data_vencimento, 
                modo_pagamento, debito_automatico, recorrente, dia_vencimento_recorrente,
                parcelado, parcela_atual, total_parcelas, grupo_parcelamento,
                categoria, observacoes, status) 
                VALUES (:usuario_id, :nome_conta, :descricao, :valor, :data_vencimento, 
                :modo_pagamento, :debito_automatico, :recorrente, :dia_vencimento_recorrente,
                :parcelado, :parcela_atual, :total_parcelas, :grupo_parcelamento,
                :categoria, :observacoes, :status)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $dados['usuario_id'],
                ':nome_conta' => $dados['nome_conta'],
                ':descricao' => $dados['descricao'] ?? null,
                ':valor' => $dados['valor'],
                ':data_vencimento' => $dados['data_vencimento'],
                ':modo_pagamento' => $dados['modo_pagamento'],
                ':debito_automatico' => $dados['debito_automatico'] ?? 0,
                ':recorrente' => 0,
                ':dia_vencimento_recorrente' => null,
                ':parcelado' => $dados['parcelado'],
                ':parcela_atual' => $dados['parcela_atual'],
                ':total_parcelas' => $dados['total_parcelas'],
                ':grupo_parcelamento' => $dados['grupo_parcelamento'],
                ':categoria' => $dados['categoria'] ?? null,
                ':observacoes' => $dados['observacoes'] ?? null,
                ':status' => $dados['status'] ?? 'Pendente'
            ]);
            
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            throw new Exception("Erro ao adicionar parcela: " . $e->getMessage());
        }
    }
    
    /**
     * Lista despesas de um grupo de parcelamento
     */
    public function listarParcelamento($grupoParcelamento, $usuarioId = null) {
        $sql = "SELECT * FROM despesas WHERE grupo_parcelamento = :grupo";
        $params = [':grupo' => $grupoParcelamento];
        
        if ($usuarioId) {
            $sql .= " AND usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        
        $sql .= " ORDER BY parcela_atual ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            throw new Exception("Erro ao listar parcelamento: " . $e->getMessage());
        }
    }
    
    /**
     * Deleta todas as parcelas de um grupo
     */
    public function deletarParcelamento($grupoParcelamento, $usuarioId = null) {
        if ($usuarioId) {
            $sql = "DELETE FROM despesas WHERE grupo_parcelamento = :grupo AND usuario_id = :usuario_id";
            $params = [':grupo' => $grupoParcelamento, ':usuario_id' => $usuarioId];
        } else {
            $sql = "DELETE FROM despesas WHERE grupo_parcelamento = :grupo";
            $params = [':grupo' => $grupoParcelamento];
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            throw new Exception("Erro ao deletar parcelamento: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém resumo de um parcelamento
     */
    public function resumoParcelamento($grupoParcelamento, $usuarioId = null) {
        $sql = "SELECT 
                COUNT(*) as total_parcelas,
                COUNT(CASE WHEN status = 'Pago' THEN 1 END) as pagas,
                COUNT(CASE WHEN status = 'Pendente' THEN 1 END) as pendentes,
                COUNT(CASE WHEN status = 'Vencido' THEN 1 END) as vencidas,
                SUM(valor) as valor_total,
                SUM(CASE WHEN status = 'Pago' THEN valor ELSE 0 END) as valor_pago,
                SUM(CASE WHEN status != 'Pago' THEN valor ELSE 0 END) as valor_pendente,
                MIN(data_vencimento) as primeira_parcela,
                MAX(data_vencimento) as ultima_parcela
                FROM despesas 
                WHERE grupo_parcelamento = :grupo";
        
        $params = [':grupo' => $grupoParcelamento];
        
        if ($usuarioId) {
            $sql .= " AND usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch(PDOException $e) {
            throw new Exception("Erro ao obter resumo: " . $e->getMessage());
        }
    }
    
    /**
     * Lista todos os grupos de parcelamento do usuário
     */
    public function listarGruposParcelamento($usuarioId) {
        $sql = "SELECT DISTINCT 
                grupo_parcelamento,
                MAX(nome_conta) as nome_conta,
                MAX(total_parcelas) as total_parcelas,
                MIN(data_vencimento) as data_inicio,
                COUNT(*) as parcelas_criadas
                FROM despesas 
                WHERE parcelado = TRUE 
                AND usuario_id = :usuario_id
                AND grupo_parcelamento IS NOT NULL
                GROUP BY grupo_parcelamento
                ORDER BY data_inicio DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':usuario_id' => $usuarioId]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            throw new Exception("Erro ao listar grupos: " . $e->getMessage());
        }
    }
    
    /**
     * Gera UUID simples para grupo de parcelamento
     */
    private function gerarUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    public function __destruct() {
        $this->db->close();
    }
}
