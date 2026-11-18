<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Middleware;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\BusinessRepository;
use App\Repositories\UserRepository;

/**
 * Controlador de autenticação.
 *
 * Gerencia registro de usuários, login, logout, dados do
 * usuário autenticado e recuperação de senha.
 */
class AuthController
{
    /**
     * Repositório de usuários.
     */
    private UserRepository $userRepo;

    /**
     * Repositório de empresas.
     */
    private BusinessRepository $businessRepo;

    /**
     * Inicializa os repositórios necessários.
     */
    public function __construct()
    {
        $db = Database::getInstance();
        $this->userRepo = new UserRepository($db);
        $this->businessRepo = new BusinessRepository($db);
    }

    /**
     * Registra um novo usuário.
     *
     * Handles POST /api/auth/register.
     * Para papel 'business_owner', também cria um rascunho de registro de empresa.
     *
     * @return void
     */
    public function register(): void
    {
        Middleware::rateLimit('auth_register', 10, 60);

        $input = $this->getJsonInput();
        $errors = [];

        // Validação dos campos obrigatórios
        if (empty($input['full_name'])) {
            $errors[] = 'O campo "Nome completo" é obrigatório.';
        }

        if (empty($input['email'])) {
            $errors[] = 'O campo "E-mail" é obrigatório.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'O e-mail informado é inválido.';
        }

        if (empty($input['password'])) {
            $errors[] = 'O campo "Senha" é obrigatório.';
        } elseif (strlen($input['password']) < 8) {
            $errors[] = 'A senha deve ter no mínimo 8 caracteres.';
        }

        if (!empty($input['password']) && ($input['password'] !== ($input['password_confirmation'] ?? ''))) {
            $errors[] = 'A confirmação de senha não confere.';
        }

        $allowedRoles = ['customer', 'business_owner'];
        $role = $input['user_role'] ?? 'customer';
        if (!in_array($role, $allowedRoles, true)) {
            $errors[] = 'Tipo de conta inválido.';
        }

        if (!empty($errors)) {
            Response::error('Erro de validação.', 422, $errors);
        }

        // Verifica se o e-mail já está cadastrado
        if ($this->userRepo->emailExists($input['email'])) {
            Response::error('Este e-mail já está cadastrado.', 409);
        }

        // Cria o hash da senha com Argon2id
        $passwordHash = password_hash($input['password'], PASSWORD_ARGON2ID);

        $userId = $this->userRepo->create([
            'full_name'      => $input['full_name'],
            'email'          => $input['email'],
            'password_hash'  => $passwordHash,
            'phone'          => $input['phone'] ?? null,
            'user_role'      => $role,
            'account_status' => 'pending_verification',
        ]);

        // Para business_owner, cria um rascunho de registro de empresa
        $businessId = null;
        if ($role === 'business_owner') {
            $businessId = $this->businessRepo->create([
                'owner_user_id'       => $userId,
                'trade_name'          => $input['trade_name'] ?? '',
                'legal_name'          => $input['legal_name'] ?? '',
                'cnpj'                => $input['cnpj'] ?? '00000000000000',
                'verification_status' => 'draft',
                'cnpj_status'         => 'pending',
            ]);
        }

        $responseData = [
            'user_id' => $userId,
        ];

        if ($businessId !== null) {
            $responseData['business_id'] = $businessId;
        }

        Response::success('Cadastro realizado com sucesso!', $responseData, 201);
    }

    /**
     * Autentica um usuário existente.
     *
     * Handles POST /api/auth/login.
     * Valida credenciais, cria sessão e retorna dados do usuário.
     *
     * @return void
     */
    public function login(): void
    {
        Middleware::rateLimit('auth_login', 5, 60);

        $input = $this->getJsonInput();
        $errors = [];

        if (empty($input['email'])) {
            $errors[] = 'O campo "E-mail" é obrigatório.';
        }

        if (empty($input['password'])) {
            $errors[] = 'O campo "Senha" é obrigatório.';
        }

        if (!empty($errors)) {
            Response::error('Erro de validação.', 422, $errors);
        }

        // Busca o usuário com hash da senha
        $user = $this->userRepo->findByEmailWithPassword($input['email']);

        if ($user === null || !password_verify($input['password'], $user['password_hash'])) {
            Response::error('E-mail ou senha incorretos.', 401);
        }

        // Verifica se a conta está ativa
        if ($user['account_status'] === 'suspended') {
            Response::error('Sua conta está suspensa. Entre em contato com o suporte.', 403);
        }

        // Remove o hash da senha antes de armazenar na sessão
        unset($user['password_hash']);

        // Regenera o ID da sessão para prevenir fixação
        Session::regenerate();
        Session::setUser($user);

        // Gera token CSRF para a sessão
        $csrfToken = Middleware::generateCsrfToken();

        Response::success('Login realizado com sucesso!', [
            'user'       => $user,
            'csrf_token' => $csrfToken,
        ]);
    }

    /**
     * Encerra a sessão do usuário.
     *
     * Handles POST /api/auth/logout.
     *
     * @return void
     */
    public function logout(): void
    {
        Session::destroy();
        Response::success('Sessão encerrada com sucesso.');
    }

    /**
     * Retorna os dados do usuário autenticado.
     *
     * Handles GET /api/auth/me.
     * Retorna 401 se não estiver autenticado.
     *
     * @return void
     */
    public function me(): void
    {
        Session::requireAuth();

        $user = Session::getUser();

        Response::success('Dados do usuário.', [
            'user' => $user,
        ]);
    }

    /**
     * Inicia o fluxo de recuperação de senha.
     *
     * Handles POST /api/auth/recover-password.
     * Valida se o e-mail existe, gera token de recuperação (stub para envio de e-mail).
     *
     * @return void
     */
    public function recoverPassword(): void
    {
        Middleware::rateLimit('auth_recover', 3, 120);

        $input = $this->getJsonInput();

        if (empty($input['email'])) {
            Response::error('O campo "E-mail" é obrigatório.', 422);
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            Response::error('O e-mail informado é inválido.', 422);
        }

        // Verifica se o e-mail existe
        $userExists = $this->userRepo->emailExists($input['email']);

        if ($userExists) {
            // Gera token de recuperação (stub — integrar com serviço de e-mail)
            $token = bin2hex(random_bytes(32));

            // TODO: Salvar token no banco com expiração e enviar e-mail
            // PasswordResetRepository->create($email, $token, expiry)
            // EmailService->sendPasswordReset($email, $token)
        }

        // Sempre retorna sucesso para não revelar se o e-mail existe
        Response::success(
            'Se o e-mail estiver cadastrado, você receberá as instruções de recuperação de senha em breve.'
        );
    }

    /**
     * Obtém e valida o corpo JSON da requisição.
     *
     * @return array Dados do corpo da requisição, sanitizados.
     */
    private function getJsonInput(): array
    {
        $rawBody = file_get_contents('php://input');

        if (empty($rawBody)) {
            return Middleware::sanitizeInput($_POST);
        }

        $data = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('Corpo da requisição inválido. Envie um JSON válido.', 400);
        }

        return Middleware::sanitizeInput($data ?? []);
    }
}
