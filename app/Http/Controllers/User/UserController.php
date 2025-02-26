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
use App\Infra\Uuid\UuidGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{   
    private UserDb $userDb;

    public function __construct(UserDb $userDb, UuidGenerator $uuidGenerator)
    {
        $this->userDb = $userDb;
    }
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
            $this->validate($request, ['file' => 'required|file']);
    
            $uploadedFile = $request->file('file');
    
            $csv = new Csv();
            $csv->setDataValidator(new CsvDataValidator())
                ->setMimeType($uploadedFile->getClientMimeType())
                ->setSizeInBytes((string)$uploadedFile->getSize())
                ->setContent($uploadedFile->getContent())
                ->setExpectedHeaders(['name', 'cpf', 'email', 'data_admissao']);
    
            $userSpreadsheet = new UserSpreadsheet();
            $createdCount = $userSpreadsheet->import($csv->getContent(), $this->userDb);
    
            return $this->buildCreatedResponse([
                'created_users' => $createdCount,
                'date_time'     => DateTime::formatDateTime('now')
            ]);
        } catch (\Exception $e) {
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
            $userDb = new UserDb();
            $users = $userDb->findAll();
            $response = [];
            foreach ($users as $userItem) {
                $response[] = [
                    'id'    => $userItem->getId(),
                    'name'  => $userItem->getName(),
                    'email' => $userItem->getEmail(),
                    'cpf'   => $userItem->getCpf(),
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
    public function showById(Request $request, string $id): JsonResponse
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

        /**
     * Retorna a elegibilidade do funcionário para solicitação de crédito consignado.
     *
     * @OA\Get(
     *     path="/user/{id}/eligibility",
     *     summary="Retorna a elegibilidade do funcionário para solicitação de crédito consignado",
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
     *         description="Elegibility status",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="eligible", type="boolean"),
     *                 @OA\Property(property="reasons", type="array", @OA\Items(type="string"))
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
    public function eligibility(Request $request, string $id): JsonResponse
    {
        try {
            $userDb = new UserDb();
            $user = $userDb->findById($id);
            if (!$user) {
                return $this->buildNotFoundResponse("User not found");
            }
            // Calcula o tempo de serviço (em meses) a partir da data de admissão.
            $admissionDate = new \DateTime($user->getDataAdmissao());
            $now = new \DateTime();
            $interval = $admissionDate->diff($now);
            $months = $interval->y * 12 + $interval->m;
            $eligible = true;
            $reasons = [];

            // Regra 1: Deve ter mais de 6 meses de serviço.
            if ($months < 6) {
                $eligible = false;
                $reasons[] = "Employee must have more than 6 months of service.";
            }
            // Regra 2: O funcionário deve estar ativo.
            if (!$user->isActive()) {
                $eligible = false;
                $reasons[] = "Employee is not active.";
            }
            // Regra 3: O funcionário deve ter uma empresa cadastrada.
            if (empty($user->getCompany())) {
                $eligible = false;
                $reasons[] = "Employee is not registered in a partner company.";
            }
            // Regra 4: O funcionário não pode ter sido admitido há mais de X anos (ex: 30 anos).
            $maxYears = 30;
            if ($interval->y > $maxYears) {
                $eligible = false;
                $reasons[] = "Employee was admitted more than {$maxYears} years ago.";
            }
            return $this->buildSuccessResponse([
                'eligible' => $eligible,
                'reasons'  => $eligible ? [] : $reasons,
            ]);
        } catch (\Exception $e) {
            return $this->buildBadRequestResponse("Erro interno: " . $e->getMessage());
        }
    }

        /**
     * Realiza o soft delete de um usuário.
     *
     * @OA\Delete(
     *     path="/user/{id}",
     *     summary="Realiza o soft delete de um usuário",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário a ser removido",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User soft deleted successfully",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="message", type="string", example="User soft deleted successfully")
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
    public function delete(Request $request, string $id): JsonResponse
    {
        try {
            $userDb = new UserDb();
            $user = $userDb->findById($id);
            if (!$user) {
                return $this->buildNotFoundResponse("User not found");
            }
            $userDb->softDelete($user);
            return $this->buildSuccessResponse(["message" => "User soft deleted successfully"]);
        } catch (\Exception $e) {
            return $this->buildBadRequestResponse("Erro interno: " . $e->getMessage());
        }
    }

    /**
     * Atualiza as informações (nome, CPF e email) de um usuário específico.
     *
     * @OA\Put(
     *     path="/user/{id}",
     *     summary="Atualiza as informações (nome, CPF e email) de um usuário",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário a ser atualizado",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name","cpf","email"},
     *                 @OA\Property(property="name", type="string", example="Novo Nome"),
     *                 @OA\Property(property="cpf", type="string", example="12345678901"),
     *                 @OA\Property(property="email", type="string", example="novo.email@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User updated successfully",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="cpf", type="string"),
     *                 @OA\Property(property="email", type="string")
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
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            // Valida os dados enviados
            $this->validate($request, [
                'name'  => 'required|string|max:100',
                'cpf'   => 'required|string',
                'email' => 'required|email|max:100',
            ]);

            $userDb = new UserDb();
            $user = $userDb->findById($id);
            if (!$user) {
                return $this->buildNotFoundResponse("User not found");
            }

            // Atualiza os dados
            $user->setName($request->input('name'));
            $user->setCpf($request->input('cpf'));
            $user->setEmail($request->input('email'));

            // Chama o repositório para atualizar o registro
            $userDb->update($user);

            // Retorna os dados atualizados
            $response = [
                'id'    => $user->getId(),
                'name'  => $user->getName(),
                'cpf'   => $user->getCpf(),
                'email' => $user->getEmail(),
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
