<?php

namespace Bitis\Boletos\NossoNumero\Tools;

use DateTime;

class Tools
{

    /**
     * @param $numero
     * @param $loop
     * @param $insert
     * @param string $tipo
     * @return string
     */
    public static function formatarNumero($numero, $loop, $insert, $tipo = "geral")
    {
        if ($tipo == "convenio") {
            return str_pad($numero, $loop, $insert, STR_PAD_RIGHT);
        }
        $numero = str_replace(",", "", $numero);
        $numero = str_pad($numero, $loop, $insert, STR_PAD_LEFT);

        return $numero;
    }

    /**
     * @param $num
     * @return int
     */
    public static function modulo10($num)
    {
        $numtotal10 = 0;
        $fator = 2;

        // Separacao dos numeros
        for ($i = strlen($num); $i > 0; $i--) {
            // pega cada numero isoladamente
            $numeros[$i] = substr($num, $i - 1, 1);
            // Efetua multiplicacao do numero pelo (falor 10)
            // 2002-07-07 01:33:34 Macete para adequar ao Mod10 do Ita�
            $temp = $numeros[$i] * $fator;
            $temp0 = 0;
            foreach (preg_split('//', $temp, -1, PREG_SPLIT_NO_EMPTY) as $k => $v) {
                $temp0 += $v;
            }
            $parcial10[$i] = $temp0; //$numeros[$i] * $fator;
            // monta sequencia para soma dos digitos no (modulo 10)
            $numtotal10 += $parcial10[$i];
            if ($fator == 2) {
                $fator = 1;
            } else {
                $fator = 2; // intercala fator de multiplicacao (modulo 10)
            }
        }

        // v�rias linhas removidas, vide fun��o original
        // Calculo do modulo 10
        $resto = $numtotal10 % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }

