<?php
/**
 * Modelo: Documento
 * Gestiona el procesamiento de documentos
 */

namespace App\Models;

use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DocumentModel
{
    private $uploadDir;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../uploads';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Procesa un archivo PDF
     */
    public function processPdf(string $filePath): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        $text = '';
        
        foreach ($pdf->getPages() as $page) {
            $text .= $page->getText() . "\n";
        }

        return $this->splitIntoChunks($text, $filePath);
    }

    /**
     * Procesa un archivo TXT
     */
    public function processTxt(string $filePath): array
    {
        $text = file_get_contents($filePath);
        $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        
        if ($encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }

        return $this->splitIntoChunks($text, $filePath);
    }

    /**
     * Procesa un archivo Markdown (MD)
     */
    public function processMd(string $filePath): array
    {
        $text = file_get_contents($filePath);
        $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        
        if ($encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }

        // Remover sintaxis markdown básica para obtener texto plano
        // (opcional: mantener formato si se desea)
        $text = preg_replace('/^#+\s+/m', '', $text); // Headers
        $text = preg_replace('/\*\*(.+?)\*\*/', '$1', $text); // Bold
        $text = preg_replace('/\*(.+?)\*/', '$1', $text); // Italic
        $text = preg_replace('/\[(.+?)\]\(.+?\)/', '$1', $text); // Links

        return $this->splitIntoChunks($text, $filePath);
    }

    /**
     * Procesa un archivo Excel (XLSX)
     */
    public function processXlsx(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $text = '';

            // Procesar todas las hojas
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();
                $text .= "\n\n=== Hoja: {$sheetName} ===\n\n";

                // Obtener todas las celdas con datos
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Convertir columna a número para iterar
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                for ($row = 1; $row <= $highestRow; $row++) {
                    $rowData = [];
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $value = $cell->getCalculatedValue();
                        
                        // Solo agregar celdas con contenido
                        if ($value !== null && trim($value) !== '') {
                            $rowData[] = trim($value);
                        }
                    }
                    
                    // Si la fila tiene datos, agregarla al texto
                    if (!empty($rowData)) {
                        $text .= implode(' | ', $rowData) . "\n";
                    }
                }
            }

            if (empty(trim($text))) {
                throw new \Exception('El archivo Excel está vacío o no contiene datos');
            }

            return $this->splitIntoChunks($text, $filePath);
            
        } catch (\Exception $e) {
            throw new \Exception('Error al procesar archivo Excel: ' . $e->getMessage());
        }
    }

    /**
     * Divide texto en chunks
     */
    private function splitIntoChunks(string $text, string $source): array
    {
        // Aumentar tamaño de chunks para reducir el número total de embeddings
        // Esto acelera el procesamiento significativamente
        $chunkSize = 2000; // Aumentado de 1000 a 2000
        $chunkOverlap = 300; // Aumentado de 200 a 300

        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        $chunks = [];
        $words = explode(' ', $text);
        $totalWords = count($words);
        
        $start = 0;
        $chunkNumber = 0;

        while ($start < $totalWords) {
            $end = min($start + $chunkSize, $totalWords);
            
            if ($end < $totalWords) {
                $actualEnd = $end;
                while ($actualEnd > $start && $words[$actualEnd - 1] !== '') {
                    $actualEnd--;
                }
                if ($actualEnd > $start) {
                    $end = $actualEnd;
                }
            }

            $chunkText = implode(' ', array_slice($words, $start, $end - $start));
            
            if (!empty(trim($chunkText))) {
                $chunks[] = [
                    'text' => trim($chunkText),
                    'source' => basename($source),
                    'chunk_number' => $chunkNumber,
                    'start' => $start,
                    'end' => $end
                ];
            }

            $start = max($end - $chunkOverlap, $start + 1);
            $chunkNumber++;
        }

        return $chunks;
    }

    /**
     * Valida el tipo de archivo
     */
    public function validateFileType(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, ['pdf', 'txt', 'xlsx', 'md']);
    }

    /**
     * Obtiene la ruta de uploads
     */
    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }
}

