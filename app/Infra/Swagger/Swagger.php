<?php

namespace App\Infra\Swagger;

use OpenApi\Generator;
use OpenApi\Annotations\OpenApi;

class Swagger
{
    private string $docBlocksPath;

    private ?OpenApi $openApiDocumentation = null;

    /**
     * Define o caminho onde os blocos de anotações Swagger estão localizados.
     *
     * @param string $docBlocksPath
     * @return Swagger
     */
    public function setDocBlocksPath(string $docBlocksPath): Swagger
    {
        $this->docBlocksPath = $docBlocksPath;

        return $this;
    }

    /**
     * Gera a documentação Swagger a partir dos blocos de anotações.
     *
     * @return OpenApi
     */
    public function generateDocumentation(): OpenApi
    {
        $this->openApiDocumentation = Generator::scan([$this->docBlocksPath]);

        return $this->openApiDocumentation;
    }

    /**
     * Salva a documentação Swagger em um arquivo JSON.
     *
     * @param string $outputFilePath
     * @return bool
     */
    public function saveDocumentationToFile(string $outputFilePath): bool
    {
        if (!$this->openApiDocumentation) {
            $this->generateDocumentation();
        }

        $jsonDocumentation = $this->openApiDocumentation->toJson();

        return file_put_contents($outputFilePath, $jsonDocumentation) !== false;
    }
}