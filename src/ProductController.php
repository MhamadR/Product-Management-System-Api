<?php

namespace TestAssignment\src;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseInterface;

class ProductController
{
    public function __construct(private ProductGatewayInterface $gateway, private ProductValidatorInterface $validator)
    {
    }

    public function processRequest(ServerRequest $request, Response $response): ResponseInterface
    {
        $method = $request->getMethod();
        switch ($method) {
            case "GET":
                return $this->handleGetRequest($response);
                break;
            case "POST":
                $data = (array) json_decode(file_get_contents('php://input'));
                // DELETE inside POST if "ids" property is set
                if (array_key_exists("ids", $data)) {
                    return $this->handleDeleteRequest($data, $response);
                    break;
                }

                return $this->handlePostRequest($data, $response);
                break;

                /*
                case "DELETE":
                    $data = (array) json_decode(file_get_contents('php://input'));
                    $this->handleDeleteRequest($data, $response);
                    break;
                */
        }
        $responseBody = json_encode(['error' => 'Method Not Allowed']);
        $stream = new StreamFactory();
        $response = $response
            ->withStatus(405) // Method Not Allowed
            ->withHeader("Allow", "GET, POST, DELETE")
            ->withBody($stream->createStream($responseBody));
        return $response;
    }

    private function handleGetRequest(Response $response): ResponseInterface
    {
        $products = json_encode($this->gateway->getAll());
        $stream = new StreamFactory();
        $response = $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(($stream->createStream($products)));
        return $response;
    }

    private function handlePostRequest(array $data, Response $response): ResponseInterface
    {
        $sanitizedData = $this->validator->sanitize($data);
        // Check if there are input errors
        $errors = $this->validator->validatePostRequest($sanitizedData);
        if (!empty($errors)) {
            $responseBody = json_encode($errors);
            $stream = new StreamFactory();
            $response = $response
                ->withStatus(422) // Unprocessable Entity
                ->withHeader('Content-Type', 'application/json')
                ->withBody($stream->createStream($responseBody));
            return $response;
        }

        $sku = $sanitizedData["sku"];
        $name = $sanitizedData["name"];
        $price = $sanitizedData["price"];
        $type = $sanitizedData["type"];
        $attributes = array_diff_key($sanitizedData, array_flip(["sku", "name", "price", "type"]));

        // Get product type id
        $productTypeId = $this->gateway->lookupProductTypeId($type);


        // Convert attributes to their relevant ids
        $attributeIds = [];
        foreach ($attributes as $attributeKey => $attributeValue) {
            $attributeId = $this->gateway->lookupAttributeId($attributeKey);
            $attributeIds[$attributeId] = $attributeValue;
        }


        $newData = [
            "sku" => $sku,
            "name" => $name,
            "price" => $price,
            "productTypeId" => $productTypeId,
            "attributeIds" => $attributeIds
        ];

        $id = $this->gateway->createProduct($newData);
        $responseBody = json_encode(['message' => 'Product created', 'id' => $id]);
        $stream = new StreamFactory();
        $response = $response
            ->withStatus(201) // Created
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream->createStream($responseBody));
        return $response;
    }

    private function handleDeleteRequest(array $data, Response $response): ResponseInterface
    {

        $errors = $this->validator->validateDeleteRequest($data);
        if (!empty($errors)) {
            $responseBody = json_encode($errors);
            $response = $response->withStatus(400); // Bad Request
        } else {
            $sanitizedData = $this->validator->sanitize($data);
            // Convert array of ids to a comma-separated string
            $idList = implode(",", $sanitizedData['ids']);
            // Delete products and associated records
            $responseBody = json_encode($this->gateway->deleteProducts($idList));
            $response = $response->withStatus(200);
        }

        $stream = new StreamFactory();
        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withBody(($stream->createStream($responseBody)));

        return $response;
    }
}
