<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ville;
use App\Utils\Validation;

#[Route('/ville')]
class VilleController extends AbstractController
{
    #[Route('/liste', name: 'app_ville_liste', methods: "GET")]
    public function listeVIlle(EntityManagerInterface $em): JsonResponse
    {
        $villes = $em->getRepository(Ville::class)->findAll();
        $result = array_map(function($ville) {
            return $ville->toJson();
        }, $villes);
        
        return $this->json([
            'success' => true,
            'message' => 'Liste des villes',
            'data' => $result,
        ], 200);
    }

    #[Route('/listeCodePostal', name: 'app_ville_liste_code_postal', methods: "GET")]
    public function listeCodePostal(EntityManagerInterface $em): JsonResponse
    {
        $codesPostaux = $em->getRepository(Ville::class)->findAllCodesPostaux();
        return $this->json([
            'success' => true,
            'message' => 'Liste des codes postaux',
            'data' => $codesPostaux,
        ], 200);
    }

    #[Route('/trouver/{codePostal}', name: 'app_ville_trouver_codePostal', methods: "POST")]
    public function findByCodePostal(string $codePostal, EntityManagerInterface $em): JsonResponse
    {
        // Vérification des données
        Validation::validateCodePostal($codePostal);

        // Nettoyage des données
        $codePostal = Validation::nettoyage($codePostal);

        // Recherche de la ville par code postal
        $ville = $em->getRepository(Ville::class)->findOneBy(['codePostal' => $codePostal]);

        // Vérifier si la ville existe
        Validation::validateNotNull($ville, $codePostal);

        return $this->json([
            'success' => true,
            'message' => 'Ville trouvée',
            'data' => $ville->toJson(),
        ], 200);
    }


    #[Route('/insert/{nom}/{cp}', name: 'app_ville_insert', methods: "POST")]
    public function insertVille(string $nom, string $cp, EntityManagerInterface $em): JsonResponse
    {
        // Vérification des données
        Validation::validateVille($nom);
        Validation::validateCodePostal($cp);

        // Nettoyage des données
        $nom = Validation::nettoyage($nom);
        $nom = Validation::toUpper($nom);
        $cp = Validation::nettoyage($cp);

        // Création de l'objet
        $ville = new Ville();
        $ville->setNom($nom);
        $ville->setCodePostal($cp);

        // Insertion en base de données
        $em->persist($ville);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Ville ajoutée',
        ], 201);
    }

    #[Route('/delete/{id}', name: 'app_ville_delete', methods: "DELETE")]
    public function deleteVille(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Vérification des données
        Validation::validateInt($id);

        // Nettoyage des données
        $id = Validation::nettoyage($id);

        // Récupérer la ville
        $ville = $em->getRepository(Ville::class)->find($id);

        // Vérifier si la ville existe
        Validation::validateNotNull($ville, $id);

        // Suppression de l'objet
        $em->remove($ville);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Ville supprimée',
        ]);
    }
}
