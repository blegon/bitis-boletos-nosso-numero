<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;
use DateTime;

class BoletoSicredi implements NossoNumeroInterface
{
    private $codigobanco = "748";

    public function geraNossoNumero($dadosboleto)
    {
        $data_processamento = DateTime::createFromFormat('d/m/Y', $dadosboleto["data_processamento"]);
        $inicio_nosso_numero = $data_processamento->format('y') ?? date("y");	// Ano da geração do título ex: 07 para 2007

        $dadosboleto["nosso_numero"] = substr($dadosboleto["nosso_numero"], -5);

        $byteidt = "2";

        $numero_seq = Tools::formatarNumero($dadosboleto["nosso_numero"], 5, "0");

        //nosso número (sem dv) é 8 digitos, formado por:
        //dois ultimos digitos do ano atual
        //+ Byte de Identificação do cedente: 1 - Cooperativa; 2 a 9 - Cedente
        //+ número sequencial de tamanho 5
        $nosso_numero_SemDv = $inicio_nosso_numero . $byteidt . $numero_seq;

        //agencia é 4 digitos
        $agencia = Tools::formatarNumero($dadosboleto["agencia"], 4, "0");

        //posto da cooperativa de credito é 2 digitos
        $posto = Tools::formatarNumero($dadosboleto["posto"], 2, "0");

        //no parana usa o codigo do cedente para gerar o nosso numero, e aqui o codigo do cedente é o mesmo numero da conta
        //daí alterando pra usar o codigo do cedente na geração do nosso numero
        //conta é 5 digitos
        $cedente = Tools::formatarNumero($dadosboleto["cedente"], 5, "0");

        //dv da conta
        //calculo do DV do nosso número
        $dv = $this->dvNossoNumero("$agencia$posto$cedente$nosso_numero_SemDv");

        return $nosso_numero_SemDv . $dv;
    }

    public function dvNossoNumero($numero)
    {
        $resto2 = Tools::modulo11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito > 9) {
            $dv = 0;
        } else {
            $dv = $digito;
        }
        return $dv;
    }

    public function dvBarra($numero)
    {
        $resto2 = Tools::modulo11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito <= 1 || $digito >= 10) {
            $dv = 1;
        } else {
            $dv = $digito;
        }
        return $dv;
    }

    public function dvCampoLivre($numero)
    {
        $resto2 = Tools::modulo11($numero, 9, 1);
        // esta rotina sofreu algumas altera��es para ajustar no layout do SICREDI
        if ($resto2 <=1) {
            $dv = 0;
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
        return $this->codigobanco . "-X";
    }
}