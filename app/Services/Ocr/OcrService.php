<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class OcrService
{
    public function __construct(
        private string $pythonPath,
        private string $scriptPath,
        private int $defaultDpi,
        private string $defaultLang,
        private int $timeout,
        private array $supportedExtensions,
        private array $supportedLanguages,
        private int $maxDpi,
    ) {}

    public function process(
        string $filePath,
        ?int $dpi = null,
        ?string $lang = null,
    ): OcrResult {
        $startTime = microtime(true);

        $dpi = $dpi ?? $this->defaultDpi;
        $lang = $lang ?? $this->defaultLang;

        // Validate file
        if (! file_exists($filePath)) {
            return OcrResult::failure("Archivo no encontrado: {$filePath}");
        }

        if (! is_file($filePath)) {
            return OcrResult::failure("La ruta no es un archivo: {$filePath}");
        }

        // Validate extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (! in_array($extension, $this->supportedExtensions)) {
            return OcrResult::failure(
                "Extensión no soportada: .{$extension}. Soportadas: ".implode(', ', $this->supportedExtensions)
            );
        }

        // Validate DPI
        if ($dpi < 72 || $dpi > $this->maxDpi) {
            return OcrResult::failure(
                "DPI debe estar entre 72 y {$this->maxDpi}. Recibido: {$dpi}"
            );
        }

        // Validate language
        $requestedLangs = explode('+', $lang);
        foreach ($requestedLangs as $requestedLang) {
            if (! in_array($requestedLang, $this->supportedLanguages) && ! in_array($lang, $this->supportedLanguages)) {
                return OcrResult::failure(
                    "Idioma no soportado: {$requestedLang}. Soportados: ".implode(', ', $this->supportedLanguages)
                );
            }
        }

        // Build command
        $process = new Process([
            $this->pythonPath,
            $this->scriptPath,
            $filePath,
            '--dpi='.$dpi,
            '--lang='.$lang,
        ]);

        $process->setTimeout($this->timeout);

        try {
            $process->run();

            $output = $process->getOutput();
            $processingTime = microtime(true) - $startTime;

            if (! $process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput();
                Log::error('OCR process failed', [
                    'file' => $filePath,
                    'exit_code' => $process->getExitCode(),
                    'error' => $errorOutput,
                    'output' => $output,
                ]);

                // Try to parse JSON error from output
                $decoded = json_decode($output, true);
                if ($decoded && isset($decoded['error'])) {
                    return OcrResult::failure($decoded['error'], $processingTime);
                }

                return OcrResult::failure(
                    "El proceso OCR falló con código {$process->getExitCode()}: {$errorOutput}",
                    $processingTime
                );
            }

            // Parse JSON response
            $decoded = json_decode($output, true);

            if ($decoded === null) {
                Log::error('OCR output is not valid JSON', [
                    'file' => $filePath,
                    'output' => $output,
                ]);

                return OcrResult::failure(
                    'La salida del proceso OCR no es JSON válido',
                    $processingTime
                );
            }

            if (! $decoded['success']) {
                return OcrResult::failure(
                    $decoded['error'] ?? 'Error desconocido en OCR',
                    $processingTime
                );
            }

            Log::info('OCR process completed', [
                'file' => $filePath,
                'pages' => $decoded['pages'],
                'text_length' => strlen($decoded['text']),
                'processing_time' => round($processingTime, 2),
            ]);

            return OcrResult::success(
                $decoded['text'],
                $decoded['pages'],
                $processingTime
            );

        } catch (ProcessTimedOutException $e) {
            $processingTime = microtime(true) - $startTime;
            Log::error('OCR process timed out', [
                'file' => $filePath,
                'timeout' => $this->timeout,
            ]);

            return OcrResult::failure(
                "El proceso OCR excedió el tiempo límite de {$this->timeout} segundos",
                $processingTime
            );
        }
    }

    public function getSupportedExtensions(): array
    {
        return $this->supportedExtensions;
    }

    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }
}
