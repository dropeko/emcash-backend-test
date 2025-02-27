<p align="center"><a href="https://emcash.com.br/" target="_blank"><img src="https://emcash.com.br/wp-content/uploads/2023/03/emcash-logo_ALTA-01.png" width="600" alt="Emcash"></a></p>

# Emcash API Challenge
 
Esta API é parte do teste técnico da Emcash e foi desenvolvida utilizando PHP e Laravel/Lumen. Ela tem como objetivo receber uma planilha CSV contendo informações essenciais dos funcionários interessados no empréstimo consignado da plataforma Emcash, realizando as devidas validações e processamento dos dados para posterior utilização no sistema.

## Funcionalidades

- **Importação de CSV:**  
  Recebe um arquivo CSV com informações dos funcionários (nome, CPF, e-mail e data de admissão), validando cabeçalhos, duplicidade, formato dos dados e campos obrigatórios.
  
- **Exportação de Dados:**  
  Permite exportar os usuários cadastrados em formato CSV com os cabeçalhos esperados.

- **Validações Rigorosas:**  
  As entradas são rigorosamente validadas (formato de e-mail, CPF, datas, etc.) e erros são tratados de forma granular, com mensagens claras para cada tipo de inconsistência.

- **Persistência Abstrata:**  
  A camada de persistência é definida por interfaces, facilitando a troca entre um repositório em memória (para testes) e um banco de dados real.

- **Testes Automatizados:**  
  A API possui uma suíte completa de testes unitários e de integração que garantem a qualidade e integridade do código.

## Estrutura do Projeto

A estrutura do projeto está organizada da seguinte forma:
```shell
   app/
├── Console/
├── Domain/
│   ├── Cpf/
│   │   └── Cpf.php
│   ├── File/
│   │   ├── Csv/
│   │   │   ├── CsvDataValidator.php
│   │   │   ├── CsvInterface.php
│   │   │   └── UserSpreadsheet/
│   │   │       └── UserSpreadsheet.php
│   │   ├── File.php
│   │   ├── FileDataValidatorInterface.php
│   │   └── FileInterface.php
│   ├── User/
│   │   ├── User.php
│   │   ├── UserDataValidator.php
│   │   ├── UserDataValidatorInterface.php
│   │   └── UserPersistenceInterface.php
│   └── Uuid/
│       └── UuidGeneratorInterface.php
├── Events/
├── Exceptions/
│   ├── CsvEmptyContentException.php
│   ├── CsvHeadersValidation.php
│   ├── DataValidationException.php
│   ├── DuplicatedDataException.php
│   ├── Handler.php
│   ├── InvalidUserObjectException.php
│   ├── UserNotFoundException.php
│   └── UserSpreadsheetException.php
├── Http/
│   ├── Controllers/
│   │   ├── Swagger/
│   │   │   └── SwaggerController.php
│   │   └── User/
│   │       └── UserController.php
│   ├── Helpers/
│   │   └── DateTime.php
│   ├── Middleware/
│   │   ├── Authenticate.php
│   │   ├── CorsMiddleware.php
│   │   └── ExampleMiddleware.php
├── Infra/
│   ├── Db/
│   │   └── UserDb.php
│   ├── File/
│   │   └── Csv/
│   │       └── Csv.php
│   ├── Memory/
│   │   └── UserMemory.php
│   ├── Swagger/
│   │   └── Swagger.php
│   └── Uuid/
│       └── UuidGenerator.php
├── Jobs/
├── Listeners/
├── Models/
│   └── User.php
├── Providers/
│   ├── AppServiceProvider.php
│   ├── AuthServiceProvider.php
│   └── EventServiceProvider.php
├── bootstrap/
│   └── app.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   └── 2025_02_25_142407_create_user_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserTableSeeder.php
├── public/
│   ├── swagger-ui/
│   │   └── index.html
│   ├── .htaccess
│   ├── api-docs.json
│   └── index.php
├── resources/
│   └── views/
├── routes/
│   └── web.php
├── storage/
├── tests/
│   ├── Http/
│   │   ├── Swagger/
│   │   │   └── SwaggerHttpTest.php
│   │   └── User/
│   │       └── UserHttpTest.php
│   └── Unit/
│       ├── File/
│       │   ├── FileTest.php
│       │   └── UserSpreadsheetTest.php
│       └── User/
│           └── UserTest.php
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
├── docker-compose.yml
├── phpunit.xml
└── README.md
```

## Instruções de Instalação e Execução

### Pré-requisitos

- Docker e Docker Compose instalados  
- Chave SSH configurada para acesso ao repositório

### Passo a Passo

<!--
1. **Clonar o repositório**  
   git clone git@github.com:seu-usuario/nome-do-repositorio.git
-->

1. **Clonar o repositório (usando chave SSH)**  
   <!-- git clone git@github.com:seu-usuario/nome-do-repositorio.git -->

2. **Instalar as dependências via Composer**  
   <!-- composer install --ignore-platform-reqs -->

3. **Build e Execução dos Containers**  
   <!-- docker-compose build  
   docker-compose up -d -->

4. **Acessar o Container e Executar Instalações**  
   Entre no container:
   <!-- docker exec -it um_api bash -->
   
   Dentro do container, execute:
   <!-- composer install --ignore-platform-reqs  
   php artisan migrate:reset  
   php artisan migrate --seed -->

5. **Atualizar Documentação do Swagger**  
   Ainda dentro do container, gere a documentação:
   <!-- ./vendor/bin/openapi --output ./public/api-docs.json ./app -->

6. **Executar os Testes**  
   Dentro do container, para rodar os testes:
   <!-- vendor/bin/phpunit -->

7. **Acessar a Documentação do Swagger**  
   Abra seu navegador e acesse a rota configurada para os docs (por exemplo, `http://localhost:82/`).

## Destaques Técnicos

- **Validação e Tratamento de Exceções:**  
  A API implementa um sistema robusto de validação e tratamento de exceções, garantindo que erros como CSV vazio, cabeçalhos incorretos e dados duplicados sejam reportados de forma clara e precisa.

- **Arquitetura Orientada a Domínio:**  
  O projeto segue os princípios do Domain Driven Design (DDD), com uma clara separação entre domínio, infraestrutura e apresentação. Isso facilita a manutenção, escalabilidade e testabilidade do sistema.

- **Testes Automatizados:**  
  Uma abrangente suíte de testes unitários e de integração garante a qualidade do código e permite um feedback rápido durante o desenvolvimento.

- **Persistência Abstrata:**  
  A utilização de interfaces para a camada de persistência permite alternar facilmente entre diferentes implementações (por exemplo, banco de dados real ou repositório em memória para testes).

- **Ambiente Docker:**  
  Toda a aplicação é executada em containers Docker, garantindo consistência e isolamento entre os ambientes de desenvolvimento, teste e produção.

## Considerações Finais

Esta API foi desenvolvida com foco em clareza, manutenibilidade e escalabilidade, utilizando as melhores práticas de desenvolvimento em PHP com Laravel/Lumen. A implementação de uma arquitetura bem definida, aliada a testes automatizados e a um robusto sistema de validação, demonstra um alto nível técnico e diferencia este projeto no cenário competitivo.

*Para dúvidas ou suporte, por favor, entre em contato.*

