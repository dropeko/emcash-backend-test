<?php

namespace App\Http\Controllers\User;

use App\Domain\File\Csv\CsvDataValidator;
use App\Domain\File\Csv\CsvInterface;
use App\Domain\File\UserSpreadsheet\UserSpreadsheet;
use App\Domain\User\User as DomainUser;
use App\Exceptions\CsvEmptyContentException;
use App\Exceptions\CsvHeadersValidation;
use App\Exceptions\DataValidationException;
use App\Exceptions\DuplicatedDataException;
use App\Exceptions\InvalidUserObjectException;
use App\Exceptions\UserSpreadsheetException;
use App\Http\Controllers\Controller;
use App\Http\Helpers\DateTime;
use App\Infra\Db\UserDb;
use App\Infra\File\Csv\Csv;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
/**
 * @OA\Post(
 *     path="/user/spreadsheet",
 *     summary="Importação de usuário através de arquivo CSV novo",
 *     tags={"User"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"file"},
 *                 @OA\Property(
 *                     property="file",
 *                     type="string",
 *                     format="binary",
 *                     description="Campo do tipo arquivo, com o nome 'file', que recebe o arquivo CSV com os dados dos usuários"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response="201",
 *         description="Created",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="created_users",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="date_time",
 *                     type="string"
 *                 ),
 *                 example={
 *                     "created_users": 2,
 *                     "date_time": "2023-12-28 04:10:10"
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response="404",
 *         description="Bad Request",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="bad_request",
 *                     type="string"
 *                 ),
 *                 example={
 *                     "bad_request": "Spreadsheet error: line 2 | CPF already created"
 *                 }
 *             )
 *         )
 *     )
 * )
 */
    public function spreadsheet(Request $request): JsonResponse
    {
        try {
            // Validação do request para garantir que um arquivo foi enviado
            $this->validate($request, ['file' => 'required|file']);

            $uploadedFile = $request->file('file');

            // Cria a instância do CSV e configura suas propriedades
            /** @var CsvInterface $csv */
            $csv = new Csv();
            $csv->setDataValidator(new CsvDataValidator())
                ->setMimeType($uploadedFile->getClientMimeType())
                ->setSizeInBytes((string)$uploadedFile->getSize())
                ->setContent($uploadedFile->getContent())
                ->setExpectedHeaders(['name', 'cpf', 'email', 'data_admissao']);

            // Opcional: você pode construir o array associativo a partir do conteúdo,
            // se necessário para debugar ou validar o CSV antes de importar:
            // $associativeArray = $csv->buildAssociativeArrayFromContent();

            // Cria instâncias dos serviços de domínio e persistência
            $userSpreadsheet = new UserSpreadsheet();
            $userDb = new UserDb();

            // Realiza a importação dos usuários a partir do conteúdo CSV
            $createdCount = $userSpreadsheet->import($csv->getContent(), $userDb);

            return $this->buildCreatedResponse([
                'created_users' => $createdCount,
                'date_time'     => DateTime::formatDateTime('now')
            ]);
        } catch (DataValidationException | CsvHeadersValidation $e) {
            return $this->buildBadRequestResponse($e->getMessage());
        } catch (UserSpreadsheetException | DuplicatedDataException $e) {
            return $this->buildBadRequestResponse($e->getMessage());
        } catch (InvalidUserObjectException $e) {
            return $this->buildBadRequestResponse($e->getMessage());
        } catch (\Exception $e) {
            // Em ambiente de produção, você pode logar o erro e retornar uma mensagem genérica
            return $this->buildBadRequestResponse("Erro interno: " . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     summary="Listagem de todos os usuários cadastrados",
     *     tags={"User"},
     *     @OA\Response(
     *          response="201",
     *          description="Created",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="id",
     *                         type="string",
     *                     ),
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                     ),
     *                     @OA\Property(
     *                         property="email",
     *                         type="string",
     *                     ),
     *                     @OA\Property(
     *                         property="cpf",
     *                         type="string",
     *                     ),
     *                     example={
     *                        {
     *                          "id": "a38a7ac8-9295-33c2-8c0b-5767c1449bc3",
     *                          "name": "Ronaldo de Assis Moreira",
     *                          "email": "ro.naldinho@email.com",
     *                          "cpf": "2023-12-28 04:10:10"
     *                        }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function all(Request $request): JsonResponse
    {
        try {
            $user = new User();

            $users = $user->findAll();

            $response = [];
            foreach($users as $user) {
                $response[] = [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'cpf' => $user->getCpf()
                ];
            }

            return $this->buildSuccessResponse($response);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *     path="/user/spreadsheet",
     *     summary="Geração dos dados dos usuários registrados em formato CSV",
     *     tags={"User"},
     *     @OA\Response(
     *          response="200",
     *          description="Created",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="csv",
     *                         type="string",
     *                     ),
     *                     example={
     *                        "csv": "name,cpf,email\nRonaldo de Assis Moreira,16742019077,drnaoseioque@email.com\n"
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function createSpreadsheet(Request $request): JsonResponse
    {
        try {
            $userDb = new \App\Infra\Db\UserDb();
            $userSpreadsheet = new \App\Domain\File\UserSpreadsheet\UserSpreadsheet();
    
            $content = $userSpreadsheet->export($userDb);
    
            return $this->buildSuccessResponse([
                'csv' => $content
            ]);
        } catch (DataValidationException | \App\Exceptions\CsvEmptyContentException $e) {
            return $this->buildBadRequestResponse($e->getMessage());
        } catch (\App\Exceptions\UserSpreadsheetException $e) {
            return $this->buildBadRequestResponse($e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Retorna os dados de um usuário específico por ID.
     *
     * @OA\Get(
     *     path="/user/{id}",
     *     summary="Retorna os dados de um usuário específico por ID",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="cpf", type="string"),
     *                 @OA\Property(property="data_admissao", type="string"),
     *                 @OA\Property(property="company", type="string"),
     *                 @OA\Property(property="active", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="message", type="string", example="User not found")
     *             )
     *         )
     *     )
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $userDb = new UserDb();
            $user = $userDb->findById($id);
            if (!$user) {
                return $this->buildNotFoundResponse("User not found");
            }
            $response = [
                'id'            => $user->getId(),
                'name'          => $user->getName(),
                'email'         => $user->getEmail(),
                'cpf'           => $user->getCpf(),
                'data_admissao' => $user->getDataAdmissao(),
                'company'       => $user->getCompany(),
                'active'        => $user->isActive(),
            ];
            return $this->buildSuccessResponse($response);
        } catch (\Exception $e) {
            return $this->buildBadRequestResponse("Erro interno: " . $e->getMessage());
        }
    }

    // Métodos auxiliares de resposta - agora declarados como public
    // Mover lógica dos métodos auxliares para a classe Controller
    public function buildSuccessResponse($data, $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    public function buildBadRequestResponse($message, $status = 400): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    public function buildNotFoundResponse($message, $status = 404): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    public function buildCreatedResponse($data, $status = 201): JsonResponse
    {
        return response()->json($data, $status);
    }
}
