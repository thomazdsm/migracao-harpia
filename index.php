<?php

require __DIR__ . '/vendor/autoload.php';

use Migracao\Database\Database;
use Migracao\App\ScriptMigracao;
use WilliamCosta\DotEnv\Environment;

// Carregar o .env
Environment::load(__DIR__ . '/');

// Pedir confirmação antes de prosseguir
echo "\nVerifique as informações antes de prosseguir:\n";
$dados = [
    'Input File' => getenv('FILE_INPUT'),
    'Ouput File' => getenv('FILE_OUTPUT'),
    'Período' => 'De '. getenv('DATA_INICIO') . ' Até ' . getenv('DATA_FIM')
];

print_r($dados);

echo "Tem certeza de que deseja executar o script de migração? (y/n): ";
$confirmacao = trim(fgets(STDIN));

if (strtolower($confirmacao) !== 'y') {
    echo "Script de migração cancelado.\n";
    exit;
}

try {
    // Executar o script de migração
    new ScriptMigracao((new Database())->getConnection());

    echo "> Migração concluída com sucesso!\n";
    echo "> Localização do Arquivo: src/App/output/" . str_replace("'", "", getenv('FILE_OUTPUT'));
} catch (Exception $e) {
    die('Erro durante a execução do script: ' . $e->getMessage());
}
