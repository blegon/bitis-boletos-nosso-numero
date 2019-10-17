<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;

class BoletoBradesco implements NossoNumeroInterface
{
    private $codigobanco = "237";

    public function geraNossoNumero($dadosboleto)
    {

        //carteira: 2 caracteres
        $carteira = Tools::formatarNumero($dadosboleto["carteira"], 2, 0);

        $nosso_numero = Tools::formatarNumero($dadosboleto["nosso_numero"], 11, 0);

        //nosso número (sem dv): 11 digitos
        $nosso_numero_SemDv = $carteira . $nosso_numero;

        //dv do nosso número
        $dv_nosso_numero = $this->dvNossoNumero($nosso_numero_SemDv);

        $nossonumero = substr($nosso_numero_SemDv, 0, 2) . '/' . substr($nosso_numero_SemDv, 2) . '-' . $dv_nosso_numero;

        return $nossonumero;
    }

    public function dvNossoNumero($numero)
    {
        $resto2 = Tools::modulo11($numero, 7, 1);
        $digito = 11 - $resto2;
        if ($digito == 10) {
            $dv = "P";
        } elseif ($digito == 11) {
            $dv = 0;
        } else {
            $dv = $digito;
        }
        return $dv;
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