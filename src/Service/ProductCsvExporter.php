<?php

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductCsvExporter
{
    public function streamProductsCsvResponse(array $products): StreamedResponse
    {
        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            // header row
            fputcsv($handle, ['id', 'name', 'description', 'price']);
            foreach ($products as $p) {
                /** @var Product $p */
                fputcsv($handle, [
                    $p->getId(),
                    $p->getName(),
                    $p->getDescription(),
                    $p->getPrice(),
                ]);
            }
            fclose($handle);
        };

        $response = new StreamedResponse($callback);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="produits.csv"');

        return $response;
    }
}
