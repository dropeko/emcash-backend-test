<?php

namespace App\Infra\File\Csv;

use App\Domain\File\Csv\CsvInterface;
use App\Domain\File\File;

class Csv extends File implements CsvInterface
{
    /**
     * Cabeçalhos esperados, se definidos.
     *
     * @var array
     */
    private array $expectedHeaders = [];

    /**
     * Conteúdo CSV processado como array associativo.
     *
     * @var array
     */
    private array $associativeContent = [];

    /**
     * Define os cabeçalhos esperados para o CSV.
     *
     * @param array $expectedHeaders
     * @return CsvInterface
     */
    public function setExpectedHeaders(array $expectedHeaders): CsvInterface
    {
        $this->expectedHeaders = $expectedHeaders;
        return $this;
    }

    /**
     * Constrói e retorna um array associativo a partir do conteúdo CSV.
     *
     * @return array
     * @throws \Exception Caso os cabeçalhos do CSV não correspondam aos esperados.
     */
    public function buildAssociativeArrayFromContent(): array
    {
        $lines = array_filter(explode("\n", $this->getContent()), function ($line) {
            return trim($line) !== '';
        });

        if (empty($lines)) {
            return [];
        }

        $headerLine = array_shift($lines);
        $headers = str_getcsv($headerLine);
  
        if (!empty($this->expectedHeaders) && $headers !== $this->expectedHeaders) {
            throw new \Exception("CSV headers inválidos. Esperado: " . implode(',', $this->expectedHeaders));
        }

        $result = [];
        foreach ($lines as $line) {
            $values = str_getcsv($line);
            
            if (count($values) !== count($headers)) {
                continue; 
            }
            $result[] = array_combine($headers, $values);
        }

        $this->associativeContent = $result;
        return $result;
    }

    /**
     * Define o conteúdo associativo processado.
     *
     * @param array $associativeContent
     * @return CsvInterface
     */
    public function setAssociativeContent(array $associativeContent): CsvInterface
    {
        $this->associativeContent = $associativeContent;
        return $this;
    }

    /**
     * Retorna o conteúdo associativo.
     *
     * @return array
     */
    public function getAssociativeContent(): array
    {
        return $this->associativeContent;
    }

    /**
     * Reconstrói o conteúdo CSV a partir do array associativo.
     *
     * @return void
     */
    public function buildFromAssociativeContent(): void
    {
        if (empty($this->associativeContent)) {
            $this->setContent('');
            return;
        }

        $headers = array_keys(reset($this->associativeContent));
        $lines = [];
        $lines[] = implode(',', $headers);
        foreach ($this->associativeContent as $row) {
            $lines[] = implode(',', $row);
        }
        $this->setContent(implode("\n", $lines));
    }
}
