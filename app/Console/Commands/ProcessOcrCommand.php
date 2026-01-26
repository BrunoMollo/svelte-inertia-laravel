<?php

namespace App\Console\Commands;

use App\Services\Ocr\OcrService;
use Illuminate\Console\Command;

class ProcessOcrCommand extends Command
{
    protected $signature = 'ocr:process
        {file : Ruta al archivo (imagen o PDF)}
        {--dpi= : DPI para el procesamiento OCR (default: 300)}
        {--lang= : Idioma(s) para OCR (spa, eng, spa+eng)}
        {--output= : Archivo de salida (opcional, por defecto stdout)}';

    protected $description = 'Procesa un archivo con OCR usando Python3 y Tesseract';

    public function handle(OcrService $ocrService): int
    {
        $filePath = $this->argument('file');
        $dpi = $this->option('dpi') ? (int) $this->option('dpi') : null;
        $lang = $this->option('lang');
        $outputFile = $this->option('output');

        // Resolve relative paths
        if (! str_starts_with($filePath, '/')) {
            $filePath = getcwd().'/'.$filePath;
        }

        $this->info("Procesando archivo: {$filePath}");

        if ($dpi) {
            $this->info("DPI: {$dpi}");
        }

        if ($lang) {
            $this->info("Idioma: {$lang}");
        }

        $this->newLine();
        $this->info('Iniciando OCR...');
        $this->newLine();

        $result = $ocrService->process($filePath, $dpi, $lang);

        if (! $result->success) {
            $this->error("Error: {$result->error}");

            return self::FAILURE;
        }

        // Output statistics
        $this->info("Páginas procesadas: {$result->pages}");
        $this->info(sprintf('Tiempo de procesamiento: %.2f segundos', $result->processingTime));
        $this->info(sprintf('Caracteres extraídos: %d', strlen($result->text)));
        $this->newLine();

        // Handle output
        if ($outputFile) {
            file_put_contents($outputFile, $result->text);
            $this->info("Resultado guardado en: {$outputFile}");
        } else {
            $this->line('--- Texto extraído ---');
            $this->newLine();
            $this->line($result->text);
            $this->newLine();
            $this->line('--- Fin del texto ---');
        }

        return self::SUCCESS;
    }
}
