<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;

class BoletoCaixa extends BoletoCaixaGlobal implements NossoNumeroInterface
{

    public function geraNossoNumero($dadosboleto)
    {
        $dadosboleto["inicio_nosso_numero"] = $dadosboleto["numero_carteira"];

        //nosso número (sem dv) 10 digitos
        $nnum = $dadosboleto["inicio_nosso_numero"] . Tools::formatarNumero($dadosboleto["nosso_numero"], 8, 0);
        //nosso n�mero completo (com dv) com 11 digitos
        $nossonumero = $nnum . '-' . $this->dvNossoNumero($nnum);

        return $nossonumero;
    }
}
