<?php
declare(strict_types=1);

// Instalador acessível diretamente (…/install.php), sem depender de reescrita de URL.
// Fica bloqueado depois da instalação (storage/installed.lock).
const FORCE_PATH = '/install.php';

require __DIR__ . '/public/index.php';
