<?php
declare(strict_types=1);

// Ponto de entrada alternativo: permite abrir o site mesmo sem reescrita de URL (.htaccess)
// quando o projeto é enviado para uma pasta do servidor (ex.: public_html/manipula-o).
require __DIR__ . '/public/index.php';
