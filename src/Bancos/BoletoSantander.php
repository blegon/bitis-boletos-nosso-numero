<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;

class BoletoSantander implements NossoNumeroInterface
{
    private $codigobanco = "033"; //Antigamente era 353

    public function geraNossoNumero($dadosboleto)
    {
        //nosso n�mero (sem dv) � 11 digitos
        $nnum = Tools::formatarNumero($dadosboleto["nosso_numero"], 12, 0);

        //dv do nosso n�mero
        $dv_nosso_numero = Tools::modulo11($nnum);

        // nosso n�mero (com dvs) s�o 13 digitos
        return $nnum . $dv_nosso_numero;
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