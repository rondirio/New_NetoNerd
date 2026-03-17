<?php
/**
 * StyleManager API - Horários Disponíveis Endpoint
 *
 * GET /api/v1/stylemanager/agendamentos/horarios
 *
 * Retorna horários disponíveis para agendamento.
 *
 * Query params:
 *   - data: Data desejada (Y-m-d)
 *   - servico_id: ID do serviço
 *   - profissional_id: ID do profissional (opcional)
 *
 * Response:
 * {
 *   "success": true,
 *   "data": [
 *     { "horario": "09:00", "disponivel": true, "profissional_id": 1, "profissional_nome": "João" },
 *     { "horario": "09:30", "disponivel": false, "profissional_id": 1, "profissional_nome": "João" }
 *   ]
 * }
 */

require_once __DIR__ . '/../config/api_helper.php';

// Apenas GET
requireMethod('GET');

// Requer autenticação
$auth = requireAuth();
$conn = $auth['db']->getConnection();

// Parâmetros obrigatórios
$data = getQueryParam('data');
$servicoId = getQueryParam('servico_id');
$profissionalId = getQueryParam('profissional_id');

if (!$data || !$servicoId) {
    errorResponse('Parâmetros data e servico_id são obrigatórios', 400);
}

if (!validateDate($data)) {
    errorResponse('Data inválida', 400);
}

// Não permite datas passadas
if ($data < date('Y-m-d')) {
    errorResponse('Não é possível agendar em datas passadas', 400);
}

// Busca informações do serviço
$stmt = $conn->prepare("SELECT id, nome, duracao FROM servicos WHERE id = ? AND ativo = 1");
$stmt->bind_param('i', $servicoId);
$stmt->execute();
$servico = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$servico) {
    errorResponse('Serviço não encontrado', 404);
}

$duracao = (int)$servico['duracao'];

// Busca configurações do estabelecimento (horário de funcionamento)
$horaAbertura = '08:00';
$horaFechamento = '20:00';
$intervaloMinutos = 30; // Intervalo entre horários

// Tenta buscar configurações do banco
$stmt = $conn->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ('hora_abertura', 'hora_fechamento', 'intervalo_agendamento')");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        switch ($row['chave']) {
            case 'hora_abertura':
                $horaAbertura = $row['valor'];
                break;
            case 'hora_fechamento':
                $horaFechamento = $row['valor'];
                break;
            case 'intervalo_agendamento':
                $intervaloMinutos = (int)$row['valor'];
                break;
        }
    }
    $stmt->close();
}

// Busca profissionais que fazem este serviço
$profissionais = [];

if ($profissionalId) {
    // Profissional específico
    $stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE id = ? AND tipo = 'profissional' AND ativo = 1");
    $stmt->bind_param('i', $profissionalId);
    $stmt->execute();
    $prof = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($prof) {
        $profissionais[] = $prof;
    }
} else {
    // Todos os profissionais ativos
    $stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE tipo = 'profissional' AND ativo = 1 ORDER BY nome");
    $stmt->execute();
    $result = $stmt->get_result();
    $profissionais = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if (empty($profissionais)) {
    successResponse([]);
}

// Busca agendamentos do dia para os profissionais
$profIds = array_column($profissionais, 'id');
$placeholders = implode(',', array_fill(0, count($profIds), '?'));
$types = str_repeat('i', count($profIds));

$sql = "SELECT profissional_id, hora_inicio, hora_fim
        FROM agendamentos
        WHERE data = ? AND profissional_id IN ($placeholders)
        AND status NOT IN ('cancelado', 'nao_compareceu')
        ORDER BY hora_inicio";

$stmt = $conn->prepare($sql);
$params = array_merge([$data], $profIds);
$types = 's' . $types;
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Organiza agendamentos por profissional
$agendamentosPorProf = [];
while ($row = $result->fetch_assoc()) {
    $profId = $row['profissional_id'];
    if (!isset($agendamentosPorProf[$profId])) {
        $agendamentosPorProf[$profId] = [];
    }
    $agendamentosPorProf[$profId][] = [
        'inicio' => $row['hora_inicio'],
        'fim' => $row['hora_fim']
    ];
}
$stmt->close();

// Gera lista de horários
$horarios = [];
$inicio = new DateTime($data . ' ' . $horaAbertura);
$fim = new DateTime($data . ' ' . $horaFechamento);

// Se for hoje, começa do próximo horário disponível
if ($data === date('Y-m-d')) {
    $agora = new DateTime();
    $agora->add(new DateInterval('PT30M')); // 30 minutos de antecedência mínima

    if ($agora > $inicio) {
        // Arredonda para próximo intervalo
        $minutos = (int)$agora->format('i');
        $arredondar = $intervaloMinutos - ($minutos % $intervaloMinutos);
        if ($arredondar < $intervaloMinutos) {
            $agora->add(new DateInterval('PT' . $arredondar . 'M'));
        }
        $agora->setTime((int)$agora->format('H'), (int)$agora->format('i'), 0);
        $inicio = $agora;
    }
}

// Gera horários
while ($inicio < $fim) {
    $horaInicio = clone $inicio;
    $horaFimServico = clone $inicio;
    $horaFimServico->add(new DateInterval('PT' . $duracao . 'M'));

    // Verifica se o serviço cabe no horário de funcionamento
    if ($horaFimServico > $fim) {
        break;
    }

    $horarioStr = $horaInicio->format('H:i');
    $horaInicioStr = $horaInicio->format('H:i:s');
    $horaFimStr = $horaFimServico->format('H:i:s');

    // Verifica disponibilidade para cada profissional
    foreach ($profissionais as $prof) {
        $disponivel = true;

        if (isset($agendamentosPorProf[$prof['id']])) {
            foreach ($agendamentosPorProf[$prof['id']] as $ag) {
                // Verifica sobreposição
                if (
                    ($horaInicioStr < $ag['fim'] && $horaFimStr > $ag['inicio'])
                ) {
                    $disponivel = false;
                    break;
                }
            }
        }

        $horarios[] = [
            'horario' => $horarioStr,
            'disponivel' => $disponivel,
            'profissional_id' => (int)$prof['id'],
            'profissional_nome' => $prof['nome']
        ];
    }

    $inicio->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
}

successResponse($horarios);
