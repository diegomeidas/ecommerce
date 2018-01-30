<?php

    //função para formatar valor dos produtos para float
    function formatPrice(float $vlprice)
    {
        return number_format($vlprice, 2, ",", ".");
    }
?>