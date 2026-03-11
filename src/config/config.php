<?php
/**
 * Configuração do Sistema
 * Carrega variáveis de ambiente e configurações
 */

class Config {
    private static $instance = null;
    private $config = [];
    
    private function __construct() {
        $this->loadEnvironment();
        $this->loadDefaults();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }
    
    /**
     * Carrega variáveis de ambiente do arquivo .env
     */
    private function loadEnvironment() {
        $envFile = __DIR__ . '/../../.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue; // Ignorar comentários
                if (strpos($line, '=') === false) continue; // Ignorar linhas inválidas
                
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover aspas se presentes
                if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                    (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                    $value = substr($value, 1, -1);
                }
                
                $this->config[$key] = $value;
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
    
    /**
     * Carrega configurações padrão
     */
    private function loadDefaults() {
        // Configurações padrão da OpenAI
        if (empty($this->config['OPENAI_API_KEY'])) {
            $this->config['OPENAI_API_KEY'] = '';
        }
        if (empty($this->config['OPENAI_MODEL'])) {
            $this->config['OPENAI_MODEL'] = 'gpt-3.5-turbo';
        }
        
        // Configurações do aplicativo
        if (empty($this->config['APP_NAME'])) {
            $this->config['APP_NAME'] = 'Sistema de Controle de Estoque';
        }
        if (empty($this->config['APP_ENV'])) {
            $this->config['APP_ENV'] = 'development';
        }
        if (empty($this->config['APP_DEBUG'])) {
            $this->config['APP_DEBUG'] = 'true';
        }
        
        // Configurações de IA
        if (empty($this->config['AI_ENABLED'])) {
            $this->config['AI_ENABLED'] = 'true';
        }
        if (empty($this->config['AI_TEMPERATURE'])) {
            $this->config['AI_TEMPERATURE'] = '0.7';
        }
        if (empty($this->config['AI_MAX_TOKENS'])) {
            $this->config['AI_MAX_TOKENS'] = '1000';
        }
    }
    
    /**
     * Obtém uma configuração
     */
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Define uma configuração
     */
    public function set($key, $value) {
        $this->config[$key] = $value;
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
    
    /**
     * Verifica se uma configuração existe
     */
    public function has($key) {
        return isset($this->config[$key]) && !empty($this->config[$key]);
    }
    
    /**
     * Obtém todas as configurações
     */
    public function all() {
        return $this->config;
    }
    
    /**
     * Verifica se o Supabase está configurado
     */
    public function isSupabaseConfigured() {
        return $this->has('SUPABASE_URL') && $this->has('SUPABASE_ANON_KEY');
    }
    
    /**
     * Verifica se a OpenAI está configurada
     */
    public function isOpenAIConfigured() {
        return $this->has('OPENAI_API_KEY');
    }
    
    /**
     * Verifica se a IA está habilitada
     */
    public function isAIEnabled() {
        return $this->get('AI_ENABLED', 'true') === 'true' && $this->isOpenAIConfigured();
    }
}

// Função auxiliar para obter configurações
function config($key, $default = null) {
    return Config::getInstance()->get($key, $default);
}

// Função auxiliar para verificar configurações
function config_has($key) {
    return Config::getInstance()->has($key);
}

// Inicializar configurações ao incluir este arquivo
$config = Config::getInstance();

?>