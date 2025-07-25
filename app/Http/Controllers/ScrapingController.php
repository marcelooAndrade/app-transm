<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Artisan;


class ScrapingController extends Controller
{
    public function stream()
    {
        return new StreamedResponse(function () {
            set_time_limit(0); // <-- Adicionado aqui

            $process = popen(PHP_BINARY . ' ' . base_path('artisan') . ' scraping:ceramicas', 'r');
            echo "event: message\n";
            echo "data: Iniciando coleta...\n\n";
            ob_flush();
            flush();

            while (!feof($process)) {
                $line = fgets($process);
                if ($line) {
                    echo "event: message\n";
                    echo 'data: ' . trim($line) . "\n\n";
                    ob_flush();
                    flush();
                }
            }

            echo "event: message\n";
            echo "data: [FINALIZADO] Coleta encerrada.\n\n";
            ob_flush();
            flush();

            pclose($process);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
