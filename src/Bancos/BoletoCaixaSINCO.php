<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;

class BoletoCaixaSINCO extends BoletoCaixaGlobal implements NossoNumeroInterface
{
    
    public function geraNossoNumero($dadosboleto)
    {
        $dadosboleto["inicio_nosso_numero"] = "9";  // Inicio do Nosso numero - obrigatoriamente deve come�ar com 9;
        //nosso n�mero (sem dv) � 17 digitos
        $nnum = $dadosboleto["inicio_nosso_numero"] . Tools::formatarNumero($dadosboleto["nosso_numero"], 17, 0);
        //dv do nosso n�mero
        $dv_nosso_numero = $this->dvNossoNumero($nnum);
        $nossonumero_dv = "$nnum$dv_nosso_numero";

        $nossonumero = substr($nossonumero_dv, 0, 18) . '-' . substr($nossonumero_dv, 18, 1);

        return $nossonumero;
    }
}