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
    private UserDb $userDb;

    public function __construct(UserDb $userDb)
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
     *         response="400",
     *         description="Bad Request",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="message",
     *                     type="string"
     *                 ),
     *                 example={
     *                     "message": "Erro interno: Spreadsheet error: line 2 | CPF already created"
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function importSpreadsheet(Request $request): JsonResponse
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
     *     path="/user/spreadsheet",
     *     summary="Geração dos dados dos usuários registrados em formato CSV",
     *     tags={"User"},
     *     @OA\Response(
     *          response="200",
     *          description="Success",
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
    public function exportSpreadsheet(Request $request): JsonResponse
    {
        try {
            $userSpreadsheet = new UserSpreadsheet();
            $content = $userSpreadsheet->export($this->userDb);

            return $this->buildSuccessResponse([
                'csv' => $content
            ]);
        } catch (DataValidationException | CsvEmptyContentException $e) {
            return $this->buildBadRequestResponse($e->getMessage());
        } catch (UserSpreadsheetException $e) {
            return $this->buildBadRequestResponse($e->getMessage());
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
     *          response="200",
     *          description="Success",
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
    public function findAllUsers(Request $request): JsonResponse
    {
        try {
            $users = $this->userDb->findAll();
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
            return $this->buildBadRequestResponse("Erro interno: " . $e->getMessage());
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
            $user = $this->userDb->findById($id);
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
            $user = $this->userDb->findById($id);
            if (!$user) {
                return $this->buildNotFoundResponse("User not found");
            }

            $admissionDate = new \DateTime($user->getDataAdmissao());
            $now = new \DateTime();
            $interval = $admissionDate->diff($now);
            $maxYears = 30;
            $months = $interval->y * 12 + $interval->m;
            $eligible = true;
            $reasons = [];

            if ($months < 6) {
                $eligible = false;
                $reasons[] = "Employee must have more than 6 months of service.";
            }
            if (!$user->isActive()) {
                $eligible = false;
                $reasons[] = "Employee is not active.";
            }
            if (empty($user->getCompany())) {
                $eligible = false;
                $reasons[] = "Employee is not registered in a partner company.";
            }
            
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
            $user = $this->userDb->findById($id);
            if (!$user) {
                return $this->buildNotFoundResponse("User not found");
            }
            $this->userDb->softDelete($user);
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
            $this->validate($request, [
                'name'  => 'required|string|max:100',
                'cpf'   => 'required|string',
                'email' => 'required|email|max:100',
            ]);

            $user = $this->userDb->findById($id);
            if (!$user) {
                return $this->buildNotFoundResponse("User not found");
            }

            $user->setName($request->input('name'));
            $user->setCpf($request->input('cpf'));
            $user->setEmail($request->input('email'));

            $this->userDb->update($user);

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
}