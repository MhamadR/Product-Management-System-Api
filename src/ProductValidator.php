<?php

namespace TestAssignment\src;

class ProductValidator implements ProductValidatorInterface
{

    public function __construct(private ProductGatewayInterface $gateway)
    {
    }

    public function validatePostRequest(array $data): array
    {
        $requiredKeys = ["sku", "name", "price", "type"];
        $validTypes = $this->gateway->getTypes();
        $validAttributes = $this->gateway->getAttributes();

        $errors = [];
        // Check if sku exists
        if ($this->gateway->productExists($data["sku"])) {
            $errors["sku"] = "SKU already exists";
        }

        // Check if required keys exist or are empty
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key]) || $data[$key] === "") {
                $errors[$key] = ucfirst($key) . " is required";
            }
        }

        // Check if price is a decimal
        if (isset($data["price"]) && filter_var($data["price"], FILTER_VALIDATE_FLOAT) === false) {
            $errors["price"] = "Price must be a number";
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
                    $errors[$attribute] = ucfirst($attribute) . " must be a number";
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

    public function validateDeleteRequest(array $data): array
    {
        $errors = [];

        if (!isset($data["ids"]) || !is_array($data["ids"]) || empty($data['ids'])) {
            $errors['ids'] = "Invalid request data. 'ids' must be a non-empty array of integers";
        } else {
            foreach ($data["ids"] as $id) {
                if (!filter_var($id, FILTER_VALIDATE_INT)) {
                    $errors['ids'] = "Invalid request data. 'ids' must contain only valid integers";
                    break;
                }
            }
        }

        return $errors;
    }

    public function sanitize(array $data): array
    {
        $sanitizedData = [];

        if (array_key_exists("ids", $data)) {
            $sanitizedData["ids"] = array_map('intval', $data["ids"]);
            return $sanitizedData;
        }

        foreach ($data as $key => $value) {
            $sanitizedData[$key] = filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $sanitizedData;
    }
}
