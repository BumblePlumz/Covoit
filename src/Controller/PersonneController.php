<?php

namespace App\Controller;

use App\Utils\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Personne;
use App\Utils\ValidationException;
use App\Entity\Marque;
use App\Entity\Voiture;
use App\Entity\Ville;
use Doctrine\ORM\Exception\ORMException;
use App\Entity\Utilisateur;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/personne')]
class PersonneController extends AbstractController
{
    private $jwtManager;
    private $tokenStorage;

    public function __construct(JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorage)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/liste', name: 'app_personne_liste', methods: ['GET'])]
    public function liste(EntityManagerInterface $em): JsonResponse
    {
        $personnes = $em->getRepository(Personne::class)->findAll();
        $result = array_map(function ($personne) {
            return $personne->toJsonAvecDependances();
        }, $personnes);

        return $this->json([
            'success' => true,
            'message' => 'Liste des personnes',
            'data' => $result,
        ], 200);
    }

    #[Route('/insert/{prenom}/{nom}/{tel}/{email}/{ville}/{voitureId}', name: 'app_personne_insert', methods: "POST")]
    public function insertPersonne(EntityManagerInterface $em, $prenom, $nom, $tel, $email, $ville, $voitureId): JsonResponse
    {
        try {
            // Vérication des données
            Validation::validateString($prenom);
            Validation::validateString($nom);
            Validation::validateTelephone($tel);
            Validation::validateEmail($email);
            Validation::validateVille($ville);
            Validation::validateInt($voitureId);

            // Nettoyage des données
            $prenom = Validation::nettoyage($prenom);
            $nom = Validation::nettoyage($nom);
            $tel = Validation::nettoyage($tel);
            $email = Validation::nettoyage($email);
            $ville = Validation::nettoyage($ville);
            $ville = Validation::toUpper($ville);
            $voitureId = Validation::nettoyage($voitureId);

            // Récupérer le jeton du TokenStorage
            $token = $this->tokenStorage->getToken();

            if (!$token) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Vous devez être connecté pour effectuer cette action!',
                ], 400);
            }

            // Décoder le jeton
            $decodedToken = $this->jwtManager->decode($token);
            $login = $decodedToken['login'];

            // Début de la transaction
            $em->beginTransaction();

            // Récupération de l'objet Utilisateur
            $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy(['login' => $login]);
            Validation::validateNotNull($utilisateur, "Login : ".$login);

            // Création de l'objet Voiture
            $voiture = $em->getRepository(Voiture::class)->find($voitureId);
            Validation::validateNotNull($voiture, "voiture : ".$voitureId);

            // Création de l'objet Ville
            $villeEntity = $em->getRepository(Ville::class)->findOneBy(['nom' => $ville]);
            Validation::validateNotNull($ville, "ville : ".$ville);

            // Création de l'objet Personne
            $personne = new Personne();
            $personne->setUtilisateur($utilisateur);
            $personne->setPrenom($prenom);
            $personne->setNom($nom);
            $personne->setTel($tel);
            $personne->setEmail($email);
            $personne->setVille($villeEntity);
            $personne->addVoiture($voiture);

            $em->persist($personne);
            $em->flush();
            $em->commit();
            return $this->json([
                'success' => true,
                'message' => 'Personne enregistrée avec succès!',
            ], 201);
        } catch (ValidationException $e) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la validation des données",
                'error' => $e->getMessage(),
            ], $e->getCode());
        } catch (ORMException $e) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de l'insertion en base de données",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/select/{id}', name: 'app_personne_select', methods: "POST")]
    public function selectPersonne(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Vérication des données
        Validation::validateInt($id);

        // Nettoyage des données
        $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;

        // Récupération de l'objet Personne
        $personne = $em->getRepository(Personne::class)->find($id);
        Validation::validateExiste($personne);
        Validation::validateNotNull($personne);
        Validation::validateExiste($personne) ? null : throw new ValidationException("Personne inexistant!");

        $result = $personne->toJsonAvecDependances();

        return $this->json([
            'success' => true,
            'message' => 'Personne trouvée avec succès!',
            'personne' => $result,
        ], 200);
    }

    // changer pour une request body afin de traiter les cas nulls.
    #[Route('/update/{id}/{prenom}/{nom}/{tel}/{email}/{marque}/{modele}/{nbPlaces}/{immatriculation}', name: 'app_update_personne', methods: "PUT")]
    public function updatePersonne(int $id, ?string $prenom, ?string $nom, ?string $tel, ?string $email, ?string $marque, ?string $modele, ?int $nbPlaces, ?string $immatriculation, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Vérication des données
            Validation::validateInt($id);
            Validation::validateString($prenom);
            Validation::validateString($nom);
            Validation::validateTelephone($tel);
            Validation::validateEmail($email);
            Validation::validateMarque($marque);
            Validation::validateModele($modele);
            Validation::validateInt($nbPlaces);
            Validation::validateImmatriculation($immatriculation);

            // Nettoyage des données
            $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;
            $prenom = Validation::validateExiste($prenom) ? Validation::nettoyage($prenom) : null;
            $nom = Validation::validateExiste($nom) ? Validation::nettoyage($nom) : null;
            $tel = Validation::validateExiste($tel) ? Validation::nettoyage($tel) : null;
            $email = Validation::validateExiste($email) ? Validation::nettoyage($email) : null;
            $marque = Validation::validateExiste($marque) ? Validation::nettoyage($marque) : null;
            $modele = Validation::validateExiste($modele) ? Validation::nettoyage($modele) : null;
            $nbPlaces = Validation::validateExiste($nbPlaces) ? Validation::nettoyage($nbPlaces) : null;
            $immatriculation = Validation::validateExiste($immatriculation) ? Validation::nettoyage($immatriculation) : null;

            // Début de la transaction
            $em->beginTransaction();

            // Récupération de l'objet Personne
            $personne = $em->getRepository(Personne::class)->findPersonneWithDependenciesById($id);
            Validation::validateNotNull($personne, "id : ".$id);

            // Mettre à jour les propriétés de l'objet Personne si les données sont différentes de null
            $prenom == null ? null : $personne->setPrenom($prenom);
            $nom == null ? null : $personne->setNom($nom);
            $tel == null ? null : $personne->setTel($tel);
            $email == null ? null : $personne->setEmail($email);

            // Récupérer la voiture correspondant à l'immatriculation
            $voiture = $em->getRepository(Voiture::class)->findOneBy(['immatriculation' => $immatriculation]);
            
            // Si la personne n'a pas encore de voiture
            if ($voiture === null) {
                $voiture = new Voiture();
                $voiture->setImmatriculation($immatriculation);
                $voiture->setModele($modele);
                $voiture->setPlaces($nbPlaces);
                $personne->addVoiture($voiture);
            } else {
                // Mettre à jour les informations de la voiture existante
                $voiture->setModele($modele);
                $voiture->setPlaces($nbPlaces);
            }

            // Récupérer ou créer la marque associée à la voiture
            if ($marque !== null) {
                $marqueEntity = $em->getRepository(Marque::class)->findOneBy(['nom' => $marque]);
                if ($marqueEntity === null) {
                    $marqueEntity = new Marque();
                    $marqueEntity->setNom($marque);
                    $em->persist($marqueEntity);
                }
                $voiture->setMarque($marqueEntity);
            }

            // Enregistrer les modifications en base de données
            $em->flush();

            // Valider la transaction
            $em->commit();

            return $this->json([
                'success' => true,
                'message' => 'Personne updated successfully!',
            ]);
        } catch (ValidationException $e) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la validation des données",
                'error' => $e->getMessage(),
            ], 409);
        } catch (ORMException $e) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la mise à jour en base de données",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/delete/{id}', name: 'app_personne_delete', methods: "DELETE")]
    public function deletePersonne(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Récupération des données
        $personne = $em->getRepository(Personne::class)->find($id);

        // Vérification que la personne existe        
        Validation::validateNotNull($personne, $id);

        $em->remove($personne);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'La personne a été supprimée avec succès!',
        ], 201);
    }
}
