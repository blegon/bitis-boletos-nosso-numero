<?php

namespace Bitis\Boletos\NossoNumero\Bancos;

use Bitis\Boletos\NossoNumero\Contracts\NossoNumeroInterface;
use Bitis\Boletos\NossoNumero\Tools\Tools;

class BoletoBB implements NossoNumeroInterface
{

    /**
     * @var string $codigobanco
     */
    private $codigobanco = "001";

    /**
     * @param array $dadosboleto
     * @return string
     */
    public function geraNossoNumero($dadosboleto)
    {
        $dadosboleto["formatacao_convenio"] = strlen($dadosboleto["convenio"]);

        // Carteira 18 com Convênio de 8 dígitos
        if ($dadosboleto["formatacao_convenio"] == "8") {
            $convenio = Tools::formatarNumero($dadosboleto["convenio"], 8, 0, "convenio");
            // Nosso numero de até 9 dígitos
            $nossonumero = Tools::formatarNumero($dadosboleto["nosso_numero"], 9, 0);
            //montando o nosso numero que aparecerá no boleto
            $nossonumero = $convenio . $nossonumero . "-" . $this->modulo11($convenio . $nossonumero);
        }

        // Carteira 18 com Convênio de 7 dígitos
        if ($dadosboleto["formatacao_convenio"] == "7") {
            $convenio = Tools::formatarNumero($dadosboleto["convenio"], 7, 0, "convenio");
            // Nosso numero de até 10 dígitos
            $nossonumero = Tools::formatarNumero($dadosboleto["nosso_numero"], 10, 0);
            $nossonumero = $convenio . $nossonumero;
            //Não existe DV na composição do nosso-número para convênios de sete posições
        }

        // Carteira 18 com Convênio de 6 dígitos
        if ($dadosboleto["formatacao_convenio"] == "6") {
            $convenio = Tools::formatarNumero($dadosboleto["convenio"], 6, 0, "convenio");

            // Nosso número de até 5 dígitos
            $nossonumero = Tools::formatarNumero($dadosboleto["nosso_numero"], 5, 0);
            //montando o nosso numero que aparecerá no boleto
            $nossonumero = $convenio . $nossonumero . "-" . $this->modulo11($convenio . $nossonumero);
        }

        return $nossonumero;
    }

    /*
      #################################################
      FUN��O DO M�DULO 11 RETIRADA DO PHPBOLETO

      MODIFIQUEI ALGUMAS COISAS...

      ESTA FUN��O PEGA O D�GITO VERIFICADOR:

      NOSSONUMERO
      AGENCIA
      CONTA
      CAMPO 4 DA LINHA DIGIT�VEL
      #################################################
     */

    public function modulo11($num, $base = 9, $r = 0)
    {
        $soma = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num, $i - 1, 1);
            $parcial[$i] = $numeros[$i] * $fator;
            $soma += $parcial[$i];
            if ($fator == $base) {
                $fator = 1;
            }
            $fator++;
        }
        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;

            //corrigido
            if ($digito == 10) {
                $digito = "X";
            }

            /*
              alterado por mim, Daniel Schultz

              Vamos explicar:

              O m�dulo 11 s� gera os digitos verificadores do nossonumero,
              agencia, conta e digito verificador com codigo de barras (aquele que fica sozinho e triste na linha digit�vel)
              s� que � foi um rolo...pq ele nao podia resultar em 0, e o pessoal do phpboleto se esqueceu disso...

              No BB, os d�gitos verificadores podem ser X ou 0 (zero) para agencia, conta e nosso numero,
              mas nunca pode ser X ou 0 (zero) para a linha digit�vel, justamente por ser totalmente num�rica.

              Quando passamos os dados para a fun��o, fica assim:

              Agencia = sempre 4 digitos
              Conta = at� 8 d�gitos
              Nosso n�mero = de 1 a 17 digitos

              A unica vari�vel que passa 17 digitos � a da linha digitada, justamente por ter 43 caracteres

              Entao vamos definir ai embaixo o seguinte...

              se (strlen($num) == 43) { n�o deixar dar digito X ou 0 }
             */

            if (strlen($num) == "43") {
                //ent�o estamos checando a linha digit�vel
                if ($digito == "0" or $digito == "X" or $digito > 9) {
                    $digito = 1;
                }
            }
            return $digito;
        }
        return $soma % 11;
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