# Data Migration
Esta API simplifica o processo de migração de dados de uma tabela para outra usando arquivos CSV. Siga as instruções abaixo para realizar uma migração bem-sucedida.

## Pré-requisitos
* PHP instalado na sua máquina
* Arquivo CSV com os dados a serem migrados

## Configuração
1. Faça o download do projeto.
2. Crie a tabela conforme query localizada em `src/Database/Query/create-table-dados_catraca.sql`.
3. Execute o `composer update`.
4. No arquivo `.env` localizado na raiz do projeto configure as seguintes variáveis:
* DB_HOST=ip_host
* DB_NAME=db_name
* DB_USER=db_username
* DB_PASS=db_password
* DB_PORT=db_port
* DB_TABLE=table_name
* FILE_INPUT=nome-do-arquivo.csv
* FILE_OUTPUT=nome-do-arquivo-de-saida.csv
* DATA_INICIO=yyyy-mm-dd
* DATA_FIM=yyyy-mm-dd
  Certifique-se de substituir pelos valores específicos do seu caso.
## Uso
1. Coloque o arquivo CSV a ser migrado na pasta input.
2. Abra o terminal na pasta do projeto.
3. Execute o seguinte comando para iniciar a migração:
   `php index.php`
* A migração será processada e o arquivo de saída será gerado na pasta output.

## Notas
1. Certifique-se de que o arquivo CSV de entrada esteja na pasta input antes de executar o script.
2. Verifique se as datas de início e fim no arquivo `.env` estão no formato correto (yyyy-mm-dd).
3. O arquivo de saída será gerado na pasta output com o nome especificado em FILE_OUTPUT no arquivo `.env`.