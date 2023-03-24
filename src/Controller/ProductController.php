<?php

namespace App\Controller;

use App\Document\Product;
use App\Domain\Db\MongodbClient;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use MongoDB\Client;
use MongoDB\Database;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product')]
    public function index(ManagerRegistry $managerRegistry, MongodbClient $connection): Response
    {
//        dd($connection, $managerRegistry->getConnection(), 'test');
//        $db = $connection->selectDatabase('laravel')->selectCollection('test');
//        dd($db->find()->toArray());
//        $p = new Product();
//        $p->setName('sasa');
//        $p->setDescription('desc');
//        $cn = $managerRegistry->getConnection();
//        $cn->persist($p);
//        $cn->flush();

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }
}
