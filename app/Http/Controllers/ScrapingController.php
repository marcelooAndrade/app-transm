<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ScrapingController extends Controller
{
    public function executar(Request $request)
    {
        try {

            ini_set('max_execution_time', 300); // permite rodar até 5 minutos

            // Caminho absoluto do seu script Python
            $process = new Process(['python3', base_path('scripts/main.py')]);
            $process->run();


            // Verifica se houve erro
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return back()->with('success', 'Scraping executado com sucesso!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao executar scraping: ' . $e->getMessage());
        }
    }

    public function stream()
    {
        ini_set('max_execution_time', 30);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no'); // nginx

        $scriptPath = base_path('scripts/main.py');
        $process = new \Symfony\Component\Process\Process(['python3', $scriptPath]);
        $process->setTimeout(600); // 10 min

        $process->start();

        foreach ($process as $type => $data) {
            echo "data: " . trim($data) . "\n\n";
            ob_flush();
            flush();
        }

        echo "data: [FINALIZADO]\n\n";
        ob_flush();
        flush();
    }
}
