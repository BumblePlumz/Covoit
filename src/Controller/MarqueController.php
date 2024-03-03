<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Marque;
use App\Utils\Validation;
use App\Utils\NotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

#[route('/marque')]
class MarqueController extends AbstractController
{
    #[Route('/liste', name: 'app_marque_liste', methods: "GET")]
    public function listeMarque(EntityManagerInterface $entityManager): JsonResponse
    {
        $marques = $entityManager->getRepository(Marque::class)->findAll();

        $result = array_map(function ($marque) {
            return $marque->toJson();
        }, $marques);

        return $this->json([
            'success' => true,
            'message' => 'Liste des marques',
            'data' => $result,
        ], 200);
    }

    #[Route('/liste/voiture/{marque}', name: 'app_marque_liste_voiture', methods: "GET")]
    public function listeVoitureParMarque($marque, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérification des données
        Validation::validateMarque($marque);

        // Nettoyer les données
        $marque = Validation::nettoyage($marque);

        // Démarrer une transaction
        $entityManager->beginTransaction();

        // Trouver la marque par son nom
        $marqueEntity = $entityManager->getRepository(Marque::class)->findOneBy(['nom' => $marque]);

        // Vérifier si la marque existe
        Validation::validateNotNull($marqueEntity, $marque);

        // Récupérer les voitures de la marque
        $id = $marqueEntity->getId();
        $voitureRepo = $entityManager->getRepository(Marque::class);
        $voitures = $voitureRepo->findMarqueWithVoituresAndDriver($id)->getVoitures();

        // Construire le résultat
        $result = array_map(function ($voiture) {
           return $voiture->toJson();
        }, $voitures->toArray());

        // Commiter la transaction
        $entityManager->flush();
        $entityManager->commit();

        return $this->json([
            'success' => true,
            'message' => 'Liste des voitures pour la marque : ' . $marque,
            'data' => $result,
        ], 200);
    }

    #[Route('/insert/{nom}', name: 'app_marque_insert', methods: "POST")]
    public function insertMarque($nom, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérification des données
        Validation::validateMarque($nom);

        // Nettoyer les données
        $nom = Validation::nettoyage($nom);

        // Créer une nouvelle instance de Marque
        $marque = new Marque();
        $marque->setNom($nom);

        // Sauvegarder la marque en base de donnée
        $entityManager->persist($marque);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Marque ajoutée avec succès!',
        ], 201);
    }

    #[Route('/delete/{id}', name: 'app_marque_delete', methods: "DELETE")]
    public function deleteMarque($id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérification des données
        Validation::validateInt($id);

        // Nettoyer les données
        $id = Validation::nettoyage($id);

        // Trouver la marque par son id
        $marque = $entityManager->getRepository(Marque::class)->find($id);

        // Vérifier si la marque existe
        Validation::validateNotNull($marque, $id);

        // Supprimer la marque de la base de donnée
        $entityManager->remove($marque);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Marque supprimée avec succès!',
        ], 200);
    }
}
