<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Utilisateur;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Utils\Validation;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UtilisateurController extends AbstractController
{
    #[Route('/login/{login}/{password}', name: 'app_login', methods: ['POST'])]

    public function login(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, $login, $password, JWTTokenManagerInterface $jWTManager): JsonResponse
    {
        // Vérication des données
        Validation::validateUsername($login);
        Validation::validatePassword($password);

        // Nettoyage des données
        $login = Validation::nettoyage($login);
        $password = Validation::nettoyage($password);

        // Recherche de l'utilisateur en base de données
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->findOneBy(['login' => $login]);
        if (!$utilisateur) {
            return new JsonResponse(['status' => 'Utilisateur non trouvé!'], Response::HTTP_NOT_FOUND);
        }
        if ($utilisateur->isActif() == false) {
            return new JsonResponse(['status' => 'Utilisateur non actif!'], Response::HTTP_FORBIDDEN);
        }

        if (!$passwordHasher->isPasswordValid($utilisateur, $password)) {
            return new JsonResponse(['status' => 'Mot de passe incorrect!'], Response::HTTP_FORBIDDEN);
        }

        // Vérification du mot de passe
        if (!$passwordHasher->isPasswordValid($utilisateur, $password)) {
            return new JsonResponse([
                'status' => 'Mot de passe incorrect!'
            ], Response::HTTP_FORBIDDEN);
        }

        $token = $jWTManager->create($utilisateur);
        $utilisateur->setToken($token);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'token' => $token,
                'utilisateur' => $utilisateur->getid(),
            ]
        ], 200);
    }

    #[Route('/inscription/{login}/{password}', name: 'app_utilisateur', methods: ['GET', 'POST'])]
    public function inscription(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, $login, $password): JsonResponse
    {
        // Vérication des données
        Validation::validateUsername($login);
        Validation::validatePassword($password);

        // Nettoyage des données
        $login = Validation::nettoyage($login);
        $password = Validation::nettoyage($password);

        // Création de l'objet
        $utilisateur = new Utilisateur();
        $utilisateur->setLogin($login);
        $hashedPassword = $passwordHasher->hashPassword($utilisateur, $password);
        $utilisateur->setPassword($hashedPassword);
        $utilisateur->setRoles(['ROLE_USER']);
        $utilisateur->setActif(0);

        // Insertion en base de données
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Utilisateur enregistrée avec succès!',
        ], 201);
    }

    #[Route('/inscriptionAdmin/{login}/{password}', name: 'app_admin', methods: ['GET', 'POST'])]
    public function inscriptionAdmin(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, $login, $password): JsonResponse
    {
        // Vérication des données
        Validation::validateUsername($login);
        Validation::validatePassword($password);

        // Nettoyage des données
        $login = Validation::nettoyage($login);
        $password = Validation::nettoyage($password);

        // Création de l'objet
        $utilisateur = new Utilisateur();
        $utilisateur->setLogin($login);
        $hashedPassword = $passwordHasher->hashPassword($utilisateur, $password);
        $utilisateur->setPassword($hashedPassword);
        $utilisateur->setRoles(['ROLE_ADMIN']);
        $utilisateur->setActif(1);

        // Insertion en base de données
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Admin enregistrée avec succès!',
        ], 201);
    }

    #[Route('/liste/inactif', name: 'app_personne_liste_inactif', methods: ['GET'])]
    public function listeInactif(EntityManagerInterface $entityManager): JsonResponse
    {
        $personnes = $entityManager->getRepository(Utilisateur::class)->findBy(['actif' => false]);
        $result = array_map(function($personne) {
            return $personne->toJson();
        }, $personnes);

        return $this->json([
            'success' => true,
            'message' => 'Liste des personnes inactives',
            'data' => $result
        ], 200);
    }

    #[Route('/update-actif/{id}/{actif}', name: 'app_update_personne_actif', methods: ["PUT"])]
    public function updatePersonneActif(int $id, int $actif, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérification des données
        Validation::validateInt($id);
        Validation::validateInt($actif);

        // Nettoyage des données
        $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;
        $isActif = Validation::validateExiste($actif) ? Validation::nettoyage($actif) : null;

        // Récupération de l'objet Personne
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);
        Validation::validateNotNull($utilisateur);

        // Mettre à jour le champ isActif de l'objet Personne si la donnée est différente de null
        $utilisateur->setActif($isActif == 1);

        // Enregistrer les modifications en base de données
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Champ actif de la personne mis à jour avec succès!',
        ]);
    }
}
