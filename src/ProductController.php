<?php

class ProductController
{
    public function __construct(private ProductGateway $gateway)
    {
    }

    public function processRequest(string $method): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAll());
                break;
            case "POST":
                // cast json_decode to array will return empty array instead of null
                //  when it is invalid or has no data
                $data = (array) json_decode(file_get_contents("php://input"), true);

                // Sanitize the input
                $sanitizedData = $this->sanitizeInput($data);
                if (!$sanitizedData) break;

                // DELETE inside POST if "ids" property is set
                if (array_key_exists("ids", $data)) {
                    // Convert array of ids to a comma-separated string
                    $idList = implode(",", $sanitizedData['ids']);
                    // Delete products and associated records
                    $result = $this->gateway->deleteProducts($idList);

                    http_response_code(200);
                    echo json_encode($result);
                    break;
                }

                // Check if there are input errors
                $errors = $this->getValidationErrors($sanitizedData);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode($errors);
                    break;
                }

                $sku = $sanitizedData["sku"];
                $name = $sanitizedData["name"];
                $price = $sanitizedData["price"];
                $type = $sanitizedData["type"];
                $attributes = array_diff_key($sanitizedData, array_flip(["sku", "name", "price", "type"]));

                // Check if the new product's sku already exists
                if ($this->gateway->productExists($sku)) {
                    http_response_code(409); // Conflict
                    echo json_encode(["message" => "SKU already exists"]);
                    break;
                }

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

                http_response_code(201);
                echo json_encode([
                    "message" => "Product created",
                    "id" => $id
                ]);
                break;

                /*
                case "DELETE":
                    // Get data from the request body
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                    $sanitizedData = $this->sanitizeInput($data);
                    if (!$sanitizedData) break;

                    // Convert array of ids to a comma-separated string
                    $idList = implode(",", $sanitizedData['ids']);
                    // Delete products and associated records
                    $result = $this->gateway->deleteProducts($idList);

                    http_response_code(200);
                    echo json_encode($result);
                    break;
                */

            default:
                http_response_code(405);
                header("Allow: GET, POST, DELETE");
        }
    }

    private function sanitizeInput(array $data): array
    {
        $sanitizedData = [];
        $validAttributes = $this->gateway->getAttributes();

        if (array_key_exists("ids", $data)) {
            // Check if the "ids" array is present and is an array
            if (!isset($data["ids"]) || !is_array($data["ids"])) {
                http_response_code(400); // Bad Request
                echo json_encode(["message" => "Invalid request data. 'ids' must be an array of strings"]);
                return [];
            } else {
                $sanitizedData["ids"] = array_map('intval', $data["ids"]);
                return $sanitizedData;
            }
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $validAttributes) || $key === "price") {
                $sanitizedData[$key] = filter_var(trim($value), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            } else {
                $sanitizedData[$key] = filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $sanitizedData;
    }

    private function getValidationErrors(array $data): array
    {
        $requiredKeys = ["sku", "name", "price", "type"];
        $validTypes = $this->gateway->getTypes();
        $validAttributes = $this->gateway->getAttributes();

        $errors = [];

        // Check if required keys exist or are empty
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key]) || $data[$key] === "") {
                $errors[$key] = "$key is required";
            }
        }

        // Check if price is a decimal
        if (isset($data["price"]) && filter_var($data["price"], FILTER_VALIDATE_FLOAT) === false) {
            $errors["price"] = "price must be a number";
        }

        // Check if product type is valid
        if (!in_array($data["type"], $validTypes)) {
            $errors["type"] = "Invalid product type";
        }

        // Check if attributes are valid and are not empty and are decimals
        foreach ($validAttributes as $attribute) {
            if (array_key_exists($attribute, $data)) {
                $value = $data[$attribute];
                if (!isset($value) || $value === "") {
                    $errors[$attribute] = "$attribute is required";
                } else if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
                    $errors[$attribute] = "$attribute must be a number";
                }
            }
        }

        // Check extra keys
        $allowedKeys = array_merge($requiredKeys, $validAttributes);
        $extraKeys = array_diff(array_keys($data), $allowedKeys);
        if (!empty($extraKeys)) {
            foreach ($extraKeys as $key) {
                $errors[$key] = "Invalid key $key";
            }
        }

        return $errors;
    }
}
