<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;

class BoletoHSBC implements NossoNumeroInterface
{
    private $codigobanco = "399";

    public function geraNossoNumero($dadosboleto)
    {
        $tipoid = 4;

        //codigocedente deve possuir 7 caracteres
        $codigo_cedente = Tools::formatarNumero($dadosboleto["codigo_cedente"], 7, 0);

        $data_vencimento = $dadosboleto["data_vencimento"]; //deve estar assim: dd/mm/aaaa
        //número do documento (sem dvs): 13 digitos
        $numero_documento = Tools::formatarNumero($dadosboleto["numero_documento"], 13, 0); //id_parcela
        // nosso número (com dvs): 16 digitos
        $documento = $numero_documento . $this->modulo_11_invertido($numero_documento) . $tipoid;

        $vencimento = substr($data_vencimento, 0, 2) . substr($data_vencimento, 3, 2) . substr($data_vencimento, 8, 2);

        $agrupado = $documento + $codigo_cedente + $vencimento;

        $nossonumero = $documento . $this->modulo_11_invertido($agrupado);

        return $nossonumero;
    }

    public function modulo_11_invertido($num)
    { // Calculo de Modulo 11 "Invertido" (com pesos de 9 a 2  e não de 2 a 9)
        $ftini = 2;
        $ftfim = 9;
        $fator = $ftfim;
        $soma = 0;

        for ($i = strlen($num); $i > 0; $i--) {
            $soma += substr($num, $i - 1, 1) * $fator;
            if (--$fator < $ftini) {
                $fator = $ftfim;
            }
        }

        $digito = $soma % 11;
        if ($digito > 9) {
            $digito = 0;
        }
        return $digito;
    }

    public function dvBarra($numero)
    {
        $resto2 = Tools::modulo11($numero, 9, 1);
        if ($resto2 == 0 || $resto2 == 1 || $resto2 == 10) {
            $dv = 1;
        } else {
            $dv = 11 - $resto2;
        }
        return $dv;
    }

    public function getCodigoBanco()
    {
        return $this->codigobanco;
    }

    public function getCodigoBancoComDv()
    {
        $parte1 = $this->codigobanco;
        $parte2 = Tools::modulo11($parte1);
        return $parte1 . "-" . $parte2;
    }
}