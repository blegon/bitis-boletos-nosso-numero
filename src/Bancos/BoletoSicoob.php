<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;

class BoletoSicoob implements NossoNumeroInterface
{
    private $codigobanco = "756";

    public function geraNossoNumero($dadosboleto)
    {
        $NossoNumero = Tools::formatarNumero($dadosboleto["nosso_numero"], 7, "0");

        //agencia é 4 digitos
        $agencia = Tools::formatarNumero($dadosboleto["agencia"], 4, "0");
        $dadosboleto["convenio"] = $dadosboleto["convenio"] . $dadosboleto["convenio_dv"];


        $convenio = Tools::formatarNumero($dadosboleto["convenio"], 10, "0");

        $sequencia = $agencia . $convenio . $NossoNumero;

        $dv_nosso_numero = $this->dvNossoNumero($sequencia);

        $nosso_numero = $NossoNumero . $dv_nosso_numero;

        $nosso_numero = Tools::formatarNumero($nosso_numero, 8, "0");

        return $nosso_numero;
    }

    public function dvNossoNumero($sequencia)
    {
        $cont=0;
        $calculoDv=0;
        for ($num=0;$num<=strlen($sequencia);$num++) {
            $cont++;
            if ($cont == 1) {
                $constante = 3;
            }
            if ($cont == 2) {
                $constante = 1;
            }
            if ($cont == 3) {
                $constante = 9;
            }
            if ($cont == 4) {
                $constante = 7;
                $cont = 0;
            }
            $calculoDv = $calculoDv + ((int) substr($sequencia, $num, 1) * $constante);
        }

        $Resto = $calculoDv % 11;
        if ($Resto == 0 || $Resto == 1) {
            $Dv = 0;
        } else {
            $Dv = 11 - $Resto;
        }

        return $Dv;
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