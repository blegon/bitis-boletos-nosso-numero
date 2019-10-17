<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;

class BoletoCaixaSIGCB extends BoletoCaixaGlobal implements NossoNumeroInterface
{
    public function geraNossoNumero($dadosboleto)
    {
        $dadosboleto["nosso_numero"] = str_pad($dadosboleto["nosso_numero"], 15, "0", STR_PAD_LEFT);

        $dadosboleto["nosso_numero1"] = substr($dadosboleto["nosso_numero"], 0, 3); // tamanho 3
        $dadosboleto["nosso_numero_const1"] = "1"; //constanto 1 , 1=registrada , 2=sem registro
        $dadosboleto["nosso_numero2"] = substr($dadosboleto["nosso_numero"], 3, 3); // tamanho 3
        $dadosboleto["nosso_numero_const2"] = "4"; //constanto 2 , 4=emitido pelo proprio cliente
        $dadosboleto["nosso_numero3"] = substr($dadosboleto["nosso_numero"], 6, 9); // tamanho 9
        //nosso número (sem dv) são 17 digitos
        $nnum = Tools::formatarNumero($dadosboleto["nosso_numero_const1"], 1, 0) . Tools::formatarNumero($dadosboleto["nosso_numero_const2"], 1, 0) . Tools::formatarNumero($dadosboleto["nosso_numero1"], 3, 0) . Tools::formatarNumero($dadosboleto["nosso_numero2"], 3, 0) . Tools::formatarNumero($dadosboleto["nosso_numero3"], 9, 0);
        //nosso número completo (com dv) com 18 digitos
        $nossonumero = $nnum ."-" . $this->dvNossoNumero($nnum);

        return $nossonumero;
    }

    public function dvCedente($numero)
    {
        $resto2 = Tools::modulo11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito == 10 || $digito == 11) {
            $digito = 0;
        }
        $dv = $digito;
        return $dv;
    }
}