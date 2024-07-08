<?php

namespace Bitis\Boletos\NossoNumero\Contracts;

interface NossoNumeroInterface
{

    /**
     * @param array $dadosboleto
     * @return string
     */
    public function geraNossoNumero($dadosboleto);
}