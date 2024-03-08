<?php

namespace Migracao\App;

use DateTime;
use PDO;
use PDOException;

class ScriptMigracao
{
    /**
     * Instancia de conexão com o banco de dados
     * @var PDO
     */
    private $connection;

    /**
     * Nome da tabela a ser manipulada
     * @var string
     */
    private $table;

    /**
     * Nome do arquivo de input
     * @var string
     */
    private $file_input;

    /**
     * Nome do arquivo de output
     * @var string
     */
    private $file_output;

    /**
     * Nome do arquivo de output
     * @var string
     */
    private $data_inicio;

    /**
     * Nome do arquivo de output
     * @var string
     */
    private $data_fim;

    /**
     * Método responsável por instanciar a classe
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->table = getenv('DB_TABLE');
        $this->file_input = 'src/App/input/'.str_replace("'", "", getenv('FILE_INPUT'));
        $this->file_output = 'src/App/output/'.str_replace("'", "", getenv('FILE_OUTPUT'));
        $this->data_inicio = str_replace("'", "", getenv('DATA_INICIO'));
        $this->data_fim = str_replace("'", "", getenv('DATA_FIM'));

        $this->verificarTabela();
        $this->scriptHorasTrabalhadas();
        $this->scriptHorasTrabalhadasGerarCsv();
    }

    /**
     * @return void
     */
    private function scriptHorasTrabalhadas()
    {
        //COD_PESSOA,COD_LOCAL,Tipo,NumInner,DataHora,Ordem,Exportado,NUM_CARTAO
        $fp = fopen($this->file_input, 'r');
        while (($dado = fgetcsv($fp, 18000, ",")) !== FALSE)
        {
            $sql = "INSERT INTO ". $this->table ."(tipo, dataHora, codCatraca)
                    VALUES (:tipo, :dataHora, :codCatraca)";
            $stmt = $this->connection->prepare($sql);
            // Binds
            $stmt->bindValue(':tipo', $dado[2]);
            $stmt->bindValue(':dataHora', $dado[4]);
            $stmt->bindValue(':codCatraca', $dado[7]);
            $stmt->execute();
            var_dump($dado);
        }
        fclose($fp);
    }

    /**
     * @return void
     */
    private function scriptHorasTrabalhadasGerarCsv()
    {
        $arrayFinal = [];

        $dias = $this->gerarArrayDeDatas();

        $sql = "SELECT codCatraca
                FROM ". $this->table ."
                GROUP BY codCatraca
                ORDER BY codCatraca";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $codigos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($codigos as $codigo){


            foreach ($dias as $dia) {
                $arrayFinal[$codigo['codCatraca']][$dia] = 0;

                $sql = "SELECT *
                FROM ". $this->table ."
                WHERE codCatraca = :codCatraca
                AND tipo in (10, 110)
                AND dataHora > :dataInicio
                AND dataHora < :dataFim
                ORDER BY dataHora asc";
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue(':codCatraca', (int)$codigo['codCatraca']);
                $stmt->bindValue(':dataInicio', $dia.' 00:00:00');
                $stmt->bindValue(':dataFim', $dia.' 23:59:59');
                $stmt->execute();
                $entradas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if(sizeof($entradas) === 0){
                    continue;
                }
//                var_dump($entradas, $codigo);
//                exit();
                $saidasUsadas = [100000000];

                foreach ($entradas as $entrada) {
                    $imploded_string = implode(",", $saidasUsadas);
//                    var_dump($imploded_string);

                    //verifica se o próximo registro é um registro de saída---------------------------------------------
                    $sql = "SELECT *
                        FROM ". $this->table ."
                        WHERE codCatraca = :codCatraca
                        AND dataHora > :data
                        ORDER BY dataHora asc
                        limit 1";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bindValue(':codCatraca', (int)$codigo['codCatraca']);
                    $stmt->bindValue(':data', $entrada['dataHora']);
                    $stmt->execute();
                    $proximaEhSaida = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if(sizeof($proximaEhSaida) === 0){
                        continue;
                    }
                    if((int)$proximaEhSaida[0]['tipo'] === 110 or (int)$proximaEhSaida[0]['tipo'] === 10 ){
                        continue;
                    }
                    //--------------------------------------------------------------------------------------------------

                    $sql = "SELECT *
                        FROM ". $this->table ."
                        WHERE codCatraca = :codCatraca
                        AND tipo in (11, 111)
                        AND dataHora > :dataInicio
                        AND dataHora < :dataFim
                        AND id NOT IN (".$imploded_string.")
                        ORDER BY dataHora asc
                        limit 1";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bindValue(':codCatraca', (int)$codigo['codCatraca']);
                    $stmt->bindValue(':dataInicio', $entrada['dataHora']);
                    $stmt->bindValue(':dataFim', $dia.' 23:59:59');
                    $stmt->execute();
                    $saida = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                    if(sizeof($saida) > 0){
                        $saidasUsadas[] = $saida[0]['id'];
                        echo 'codigo: '. (int)$codigo['codCatraca'] .' entrada: '.$entrada['dataHora'].' saída: '.$saida[0]['dataHora'].PHP_EOL;
                        $soma = strtotime($saida[0]['dataHora'])  - strtotime($entrada['dataHora']);
                        $arrayFinal[$codigo['codCatraca']][$dia] += $soma;
                    }


                }
            }
        }

        var_dump($arrayFinal);

        $fwrite = fopen($this->file_output, 'w');
        foreach ($arrayFinal as $key => $item){
            foreach ($item as $itemDia => $anotherItem ){
                if($anotherItem > 0){

                    $hours = floor($anotherItem/3600);
                    $minutes = floor(($anotherItem % 3600)/60);
                    $seconds = (($anotherItem%3600)%60);
                    $time =
                        str_pad($hours, 2, '0', STR_PAD_LEFT).':' .
                        str_pad(abs($minutes), 2, '0', STR_PAD_LEFT).':'.
                        str_pad(abs($seconds), 2, '0', STR_PAD_LEFT);

                    fputcsv($fwrite, [ $key,  $time, $itemDia]);
                }
            }
        }

        fclose($fwrite);
    }

    /**
     * Gerar a array de dias
     * @return array
     * @throws \Exception
     */
    private function gerarArrayDeDatas()
    {
        $datas = [];
        $dataAtual = new DateTime($this->data_inicio);

        while ($dataAtual <= new DateTime($this->data_fim)) {
            $datas[] = $dataAtual->format('Y-m-d');
            $dataAtual->modify('+1 day');
        }

        return $datas;
    }

    /**
     * Verifica se a tabela 'dados_catraca' está vazia
     * @return void
     */
    private function verificarTabela()
    {
        try {
            $stmt = $this->connection->query("SELECT COUNT(*) FROM dados_catraca");
            $rowCount = $stmt->fetchColumn();

            if ($rowCount > 0) {
                // A tabela não está vazia, então vamos limpá-la
                $this->connection->exec("TRUNCATE TABLE dados_catraca");
            }
        } catch (PDOException $e) {
            die('ERROR: ' . $e->getMessage());
        }
    }
}
