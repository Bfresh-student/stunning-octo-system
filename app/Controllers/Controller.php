<?php

declare(strict_types=1);

namespace App\Controllers;

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        include __DIR__ . "/../Views/$view.php";
    }
}
