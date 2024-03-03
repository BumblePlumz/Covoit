<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Voiture;
use App\Utils\Validation;
use App\Utils\NotFoundException;
use App\Entity\Marque;

#[Route('/voiture')]
class VoitureController extends AbstractController
{
    #[Route('/liste', name: 'app_voiture_liste', methods: "GET")]
    public function listeVoiture(EntityManagerInterface $em): JsonResponse
    {
        $repository = $em->getRepository(Voiture::class);
        $voitures = $repository->findAll();
        
        $result = array_map(function($voiture) {
            return $voiture->toJsonAvecMarque();
        }, $voitures);

        return $this->json([
            'success' => true,
            'message' => 'Liste des voitures',
            'data' => $result,
        ], 200);
    }
    
    #[Route('/insert/{modele}/{place}/{marqueId}/{immatriculation}', name: 'app_voiture_insert', methods: "POST")]
    public function insertVoiture(string $modele, int $place, int $marqueId, string $immatriculation, EntityManagerInterface $em): JsonResponse
    {
        // Validation des données
        Validation::validateModele($modele);
        Validation::validateInt($place);
        Validation::validateInt($marqueId, 'Marque');
        Validation::validateImmatriculation($immatriculation, 'Immatriculation');

        // Nettoyage des données
        $modele = Validation::nettoyage($modele);
        $place = Validation::nettoyage($place);
        $marqueId = Validation::nettoyage($marqueId);
        $immatriculation = Validation::nettoyage($immatriculation);

        // Récupération de la marque
        $marque = $em->getRepository(Marque::class)->find($marqueId);

        // Vérification que la marque de la voiture existe
        Validation::validateNotNull($marque, 'Marque');
        
        $voiture = new Voiture();
        $voiture->setModele($modele);
        $voiture->setPlaces($place);
        $voiture->setMarque($marque);
        $voiture->setImmatriculation($immatriculation);

        $em->persist($voiture);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Voiture ajoutée avec succès',
        ], 201);
    }

    #[Route('/delete/{id}', name: 'app_voiture_delete', methods: "DELETE")]
    public function deleteVoiture(int $id, EntityManagerInterface $em): JsonResponse
    {   
        // Validation des données
        Validation::validateInt($id);

        // Nettoyage des données
        $id = Validation::nettoyage($id);

        // Récupération des données
        $voiture = $em->getRepository(Voiture::class)->find($id);

        // Vérification que la voiture existe
        Validation::validateNotNull($voiture, 'Voiture');

        $em->remove($voiture);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Voiture supprimée avec succès',
        ], 200);
    }
}
