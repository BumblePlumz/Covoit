<?php

namespace App\Utils;

use App\Utils\ValidationException;
use App\Utils\NotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class Validation
{
    // -----------------------
    // TYPE VALIDATION
    // -----------------------
    public static function validateInt($number): void
    {
        if (!is_numeric($number)) {
            throw new ValidationException('Veuillez entrer un nombre valide');
        }
    }

    public static function validateString($string): void
    {
        if (!preg_match('/^[A-Za-z\s-]+$/', $string)) {
            throw new ValidationException('Veuillez entrer une chaîne de caractères valide');
        }
    }

    // -----------------------
    // DATA VALIDATION
    // -----------------------
    public static function validateEmail($email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Adresse email invalide');
        }
    }

    public static function validateUsername($username): void
    {
        if (!preg_match('/^(?=.*[a-zA-Z_])[\w]{3,20}$/', $username)) {
            throw new ValidationException('Le nom d\'utilisateur doit contenir entre 3 et 20 caractères et ne doit contenir que des lettres, des chiffres et des underscores');
        }
    }

    public static function validatePassword($password): void
    {
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{12,}$/', $password)) {
            throw new ValidationException('Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre, un caractère spécial et doit contenir au moins 12 caractères');
        }
    }
    public static function validateMarque($nom): void
    {
        if (!preg_match('/^[a-zA-Z0-9\s\-\'\.,]+$/', $nom)) {
            throw new ValidationException('Le nom de la marque doit contenir entre 3 et 20 caractères et ne doit contenir que des lettres, des chiffres et des espaces');
        }
    }

    public static function validateVille($nom): void
    {
        if (!preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\- ]+$/', $nom)) {
            throw new ValidationException('Le nom de la ville doit contenir que des lettres et certains caractères spéciaux (- et espace)');
        }
    }

    public static function validateCodePostal($cp): void
    {
        if (!preg_match('/^[0-9]{5}$/', $cp)) {
            throw new ValidationException('Le code postal doit contenir 5 chiffres');
        }
    }

    public static function validateImmatriculation($immatriculation): void
    {
        if (!preg_match('/^[A-Z]{2}-[0-9]{3}-[A-Z]{2}$/', $immatriculation)) {
            throw new ValidationException('Immatriculation invalide');
        }
    }

    public static function validateTelephone($tel): void
    {
        if (!preg_match('/^\+(?:\d{11})|\d{9}$/', $tel)) {
            throw new ValidationException('Numéro de téléphone invalide (format: +33612345678 ou 0612345678)');
        }
    }

    public static function validateBcryptPassword($password, $hash): void
    {
        if (!password_verify($password, $hash)) {
            throw new ValidationException('Mot de passe incorrect');
        }
    }

    public static function validateDatetime($datetime): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $datetime)) {
            throw new ValidationException('Format de date et heure invalide. Utilisez le format "Y-m-d H:i"');
        }
    }

    public static function validateDate($date): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new ValidationException('Format de date invalide. Utilisez le format "Y-m-d"');
        }
    }

    public static function validateTime($time): void
    {
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            throw new ValidationException('Format d\'heure invalide. Utilisez le format "H:i"');
        }
    }

    public static function validateModele($modele): void
    {
        if (!preg_match('/^[A-Za-z0-9\s-]+$/', $modele)) {
            throw new ValidationException('Le modèle doit contenir entre 3 et 20 caractères et ne doit contenir que des lettres, des chiffres et des espaces');
        }
    }

    public static function validateObjectType($object, $type): void
    {
        if (!is_a($object, $type)) {
            throw new ValidationException('Type d\'objet invalide');
        }
    }
    // -----------------------
    // ETAT VALIDATION
    // -----------------------
    public static function validateExiste($variable): bool
    {
        return isset($variable) && !empty($variable);
    }

    public static function validateNotNull($variable, $label = ""): void
    {
        if ($variable == null) {
            throw new NotFoundException('Veuillez entrer une valeur correcte pour : ' . $label);
        }
    }

    public static function validateIsEmpty($variable): void
    {
        if (empty($variable)) {
            throw new NotFoundException('Aucun résultat trouvé');
        }
    }

    public static function validateHas($request, $variable): bool
    {
        return $request->request->has($variable);
    }

    public static function validateBoolean($field): void
    {
        if (!is_bool($field)) {
            throw new ValidationException('Le champ doit être un booléen');
        }
    }

    // -----------------------
    // NETTOYAGE
    // -----------------------
    public static function nettoyage($string): string
    {
        $string = trim($string);
        $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        return $string;
    }

    public static function toUpper($string): string
    {
        return strtoupper($string);
    }

    // -----------------------
    // CALCUL
    // -----------------------
    public static function checkPlacesDisponible($placesDisponible, $passagers): void
    {
        if ($passagers > $placesDisponible) {
            throw new ValidationException('Il n\'y a plus de places disponibles pour ce trajet!');
        }
    }
}
