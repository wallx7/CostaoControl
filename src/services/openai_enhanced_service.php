<?php
/**
 * Serviço OpenAI aprimorado com dados do banco
 * Integra com o banco para fornecer contexto real ao assistente
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/store.php';

class OpenAIServiceEnhanced {
    private $apiKey;
    private $model;
    
    public function __construct() {
        $this->apiKey = config('OPENAI_API_KEY', '');
        $this->model = config('OPENAI_MODEL', 'gpt-3.5-turbo');
    }
    
    /**
     * Verifica se a OpenAI está configurada
     */
    public function isConfigured() {
        return !empty($this->apiKey);
    }
    
    /**
     * Busca contexto do banco de dados para treinar a IA
     */
    private function getDatabaseContext() {
        try {
            $context = [];
            
            // Buscar estatísticas do dashboard via store.php (sessão)
            $stats = getDashboardData();
            if ($stats) {
                $context['dashboard_stats'] = $stats;
            }
            
            // Buscar equipamentos recentes
            $equipamentos = equipamentosList('', '', '', '', '', '');
            if ($equipamentos && count($equipamentos) > 0) {
                // Limitar para não sobrecarregar o contexto
                $context['equipamentos_recentes'] = array_slice($equipamentos, 0, 20);
            }
            
            $context['categorias'] = categoriasList();
            
            return $context;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar contexto do banco: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Constrói prompt do sistema com contexto do banco
     */
    private function buildSystemPrompt($userContext = []) {
        $dbContext = $this->getDatabaseContext();
        
        $prompt = "Você é um assistente especializado em gestão de inventário e controle de estoque.\n\n";
        
        // Adicionar contexto do banco de dados
        if (!empty($dbContext)) {
            $prompt .= "### CONTEXTO ATUAL DO SISTEMA:\n\n";
            
            if (isset($dbContext['dashboard_stats'])) {
                $stats = $dbContext['dashboard_stats'];
                $prompt .= "📊 **Estatísticas Gerais:**\n";
                $prompt .= "- Total de equipamentos: " . ($stats['total_equipamentos'] ?? 0) . "\n";
                $prompt .= "- Valor total do estoque: R$ " . number_format($stats['valor_total_estoque'] ?? 0, 2, ',', '.') . "\n";
                $prompt .= "- Equipamentos em manutenção: " . ($stats['manutencoes_pendentes'] ?? 0) . "\n";
            }
            
            if (isset($dbContext['equipamentos_recentes']) && !empty($dbContext['equipamentos_recentes'])) {
                $prompt .= "\n🖥️ **Equipamentos Recentes:**\n";
                foreach ($dbContext['equipamentos_recentes'] as $eq) {
                    $prompt .= "- " . ($eq['nome'] ?? 'N/A') . " (" . ($eq['status'] ?? 'N/A') . ")\n";
                }
            }
            
            if (isset($dbContext['categorias']) && !empty($dbContext['categorias'])) {
                $prompt .= "\n📁 **Categorias Disponíveis:**\n";
                $cats = [];
                foreach ($dbContext['categorias'] as $c) {
                    $cats[] = $c['nome'];
                }
                $prompt .= implode(', ', $cats) . "\n";
            }
        }
        
        $prompt .= "\n### INSTRUÇÕES:\n";
        $prompt .= "1. Responda com base no contexto fornecido acima.\n";
        $prompt .= "2. Se a pergunta for sobre criar, editar ou excluir itens, explique como fazer isso na interface.\n";
        $prompt .= "3. Seja conciso e profissional.\n";
        $prompt .= "4. Se não souber a resposta com base no contexto, diga que não encontrou a informação.\n";
        
        return $prompt;
    }

    /**
     * Envia mensagem para o chat
     */
    public function chat($message, $history = []) {
        if (!$this->isConfigured()) {
            return [
                'error' => 'OpenAI não configurada. Verifique sua API Key no arquivo .env'
            ];
        }
        
        $systemPrompt = $this->buildSystemPrompt();
        
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];
        
        // Adicionar histórico
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['content']
            ];
        }
        
        // Adicionar mensagem atual
        $messages[] = ['role' => 'user', 'content' => $message];
        
        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 500
        ];
        
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['error' => 'Erro na API OpenAI: ' . $httpCode];
        }
        
        $response = json_decode($result, true);
        
        if (isset($response['choices'][0]['message']['content'])) {
            return ['content' => $response['choices'][0]['message']['content']];
        }
        
        return ['error' => 'Resposta inválida da OpenAI'];
    }
}
