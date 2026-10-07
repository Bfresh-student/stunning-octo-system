<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\User;
use App\Services\Hashage;
use App\Services\UploadFiles;
use App\Utils\Csrf;

class UserController extends Controller
{
    public function __construct(private User $user, private UploadFiles $uploadFiles)
    {
    }

    private function getBaseUrl(): string
    {
        $base = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($base === '.' || $base === '/' || $base === '\\' || empty($base)) {
            return '';
        }
        $base = str_replace('\\', '/', $base);
        return '/' . trim($base, '/');
    }

    public function profile(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        $baseUrl = $this->getBaseUrl();

        if (!$user) {
            header('Location: ' . $baseUrl . '/login');
            exit;
        }

        $this->render('profile', [
            'user'    => $user,
            'baseUrl' => $baseUrl
        ]);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header('Location: ' . $this->getBaseUrl() . '/login');
        exit;
    }

    public function login(): void
    {
        $baseUrl = $this->getBaseUrl();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $hashage = new Hashage();
            $userData = $this->user->getUserByEmail($email);

            if ($userData && $hashage->verify($password, $userData['mot_de_passe'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'    => $userData['id'],
                    'nom'   => $userData['nom'],
                    'email' => $userData['email'],
                    'role'  => 'user'
                ];
                header('Location: ' . $baseUrl . '/profile');
                exit;
            } else {
                $error = "Identifiants invalides.";
                $this->render('login', [
                    'error'   => $error,
                    'baseUrl' => $baseUrl
                ]);
                return;
            }
        }

        // Si requête GET : afficher le formulaire de connexion
        $this->render('login', ['baseUrl' => $baseUrl]);
    }

    public function register(): void
    {
        $baseUrl = $this->getBaseUrl();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');
            $csrfToken = trim($_POST['csrf_token'] ?? '');

            // Vérification jeton CSRF
            if (!Csrf::validateToken($csrfToken)) {
                $error = "Jeton CSRF invalide.";
                $this->render('register', ['error' => $error, 'baseUrl' => $baseUrl]);
                return;
            }

            // Vérification des champs
            if (empty($username) || empty($password) || empty($confirmPassword) || empty($email)) {
                $error = "Tous les champs sont requis.";
                $this->render('register', ['error' => $error, 'baseUrl' => $baseUrl]);
                return;
            }

            if (strlen($password) < 8) {
                $error = "Le mot de passe doit contenir au moins 8 caractères.";
                $this->render('register', ['error' => $error, 'baseUrl' => $baseUrl]);
                return;
            }

            if ($password !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas.";
                $this->render('register', ['error' => $error, 'baseUrl' => $baseUrl]);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Adresse e-mail invalide.";
                $this->render('register', ['error' => $error, 'baseUrl' => $baseUrl]);
                return;
            }

            if ($this->user->getUserByEmail($email)) {
                $error = "Un utilisateur avec cet e-mail existe déjà.";
                $this->render('register', ['error' => $error, 'baseUrl' => $baseUrl]);
                return;
            }

            // Upload de l'image de profil (optionnel)
            $profileImage = null;
            if (!empty($_FILES['profile_image']['name'])) {
                try {
                    $profileImage = $this->uploadFiles->uploadFileImage('profile_image');
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    $this->render('register', ['error' => $error, 'baseUrl' => $baseUrl]);
                    return;
                }
            }

            $hashage = new Hashage();
            $hashedPassword = $hashage->hash($password);
            $this->user->addUser($username, $email, $hashedPassword);

            // Connexion automatique après inscription
            $userData = $this->user->getUserByEmail($email);
            $_SESSION['user'] = [
                'id'            => $userData['id'] ?? null,
                'nom'           => $username,
                'email'         => $email,
                'profile_image' => $profileImage,
                'role'          => 'user'
            ];

            header('Location: ' . $baseUrl . '/profile');
            exit;
        }

        // Si requête GET : afficher le formulaire d'inscription
        $this->render('register', ['baseUrl' => $baseUrl]);
    }
}
