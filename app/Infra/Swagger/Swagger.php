<?php

namespace App\Infra\Swagger;

class Swagger
{
    private string $docBlocksPath;

    // private OpenApi $openApiDocumentation;

    public function setDocBlocksPath(string $docBlocksPath): Swagger
    {
        $this->docBlocksPath = $docBlocksPath;

        return $this;
    }

    // public function generateDocumentation(): OpenApi
    // {
    //     $this->openApiDocumentation = Generator::scan([$this->docBlocksPath]);

    //     return $this->openApiDocumentation;
    // }
}
