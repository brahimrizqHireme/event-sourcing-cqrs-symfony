<?php

namespace App\Controller;

use App\Application\Command\ChangeProductName;
use App\Application\Command\CreateProduct;
use App\Application\Event\ProductNameWasChanged;
use App\Application\ValueObject\ProductId;
use App\Document\Product;
use App\Domain\CQRS\Command\CommandBusInterface;
use App\Domain\Db\MongodbClient;
use MongoDB\Client;
use MongoDB\Database;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    public function __construct(
        private CommandBusInterface $commandBus
    )
    {
    }

    #[Route('/', name: 'app_product')]
    public function index(MongodbClient $connection): Response
    {

        $command = new CreateProduct(
            ProductId::generate(),
            'test name',
            'test description'
        );
        $this->commandBus->dispatch($command);
        return new JsonResponse([
            'status' => 'Product was created',
            'product_id' => $command->id()->toString(),
        ], JsonResponse::HTTP_ACCEPTED);
    }

    #[Route('/change-name/{name}', name: 'change_product_name')]
    public function changeProductName(string $name, Request $request) :Response
    {
        $start = microtime(true);
//        for ($i = 0; $i < 1000; $i++) {

        $command = new ChangeProductName(
                ProductId::fromString("3cdf59f2-2383-4d59-9195-43f78688fa65"),
                $name
            );
            $this->commandBus->dispatch($command);
//            dump($command->id()->toString());

//        }

        $time_elapsed_secs = microtime(true) - $start;

        dd(round($time_elapsed_secs, 2));
        return new JsonResponse([
            'status' => 'Product was created',
            'product_id' => $command->id()->toString(),
        ], JsonResponse::HTTP_ACCEPTED);
    }
}
