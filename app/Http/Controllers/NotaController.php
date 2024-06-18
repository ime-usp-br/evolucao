<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SplFileInfo;
use Uspdev\Replicado\Graduacao;
use Uspdev\Replicado\DB;

class NotaController extends Controller
{
    const CURSOS_IME = array(
        'BCC' => [45051, 45052],
        'LM'  => [45024],
        'BM'  => [45031],
        'BE'  => [45061],
        'BMA' => [45042],
        'BMAC'=> [45070],
    );

    /**
     * Listagem das opcoes disponiveis:
     * - Pesquisa por nota individual;
     * - Listagem geral.
     */
    public function index () {
        return view('notas.index');
    }

    private function _obterMediaPonderada(int $codpes, int $codpgm = null, array $rstfim = ['A', 'RN', 'RA', 'RF'])
    {
        $result = Graduacao::listarDisciplinasAluno($codpes, $codpgm, $rstfim);

        $creditos = 0;
        $soma = 0;
        foreach ($result as $row) {
            $creditos += $row['creaul'] + $row['cretrb'];
            $nota = $row['notfim2'] ?: $row['notfim'];
            $soma += $nota * ($row['creaul'] + $row['cretrb']);
        }
        return empty($soma) ? 0 : round($soma / $creditos, 2);
    }

    /**
     * A partir do codpes de um aluno e um curso, calcula suas medias limpas e sujas.
     * 
     * @param int $codpes Codigo de pessoa (NUSP) do aluno.
     * @param int $codcur Codigo do curso do aluno.
     * @return array List de medias
     */
    private function _aluno_medias (int $codpes, int $codpgm) {
        // Captura o historico
        $historico = Graduacao::listarDisciplinasAluno($codpes, $codpgm);

        // Se estiver vazio, retorna nulo
        if (empty($historico)) return array('limpa'=>0,'suja'=>0);

        // Calculo das medias
        $medias = array(
            'limpa' => $this->_obterMediaPonderada($codpes, $codpgm, $rstfim = ['A']),
            'suja'  => $this->_obterMediaPonderada($codpes, $codpgm)
        );

        return $medias;
    }

    /**
     * Pagina de importacao
     */
    public function importar () {
        return view('notas.importar.index');
    }

    /**
     * Importacao de CSV
     */
    public function importar_csv (Request $request) {
        // Valida o arquivo enviado. Rejeita qualquer maior que 2048 kilobyes (=2MB)
        // $request->validate([
        //     // 'arquivo' => 'required|mimes:csv|max:2048',
        //     'arquivo' => 'required|max:2048',
        // ]);

        // Salva o arquivo na pasta \app\public
        $arquivo = $request->file('arquivo');
        $arquivoNome = $arquivo->getClientOriginalName();
        $arquivoCaminho = $arquivo->store('uploads');

        // Transforma em um array
        $array_csv = $this->_csv_para_array($arquivoCaminho);

        // Captura as informacoes de curso, media e ano de conclusao
        $res['alunos'] = $this->_infos_alunos($array_csv);

        return view('notas.importar.visualizacao', $res);
    }

    /**
     * Le um arquivo CSV a partir do $csvPath e retorna um array
     * com as informacoes
     * 
     * @param string $csvPath Caminho do arquivo csv
     * @param bool   $temCabecalho Se o arquivo tem um cabecalho
     * @return array Array (indexado ou nao) com as informacoes do arquivo csv
     */
    private function _csv_para_array (string $csvPath, bool $temCabecalho=True) {
        // Abre um arquivo e transforma em array
        $csv = array_map('str_getcsv', file(Storage::path($csvPath)));
        // Se tiver cabecalho, precisa utilizar indices
        if ($temCabecalho) {
            $cabecalho = $csv[0];
            array_shift($csv); // Remove o cabecalho do array
            $dados = array();
            foreach ($csv as $linha) {
                $dado = array();
                foreach ($cabecalho as $i=>$indice)
                    $dado[$indice] = $linha[$i];
                $dados[] = $dado;
            }
            return $dados;
        } 
        else
            return $csv;
    }

    /**
     * A partir de um array de alunos com nusp, ano de ingresso e
     * sigla do curso, captura o codigo do programa e as medias 
     * ponderadas suja e limpa do aluno.
     * 
     * @param array $alunos Array com alunos. Precisa ter chaves 'nusp',
     *                      'ingresso' e 'curso'.
     * @return array Array com as informacoes desejadas.
     */
    private function _infos_alunos (array $alunos_codpes) {
        $alunos = array();
        foreach ($alunos_codpes as $aluno) {
            // Captura o programa cursado pelo aluno
            $codpgm = $this->_programa_aluno($aluno)['codpgm'];

            // Medias ponderadas
            $aluno['medias'] = $this->_aluno_medias($aluno['nusp'], $codpgm);

            $alunos[] = $aluno;
        }
        return $alunos;
    }

    /**
     * A partir de um array com o nusp, o ano de ingresso e a sigla do curso,
     * captura o codigo do programa do aluno (i.e. a matricula de quando fez o curso).
     * 
     * @param array $aluno Array com chaves `nusp`, `ingresso` e `curso`
     * @return array Programa ou vazio.
     */
    private function _programa_aluno (array $aluno) {
        $codpes = $aluno['nusp'];
        $ingresso = $aluno['ingresso'];
        $query = "SELECT
                    H.codpgm
                FROM
                    HABILPROGGR H
                WHERE
                    H.codpes = convert(int,$codpes)
                    AND YEAR(H.dtaini) = convert(int,$ingresso)";
        
        $codcurs = self::CURSOS_IME[strtoupper($aluno['curso'])];
        $str_codcurs = implode(',', array_map('intval', $codcurs));
        $query .= " AND H.codcur IN ($str_codcurs)";
        return DB::fetch($query) ?: [];
    }

    /**
     * A partir do codpes (nusp) de um aluno, verifica se ele tem historico
     * registrado no sistema (i.e. se fez alguma disciplina)
     * 
     * @param int $codpes Codigo de pessoa (NUSP) do aluno.
     * @return bool Se o aluno tem ou nao historico.
     */
    private function _aluno_tem_historico (int $codpes) {
        $historico = Graduacao::listarDisciplinasAluno($codpes);
        return !empty($historico);
    }
}
