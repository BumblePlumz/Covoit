<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Utils\ValidationException;
use App\Utils\Validation;
use App\Entity\Trajet;
use App\Entity\Personne;
use App\Entity\Utilisateur;

#[route('/inscription')]
class InscriptionController extends AbstractController
{
    private $jwtManager;
    private $tokenStorage;

    public function __construct(JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorage)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
    }

    // La liste des inscriptions de l'utilisateur connecté
    #[Route('/', name: 'app_inscription', methods: "GET")]
    public function listeInscription(EntityManagerInterface $em): JsonResponse
    {
        // Récupération de l'utilisateur connecté
        $personne = $this->getPersonneFromToken($em);

        // Récupération des inscriptions
        $trajets = $personne->getTrajets();
        Validation::validateNotNull($trajets, 'Pas de trajet trouvé');

        $result = array_map(function ($trajet) {
            return $trajet->toJson();
        }, $trajets);

        return $this->json([
            'success' => true,
            'message' => 'liste des inscriptions',
            'data' => $result,
        ], 200);
    }

    // Le conducteur d'un trajet
    #[Route('/conducteur/{idtrajet}', name: 'app_inscription_liste_conducteur', methods: "GET")]
    public function listeInscriptionConducteur(int $idtrajet, EntityManagerInterface $em): JsonResponse
    {
        // Récupération des données
        Validation::validateInt($idtrajet);

        // Nettoyage des données
        $idtrajet = Validation::nettoyage($idtrajet);

        // Récupération du trajet
        $trajet = $em->getRepository(Trajet::class)->find($idtrajet);
        Validation::validateNotNull($trajet, "id : " . $idtrajet);

        // Récupération du conducteur
        $conducteur = $trajet->getConducteur();
        Validation::validateNotNull($conducteur, 'Conducteur introuvable !');

        $result = $conducteur->toJson();
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Le conducteur du trajet',
            'data' => $result,
        ], 200);
    }

    // La liste des trajets d'une personne
    #[Route('/listePersonne/{idpers}', name: 'app_inscription_liste_personne', methods: "GET")]
    public function listeInscriptionUser(int $idpers, EntityManagerInterface $em): JsonResponse
    {
        // Vérification des données
        Validation::validateInt($idpers);

        // Nettoyage des données
        $idpers = Validation::nettoyage($idpers);

        // Récupération et vérification de la personne
        $personne = $em->getRepository(Personne::class)->find($idpers);
        Validation::validateNotNull($personne, "id : " . $idpers);

        // Récupération des trajets de la personne
        $trajets = $personne->getTrajets();
        $result = array_map(function ($trajet) {
            return $trajet->toJson();
        }, $trajets);


        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'liste des trajets de la personne',
            'data' => $result,
        ], 200);
    }

    #[Route('/insert/{idpers}/{idtrajet}', name: 'app_insert_inscription', methods: "POST")]
    public function insertInscription(EntityManagerInterface $em, $idpers, $idtrajet): JsonResponse
    {
        // Récupération et validation des données
        $personne = $em->getRepository(Personne::class)->find($idpers);
        Validation::validateNotNull($personne, "id personne : ".$idpers);

        $trajet = $em->getRepository(Trajet::class)->find($idtrajet);
        Validation::validateNotNull($trajet, "id trajet : ".$idtrajet);

        // Mis à jour du trajet
        $passagers = $trajet->getPassagers();
        if ($trajet->isPlacesDisponible()){
            $trajet->addPassager($personne);
            $em->persist($trajet);
        }else{
            throw new ValidationException('Plus de place disponible');
        }
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Inscription réussie',
            'data' => $trajet,
        ], 200);
    }

    #[Route('/delete/{idtrajet}', name: 'app_delete_passager', methods: "DELETE")]
    public function deletePassager(int $idtrajet, EntityManagerInterface $em): JsonResponse
    {
        // Récupération de l'utilisateur connecté
        $personne = $this->getPersonneFromToken($em);

        // Récupération du trajet
        $trajet = $em->getRepository(Trajet::class)->find($idtrajet);
        Validation::validateNotNull($trajet, "id trajet : ".$idtrajet);

        // Suppression du passager du trajet
        if ($trajet->getPassagers()->contains($personne)) {
            $trajet->removePassager($personne);
            $em->persist($trajet);
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Passager introuvable dans le trajet',
            ], 404);
        }
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Passager supprimé du trajet',
        ], 200);
    }

    private function getPersonneFromToken($em): Personne
    {
        // Récupérer le jeton du TokenStorage
        $token = $this->tokenStorage->getToken();
        Validation::validateNotNull($token, 'Vous devez être connecté pour effectuer cette action!');

        if (!$token) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous devez être connecté pour effectuer cette action!',
            ], 400);
        }
        // Décoder le jeton
        $decodedToken = $this->jwtManager->decode($token);
        $login = $decodedToken['login'];

        // Récupération de l'utilisateur connecté'
        $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy(['login' => $login]);
        Validation::validateNotNull($utilisateur, 'Utilisateur introuvable !');

        // Récupération de la personne
        $personne = $em->getRepository(Personne::class)->findOneBy(['utilisateur' => $utilisateur->getId()]);
        Validation::validateNotNull($personne, 'Personne introuvable !');

        return $personne;
    }
}
