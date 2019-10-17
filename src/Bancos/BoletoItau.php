<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;

class BoletoItau implements NossoNumeroInterface
{
    private $codigobanco = "341";

    public function geraNossoNumero($dadosboleto)
    {

        //agencia � 4 digitos
        $agencia = Tools::formatarNumero($dadosboleto["agencia"], 4, 0);
        //conta � 5 digitos + 1 do dv
        $conta = Tools::formatarNumero($dadosboleto["conta"], 5, 0);
        //carteira 175
        $carteira = $dadosboleto["carteira"];
        //nosso_numero no maximo 8 digitos
        $nnum = Tools::formatarNumero($dadosboleto["nosso_numero"], 8, 0);

        $nosso_numero = $nnum . '-' . Tools::modulo10($agencia . $conta . $carteira . $nnum);

        return $nosso_numero;
    }

    public function dvBarra($numero)
    {
        $resto2 = Tools::modulo11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito == 0 || $digito == 1 || $digito == 10  || $digito == 11) {
            $dv = 1;
        } else {
            $dv = $digito;
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