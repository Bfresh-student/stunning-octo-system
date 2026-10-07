<?php

declare(strict_types=1);

namespace App\Services;

class UploadFiles
{
    public function __construct()
    {
    }

    public function uploadFileImage(string $inputName): ?string
    {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Erreur lors du téléchargement du fichier (code: " . $_FILES[$inputName]['error'] . ").");
        }

        $nomFichier = basename($_FILES[$inputName]['name']);
        $extension = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES[$inputName]['tmp_name']);
        $mimesAutorises = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime, $mimesAutorises, true)) {
            throw new \Exception("Type de fichier non autorisé. Seuls les fichiers JPEG, PNG et WebP sont autorisés.");
        }

        if (!in_array($extension, $extensionsAutorisees, true)) {
            throw new \Exception("Extension de fichier non autorisée. Seules les extensions jpg, jpeg, png et webp sont autorisées.");
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $nouveauNom = uniqid('img_', true) . '.' . $extension;
        $destination = $uploadDir . '/' . $nouveauNom;

        if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $destination)) {
            throw new \Exception("Échec du déplacement du fichier téléchargé.");
        }

        return '/uploads/' . $nouveauNom;
    }
}