        return $digito;
    }

    /**
     * @param $num
     * @param int $base
     * @param int $r
     * @param int $resto10
     * @return int
     */
    public static function modulo11($num, $base = 9, $r = 0, $resto10 = 0)
    {
        /**
         *   Autor:
         *           Pablo Costa <pablo@users.sourceforge.net>
         *
         *   Função:
         *    Calculo do Modulo 11 para geracao do digito verificador
         *    de boletos bancarios conforme documentos obtidos
         *    da Febraban - www.febraban.org.br
         *
         *   Entrada:
         *     $num: string numérica para a qual se deseja calcularo digito verificador;
         *     $base: valor maximo de multiplicacao [2-$base]
         *     $r: quando especificado um devolve somente o resto
         *
         *   Sa�da:
         *     Retorna o Digito verificador.
         *
         *   Observações:
         *     - Script desenvolvido sem nenhum reaproveitamento de código pré existente.
         *     - Assume-se que a verificação do formato das variáveis de entrada é feita antes da execução deste script.
         */
        $soma = 0;
        $fator = 2;
        /* Separacao dos numeros */
        for ($i = strlen($num); $i > 0; $i--) {
            // pega cada numero isoladamente
            $numeros[$i] = substr($num, $i - 1, 1);
            // Efetua multiplicacao do numero pelo falor
            $parcial[$i] = $numeros[$i] * $fator;
            // Soma dos digitos
            $soma += $parcial[$i];
            if ($fator == $base) {
                // restaura fator de multiplicacao para 2
                $fator = 1;
            }
            $fator++;
        }

        /* Calculo do modulo 11 */
        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;
            if ($digito == 10) {
                $digito = $resto10;
            }
            return $digito;
        }
        return $soma % 11;
    }

    /**
     * @param $codigo
     * @return string
     */
    public static function getLinhaDigitavel($codigo)
    {

        // COMPOSICAO DO CODIGO
        // Posição | Larg | Conte�do
        // --------+------+---------------
        // 1 a 3   |  03  | Identcação do banco
        // 4       |  01  | Código da Moeda - 9 para R$
        // 5       |  01  | Digito verificador geral do Código de Barras
        // 6 a 9   |  04  | Fator de Vencimento
        // 10 a 19 |  10  | Valor (8 inteiros e 2 decimais)
        // 20 a 44 |  25  | Campo Livre definido por cada banco (25 caracteres)

        //COMPOSICAO DA LINHA DIGITAVEL

        // 1. Campo - composto pelo código do banco, código da mo�da, as cinco primeiras posições
        // do campo livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 0, 4);
        $p2 = substr($codigo, 19, 5);
        $p3 = self::modulo10("$p1$p2");
        $p4 = "$p1$p2$p3";
        $p5 = substr($p4, 0, 5);
        $p6 = substr($p4, 5);
        $campo1 = "$p5.$p6";

        // 2. Campo - composto pelas posi�oes 6 a 15 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 24, 10);
        $p2 = self::modulo10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo2 = "$p4.$p5";

        // 3. Campo composto pelas posicoes 16 a 25 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 34, 10);
        $p2 = self::modulo10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo3 = "$p4.$p5";

        // 4. Campo - digito verificador do codigo de barras
        $campo4 = substr($codigo, 4, 1);

        // 5. Campo composto pelo fator vencimento e valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
        $p1 = substr($codigo, 5, 4);
        $p2 = substr($codigo, 9, 10);
        $campo5 = "$p1$p2";

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }

    /**
     * @param $data
     * @return string
     */
    public static function dataJuliano($data)
    {
        $dia = (int)substr($data, 0, 2);
        $mes = (int)substr($data, 3, 2);
        $ano = (int)substr($data, 6, 4);
        $dataf = strtotime("$ano/$mes/$dia");
        $datai = strtotime(($ano - 1) . '/12/31');
        $dias = (int)(($dataf - $datai) / (60 * 60 * 24));
        return str_pad($dias, 3, '0', STR_PAD_LEFT) . substr($data, 9, 4);
    }

    /**
     * @param $entra
     * @param $comp
     * @return bool|string
     */
    private static function esquerda($entra, $comp)
    {
        return substr($entra, 0, $comp);
    }

    /**
     * @param $entra
     * @param $comp
     * @return bool|string
     */
    private static function direita($entra, $comp)
    {
        return substr($entra, strlen($entra) - $comp, $comp);
    }

    /**
     * @param $valor
     * @return string
     */
    public static function fbarcode($valor)
    {
        $fino = 1;
        $largo = 3;
        $altura = 50;

        $barcodes[0] = "00110";
        $barcodes[1] = "10001";
        $barcodes[2] = "01001";
        $barcodes[3] = "11000";
        $barcodes[4] = "00101";
        $barcodes[5] = "10100";
        $barcodes[6] = "01100";
        $barcodes[7] = "00011";
        $barcodes[8] = "10010";
        $barcodes[9] = "01010";

        for ($f1 = 9; $f1 >= 0; $f1--) {

            for ($f2 = 9; $f2 >= 0; $f2--) {
                $f = ($f1 * 10) + $f2;
                $texto = "";

                for ($i = 1; $i < 6; $i++) {
                    $texto .= substr($barcodes[$f1], ($i - 1), 1) . substr($barcodes[$f2], ($i - 1), 1);
                }

                $barcodes[$f] = $texto;
            }

        }


        //Desenho da barra

        //Guarda inicial
        $barcode = "<div style='width:${fino}px;height:${altura}px; background:black;display: block; float:left; border:none;' ></div>";
        $barcode .= "<div style='width:${fino}px;height:${altura}px; background:white;display: block; float:left; border:none;' ></div>";
        $barcode .= "<div style='width:${fino}px;height:${altura}px; background:black;display: block; float:left; border:none;' ></div>";
        $barcode .= "<div style='width:${fino}px;height:${altura}px; background:white;display: block; float:left; border:none;' ></div>";

        $texto = $valor;
        if ((strlen($texto) % 2) !== 0) {
            $texto = "0" . $texto;
        }

        // Draw dos dados
        while (strlen($texto) > 0) {
            $i = round(self::esquerda($texto, 2));
            $texto = self::direita($texto, strlen($texto) - 2);
            $f = $barcodes[$i];
            for ($i = 1; $i < 11; $i += 2) {
                if (substr($f, ($i - 1), 1) == "0") {
                    $f1 = $fino;
                } else {
                    $f1 = $largo;
                }

                $barcode .= "<div style='width:${f1}px;height:${altura}px; background:black;display: block; float:left; border:none;' ></div>";

                if (substr($f, $i, 1) == "0") {
                    $f2 = $fino;
                } else {
                    $f2 = $largo;
                }

                $barcode .= "<div style='width:${f2}px;height:${altura}px; background:white;display: block; float:left; border:none;' ></div>";
            }
        }

        // Draw guarda final
        $barcode .= "<div style='width:${largo}px;height:${altura}px; background:black;display: block; float:left; border:none;' ></div>";
        $barcode .= "<div style='width:${fino}px;height:${altura}px; background:white;display: block; float:left; border:none;' ></div>";
        $barcode .= "<div style='width:${fino}px;height:${altura}px; background:black;display: block; float:left; border:none;' ></div>";

        return $barcode;
    }

    /**
     * Atualizado em 2025 para atender a nova data de referência
     * 
     * @param $data
     * @return float|int
     */
    public static function fatorVencimento($data)
    {
        $dataBase = new DateTime("2025-02-22"); // Data fixa
    
        // Possíveis formatos aceitos
        $formatosAceitos = [
            "d/m/Y", "Y-m-d", "d-m-Y", "m/d/Y"
        ];
    
        $dataVenc = null;
    
        // Tenta criar um objeto DateTime para cada formato
        foreach ($formatosAceitos as $formato) {
            $dataVenc = DateTime::createFromFormat($formato, $data);
            if ($dataVenc !== false) {
                break;
            }
        }
    
        $dataBase = new DateTime("2025-02-22");
    
        // Calcula a diferença de dias
        $diferenca = $dataBase->diff($dataVenc)->days;
    
        // Ajusta o fato
        return $diferenca + 1000;
    }

    private static function dateToDays($year, $month, $day)
    {
        $century = substr($year, 0, 2);
        $year = substr($year, 2, 2);
        if ($month > 2) {
            $month -= 3;
        } else {
            $month += 9;
            if ($year) {
                $year--;
            } else {
                $year = 99;
                $century--;
            }
        }
        return (
            floor((146097 * $century) / 4) +
            floor((1461 * $year) / 4) +
            floor((153 * $month + 2) / 5) +
            $day + 1721119
        );
    }
}